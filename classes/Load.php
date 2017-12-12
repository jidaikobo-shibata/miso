<?php
/**
 * Miso\Load
 *
 * @author     Jidaikobo Inc.
 * @license    The MIT License (MIT)
 * @copyright  Jidaikobo Inc.
 * @link       http://www.jidaikobo.com
 */
namespace Miso;

class Load
{
	/**
	 * setPageTitle
	 *
	 * @return Void
	 */
	public static function setPageTitle()
	{
		$slug = Input::get('page');
		$classname = Miso::slug2classname($slug);
		if ( ! class_exists($classname)) return;

		$action = Input::get('action');
		if ( ! method_exists($classname, 'action'.ucfirst($action))) return;

		$properties = $classname::properties();
		if (
			! isset($properties->$action['page_title']) ||
			empty($properties->$action['page_title'])
		) return;
		$properties = $properties->$action;

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
		$controllers = Miso::get('controllers');
		foreach ($controllers as $controller)
		{
			// left admin menu
			add_action(
				'admin_menu',
				function () use ($controller)
				{
					$properties = $controller::properties();

					// default
					// the others of below should call error
					$icon_url = isset($properties->index['icon_url']) && $properties->index['icon_url'] ?
										$properties->index['icon_url']:
										'';
					$position = isset($properties->index['position']) && $properties->index['position'] ?
										intval($properties->index['position']):
										25;

					// add_menu_page
					add_menu_page(
						__($properties->index['page_title'], 'miso'), // page title
						__($properties->index['menu_title'], 'miso'), // menu title
						$properties->index['capability'], // capability
						\Miso\Miso::classname2slug($controller), // slug
						array($controller, 'base'), // function
						$icon_url, // icon_url
						$position // position
					);
				}
			);
		}

		// if actionCreate exists add sub_menu
	}
}
