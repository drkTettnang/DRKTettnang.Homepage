<?php
namespace TYPO3\Flow\Property\TypeConverter;

/*                                                                        *
 * This script belongs to the TYPO3 Flow framework.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * Type converter which provides sensible default implementations for most methods. If you extend this class
 * you only need to do the following:
 *
 * - set $sourceTypes
 * - set $targetType
 * - set $priority
 * - implement convertFrom()
 *
 * @api
 * @Flow\Scope("singleton")
 */
abstract class AbstractTypeConverter implements \TYPO3\Flow\Property\TypeConverterInterface {

	/**
	 * The source types this converter can convert.
	 *
	 * @var array<string>
	 * @api
	 */
	protected $sourceTypes = array();

	/**
	 * The target type this converter can convert to.
	 *
	 * @var string
	 * @api
	 */
	protected $targetType = '';

	/**
	 * The priority for this converter.
	 *
	 * @var integer
	 * @api
	 */
	protected $priority;

	/**
	 * Returns the list of source types the TypeConverter can handle.
	 * Must be PHP simple types, classes or object is not allowed.
	 *
	 * @return array<string>
	 * @api
	 */
	public function getSupportedSourceTypes() {
		return $this->sourceTypes;
	}

	/**
	 * Return the target type this TypeConverter converts to.
	 * Can be a simple type or a class name.
	 *
	 * @return string
	 * @api
	 */
	public function getSupportedTargetType() {
		return $this->targetType;
	}

	/**
	 * Returns the $originalTargetType unchanged in this implementation.
	 *
	 * @param mixed $source the source data
	 * @param string $originalTargetType the type we originally want to convert to
	 * @param \TYPO3\Flow\Property\PropertyMappingConfigurationInterface $configuration
	 * @return string
	 * @api
	 */
	public function getTargetTypeForSource($source, $originalTargetType, \TYPO3\Flow\Property\PropertyMappingConfigurationInterface $configuration = NULL) {
		return $originalTargetType;
	}

	/**
	 * Return the priority of this TypeConverter. TypeConverters with a high priority are chosen before low priority.
	 *
	 * @return integer
	 * @api
	 */
	public function getPriority() {
		return $this->priority;
	}

	/**
	 * This implementation always returns TRUE for this method.
	 *
	 * @param mixed $source the source data
	 * @param string $targetType the type to convert to.
	 * @return boolean TRUE if this TypeConverter can convert from $source to $targetType, FALSE otherwise.
	 * @api
	 */
	public function canConvertFrom($source, $targetType) {
		return TRUE;
	}

	/**
	 * Returns an empty list of sub property names
	 *
	 * @param mixed $source
	 * @return array<string>
	 * @api
	 */
	public function getSourceChildPropertiesToBeConverted($source) {
		return array();
	}

	/**
	 * This method is never called, as getSourceChildPropertiesToBeConverted() returns an empty array.
	 *
	 * @param string $targetType
	 * @param string $propertyName
	 * @param \TYPO3\Flow\Property\PropertyMappingConfigurationInterface $configuration
	 * @return string
	 * @api
	 */
	public function getTypeOfChildProperty($targetType, $propertyName, \TYPO3\Flow\Property\PropertyMappingConfigurationInterface $configuration) {
	}
}
