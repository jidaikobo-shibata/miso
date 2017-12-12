<?php
/**
 * Miso\Util
 *
 * @author     Jidaikobo Inc.
 * @license    The MIT License (MIT)
 * @copyright  Jidaikobo Inc.
 * @link       http://www.jidaikobo.com
 */
namespace Miso;

class Util
{
	/**
	 * add autoloader path
	 *
	 * @param  String $path
	 * @param  String $namespace
	 * @return Void
	 */
	public static function addAutoloaderPath($path, $namespace = '')
	{
		spl_autoload_register(
			function ($class_name) use ($path, $namespace)
			{
				// check namespace
				$class = $class_name;
				$strlen = strlen($namespace);

				if (substr($class, 0, $strlen) !== $namespace) return;
				$class = substr($class, $strlen + 1);

				// underscores are directories
				$path.= str_replace('\\', '/', $class);

				// require
				$file_path = $path.'.php';
				if (file_exists($file_path))
				{
					require $path.'.php';
				}
				else
				{
					return false;
				}

				// init
				if (method_exists($class_name, '_init') and is_callable($class_name.'::_init'))
				{
					call_user_func($class_name.'::_init');
				}
			}
		);
	}

	/**
	 * get current uri
	 *
	 * @return String
	 */
	public static function uri()
	{
		$uri = static::is_ssl() ? 'https' : 'http';
		$uri.= '://'.$_SERVER["HTTP_HOST"].rtrim($_SERVER["REQUEST_URI"], '/');
		return static::s($uri);
	}

	/**
	 * get root relative
	 *
	 * @return String
	 */
	public static function rootRelative()
	{
		$host = $_SERVER["HTTP_HOST"];
		$offset = strpos(home_url(), $host) + strlen($host);
		return substr(home_url(), 0, $offset);
	}

	/**
	 * add query strings
	 * this medhod doesn't apply sanitizing
	 *
	 * @param  String $uri
	 * @param  Array  $query_strings array(array('key', 'val'),...)
	 * @return String
	 */
	public static function addQueryStrings($uri, $query_strings = array())
	{
		$delimiter = strpos($uri, '?') !== false ? '&amp;' : '?';
		$qs = array();
		foreach ($query_strings as $v)
		{
			// if (is_array($v))
			$qs[] = $v[0].'='.$v[1];
		}
		return $uri.$delimiter.join('&amp;', $qs);
	}

	/**
	 * remove query strings
	 *
	 * @param  String $uri
	 * @param  Array  $query_strings array('key',....)
	 * @return String
	 */
	public static function removeQueryStrings($uri, $query_strings = array())
	{
		if (strpos($uri, '?') !== false)
		{
			// all query strings
			$query_strings = $query_strings ?: array_keys($_GET);

			// replace
			$uri = str_replace('&amp;', '&', $uri);
			$pos = strpos($uri, '?');
			$base_url = substr($uri, 0, $pos);
			$qs = explode('&', substr($uri, $pos + 1));
			foreach ($qs as $k => $v)
			{
				foreach ($query_strings as $vv)
				{
					if (substr($v, 0, strpos($v, '=')) == $vv)
					{
						unset($qs[$k]);
					}
				}
			}
			$uri = $qs ? $base_url.'?'.join('&amp;', $qs) : $base_url;
		}
		return $uri;
	}

	/**
	 * is ssl
	 *
	 * @return Bool
	 */
	public static function isSsl()
	{
		return isset($_SERVER['HTTP_X_SAKURA_FORWARDED_FOR']) ||
			(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']);
	}

	/**
	 * sanitize html
	 *
	 * @param  String|Array $str
	 * @return String|Array
	 */
	public static function s($str)
	{
		if (is_array($str)) return array_map(array('\\Miso\\Util', 's'), $str);
		return htmlentities($str, ENT_QUOTES, 'UTF-8', false);
	}

	/**
	 * truncate
	 *
	 * @param  String  $str
	 * @param  Integer $len
	 * @param  String  $lead
	 * @return String
	 */
	public static function truncate($str, $len, $lead = '...')
	{
		$target_len = mb_strlen($str);
		return $target_len > $len ? mb_substr($str, 0, $len).$lead : $str;
	}

	/**
	 * error
	 *
	 * @param  String $message
	 * @return Void
	 */
	public static function error($message = '')
	{
		if ( ! headers_sent())
		{
			header('Content-Type: text/plain; charset=UTF-8', true, 403);
		}
		die(Util::s($message));
	}
}
