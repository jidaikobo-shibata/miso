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
		// load mvc first
		foreach (array('model', 'view', 'controller') as $dir)
		{
			$type = $dir.'s';

			foreach (glob(get_stylesheet_directory()."/miso/".$dir."/*.php") as $filename)
			{
				include($filename);
				static::${$type}[] = self::path2classname($dir, $filename);
			}

			// 子テーマを使っているなら、親テーマを読む
			if (get_stylesheet_directory() != get_template_directory())
			{
				foreach (glob(get_template_directory()."/miso/".$dir."/*.php") as $filename)
				{
					$classname = self::path2classname($dir, $filename);
					if (in_array($classname, static::${$type})) continue;
					include($filename);
					static::${$type}[] = self::path2classname($dir, $filename);
				}
			}
		}

		// load language if exists
		foreach (glob(get_stylesheet_directory()."/miso/languages/*") as $filename)
		{
			load_plugin_textdomain('miso', FALSE, dirname($filename));
			break;
		}
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
	 * path2classname
	 *
	 * @param  String $type
	 * @param  String $path
	 * @return String
	 */
	private static function path2classname($type, $path)
	{
		return '\\Miso\\'.ucfirst($type).'_'.ucfirst(substr(basename($path), 0, -4));
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
}
