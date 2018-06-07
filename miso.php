<?php
/*
Plugin Name: Miso
Plugin URI: https://wordpress.org/plugins/miso/
Description: light weight MVC Framework which coexist with WordPress role system and routing.
Author: Jidaikobo Inc.
Text Domain: miso
Domain Path: /languages/
Version: 0.2
Author URI: http://www.jidaikobo.com/
License: GPL2

Copyright 2017 jidaikobo (email : support@jidaikobo.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

// WP_INSTALLING
if (defined('WP_INSTALLING') && WP_INSTALLING) return;

// language
load_plugin_textdomain('miso', FALSE, plugin_basename(__DIR__).'/languages');

// Autoloader
include(__DIR__.'/classes/Util.php');
\Miso\Util::addAutoloaderPath(__DIR__.'/classes/', 'Miso');

// Idiorm and Paris
if ( ! class_exists('ORM'))        include(__DIR__.'/libs/idiorm/idiorm.php');
if ( ! class_exists('ORMWrapper')) include(__DIR__.'/libs/paris/paris.php');

// configure
\ORM::configure('mysql:host='.DB_HOST.';dbname='.DB_NAME);
\ORM::configure('username', DB_USER);
\ORM::configure('password', DB_PASSWORD);

// session
add_action('init', array('\\Miso\\Session', 'forge'), 10, 0);

// out buffer for in controller redirection
add_filter('after_setup_theme', array('\\Miso\\Miso', 'bufferStart'), 20);
add_filter('shutdown', array('\\Miso\\Miso', 'bufferOut'), 20);

// help
add_action(
	'admin_menu',
	function ()
	{
		add_options_page(
			__('Miso Framework', 'miso'),
			__('Miso Framework', 'miso'),
			'level_10',
			'miso_options',
			array('\\Miso\\Help', 'ussage')
		);
	});

// Prepare
\Miso\Miso::prepare();

// Routing (loading)
\Miso\Miso::setPageTitle();
\Miso\Miso::controller();