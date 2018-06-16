<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Joomlaupdate\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Authentication\Authentication;
use Joomla\CMS\Extension\ExtensionHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

/**
 * Joomla! update overview Model
 *
 * @since  2.5.4
 */
class UpdateModel extends BaseDatabaseModel
{
	/**
	 * @var   array  $updateInformation  null
	 * Holds the update information evaluated in getUpdateInformation.
	 *
	 * @since 4.0.0
	 */
	private $updateInformation = null;

	/**
	 * Detects if the Joomla! update site currently in use matches the one
	 * configured in this component. If they don't match, it changes it.
	 *
	 * @return  void
	 *
	 * @since    2.5.4
	 */
	public function applyUpdateSite()
	{
		// Determine the intended update URL.
		$params = ComponentHelper::getParams('com_joomlaupdate');

		switch ($params->get('updatesource', 'nochange'))
		{
			// "Minor & Patch Release for Current version AND Next Major Release".
			case 'sts':
			case 'next':
				$updateURL = 'https://update.joomla.org/core/test/next_major_list.xml';
				break;

			// "Testing"
			case 'testing':
				$updateURL = 'https://update.joomla.org/core/test/next_major_list.xml';
				break;

			// "Custom"
			// TODO: check if the customurl is valid and not just "not empty".
			case 'custom':
				if (trim($params->get('customurl', '')) != '')
				{
					$updateURL = trim($params->get('customurl', ''));
				}
				else
				{
					return \JFactory::getApplication()->enqueueMessage(\JText::_('COM_JOOMLAUPDATE_CONFIG_UPDATESOURCE_CUSTOM_ERROR'), 'error');
				}
				break;

			/**
			 * "Minor & Patch Release for Current version (recommended and default)".
			 * The commented "case" below are for documenting where 'default' and legacy options falls
			 * case 'default':
			 * case 'lts':
			 * case 'nochange':
			 */
			default:
				$updateURL = 'https://update.joomla.org/core/test/next_major_list.xml';
		}

		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('us') . '.*')
			->from($db->quoteName('#__update_sites_extensions') . ' AS ' . $db->quoteName('map'))
			->join(
				'INNER', $db->quoteName('#__update_sites') . ' AS ' . $db->quoteName('us')
				. ' ON (' . 'us.update_site_id = map.update_site_id)'
			)
			->where('map.extension_id = ' . $db->quote(700));
		$db->setQuery($query);
		$update_site = $db->loadObject();

