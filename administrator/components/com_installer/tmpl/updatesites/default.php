<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('behavior.multiselect');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<div id="installer-manage" class="clearfix">
	<form action="<?php echo Route::_('index.php?option=com_installer&view=updatesites'); ?>" method="post" name="adminForm" id="adminForm">
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
					<table class="table">
						<thead>
							<tr>
								<td style="width:1%" class="text-center">
									<?php echo HTMLHelper::_('grid.checkall'); ?>
								</td>
								<th scope="col" style="width:1%" class="nowrap text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'enabled', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="nowrap">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_UPDATESITE_NAME', 'update_site_name', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" style="width:20%" class="nowrap d-none d-md-table-cell text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_NAME', 'name', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" style="width:10%" class="d-none d-md-table-cell text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_LOCATION', 'client_translated', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" style="width:10%" class="d-none d-md-table-cell text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_TYPE', 'type_translated', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" style="width:10%" class="d-none d-md-table-cell text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_FOLDER', 'folder_translated', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" style="width:5%" class="nowrap d-none d-md-table-cell text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'update_site_id', $listDirn, $listOrder); ?>
								</th>
							</tr>
						</thead>
						<tbody>
						<?php foreach ($this->items as $i => $item) : ?>
							<tr class="row<?php echo $i % 2; if ($item->enabled == 2) echo ' protected'; ?>">
								<td class="text-center">
									<?php echo HTMLHelper::_('grid.id', $i, $item->update_site_id); ?>
								</td>
								<td class="text-center">
									<?php if (!$item->element) : ?>
										<strong>X</strong>
									<?php else : ?>
										<?php echo HTMLHelper::_('InstallerHtml.Updatesites.state', $item->enabled, $i, $item->enabled < 2, 'cb'); ?>
									<?php endif; ?>
								</td>
								<th scope="row">
									<label for="cb<?php echo $i; ?>">
										<?php echo Text::_($item->update_site_name); ?>
										<br>
										<span class="small break-word">
											<a href="<?php echo $item->location; ?>" target="_blank" rel="noopener noreferrer"><?php echo $this->escape($item->location); ?></a>
										</span>
									</label>
								</th>
								<td class="d-none d-md-table-cell text-center">
									<span class="bold hasTooltip" title="<?php echo HTMLHelper::_('tooltipText', $item->name, $item->description, 0); ?>">
										<?php echo $item->name; ?>
									</span>
								</td>
								<td class="d-none d-md-table-cell text-center">
									<?php echo $item->client_translated; ?>
								</td>
								<td class="d-none d-md-table-cell text-center">
									<?php echo $item->type_translated; ?>
								</td>
								<td class="d-none d-md-table-cell text-center">
									<?php echo $item->folder_translated; ?>
								</td>
								<td class="d-none d-md-table-cell text-center">
									<?php echo $item->update_site_id; ?>
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
</div>
