<?php
/**
 * @version       $Id:
 * @package       Matware.Plugin
 * @subpackage    Matware
 * @copyright     Copyright (C) 2004 - 2014 Matware - All rights reserved.
 * @author        Matias Aguirre
 * @email         maguirre@matware.com.ar
 * @link          http://www.matware.com.ar/
 * @license       GNU/GPL http://www.gnu.org/licenses/gpl-2.0-standalone.html
 */

defined('JPATH_BASE') or die;

/**
 * System plugin for Matware
 *
 * @package     Joomla.Plugin
 * @subpackage  System
 * @since       1.0
 */
class PlgSystemMatware extends JPlugin
{
	/**
	 * Method to register custom library.
	 *
	 * @return  void
	 */
	public function onAfterInitialise()
	{
		$bootstrap = JPATH_LIBRARIES . '/matware/bootstrap.php';

		if (file_exists($bootstrap))
		{
			require_once $bootstrap;
		}
	}


	/**
	 * This event is triggered before the framework creates the Head section of the Document.
	 *
	 * @return  void
	 *
	 * @todo    Find a cleaner way to prioritise assets
	 */
	public function onBeforeCompileHead()
	{
		$this->disableMootools = true;

		$doc = JFactory::getDocument();
		$isAdmin = JFactory::getApplication()->isAdmin();
		//RHtmlMedia::loadFrameworkJs();
		if ($doc->_scripts)
		{
			$template = JFactory::getApplication()->getTemplate();
			// Remove Mootools if asked by view, or if it's a site view and it has been asked via plugin parameters
			if ($this->disableMootools)
			{
				$doc->addScriptDeclaration("function do_nothing() { return; }");
				unset($doc->_scripts[JURI::root(true) . '/media/system/js/mootools-core.js']);
				unset($doc->_scripts[JURI::root(true) . '/media/system/js/mootools-more.js']);
				unset($doc->_scripts[JURI::root(true) . '/media/system/js/caption.js']);
				unset($doc->_scripts[JURI::root(true) . '/media/system/js/modal.js']);
				unset($doc->_scripts[JURI::root(true) . '/media/system/js/mootools.js']);
				unset($doc->_scripts[JURI::root(true) . '/plugins/system/mtupgrade/mootools.js']);
				unset($doc->_scripts[JURI::root(true) . '/media/system/js/mootools-core-uncompressed.js']);
				unset($doc->_scripts[JURI::root(true) . '/media/system/js/caption-uncompressed.js']);
				unset($doc->_scripts[JURI::root(true) . '/media/system/js/modal-uncompressed.js']);
				unset($doc->_scripts[JURI::root(true) . '/media/system/js/mootools-more-uncompressed.js']);
				if ($doc->_styleSheets)
				{
					unset($doc->_styleSheets[JURI::root(true) . '/media/system/css/modal.css']);
				}
				if (!$isAdmin)
				{
					unset($doc->_scripts[JURI::root(true) . '/media/system/js/core.js']);
					unset($doc->_scripts[JURI::root(true) . '/media/system/js/core-uncompressed.js']);
				}
			}
			// Remove jQuery in administration, or if it's frontend site and it has been asked via plugin parameters
			if (true)
			{
				unset($doc->_scripts[JURI::root(true) . '/media/jui/js/jquery.min.js']);
				unset($doc->_scripts[JURI::root(true) . '/media/jui/js/jquery.js']);
				unset($doc->_scripts[JURI::root(true) . '/media/jui/js/jquery-noconflict.js']);
				$jQueryChosen = false;
				if (isset($doc->_scripts[JURI::root(true) . '/media/jui/js/chosen.jquery.js'])
					|| isset($doc->_scripts[JURI::root(true) . '/media/jui/js/chosen.jquery.min.js']))
				{
					$jQueryChosen = true;
					unset($doc->_scripts[JURI::root(true) . '/media/jui/js/chosen.jquery.js']);
					unset($doc->_scripts[JURI::root(true) . '/media/jui/js/chosen.jquery.min.js']);
					unset($doc->_styleSheets[JURI::root(true) . '/media/jui/css/chosen.css']);
					unset($doc->_styleSheets[JURI::root(true) . '/media/jui/css/chosen.min.css']);
				}
				// Template specific overrides for jQuery files (valid in Joomla 3.x)
				unset($doc->_scripts[JURI::root(true) . '/templates/' . $template . '/js/jui/jquery.min.js']);
				unset($doc->_scripts[JURI::root(true) . '/templates/' . $template . '/js/jui/jquery.js']);
				unset($doc->_scripts[JURI::root(true) . '/templates/' . $template . '/js/jui/jquery-noconflict.js']);
				if (isset($doc->_scripts[JURI::root(true) . '/templates/' . $template . '/js/jui/chosen.jquery.js'])
					|| isset($doc->_scripts[JURI::root(true) . '/templates/' . $template . '/js/jui/chosen.jquery.min.js']))
				{
					$jQueryChosen = true;
					unset($doc->_scripts[JURI::root(true) . '/templates/' . $template . '/js/jui/chosen.jquery.js']);
					unset($doc->_scripts[JURI::root(true) . '/templates/' . $template . '/js/jui/chosen.jquery.min.js']);
					unset($doc->_styleSheets[JURI::root(true) . '/templates/' . $template . '/css/jui/chosen.css']);
					unset($doc->_styleSheets[JURI::root(true) . '/templates/' . $template . '/css/jui/chosen.min.css']);
				}
				// Enables chosen when it was removed
				if ($jQueryChosen)
				{
					//RHtml::_('rjquery.chosen', 'select');
				}
			}
			// Remove jQuery Migrate in administration, or if it's frontend site and it has been asked via plugin parameters
			if (true)
			{
				unset($doc->_scripts[JURI::root(true) . '/media/jui/js/jquery-migrate.min.js']);
				unset($doc->_scripts[JURI::root(true) . '/media/jui/js/jquery-migrate.js']);
				// Template specific overrides for jQuery files (valid in Joomla 3.x)
				unset($doc->_scripts[JURI::root(true) . '/templates/' . $template . '/js/jui/jquery-migrate.min.js']);
				unset($doc->_scripts[JURI::root(true) . '/templates/' . $template . '/js/jui/jquery-migrate.js']);
			}
			// Remove Bootstrap in administration, or if it's frontend site and it has been asked via plugin parameters
			if (true)
			{
				unset($doc->_scripts[JURI::root(true) . '/media/jui/js/bootstrap.js']);
				unset($doc->_scripts[JURI::root(true) . '/media/jui/js/bootstrap.min.js']);
				// Template specific overrides for jQuery files (valid in Joomla 3.x)
				unset($doc->_scripts[JURI::root(true) . '/templates/' . $template . '/js/jui/bootstrap.js']);
				unset($doc->_scripts[JURI::root(true) . '/templates/' . $template . '/js/jui/bootstrap.min.js']);
			}
		}
	}
}
