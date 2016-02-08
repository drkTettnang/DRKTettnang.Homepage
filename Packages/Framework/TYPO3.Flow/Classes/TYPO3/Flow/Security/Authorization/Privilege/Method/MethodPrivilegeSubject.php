<?php
namespace TYPO3\Flow\Security\Authorization\Privilege\Method;

/*
 * This file is part of the TYPO3.Flow package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use TYPO3\Flow\Aop\JoinPointInterface;
use TYPO3\Flow\Security\Authorization\Privilege\PrivilegeSubjectInterface;

/**
 * A method privilege subject
 */
class MethodPrivilegeSubject implements PrivilegeSubjectInterface
{
    /**
     * @var JoinPointInterface
     */
    protected $joinPoint;

    /**
     * @param JoinPointInterface $joinPoint
     * @return void
     */
    public function __construct(JoinPointInterface $joinPoint)
    {
        $this->joinPoint = $joinPoint;
    }

    /**
     * @return JoinPointInterface
     */
    public function getJoinPoint()
    {
        return $this->joinPoint;
    }
}
