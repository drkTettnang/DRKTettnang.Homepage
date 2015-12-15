<?php
namespace TYPO3\Flow\Tests\Functional\Property;

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
 * Testcase for Property Mapper
 *
 */
class PropertyMapperTest extends \TYPO3\Flow\Tests\FunctionalTestCase {

	/**
	 *
	 * @var \TYPO3\Flow\Property\PropertyMapper
	 */
	protected $propertyMapper;

	static protected $testablePersistenceEnabled = TRUE;

	/**
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$this->propertyMapper = $this->objectManager->get('TYPO3\Flow\Property\PropertyMapper');
	}

	/**
	 * @test
	 */
	public function domainObjectWithSimplePropertiesCanBeCreated() {
		$source = array(
			'name' => 'Robert Skaarhoj',
			'age' => '25',
			'averageNumberOfKids' => '1.5'
		);

		$result = $this->propertyMapper->convert($source, 'TYPO3\Flow\Tests\Functional\Property\Fixtures\TestEntity');
		$this->assertSame('Robert Skaarhoj', $result->getName());
		$this->assertSame(25, $result->getAge());
		$this->assertSame(1.5, $result->getAverageNumberOfKids());
	}

	/**
	 * @test
	 */
	public function domainObjectWithVirtualPropertiesCanBeCreated() {
		$source = array(
			'name' => 'Robert Skaarhoj',
			'yearOfBirth' => '1988',
			'averageNumberOfKids' => '1.5'
		);

		$result = $this->propertyMapper->convert($source, 'TYPO3\Flow\Tests\Functional\Property\Fixtures\TestEntity');
		$this->assertSame('Robert Skaarhoj', $result->getName());
		$this->assertSame(25, $result->getAge());
		$this->assertSame(1.5, $result->getAverageNumberOfKids());
	}

	/**
	 * @test
	 */
	public function simpleObjectWithSimplePropertiesCanBeCreated() {
		$source = array(
			'name' => 'Christopher',
			'size' => '187',
			'signedCla' => TRUE
		);

		$result = $this->propertyMapper->convert($source, 'TYPO3\Flow\Tests\Functional\Property\Fixtures\TestClass');
		$this->assertSame('Christopher', $result->getName());
		$this->assertSame(187, $result->getSize());
		$this->assertSame(TRUE, $result->getSignedCla());
	}

	/**
	 * @test
	 */
	public function valueobjectCanBeMapped() {
		$source = array(
			'__identity' => 'abcdefghijkl',
			'name' => 'Christopher',
			'age' => '28'
		);

		$result = $this->propertyMapper->convert($source, 'TYPO3\Flow\Tests\Functional\Property\Fixtures\TestValueobject');
		$this->assertSame('Christopher', $result->getName());
		$this->assertSame(28, $result->getAge());
	}

	/**
	 * @test
	 */
	public function integerCanBeMappedToString() {
		$source = array(
			'name' => 42,
			'size' => 23
		);

		$result = $this->propertyMapper->convert($source, 'TYPO3\Flow\Tests\Functional\Property\Fixtures\TestClass');
		$this->assertSame('42', $result->getName());
		$this->assertSame(23, $result->getSize());
	}

	/**
	 * @test
	 */
	public function targetTypeForEntityCanBeOverridenIfConfigured() {
		$source = array(
			'__type' => 'TYPO3\Flow\Tests\Functional\Property\Fixtures\TestEntitySubclass',
			'name' => 'Arthur',
			'age' => '42'
		);

		$configuration = $this->objectManager->get('TYPO3\Flow\Property\PropertyMappingConfigurationBuilder')->build();
		$configuration->setTypeConverterOption('TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter', \TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter::CONFIGURATION_OVERRIDE_TARGET_TYPE_ALLOWED, TRUE);

		$result = $this->propertyMapper->convert($source, 'TYPO3\Flow\Tests\Functional\Property\Fixtures\TestEntity', $configuration);
		$this->assertInstanceOf('\TYPO3\Flow\Tests\Functional\Property\Fixtures\TestEntitySubclass', $result);
	}

	/**
	 * @test
	 * @expectedException \TYPO3\Flow\Property\Exception
	 */
	public function overridenTargetTypeForEntityMustBeASubclass() {
		$source = array(
			'__type' => 'TYPO3\Flow\Tests\Functional\Property\Fixtures\TestClass',
			'name' => 'A horse'
		);

		$configuration = $this->objectManager->get('TYPO3\Flow\Property\PropertyMappingConfigurationBuilder')->build();
		$configuration->setTypeConverterOption('TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter', \TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter::CONFIGURATION_OVERRIDE_TARGET_TYPE_ALLOWED, TRUE);

		$this->propertyMapper->convert($source, 'TYPO3\Flow\Tests\Functional\Property\Fixtures\TestEntity', $configuration);
	}