		if ($update_site->location != $updateURL)
		{
			// Modify the database record.
			$update_site->last_check_timestamp = 0;
			$update_site->location = $updateURL;
			$db->updateObject('#__update_sites', $update_site, 'update_site_id');

			// Remove cached updates.
			$query->clear()
				->delete($db->quoteName('#__updates'))
				->where($db->quoteName('extension_id') . ' = ' . $db->quote('700'));
			$db->setQuery($query);
			$db->execute();
		}
	}

	/**
	 * Makes sure that the Joomla! update cache is up-to-date.
	 *
	 * @param   boolean  $force  Force reload, ignoring the cache timeout.
	 *
	 * @return  void
	 *
	 * @since    2.5.4
	 */
	public function refreshUpdates($force = false)
	{
		if ($force)
		{
			$cache_timeout = 0;
		}
		else
		{
			$update_params = ComponentHelper::getParams('com_installer');
			$cache_timeout = $update_params->get('cachetimeout', 6, 'int');
			$cache_timeout = 3600 * $cache_timeout;
		}

		$updater = \JUpdater::getInstance();

		$reflection = new \ReflectionObject($updater);
		$reflectionMethod = $reflection->getMethod('findUpdates');
		$methodParameters = $reflectionMethod->getParameters();

		if (count($methodParameters) >= 4)
		{
			// Reinstall support is available in \JUpdater
			$updater->findUpdates(700, $cache_timeout, \JUpdater::STABILITY_STABLE, true);
		}
		else
		{
			$updater->findUpdates(700, $cache_timeout, \JUpdater::STABILITY_STABLE);
		}
	}

	/**
	 * Returns an array with the Joomla! update information.
	 *
	 * @return  array
	 *
	 * @since   2.5.4
	 */
	public function getUpdateInformation()
	{
		if ($this->updateInformation)
		{
			return $this->updateInformation;
		}

		// Initialise the return array.
		$this->updateInformation = array(
			'installed' => \JVERSION,
			'latest'    => null,
			'object'    => null,
			'hasUpdate' => false
		);

		// Fetch the update information from the database.
		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__updates'))
			->where($db->quoteName('extension_id') . ' = ' . $db->quote(700));
		$db->setQuery($query);
		$updateObject = $db->loadObject();

		if (is_null($updateObject))
		{
			$this->updateInformation['latest'] = \JVERSION;

			return $this->updateInformation;
		}

		$this->updateInformation['latest']    = $updateObject->version;
		$this->updateInformation['hasUpdate'] = $updateObject->version != \JVERSION;

		// Fetch the full update details from the update details URL.
		jimport('joomla.updater.update');
		$update = new \JUpdate;
		$update->loadFromXML($updateObject->detailsurl);

		$this->updateInformation['object'] = $update;

		return $this->updateInformation;
	}

	/**
	 * Returns an array with the configured FTP options.
	 *
	 * @return  array
	 *
	 * @since   2.5.4
	 */
	public function getFTPOptions()
	{
		$config = \JFactory::getApplication()->getConfig();

		return array(
			'host'      => $config->get('ftp_host'),
			'port'      => $config->get('ftp_port'),
			'username'  => $config->get('ftp_user'),
			'password'  => $config->get('ftp_pass'),
			'directory' => $config->get('ftp_root'),
			'enabled'   => $config->get('ftp_enable'),
		);
	}

	/**
	 * Removes all of the updates from the table and enable all update streams.
	 *
	 * @return  boolean  Result of operation.
	 *
	 * @since   3.0
	 */
	public function purge()
	{
		$db = $this->getDbo();

		// Modify the database record
		$update_site = new \stdClass;
		$update_site->last_check_timestamp = 0;
		$update_site->enabled = 1;
		$update_site->update_site_id = 1;
		$db->updateObject('#__update_sites', $update_site, 'update_site_id');

		$query = $db->getQuery(true)
			->delete($db->quoteName('#__updates'))
			->where($db->quoteName('update_site_id') . ' = ' . $db->quote('1'));
		$db->setQuery($query);

		if ($db->execute())
		{
			$this->_message = \JText::_('COM_JOOMLAUPDATE_CHECKED_UPDATES');

			return true;
		}
		else
		{
			$this->_message = \JText::_('COM_JOOMLAUPDATE_FAILED_TO_CHECK_UPDATES');

			return false;
		}
	}

	/**
	 * Downloads the update package to the site.
	 *
	 * @return  boolean|string  False on failure, basename of the file in any other case.
	 *
	 * @since   2.5.4
	 */
	public function download()
	{
		$updateInfo = $this->getUpdateInformation();
		$packageURL = $updateInfo['object']->downloadurl->_data;
		$sources    = $updateInfo['object']->get('downloadSources', array());
		$headers    = get_headers($packageURL, 1);

		// Follow the Location headers until the actual download URL is known
		while (isset($headers['Location']))
		{
			$packageURL = $headers['Location'];
			$headers    = get_headers($packageURL, 1);
		}

		// Remove protocol, path and query string from URL
		$basename = basename($packageURL);

		if (strpos($basename, '?') !== false)
		{
			$basename = substr($basename, 0, strpos($basename, '?'));
		}

		// Find the path to the temp directory and the local package.
		$config  = \JFactory::getConfig();
		$tempdir = $config->get('tmp_path');
		$target  = $tempdir . '/' . $basename;

		// Do we have a cached file?
		$exists = \JFile::exists($target);

		if (!$exists)
		{
			// Not there, let's fetch it.
			$mirror = 0;

			while (!($download = $this->downloadPackage($packageURL, $target)) && isset($sources[$mirror]))
			{
				$name       = $sources[$mirror];
				$packageURL = $name->url;
				$mirror++;
			}

			return $download;
		}
		else
		{
			// Is it a 0-byte file? If so, re-download please.
			$filesize = @filesize($target);

			if (empty($filesize))
			{
				$mirror = 0;

				while (!($download = $this->downloadPackage($packageURL, $target)) && isset($sources[$mirror]))
				{
					$name       = $sources[$mirror];
					$packageURL = $name->url;
					$mirror++;
				}

				return $download;
			}

			// Yes, it's there, skip downloading.
			return $basename;
		}
	}

	/**
	 * Downloads a package file to a specific directory
	 *
	 * @param   string  $url     The URL to download from
	 * @param   string  $target  The directory to store the file
	 *
	 * @return  boolean True on success
	 *
	 * @since   2.5.4
	 */
	protected function downloadPackage($url, $target)
	{
		\JLoader::import('helpers.download', JPATH_COMPONENT_ADMINISTRATOR);

		try
		{
			\JLog::add(\JText::sprintf('COM_JOOMLAUPDATE_UPDATE_LOG_URL', $url), \JLog::INFO, 'Update');
		}
		catch (\RuntimeException $exception)
		{
			// Informational log only
		}

		jimport('joomla.filesystem.file');

		// Make sure the target does not exist.
		\JFile::delete($target);

		// Download the package
		try
		{
			$result = \JHttpFactory::getHttp([], ['curl', 'stream'])->get($url);
		}
		catch (\RuntimeException $e)
		{
			return false;
		}

		if (!$result || ($result->code != 200 && $result->code != 310))
		{
			return false;
		}

		// Write the file to disk
		\JFile::write($target, $result->body);

		return basename($target);
	}

	/**
	 * Create restoration file.
	 *
	 * @param   string  $basename  Optional base path to the file.
	 *
	 * @return  boolean True if successful; false otherwise.
	 *
	 * @since  2.5.4
	 */
	public function createRestorationFile($basename = null)
	{
		// Load overrides plugin.
		\JPluginHelper::importPlugin('overrides');

		// Get a password
		$password = \JUserHelper::genRandomPassword(32);
		$app = \JFactory::getApplication();
		$app->setUserState('com_joomlaupdate.password', $password);

		// Trigger on before joomla event.
		$app->triggerEvent('onJoomlaBeforeUpdate', array($this));

		// Do we have to use FTP?
		$method = \JFactory::getApplication()->getUserStateFromRequest('com_joomlaupdate.method', 'method', 'direct', 'cmd');

		// Get the absolute path to site's root.
		$siteroot = JPATH_SITE;

		// If the package name is not specified, get it from the update info.
		if (empty($basename))
		{
			$updateInfo = $this->getUpdateInformation();
			$packageURL = $updateInfo['object']->downloadurl->_data;
			$basename = basename($packageURL);
		}

		// Get the package name.
		$config  = $app->getConfig();
		$tempdir = $config->get('tmp_path');
		$file    = $tempdir . '/' . $basename;

		$filesize = @filesize($file);
		$app->setUserState('com_joomlaupdate.password', $password);
		$app->setUserState('com_joomlaupdate.filesize', $filesize);

		$data = "<?php\ndefined('_AKEEBA_RESTORATION') or die('Restricted access');\n";
		$data .= '$restoration_setup = array(' . "\n";
		$data .= <<<ENDDATA
	'kickstart.security.password' => '$password',
	'kickstart.tuning.max_exec_time' => '5',
	'kickstart.tuning.run_time_bias' => '75',
	'kickstart.tuning.min_exec_time' => '0',
	'kickstart.procengine' => '$method',
	'kickstart.setup.sourcefile' => '$file',
	'kickstart.setup.destdir' => '$siteroot',
	'kickstart.setup.restoreperms' => '0',
	'kickstart.setup.filetype' => 'zip',
	'kickstart.setup.dryrun' => '0',
	'kickstart.setup.renamefiles' => array(),
	'kickstart.setup.postrenamefiles' => false
ENDDATA;

		if ($method != 'direct')
		{
			/*
			 * Fetch the FTP parameters from the request. Note: The password should be
			 * allowed as raw mode, otherwise something like !@<sdf34>43H% would be
			 * sanitised to !@43H% which is just plain wrong.
			 */
			$ftp_host = $app->input->get('ftp_host', '');
			$ftp_port = $app->input->get('ftp_port', '21');
			$ftp_user = $app->input->get('ftp_user', '');
			$ftp_pass = addcslashes($app->input->get('ftp_pass', '', 'raw'), "'\\");
			$ftp_root = $app->input->get('ftp_root', '');

			// Is the tempdir really writable?
			$writable = @is_writable($tempdir);

			if ($writable)
			{
				// Let's be REALLY sure.
				$fp = @fopen($tempdir . '/test.txt', 'w');

				if ($fp === false)
				{
					$writable = false;
				}
				else
				{
					fclose($fp);
					unlink($tempdir . '/test.txt');
				}
			}

			// If the tempdir is not writable, create a new writable subdirectory.
			if (!$writable)
			{
				$FTPOptions = \JClientHelper::getCredentials('ftp');
				$ftp = \JClientFtp::getInstance($FTPOptions['host'], $FTPOptions['port'], array(), $FTPOptions['user'], $FTPOptions['pass']);
				$dest = \JPath::clean(str_replace(JPATH_ROOT, $FTPOptions['root'], $tempdir . '/admintools'), '/');

				if (!@mkdir($tempdir . '/admintools'))
				{
					$ftp->mkdir($dest);
				}

				if (!@chmod($tempdir . '/admintools', 511))
				{
					$ftp->chmod($dest, 511);
				}

				$tempdir .= '/admintools';
			}

			// \Just in case the temp-directory was off-root, try using the default tmp directory.
			$writable = @is_writable($tempdir);

			if (!$writable)
			{
				$tempdir = JPATH_ROOT . '/tmp';

				// Does the JPATH_ROOT/tmp directory exist?
				if (!is_dir($tempdir))
				{
					\JFolder::create($tempdir, 511);
					$htaccessContents = "order deny,allow\ndeny from all\nallow from none\n";
					\JFile::write($tempdir . '/.htaccess', $htaccessContents);
				}

				// If it exists and it is unwritable, try creating a writable admintools subdirectory.
				if (!is_writable($tempdir))
				{
					$FTPOptions = \JClientHelper::getCredentials('ftp');
					$ftp = \JClientFtp::getInstance($FTPOptions['host'], $FTPOptions['port'], array(), $FTPOptions['user'], $FTPOptions['pass']);
					$dest = \JPath::clean(str_replace(JPATH_ROOT, $FTPOptions['root'], $tempdir . '/admintools'), '/');

					if (!@mkdir($tempdir . '/admintools'))
					{
						$ftp->mkdir($dest);
					}

					if (!@chmod($tempdir . '/admintools', 511))
					{
						$ftp->chmod($dest, 511);
					}

					$tempdir .= '/admintools';
				}
			}

			// If we still have no writable directory, we'll try /tmp and the system's temp-directory.
			$writable = @is_writable($tempdir);

			if (!$writable)
			{
				if (@is_dir('/tmp') && @is_writable('/tmp'))
				{
					$tempdir = '/tmp';
				}
				else
				{
					// Try to find the system temp path.
					$tmpfile = @tempnam('dummy', '');
					$systemp = @dirname($tmpfile);
					@unlink($tmpfile);

					if (!empty($systemp))
					{
						if (@is_dir($systemp) && @is_writable($systemp))
						{
							$tempdir = $systemp;
						}
					}
				}
			}

			$data .= <<<ENDDATA
	,
	'kickstart.ftp.ssl' => '0',
	'kickstart.ftp.passive' => '1',
	'kickstart.ftp.host' => '$ftp_host',
	'kickstart.ftp.port' => '$ftp_port',
	'kickstart.ftp.user' => '$ftp_user',
	'kickstart.ftp.pass' => '$ftp_pass',
	'kickstart.ftp.dir' => '$ftp_root',
	'kickstart.ftp.tempdir' => '$tempdir'
ENDDATA;
		}

		$data .= ');';

		// Remove the old file, if it's there...
		$configpath = JPATH_COMPONENT_ADMINISTRATOR . '/restoration.php';

		if (\JFile::exists($configpath))
		{
			\JFile::delete($configpath);
		}

		// Write new file. First try with \JFile.
		$result = \JFile::write($configpath, $data);

		// In case \JFile used FTP but direct access could help.
		if (!$result)
		{
			if (function_exists('file_put_contents'))
			{
				$result = @file_put_contents($configpath, $data);

				if ($result !== false)
				{
					$result = true;
				}
			}
			else
			{
				$fp = @fopen($configpath, 'wt');

				if ($fp !== false)
				{
					$result = @fwrite($fp, $data);

					if ($result !== false)
					{
						$result = true;
					}

					@fclose($fp);
				}
			}
		}

		return $result;
	}

	/**
	 * Runs the schema update SQL files, the PHP update script and updates the
	 * manifest cache and #__extensions entry. Essentially, it is identical to
	 * \JInstallerFile::install() without the file copy.
	 *
	 * @return  boolean True on success.
	 *
	 * @since   2.5.4
	 */
	public function finaliseUpgrade()
	{
		$installer = \JInstaller::getInstance();

		$manifest = $installer->isManifest(JPATH_MANIFESTS . '/files/joomla.xml');

		if ($manifest === false)
		{
			$installer->abort(\JText::_('JLIB_INSTALLER_ABORT_DETECTMANIFEST'));

			return false;
		}

		$installer->manifest = $manifest;

		$installer->setUpgrade(true);
		$installer->setOverwrite(true);

		$installer->extension = new \Joomla\CMS\Table\Extension(Factory::getDbo());
		$installer->extension->load(700);

		$installer->setAdapter($installer->extension->type);

		$installer->setPath('manifest', JPATH_MANIFESTS . '/files/joomla.xml');
		$installer->setPath('source', JPATH_MANIFESTS . '/files');
		$installer->setPath('extension_root', JPATH_ROOT);

		// Run the script file.
		\JLoader::register('JoomlaInstallerScript', JPATH_ADMINISTRATOR . '/components/com_admin/script.php');

		$manifestClass = new \JoomlaInstallerScript;

		ob_start();
		ob_implicit_flush(false);

		if ($manifestClass && method_exists($manifestClass, 'preflight'))
		{
			if ($manifestClass->preflight('update', $installer) === false)
			{
				$installer->abort(\JText::_('JLIB_INSTALLER_ABORT_FILE_INSTALL_CUSTOM_INSTALL_FAILURE'));

				return false;
			}
		}

		// Create msg object; first use here.
		$msg = ob_get_contents();
		ob_end_clean();

		// Get a database connector object.
		$db = $this->getDbo();

		/*
		 * Check to see if a file extension by the same name is already installed.
		 * If it is, then update the table because if the files aren't there
		 * we can assume that it was (badly) uninstalled.
		 * If it isn't, add an entry to extensions.
		 */
		$query = $db->getQuery(true)
			->select($db->quoteName('extension_id'))
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('type') . ' = ' . $db->quote('file'))
			->where($db->quoteName('element') . ' = ' . $db->quote('joomla'));
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (\RuntimeException $e)
		{
			// Install failed, roll back changes.
			$installer->abort(
				\JText::sprintf('JLIB_INSTALLER_ABORT_FILE_ROLLBACK', \JText::_('JLIB_INSTALLER_UPDATE'), $e->getMessage())
			);

			return false;
		}

		$id = $db->loadResult();
		$row = new \Joomla\CMS\Table\Extension(Factory::getDbo());

		if ($id)
		{
			// Load the entry and update the manifest_cache.
			$row->load($id);

			// Update name.
			$row->set('name', 'files_joomla');

			// Update manifest.
			$row->manifest_cache = $installer->generateManifestCache();

			if (!$row->store())
			{
				// Install failed, roll back changes.
				$installer->abort(
					\JText::sprintf('JLIB_INSTALLER_ABORT_FILE_ROLLBACK', \JText::_('JLIB_INSTALLER_UPDATE'), $row->getError())
				);

				return false;
			}
		}
		else
		{
			// Add an entry to the extension table with a whole heap of defaults.
			$row->set('name', 'files_joomla');
			$row->set('type', 'file');
			$row->set('element', 'joomla');

			// There is no folder for files so leave it blank.
			$row->set('folder', '');
			$row->set('enabled', 1);
			$row->set('protected', 0);
			$row->set('access', 0);
			$row->set('client_id', 0);
			$row->set('params', '');
			$row->set('manifest_cache', $installer->generateManifestCache());

			if (!$row->store())
			{
				// Install failed, roll back changes.
				$installer->abort(\JText::sprintf('JLIB_INSTALLER_ABORT_FILE_INSTALL_ROLLBACK', $row->getError()));

				return false;
			}

			// Set the insert id.
			$row->set('extension_id', $db->insertid());

			// Since we have created a module item, we add it to the installation step stack
			// so that if we have to rollback the changes we can undo it.
			$installer->pushStep(array('type' => 'extension', 'extension_id' => $row->extension_id));
		}

		$result = $installer->parseSchemaUpdates($manifest->update->schemas, $row->extension_id);

		if ($result === false)
		{
			// Install failed, rollback changes (message already logged by the installer).
			$installer->abort();

			return false;
		}

		// Start Joomla! 1.6.
		ob_start();
		ob_implicit_flush(false);

		if ($manifestClass && method_exists($manifestClass, 'update'))
		{
			if ($manifestClass->update($installer) === false)
			{
				// Install failed, rollback changes.
				$installer->abort(\JText::_('JLIB_INSTALLER_ABORT_FILE_INSTALL_CUSTOM_INSTALL_FAILURE'));

				return false;
			}
		}

		// Append messages.
		$msg .= ob_get_contents();
		ob_end_clean();

		// Clobber any possible pending updates.
		$update = new \Joomla\CMS\Table\Update(Factory::getDbo());
		$uid = $update->find(
			array('element' => 'joomla', 'type' => 'file', 'client_id' => '0', 'folder' => '')
		);

		if ($uid)
		{
			$update->delete($uid);
		}

		// And now we run the postflight.
		ob_start();
		ob_implicit_flush(false);

		if ($manifestClass && method_exists($manifestClass, 'postflight'))
		{
			$manifestClass->postflight('update', $installer);
		}

		// Append messages.
		$msg .= ob_get_contents();
		ob_end_clean();

		if ($msg != '')
		{
			$installer->set('extension_message', $msg);
		}

		// Refresh versionable assets cache.
		\JFactory::getApplication()->flushAssets();

		return true;
	}

	/**
	 * Removes the extracted package file.
	 *
	 * @return  void
	 *
	 * @since   2.5.4
	 */
	public function cleanUp()
	{
		// Load overrides plugin.
		\JPluginHelper::importPlugin('overrides');

		$app = \JFactory::getApplication();

		// Trigger on before joomla event.
		$app->triggerEvent('onJoomlaAfterUpdate', array($this));

		// Remove the update package.
		$config = \JFactory::getConfig();
		$tempdir = $config->get('tmp_path');

		$file = $app->getUserState('com_joomlaupdate.file', null);
		$target = $tempdir . '/' . $file;

		if (!@unlink($target))
		{
			\JFile::delete($target);
		}

		// Remove the restoration.php file.
		$target = JPATH_COMPONENT_ADMINISTRATOR . '/restoration.php';

		if (!@unlink($target))
		{
			\JFile::delete($target);
		}

		// Remove joomla.xml from the site's root.
		$target = JPATH_ROOT . '/joomla.xml';

		if (!@unlink($target))
		{
			\JFile::delete($target);
		}

		// Unset the update filename from the session.
		\JFactory::getApplication()->setUserState('com_joomlaupdate.file', null);
	}

	/**
	 * Uploads what is presumably an update ZIP file under a mangled name in the temporary directory.
	 *
	 * @return  void
	 *
	 * @since   3.6.0
	 */
	public function upload()
	{
		// Get the uploaded file information.
		$input = \JFactory::getApplication()->input;

		// Do not change the filter type 'raw'. We need this to let files containing PHP code to upload. See \JInputFiles::get.
		$userfile = $input->files->get('install_package', null, 'raw');

		// Make sure that file uploads are enabled in php.
		if (!(bool) ini_get('file_uploads'))
		{
			throw new \RuntimeException(\JText::_('COM_INSTALLER_MSG_INSTALL_WARNINSTALLFILE'), 500);
		}

		// Make sure that zlib is loaded so that the package can be unpacked.
		if (!extension_loaded('zlib'))
		{
			throw new \RuntimeException(\JText::_('COM_INSTALLER_MSG_INSTALL_WARNINSTALLZLIB'), 500);
		}

		// If there is no uploaded file, we have a problem...
		if (!is_array($userfile))
		{
			throw new \RuntimeException(\JText::_('COM_INSTALLER_MSG_INSTALL_NO_FILE_SELECTED'), 500);
		}

		// Is the PHP tmp directory missing?
		if ($userfile['error'] && ($userfile['error'] == UPLOAD_ERR_NO_TMP_DIR))
		{
			throw new \RuntimeException(
				\JText::_('COM_INSTALLER_MSG_INSTALL_WARNINSTALLUPLOADERROR') . '<br>' .
				\JText::_('COM_INSTALLER_MSG_WARNINGS_PHPUPLOADNOTSET'),
				500
			);
		}

		// Is the max upload size too small in php.ini?
		if ($userfile['error'] && ($userfile['error'] == UPLOAD_ERR_INI_SIZE))
		{
			throw new \RuntimeException(
				\JText::_('COM_INSTALLER_MSG_INSTALL_WARNINSTALLUPLOADERROR') . '<br>' . \JText::_('COM_INSTALLER_MSG_WARNINGS_SMALLUPLOADSIZE'),
				500
			);
		}

		// Check if there was a different problem uploading the file.
		if ($userfile['error'] || $userfile['size'] < 1)
		{
			throw new \RuntimeException(\JText::_('COM_INSTALLER_MSG_INSTALL_WARNINSTALLUPLOADERROR'), 500);
		}

		// Build the appropriate paths.
		$config   = \JFactory::getConfig();
		$tmp_dest = tempnam($config->get('tmp_path'), 'ju');
		$tmp_src  = $userfile['tmp_name'];

		// Move uploaded file.
		jimport('joomla.filesystem.file');

		if (version_compare(\JVERSION, '3.4.0', 'ge'))
		{
			$result = \JFile::upload($tmp_src, $tmp_dest, false, true);
		}
		else
		{
			// Old Joomla! versions didn't have UploadShield and don't need the fourth parameter to accept uploads
			$result = \JFile::upload($tmp_src, $tmp_dest);
		}

		if (!$result)
		{
			throw new \RuntimeException(\JText::_('COM_INSTALLER_MSG_INSTALL_WARNINSTALLUPLOADERROR'), 500);
		}

		\JFactory::getApplication()->setUserState('com_joomlaupdate.temp_file', $tmp_dest);
	}

	/**
	 * Checks the super admin credentials are valid for the currently logged in users
	 *
	 * @param   array  $credentials  The credentials to authenticate the user with
	 *
	 * @return  boolean
	 *
	 * @since   3.6.0
	 */
	public function captiveLogin($credentials)
	{
		// Make sure the username matches
		$username = $credentials['username'] ?? null;
		$user     = \JFactory::getUser();

		if (strtolower($user->username) != strtolower($username))
		{
			return false;
		}

		// Make sure the user we're authorising is a Super User
		if (!$user->authorise('core.admin'))
		{
			return false;
		}

		// Get the global \JAuthentication object.
		$authenticate = Authentication::getInstance();
		$response     = $authenticate->authenticate($credentials);

		if ($response->status !== Authentication::STATUS_SUCCESS)
		{
			return false;
		}

		return true;
	}

	/**
	 * Does the captive (temporary) file we uploaded before still exist?
	 *
	 * @return  boolean
	 *
	 * @since   3.6.0
	 */
	public function captiveFileExists()
	{
		$file = \JFactory::getApplication()->getUserState('com_joomlaupdate.temp_file', null);

		\JLoader::import('joomla.filesystem.file');

		if (empty($file) || !\JFile::exists($file))
		{
			return false;
		}

		return true;
	}

	/**
	 * Remove the captive (temporary) file we uploaded before and the .
	 *
	 * @return  void
	 *
	 * @since   3.6.0
	 */
	public function removePackageFiles()
	{
		$files = array(
			\JFactory::getApplication()->getUserState('com_joomlaupdate.temp_file', null),
			\JFactory::getApplication()->getUserState('com_joomlaupdate.file', null),
		);

		\JLoader::import('joomla.filesystem.file');

		foreach ($files as $file)
		{
			if (\JFile::exists($file))
			{
				if (!@unlink($file))
				{
					\JFile::delete($file);
				}
			}
		}
	}

	/**
	 * Gets PHP options.
	 * TODO: Outsource, build common code base for pre install and pre update check
	 *
	 * @return array Array of PHP config options
	 *
	 * @since   4.0.0
	 */
	public function getPhpOptions()
	{
		$options = array();

		/*
		 * Check the PHP Version. It is already checked in JUpdate.
		 * A Joomla! Update which is not supported by current PHP
		 * version is not shown. So this check is actually unnecessary.
		 */
		$option         = new \stdClass;
		$option->label  = \JText::sprintf('INSTL_PHP_VERSION_NEWER', $this->getTargetMinimumPHPVersion());
		$option->state  = $this->isPhpVersionSupported();
		$option->notice = null;
		$options[]      = $option;

		// Check for zlib support.
		$option         = new \stdClass;
		$option->label  = \JText::_('INSTL_ZLIB_COMPRESSION_SUPPORT');
		$option->state  = extension_loaded('zlib');
		$option->notice = null;
		$options[]      = $option;

		// Check for XML support.
		$option         = new \stdClass;
		$option->label  = \JText::_('INSTL_XML_SUPPORT');
		$option->state  = extension_loaded('xml');
		$option->notice = null;
		$options[]      = $option;

		// Check if configured database is compatible with Joomla 4
		if (version_compare($this->getUpdateInformation()['latest'], '4', '>='))
		{
			$option = new \stdClass;
			$option->label  = \JText::sprintf('INSTL_DATABASE_SUPPORTED', $this->getConfiguredDatabaseType());
			$option->state  = $this->isDatabaseTypeSupported();
			$option->notice = null;
			$options[]      = $option;
		}

		// Check for mbstring options.
		if (extension_loaded('mbstring'))
		{
			// Check for default MB language.
			$option = new \stdClass;
			$option->label  = \JText::_('INSTL_MB_LANGUAGE_IS_DEFAULT');
			$option->state  = strtolower(ini_get('mbstring.language')) === 'neutral';
			$option->notice = $option->state ? null : \JText::_('INSTL_NOTICEMBLANGNOTDEFAULT');
			$options[] = $option;

			// Check for MB function overload.
			$option = new \stdClass;
			$option->label  = \JText::_('INSTL_MB_STRING_OVERLOAD_OFF');
			$option->state  = ini_get('mbstring.func_overload') == 0;
			$option->notice = $option->state ? null : \JText::_('INSTL_NOTICEMBSTRINGOVERLOAD');
			$options[] = $option;
		}

		// Check for a missing native parse_ini_file implementation.
		$option = new \stdClass;
		$option->label  = \JText::_('INSTL_PARSE_INI_FILE_AVAILABLE');
		$option->state  = $this->getIniParserAvailability();
		$option->notice = null;
		$options[] = $option;

		// Check for missing native json_encode / json_decode support.
		$option = new \stdClass;
		$option->label  = \JText::_('INSTL_JSON_SUPPORT_AVAILABLE');
		$option->state  = function_exists('json_encode') && function_exists('json_decode');
		$option->notice = null;
		$options[] = $option;

		return $options;
	}

	/**
	 * Gets PHP Settings.
	 * TODO: Outsource, build common code base for pre install and pre update check
	 *
	 * @return  array
	 *
	 * @since   4.0.0
	 */
	public function getPhpSettings()
	{
		$settings = array();

		// Check for display errors.
		$setting = new \stdClass;
		$setting->label = \JText::_('INSTL_DISPLAY_ERRORS');
		$setting->state = (bool) ini_get('display_errors');
		$setting->recommended = false;
		$settings[] = $setting;

		// Check for file uploads.
		$setting = new \stdClass;
		$setting->label = \JText::_('INSTL_FILE_UPLOADS');
		$setting->state = (bool) ini_get('file_uploads');
		$setting->recommended = true;
		$settings[] = $setting;

		// Check for output buffering.
		$setting = new \stdClass;
		$setting->label = \JText::_('INSTL_OUTPUT_BUFFERING');
		$setting->state = (bool) ini_get('output_buffering');
		$setting->recommended = false;
		$settings[] = $setting;

		// Check for session auto-start.
		$setting = new \stdClass;
		$setting->label = \JText::_('INSTL_SESSION_AUTO_START');
		$setting->state = (bool) ini_get('session.auto_start');
		$setting->recommended = false;
		$settings[] = $setting;

		// Check for native ZIP support.
		$setting = new \stdClass;
		$setting->label = \JText::_('INSTL_ZIP_SUPPORT_AVAILABLE');
		$setting->state = function_exists('zip_open') && function_exists('zip_read');
		$setting->recommended = true;
		$settings[] = $setting;

		return $settings;
	}

	/**
	 * Returns the configured database type id (mysqli or sqlsrv or ...)
	 *
	 * @return string
	 *
	 * @since 4.0.0
	 */
	private function getConfiguredDatabaseType()
	{
		return Factory::getApplication()->get('dbtype');
	}

	/**
	 * Returns true, if J! version is < 4 or current configured
	 * database type is compatible with the update.
	 *
	 * @return boolean
	 *
	 * @since 4.0.0
	 */
	public function isDatabaseTypeSupported()
	{
		if (version_compare($this->getUpdateInformation()['latest'], '4', '>='))
		{
			$unsupportedDatabaseTypes = array('sqlsrv', 'sqlazure');
			$currentDatabaseType = $this->getConfiguredDatabaseType();

			return !in_array($currentDatabaseType, $unsupportedDatabaseTypes);
		}

		return true;
	}


	/**
	 * Returns true, if current installed php version is compatible with the update.
	 *
	 * @return boolean
	 *
	 * @since 4.0.0
	 */
	public function isPhpVersionSupported()
	{
		return version_compare(PHP_VERSION, $this->getTargetMinimumPHPVersion(), '>=');
	}

	/**
	 * Returns the PHP minimum version for the update.
	 * Returns JOOMLA_MINIMUM_PHP, if there is no information given.
	 *
	 * @return string
	 *
	 * @since 4.0.0
	 */
	private function getTargetMinimumPHPVersion()
	{
		return isset($this->getUpdateInformation()['object']->php_minimum) ?
			$this->getUpdateInformation()['object']->php_minimum->_data :
			JOOMLA_MINIMUM_PHP;
	}

	/**
	 * Checks the availability of the parse_ini_file and parse_ini_string functions.
	 * TODO: Outsource, build common code base for pre install and pre update check
	 *
	 * @return  boolean  True if the method exists.
	 *
	 * @since   4.0.0
	 */
	public function getIniParserAvailability()
	{
		$disabledFunctions = ini_get('disable_functions');

		if (!empty($disabledFunctions))
		{
			// Attempt to detect them in the disable_functions blacklist.
			$disabledFunctions = explode(',', trim($disabledFunctions));
			$numberOfDisabledFunctions = count($disabledFunctions);

			for ($i = 0; $i < $numberOfDisabledFunctions; $i++)
			{
				$disabledFunctions[$i] = trim($disabledFunctions[$i]);
			}

			$result = !in_array('parse_ini_string', $disabledFunctions);
		}
		else
		{
			// Attempt to detect their existence; even pure PHP implementations of them will trigger a positive response, though.
			$result = function_exists('parse_ini_string');
		}

		return $result;
	}

	/**
	 * Gets an array containing all installed extensions, that are not core extensions.
	 *
	 * @return  array  name,version,updateserver
	 *
	 * @since   4.0.0
	 */
	public function getNonCoreExtensions()
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select(
			$db->qn('ex.name') . ', ' .
			$db->qn('ex.extension_id') . ', ' .
			$db->qn('ex.manifest_cache') . ', ' .
			$db->qn('ex.type') . ', ' .
			$db->qn('ex.folder') . ', ' .
			$db->qn('ex.element') . ', ' .
			$db->qn('ex.client_id') . ', ' .
			$db->qn('si.location')
		)->from(
			$db->qn('#__extensions', 'ex')
		)->leftJoin(
			$db->qn('#__update_sites_extensions', 'se') .
			' ON ' . $db->qn('se.extension_id') . ' = ' . $db->qn('ex.extension_id')
		)->leftJoin(
			$db->qn('#__update_sites', 'si') .
			' ON ' . $db->qn('si.update_site_id') . ' = ' . $db->qn('se.update_site_id')
		)->where(
			$db->qn('ex.package_id') . ' = 0'
		);

		$db->setQuery($query);
		$rows = $db->loadObjectList();
		$rows = array_filter($rows, self::class . '::isNonCoreExtension');

		foreach ($rows as $extension)
		{
			$decode = json_decode($extension->manifest_cache);
			$this->translateExtensionName($extension);
			$extension->version = $decode->version;
			unset($extension->manifest_cache);
		}

		return $rows;
	}

	/**
	 * Checks if extension is non core extension.
	 *
	 * @param   object  $extension  The extension to be checked
	 *
	 * @return  bool  true if extension is not a core extension
	 *
	 * @since   4.0.0
	 */
	private static function isNonCoreExtension($extension)
	{
		$coreExtensions = ExtensionHelper::getCoreExtensions();

		foreach ($coreExtensions as $coreExtension)
		{
			if ($coreExtension[1] == $extension->element)
			{
				return false;
			}
		}

		return true;

	}

	/**
	 * Called by controller's fetchExtensionCompatibility, which is called via AJAX.
	 *
	 * @param   string  $extensionID          The ID of the checked extension
	 * @param   string  $joomlaTargetVersion  Target version of Joomla
	 *
	 * @return object
	 *
	 * @since 4.0.0
	 */
	public function fetchCompatibility($extensionID, $joomlaTargetVersion)
	{
		$updateFileUrl = $this->getUpdateSiteLocation($extensionID);

		if (!$updateFileUrl)
		{
			return (object) array('state' => 2);
		}
		else
		{
			$compatibleVersion = $this->checkCompatibility($updateFileUrl, $joomlaTargetVersion);

			if ($compatibleVersion)
			{
				return (object) array('state' => 1, 'compatibleVersion' => $compatibleVersion->_data);
			}
			else
			{
				return (object) array('state' => 0);
			}
		}
	}

	/**
	 * Get the URL to the update server for a given extension ID
	 *
	 * @param   int  $extensionID  The extension ID
	 *
	 * @return  mixed  URL or false
	 *
	 * @since 4.0.0
	 */
	private function getUpdateSiteLocation($extensionID)
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select($db->qn('us.location'))
			->from($db->qn('#__update_sites', 'us'))
			->leftJoin(
				$db->qn('#__update_sites_extensions', 'e')
				. ' ON ' . $db->qn('e.update_site_id') . ' = ' . $db->qn('us.update_site_id')
			)
			->where($db->qn('e.extension_id') . ' = ' . (int) $extensionID);

		$db->setQuery($query);

		$rows = $db->loadObjectList();

		return count($rows) >= 1 ? end($rows)->location : false;
	}

	/**
	 * Method to check non core extensions for compatibility.
	 *
	 * @param   string  $updateFileUrl        The items update XML url.
	 * @param   string  $joomlaTargetVersion  The Joomla! version to test against
	 *
	 * @return  mixed  An array of data items or false.
	 *
	 * @since   4.0.0
	 */
	private function checkCompatibility($updateFileUrl, $joomlaTargetVersion)
	{
		$update = new \Joomla\CMS\Updater\Update;
		$update->set('jversion.full', $joomlaTargetVersion);
		$update->loadFromXML($updateFileUrl);

		$downloadUrl = $update->get('downloadurl');

		return !empty($downloadUrl) && !empty($downloadUrl->_data) ? $update->get('version') : false;
	}

	/**
	 * Translates an extension name
	 *
	 * @param   object  &$item  The extension of which the name needs to be translated
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	protected function translateExtensionName(&$item)
	{
		// ToDo: Cleanup duplicated code. from com_installer/models/extension.php
		$lang = Factory::getLanguage();
		$path = $item->client_id ? JPATH_ADMINISTRATOR : JPATH_SITE;

		$extension = $item->element;
		$source = JPATH_SITE;

		switch ($item->type)
		{
			case 'component':
				$extension = $item->element;
				$source = $path . '/components/' . $extension;
				break;
			case 'module':
				$extension = $item->element;
				$source = $path . '/modules/' . $extension;
				break;
			case 'file':
				$extension = 'files_' . $item->element;
				break;
			case 'library':
				$extension = 'lib_' . $item->element;
				break;
			case 'plugin':
				$extension = 'plg_' . $item->folder . '_' . $item->element;
				$source = JPATH_PLUGINS . '/' . $item->folder . '/' . $item->element;
				break;
			case 'template':
				$extension = 'tpl_' . $item->element;
				$source = $path . '/templates/' . $item->element;
		}

		$lang->load("$extension.sys", JPATH_ADMINISTRATOR, null, false, true)
		|| $lang->load("$extension.sys", $source, null, false, true);
		$lang->load($extension, JPATH_ADMINISTRATOR, null, false, true)
		|| $lang->load($extension, $source, null, false, true);

		// Translate the extension name if possible
		$item->name = \JText::_($item->name);
	}
}
