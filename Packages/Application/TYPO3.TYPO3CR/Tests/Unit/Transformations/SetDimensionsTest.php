<?php
namespace TYPO3\TYPO3CR\Tests\Unit\Transformations;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.TYPO3CR".         *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Tests\UnitTestCase;
use TYPO3\TYPO3CR\Domain\Model\ContentDimension;
use TYPO3\TYPO3CR\Domain\Model\NodeData;
use TYPO3\TYPO3CR\Domain\Repository\ContentDimensionRepository;
use TYPO3\TYPO3CR\Migration\Transformations\SetDimensions;

/**
 * Testcase for the SetDimensions transformation
 */
class SetDimensionsTest extends UnitTestCase {

	/**
	 * @return array
	 */
	public function setDimensionsInput() {
		return array(
			// single dimension, single value
			array(
				array(
					'language' => array('en')
				),
				array(
					array('language' => 'en')
				)
			),
			// single dimension, two values
			array(
				array(
					'system' => array('iOS', 'Android')
				),
				array(
					array('system' => 'iOS'),
					array('system' => 'Android')
				)
			),
			// two dimension, single values
			array(
				array(
					'language' => array('lv'),
					'system' => array('Neos')
				),
				array(
					array('language' => 'lv'),
					array('system' => 'Neos')
				)
			),
			// two dimension, multiple values
			array(
				array(
					'language' => array('lv'),
					'system' => array('Neos', 'Flow')
				),
				array(
					array('language' => 'lv'),
					array('system' => 'Neos'),
					array('system' => 'Flow')
				)
			),
		);
	}

	/**
	 * @dataProvider setDimensionsInput
	 * @test
	 * @param array $setValues The values passed to the transformation
	 * @param array $expectedValues The values that are expected to be set on the node
	 * @param array $configuredDimensions Optional set of dimensions "configured in the system"
	 */
	public function setDimensionsWorksAsExpected(array $setValues, array $expectedValues, array $configuredDimensions = NULL) {
		$transformation = new SetDimensions();

		$transformation->setAddDefaultDimensionValues($configuredDimensions !== NULL);
		$transformation->setDimensionValues($setValues);

		if ($configuredDimensions !== NULL) {
			$configuredDimensionObjects = array();
			foreach ($configuredDimensions as $dimensionIdentifier => $dimensionDefault) {
				$configuredDimensionObjects[] = new ContentDimension($dimensionIdentifier, $dimensionDefault);
			}

			$mockContentDimensionRepository = $this->getMockBuilder(ContentDimensionRepository::class)->getMock();
			$mockContentDimensionRepository->expects($this->atLeastOnce())->method('findAll')->will($this->returnValue($configuredDimensionObjects));
			$this->inject($transformation, 'contentDimensionRepository', $mockContentDimensionRepository);
		}

		$expected = array(
			'count' => count($expectedValues),
			'dimensions' => $expectedValues
		);

		$mockNode = $this->getMockBuilder(NodeData::class)->disableOriginalConstructor()->getMock();
		$mockNode->expects($this->once())->method('setDimensions')->with($this->callback(function (array $dimensions) use ($expected) {
			if (count($dimensions) === $expected['count']) {
				$simplifiedDimensions = array();
				foreach ($dimensions as $dimension) {
					if (!($dimension instanceof \TYPO3\TYPO3CR\Domain\Model\NodeDimension)) {
						return FALSE;
					}
					$simplifiedDimensions[] = array($dimension->getName() => $dimension->getValue());
				}
				if ($expected['dimensions'] === $simplifiedDimensions) {
					return TRUE;
				}
			}

			return FALSE;
		}));

		$transformation->execute($mockNode);
	}

	/**
	 * @test
	 */
	public function setDimensionsFillsInDefaultDimensionsAndValues() {
		$dimensionsToBeSet = array(
			'language' => array('lv'),
			'system' => array('Neos')
		);

		$expectedDimensions = array(
			array('language' => 'lv'),
			array('system' => 'Neos'),
			array('country' => 'New Zealand')
		);

		$configuredDimensions = array(
			'language' => 'en',
			'system' => 'Symfony',
			'country' => 'New Zealand'
		);

		$this->setDimensionsWorksAsExpected($dimensionsToBeSet, $expectedDimensions, $configuredDimensions);
	}
}
