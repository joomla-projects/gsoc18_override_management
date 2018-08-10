<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;

defined('JPATH_PLATFORM') or die;

use Joomla\Console\AbstractCommand;
use Joomla\DI\ContainerAwareInterface;
use Joomla\DI\ContainerAwareTrait;
use Joomla\Session\SessionInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Console command for performing session garbage collection
 *
 * @since  4.0.0
 */
class SessionGcCommand extends AbstractCommand implements ContainerAwareInterface
{
	use ContainerAwareTrait;

	/**
	 * Execute the command.
	 *
	 * @return  integer  The exit code for the command.
	 *
	 * @since   4.0.0
	 */
	public function execute(): int
	{
		$symfonyStyle = $this->createSymfonyStyle();

		$symfonyStyle->title('Running Session Garbage Collection');

		$session = $this->getSessionService($this->getApplication()->getConsoleInput()->getOption('application'));

		$gcResult = $session->gc();

		// Destroy the session started for this process
		$session->destroy();

		if ($gcResult === false)
		{
			$symfonyStyle->error('Garbage collection was not completed. Either the operation failed or is not supported on your platform.');

			return 1;
		}

		$symfonyStyle->success('Garbage collection completed.');

		return 0;
	}

	/**
	 * Initialise the command.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	protected function initialise()
	{
		$this->setName('session:gc');
		$this->setDescription('Performs session garbage collection');
		$this->addOption('application', 'app', InputOption::VALUE_OPTIONAL, 'The application to perform garbage collection for.', 'site');
		$this->setHelp(
<<<EOF
The <info>%command.name%</info> command runs PHP's garbage collection operation for session data

<info>php %command.full_name%</info>

This command defaults to performing garbage collection for the frontend (site) application. To run garbage collection
for another application, you can specify it with the <info>--application</info> option.

<info>php %command.full_name% --application=[APPLICATION]</info>
EOF
		);
	}

	/**
	 * Get the session service for the requested application.
	 *
	 * @param   string  $application  The application session service to retrieve
	 *
	 * @return  SessionInterface
	 *
	 * @since   4.0.0
	 */
	private function getSessionService(string $application): SessionInterface
	{
		if (!$this->getContainer()->has("session.web.$application"))
		{
			throw new \InvalidArgumentException(
				sprintf(
					'The `%s` application is not a valid option.',
					$application
				)
			);
		}

		return $this->getContainer()->get("session.web.$application");
	}
}
