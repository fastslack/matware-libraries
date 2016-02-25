<?php
/**
 * @version       $Id:
 * @package       Matware.Libraries
 * @subpackage    OAuth2
 * @copyright     Copyright (C) 2004 - 2016 Matware - All rights reserved.
 * @author        Matias Aguirre
 * @email         maguirre@matware.com.ar
 * @link          http://www.matware.com.ar/
 * @license       GNU/GPL http://www.gnu.org/licenses/gpl-2.0-standalone.html
 */
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.environment.response');

/**
 * Joomla! class for interacting with an OAuth 2.0 server.
 *
 * @package     Matware.Libraries
 * @subpackage  OAuth2
 * @since       1.0
 */
class MClientOauth2
{
	/**
	 * @var    JRegistry  Options for the JClientOAuth2 object.
	 * @since  1.0
	 */
	protected $options;

	/**
	 * @var    JHttp  The HTTP client object to use in sending HTTP requests.
	 * @since  1.0
	 */
	protected $http;

	/**
	 * @var    JInput  The input object to use in retrieving GET/POST data.
	 * @since  1.0
	 */
	protected $input;

	/**
	 * @var    JApplicationWeb  The application object to send HTTP headers for redirects.
	 * @since  1.0
	 */
	protected $application;

	/**
	 * Constructor.
	 *
	 * @param   JRegistry        $options      JClientOAuth2 options object
	 * @param   JHttp            $http         The HTTP client object
	 * @param   JInput           $input        The input object
	 * @param   JApplicationWeb  $application  The application object
	 *
	 * @since   1.0
	 */
	public function __construct(JRegistry $options = null, JHttp $http = null, JInput $input = null, JApplicationWeb $application = null)
	{
		$this->options = isset($options) ? $options : new JRegistry;
		$this->http = isset($http) ? $http : new JHttp($this->options);
		$this->application = isset($application) ? $application : new JApplicationWeb;
		$this->input = isset($input) ? $input : $this->application->input;

		$this->rest_key = $this->randomKey();
	}

	/**
	 * Fetch the access token making the OAuth 2.0 method process
	 *
	 * @return	object	Returns the token object
	 *
	 * @since 	1.0
	 * @throws	Exception
	 */
	public function fetchAccessToken()
	{
		// Execute the temporary token
		try {
			// Create the request array to be sent
			$append = array(
				'oauth_response_type' => 'temporary'
			);

			$code = (object) $this->getPostRequest($append);
		} catch (RuntimeException $e) {
			throw new RuntimeException($e->getMessage());
		}

		// Get authorization token
		try {
			// Create the request array to be sent
			$append = array(
				'oauth_grant_type' => 'authorization_code',
				'oauth_response_type' => 'authorise',
				'oauth_code' => $code->oauth_code
			);

			$code = (object) $this->getPostRequest($append);
		} catch (RuntimeException $e) {
			throw new RuntimeException($e->getMessage());
		}

		// Get access token
		try {
			// Create the request array to be sent
			$append = array(
				'oauth_response_type' => 'token',
				'oauth_code' => $code->oauth_code
			);
			$token = (object) $this->getPostRequest($append);
		} catch (RuntimeException $e) {
			throw new RuntimeException($e->getMessage());
		}

		return $token;
	}

	/**
	 * Get the rest headers to send
	 *
	 * @param   string  $form  True if we like to use POST
	 *
	 * @return  array   The RESTful headers
	 *
	 * @since   1.0
	 */
	protected function getRestHeaders($form = false)
	{
		// Encode the headers for REST
		$user_encode = $this->encode($this->options->get('username'), $this->rest_key);
		$pw_encode = $this->encode($this->options->get('password'), $this->rest_key);
		$authorization = $this->encode($user_encode, $pw_encode, true);

		$headers = array(
			'Authorization' => 'Bearer ' . base64_encode($authorization)
		);

		if ($form === true)
		{
			$headers['Content-Type'] = 'application/x-www-form-urlencoded';
		}

		return $headers;
	}

	/**
	 * Get the POST data to send
	 *
	 * @return  array   The POST data to send
	 *
	 * @since   1.0
	 */
	protected function getPostData()
	{
		// Set the user and password to headers
		$rest_key = $this->randomKey();

		// Encode the headers for REST
		$user_encode = $this->encode($this->options->get('username'), $this->rest_key);
		$pw_encode = $this->encode($this->options->get('password'), $this->rest_key);
		$client_secret = $this->encode($this->randomKey(), $pw_encode, true);

		$post = array(
			'oauth_client_id' => base64_encode($user_encode),
			'oauth_client_secret' => base64_encode($client_secret),
			'oauth_signature_method' => $this->options->get('signature_method')
		);

		return $post;
	}

	/**
	 * Get the request for post
	 *
	 * @param   array  $append  The array with oauth parameters to append
	 *
	 * @return	string	Returns authentication token
	 *
	 * @since 	1.0
	 * @throws	Exception
	 */
	public function getPostRequest($append = array())
	{
		// Get the headers
		$data = $this->getPostData();

		// Append parameters to existing data
		$data = $data + $append;

		// Send the request
		$response = $this->http->post($this->options->get('url'), $data, $this->getRestHeaders(true));

		// Process the response
		$token = $this->processRequest($response);

		return $token;
	}

