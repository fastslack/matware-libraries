<?php
/**
 * @version       $Id: 
 * @package       Matware.Libraries
 * @subpackage    MSocket
 * @copyright     Copyright (C) 2004 - 2014 Matware - All rights reserved.
 * @author        Matias Aguirre
 * @email         maguirre@matware.com.ar
 * @link          http://www.matware.com.ar/
 * @license       GNU/GPL http://www.gnu.org/licenses/gpl-2.0-standalone.html
 */
// No direct access
defined('_JEXEC') or die;

/**
 * A class to implement version 75 of the handshake
 */
class MWebsocketHandshake75 implements MWebsocketHandshake
{
	/**
	 * Perform the handshake
	 * $user - The user/client that requests the websocket connection
	 * $headers - an array containing the HTTP headers sent
	 */
	function doHandshake(MWebsocketUser $user, $headers)
	{
		$origin = $headers['Origin'];
		$host = $headers['Host'];
		$status = $headers['status'];
		$statusFields = explode(' ', $status);
		$resource = $statusFields[1];

		$upgrade = "HTTP/1.1 101 WebSocket Protocol Handshake\r\n" . "Upgrade: WebSocket\r\n" . "Connection: Upgrade\r\n"
			. "Sec-WebSocket-Protocol: " . $app . "\r\n" . "Sec-WebSocket-Origin: " . $origin . "\r\n" . "Sec-WebSocket-Location: ws://" . $host
			. $statusFields[1] . "\r\n" . "\r\n" . "\r\n";

		socket_write($user->socket(), $upgrade, strlen($upgrade));
		$user->setHandshakeDone();
		$user->setProtocol(new MWebsocketProtocol76);

		return true;
	}
}
