<?php
namespace TYPO3\TYPO3CR\Eel\FlowQueryOperations;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.TYPO3CR".         *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Eel\FlowQuery\FlowQuery;
use TYPO3\Eel\FlowQuery\Operations\AbstractOperation;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;

/**
 * "nextUntil" operation working on TYPO3CR nodes. It iterates over all context elements
 * and returns each following sibling until the matching sibling is found.
 * If an optional filter expression is provided as a second argument,
 * it only returns the nodes matching the given expression.
 */
class NextUntilOperation extends AbstractOperation {

	/**
	 * {@inheritdoc}
	 *
	 * @var string
	 */
	static protected $shortName = 'nextUntil';

	/**
	 * {@inheritdoc}
	 *
	 * @var integer
	 */
	static protected $priority = 0;

	/**
	 * {@inheritdoc}
	 *
	 * @param array (or array-like object) $context onto which this operation should be applied
	 * @return boolean TRUE if the operation can be applied onto the $context, FALSE otherwise
	 */
	public function canEvaluate($context) {
		return count($context) === 0 || (isset($context[0]) && ($context[0] instanceof NodeInterface));
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param FlowQuery $flowQuery the FlowQuery object
	 * @param array $arguments the arguments for this operation
	 * @return void
	 */
	public function evaluate(FlowQuery $flowQuery, array $arguments) {
		$output = array();
		$outputNodePaths = array();
		$until = array();

		foreach ($flowQuery->getContext() as $contextNode) {
			$nextNodes = $this->getNextForNode($contextNode);
			if (isset($arguments[0]) && !empty($arguments[0])) {
				$untilQuery = new FlowQuery($nextNodes);
				$untilQuery->pushOperation('filter', array($arguments[0]));

				$until = $untilQuery->get();
			}

			if (isset($until[0]) && !empty($until[0])) {
				$nextNodes = $this->getNodesUntil($nextNodes, $until[0]);
			}

			if (is_array($nextNodes)) {
				foreach ($nextNodes as $nextNode) {
					if ($nextNode !== NULL && !isset($outputNodePaths[$nextNode->getPath()])) {
						$outputNodePaths[$nextNode->getPath()] = TRUE;
						$output[] = $nextNode;
					}
				}
			}
		}

		$flowQuery->setContext($output);

		if (isset($arguments[1]) && !empty($arguments[1])) {
			$flowQuery->pushOperation('filter', array($arguments[1]));
		}
	}

	/**
	 * @param NodeInterface $contextNode The node for which the next nodes should be found
	 * @return array|NULL The following nodes of $contextNode or NULL
	 */
	protected function getNextForNode(NodeInterface $contextNode) {
		$nodesInContext = $contextNode->getParent()->getChildNodes();
		$count = count($nodesInContext);

		for ($i = 0; $i < $count; $i++) {
			if ($nodesInContext[$i] === $contextNode) {
				unset($nodesInContext[$i]);
				return array_values($nodesInContext);
			} else {
				unset($nodesInContext[$i]);
			}
		}
		return NULL;
	}

	/**
	 * @param array $nextNodes the remaining nodes
	 * @param NodeInterface $until
	 * @return array
	 */
	protected function getNodesUntil($nextNodes, NodeInterface $until) {
		$count = count($nextNodes) - 1;

		for ($i = $count; $i >= 0; $i--) {
			if ($nextNodes[$i]->getPath() === $until->getPath()) {
				unset($nextNodes[$i]);
				return array_values($nextNodes);
			} else {
				unset($nextNodes[$i]);
			}
		}
		return array_values($nextNodes);
	}
}