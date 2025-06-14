<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2025 Richard Steinmetz
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\OccAgent\Service;

use OCA\OccAgent\Adapter\PsrLoggerToSymfonyOutputAdapter;
use OCA\OccAgent\LlmChainTools\GetCommandInfo;
use OCA\OccAgent\LlmChainTools\InvokeCommand;
use PhpLlm\LlmChain\Chain\Chain;
use PhpLlm\LlmChain\Chain\Toolbox\ChainProcessor;
use PhpLlm\LlmChain\Chain\Toolbox\Toolbox;
use PhpLlm\LlmChain\Chain\Toolbox\ToolFactory\ReflectionToolFactory;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\GPT;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\PlatformFactory;
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
		$toolbox = new Toolbox(new ReflectionToolFactory(), [
			new GetCommandInfo($commands),
			new InvokeCommand($commands, $output, $audit),
		]);
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
