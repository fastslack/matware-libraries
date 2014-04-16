<?php
/**
 * @version       $Id: 
 * @package       Matware.Component
 * @subpackage    Webservices
 * @copyright     Copyright (C) 2004 - 2014 Matware - All rights reserved.
 * @author        Matias Aguirre
 * @email         maguirre@matware.com.ar
 * @link          http://www.matware.com.ar/
 * @license       GNU/GPL http://www.gnu.org/licenses/gpl-2.0-standalone.html
 */
defined('_JEXEC') or die;

/**
 * Backend front controller of Joomla! Webservices API.
 *
 * @package     Webservices.Admin
 * @subpackage  Controllers
 * @since       1.0
 */
class WebservicesController extends JControllerLegacy
{
	/**
	 * Typical view method for MVC based architecture
	 *
	 * This function is provide as a default implementation, in most cases
	 * you will need to override it in your own controllers.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JControllerLegacy  A JControllerLegacy object to support chaining.
	 */
	public function display($cachable = false, $urlparams = array())
	{
		require_once JPATH_COMPONENT.'/helpers/webservices.php';

		$input = JFactory::getApplication()->input;

		$view = $input->get('view', 'tokens');

		// Set default view if not set.
		$input->set('view', $view);
		$input->set('task', $input->get('task', 'display'));

		parent::display($cachable, $urlparams);
	}
}
