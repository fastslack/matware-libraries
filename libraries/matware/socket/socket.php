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
defined('JPATH_PLATFORM') or die;

/**
 * Sockets class.
 *
 * This class allows for simple but smart objects with get and set methods
 * and an internal error handler.
 *
 * Based on PHP Socket Programming Tutorial - http://michaelcamden.me/?p=36
 * Copyright (C) 2011 Michael Camden
 * See http://michaelcamden.me/ for more information
 *
 * @package     Matware.Libraries
 * @subpackage  Base
 * @since       11.1
 */
class MSocket
{
	/**
	* Domain type to use when creating the socket
	* @var int
	*/
	public $domain = AF_INET;
	/**
	* The stream type to use when creating the socket
	* @var int
	*/
	public $type = SOCK_STREAM;
	/**
	* The protocol to use when creating the socket
	* @var int
	*/
	public $protocol = SOL_TCP;

	/**
	* Stores a reference to the created socket
	* @var Resource
	*/
	public $link = null;
	/**
	* Array of connected children 
	* @var array
	*/
	public $_sockets = array();
	/**
	* Bool which determines if the socket is listening or not
	* @var boolean
	*/
	private $listening = false;


	/**
	* Creates a new Socket.
	*
	* @param array $args
	* @param int $args[domain] AF_INET|AF_INET6|AF_UNIX
	* @param int $args[type] SOCK_STREAM|SOCK_DGRAM|SOCK_SEQPACKET|SOCK_RAW|SOCK_UDM
	* @param int $args[protocol] SOL_TCP|SOL_UDP
	* @return Socket
	*/
	protected function __construct(array $args = null) {

		// Default socket info
		$defaults = array(
			"domain" => AF_INET,
			"type" => SOCK_STREAM,
			"protocol" => SOL_TCP
		);
		if($args == null) {
			$args = array();
		}
		// Merge $args in to $defaults
		$args = array_merge($defaults, $args);

		// Store these values for later, just in case
		$this->domain = $args['domain'];
		$this->type = $args['type'];
		$this->protocol = $args['protocol'];

	}

	/**
	 * Creates and returns the socket on which the server will listen
	 * $address is the address at which the server is listening
	 * $port is the port at which the server is listening
	 */
	protected function getSocket()
	{
		if(($this->link = socket_create($this->domain, $this->type, $this->protocol)) === false) {
			throw new JException("Unable to create Socket. PHP said, " . $this->getLastError(), socket_last_error());
		}
	}

	/**
	* At destruct, close the socket
	*/
	public function __destruct() {
		@$this->close();
	}
	/**
	* Closes the listening socket
	* 
	* @return void
	*/
	public function close() {
		$this->listening = false;

		// @see http://www.php.net/manual/en/function.socket-close.php#66810
		$socketOptions = array('l_onoff' => 1, 'l_linger' => 0);
		socket_set_option($this->link, SOL_SOCKET, SO_LINGER, $socketOptions);

		socket_close($this->link);
	}
	/**
	* Terminates all active child connections
	*
	* @return void;
	*/
	public function killAll() {
		foreach($this->threads as $child) {
			$child->close();
		}
		$this->listening = false;
		$this->close();
	}
	/**
	* Returns the last error on the socket specified. If no socket is specified
	* the last error that occured is returned.
	* 
	* @param Resource $socket 
	* @return string
	*/
	public function getLastError($socket = null) {
		if(empty($socket)) {
			return socket_strerror(socket_last_error());
		}
		else {
			return socket_strerror(socket_last_error($socket));
		}
	}
}
