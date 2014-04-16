<?php
/**
 * @package     Webservices.Admin
 * @subpackage  Helpers
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
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
