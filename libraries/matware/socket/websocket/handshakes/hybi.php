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
 * Performs the HyBi handshake
 */
class MWebsocketHandshakeHyBi implements MWebsocketHandshake
{

	/**
	 * Perform the HyBi Handshake operation
	 *
	 * $user - The user/client that is trying to establish connection
	 * $headers - An array of headers sent in the Websocket connect request
	 */
	function doHandshake(MWebsocketUser $user, $headers)
	{
		// Get the key sent from the client
		$strkey1 = $headers['Sec-WebSocket-Key'];
		// Append the Magic ID
		$keyPlusMagic = $strkey1 . "258EAFA5-E914-47DA-95CA-C5AB0DC85B11";

		// Get the raw sha1 then encode it
		$shaAcceptKey = sha1($keyPlusMagic, true);
		$socketAccept = base64_encode($shaAcceptKey);

		// Grab the rest of the headers needed
		if (isset($headers['Origin']))
			$origin = $headers['Origin'];
		if (isset($headers["Sec-WebSocket-Origin"]))
			$origin = $headers["Sec-WebSocket-Origin"];
		$host = $headers['Host'];
		$status = $headers['status'];
		$statusFields = explode(' ', $status);
		$resource = $statusFields[1];

		if (isset($headers['Sec-WebSocket-Extensions']))
		{
			$exts = explode(',', $headers['Sec-WebSocket-Extensions']);
		}

		// Now create the upgrade response
		$upgrade = "HTTP/1.1 101 Switching Protocols\r\n" . "Upgrade: WebSocket\r\n" . "Connection: Upgrade\r\n" . "Sec-WebSocket-Version: 8\r\n";
		if (isset($origin))
		{
			$upgrade .= "Sec-WebSocket-Origin: " . $origin . "\r\n";
		}
		if (isset($headers['Sec-WebSocket-Protocol']))
		{
			$upgrade = $upgrade . "Sec-WebSocket-Protocol: " . $app . "\r\n";
		}

		if (isset($headers['Sec-WebSocket-Extensions']))
		{
			//@TODO - need to process the Extensions and figure out what is supported
			//$upgrade = $upgrade."Sec-WebSocket-Extensions: ". $exts[0] . "\r\n";
		}
		$upgrade = $upgrade . "Sec-WebSocket-Accept: " . $socketAccept . "\r\n" . "\r\n";

		//socket_write($user->socket(),$upgrade.chr(0),strlen($upgrade.chr(0)));
		socket_write($user->socket(), $upgrade, strlen($upgrade));
		$user->setHandshakeDone();
		$user->setProtocol(new MWebsocketProtocolHyBi());
		return true;
	}
}
