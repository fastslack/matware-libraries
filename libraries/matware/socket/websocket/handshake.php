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
