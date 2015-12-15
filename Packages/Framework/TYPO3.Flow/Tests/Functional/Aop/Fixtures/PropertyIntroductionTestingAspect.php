<?php
namespace TYPO3\Flow\Tests\Functional\Aop\Fixtures;

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
 * An aspect for testing the basic functionality of the AOP framework
 *
 * @Flow\Aspect
 */
class PropertyIntroductionTestingAspect {

	/**
	 * @Flow\Introduce("class(TYPO3\Flow\Tests\Functional\Aop\Fixtures\TargetClass04)")
	 * @var string
	 */
	protected $introducedProtectedProperty;

	/**
	 * @Flow\Introduce("class(TYPO3\Flow\Tests\Functional\Aop\Fixtures\TargetClass04)")
	 * @var array
	 */
	public $introducedPublicProperty;

	/**
	 * @Flow\Introduce("class(TYPO3\Flow\Tests\Functional\Aop\Fixtures\TargetClass04)")
	 * @Flow\Transient
	 * @var string
	 */
	protected $introducedTransientProperty;

}
