<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Overrides.Override
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;

/**
 * Override Plugin
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgInstallerOverride extends CMSPlugin
{
	/**
	 * Application object.
	 *
	 */
	protected $app;

	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;

	/**
	 * Database object
	 *
	 * @var    JDatabaseDriver
	 * @since  __DEPLOY_VERSION__
	 */
	protected $db;

	/**
	 * Method to get com_templates model instance.
	 *
	 * @param   string  $name    The model name. Optional
	 * @param   string  $prefix  The class prefix. Optional
	 *
	 * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel The model.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getModel($name = 'Template', $prefix = 'Administrator')
	{
		$app = Factory::getApplication();
		$model = $app->bootComponent('com_templates')->createMVCFactory($app)->createModel($name, $prefix);

		return $model;
	}

	/**
	 * Purges session array.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function purge()
	{
		// Delete stored session value.
		$session = Factory::getSession();
		$session->clear('override.beforeEventFiles');
		$session->clear('override.afterEventFiles');
	}

	/**
	 * Method to store files before event.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function storeBeforeEventFiles()
	{
		// Get session instance.
		$session = Factory::getSession();

		// Delete stored session value.
		$this->purge();

		// Get list and store in session.
		$list = $this->getOverrideCoreList();
		$session->set('override.beforeEventFiles', $list);
	}

	/**
	 * Method to store files after event.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function storeAfterEventFiles()
	{
		// Get session instance
		$session = Factory::getSession();

		// Get list and store in session.
		$list = $this->getOverrideCoreList();
		$session->set('override.afterEventFiles', $list);
	}

	/**
	 * Method to prepare changed or updated core file.
	 *
	 * @return   array  A list of changed files.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getUpdatedFiles()
	{
		// Get session instance
		$session = Factory::getSession();

		$after  = $session->get('override.afterEventFiles');
		$before = $session->get('override.beforeEventFiles');
		$size1  = count($after);
		$size2  = count($before);

		$result = null;

		if ($size1 === $size2)
		{
			$result = array();

			for ($i = 0; $i <= $size1; $i++)
			{
				if ($after[$i]->coreFile !== $before[$i]->coreFile)
				{
					$result[] = $after[$i];
				}
			}
		}

		$this->params->set('overridefiles', json_encode($result, JSON_HEX_QUOT));
		$this->saveParams();

		return $result;
	}

	/**
	 * Method to get core list of override files.
	 *
	 * @return   array  The list of core files.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getOverrideCoreList()
	{
		// Get template model
		$templateModel = $this->getModel();
		$result = $templateModel->getCoreList();

		return $result;
	}

	/**
	 * Event before extension update.
	 *
	 * @return   void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onExtensionBeforeUpdate()
	{
		$this->storeBeforeEventFiles();
	}

	/**
	 * Event after extension update.
	 *
	 * @return   void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onExtensionAfterUpdate()
	{
		$this->storeAfterEventFiles();
		$results = $this->getUpdatedFiles();
		$num = count($results);

		if ($num != 0)
		{
			$span = '<span class="badge badge-light">' . $num . '</span>';
			$this->app->enqueueMessage(\JText::sprintf('PLG_INSTALLER_OVERRIDE_FILE_UPDATED', $span), 'notice');
		}
	}

	/**
	 * Event before joomla update.
	 *
	 * @return   void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onJoomlaBeforeUpdate()
	{
		$this->storeBeforeEventFiles();
	}

	/**
	 * Event after joomla update.
	 *
	 * @return   void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onJoomlaAfterUpdate()
	{
		$this->storeAfterEventFiles();
		$results = $this->getUpdatedFiles();
		$num = count($results);

		if ($num != 0)
		{
			$span = '<span class="badge badge-light">' . $num . '</span>';
			$this->app->enqueueMessage(\JText::sprintf('PLG_INSTALLER_OVERRIDE_FILE_UPDATED', $span), 'notice');
		}
	}

	/**
	 * Event before install.
	 *
	 * @return   void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onInstallerBeforeInstaller()
	{
		$this->storeBeforeEventFiles();
	}

	/**
	 * Event after install.
	 *
	 * @return   void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onInstallerAfterInstaller()
	{
		$this->storeAfterEventFiles();
		$results = $this->getUpdatedFiles();
		$num = count($results);

		if ($num != 0)
		{
			$span = '<span class="badge badge-light">' . $num . '</span>';
			$this->app->enqueueMessage(\JText::sprintf('PLG_INSTALLER_OVERRIDE_FILE_UPDATED', $span), 'notice');
		}
	}

	/**
	 * Save the plugin parameters
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function saveParams()
	{
		$query = $this->db->getQuery(true)
				->update($this->db->quoteName('#__extensions'))
				->set($this->db->quoteName('params') . ' = ' . $this->db->quote($this->params->toString('JSON')))
				->where($this->db->quoteName('type') . ' = ' . $this->db->quote('plugin'))
				->where($this->db->quoteName('folder') . ' = ' . $this->db->quote('installer'))
				->where($this->db->quoteName('element') . ' = ' . $this->db->quote('override'));

		try
		{
			// Lock the tables to prevent multiple plugin executions causing a race condition
			$this->db->lockTable('#__extensions');
		}
		catch (Exception $e)
		{
			// If we can't lock the tables it's too risky to continue execution
			return false;
		}

		try
		{
			// Update the plugin parameters
			$result = $this->db->setQuery($query)->execute();
		}
		catch (Exception $exc)
		{
			// If we failed to execute
			$this->db->unlockTables();
			$result = false;
		}

		try
		{
			// Unlock the tables after writing
			$this->db->unlockTables();
		}
		catch (Exception $e)
		{
			// If we can't lock the tables assume we have somehow failed
			$result = false;
		}

		return $result;
	}
}
