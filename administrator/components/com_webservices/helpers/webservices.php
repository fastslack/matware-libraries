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
 * Main Helper
 *
 * @package     Webservices.Admin
 * @subpackage  Helpers
 * @since       1.0
 */
class WebservicesHelper
{
	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @param   int  $categoryId  The category ID.
	 *
	 * @return	JObject
	 */
	public static function getActions($categoryId = 0)
	{
		$user	= JFactory::getUser();
		$result	= new JObject;

		if (empty($categoryId))
		{
			$assetName = 'com_webservices';
			$level = 'component';
		}
		else
		{
			$assetName = 'com_webservices.category.' . (int) $categoryId;
			$level = 'category';
		}

		$actions = JAccess::getActions('com_webservices', $level);

		foreach ($actions as $action)
		{
			$result->set($action->name,	$user->authorise($action->name, $assetName));
		}

		return $result;
	}

	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $vName  The name of the active view.
	 *
	 * @return	void
	 */
	public static function addSubmenu($vName = 'webservices')
	{
		JHtmlSidebar::addEntry(
			JText::_('COM_WEBSERVICES_CREDENTIALS_LIST_TITLE'),
			'index.php?option=com_webservices&view=credentials',
			$vName == 'credentials'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_WEBSERVICES_TOKENS_LIST_TITLE'),
			'index.php?option=com_webservices&view=tokens',
			$vName == 'tokens'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_WEBSERVICES_JS_LIST_TITLE'),
			'index.php?option=com_webservices&view=js',
			$vName == 'js'
		);
	}
}
