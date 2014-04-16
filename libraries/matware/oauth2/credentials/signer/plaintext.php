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
 * OAuth PLAINTEXT Signature Method class for the Matware.Libraries.
 *
 * @package     Matware.Libraries
 * @subpackage  OAuth2
 * @since       1.0
 */
class MOauth2CredentialsSignerPlaintext extends MOauth2CredentialsSigner
{
	/**
	 * Calculate and return the OAuth message signature using PLAINTEXT
	 *
	 * @param   string  $baseString        The OAuth message as a normalized base string.
	 * @param   string  $clientSecret      The OAuth client's secret.
	 * @param   string  $credentialSecret  The OAuth credentials' secret.
	 *
	 * @return  string  The OAuth message signature.
	 *
	 * @since   1.0
	 * @throws  InvalidArgumentException
	 */
	public function sign($baseString, $clientSecret, $credentialSecret)
	{
		return $clientSecret . '&' . $credentialSecret;
	}

	/**
	 * Decode the client secret key
	 *
	 * @param   string  $clientSecret  The OAuth client's secret.
	 *
	 * @return  string  The decoded key
	 *
	 * @since   1.0
	 * @throws  InvalidArgumentException
	 */
	public function secretDecode($clientSecret)
	{
		$clientSecret = explode(":", base64_decode($clientSecret));
		$clientSecret = $clientSecret[1];

		return $clientSecret;
	}
}
