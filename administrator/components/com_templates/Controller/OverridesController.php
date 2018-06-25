<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Templates\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

/**
 * Template overrides controller class.
 *
 * @since  __DEPLOY_VERSION__
 */
class OverridesController extends BaseController
{
  /**
	 * Fetch and report updates in \JSON format, for A\JAX requests
	 * Created only for test.
	 *
	 * @return void
	 *
	 * @since 2.5
	 */
	public function ajax()
	{
		$app = $this->app;

		if (!\JSession::checkToken('get'))
		{
			$app->setHeader('status', 403, true);
			$app->sendHeaders();
			echo \JText::_('JINVALID_TOKEN');
			$app->close();
		}

		$session = \JFactory::getSession();

		$result = array();
		$updates = $session->get('override.result');

		if (count($updates) !== 0 && is_array($updates))
		{
			$result = $updates;
		}

		echo json_encode($result);

		$app->close();
	}
}
