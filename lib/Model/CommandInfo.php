<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2025 Richard Steinmetz
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\OccAgent\Model;

use Symfony\Component\Console\Command\Command;

final class CommandInfo implements \JsonSerializable {
	public function __construct(
		private readonly Command $command,
		private readonly string $app,
		private readonly string $name,
		private readonly string $description,
		/** @var ArgumentInfo[] */
		private readonly array $arguments,
		/** @var OptionInfo[] */
		private readonly array $options,
	) {
	}

	public static function fromCommand(Command $command, string $app): self {
		$definition = $command->getDefinition();
		$arguments = array_map(ArgumentInfo::fromInputArgument(...), $definition->getArguments());
		$options = array_map(OptionInfo::fromInputOption(...), $definition->getOptions());

		return new self(
			$command,
			$app,
			$command->getName(),
			$command->getDescription(),
			$arguments,
			$options,
		);
	}

	public function getCommand(): Command {
		return $this->command;
	}

	public function getApp(): string {
		return $this->app;
	}

	public function getName(): string {
		return $this->name;
	}

	public function getDescription(): string {
		return $this->description;
	}

	/**
	 * @return ArgumentInfo[]
	 */
	public function getArguments(): array {
		return $this->arguments;
	}

	function jsonSerialize() {
		return [
			'app' => $this->getApp(),
			'name' => $this->getName(),
			'description' => $this->getDescription(),
			'arguments' => $this->arguments,
			'options' => $this->options,
		];
	}
}
