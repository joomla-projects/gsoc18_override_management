<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

$published = $this->state->get('filter.published');
?>
<div class="modal hide fade" id="collapseModal">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">&#215;</button>
		<h3><?php echo Text::_('COM_CONTACT_BATCH_OPTIONS'); ?></h3>
	</div>
	<div class="modal-body modal-batch">
		<p><?php echo Text::_('COM_CONTACT_BATCH_TIP'); ?></p>
		<div class="row">
			<div class="form-group col-md-6">
				<div class="controls">
					<?php echo HTMLHelper::_('batch.language'); ?>
				</div>
			</div>
			<div class="form-group col-md-6">
				<div class="controls">
					<?php echo HTMLHelper::_('batch.access'); ?>
				</div>
			</div>
		</div>
		<div class="row">
		<?php if ($published >= 0) : ?>
			<div class="form-group col-md-6">
				<div class="controls">
					<?php echo HTMLHelper::_('batch.item', 'com_contact'); ?>
				</div>
			</div>
		<?php endif; ?>
		<div class="form-group col-md-6">
			<div class="controls">
				<?php echo HTMLHelper::_('batch.tag'); ?>
			</div>
		</div>
		<div class="row">
			<div class="control-group">
				<div class="controls">
					<?php echo HTMLHelper::_('batch.user'); ?>
				</div>
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<button class="btn btn-secondary" type="button" onclick="document.getElementById('batch-category-id').value='';document.getElementById('batch-access').value='';document.getElementById('batch-language-id').value='';document.getElementById('batch-user-id').value='';document.getElementById('batch-tag-id').value=''" data-dismiss="modal">
			<?php echo Text::_('JCANCEL'); ?>
		</button>
		<button class="btn btn-primary" type="submit" onclick="Joomla.submitbutton('contact.batch');">
			<?php echo Text::_('JGLOBAL_BATCH_PROCESS'); ?>
		</button>
	</div>
</div>
