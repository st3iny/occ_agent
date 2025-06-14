<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2025 Richard Steinmetz
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\OccAgent\Command;

use OCA\OccAgent\Model\CommandInfo;
use OCA\OccAgent\Service\CommandExtractionService;
use OCA\OccAgent\Service\LlmChainService;
use PhpLlm\LlmChain\Platform\Message\Message;
use PhpLlm\LlmChain\Platform\Message\MessageBag;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\MissingInputException;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

final class Agent extends Command {
	public function __construct(
		private readonly LlmChainService $chainService,
		private readonly CommandExtractionService $commandExtractionService,
	) {
		parent::__construct();
	}

	protected function configure(): void {
		$this->setName('agent');
		$this->setDescription('Run the occ agent with an interactive prompt');
		$this->addOption('audit', null, null, 'Log all occ commands and their arguments');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$apiKey = getenv('OPEN_AI_KEY');
		if (!$apiKey) {
			$output->writeln('<error>Missing environment variable OPEN_AI_KEY!</error>');
			$output->writeln('');
			$output->writeln('Please export it before running the agent.');
			return 1;
		}

		$audit = (bool)$input->getOption('audit');
		$chain = $this->chainService->createChain($apiKey, $output, $audit);


		// Generate simple command list for the LLM. Sending the full JSON of all commands would
		// cause excessive token usage.
		$commands = $this->commandExtractionService->extractCommands();
		$commandList = implode('\n', array_map(
			static fn (CommandInfo $command) => sprintf(
				'%s %s',
				$command->getName(),
				$command->getDescription(),
			),
			$commands,
		));

		$messages = new MessageBag(Message::forSystem(<<<PROMPT
			The user will prompt commands to interact with a Nextcloud server.
			Your job is to find the right command or sequence of commands and run them.
			A list of available commands is provided below.
			Please do not use or attempt to invoke any other command than the provided ones.
			Please inquire the get_command_info tool for more information about a specific command
			instead of relying on previous knowledge about it.

			Each command has arguments and options.
			Arguments are passed are passed as positional arguments.
			Some arguments are required and some are optional.
			Options are passed using a -- prefix like in unix programs.
		    Some options accept an optional value and some even require one

			$commandList
		PROMPT));

		/** @var QuestionHelper $helper */
		$helper = $this->getHelper('question');

		while (true) {
			$q = new Question('You > ');
			try {
				$msg = $helper->ask($input, $output, $q);
			} catch (MissingInputException $e)  {
				break;
			}

			if (in_array(strtolower($msg), ['exit', 'quit', 'bye'])) {
				break;
			}

			// Usually, it is smart to keep the all messages in the context because LLMs are
			// stateless. However, this makes no sense for the agent as this would result in
			// commands being executed multiple times.
			$response = $chain->call($messages->with(Message::ofUser($msg)));
			$responseContent = (string)$response->getContent();

			$output->writeln("Agent > $responseContent");
			$output->writeln('');
		}

		$output->writeln('');
		$output->writeln('Bye');
		return 0;
	}
}
