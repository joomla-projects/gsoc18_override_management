<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

echo HTMLHelper::_('bootstrap.startAccordion', 'fieldOptions', array('active' => 'collapse0'));

$fieldSets = $this->form->getFieldsets('params');
$i         = 0;
?>
<?php foreach ($fieldSets as $name => $fieldSet) : ?>
	<?php $label = !empty($fieldSet->label) ? $fieldSet->label : 'COM_FIELDS_' . $name . '_FIELDSET_LABEL'; ?>
	<?php echo HTMLHelper::_('bootstrap.addSlide', 'fieldOptions', Text::_($label), 'collapse' . ($i++)); ?>
	<?php if (isset($fieldSet->description) && trim($fieldSet->description)) : ?>
		<?php echo '<p class="tip">' . $this->escape(Text::_($fieldSet->description)) . '</p>'; ?>
	<?php endif; ?>
	<?php foreach ($this->form->getFieldset($name) as $field) : ?>
		<div class="control-group">
			<div class="control-label">
				<?php echo $field->label; ?>
			</div>
			<div class="controls">
				<?php echo $field->input; ?>
			</div>
		</div>
	<?php endforeach; ?>

	<?php if ($name == 'basic'): ?>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('note'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('note'); ?>
			</div>
		</div>
	<?php endif; ?>
	<?php echo HTMLHelper::_('bootstrap.endSlide'); ?>
<?php endforeach; ?>
<?php echo HTMLHelper::_('bootstrap.endAccordion'); ?>