	/**
	 * Refresh the access token instance.
	 *
	 * @param   string  $token  The old token to be refreshed
	 *
	 * @return  array  The new access token
	 *
	 * @since   1.0
	 */
	public function refreshToken($token = null)
	{
		// Get access token
		try {
			// Create the request array to be sent
			$append = array(
				'oauth_response_type' => 'refresh_token',
				'oauth_refresh_token' => $token
			);
			$token = (object) $this->getPostRequest($append);
		} catch (RuntimeException $e) {
			throw new RuntimeException($e->getMessage());
		}

		return $token;
	}

	/**
	 * Get the resource using the access token.
	 *
	 * @param   string  $token  The access token
	 *
	 * @return	string	Returns the JSON+HAL resource
	 *
	 * @since 	1.0
	 * @throws	Exception
	 */
	public function getResource($options = array(), $resource = null)
	{
		// Get the headers
		$data = $this->getPostData();

		// Set the correct client_id for oauth2
		$options['oauth_client_id'] = !empty($options['oauth_client_id']) ? $options['oauth_client_id'] : $data['oauth_client_id'];

		// Set the http method
		$method = !empty($options['method']) ? strtolower($options['method']) : "get";
		unset($options['method']);

		// Add GET parameters to URL
		$url_query = http_build_query($options);
		$url = $this->options->get('url') . "?{$url_query}";

		// Send the request
		if ($method == "get")
		{
			$response = $this->http->get($url, $this->getRestHeaders());
		} else if ($method == "post") {
			$response = $this->http->post($url, $resource, $this->getRestHeaders());
		}

		// Check the response
		if ($response->code >= 200 && $response->code < 400)
		{
			return $response->body;
		}
		else
		{
			throw new RuntimeException('Error code ' . $response->code . ': ' . $response->body . '.');
		}
	}

	/**
	 * Process the HTTP request and return and array with the token
	 *
	 * @return	array	Returns a reference to the token response.
	 *
	 * @since 	1.0
	 * @throws	Exception
	 */
	function processRequest($response)
	{
		// Check if the request is correct
		if ($response->code >= 200 && $response->code < 400)
		{
			if ($response->headers['X-Powered-By'] == 'JoomlaWebAPI/1.0')
			{
				$token = array_merge(json_decode($response->body, true), array('created' => time()));
			}
			else
			{
				parse_str($response->body, $token);
				$token = array_merge($token, array('created' => time()));
			}

			return $token;
		}
		else
		{
			throw new RuntimeException('Error code ' . $response->code . ': ' . $response->body . '.');
		}
	}

	/**
	 * Get an option from the JClientOAuth2 instance.
	 *
	 * @param   string  $key  The name of the option to get
	 *
	 * @return  mixed  The option value
	 *
	 * @since   1.0
	 */
	public function getOption($key)
	{
		return $this->options->get($key);
	}

	/**
	 * Set an option for the JClientOAuth2 instance.
	 *
	 * @param   string  $key    The name of the option to set
	 * @param   mixed   $value  The option value to set
	 *
	 * @return  JClientOAuth2  This object for method chaining
	 *
	 * @since   1.0
	 */
	public function setOption($key, $value)
	{
		$this->options->set($key, $value);

		return $this;
	}

	/**
	 * Get the access token from the JClientOAuth2 instance.
	 *
	 * @return  array  The access token
	 *
	 * @since   1.0
	 */
	public function getAccessToken()
	{
		return $this->getOption('access_token');
	}

	/**
	 * Set an option for the JClientOAuth2 instance.
	 *
	 * @param   array  $value  The access token
	 *
	 * @return  JClientOAuth2  This object for method chaining
	 *
	 * @since   1.0
	 */
	public function setToken($value)
	{
		if (is_array($value) && !array_key_exists('expires_in', $value) && array_key_exists('expires', $value))
		{
			$value['expires_in'] = $value['expires'];
			unset($value['expires']);
		}

		$this->setOption('access_token', $value);

		return $this;
	}

	/**
	 * Generate a random (and optionally unique) key.
	 *
	 * @param   boolean  $unique  True to enforce uniqueness for the key.
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	protected function randomKey($unique = false)
	{
		$str = md5(uniqid(rand(), true));

		if ($unique)
		{
			list ($u, $s) = explode(' ', microtime());
			$str .= dechex($u) . dechex($s);
		}

		return $str;
	}

	/**
	 * Verify if the client has been authenticated
	 *
	 * @return  boolean  Is authenticated
	 *
	 * @since   1.0
	 */
	public function isAuthenticated()
	{
		$token = $this->getToken();

		if (!$token || !array_key_exists('access_token', $token))
		{
			return false;
		}
		elseif (array_key_exists('expires_in', $token) && $token['created'] + $token['expires_in'] < time() + 20)
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Encode the string with the key
	 *
	 * @param   string   $string  The string to encode.
	 * @param   string   $key     The key to encode the string.
	 * @param   boolean  $base64  True to encode the strings.
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	protected function encode($string, $key, $base64 = false)
	{
		if ($base64 === true)
		{
			$return = base64_encode($string) . ":" . base64_encode($key);
		}
		else
		{
			$return = "{$string}:{$key}";
		}

		return $return;
	}
}
