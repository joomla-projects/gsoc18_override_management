<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Component\Languages\Administrator\Helper\LanguagesHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');
$client    = $this->state->get('filter.client') == '0' ? Text::_('JSITE') : Text::_('JADMINISTRATOR');
$language  = $this->state->get('filter.language');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

$opposite_client   = $this->state->get('filter.client') == '1' ? Text::_('JSITE') : Text::_('JADMINISTRATOR');
$opposite_filename = constant('JPATH_' . strtoupper(1 - $this->state->get('filter.client')? 'administrator' : 'site'))
	. '/language/overrides/' . $this->state->get('filter.language', 'en-GB') . '.override.ini';
$opposite_strings  = LanguagesHelper::parseFile($opposite_filename);
?>

<form action="<?php echo Route::_('index.php?option=com_languages&view=overrides'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div id="j-sidebar-container" class="col-md-2">
			<?php echo $this->sidebar; ?>
		</div>
		<div class="col-md-10">
			<div id="j-main-container" class="j-main-container">
				<div id="filter-bar" class="btn-toolbar clearfix">
					<div class="filter-search btn-group float-left">
						<div class="input-group">
							<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" class="form-control hasTooltip" title="<?php echo HTMLHelper::tooltipText('COM_LANGUAGES_VIEW_OVERRIDES_FILTER_SEARCH_DESC'); ?>">
							<div class="input-group-append">
								<button type="submit" class="btn btn-secondary hasTooltip" title="<?php echo HTMLHelper::_('tooltipText', 'JSEARCH_FILTER_SUBMIT'); ?>"><span class="icon-search" aria-hidden="true"></span></button>
								<button type="button" class="btn btn-secondary hasTooltip" title="<?php echo HTMLHelper::_('tooltipText', 'JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();"><span class="icon-remove" aria-hidden="true"></span></button>
							</div>
						</div>
					</div>
					<div class="btn-group float-right d-none d-md-block">
						<label for="limit" class="sr-only"><?php echo Text::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?></label>
						<?php echo $this->pagination->getLimitBox(); ?>
					</div>
				</div>
				<?php if (empty($this->items)) : ?>
					<joomla-alert type="warning"><?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></joomla-alert>
				<?php else : ?>
					<table class="table" id="overrideList">
						<thead>
							<tr>
								<td style="width:1%" class="text-center">
									<?php echo HTMLHelper::_('grid.checkall'); ?>
								</td>
								<th scope="col" style="width:30%">
									<?php echo HTMLHelper::_('grid.sort', 'COM_LANGUAGES_VIEW_OVERRIDES_KEY', 'key', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="d-none d-md-table-cell">
									<?php echo HTMLHelper::_('grid.sort', 'COM_LANGUAGES_VIEW_OVERRIDES_TEXT', 'text', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="nowrap d-none d-md-table-cell">
									<?php echo Text::_('COM_LANGUAGES_FIELD_LANG_TAG_LABEL'); ?>
								</th>
								<th scope="col" class="d-none d-md-table-cell">
									<?php echo Text::_('JCLIENT'); ?>
								</th>
							</tr>
						</thead>
						<tbody>
						<?php $canEdit = Factory::getUser()->authorise('core.edit', 'com_languages'); ?>
						<?php $i = 0; ?>
						<?php foreach ($this->items as $key => $text) : ?>
							<tr class="row<?php echo $i % 2; ?>" id="overriderrow<?php echo $i; ?>">
								<td class="text-center">
									<?php echo HTMLHelper::_('grid.id', $i, $key); ?>
								</td>
								<th scope="row">
									<?php if ($canEdit) : ?>
										<a id="key[<?php echo $this->escape($key); ?>]" href="<?php echo Route::_('index.php?option=com_languages&task=override.edit&id=' . $key); ?>" title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo $this->escape(addslashes($key)); ?>">
											<span class="fa fa-pencil-square mr-2" aria-hidden="true"></span><?php echo $this->escape($key); ?></a>
									<?php else : ?>
										<?php echo $this->escape($key); ?>
									<?php endif; ?>
								</th>
								<td class="d-none d-md-table-cell">
									<span id="string[<?php echo $this->escape($key); ?>]"><?php echo $this->escape($text); ?></span>
								</td>
								<td class="d-none d-md-table-cell">
									<?php echo $language; ?>
								</td>
								<td class="d-none d-md-table-cell">
									<?php echo $client; ?>
									<?php
									if (isset($opposite_strings[$key]) && ($opposite_strings[$key] == $text))
									{
										echo '/' . $opposite_client;
									}
									?>
								</td>
							</tr>
						<?php $i++; ?>
						<?php endforeach; ?>
						</tbody>
					</table>

					<?php // load the pagination. ?>
					<?php echo $this->pagination->getListFooter(); ?>

				<?php endif; ?>

				<input type="hidden" name="task" value="">
				<input type="hidden" name="boxchecked" value="0">
				<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>">
				<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>">
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
