<?php
/**
 * Miso\Controller
 *
 * @author     Jidaikobo Inc.
 * @license    The MIT License (MIT)
 * @copyright  Jidaikobo Inc.
 * @link       http://www.jidaikobo.com
 */
namespace Miso;

abstract class Controller
{
	protected static $properties = array();

	/**
	 * actionIndex
	 *
	 * @return Void
	 */
	abstract public static function actionIndex();

	/**
	 * properties
	 *
	 * @return Array
	 */
	final public static function properties()
	{
		// throw error
		if (empty(static::$properties) || ! is_array(static::$properties)) throw new \Exception(__('Fatal Error: Controller class must implement protected static::$properties', 'miso'));

		$properties = static::$properties;
		$position = array();
		foreach ($properties as $key => $row)
		{
			$pos = intval(Arr::get($row, 'position'));
			if ( ! isset($properties[$key])) continue;
			Arr::set($properties[$key], 'position', $pos);
			$position[$key] = $pos;
		}
		if ($position)
		{
			array_multisort($position, SORT_ASC, $properties);
		}
		return $properties;
	}

	/**
	 * indexes
	 *
	 * @return Array
	 */
	private static function indexes()
	{
		$indexes = array();
		$properties = static::properties();

		if (isset($properties['index']))
		{
			$indexes[] = array(
				'controller' => get_called_class(),
				'action'     => 'index',
				'str'        => __('All'),
				'num'        => Arr::get($properties['index'], 'num', false),
			);
		}

		foreach ($properties as $k => $v)
		{
			if ( ! Arr::get($v, 'is_index', false)) continue;
			$indexes[] = array(
				'controller' => get_called_class(),
				'action'     => $k,
				'str'        => __(Arr::get($v, 'menu_title'), 'miso'),
				'num'        => Arr::get($v, 'num', false),
			);
		}

		return $indexes;
	}

	/**
	 * base
	 *
	 * @return Void
	 */
	public static function base()
	{
		// routing
		list($type, $action, $method) = static::routing();
		if ( ! $action) die();
		// execute
		$body = static::$method();

		//Session::add('messages', 'errors', 'hoge');
// nonce強制
// method が post

		$indexes = self::indexes();

		// draw
		if ($body)
		{
			// show page
			$properties = static::properties();
			$properties = $properties[$action];

			// echo h1
			$html = '';
			$html.= '<div class="wrap">';
			$html.= '<div id="icon-themes" class="icon32"><br /></div>';
			$html.= '<h1>'.__($properties['page_title'], "miso").'</h1>';
			$html.= '<div class="inside">';
			echo $html;

			// echo message
			require (dirname(__DIR__).'/views/messages.php');

			// echo other actions?
			if ($indexes)
			{
				$links = array();
				foreach ($indexes as $index)
				{
					$str = __(__(esc_html($index['str'])), 'miso');
					$action = esc_html($index['action']);
					$count = $index['num'] ? ' <span class="count">('.$index['num'].')</span>' : '';
					$link = Miso::getMisoUrl($index['controller'], $action);
					$current = Miso::isCurrent($action) ? ' class="current"' : '' ;
					$links[] = '<li class="'.$action.'"><a href="'.$link.'"'.$current.'>'.$str.$count.'</a></li>';
				}

				$html = '<ul class="subsubsub">'.join(' | ', $links).'</ul><br class="clear" />';
				echo $html;
			}

			// echo body
			echo $body;

			$html = '';
			$html.= '</div><!--/.inside-->';
			$html.= '</div><!--/.wrap-->';
			echo $html;
		}
	}

	/**
	 * routing
	 *
	 * @return Array
	 */
	public static function routing()
	{
		$action = Input::param('action');

		// actionIndex
		if ( ! $action)
		{
			return array('action', 'index', 'actionIndex');
		}

		// other action
		// post takes priority other action
		$baseaction = 'action';
		if (
			Input::server('REQUEST_METHOD') == 'POST' &&
			method_exists(get_called_class(), 'post'.ucfirst($action))
		)
		{
			$baseaction = 'post';
		}
		$action_name = $baseaction.ucfirst($action);

		// check capability
		if (method_exists(get_called_class(), $action_name))
		{
			// check capability
			$properties = static::properties();
			$cu = wp_get_current_user();
			if (isset($properties[$action]['capability']))
			{
				if ( ! $cu->has_cap($properties[$action]['capability']))
				{
					return array(false, false, false);
				}
			}
			elseif ( ! $cu->has_cap($properties['index']['capability']))
			{
				return array(false, false, false);
			}

			// return function
			return array($baseaction, $action, $action_name);
		}
		return array(false, false, false);
	}

	/**
	 * url
	 *
	 * @param  String $action
	 * @return Void
	 */
	public static function url($action)
	{
		return Miso::getMisoUrl(get_called_class(), $action);
	}

	/**
	 * redirect
	 *
	 * @param  String $action
	 * @return Void
	 */
	protected static function redirect($action)
	{
		Miso::redirect(get_called_class(), $action);
	}
}
