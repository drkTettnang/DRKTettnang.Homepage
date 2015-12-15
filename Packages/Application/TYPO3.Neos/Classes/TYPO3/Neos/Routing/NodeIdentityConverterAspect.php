<?php
namespace TYPO3\Neos\Routing;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Neos".            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;

/**
 * Aspect to convert a node object to its context node path. This is used in URI
 * building in order to make linking to nodes a lot easier.
 *
 * On the long term, type converters should be able to convert the reverse direction
 * as well, and then this aspect could be removed.
 *
 * @Flow\Scope("singleton")
 * @Flow\Aspect
 */
class NodeIdentityConverterAspect {

	/**
	 * Convert the object to its context path, if we deal with TYPO3CR nodes.
	 *
	 * @Flow\Around("method(TYPO3\Flow\Persistence\AbstractPersistenceManager->convertObjectToIdentityArray())")
	 * @param \TYPO3\Flow\Aop\JoinPointInterface $joinPoint the joinpoint
	 * @return string|array the context path to be used for routing
	 */
	public function convertNodeToContextPathForRouting(\TYPO3\Flow\Aop\JoinPointInterface $joinPoint) {
		$objectArgument = $joinPoint->getMethodArgument('object');
		if ($objectArgument instanceof NodeInterface) {
			return $objectArgument->getContextPath();
		} else {
			return $joinPoint->getAdviceChain()->proceed($joinPoint);
		}
	}
}
