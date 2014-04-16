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
 * Credential List Controller
 *
 * @package     Webservices.Admin
 * @subpackage  Controllers
 * @since       1.0
 */
class WebservicesControllerCredentials extends JControllerAdmin
{
	/**
	 * Return to control panel
	 *
	 * @return  void
	 */
	public function toPanel()
	{
		$this->setRedirect(JRoute::_('index.php?option=com_webservices', false));
	}

	function getModel ($name = 'Credentials', $prefix = '', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}
}
