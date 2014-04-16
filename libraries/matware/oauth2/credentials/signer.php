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

/**
 * OAuth message signer interface for the Matware.Libraries.
 *
 * @package     Matware.Libraries
 * @subpackage  OAuth2
 * @since       1.0
 */
class MOauth2CredentialsSigner
{
	/**
	 * Method to get a message signer object based on the message's oauth_signature_method parameter.
	 *
	 * @param   string  $method  The method of the signer (HMAC-SHA1 || RSA-SHA1 || PLAINTEXT)
	 *
	 * @return  MOauth2CredentialsSigner  The OAuth message signer object for the message.
	 *
	 * @since   1.0
	 * @throws  InvalidArgumentException
	 */
	public static function getInstance($method)
	{
		switch ($method)
		{
			case 'HMAC-SHA1':
				$signer = new MOauth2CredentialsSignerHMAC;
				break;
			case 'RSA-SHA1':
				// @TODO We don't support RSA because we don't yet have a way to inject the private key.
				throw new InvalidArgumentException('RSA signatures are not supported');
				break;
			case 'PLAINTEXT':
				$signer = new MOauth2CredentialsSignerPlaintext;
				break;
			default:
				throw new InvalidArgumentException('No valid signature method was found.');
				break;
		}

		return $signer;
	}

	/**
	 * Perform a password authentication challenge.
	 *
	 * @param   MOauth2Client  $client   The client object
	 * @param   string         $request  The request object.
	 *
	 * @return  boolean  True if authentication is ok, false if not
	 *
	 * @since   1.0
	 */
	public function doJoomlaAuthentication(MOauth2Client $client, $request)
	{
		// Build the response for the client.
		$types = array('PHP_AUTH_', 'PHP_HTTP_', 'PHP_');

		foreach ( $types as $type )
		{
			if (isset($request->_headers[$type . 'USER']))
			{
				$user_decode = base64_decode($request->_headers[$type . 'USER']);
			}

			if (isset($request->_headers[$type . 'PW']))
			{
				$password_decode = base64_decode($request->_headers[$type . 'PW']);
			}
		}

		// Check if the username and password are present
		if ( !isset($user_decode) || !isset($password_decode) )
		{
			if (isset($request->client_id))
			{
				$user_decode = explode(":", base64_decode($request->client_id));
				$user_decode = $user_decode[0];
			}

			if (isset($request->client_secret))
			{
				$password_decode = explode(":", base64_decode($request->client_secret));
				$password_decode = base64_decode($password_decode[1]);
				$password_decode = explode(":", $password_decode);
				$password_decode = $password_decode[0];
			}
		}

		// Check if the username and password are present
		if (!isset($user_decode) || !isset($password_decode))
		{
			throw new Exception('Username or password is not set');
			exit;
		}

		// Verify the password
		$match = JUserHelper::verifyPassword($password_decode, $client->_identity->password, $client->_identity->id);

		return $match;
	}
}
