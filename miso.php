<?php
/*
Plugin Name: Miso
Plugin URI: https://wordpress.org/plugins/miso/
Description: MVC Framework which coexist with WordPress role system and routing.
Author: Jidaikobo Inc.
Text Domain: miso
Domain Path: /languages/
Version: 0.1
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

// session
add_action('init', array('\\Miso\\Session', 'forge'), 10, 0);

// Prepare
\Miso\Miso::prepare();

// Routing (loading)
\Miso\Load::setPageTitle();
\Miso\Load::controller();
