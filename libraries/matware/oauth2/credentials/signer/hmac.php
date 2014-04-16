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
 * OAuth HMAC-SHA1 Signature Method class for the Matware.Libraries.
 *
 * @package     Matware.Libraries
 * @subpackage  OAuth2
 * @since       1.0
 */
class MOauth2CredentialsSignerHMAC implements MOauth2CredentialsSigner
{
	/**
	 * Calculate and return the OAuth message signature using HMAC-SHA1
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
		// Build the key for hashing the base string.
		$key = $clientSecret . '&' . $credentialSecret;

		// Generate the binary hash of the based string and key.
		$hmac = hash_hmac('sha1', $baseString, $key, true);

		return base64_encode($hmac);
	}
}
