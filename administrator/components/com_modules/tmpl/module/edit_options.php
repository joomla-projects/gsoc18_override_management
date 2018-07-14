<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

?>
<?php
	echo HTMLHelper::_('bootstrap.startAccordion', 'moduleOptions', array('active' => 'collapse0'));
	$fieldSets = $this->form->getFieldsets('params');
	$i = 0;

	foreach ($fieldSets as $name => $fieldSet) :
		$label = !empty($fieldSet->label) ? $fieldSet->label : 'COM_MODULES_' . $name . '_FIELDSET_LABEL';
		$class = isset($fieldSet->class) && !empty($fieldSet->class) ? $fieldSet->class : '';

		echo HTMLHelper::_('bootstrap.addSlide', 'moduleOptions', Text::_($label), 'collapse' . ($i++), $class);
			if (isset($fieldSet->description) && trim($fieldSet->description)) :
				echo '<p class="tip">' . $this->escape(Text::_($fieldSet->description)) . '</p>';
			endif;
			?>
				<?php foreach ($this->form->getFieldset($name) as $field) : ?>
					<div class="control-group">
						<div class="control-label">
							<?php echo $field->label; ?>
						</div>
						<div class="controls">
							<?php echo $field->input; ?>
						</div>
					</div>
				<?php endforeach;
		echo HTMLHelper::_('bootstrap.endSlide');
	endforeach;
echo HTMLHelper::_('bootstrap.endAccordion');
