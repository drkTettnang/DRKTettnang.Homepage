<?php
namespace TYPO3\Flow\Security\Authorization;

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
 * The security interceptor resolver. It resolves the class name of a security interceptor based on names.
 *
 * @Flow\Scope("singleton")
 */
class InterceptorResolver
{
    /**
     * @var \TYPO3\Flow\Object\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Constructor.
     *
     * @param \TYPO3\Flow\Object\ObjectManagerInterface $objectManager The object manager
     */
    public function __construct(\TYPO3\Flow\Object\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Resolves the class name of a security interceptor. If a valid interceptor class name is given, it is just returned.
     *
     * @param string $name The (short) name of the interceptor
     * @return string The class name of the security interceptor, NULL if no class was found.
     * @throws \TYPO3\Flow\Security\Exception\NoInterceptorFoundException
     */
    public function resolveInterceptorClass($name)
    {
        $resolvedObjectName = $this->objectManager->getCaseSensitiveObjectName($name);
        if ($resolvedObjectName !== false) {
            return $resolvedObjectName;
        }

        $resolvedObjectName = $this->objectManager->getCaseSensitiveObjectName('TYPO3\Flow\Security\Authorization\Interceptor\\' . $name);
        if ($resolvedObjectName !== false) {
            return $resolvedObjectName;
        }

        throw new \TYPO3\Flow\Security\Exception\NoInterceptorFoundException('A security interceptor with the name: "' . $name . '" could not be resolved.', 1217154134);
    }
}