	/**
	 * @test
	 */
	public function targetTypeForSimpleObjectCanBeOverridenIfConfigured() {
		$source = array(
			'__type' => 'TYPO3\Flow\Tests\Functional\Property\Fixtures\TestSubclass',
			'name' => 'Tower of Pisa'
		);

		$configuration = $this->objectManager->get('TYPO3\Flow\Property\PropertyMappingConfigurationBuilder')->build();
		$configuration->setTypeConverterOption('TYPO3\Flow\Property\TypeConverter\ObjectConverter', \TYPO3\Flow\Property\TypeConverter\ObjectConverter::CONFIGURATION_OVERRIDE_TARGET_TYPE_ALLOWED, TRUE);

		$result = $this->propertyMapper->convert($source, 'TYPO3\Flow\Tests\Functional\Property\Fixtures\TestClass', $configuration);
		$this->assertInstanceOf('TYPO3\Flow\Tests\Functional\Property\Fixtures\TestSubclass', $result);
	}

	/**
	 * @test
	 * @expectedException \TYPO3\Flow\Property\Exception
	 */
	public function overridenTargetTypeForSimpleObjectMustBeASubclass() {
		$source = array(
			'__type' => 'TYPO3\Flow\Tests\Functional\Property\Fixtures\TestEntity',
			'name' => 'A horse'
		);

		$configuration = $this->objectManager->get('TYPO3\Flow\Property\PropertyMappingConfigurationBuilder')->build();
		$configuration->setTypeConverterOption('TYPO3\Flow\Property\TypeConverter\ObjectConverter', \TYPO3\Flow\Property\TypeConverter\ObjectConverter::CONFIGURATION_OVERRIDE_TARGET_TYPE_ALLOWED, TRUE);

		$this->propertyMapper->convert($source, 'TYPO3\Flow\Tests\Functional\Property\Fixtures\TestClass', $configuration);
	}

	/**
	 * @test
	 */
	public function mappingPersistentEntityOnlyChangesModifiedProperties() {
		$entityIdentity = $this->createTestEntity();

		$source = array(
			'__identity' => $entityIdentity,
			'averageNumberOfKids' => '5.5'
		);

		$result = $this->propertyMapper->convert($source, 'TYPO3\Flow\Tests\Functional\Property\Fixtures\TestEntity');
		$this->assertSame('Egon Olsen', $result->getName());
		$this->assertSame(42, $result->getAge());
		$this->assertSame(5.5, $result->getAverageNumberOfKids());
	}

	/**
	 * @test
	 */
	public function mappingPersistentEntityAllowsToSetValueToNull() {
		$entityIdentity = $this->createTestEntity();

		$source = array(
			'__identity' => $entityIdentity,
			'averageNumberOfKids' => ''
		);

		$result = $this->propertyMapper->convert($source, 'TYPO3\Flow\Tests\Functional\Property\Fixtures\TestEntity');
		$this->assertSame('Egon Olsen', $result->getName());
		$this->assertSame(42, $result->getAge());
		$this->assertSame(NULL, $result->getAverageNumberOfKids());
	}

	/**
	 * @test
	 */
	public function mappingOfPropertiesWithUnqualifiedInterfaceName() {
		$relatedEntity = new Fixtures\TestEntity();

		$source = array(
			'relatedEntity' => $relatedEntity,
		);
		$result = $this->propertyMapper->convert($source, 'TYPO3\Flow\Tests\Functional\Property\Fixtures\TestEntity');
		$this->assertSame($relatedEntity, $result->getRelatedEntity());
	}

	/**
	 * Testcase for http://forge.typo3.org/issues/36988 - needed for Neos
	 * editing
	 *
	 * @test
	 */
	public function ifTargetObjectTypeIsPassedAsArgumentDoNotConvertIt() {
		$entity = new Fixtures\TestEntity();
		$entity->setName('Egon Olsen');

		$result = $this->propertyMapper->convert($entity, 'TYPO3\Flow\Tests\Functional\Property\Fixtures\TestEntity');
		$this->assertSame($entity, $result);
	}

	/**
	 * Testcase for http://forge.typo3.org/issues/39445
	 *
	 * @test
	 */
	public function ifTargetObjectTypeIsPassedRecursivelyDoNotConvertIt() {
		$entity = new Fixtures\TestEntity();
		$entity->setName('Egon Olsen');

		$result = $this->propertyMapper->convert(array($entity), 'array<TYPO3\Flow\Tests\Functional\Property\Fixtures\TestEntity>');
		$this->assertSame(array($entity), $result);
	}

	/**
	 * Add and persist a test entity, and return the identifier of the newly created
	 * entity.
	 *
	 * @return string identifier of newly created entity
	 */
	protected function createTestEntity() {
		$entity = new Fixtures\TestEntity();
		$entity->setName('Egon Olsen');
		$entity->setAge(42);
		$entity->setAverageNumberOfKids(3.5);
		$this->persistenceManager->add($entity);
		$entityIdentifier = $this->persistenceManager->getIdentifierByObject($entity);

		$this->persistenceManager->persistAll();
		$this->persistenceManager->clearState();

		return $entityIdentifier;
	}

