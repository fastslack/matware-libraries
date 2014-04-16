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
defined('_JEXEC') or die( 'Restricted access' );

/**
 * MOauth2ProtocolRequestPost class
 *
 * @package  Matware.Libraries
 * @since    1.0
 */
class MOauth2ProtocolRequestPost
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
	 * Parse the request POST variables for OAuth parameters.
	 *
	 * @return  mixed  Array of OAuth 2.0 parameters if found or boolean false otherwise.
	 *
	 * @since   1.0
	 */
	public function processVars()
	{
		// If we aren't handling a post request with urlencoded vars then there is nothing to do.
		if (strtoupper($this->_input->getMethod()) != 'POST'
			|| !strpos($this->_input->server->get('CONTENT_TYPE', ''), 'x-www-form-urlencoded') )
		{
			return false;
		}

		// Initialise variables.
		$parameters = array();

		// Iterate over the reserved parameters and look for them in the POST variables.
		foreach (MOauth2ProtocolRequest::getReservedParameters() as $k)
		{
			if ($this->_input->post->getString('oauth_' . $k, false))
			{
				$parameters['OAUTH_' . strtoupper($k)] = trim($this->_input->post->getString('oauth_' . $k));
			}
		}

		// If we didn't find anything return false.
		if (empty($parameters))
		{
			return false;
		}

		return $parameters;
	}
}
