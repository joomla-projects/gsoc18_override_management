<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');

HTMLHelper::_('behavior.multiselect');

$user      = Factory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder == 'a.ordering';

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_languages&task=languages.saveOrderAjax&tmpl=component';
	HTMLHelper::_('sortablelist.sortable', 'contentList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
?>
<form action="<?php echo Route::_('index.php?option=com_languages&view=languages'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div id="j-sidebar-container" class="col-md-2">
			<?php echo $this->sidebar; ?>
		</div>
		<div class="col-md-10">
			<div id="j-main-container" class="j-main-container">
				<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
				<?php if (empty($this->items)) : ?>
					<joomla-alert type="warning"><?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></joomla-alert>
				<?php else : ?>
					<table class="table" id="contentList">
						<thead>
							<tr>
								<th scope="col" style="width:1%" class="nowrap text-center d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
								</th>
								<td style="width:1%"  class="nowrap text-center">
									<?php echo HTMLHelper::_('grid.checkall'); ?>
								</td>
								<th scope="col" style="width:1%" class="nowrap text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="title nowrap">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="title nowrap d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_LANGUAGES_HEADING_TITLE_NATIVE', 'a.title_native', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" style="width:10%" class="nowrap text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_LANGUAGES_HEADING_LANG_TAG', 'a.lang_code', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" style="width:10%" class="nowrap text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_LANGUAGES_HEADING_LANG_CODE', 'a.sef', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" style="width:10%" class="nowrap d-none d-md-table-cell text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_LANGUAGES_HEADING_LANG_IMAGE', 'a.image', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" style="width:10%" class="nowrap d-none d-md-table-cell text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" style="width:10%" class="nowrap d-none d-md-table-cell text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_LANGUAGES_HEADING_HOMEPAGE', 'l.home', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" style="width:5%" class="nowrap d-none d-md-table-cell text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.lang_id', $listDirn, $listOrder); ?>
								</th>
							</tr>
						</thead>
						<tbody>
						<?php
						foreach ($this->items as $i => $item) :
							$canCreate = $user->authorise('core.create',     'com_languages');
							$canEdit   = $user->authorise('core.edit',       'com_languages');
							$canChange = $user->authorise('core.edit.state', 'com_languages');
						?>
							<tr class="row<?php echo $i % 2; ?>">
								<td class="order nowrap text-center d-none d-md-table-cell">
									<?php if ($canChange) :
										$disableClassName = '';
										$disabledLabel	  = '';

										if (!$saveOrder) :
											$disabledLabel    = Text::_('JORDERINGDISABLED');
											$disableClassName = 'inactive tip-top';
										endif; ?>
										<span class="sortable-handler hasTooltip <?php echo $disableClassName; ?>" title="<?php echo $disabledLabel; ?>">
											<span class="icon-menu" aria-hidden="true"></span>
										</span>
										<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order">
									<?php else : ?>
										<span class="sortable-handler inactive">
											<span class="icon-menu" aria-hidden="true"></span>
										</span>
									<?php endif; ?>
								</td>
								<td>
									<?php echo HTMLHelper::_('grid.id', $i, $item->lang_id); ?>
								</td>
								<td class="text-center">
									<?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'languages.', $canChange); ?>
								</td>
								<th scope="row">
									<span class="editlinktip hasTooltip" title="<?php echo HTMLHelper::_('tooltipText', Text::_('JGLOBAL_EDIT_ITEM'), $item->title, 0); ?>">
									<?php if ($canEdit) : ?>
										<a href="<?php echo Route::_('index.php?option=com_languages&task=language.edit&lang_id=' . (int) $item->lang_id); ?>" title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo $this->escape(addslashes($item->title)); ?>">
											<span class="fa fa-pencil-square mr-2" aria-hidden="true"></span><?php echo $this->escape($item->title); ?></a>
									<?php else : ?>
										<?php echo $this->escape($item->title); ?>
									<?php endif; ?>
									</span>
								</th>
								<td class="d-none d-md-table-cell">
									<?php echo $this->escape($item->title_native); ?>
								</td>
								<td class="text-center">
									<?php echo $this->escape($item->lang_code); ?>
								</td>
								<td class="text-center">
									<?php echo $this->escape($item->sef); ?>
								</td>
								<td class="d-none d-md-table-cell text-center">
									<?php if ($item->image) : ?>
										<?php echo HTMLHelper::_('image', 'mod_languages/' . $item->image . '.gif', $item->image, null, true); ?>&nbsp;<?php echo $this->escape($item->image); ?>
									<?php else : ?>
										<?php echo Text::_('JNONE'); ?>
									<?php endif; ?>
								</td>
								<td class="d-none d-md-table-cell text-center">
									<?php echo $this->escape($item->access_level); ?>
								</td>
								<td class="d-none d-md-table-cell text-center">
									<?php echo ($item->home == '1') ? Text::_('JYES') : Text::_('JNO'); ?>
								</td>
								<td class="d-none d-md-table-cell text-center">
									<?php echo $this->escape($item->lang_id); ?>
								</td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>

					<?php // load the pagination. ?>
					<?php echo $this->pagination->getListFooter(); ?>

				<?php endif; ?>
				<input type="hidden" name="task" value="">
				<input type="hidden" name="boxchecked" value="0">
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
