<?php
/**
 * Miso\Miso
 *
 * @author     Jidaikobo Inc.
 * @license    The MIT License (MIT)
 * @copyright  Jidaikobo Inc.
 * @link       http://www.jidaikobo.com
 */
namespace Miso;

class Miso
{
	protected static $models = array();
	protected static $views = array();
	protected static $controllers = array();

	/**
	 * prepare
	 *
	 * @return Void
	 */
	public static function prepare()
	{
		// crawl theme to declare MVC classes
		self::crawl(get_stylesheet_directory());

		// crawl child theme theme, if used
		if (get_stylesheet_directory() != get_template_directory())
		{
			self::crawl(get_template_directory());
		}

		// set tempalte path
		if (file_exists(get_stylesheet_directory().'/miso/view/'))
		{
			View::forge(get_stylesheet_directory().'/miso/view/');
		}

		// add tempalte path of child theme
		if (
			get_stylesheet_directory() != get_template_directory() &&
			file_exists(get_template_directory().'/miso/view/')
		)
		{
			View::addTplPath(get_template_directory().'/miso/view/');
		}

		// load language if exists
		foreach (glob(get_stylesheet_directory()."/miso/languages/*") as $filename)
		{
			load_plugin_textdomain('miso', FALSE, dirname($filename));
			break;
		}

		// add language of child theme
		if (get_stylesheet_directory() != get_template_directory())
		{
			foreach (glob(get_template_directory()."/miso/".$dir."/*.php") as $filename)
			{
				load_plugin_textdomain('miso', FALSE, dirname($filename));
				break;
			}
		}
	}

	/**
	 * crawl
	 *
	 * @param  String $path
	 * @return Void
	 */
	private static function crawl($path)
	{
		foreach (array('model', 'controller') as $dir)
		{
			$type = $dir.'s';

			foreach (glob($path."/miso/".$dir."/*.php") as $filename)
			{
				// just load sub dir depth 1. this maybe trait
				$dir2 = substr(basename($filename), 0, -4); // remove .php
				foreach (glob($path."/miso/".$dir.'/'.$dir2."/*.php") as $trait)
				{
					include($trait);
				}

				// load model/controller/trait
				include($filename);

				// add to list?
				$class_name = self::path2classname($dir, $filename);
				if (in_array($class_name, static::${$type})) continue;
				if ( ! class_exists($class_name)) continue;
				static::${$type}[] = $class_name;

				// init
				if (method_exists($class_name, '_init') and is_callable($class_name.'::_init'))
				{
					call_user_func($class_name.'::_init');
				}
			}
		}
	}

	/**
	 * setPageTitle
	 *
	 * @return Void
	 */
	public static function setPageTitle()
	{
		$slug = Input::get('page');
		$classname = self::slug2classname($slug);
		if ( ! class_exists($classname)) return;

		$action = Input::get('action');
		if ( ! method_exists($classname, 'action'.ucfirst($action))) return;

		$properties = $classname::properties();
		if (
			! isset($properties[$action]['page_title']) ||
			empty($properties[$action]['page_title'])
		) return;
		$properties = $properties[$action];

		// modify page title
		add_action(
			'admin_menu',
			function () use ($properties)
			{
				global $title;
				$title = $properties['page_title'];
			}
		);
	}

	/**
	 * assets
	 *
	 * @return Void
	 */
	public static function assets()
	{
	}

