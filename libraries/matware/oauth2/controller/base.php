<?php
/**
 * @version       $Id: 
 * @package       Matware.Libraries
 * @subpackage    OAuth2
 * @copyright     Copyright (C) 2004 - 2014 Matware - All rights reserved.
 * @author        Matias Aguirre
 * @email         maguirre@matware.com.ar
 * @link          http://www.matware.com.ar/
 * @license       GNU/GPL http://www.gnu.org/licenses/gpl-2.0-standalone.html
 */
defined('JPATH_PLATFORM') or die;

JLoader::register('MOauth2Client', JPATH_LIBRARIES.'/matware/oauth2/client.php');

/**
 * OAuth Controller class for initiating temporary credentials for the Matware.Libraries.
 *
 * @package     Matware.Libraries
 * @subpackage  OAuth2
 * @since       1.0
 */
class MOauth2ControllerBase extends JControllerBase
{
	/**
	 * Method required by JControllerBase
	 *
	 * @return  none
	 *
	 * @since   1.0
	 */
	public function execute()
	{
	}

	/**
	 * Initialise the controller
	 *
	 * @return  none
	 *
	 * @since   1.0
	 */
	protected function initialise()
	{
		// Verify that we have an OAuth 2.0 application.
		if ((!$this->app instanceof ApiApplicationWeb))
		{
			$this->respondError(400, 'invalid_request', 'Cannot perform OAuth 2.0 authorisation without an OAuth 2.0 application.');
		}

		// We need a valid signature to do initialisation.
		if (!isset($this->request->access_token) && (!$this->request->client_id || !$this->request->client_secret || !$this->request->signature_method) )
		{
			$this->respondError(400, 'invalid_request', 'Invalid OAuth request signature.');

			return false;
		}
	}

	/**
	 * Get an OAuth 2.0 client object based on the request message.
	 *
	 * @param   string  $client_id  The OAuth 2.0 client_id parameter for which to load the client.
	 *
	 * @return  MOauth2Client
	 *
	 * @since   1.0
	 * @throws  InvalidArgumentException
	 */
	public function fetchClient($client_id)
	{
		$client_id = base64_decode($client_id);
		$client_id = explode(":", $client_id);
		$client_id = $client_id[0];

		// Ensure there is a consumer key.
		if (empty($client_id))
		{
			$this->respondError(400, 'unauthorized_client', 'There is no OAuth consumer key in the request.');
		}

		// Get an OAuth client object and load it using the incoming client key.
		$client = new MOauth2Client;
		$client->loadByKey($client_id);

		// Verify the client key for the message.
		if ($client->username != $client_id)
		{
			$this->respondError(400, 'unauthorized_client', 'The OAuth consumer key is not valid.');
		}

		return $client;
	}

	/**
	 * Return the JSON message for CORS or simple request.
	 *
	 * @param   string	$message	The return message
	 *
	 * @return  string	$body	    The message prepared if CORS is enabled, or same if false.
	 *
	 * @since   1.0
	 * @throws  InvalidArgumentException
	 */
	public function prepareBody($message)
	{
		$callback = $this->app->input->get->getString('callback', false);

		if ($callback !== false)
		{
			$body = $callback . "(".json_encode($message).")";
		}else{
			$body = json_encode($message);
		}

		return $body;
	}

	/**
	 * Return the JSON error based on RFC 6749 (http://tools.ietf.org/html/rfc6749#section-5.2)
	 *
	 * @param   int     $status   The HTTP protocol status. Default: 400 for errors
	 * @param   string  $code     The OAuth2 framework error code
	 * @param   string  $message  The error description
	 *
	 * @return  none
	 *
	 * @since   1.0
	 * @throws  InvalidArgumentException
	 */
	public function respondError($status, $code, $message)
	{
		$response = array(
			'error' => $code,
			'error_description' => $message
		);

		$this->response->setHeader('status', $status)
			->setBody(json_encode($response))
			->respond();

		exit;
	}
}
