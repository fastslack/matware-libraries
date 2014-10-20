<?php
/**
 * @version       $Id: 
 * @package       Matware.Libraries
 * @subpackage    Bootstrap
 * @copyright     Copyright (C) 2004 - 2014 Matware - All rights reserved.
 * @author        Matias Aguirre
 * @email         maguirre@matware.com.ar
 * @link          http://www.matware.com.ar/
 * @license       GNU/GPL http://www.gnu.org/licenses/gpl-2.0-standalone.html
 */

defined('JPATH_PLATFORM') or die;

// Register the classes for autoload.
JLoader::registerPrefix('M', JPATH_LIBRARIES . '/matware');

// Import the MWebSocket class from Matware libraries.
JLoader::register('MSocketDaemon', JPATH_LIBRARIES.'/matware/socket/daemon.php');
JLoader::register('MWebsocket', JPATH_LIBRARIES.'/matware/socket/websocket.php');
JLoader::register('MWebsocketUser', JPATH_LIBRARIES.'/matware/socket/websocket/user.php');

// Interfaces.
JLoader::register('MWebsocketController', JPATH_LIBRARIES.'/matware/socket/websocket/controller.php');
JLoader::register('MWebsocketHandshake', JPATH_LIBRARIES.'/matware/socket/websocket/handshake.php');
JLoader::register('MWebsocketProtocol', JPATH_LIBRARIES.'/matware/socket/websocket/protocol.php');

// Adapters.
JLoader::discover('MWebsocketHandshake', JPATH_LIBRARIES.'/matware/socket/websocket/handshakes');
JLoader::discover('MWebsocketProtocol', JPATH_LIBRARIES.'/matware/socket/websocket/protocols');
