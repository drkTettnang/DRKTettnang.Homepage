<?php
namespace TYPO3\Flow\Validation\Validator;

/*                                                                        *
 * This script belongs to the TYPO3 Flow framework.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * Validator for email addresses
 *
 * @api
 * @Flow\Scope("singleton")
 */
class EmailAddressValidator extends AbstractValidator {

	/**
	 * Checks if the given value is a valid email address.
	 *
	 * @param mixed $value The value that should be validated
	 * @return void
	 * @api
	 */
	protected function isValid($value) {
		if (!is_string($value) || !$this->validEmail($value)) {
			$this->addError('Please specify a valid email address.', 1221559976);
		}
	}

	/**
	 * Checking syntax of input email address
	 *
	 * @param string $emailAddress Input string to evaluate
	 * @return boolean Returns TRUE if the $email address (input string) is valid
	 */
	protected function validEmail($emailAddress) {
		return (filter_var($emailAddress, FILTER_VALIDATE_EMAIL) !== FALSE);
	}
}
