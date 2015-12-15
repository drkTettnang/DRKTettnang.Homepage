<?php
namespace TYPO3\TYPO3CR\Service;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.TYPO3CR".         *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\TYPO3CR\Domain\Model\NodeData;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\TYPO3CR\Domain\Model\Workspace;
use TYPO3\TYPO3CR\Domain\Service\Context;
use TYPO3\TYPO3CR\Exception\WorkspaceException;
use TYPO3\TYPO3CR\Service\Utility\NodePublishingDependencySolver;

/**
 * A generic TYPO3CR Publishing Service
 *
 * @api
 * @Flow\Scope("singleton")
 */
class PublishingService implements PublishingServiceInterface {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\TYPO3CR\Domain\Repository\WorkspaceRepository
	 */
	protected $workspaceRepository;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\TYPO3CR\Domain\Repository\NodeDataRepository
	 */
	protected $nodeDataRepository;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\TYPO3CR\Domain\Factory\NodeFactory
	 */
	protected $nodeFactory;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\TYPO3CR\Domain\Service\ContextFactoryInterface
	 */
	protected $contextFactory;

	/**
	 * Returns a list of nodes contained in the given workspace which are not yet published
	 *
	 * @param Workspace $workspace
	 * @return array<\TYPO3\TYPO3CR\Domain\Model\NodeInterface>
	 * @api
	 */
	public function getUnpublishedNodes(Workspace $workspace) {
		if ($workspace->getName() === 'live') {
			return array();
		}

		$nodeData = $this->nodeDataRepository->findByWorkspace($workspace);
		$unpublishedNodes = array();
		foreach ($nodeData as $singleNodeData) {
			/** @var NodeData $singleNodeData */
			// Skip the root entry from the workspace as it can't be published
			if ($singleNodeData->getPath() === '/') {
				continue;
			}
			$node = $this->nodeFactory->createFromNodeData($singleNodeData, $this->createContext($workspace, $singleNodeData->getDimensionValues()));
			if ($node !== NULL) {
				$unpublishedNodes[] = $node;
			}
		}

		$unpublishedNodes = $this->sortNodesForPublishing($unpublishedNodes);

		return $unpublishedNodes;
	}

	/**
	 * Returns the number of unpublished nodes contained in the given workspace
	 *
	 * @param Workspace $workspace
	 * @return integer
	 * @api
	 */
	public function getUnpublishedNodesCount(Workspace $workspace) {
		return $workspace->getNodeCount() - 1;
	}

	/**
	 * Publishes the given node to the specified target workspace. If no workspace is specified, "live" is assumed.
	 *
	 * @param NodeInterface $node
	 * @param Workspace $targetWorkspace If not set the "live" workspace is assumed to be the publishing target
	 * @return void
	 * @api
	 */
	public function publishNode(NodeInterface $node, Workspace $targetWorkspace = NULL) {
		if ($targetWorkspace === NULL) {
			$targetWorkspace = $this->workspaceRepository->findOneByName('live');
		}
		$nodes = array($node);

		$sourceWorkspace = $node->getWorkspace();
		$sourceWorkspace->publishNodes($nodes, $targetWorkspace);

		$this->emitNodePublished($node, $targetWorkspace);
	}

	/**
	 * Publishes the given nodes to the specified target workspace. If no workspace is specified, "live" is assumed.
	 *
	 * @param array<\TYPO3\TYPO3CR\Domain\Model\NodeInterface> $nodes The nodes to publish
	 * @param Workspace $targetWorkspace If not set the "live" workspace is assumed to be the publishing target
	 * @return void
	 * @api
	 */
	public function publishNodes(array $nodes, Workspace $targetWorkspace = NULL) {
		$nodes = $this->sortNodesForPublishing($nodes);

		foreach ($nodes as $node) {
			$this->publishNode($node, $targetWorkspace);
		}
	}

	/**
	 * Discards the given node.
	 *
	 * @param NodeInterface $node
	 * @return void
	 * @throws \TYPO3\TYPO3CR\Exception\WorkspaceException
	 * @api
	 */
	public function discardNode(NodeInterface $node) {
		if ($node->getWorkspace()->getName() === 'live') {
			throw new WorkspaceException('Nodes in the live workspace cannot be discarded.', 1395841899);
		}

		$possibleShadowNodeData = $this->nodeDataRepository->findOneByMovedTo($node->getNodeData());
		if ($possibleShadowNodeData !== NULL) {
			$this->nodeDataRepository->remove($possibleShadowNodeData);
		}

		if ($node->getPath() !== '/') {
			$this->nodeDataRepository->remove($node);
			$this->emitNodeDiscarded($node);
		}
	}

	/**
	 * Discards the given nodes.
	 *
	 * @param array<\TYPO3\TYPO3CR\Domain\Model\NodeInterface> $nodes The nodes to discard
	 * @return void
	 * @api
	 */
	public function discardNodes(array $nodes) {
		foreach ($nodes as $node) {
			$this->discardNode($node);
		}
	}

	/**
	 * Sort an unsorted list of nodes in a publishable order
	 *
	 * @param array $nodes Unsorted list of nodes (unpublished nodes)
	 * @return array Sorted list of nodes for publishing
	 * @throws WorkspaceException
	 */
	protected function sortNodesForPublishing(array $nodes) {
		$sorter = new NodePublishingDependencySolver();
		return $sorter->sort($nodes);
	}

	/**
	 * Signals that a node has been published.
	 *
	 * The signal emits the source node and target workspace, i.e. the node contains its source
	 * workspace.
	 *
	 * @param NodeInterface $node
	 * @param Workspace $targetWorkspace
	 * @return void
	 * @Flow\Signal
	 * @api
	 */
	public function emitNodePublished(NodeInterface $node, Workspace $targetWorkspace = NULL) {
	}

	/**
	 * Signals that a node has been discarded.
	 *
	 * The signal emits the node that has been discarded.
	 *
	 * @param NodeInterface $node
	 * @return void
	 * @Flow\Signal
	 * @api
	 */
	public function emitNodeDiscarded(NodeInterface $node) {
	}

	/**
	 * Creates a new content context based on the given workspace and the NodeData object.
	 *
	 * @param Workspace $workspace Workspace for the new context
	 * @param array $dimensionValues The dimension values for the new context
	 * @param array $contextProperties Additional pre-defined context properties
	 * @return Context
	 */
	protected function createContext(Workspace $workspace, array $dimensionValues, array $contextProperties = array()) {
		$contextProperties += array(
			'workspaceName' => $workspace->getName(),
			'inaccessibleContentShown' => TRUE,
			'invisibleContentShown' => TRUE,
			'removedContentShown' => TRUE,
			'dimensions' => $dimensionValues
		);

		return $this->contextFactory->create($contextProperties);
	}

}
