<?php
namespace TYPO3\Neos\Tests\Unit\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Neos".            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Testcase for the "Domain" domain model
 *
 */
class DomainTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @test
	 */
	public function setHostPatternAllowsForSettingTheHostPatternOfTheDomain() {
		$domain = new \TYPO3\Neos\Domain\Model\Domain();
		$domain->setHostPattern('typo3.com');
		$this->assertSame('typo3.com', $domain->getHostPattern());
	}

	/**
	 * @test
	 */
	public function setSiteSetsTheSiteTheDomainIsPointingTo() {
		$mockSite = $this->getMock('TYPO3\Neos\Domain\Model\Site', array(), array(), '', FALSE);

		$domain = new \TYPO3\Neos\Domain\Model\Domain;
		$domain->setSite($mockSite);
		$this->assertSame($mockSite, $domain->getSite());
	}
}
