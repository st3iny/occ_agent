<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2025 Richard Steinmetz
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\OccAgent\LlmChainTools;

use OCA\OccAgent\Model\CommandInfo;
use PhpLlm\LlmChain\Chain\Toolbox\Attribute\AsTool;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

#[AsTool(name: 'invoke_command', description: 'Invoke a Nextcloud command.')]
final class InvokeCommand {
	public function __construct(
		/** @var CommandInfo[] */
		private readonly array $commands,
		private readonly OutputInterface $output,
		private readonly bool $audit,
	) {
	}

	public function __invoke(string $name, array $options, array $arguments): string {
		$command = $this->getCommand($name);
		if ($command === null) {
			return "There is no command with name $name!";
		}

		$argv = ['occ', $name, ...$options, ...$arguments];

		if ($this->audit) {
			$this->output->writeln(sprintf(
				'[AUDIT] Running %s with %s',
				$command->getName(),
				json_encode($argv, JSON_THROW_ON_ERROR),
			));
		}

		//$input = new ArgvInput(['occ', $name, ...$argv]);
		$input = new ArgvInput($argv);
		$output = new BufferedOutput();

		try {
			$code = $command->run($input, $output);
		} catch (\Exception $e) {
			return sprintf(
				'The command %s threw an exception: %s\n\n%s',
				$name,
				$e->getMessage(),
				$e->getTraceAsString(),
			);
		}

		$out = $output->fetch();
		return "The command returned unix exit code $code and generated the following output.\n\n$out";
	}

	private function getCommand(string $name): ?Command {
		foreach ($this->commands as $command) {
			if ($command->getName() === $name) {
				return $command->getCommand();
			}
		}

		return null;
	}
}
