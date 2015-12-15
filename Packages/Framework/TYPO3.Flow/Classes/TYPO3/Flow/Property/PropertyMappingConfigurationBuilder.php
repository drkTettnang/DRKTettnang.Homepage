<?php
namespace TYPO3\Flow\Property;

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
 * This builder creates the default configuration for Property Mapping, if no configuration has been passed to the Property Mapper.
 *
 * @Flow\Scope("singleton")
 */
class PropertyMappingConfigurationBuilder {

	/**
	 * Builds the default property mapping configuration.
	 *
	 * @param string $type the implementation class name of the PropertyMappingConfiguration to instantiate; must be a subclass of TYPO3\Flow\Property\PropertyMappingConfiguration
	 * @return \TYPO3\Flow\Property\PropertyMappingConfiguration
	 */
	public function build($type = 'TYPO3\Flow\Property\PropertyMappingConfiguration') {
		$configuration = new $type();

		$configuration->setTypeConverterOptions('TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter', array(
			\TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED => TRUE,
			\TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter::CONFIGURATION_MODIFICATION_ALLOWED => TRUE
		));
		$configuration->allowAllProperties();

		return $configuration;
	}
}
