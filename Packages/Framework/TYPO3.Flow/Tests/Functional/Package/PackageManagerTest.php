<?php
namespace TYPO3\Flow\Tests\Functional\Package;

/*                                                                        *
 * This script belongs to the TYPO3 Flow framework.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Tests\FunctionalTestCase;

/**
 * Testcase for Package Manager
 *
 */
class PackageManagerTest extends FunctionalTestCase {

	/**
	 *
	 * @var \TYPO3\Flow\Package\PackageManager
	 */
	protected $packageManager;

	/**
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$this->packageManager = $this->objectManager->get('TYPO3\Flow\Package\PackageManagerInterface');
	}

	/**
	 * @test
	 */
	public function getPackageOfObjectReturnsCorrectPackageForAnExistingObject() {
		$package = $this->packageManager->getPackageOfObject($this);
		$this->assertSame('TYPO3.Flow', $package->getPackageKey());
	}

	/**
	 * @test
	 */
	public function getPackageOfObjectReturnsCorrectPackageForAnExistingProxyObject() {
		$account = new \TYPO3\Flow\Security\Account();
		$package = $this->packageManager->getPackageOfObject($account);
		$this->assertSame('TYPO3.Flow', $package->getPackageKey());
	}

	/**
	 * @test
	 */
	public function getPackageOfObjectReturnsNullForObjectsThatDontBelongToAPackage() {
		$genericObject = new \stdClass();
		$this->assertNull($this->packageManager->getPackageOfObject($genericObject));
	}

	/**
	 * @test
	 */
	public function getPackageByClassNameReturnsCorrectPackageForAnExistingClass() {
		$existingClassName = get_class($this);
		$package = $this->packageManager->getPackageByClassName($existingClassName);
		$this->assertSame('TYPO3.Flow', $package->getPackageKey());
	}

	/**
	 * @test
	 */
	public function getPackageByClassNameReturnsNullForNonExistingClasses() {
		$nonExistingClassName = 'SomeNonExistingClass';
		$this->assertNull($this->packageManager->getPackageByClassName($nonExistingClassName));
	}

	/**
	 * @test
	 */
	public function getPackageByClassNameReturnsNullForClassesThatAreNotPartOfAPackage() {
		$globalClassName = 'stdClass';
		$this->assertNull($this->packageManager->getPackageByClassName($globalClassName));
	}

}
