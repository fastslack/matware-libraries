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

class MWebsocketProtocol76 implements MWebsocketProtocol
{
	private $socket;
	private $state = 0;
	private $frame = "";
	private $framelength = 0;

	function setSocket($socket)
	{
		$this->socket = $socket;
	}

	function read()
	{
		$messageLength = 0;
		$out = array();

		$bytes = @socket_recv($this->socket, $buffer, 2048, 0);

		if ($bytes == 0)
		{
			throw new UnexpectedValueException('Request empty');
		}

		for ($i = 0, $l = $bytes; $i < $l; $i++)
		{
			$b = $buffer[$i];
			if ($this->state === 0)
			{
				if (ord($b & 0x80) === 128)
				{
					$this->state = 2;
				}
				else
				{
					$this->state = 1;
				}
			}
			else if ($this->state === 1)
			{
				if (ord($b) === 255)
				{
					$this->state = 0;
					$out['size'] = $this->framelength;
					$out['binary'] = false;
					$out['frame'] = $this->frame;
					$this->framelength = 0;
					$this->frame = "";
					return $out;
				}
				else
				{
					$this->frame .= $b;
					$this->framelength++;
				}

			}
			else if ($this->state === 2)
			{
				echo "Oops looks like I got a close\n";
				if (ord($b) === 0)
				{
					throw new RuntimeException('State == 2');
				}
			}
		}

		// Got a bad request so terminate it
		throw new RuntimeException('No return');
	}

	function send($data)
	{
		$rawBytesSend = $data['size'] + 2;
		$packet = pack('C', 0x00);

		if ($data['size'] !== 0)
		{
			for ($i = 0; $i < $data['size']; $i++)
			{
				$packet .= $data['frame'][$i];
			}
		}

		$packet .= pack('C', 0xff);
		$bytesSent = socket_write($this->socket, $packet, strlen($packet));

		echo "\nBytes sent = $bytesSent";

		return $bytesSent;
	}

	function close()
	{
		$data = array();
		$data['size'] = 0;
		$this->send($data);
	}
}
