<?php
/**
 * @version       $Id: 
 * @package       Matware.Libraries
 * @subpackage    MSocket
 * @copyright     Copyright (C) 1996 - 2011 Matware - All rights reserved.
 * @author        Matias Aguirre
 * @email         maguirre@matware.com.ar
 * @link          http://www.matware.com.ar/
 * @license       GNU/GPL http://www.gnu.org/licenses/gpl-2.0-standalone.html
 */
// No direct access
defined('_JEXEC') or die;

/**
 * WebSocket implements the basic websocket protocol handling initial handshaking and also
 * dispatching requests up to the clients bound to the socket.
 */
class MWebsocket extends MSocketDaemon
{
	private static $_instance;

	public $_controller;

	protected function __construct(MWebsocketController $controller)
	{
		parent::__construct();

		$this->_controller = $controller;
	}

	public static function getInstance(MWebsocketController $controller)
	{
		if (!(self::$_instance instanceof MWebsocket))
		{
			self::$_instance = new MWebsocket($controller);
		}

		return self::$_instance;
	}

	protected function connect($socket)
	{
		$user = new MWebsocketUser;
		$user->setSocket($socket);
		array_push($this->_users, $user);
		array_push($this->_sockets, $socket);
	}

	public function disconnect($socket)
	{
		JLog::add('Disconnecting ' . $socket, JLog::INFO, 'WebSocket');

		$found = null;
		$n = count($this->_users);
		for ($i = 0; $i < $n; $i++)
		{
			if ($this->_users[$i]->socket() == $socket)
			{
				$found = $i;
				break;
			}
		}
		if (!is_null($found))
		{
			array_splice($this->_users, $found, 1);
		}

		$index = array_search($socket, $this->_sockets);

		socket_close($socket);

		if ($index >= 0)
		{
			array_splice($this->_sockets, $index, 1);
		}
	}

	public function start()
	{
		$this->getSocket($this->_controller->address, $this->_controller->port);

		$users = array();

		$this->_sockets = array(
			$this->link
		);

		// The main process
		while (true)
		{
			// Initialise vars
			$write = NULL;
			$except = NULL;

			$changed = $this->_sockets;
			socket_select($changed, $write, $except, NULL);

			foreach ($changed as $socket)
			{
				try
				{
					if ($socket == $this->link)
					{
						$client = socket_accept($this->link);
						if ($client < 0)
						{
							JLog::add('socket_accept() failed', JLog::ALERT, 'WebSocket');
							continue;
						}
						else
						{
							$this->connect($client);
							JLog::add($client . ' CONNECTED', JLog::INFO, 'WebSocket');
						}
					}
					else
					{
						JLog::add($client . ': Processing request', JLog::INFO, 'WebSocket');
						$user = $this->getUserBySocket($socket, $users);
						$this->handleRequest($socket, $user);
					}
				}
				catch (Exception $e)
				{
					JLog::add($socket.' disconnected', JLog::ALERT, 'WebSocket');
					echo "\n".$e->getMessage();
					$this->disconnect($socket);
				}
			}
		}
	}

	protected function doHandshake($socket, MWebsocketUser $user)
	{
		JLog::add($socket . ' performing handshake', JLog::INFO, 'WebSocket');
		$bytes = @socket_recv($socket, $buffer, 2048, 0);
		if ($bytes == 0)
		{
			MWebsocket::getInstance($this->_controller)->disconnect($socket);
			JLog::add($socket . ' DISCONNECTED!', JLog::INFO, 'WebSocket');
			return;
		}

		$headers = $this->parseHeaders($buffer);

		if (count($headers) == 0 || !isset($headers['Upgrade']))
		{
			// Not good send back an error status
			$this->sendFatalErrorResponse($user);
		}
		else
		{
			if (strtolower($headers['Upgrade']) != 'websocket')
			{
				$this->sendFatalErrorResponse($user);
			}
			// now get the handshaker for this request
			$hs = $this->getHandshake($headers);
			if (!$hs->dohandshake($user, $headers))
			{
				throw new RuntimeException('Handshake failed');
			}
			JLog::add($socket . ' Handshake Done', JLog::INFO, 'WebSocket');
		}
	}

	/**
	 * Looks at the headers to determine which handshaker to
	 * use
	 * $headers are the headers in the request
	 */
	private function getHandshake($headers)
	{
		// Lets check which handshaker we need
		if (isset($headers['Sec-WebSocket-Version']))
		{
			if ($headers['Sec-WebSocket-Version'] === '13')
			{
				// This is the HyBI handshaker
				return new MWebsocketHandshakeHyBi();
			}
			// Not a version we support
			$this->sendFatalErrorResponse();
		}
		else if (isset($headers['Sec-WebSocket-Key1']) && isset($headers['Sec-WebSocket-Key2']))
		{
			// Draft 76
			return new MWebsocketHandshake76();
		}
		// Must be draft 75

		return new MWebsocketHandshake75();
	}

	protected function getUserBySocket($socket)
	{
		$found = null;
		foreach ($this->_users as $user)
		{
			if ($user->socket() == $socket)
			{
				$found = $user;
				break;
			}
		}

		return $found;
	}

	/**
	 * Entry point for all client requests. This function
	 * determines if handshaking has been done and if not selects the
	 * specific handshaking protocol and invokes it.
	 *
	 * If handshaking has been done this function dispatches the request
	 * to the service bound to the request associated with the user object
	 */
	function handleRequest($socket, MWebsocketUser $user)
	{
		// Check the handshake required
		if (!$user->handshakeDone())
		{
			$this->doHandshake($socket, $user);
		}

		try
		{
			$protocol = $user->protocol();
			if (isset($protocol))
			{
				$protocol->setSocket($socket);
				$result = $protocol->read();
				$bytesRead = $result['size'];

				if ($bytesRead !== -1 && $bytesRead !== -2)
				{
					// Encode to JSON when we manage object, not needed in test
					//$message = json_encode($this->_controller->onMessage($result));
					$message = $this->_controller->onMessage($result);

					$binary = is_string($message) ? false : true;
					$protocol->send(array('size' => strlen($message), 'frame' => $message, 'binary' => $binary));
				}
				else
				{
					$this->_controller->onError('Error handling request!');
					// badness must close
					$protocol->close();
					$this->disconnect($socket);

					return;
				}
			}
			else
			{
				$this->sendFatalErrorResponse($user);
				return;
			}
		}
		catch (WSClientClosedException $e)
		{
			$this->_controller->onClose();
		}
	}


	protected function parseHeaders($headers = false)
	{
		if ($headers === false)
		{
			return false;
		}
		$statusDone = false;
		$headers = str_replace("\r", "", $headers);
		$headers = explode("\n", $headers);
		foreach ($headers as $value)
		{
			$header = explode(": ", $value);
			if (count($header) == 1)
			{
				// if($header[0] && !$header[1]){
				if (!$statusDone)
				{
					$headerdata['status'] = $header[0];
					$statusDone = true;
				}
				else
				{
					$headerdata['body'] = $header[0];
					//return $headerdata;
				}
			}
			elseif ($header[0] && $header[1])
			{
				$headerdata[$header[0]] = $header[1];
			}
		}

		return $headerdata;
	}

	/**
	 * Takes the appropriate action to close the connection down
	 */
	private function sendFatalErrorResponse(MWebsocketUser $user)
	{
		// Just close the socket if in handhake mode
		if (!$user->handshakeDone())
		{
			MWebsocket::getInstance($this->_controller)->disconnect($user->socket());
			return;
		}
		else
		{
			//send a status code and then close
		}
	}
}
