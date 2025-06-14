<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2025 Richard Steinmetz
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\OccAgent\Model;

use Symfony\Component\Console\Input\InputArgument;

final class ArgumentInfo implements \JsonSerializable {
	public function __construct(
		private readonly string $name,
		private readonly string $description,
		private readonly bool $optional,
	) {
	}

	public static function fromInputArgument(InputArgument $argument): self {
		return new self(
			$argument->getName(),
			$argument->getDescription(),
			!$argument->isRequired(),
		);
	}

	public function getName(): string {
		return $this->name;
	}

	public function getDescription(): string {
		return $this->description;
	}

	public function isOptional(): bool {
		return $this->optional;
	}

	function jsonSerialize() {
		return [
			'name' => $this->getName(),
			'description' => $this->getDescription(),
			'optional' => $this->isOptional(),
		];
	}
}
