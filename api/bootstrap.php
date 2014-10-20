<?php
/**
 * @package     Joomla.Services
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/*
 * If you get 404's when requesting pages in the API then you probably
 * need to add the following lines to your .htaccess file.
 *
 * # If the requested path and file is not /index.php and the request
 * # has not already been internally rewritten to the index.php script
 * RewriteCond %{REQUEST_URI} !^/index\.php
 * # and the request is for something within the api folder
 * RewriteCond %{REQUEST_URI} /api/ [NC]
 * # and the requested path and file doesn't directly match a physical file
 * RewriteCond %{REQUEST_FILENAME} !-f
 * # and the requested path and file doesn't directly match a physical folder
 * RewriteCond %{REQUEST_FILENAME} !-d
 * # internally rewrite the request to the API index.php script
 * RewriteRule .* api/index.php [L]
 * #
 */
error_reporting(-1);
ini_set('display_errors', 1);

// Define the application home directory.
$JAPIHOME = getenv('JAPI_HOME') ? getenv('JAPI_HOME') : dirname(__DIR__);

// Look for the Joomla Platform.
$JPLATFORMHOME = getenv('JPLATFORM_HOME') ? getenv('JPLATFORM_HOME') : dirname(__DIR__) . '/libraries';

// Fire up the Platform importer.
if (file_exists($JPLATFORMHOME . '/import.php'))
{
	require $JPLATFORMHOME . '/import.php';
}

// Ensure that required path constants are defined.
if (!defined('JPATH_SITE'))
{
	define('JPATH_SITE', $JAPIHOME);
}
if (!defined('JPATH_ADMINISTRATOR'))
{
	define('JPATH_ADMINISTRATOR', $JAPIHOME . '/administrator');
}
if (!defined('JPATH_CACHE'))
{
	define('JPATH_CACHE', '/tmp/cache');
}
if (!defined('JPATH_CONFIGURATION'))
{
	define('JPATH_CONFIGURATION', $JAPIHOME . '/etc');
}
if (!defined('JPATH_API'))
{
	define('JPATH_API', $JAPIHOME . '/api');
}
if (!defined('JPATH_PLUGINS'))
{
	define('JPATH_PLUGINS', $JAPIHOME . '/plugins');
}
