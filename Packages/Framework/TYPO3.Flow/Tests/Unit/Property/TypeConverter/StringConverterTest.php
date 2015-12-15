<?php
namespace TYPO3\Flow\Tests\Unit\Property\TypeConverter;

/*                                                                        *
 * This script belongs to the TYPO3 Flow framework.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Property\TypeConverter\StringConverter;

/**
 * Testcase for the String converter
 *
 * @covers \TYPO3\Flow\Property\TypeConverter\StringConverter<extended>
 */
class StringConverterTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @var \TYPO3\Flow\Property\TypeConverterInterface
	 */
	protected $converter;

	public function setUp() {
		$this->converter = new StringConverter();
	}

	/**
	 * @test
	 */
	public function checkMetadata() {
		$this->assertEquals(array('string', 'integer', 'float', 'boolean', 'array', 'DateTime'), $this->converter->getSupportedSourceTypes(), 'Source types do not match');
		$this->assertEquals('string', $this->converter->getSupportedTargetType(), 'Target type does not match');
		$this->assertEquals(1, $this->converter->getPriority(), 'Priority does not match');
	}

	/**
	 * @test
	 */
	public function convertFromShouldReturnSourceString() {
		$this->assertEquals('myString', $this->converter->convertFrom('myString', 'string'));
	}

	/**
	 * @test
	 */
	public function canConvertFromShouldReturnTrue() {
		$this->assertTrue($this->converter->canConvertFrom('myString', 'string'));
	}

	/**
	 * @test
	 */
	public function getSourceChildPropertiesToBeConvertedShouldReturnEmptyArray() {
		$this->assertEquals(array(), $this->converter->getSourceChildPropertiesToBeConverted('myString'));
	}


	public function arrayToStringDataProvider() {
		return array(
			array(array('Foo', 'Bar', 'Baz'), 'Foo,Bar,Baz', array()),
			array(array('Foo', 'Bar', 'Baz'), 'Foo, Bar, Baz', array(StringConverter::CONFIGURATION_CSV_DELIMITER => ', ')),
			array(array(), '', array()),
			array(array(1,2, 'foo'), '[1,2,"foo"]', array(StringConverter::CONFIGURATION_ARRAY_FORMAT => StringConverter::ARRAY_FORMAT_JSON))
		);
	}

	/**
	 * @test
	 * @dataProvider arrayToStringDataProvider
	 */
	public function canConvertFromStringToArray($source, $expectedResult, $mappingConfiguration) {

		// Create a map of arguments to return values.
		$configurationValueMap = array();
		foreach ($mappingConfiguration as $setting => $value) {
			$configurationValueMap[] = array('TYPO3\Flow\Property\TypeConverter\StringConverter', $setting, $value);
		}

		$propertyMappingConfiguration = $this->getMock('\TYPO3\Flow\Property\PropertyMappingConfiguration');
		$propertyMappingConfiguration
			->expects($this->any())
			->method('getConfigurationValue')
			->will($this->returnValueMap($configurationValueMap));

		$this->assertEquals($expectedResult, $this->converter->convertFrom($source, 'array', array(), $propertyMappingConfiguration));
	}
}
