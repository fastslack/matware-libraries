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

jimport('matware.socket.socket');

/**
 * MSocketDaemon Class
 *
 * @package     Matware.Libraries
 * @subpackage  MSocket
 * @since       1.0
 */
class MSocketDaemon extends MSocket
{
	/**
	* 
	* @var array
	*/
	public $_users = array();

	protected function __construct()
	{
		parent::__construct();
	}

	/**
	 * Creates and returns the socket on which the server will listen
	 * $address is the address at which the server is listening
	 * $port is the port at which the server is listening
	 */
	protected function getSocket()
	{
		// Call parent getSocket
		parent::getSocket();

		if (!socket_set_option($this->link, SOL_SOCKET, SO_REUSEADDR, 1))
		{
			JLog::add('socket_option() failed', JLog::ERROR, 'MSocketDaemon');
			throw new RuntimeException('socket_option() failed');
		}

		// Bind to the host/port
		if (!socket_bind($this->link, $this->_controller->address, $this->_controller->port))
		{
			JLog::add('socket_bind() failed', JLog::ERROR, 'MSocketDaemon');
			throw new RuntimeException('socket_bind() failed');
		}

		// Try to listen
		if (!socket_listen($this->link, 20))
		{
			JLog::add('socket_listen() failed', JLog::ERROR, 'MSocketDaemon');
			throw new RuntimeException('socket_listen() failed');
		}

		// Logging
		JLog::add('> Daemon started : ' . date('Y-m-d H:i:s'), JLog::INFO, 'MSocketDaemon');
		JLog::add('> Master socket  : ' . $this->link, JLog::INFO, 'MSocketDaemon');
		JLog::add('> Listening on   : ' . $this->_controller->address . ' port ' . $this->_controller->port, JLog::INFO, 'MSocketDaemon');
	}
} // end class
