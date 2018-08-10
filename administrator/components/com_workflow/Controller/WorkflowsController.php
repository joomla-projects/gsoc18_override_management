<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Workflow\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

/**
 * The first example class, this is in the same
 * package as declared at the start of file but
 * this example has a defined subpackage
 *
 * @since  __DEPLOY_VERSION__
 */
class WorkflowsController extends AdminController
{
	/**
	 * Constructor.
	 *
	 * @param   array                $config   An optional associative array of configuration settings.
	 *                                         Recognized key values include 'name', 'default_task', 'model_path', and
	 *                                         'view_path' (this list is not meant to be comprehensive).
	 * @param   MVCFactoryInterface  $factory  The factory.
	 * @param   CmsApplication       $app      The JApplication for the dispatcher
	 * @param   \JInput              $input    Input
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function __construct(array $config = array(), MVCFactoryInterface $factory = null, $app = null, $input = null)
	{
		parent::__construct($config, $factory, $app, $input);
		$this->registerTask('unsetDefault',	'setDefault');
	}

	/**
	 * Proxy for getModel
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  The array of possible config values. Optional.
	 *
	 * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel  The model.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getModel($name = 'Workflow', $prefix = 'Administrator', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Method to set the home property for a list of items
	 *
	 * @return  void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function setDefault()
	{
		// Check for request forgeries
		Session::checkToken('request') or die(Text::_('JINVALID_TOKEN'));

		// Get items to publish from the request.
		$cid   = $this->input->get('cid', array(), 'array');
		$data  = array('setDefault' => 1, 'unsetDefault' => 0);
		$task  = $this->getTask();
		$value = ArrayHelper::getValue($data, $task, 0, 'int');

		if (!$value)
		{
			$this->setMessage(Text::_('COM_WORKFLOW_DISABLE_DEFAULT'), 'warning');
			$this->setRedirect(
				Route::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_list
					. '&extension=' . $this->input->getCmd("extension"), false
				)
			);

			return;
		}

		if (empty($cid) || !is_array($cid))
		{
			$this->setMessage(Text::_('COM_WORKFLOW_NO_ITEM_SELECTED'), 'warning');
		}
		elseif (count($cid) > 1)
		{
			$this->setMessage(Text::_('COM_WORKFLOW_TOO_MANY_ITEMS'), 'error');
		}
		else
		{
			// Get the model.
			$model = $this->getModel();

			// Make sure the item ids are integers
			$id = (int) reset($cid);

			// Publish the items.
			if (!$model->setDefault($id, $value))
			{
				$this->setMessage($model->getError(), 'warning');
			}
			else
			{
				if ($value === 1)
				{
					$ntext = 'COM_WORKFLOW_ITEM_SET_DEFAULT';
				}
				else
				{
					$ntext = 'COM_WORKFLOW_ITEM_UNSET_DEFAULT';
				}

				$this->setMessage(Text::_($ntext, count($cid)));
			}
		}

		$this->setRedirect(
			Route::_(
				'index.php?option=' . $this->option . '&view=' . $this->view_list
				. '&extension=' . $this->input->getCmd("extension"), false
			)
		);
	}

	/**
	 * Deletes and returns correctly.
	 *
	 * @return  void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function delete()
	{
		parent::delete();
		$this->setRedirect(
			Route::_(
				'index.php?option=' . $this->option . '&view=' . $this->view_list
				. '&extension=' . $this->input->getCmd("extension"), false
			)
		);
	}
}
