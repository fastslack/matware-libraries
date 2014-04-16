<?php
/**
 * @package     Webservices.Admin
 * @subpackage  Controllers
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
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
