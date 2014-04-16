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

class MWebsocketUser
{
	private $id;
	private $socket;
	private $handshake = false;
	private $transcoder;
	private $protocol;

	/**
	 * Class Constructor for the WsUser Object
	 *
	 */
	public function __construct()
	{
		$this->id = uniqid();
	}

	function id()
	{
		return $this->id;
	}

	function setSocket($socket)
	{
		$this->socket = $socket;
	}

	function socket()
	{
		return $this->socket;
	}

	function setHandshakeDone()
	{
		$this->handshake = true;
	}

	function handshakeDone()
	{
		return $this->handshake;
	}

	function setTranscoder(MessageTranscoder $transcoder)
	{
		$this->transcoder = $transcoder;
	}

	function transcoder()
	{
		return $this->transcoder;
	}

	function setProtocol(MWebsocketProtocol $protocol)
	{
		$this->protocol = $protocol;
	}

	function protocol()
	{
		return $this->protocol;
	}
}