	/**
	 * controller
	 *
	 * @return Void
	 */
	public static function controller()
	{
		$controllers = self::get('controllers');

		foreach ($controllers as $controller)
		{
			// left admin menu
			add_action(
				'admin_menu',
				function () use ($controller)
				{
					$properties = $controller::properties();
					$slug = self::classname2slug($controller);

					foreach ($properties as $k => $v)
					{
						// default
						// the others of below should call error
						$icon_url = Arr::get($properties[$k], 'icon_url', '');
						$position = Arr::get($properties[$k], 'position');
						$position = $position ?: 25;

						// add_menu_page
						if ($k == 'index')
						{
							add_menu_page(
								__($properties['index']['page_title'], 'miso'), // page title
								__($properties['index']['menu_title'], 'miso'), // menu title
								$properties['index']['capability'], // capability
								$slug, // slug
								array($controller, 'base'), // function
								$icon_url, // icon_url
								$position // position
							);
							continue;
						}
						elseif (Arr::get($properties[$k], 'is_submenu', false))
						{
							add_submenu_page(
								$slug,
								__($properties[$k]['page_title'], 'miso'),
								__($properties[$k]['menu_title'], 'miso'),
								$properties[$k]['capability'],
								$slug.'&amp;action='.$k,
								array($controller, 'base')
							);
						}

						// accessible but not show in menu
						if (Arr::get($properties['index'], 'is_menu', false))
						{
							remove_menu_page($slug);
						}

					} // end of foreach
				} // end of function
			); // end of add_action
		}
	}

	/**
	 * path2classname
	 *
	 * @param  String $type
	 * @param  String $path
	 * @param  String $parent
	 * @return String
	 */
	private static function path2classname($type, $path, $parent = '')
	{
		$base = ucfirst(substr(basename($path), 0, -4));
		if ($parent)
		{
			return '\\Miso\\'.ucfirst($type).'_'.ucfirst($parent).'_'.$base;
		}
		return '\\Miso\\'.ucfirst($type).'_'.$base;
	}

	/**
	 * classname2slug
	 *
	 * @param  String $classname
	 * @return String
	 */
	public static function classname2slug($classname)
	{
		return str_replace('\\', '', $classname);
	}

	/**
	 * slug2classname
	 *
	 * @param  String $slug
	 * @return String
	 */
	public static function slug2classname($slug)
	{
		return str_replace('Miso', '\\Miso\\', $slug);
	}

	/**
	 * isCurrent
	 *
	 * @param  String $action
	 * @return Bool
	 */
	public static function isCurrent($action)
	{
		// is index
		if ( ! Input::get('action') && $action == 'index') return true;
		return Input::get('action') == $action;
	}

	/**
	 * getMisoUrl
	 *
	 * @param  String $controller_class
	 * @param  String $action
	 * @return String
	 */
	public static function getMisoUrl($controller_class, $action)
	{
		$controller = esc_html(static::classname2slug($controller_class));
		if ($action == 'index')
		{
			return admin_url('admin.php?page='.$controller);
		}
		$action = esc_html($action);
		return admin_url('admin.php?page='.$controller.'&action='.$action);
	}

	/**
	 * redirect
	 *
	 * @param  String $controller
	 * @param  String $action
	 * @return String
	 */
	public static function redirect($controller, $action)
	{
		ob_end_clean();
		wp_safe_redirect(static::getMisoUrl($controller, $action));
		exit;
	}

	/**
	 * get
	 *
	 * @param  String $type
	 * @return Array
	 */
	public static function get($type)
	{
		$type = strtolower($type);
		return static::${$type};
	}

	/**
	 * bufferStart
	 *
	 * @return Void
	 */
	public static function bufferStart()
	{
		if (
			! is_admin() ||
			! isset($_SERVER['REQUEST_URI']) ||
			strpos($_SERVER['REQUEST_URI'], 'MisoController_') === false
		) return;

		ob_start();
		return;
	}

	/**
	 * bufferOut
	 *
	 * @return Void
	 * @link https://stackoverflow.com/questions/772510/wordpress-filter-to-modify-final-html-output
	 */
	public static function bufferOut()
	{
		if (
			! is_admin() ||
			! isset($_SERVER['REQUEST_URI']) ||
			strpos($_SERVER['REQUEST_URI'], 'MisoController_') === false
		) return;

		$levels = ob_get_level();

		$final = '';
		for ($i = 0; $i < $levels; $i++)
		{
			$final .= ob_get_clean();
		}
		echo $final;

		return;
	}
}
