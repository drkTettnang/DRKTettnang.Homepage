<?php
namespace TYPO3\Flow\Utility;

/*                                                                        *
 * This script belongs to the TYPO3 Flow framework.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

require_once(FLOW_PATH_FLOW . 'Resources/PHP/iSecurity/Security_Randomizer.php');

use TYPO3\Flow\Annotations as Flow;

/**
 * A utility class for various algorithms.
 *
 * @Flow\Scope("singleton")
 */
class Algorithms {

	/**
	 * Generates a universally unique identifier (UUID) according to RFC 4122.
	 * The algorithm used here, might not be completely random.
	 *
	 * @return string The universally unique id
	 * @todo check for randomness, optionally generate type 1 and type 5 UUIDs, use php5-uuid extension if available
	 */
	static public function generateUUID() {
		return strtolower(\Security_Randomizer::getRandomGUID());
	}

	/**
	 * Returns a string of random bytes.
	 *
	 * @param integer $count Number of bytes to generate
	 * @return string Random bytes
	 */
	static public function generateRandomBytes($count) {
		return \Security_Randomizer::getRandomBytes($count);
	}

	/**
	 * Returns a random token in hex format.
	 *
	 * @param integer $count Token length
	 * @return string A random token
	 */
	static public function generateRandomToken($count) {
		return \Security_Randomizer::getRandomToken($count);
	}

	/**
	 * Returns a random string with alpha-numeric characters.
	 *
	 * @param integer $count Number of characters to generate
	 * @param string $characters Allowed characters, defaults to alpha-numeric (a-zA-Z0-9)
	 * @return string A random string
	 */
	static public function generateRandomString($count, $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789') {
		return \Security_Randomizer::getRandomString($count, $characters);
	}

}
