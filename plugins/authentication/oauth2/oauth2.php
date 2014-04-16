<?php
/**
 * @version       $Id: 
 * @package       Matware.Plugin
 * @subpackage    OAuth2
 * @copyright     Copyright (C) 2004 - 2014 Matware - All rights reserved.
 * @author        Matias Aguirre
 * @email         maguirre@matware.com.ar
 * @link          http://www.matware.com.ar/
 * @license       GNU/GPL http://www.gnu.org/licenses/gpl-2.0-standalone.html
 */

defined('_JEXEC') or die;

jimport('cms.html.html');
jimport('joomla.user.helper');

JLoader::register('MOauth2Server', JPATH_LIBRARIES.'/matware/oauth2/server.php');

/**
 * OAuth2 Authentication Plugin
 *
 * @package     Joomla.Plugin
 * @subpackage  Authentication.oauth2
 * @since       1.0
 */
class PlgAuthenticationOAuth2 extends JPlugin
{
	/**
	 * This method should handle any authentication and report back to the subject
	 *
	 * @param   array   $credentials  Array holding the user credentials
	 * @param   array   $options      Array of extra options
	 * @param   object  $response     Authentication response object
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function onBeforeExecute ()
	{
		//if (!$this->isSSLConnection()) {
		//	exit;
		//}

		// Init the flag
		$request = false;
		// Load the Joomla! application
		$app = JFactory::getApplication();
		// Get the OAuth2 server instance
		$oauth_server = new MOauth2Server;

		if ($oauth_server->listen())
		{
			$request = true;
		}
	}

	public function onUserAuthenticate() {}

	/**
	 * Determine if we are using a secure (SSL) connection.
	 *
	 * @return  boolean  True if using SSL, false if not.
	 *
	 * @since   1.0
	 */
	public function isSSLConnection()
	{
		return ((isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on')) || getenv('SSL_PROTOCOL_VERSION'));
	}
}
