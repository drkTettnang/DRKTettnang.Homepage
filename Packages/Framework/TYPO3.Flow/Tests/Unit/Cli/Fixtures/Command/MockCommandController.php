<?php
namespace TYPO3\Flow\Tests\Unit\Cli\Fixtures\Command;

/*                                                                        *
 * This script belongs to the TYPO3 Flow framework.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * A mock CLI Command
 */
class MockACommandController extends \TYPO3\Flow\Cli\Command {

	public function fooCommand() {
	}

	public function barCommand($someArgument) {
	}
}

/**
 * Another mock CLI Command
 */
class MockBCommandController extends \TYPO3\Flow\Cli\Command {

	public function bazCommand() {
	}
}
