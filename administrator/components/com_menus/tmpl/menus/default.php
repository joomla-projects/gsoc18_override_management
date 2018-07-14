<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

// Include the component HTML helpers.
HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');

HTMLHelper::_('behavior.multiselect');

$uri       = Uri::getInstance();
$return    = base64_encode($uri);
$user      = Factory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$modMenuId = (int) $this->get('ModMenuId');
$itemIds   = [];

foreach ($this->items as $item)
{
	if ($user->authorise('core.edit', 'com_menus'))
	{
		$itemIds[] = $item->id;
	}
}

Factory::getDocument()->addScriptOptions('menus-default', ['items' => $itemIds]);
HTMLHelper::_('script', 'com_menus/admin-menus-default.min.js', array('version' => 'auto', 'relative' => true));
?>
<form action="<?php echo Route::_('index.php?option=com_menus&view=menus'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div id="j-sidebar-container" class="col-md-2">
			<?php echo $this->sidebar; ?>
		</div>
		<div class="col-md-10">
			<div id="j-main-container" class="j-main-container">
				<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this, 'options' => array('filterButton' => false))); ?>
				<?php if (empty($this->items)) : ?>
					<joomla-alert type="warning"><?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></joomla-alert>
				<?php else : ?>
					<table class="table" id="menuList">
						<thead>
							<tr>
								<th style="width:1%">
									<?php echo HTMLHelper::_('grid.checkall'); ?>
								</th>
								<th>
									<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
								</th>
								<th style="width:10%" class="nowrap text-center">
									<span class="icon-publish" aria-hidden="true"></span>
									<span class="d-none d-md-inline"><?php echo Text::_('COM_MENUS_HEADING_PUBLISHED_ITEMS'); ?></span>
								</th>
								<th style="width:10%" class="nowrap text-center">
									<span class="icon-unpublish" aria-hidden="true"></span>
									<span class="d-none d-md-inline"><?php echo Text::_('COM_MENUS_HEADING_UNPUBLISHED_ITEMS'); ?></span>
								</th>
								<th style="width:10%" class="nowrap text-center">
									<span class="icon-trash" aria-hidden="true"></span>
									<span class="d-none d-md-inline"><?php echo Text::_('COM_MENUS_HEADING_TRASHED_ITEMS'); ?></span>
								</th>
								<th style="width:10%" class="nowrap text-center">
									<span class="icon-cube" aria-hidden="true"></span>
									<span class="d-none d-md-inline"><?php echo Text::_('COM_MENUS_HEADING_LINKED_MODULES'); ?></span>
								</th>
								<th style="width:5%" class="nowrap d-none d-md-table-cell text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
								</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<td colspan="15">
									<?php echo $this->pagination->getListFooter(); ?>
								</td>
							</tr>
						</tfoot>
						<tbody>
						<?php foreach ($this->items as $i => $item) :
							$canEdit        = $user->authorise('core.edit',   'com_menus.menu.' . (int) $item->id);
							$canManageItems = $user->authorise('core.manage', 'com_menus.menu.' . (int) $item->id);
						?>
							<tr class="row<?php echo $i % 2; ?>">
								<td class="text-center">
									<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
								</td>
								<td>
									<?php if ($canManageItems) : ?>
										<a href="<?php echo Route::_('index.php?option=com_menus&view=items&menutype=' . $item->menutype); ?>" title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo $this->escape(addslashes($item->title)); ?>">
											<span class="fa fa-pencil-square mr-2" aria-hidden="true"></span><?php echo $this->escape($item->title); ?></a>
									<?php else : ?>
										<?php echo $this->escape($item->title); ?>
									<?php endif; ?>
									<div class="small">
										<?php echo Text::_('COM_MENUS_MENU_MENUTYPE_LABEL'); ?>:
										<?php if ($canEdit) : ?>
											<a href="<?php echo Route::_('index.php?option=com_menus&task=menu.edit&id=' . $item->id); ?>" title="<?php echo $this->escape($item->description); ?>">
											<?php echo $this->escape($item->menutype); ?></a>
										<?php else : ?>
											<?php echo $this->escape($item->menutype); ?>
										<?php endif; ?>
									</div>
								</td>
								<td class="text-center btns">
									<?php if ($canManageItems) : ?>
										<a class="badge<?php echo ($item->count_published > 0) ? ' badge-success' : ' badge-secondary'; ?>" href="<?php echo Route::_('index.php?option=com_menus&view=items&menutype=' . $item->menutype . '&filter[published]=1'); ?>">
											<?php echo $item->count_published; ?></a>
									<?php else : ?>
										<span class="badge<?php echo ($item->count_published > 0) ? ' badge-success' : ' badge-secondary'; ?>">
											<?php echo $item->count_published; ?></span>
									<?php endif; ?>
								</td>
								<td class="text-center btns">
									<?php if ($canManageItems) : ?>
										<a class="badge<?php echo ($item->count_unpublished > 0) ? ' badge-danger' : ' badge-secondary'; ?>" href="<?php echo Route::_('index.php?option=com_menus&view=items&menutype=' . $item->menutype . '&filter[published]=0'); ?>">
											<?php echo $item->count_unpublished; ?></a>
									<?php else : ?>
										<span class="badge<?php echo ($item->count_unpublished > 0) ? ' badge-danger' : ' badge-secondary'; ?>">
											<?php echo $item->count_unpublished; ?></span>
									<?php endif; ?>
								</td>
								<td class="text-center btns">
									<?php if ($canManageItems) : ?>
										<a class="badge<?php echo ($item->count_trashed > 0) ? ' badge-danger' : ' badge-secondary'; ?>" href="<?php echo Route::_('index.php?option=com_menus&view=items&menutype=' . $item->menutype . '&filter[published]=-2'); ?>">
											<?php echo $item->count_trashed; ?></a>
									<?php else : ?>
										<span class="badge<?php echo ($item->count_trashed > 0) ? ' badge-danger' : ' badge-secondary'; ?>">
											<?php echo $item->count_trashed; ?></span>
									<?php endif; ?>
								</td>
								<td class="text-center">
									<?php if (isset($this->modules[$item->menutype])) : ?>
										<div class="dropdown">
											<a href="#" class="btn btn-secondary btn-sm dropdown-toggle" data-toggle="dropdown">
												<?php echo Text::_('COM_MENUS_MODULES'); ?>
												<span class="caret"></span>
											</a>
											<div class="dropdown-menu dropdown-menu-right">
												<?php foreach ($this->modules[$item->menutype] as &$module) : ?>
													<?php if ($user->authorise('core.edit', 'com_modules.module.' . (int) $module->id)) : ?>
														<?php $link = Route::_('index.php?option=com_modules&task=module.edit&id=' . $module->id . '&return=' . $return . '&tmpl=component&layout=modal'); ?>
														<a class="dropdown-item" href="#moduleEdit<?php echo $module->id; ?>Modal" role="button" class="button" data-toggle="modal" title="<?php echo Text::_('COM_MENUS_EDIT_MODULE_SETTINGS'); ?>">
															<?php echo Text::sprintf('COM_MENUS_MODULE_ACCESS_POSITION', $this->escape($module->title), $this->escape($module->access_title), $this->escape($module->position)); ?></a>
													<?php else : ?>
                                                        <a href="#" class="disabled" disabled="disabled">
                                                            <span class="dropdown-item"><?php echo Text::sprintf('COM_MENUS_MODULE_ACCESS_POSITION', $this->escape($module->title), $this->escape($module->access_title), $this->escape($module->position)); ?></span>
                                                        </a>
													<?php endif; ?>
												<?php endforeach; ?>
											</div>
										 </div>
										<?php foreach ($this->modules[$item->menutype] as &$module) : ?>
											<?php if ($user->authorise('core.edit', 'com_modules.module.' . (int) $module->id)) : ?>
												<?php $link = Route::_('index.php?option=com_modules&task=module.edit&id=' . $module->id . '&return=' . $return . '&tmpl=component&layout=modal'); ?>
												<?php echo HTMLHelper::_(
														'bootstrap.renderModal',
														'moduleEdit' . $module->id . 'Modal',
														array(
															'title'       => Text::_('COM_MENUS_EDIT_MODULE_SETTINGS'),
															'backdrop'    => 'static',
															'keyboard'    => false,
															'closeButton' => false,
															'url'         => $link,
															'height'      => '400px',
															'width'       => '800px',
															'bodyHeight'  => 70,
															'modalWidth'  => 80,
															'footer'      => '<a type="button" class="btn btn-secondary" data-dismiss="modal" aria-hidden="true"'
																	. ' onclick="jQuery(\'#moduleEdit' . $module->id . 'Modal iframe\').contents().find(\'#closeBtn\').click();">'
																	. Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</a>'
																	. '<button type="button" class="btn btn-primary" aria-hidden="true"'
																	. ' onclick="jQuery(\'#moduleEdit' . $module->id . 'Modal iframe\').contents().find(\'#saveBtn\').click();">'
																	. Text::_('JSAVE') . '</button>'
																	. '<button type="button" class="btn btn-success" aria-hidden="true"'
																	. ' onclick="jQuery(\'#moduleEdit' . $module->id . 'Modal iframe\').contents().find(\'#applyBtn\').click();">'
																	. Text::_('JAPPLY') . '</button>',
														)
													); ?>
											<?php endif; ?>
										<?php endforeach; ?>
									<?php elseif ($modMenuId) : ?>
										<?php $link = Route::_('index.php?option=com_modules&task=module.add&eid=' . $modMenuId . '&params[menutype]=' . $item->menutype . '&tmpl=component&layout=modal'); ?>
										<a class="btn btn-sm btn-primary" data-toggle="modal" role="button" href="#moduleAddModal"><?php echo Text::_('COM_MENUS_ADD_MENU_MODULE'); ?></a>
										<?php echo HTMLHelper::_(
												'bootstrap.renderModal',
												'moduleAddModal',
												array(
													'title'       => Text::_('COM_MENUS_ADD_MENU_MODULE'),
													'backdrop'    => 'static',
													'keyboard'    => false,
													'closeButton' => false,
													'url'         => $link,
													'height'      => '400px',
													'width'       => '800px',
													'bodyHeight'  => 70,
													'modalWidth'  => 80,
													'footer'      => '<a type="button" class="btn btn-secondary" data-dismiss="modal" aria-hidden="true"'
															. ' onclick="jQuery(\'#moduleAddModal iframe\').contents().find(\'#closeBtn\').click();">'
															. Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</a>'
															. '<button type="button" class="btn btn-primary" aria-hidden="true"'
															. ' onclick="jQuery(\'#moduleAddModal iframe\').contents().find(\'#saveBtn\').click();">'
															. Text::_('JSAVE') . '</button>'
															. '<button type="button" class="btn btn-success" aria-hidden="true"'
															. ' onclick="jQuery(\'#moduleAddModal iframe\').contents().find(\'#applyBtn\').click();">'
															. Text::_('JAPPLY') . '</button>',
												)
											); ?>
									<?php endif; ?>
								</td>
								<td class="d-none d-md-table-cell text-center">
									<?php echo $item->id; ?>
								</td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>

				<input type="hidden" name="task" value="">
				<input type="hidden" name="boxchecked" value="0">
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
