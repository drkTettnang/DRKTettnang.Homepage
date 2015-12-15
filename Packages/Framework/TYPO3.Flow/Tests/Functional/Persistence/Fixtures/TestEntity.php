<?php
namespace TYPO3\Flow\Tests\Functional\Persistence\Fixtures;

/*                                                                        *
 * This script belongs to the TYPO3 Flow framework.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Object\ObjectManagerInterface;
use TYPO3\Flow\Tests\Functional\Persistence\Fixtures\SubEntity as ImportedSubEntity;

/**
 * A simple entity for persistence tests
 *
 * @Flow\Entity
 * @ORM\Table(name="persistence_testentity")
 */
class TestEntity {

	/**
	 * @var ObjectManagerInterface
	 * @Flow\Inject
	 */
	protected $objectManager;

	/**
	 * @var string
	 * @Flow\Validate(type="StringLength", options={"minimum"=3})
	 */
	protected $name = '';

	/**
	 * @var TestEntity
	 * @ORM\ManyToOne
	 */
	protected $relatedEntity;

	/**
	 * @var Collection<ImportedSubEntity>
	 * @ORM\OneToMany(mappedBy="parentEntity")
	 */
	protected $subEntities;

	/**
	 * @var TestValueObject
	 * @ORM\ManyToOne
	 */
	protected $relatedValueObject;

	/**
	 * @var string
	 * @Flow\Validate(type="NotEmpty", validationGroups={"SomeOther"})
	 */
	protected $description = 'This is some text';

	/**
	 * @var array
	 */
	protected $arrayProperty = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->subEntities = new ArrayCollection();
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @param string $name
	 * @return void
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * @param string $description
	 */
	public function setDescription($description) {
		$this->description = $description;
	}

	/**
	 * @param array $arrayProperty
	 * @return void
	 */
	public function setArrayProperty($arrayProperty) {
		$this->arrayProperty = $arrayProperty;
	}

	/**
	 * @return array
	 */
	public function getArrayProperty() {
		return $this->arrayProperty;
	}

	/**
	 * @return string
	 */
	public function sayHello() {
		return 'Hello';
	}

	/**
	 * @param TestEntity $relatedEntity
	 * @return void
	 */
	public function setRelatedEntity(TestEntity $relatedEntity) {
		$this->relatedEntity = $relatedEntity;
	}

	/**
	 * @return TestEntity
	 */
	public function getRelatedEntity() {
		return $this->relatedEntity;
	}

	/**
	 * @param Collection<ImportedSubEntity> $subEntities
	 * @return void
	 */
	public function setSubEntities(Collection $subEntities) {
		$this->subEntities = $subEntities;
	}

	/**
	 * @param ImportedSubEntity $subEntity
	 * @return void
	 */
	public function addSubEntity(ImportedSubEntity $subEntity) {
		$this->subEntities->add($subEntity);
	}

	/**
	 * @return Collection<ImportedSubEntity>
	 */
	public function getRelatedEntities() {
		return $this->subEntities;
	}

	/**
	 * @return ObjectManagerInterface
	 */
	public function getObjectManager() {
		return $this->objectManager;
	}

	/**
	 * @param TestValueObject $relatedValueObject
	 * @return void
	 */
	public function setRelatedValueObject($relatedValueObject) {
		$this->relatedValueObject = $relatedValueObject;
	}

	/**
	 * @return TestValueObject
	 */
	public function getRelatedValueObject() {
		return $this->relatedValueObject;
	}
}
