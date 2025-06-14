<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2025 Richard Steinmetz
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\OccAgent\Model;

use Symfony\Component\Console\Input\InputOption;

final class OptionInfo implements \JsonSerializable {
	public function __construct(
		private readonly string $name,
		private readonly string $description,
		private readonly bool $acceptsValue,
		private readonly bool $requiresValue,
		private readonly mixed $defaultValue,
	) {
	}

	public static function fromInputOption(InputOption $option): self {
		return new self(
			$option->getName(),
			$option->getDescription(),
			$option->acceptValue(),
			$option->isValueRequired(),
			$option->getDefault(),
		);
	}

	public function getName(): string {
		return $this->name;
	}

	public function getDescription(): string {
		return $this->description;
	}

	public function doesAcceptValue(): bool {
		return $this->acceptsValue;
	}

	public function doesRequireValue(): bool {
		return $this->requiresValue;
	}

	public function getDefaultValue(): mixed {
		return $this->defaultValue;
	}

	function jsonSerialize() {
		return [
			'name' => $this->getName(),
			'description' => $this->getDescription(),
			'acceptsValue' => $this->doesAcceptValue(),
			'requiresValue' => $this->doesRequireValue(),
			'defaultValue' => $this->defaultValue,
		];
	}
}
