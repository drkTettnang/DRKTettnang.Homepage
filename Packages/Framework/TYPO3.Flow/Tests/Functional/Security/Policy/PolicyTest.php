<?php
namespace TYPO3\Flow\Tests\Functional\Security\Policy;

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
 * Testcase for the security policy behavior
 */
class PolicyTest extends \TYPO3\Flow\Tests\FunctionalTestCase {

	/**
	 * @var boolean
	 */
	protected $testableSecurityEnabled = TRUE;

	/**
	 * @test
	 */
	public function nonAuthenticatedUsersHaveTheEverybodyAndAnonymousRole() {
		$hasEverybodyRole = FALSE;
		$hasAnonymousRole = FALSE;

		foreach ($this->securityContext->getRoles() as $role) {
			if ((string)$role === 'TYPO3.Flow:Everybody') {
				$hasEverybodyRole = TRUE;
			}
			if ((string)$role === 'TYPO3.Flow:Anonymous') {
				$hasAnonymousRole = TRUE;
			}
		}

		$this->assertEquals(2, count($this->securityContext->getRoles()));

		$this->assertTrue($this->securityContext->hasRole('TYPO3.Flow:Everybody'), 'Everybody - hasRole()');
		$this->assertTrue($hasEverybodyRole, 'Everybody - getRoles()');

		$this->assertTrue($this->securityContext->hasRole('TYPO3.Flow:Anonymous'), 'Anonymous - hasRole()');
		$this->assertTrue($hasAnonymousRole, 'Anonymous - getRoles()');
	}
}
