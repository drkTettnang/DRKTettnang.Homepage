<?php
namespace TYPO3\Flow\Mvc\Exception;

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
 * This exception is thrown by a controller to stop the execution of the current
 * action and return the control to the dispatcher for the special case of a
 * forward().
 *
 * @api
 */
class ForwardException extends StopActionException {

	/**
	 * @var \TYPO3\Flow\Mvc\ActionRequest
	 */
	protected $nextRequest;

	/**
	 * Sets the next request, containing the information about the next action to
	 * execute.
	 *
	 * @param \TYPO3\Flow\Mvc\ActionRequest $nextRequest
	 * @return void
	 */
	public function setNextRequest(\TYPO3\Flow\Mvc\ActionRequest $nextRequest) {
		$this->nextRequest = $nextRequest;
	}

	/**
	 * Returns the next request
	 *
	 * @return \TYPO3\Flow\Mvc\ActionRequest
	 */
	public function getNextRequest() {
		return $this->nextRequest;
	}

}
