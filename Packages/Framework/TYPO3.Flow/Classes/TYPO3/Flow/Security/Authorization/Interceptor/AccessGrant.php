<?php
namespace TYPO3\Flow\Security\Authorization\Interceptor;

/*
 * This file is part of the TYPO3.Flow package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use TYPO3\Flow\Annotations as Flow;

/**
 * This security interceptor always grants access.
 *
 * @Flow\Scope("singleton")
 */
class AccessGrant implements \TYPO3\Flow\Security\Authorization\InterceptorInterface
{
    /**
     * Invokes nothing, always returns TRUE.
     *
     * @return boolean Always returns TRUE
     */
    public function invoke()
    {
        return true;
    }
}
