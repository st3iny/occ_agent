<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2025 Richard Steinmetz
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\OccAgent\AppInfo;

use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;

include_once __DIR__ . '/../../vendor/autoload.php';

final class Application extends App implements IBootstrap {
	public const APP_ID = 'occ_agent';

	public function __construct(array $urlParams = []) {
		parent::__construct(self::APP_ID, $urlParams);
	}

	public function register(IRegistrationContext $context): void {
	}

	public function boot(IBootContext $context): void {
	}
}
