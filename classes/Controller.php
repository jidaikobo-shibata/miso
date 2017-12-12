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
	/**
	 * properties
	 *
	 * @return Array
	 */
	abstract public static function properties();

	/**
	 * actionIndex
	 *
	 * @return Void
	 */
	abstract public static function actionIndex();

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

		// Session::add('messages', 'errors', 'hoge');

		// draw
		if ($body)
		{
			// show page
			$properties = static::properties();
			$properties = $properties->$action;

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
	 * @return Array|Bool
	 */
	public static function routing()
	{
		$action = Input::param('action');

		// actionIndex
		if ( ! $action)
		{
			return array('action', 'index', 'actionIndex');
		}
		else
		{
			// other action
			// post and get takes priority other action
			foreach (array('post', 'get', 'action') as $baseaction)
			{
				if (in_array($baseaction, array('post', 'get')))
				{
					$action = Input::$baseaction('action');
				}
				$action_name = $baseaction.ucfirst($action);
				if (method_exists(get_called_class(), $action_name))
				{
					// check capability
					$properties = static::properties();
					$cu = wp_get_current_user();
					if (isset($properties->$action['capability']))
					{
						if ( ! $cu->has_cap($properties->$action['capability'])) return false;
					}
					elseif ( ! $cu->has_cap($properties->index['capability']))
					{
						return false;
					}

					// return function
					return array($baseaction, $action, $action_name);
				}
			}
		}
		return false;
	}
}
