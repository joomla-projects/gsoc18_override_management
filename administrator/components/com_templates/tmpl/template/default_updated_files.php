<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;

$plugin = PluginHelper::getPlugin('installer', 'override');
$params = new Registry($plugin->params);
$result = json_decode($params->get('overridefiles'), JSON_HEX_QUOT);

usort(
	$result,
	function ($a, $b)
	{
		return strcmp($a['template'], $b['template']);
	}
);
?>

<div class="row">
	<div class="col-md-12">
		<?php if (count($result) !== 0) : ?>
			<joomla-alert type="info" role="alert" class="joomla-alert--show">
				<span class="icon-info" aria-hidden="true"></span>
				<?php echo Text::_('COM_TEMPLATES_OVERRIDE_UPDATE_INFO'); ?>
			</joomla-alert>
			<table class="table table-striped">
				<thead>
					<tr>
						<th style="width:25%">
							<?php echo Text::_('COM_TEMPLATES_OVERRIDE_TEMPLATE_FILE'); ?>
						</th>
						<th>
							<?php echo Text::_('COM_TEMPLATES_OVERRIDE_TEMPLATE_ELEMENT'); ?>
						</th>
						<th>
							<?php echo Text::_('COM_TEMPLATES_OVERRIDE_LOCATION'); ?>
						</th>
						<th>
							<?php echo Text::_('COM_TEMPLATES_OVERRIDE_MODIFIED_DATE'); ?>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($result as $value) : ?>
						<tr>
							<td>
								<?php echo base64_decode($value['id']); ?>
							</td>
							<td>
								<span class="badge badge-success"><?php echo $value['template']; ?></span>
							</td>
							<td>
								<span class="badge badge-success"><?php echo $value['client']; ?></span>
							</td>
							<td>
								<?php if (empty($value['modifiedDate'])) : ?>
									<span class="badge badge-warning">Core file got removed</span>
								<?php endif; ?>
								<?php echo $value['modifiedDate']; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php else : ?>
			<joomla-alert type="success" role="alert" class="joomla-alert--show">
				<span class="icon-info" aria-hidden="true"></span>
				<?php echo Text::_('COM_TEMPLATES_OVERRIDE_UPTODATE'); ?>
			</joomla-alert>
		<?php endif; ?>
	</div>
</div>
