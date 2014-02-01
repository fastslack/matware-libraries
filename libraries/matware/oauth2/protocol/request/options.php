<?php
/**
 * @package     Matware.Libraries
 * @subpackage  OAuth2
 *
 * @copyright   Copyright (C) 2004 - 2014 Matware. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die( 'Restricted access' );

/**
 * MOauth2ProtocolRequestOptions class
 *
 * @package  Matware.Libraries
 * @since    1.0
 */
class MOauth2ProtocolRequestOptions
{
	/**
	 * Object constructor.
	 *
	 * @since   1.0
	 */
	public function __construct()
	{
		$this->_app = JFactory::getApplication();

		// Setup the database object.
		$this->_input = $this->_app->input;
	}

	/**
	 * Parse the request OPTIONS variables for OAuth parameters.
	 *
	 * @return  mixed  Array of OAuth 2.0 parameters if found or boolean false otherwise.
	 *
	 * @since   1.0
	 */
	public function processVars()
	{
		// Get a JURI instance for the request URL.
		$uri = new JURI($this->_app->get('uri.request'));

		// Initialise params array.
		$params = array();

		// Iterate over the reserved parameters and look for them in the POST variables.
		foreach (MOauth2ProtocolRequest::getReservedParameters() as $k)
		{
			if ($this->_input->get->getString('oauth_' . $k, false))
			{
				$params['OAUTH_' . strtoupper($k)] = trim($this->_input->get->getString('oauth_' . $k));
			}
		}

		// Make sure that any found oauth_signature is not included.
		unset($params['signature']);

		// Ensure the parameters are in order by key.
		ksort($params);

		return $params;
	}
}
