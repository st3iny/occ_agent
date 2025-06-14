<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2025 Richard Steinmetz
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\OccAgent\Service;

use OCA\OccAgent\Adapter\PsrLoggerToSymfonyOutputAdapter;
use OCA\OccAgent\Model\CommandInfo;
use PhpLlm\LlmChain\Chain\Chain;
use PhpLlm\LlmChain\Chain\Toolbox\ChainProcessor;
use PhpLlm\LlmChain\Chain\Toolbox\Toolbox;
use PhpLlm\LlmChain\Chain\Toolbox\ToolFactory\MemoryToolFactory;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\GPT;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\PlatformFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

final class LlmChainService {
	public function __construct(
		private readonly CommandExtractionService $commandExtractionService,
	) {
	}

	public function createChain(string $apiKey, OutputInterface $output, bool $audit): Chain  {
		$platform = PlatformFactory::create($apiKey);
		$llm = new GPT(GPT::GPT_4O_MINI);

		$commands = $this->commandExtractionService->extractCommands();
		$commandInvoker = new class($commands, $output, $audit) {
			public function __construct(
				/** @var CommandInfo[] */
				private readonly array $commands,
				private readonly OutputInterface $output,
				private readonly bool $audit,
			) {
			}

			private function getCommand(string $name): ?Command {
				foreach ($this->commands as $cmd) {
					if ($cmd->getName() === $name) {
						return $cmd->getCommand();
					}
				}

				return null;
			}

			public function __invoke(string $name, array $argv): string {
				$command = $this->getCommand($name);
				if ($command === null) {
					return "There is no command with name $name!";
				}

				if ($this->audit) {
					$this->output->writeln(sprintf(
						"[AUDIT] Running %s with %s",
						$command->getName(),
						json_encode($argv, JSON_THROW_ON_ERROR),
					));
				}

				$input = new ArgvInput(['occ', $name, ...$argv]);
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

				return sprintf(
					"The command returned unix exit code %d and generated the following output.\n\n%s",
					$code,
					$output->fetch(),
				);
			}
		};

		$metadataFactory = (new MemoryToolFactory())
			->addTool(
				get_class($commandInvoker),
				'run_command',
				'Run a command of Nextcloud with parameters.'
			);
		$toolbox = new Toolbox($metadataFactory, [$commandInvoker]);
		$toolProcessor = new ChainProcessor($toolbox);
		return new Chain(
			$platform,
			$llm,
			inputProcessors: [$toolProcessor],
			outputProcessors: [$toolProcessor],
			logger: new PsrLoggerToSymfonyOutputAdapter($output),
		);
	}
}
