<?php
namespace TYPO3\TYPO3CR\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3CR".               *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Interface for rendering a node label string based on some strategy
 *
 * @api
 */
interface NodeLabelGeneratorInterface {

	/**
	 * Render a node label
	 *
	 * @param NodeInterface $node
	 * @return string
	 * @api
	 */
	public function getLabel(NodeInterface $node);
}
