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
class PlgOverridesOverride extends CMSPlugin
{

	/**
	 * Application object.
	 *
	 */
	protected $app;

	/**
	 * Constructor.
	 *
	 * @param   object  &$subject  The object to observe -- event dispatcher.
	 * @param   object  $config    An optional associative array of configuration settings.
	 *
	 * @since   __VERSION__
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		// If we are not on admin don't process.
		if ($this->app->isClient('site'))
		{
			return;
		}
	}

	/**
	 * Method to get com_templates model instance.
	 *
	 * @param   string  $name    The model name. Optional
	 *
	 * @param		string  $prefix  The class prefix. Optional
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
	 * Method to prepare changed or updated core file.
	 *
	 * @return  array  A list of changed files.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getUpdatedFiles()
	{
		$after  = $this->afterEventFiles;
		$before = $this->beforeEventFiles;

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

		return $result;
	}

	/**
	 * Method to get core list of override files.
	 *
	 * @return  array  The list of core files.
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
	 * @since   __DEPLOY_VERSION__
	 */
	public function onExtensionBeforeUpdate()
	{
		$this->beforeEventFiles = $this->getOverrideCoreList();
	}

	/**
	 * Event after extension update.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onExtensionAfterUpdate()
	{
		$this->afterEventFiles = $this->getOverrideCoreList();
		$results = $this->getUpdatedFiles();
		$num = count($results);

		if ($num != 0)
		{
			$this->app->enqueueMessage(\JText::sprintf('PLG_OVERRIDES_OVERRIDE_UPDATED', $num), 'warning');
		}

		file_put_contents('exresult.txt', print_r($results, true));
	}

	/**
	 * Event before joomla update.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onJoomlaBeforeUpdate()
	{
		$this->beforeEventFiles = $this->getOverrideCoreList();
	}

	/**
	 * Event after joomla update.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
  public function onJoomlaAfterUpdate()
	{
		$this->afterEventFiles = $this->getOverrideCoreList();
		$results = $this->getUpdatedFiles();
		$num = count($results);

		if ($num != 0)
		{
			$this->app->enqueueMessage(\JText::sprintf('PLG_OVERRIDES_OVERRIDE_UPDATED', $num), 'warning');
		}

		file_put_contents('joresult.txt', print_r($results, true));
	}

	/**
	 * Event before install.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
  public function onInstallerBeforeInstaller()
	{
		$this->beforeEventFiles = $this->getOverrideCoreList();
	}

	/**
	 * Event after install.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
  public function onInstallerAfterInstaller()
	{
		$this->afterEventFiles = $this->getOverrideCoreList();
		$results = $this->getUpdatedFiles();
		$num = count($results);

		if ($num != 0)
		{
			$this->app->enqueueMessage(\JText::sprintf('PLG_OVERRIDES_OVERRIDE_UPDATED', $num), 'warning');
		}

		file_put_contents('inresult.txt', print_r($results, true));
	}
}
