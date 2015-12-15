<?php
namespace TYPO3\TYPO3CR\Migration\Domain\Factory;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3CR".               *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * Migration factory.
 *
 */
class MigrationFactory {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\TYPO3CR\Migration\Configuration\ConfigurationInterface
	 */
	protected $migrationConfiguration;

	/**
	 * @param string $version
	 * @return \TYPO3\TYPO3CR\Migration\Domain\Model\Migration
	 */
	public function getMigrationForVersion($version) {
		$migrationConfiguration = $this->migrationConfiguration->getMigrationVersion($version);
		$migration = new \TYPO3\TYPO3CR\Migration\Domain\Model\Migration($version, $migrationConfiguration);
		return $migration;
	}

	/**
	 * Return array of all available migrations with the current configuration type
	 *
	 * @return array
	 */
	public function getAvailableMigrationsForCurrentConfigurationType() {
		return $this->migrationConfiguration->getAvailableVersions();
	}
}
