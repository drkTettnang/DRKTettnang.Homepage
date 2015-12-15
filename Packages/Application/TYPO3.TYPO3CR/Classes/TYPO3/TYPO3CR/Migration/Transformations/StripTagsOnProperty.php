<?php
namespace TYPO3\TYPO3CR\Migration\Transformations;

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
 * Strip all tags on a given property
 */
class StripTagsOnProperty extends AbstractTransformation {

	/**
	 * Property name to change
	 *
	 * @var string
	 */
	protected $propertyName;

	/**
	 * Sets the name of the property to work on.
	 *
	 * @param string $propertyName
	 * @return void
	 */
	public function setProperty($propertyName) {
		$this->propertyName = $propertyName;
	}

	/**
	 * Returns TRUE if the given node has the property to work on.
	 *
	 * @param \TYPO3\TYPO3CR\Domain\Model\NodeData $node
	 * @return boolean
	 */
	public function isTransformable(\TYPO3\TYPO3CR\Domain\Model\NodeData $node) {
		return ($node->hasProperty($this->propertyName));
	}

	/**
	 * Strips tags on the value of the property to work on.
	 *
	 * @param \TYPO3\TYPO3CR\Domain\Model\NodeData $node
	 * @return void
	 */
	public function execute(\TYPO3\TYPO3CR\Domain\Model\NodeData $node) {
		$node->setProperty($this->propertyName, strip_tags($node->getProperty($this->propertyName)));
	}
}
