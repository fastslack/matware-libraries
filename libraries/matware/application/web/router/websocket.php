<?php
/**
 * @version       $Id: 
 * @package       Matware.Libraries
 * @subpackage    Websocket
 * @copyright     Copyright (C) 2004 - 2014 Matware - All rights reserved.
 * @author        Matias Aguirre
 * @email         maguirre@matware.com.ar
 * @link          http://www.matware.com.ar/
 * @license       GNU/GPL http://www.gnu.org/licenses/gpl-2.0-standalone.html
 */

defined('JPATH_PLATFORM') or die;

/**
 * Websocket Web application router class for the Matware Libraries.
 *
 * @package     Matware.Libraries
 * @subpackage  Application
 * @since       1.0
 */
class MApplicationWebRouterWebsocket extends JApplicationWebRouterBase
{
	/**
	 * Parse the given route and return the name of a controller mapped to the given route.
	 *
	 * @param   string  $route  The route string for which to find and execute a controller.
	 *
	 * @return  string  The controller name for the given route excluding prefix.
	 *
	 * @since   1.0
	 * @throws  InvalidArgumentException
	 */
	protected function parseRoute($route)
	{
		$controller = parent::parseRoute($route);

		// If the controller name includes a component route prefix then handle it.
		// Form is 'controller/' component-name-without-com_ controllername
		// eg. 'component/content/ArticlesList' will become ComponentContentArticlesList
		$parts = explode('/', $controller);
		if ($parts[0] == 'component')
		{
			$this->controllerPrefix = 'Component' . ucfirst($parts[1]);
			$controller = $parts[2];
		}

		return $controller;
	}

	/**
	 * Find and execute the appropriate controller based on a given route.
	 *
	 * @param   string  $route  The route string for which to find and execute a controller.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @throws  InvalidArgumentException
	 * @throws  RuntimeException
	 */
	public function execute($route)
	{
		// Get the controller name based on the route patterns and requested route.
		$name = $this->parseRoute($route);

		// Append the HTTP method based suffix.
		$name .= $this->fetchControllerSuffix();

		// Get the controller object by name.
		$controller = $this->fetchController($name);

		// Execute the controller.
		return $controller->execute();
	}

	/**
	 * Get the controller class suffix string.
	 *
	 * @return  string
	 *
	 * @since   1.0
	 * @throws  RuntimeException
	 */
	protected function fetchControllerSuffix()
	{
		return 'Websocket';
	}
}
