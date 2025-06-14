<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2025 Richard Steinmetz
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\OccAgent\LlmChainTools;

use OCA\OccAgent\Model\CommandInfo;
use OCA\OccAgent\Service\CommandExtractionService;
use PhpLlm\LlmChain\Chain\Toolbox\Attribute\AsTool;

#[AsTool(
	name: 'get_command_info',
	description: 'Get more information about a Nextcloud command in JSON format.',
)]
final class GetCommandInfo {
	private readonly CommandExtractionService $commandExtractionService;

	public function __construct(
		/** @var CommandInfo[] */
		private readonly array $commands,
	) {
	}

	public function __invoke(string $name): string {
		$command = $this->getCommand($name);
		if ($command === null) {
			return "The command $name does not exist!";
		}

		return json_encode($command, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
	}

	private function getCommand(string $name): ?CommandInfo {
		foreach ($this->commands as $command) {
			if ($command->getName() === $name) {
				return $command;
			}
		}

		return null;
	}
}