	/**
	 * Testcase for #32829
	 *
	 * @test
	 */
	public function mappingToFieldsFromSubclassWorksIfTargetTypeIsOverridden() {
		$source = array(
			'__type' => 'TYPO3\Flow\Tests\Functional\Property\Fixtures\TestEntitySubclassWithNewField',
			'testField' => 'A horse'
		);

		$configuration = $this->objectManager->get('TYPO3\Flow\Property\PropertyMappingConfigurationBuilder')->build();
		$configuration->setTypeConverterOption('TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter', \TYPO3\Flow\Property\TypeConverter\ObjectConverter::CONFIGURATION_OVERRIDE_TARGET_TYPE_ALLOWED, TRUE);

		$theHorse = $this->propertyMapper->convert($source, 'TYPO3\Flow\Tests\Functional\Property\Fixtures\TestEntity', $configuration);
		$this->assertInstanceOf('TYPO3\Flow\Tests\Functional\Property\Fixtures\TestEntitySubclassWithNewField', $theHorse);
	}

	/**
	 * @test
	 * @dataProvider invalidTypeConverterConfigurationsForOverridingTargetTypes
	 * @expectedException \TYPO3\Flow\Property\Exception
	 */
	public function mappingToFieldsFromSubclassThrowsExceptionIfTypeConverterOptionIsInvalidOrNotSet(\TYPO3\Flow\Property\PropertyMappingConfigurationInterface $configuration = NULL) {
		$source = array(
			'__type' => 'TYPO3\Flow\Tests\Functional\Property\Fixtures\TestEntitySubclassWithNewField',
			'testField' => 'A horse'
		);

		$this->propertyMapper->convert($source, 'TYPO3\Flow\Tests\Functional\Property\Fixtures\TestEntity', $configuration);
	}

	/**
	 * Data provider with invalid configuration for target type overrides
	 *
	 * @return array
	 */
	public function invalidTypeConverterConfigurationsForOverridingTargetTypes() {
		$configurationWithNoSetting = new \TYPO3\Flow\Property\PropertyMappingConfiguration();

		$configurationWithOverrideOff = new \TYPO3\Flow\Property\PropertyMappingConfiguration();
		$configurationWithOverrideOff->setTypeConverterOption('TYPO3\Flow\Property\TypeConverter\ObjectConverter', \TYPO3\Flow\Property\TypeConverter\ObjectConverter::CONFIGURATION_OVERRIDE_TARGET_TYPE_ALLOWED, FALSE);

		return array(
			array(NULL),
			array($configurationWithNoSetting),
			array($configurationWithOverrideOff),
		);
	}

	/**
	 * @test
	 * @expectedException \TYPO3\Flow\Property\Exception
	 */
	public function convertFromShouldThrowExceptionIfGivenSourceTypeIsNotATargetType() {
		$source = array(
			'__type' => 'TYPO3\Flow\Tests\Functional\Property\Fixtures\TestClass',
			'testField' => 'A horse'
		);

		$configuration = $this->objectManager->get('TYPO3\Flow\Property\PropertyMappingConfigurationBuilder')->build();
		$configuration->setTypeConverterOption('TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter', \TYPO3\Flow\Property\TypeConverter\ObjectConverter::CONFIGURATION_OVERRIDE_TARGET_TYPE_ALLOWED, TRUE);

		$this->propertyMapper->convert($source, 'TYPO3\Flow\Tests\Functional\Property\Fixtures\TestEntity', $configuration);
	}

	/**
	 * Test case for #47232
	 *
	 * @test
	 */
	public function convertedAccountRolesCanBeSet() {
		$source = array(
			'accountIdentifier' => 'someAccountIdentifier',
			'credentialsSource' => 'someEncryptedStuff',
			'authenticationProviderName' => 'DefaultProvider',
			'roles' => array('TYPO3.Flow:Customer', 'TYPO3.Flow:Administrator')
		);

		$expectedRoleIdentifiers = array('TYPO3.Flow:Customer', 'TYPO3.Flow:Administrator');

		$configuration = $this->objectManager->get('TYPO3\Flow\Property\PropertyMappingConfigurationBuilder')->build();
		$configuration->forProperty('roles.*')->allowProperties();

		$account = $this->propertyMapper->convert($source, 'TYPO3\Flow\Security\Account', $configuration);

		$this->assertInstanceOf('\TYPO3\Flow\Security\Account', $account);
		$this->assertEquals(2, count($account->getRoles()));
		$this->assertEquals($expectedRoleIdentifiers, array_keys($account->getRoles()));
	}

	/**
	 * @test
	 */
	public function persistentEntityCanBeSerializedToIdentifierUsingObjectSource() {
		$entity = new Fixtures\TestEntity();
		$entity->setName('Egon Olsen');
		$entity->setAge(42);
		$entity->setAverageNumberOfKids(3.5);
		$this->persistenceManager->add($entity);

		$entityIdentifier = $this->persistenceManager->getIdentifierByObject($entity);

		$this->persistenceManager->persistAll();
		$this->persistenceManager->clearState();

		$source = $entity;

		$result = $this->propertyMapper->convert($source, 'string');

		$this->assertSame($entityIdentifier, $result);
	}

}
