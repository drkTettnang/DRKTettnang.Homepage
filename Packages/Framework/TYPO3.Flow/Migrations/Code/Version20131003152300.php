<?php
namespace TYPO3\Flow\Core\Migrations;

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
 * Change TYPO3\Flow\Persistence\Doctrine\DatabaseConnectionException to
 * TYPO3\Flow\Persistence\Doctrine\Exception\DatabaseConnectionException
 */
class Version20131003152300 extends AbstractMigration {

	/**
	 * NOTE: This method is overridden for historical reasons. Previously code migrations were expected to consist of the
	 * string "Version" and a 12-character timestamp suffix. The suffix has been changed to a 14-character timestamp.
	 * For new migrations the classname pattern should be "Version<YYYYMMDDhhmmss>" (14-character timestamp) and this method should *not* be implemented
	 *
	 * @return string
	 */
	public function getIdentifier() {
		return 'TYPO3.Flow-201310031523';
	}

	/**
	 * @return void
	 */
	public function up() {
		$this->searchAndReplace(
			'TYPO3\Flow\Persistence\Doctrine\DatabaseConnectionException',
			'TYPO3\Flow\Persistence\Doctrine\Exception\DatabaseConnectionException'
		);
	}

}
