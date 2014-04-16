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

interface MWebsocketController
{
	/**
	 * Set the set the Websocket protocol class
	 * over which this application is being called
	 * This is needed for the application to send data back
	 * to the client
	 */
	function setProtocol(MWebsocketProtocol $protocol);

	/**
	 * Called whenever there is a message from a client
	 * $msg is an array with the following structure
	 *  Array {
	 * 		'size': The size in bytes of the data frame
	 * 		'frame': A buffer containing the data received
	 * 		'binary': boolean indicator true is frame is binary false if frame is utf8
	 *  }
	 */
	function onMessage($msg);

	/**
	 * Called when the client closes connection
	 */
	function onClose();

	/**
	 * Called in the event of an error.
	 */
	function onError($err);
}
