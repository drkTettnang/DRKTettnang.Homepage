<?php
namespace TYPO3\Neos\TYPO3CR\Transformations;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Neos".            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Doctrine\Common\Persistence\ObjectManager;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Persistence\PersistenceManagerInterface;
use TYPO3\Flow\Reflection\ObjectAccess;
use TYPO3\Flow\Utility\TypeHandling;
use TYPO3\Media\Domain\Model\Asset;
use TYPO3\TYPO3CR\Domain\Model\NodeData;
use TYPO3\TYPO3CR\Migration\Transformations\AbstractTransformation;

/**
 * Convert serialized Assets to references.
 */
class AssetTransformation extends AbstractTransformation {

	/**
	 * @Flow\Inject
	 * @var PersistenceManagerInterface
	 */
	protected $persistenceManager;

	/**
	 * Doctrine's Entity Manager. Note that "ObjectManager" is the name of the related interface.
	 *
	 * @Flow\Inject
	 * @var ObjectManager
	 */
	protected $entityManager;

	/**
	 * @param NodeData $node
	 * @return boolean
	 */
	public function isTransformable(NodeData $node) {
		return TRUE;
	}

	/**
	 * Change the property on the given node.
	 *
	 * @param NodeData $node
	 * @return void
	 */
	public function execute(NodeData $node) {
		foreach ($node->getNodeType()->getProperties() as $propertyName => $propertyConfiguration) {
			if (isset($propertyConfiguration['type']) && in_array(trim($propertyConfiguration['type']), $this->getHandledObjectTypes())) {
				if (!isset($nodeProperties)) {
					$nodeRecordQuery = $this->entityManager->getConnection()->prepare('SELECT properties FROM typo3_typo3cr_domain_model_nodedata WHERE persistence_object_identifier=?');
					$nodeRecordQuery->execute([$this->persistenceManager->getIdentifierByObject($node)]);
					$nodeRecord = $nodeRecordQuery->fetch(\PDO::FETCH_ASSOC);
					$nodeProperties = unserialize($nodeRecord['properties']);
				}

				if (!isset($nodeProperties[$propertyName]) || !is_object($nodeProperties[$propertyName])) {
					continue;
				}

				/** @var Asset $assetObject */
				$assetObject = $nodeProperties[$propertyName];
				$nodeProperties[$propertyName] = NULL;

				$stream = $assetObject->getResource()->getStream();

				if ($stream === FALSE) {
					continue;
				}

				fclose($stream);
				$objectType = TypeHandling::getTypeForValue($assetObject);
				$objectIdentifier = ObjectAccess::getProperty($assetObject, 'Persistence_Object_Identifier', TRUE);

				$nodeProperties[$propertyName] = array(
					'__flow_object_type' => $objectType,
					'__identifier' => $objectIdentifier
				);
			}
		}

		if (isset($nodeProperties)) {
			$nodeUpdateQuery = $this->entityManager->getConnection()->prepare('UPDATE typo3_typo3cr_domain_model_nodedata SET properties=? WHERE persistence_object_identifier=?');
			$nodeUpdateQuery->execute([serialize($nodeProperties), $this->persistenceManager->getIdentifierByObject($node)]);
		}
	}

	/**
	 * @return array
	 */
	protected function getHandledObjectTypes() {
		return array (
			'TYPO3\Media\Domain\Model\Asset',
			'TYPO3\Media\Domain\Model\Audio',
			'TYPO3\Media\Domain\Model\Document',
			'TYPO3\Media\Domain\Model\Video'
		);
	}
}
