<?php
/**
 * @package     Webservices.Admin
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die;

$action = JRoute::_('index.php?option=com_webservices&view=credential');

$document	= JFactory::getDocument();
$document->addStyleSheet("components/com_webservices/css/webservices.css");

// HTML helpers
JHtml::_('behavior.keepalive');

$script = 'function isValidURL(url){
    var RegExp = /(http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;

    if(RegExp.test(url)){
        return true;
    }else{
        return false;
    }
}';

// Load script on document load.
//$document->addScriptDeclaration($script);
?>
<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal">

	<!-- Begin Content -->
	<div class="span12">
		<fieldset class="form-vertical">
			<div class="control-group">
					<div class="control-label">
					<?php echo $this->form->getLabel('url'); ?>
					</div>
					<div class="controls">
					<?php echo $this->form->getInput('url'); ?>
					</div>
			</div>
			<div class="control-group">
					<div class="control-label">
					<?php echo $this->form->getLabel('username'); ?>
					</div>
					<div class="controls">
					<?php echo $this->form->getInput('username'); ?>
					</div>
			</div>
			<div class="control-group">
					<div class="control-label">
					<?php echo $this->form->getLabel('password'); ?>
					</div>
					<div class="controls">
					<?php echo $this->form->getInput('password'); ?>
					</div>
			</div>
			<div class="control-group">
					<div class="control-label">
					<?php echo $this->form->getLabel('signature_method'); ?>
					</div>
					<div class="controls">
					<?php echo $this->form->getInput('signature_method'); ?>
					</div>
			</div>
		</fieldset>
		<!-- hidden fields -->
	  	<input type="hidden" name="option"	value="com_webservices">
	  	<input type="hidden" name="id"	value="<?php echo $this->item->tokens_id; ?>">
	  	<input type="hidden" name="task" value="">
	  	<input type="hidden" name="boxchecked" value="">
		<?php echo JHTML::_('form.token'); ?>
	</div>
</form>
