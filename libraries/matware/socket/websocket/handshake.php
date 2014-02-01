<?php
/**
 * @package     Matware.Libraries
 * @subpackage  WebSocket
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Abstract interface to be used by all Hanadshake implementations
 */
interface MWebsocketHandshake
{
	/**
	 * Perform a Websocket Handshake
	 *
	 * $user - the user object associated with this request.
	 * $headers - array of the headers sent by the user for purposes of performing the handshake
	 */
	function doHandshake(MWebsocketUser $user, $headers);
}
