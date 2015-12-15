<?php
namespace TYPO3\Flow\Core\Booting;

/*                                                                        *
 * This script belongs to the TYPO3 Flow framework.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Core\Bootstrap;

/**
 * A boot sequence, consisting of individual steps, each of them initializing a
 * specific part of the application.
 *
 * @api
 */
class Sequence {

	/**
	 * @var string
	 */
	protected $identifier;

	/**
	 * @var array
	 */
	protected $steps = array();

	/**
	 * @param string $identifier
	 */
	public function __construct($identifier) {
		$this->identifier = $identifier;
	}

	/**
	 * Adds the given step to this sequence, to be executed after then step specified
	 * by $previousStepIdentifier. If no previous step is specified, the new step
	 * is added to the list of steps executed right at the start of the sequence.
	 *
	 * @param \TYPO3\Flow\Core\Booting\Step $step The new step to add
	 * @param string $previousStepIdentifier The preceding step
	 * @return void
	 */
	public function addStep(Step $step, $previousStepIdentifier = 'start') {
		$this->steps[$previousStepIdentifier][] = $step;
	}

	/**
	 * Removes all occurrences of the specified step from this sequence
	 *
	 * @param string $stepIdentifier
	 * @return void
	 * @throws \TYPO3\Flow\Exception
	 */
	public function removeStep($stepIdentifier) {
		$removedOccurrences = 0;
		foreach ($this->steps as $previousStepIdentifier => $steps) {
			foreach ($steps as $step) {
				if ($step->getIdentifier() === $stepIdentifier) {
					unset($this->steps[$previousStepIdentifier][$stepIdentifier]);
					$removedOccurrences ++;
				}
			}
		}
		if ($removedOccurrences === 0) {
			throw new \TYPO3\Flow\Exception(sprintf('Cannot remove sequence step with identifier "%s" because no such step exists in the given sequence.', $stepIdentifier), 1322591669);
		}
	}

	/**
	 * Executes all steps of this sequence
	 *
	 * @param \TYPO3\Flow\Core\Bootstrap $bootstrap
	 * @return void
	 */
	public function invoke(Bootstrap $bootstrap) {
		if (isset($this->steps['start'])) {
			foreach ($this->steps['start'] as $step) {
				$this->invokeStep($step, $bootstrap);
			}
		}
	}

	/**
	 * Invokes a single step of this sequence and also invokes all steps registered
	 * to be executed after the given step.
	 *
	 * @param \TYPO3\Flow\Core\Booting\Step $step The step to invoke
	 * @param \TYPO3\Flow\Core\Bootstrap $bootstrap
	 * @return void
	 */
	protected function invokeStep(Step $step, Bootstrap $bootstrap) {
		$bootstrap->getSignalSlotDispatcher()->dispatch(__CLASS__, 'beforeInvokeStep', array($step, $this->identifier));
		$identifier = $step->getIdentifier();
		$step($bootstrap);
		$bootstrap->getSignalSlotDispatcher()->dispatch(__CLASS__, 'afterInvokeStep', array($step, $this->identifier));
		if (isset($this->steps[$identifier])) {
			foreach ($this->steps[$identifier] as $followingStep) {
				$this->invokeStep($followingStep, $bootstrap);
			}
		}
	}
}
