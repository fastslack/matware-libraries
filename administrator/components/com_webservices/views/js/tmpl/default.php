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

$action = JRoute::_('index.php?option=com_webservices&view=js');

$document	= JFactory::getDocument();
$document->addStyleSheet("components/com_webservices/css/webservices.css");

// HTML helpers
JHtml::_('behavior.framework', true);

// Add the script to the document head.
// $document->addScript() is not working with jQuery? JDocument bug?
JFactory::getDocument()->addScriptDeclaration(file_get_contents(JPATH_ROOT.'/media/com_joomlaupdate/encryption.js'));
JFactory::getDocument()->addScriptDeclaration(file_get_contents('components/com_webservices/js/oauth2.webservices.js'));

?>
<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal">
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>

	<!-- Begin Content -->
	<div class="span12">

		URL:<br />
		<input type="text" class="inputbox span6" id="url" name="url" value=""><br /><br />
		Username:<br />
		<input type="text" class="inputbox span6" id="oauth_client_id" name="oauth_client_id" value=""><br /><br />
		Password:<br />
		<input type="password" class="inputbox span6" id="oauth_client_secret" name="oauth_client_secret" value=""><br /><br />

		<button id="authorise" name="authorise">Authorise</button><button id="resource" name="resource">Get resource</button><br /><br />

		<div id="returnDiv" name="returnDiv" class="span10 jsbox"></div>

		<!-- hidden fields -->
	  	<input type="hidden" name="option"	 value="com_webservices">
			<input type="hidden" id="tmp_token" name="tmp_token" value="">
	  	<input type="hidden" name="task" value="">
	  	<input type="hidden" name="boxchecked" value="">
		<?php echo JHTML::_('form.token'); ?>
	</div>
</form>
