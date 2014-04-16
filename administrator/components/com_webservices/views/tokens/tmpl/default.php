<?php
/**
 * @package     Webservices.Admin
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

JHTML::_('bootstrap.tooltip');
JHTML::_('behavior.multiselect');
JHTML::_('formbehavior.chosen', 'select');
JHTML::_('behavior.modal');

$doc = JFactory::getDocument();

$doc->addScript('components/com_webservices/js/jquery.zclip.min.js');
$doc->addStyleSheet("components/com_webservices/css/webservices.css");

$user = JFactory::getUser();
$action = JRoute::_('index.php?option=com_webservices&view=tokens');
$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
$saveOrder = $listOrder == 'ordering';
?>
<script>
	SqueezeBox.assign($$('a.modal'), {
		parse: 'rel'
	});

	Joomla.orderTable = function()
	{
		table = document.getElementById("sortTable");
		direction = document.getElementById("directionTable");
		order = table.options[table.selectedIndex].value;
		if (order != '<?php echo $listOrder; ?>')
		{
			dirn = 'asc';
		}
		else
		{
			dirn = direction.options[direction.selectedIndex].value;
		}
		Joomla.tableOrdering(order, dirn, '');
	}

	 $('a#copy-dynamic').zclip({
		path:'components/com_webservices/js/ZeroClipboard.swf',
		copy:$('a#copy-input').text()
	});


</script>

<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
		<div id="filter-bar" class="btn-toolbar">
			<div class="filter-search btn-group pull-left">
				<label for="filter_search" class="element-invisible"><?php echo JText::_('COM_WEBLINKS_SEARCH_IN_TITLE');?></label>
				<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" class="hasTooltip" title="<?php echo JHtml::tooltipText('COM_WEBLINKS_SEARCH_IN_TITLE'); ?>" />
			</div>
			<div class="btn-group pull-left">
				<button type="submit" class="btn hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
				<button type="button" class="btn hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.id('filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
			</div>
			<div class="btn-group pull-right hidden-phone">
				<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC');?></label>
				<?php echo $this->pagination->getLimitBox(); ?>
			</div>
			<div class="btn-group pull-right hidden-phone">
				<label for="directionTable" class="element-invisible"><?php echo JText::_('JFIELD_ORDERING_DESC');?></label>
				<select name="directionTable" id="directionTable" class="input-medium" onchange="Joomla.orderTable()">
					<option value=""><?php echo JText::_('JFIELD_ORDERING_DESC');?></option>
					<option value="asc" <?php if ($listDirn == 'asc') echo 'selected="selected"'; ?>><?php echo JText::_('JGLOBAL_ORDER_ASCENDING');?></option>
					<option value="desc" <?php if ($listDirn == 'desc') echo 'selected="selected"'; ?>><?php echo JText::_('JGLOBAL_ORDER_DESCENDING');?></option>
				</select>
			</div>
			<div class="btn-group pull-right">
				<label for="sortTable" class="element-invisible"><?php echo JText::_('JGLOBAL_SORT_BY');?></label>
				<select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">
					<option value=""><?php echo JText::_('JGLOBAL_SORT_BY');?></option>
					<?php echo JHtml::_('select.options', $sortFields, 'value', 'text', $listOrder);?>
				</select>
			</div>
		</div>
		<hr />
		<table class="table table-striped table-hover" id="tokenList">
			<thead>
			<tr>
				<th width="1%" class="hidden-phone">
					<input type="checkbox" name="checkall-toggle" value=""
					       title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
				</th>
				<th width="25%">
					<?php echo JHtml::_('grid.sort', 'COM_WEBSERVICES_RESOURCE_URI_LABEL', 't.resource_uri', $listDirn, $listOrder); ?>
				</th>
				<th width="2%">
					&nbsp;
				</th>
				<th width="10%">
					<?php echo JHtml::_('grid.sort', 'COM_WEBSERVICES_ACCESS_TOKEN_LABEL', 't.access_token', $listDirn, $listOrder); ?>
				</th>
				<th width="10%">
					<?php echo JHtml::_('grid.sort', 'COM_WEBSERVICES_REFRESH_TOKEN_LABEL', 't.refresh_token', $listDirn, $listOrder); ?>
				</th>
				<th width="10%">
					<?php echo JHtml::_('grid.sort', 'COM_WEBSERVICES_EXPIRES_IN_LABEL', 't.expires_in', $listDirn, $listOrder); ?>
				</th>
				<th width="1%" class="nowrap hidden-phone">
					<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 't.tokens_id', $listDirn, $listOrder); ?>
				</th>
			</tr>
			</thead>
			<?php if ($this->items): ?>
				<tbody>
				<?php foreach ($this->items as $i => $item): ?>
					<?php
						if ( $item->access_token != 'anonymous' )
						{
							$modalLink = 'components/com_webservices/hal_browser/hal_browser.html#'.$this->escape($item->resource_uri).'?oauth_access_token='.$this->escape($item->access_token).'&oauth_client_id='.$item->client_id;
						}
						else
						{
							$modalLink = 'components/com_webservices/hal_browser/hal_browser.html#'.$this->escape($item->resource_uri);
						}

						$canChange = 1;
						$canEdit = 1;
					?>
					<tr class="row<?php echo $i % 2; ?>">
						<td>
							<?php echo JHtml::_('grid.id', $i, $item->tokens_id); ?>
						</td>
						<td>
							<a id="copy-input" class="modal" rel="{handler: 'iframe', size: {x: 1050, y: 600}}" href="<?php echo $modalLink; ?>">
								<?php echo $this->escape($item->resource_uri); ?>
							</a>
						</td>
						<td>
							<a id="copy-dynamic" href="#"><span title="copy to clipboard" data-copied-hint="copied!" data-clipboard-text="<?php echo $this->escape($item->resource_uri); ?>" class="js-zeroclipboard url-box-clippy minibutton zeroclipboard-button"><img src="components/com_webservices/images/clip.png"></span></a>
						</td>
						<td>
							<?php echo $this->escape($item->access_token); ?>
						</td>
						<td>
							<?php echo $this->escape($item->refresh_token); ?>
						</td>
						<td>
							<?php echo $item->expiration_date; ?>
						</td>
						<td>
							<?php echo $item->tokens_id; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			<?php endif; ?>
		</table>
		<?php echo $this->pagination->getListFooter(); ?>
	</div>

	<div>
		<input type="hidden" name="task" value="">
		<input type="hidden" name="boxchecked" value="0">
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
