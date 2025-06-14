<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2025 Richard Steinmetz
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\OccAgent\Adapter;

use Psr\Log\LoggerInterface;
use Stringable;
use Symfony\Component\Console\Output\OutputInterface;

final class PsrLoggerToSymfonyOutputAdapter implements LoggerInterface {
	public function __construct(
		private readonly OutputInterface $output,
	) {
	}

	public function emergency(Stringable|string $message, array $context = array()): void {
		$this->log('EMERG', $message, $context);
	}

	public function alert(Stringable|string $message, array $context = array()): void {
		$this->log('ALERT', $message, $context);
	}

	public function critical(Stringable|string $message, array $context = array()): void {
		$this->log('CRIT', $message, $context);
	}

	public function error(Stringable|string $message, array $context = array()): void {
		$this->log('ERROR', $message, $context);
	}

	public function warning(Stringable|string $message, array $context = array()): void {
		$this->log('WARN', $message, $context);
	}

	public function notice(Stringable|string $message, array $context = array()): void {
		$this->log('NOTICE', $message, $context);
	}

	public function info(Stringable|string $message, array $context = array()): void {
		$this->log('INFO', $message, $context);
	}

	public function debug(Stringable|string $message, array $context = array()): void {
		$this->log('DEBUG', $message, $context);
	}

	public function log($level, Stringable|string $message, array $context = array()): void {
		$this->output->writeln("[$level] $message " . json_encode($context));
	}
}
