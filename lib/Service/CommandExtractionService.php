<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2025 Richard Steinmetz
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\OccAgent\Service;

use OCA\OccAgent\Model\CommandInfo;
use OCP\App\IAppManager;
use OCP\Defaults;
use OCP\ServerVersion;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Command\Command;

final class CommandExtractionService {
	public function __construct(
		private readonly IAppManager $appManager,
		private readonly ContainerInterface $container,
		private readonly ServerVersion $serverVersion,
		private readonly Defaults $defaults,
	) {
	}

	/**
	 * @return CommandInfo[]
	 */
	public function extractCommands(): array {
		$commands = [];

		// Core commands (hackity hack but there is no better way at the moment ...)
		$application = new SymfonyApplication(
			$this->defaults->getName(),
			$this->serverVersion->getVersionString(),
		);
		include __DIR__ . '/../../../../core/register_command.php';
		$coreCommands = $application->all();
		foreach ($coreCommands as $command) {
			if ($command->getDescription() === '') {
				continue;
			}

			$commands[] = CommandInfo::fromCommand($command, 'core');
		}

		// App commands
		$apps = $this->appManager->getEnabledApps();
		foreach ($apps as $app) {
			$appInfo = $this->appManager->getAppInfo($app);
			if (!isset($appInfo['commands'])) {
				continue;
			}

			foreach ($appInfo['commands'] as $commandClass) {
				try {
					/** @var Command $command */
					$command = $this->container->get($commandClass);
				} catch (ContainerExceptionInterface $e) {
					continue;
				}

				if ($command->getDescription() === '') {
					continue;
				}

				$commands[] = CommandInfo::fromCommand($command, $app);
			}
		}

		return $commands;
	}
}
