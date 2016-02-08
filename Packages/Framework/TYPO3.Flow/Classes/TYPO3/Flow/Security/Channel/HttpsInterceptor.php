<?php
namespace TYPO3\Flow\Security\Channel;

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
 * This security interceptor switches the current channel between HTTP and HTTPS protocol.
 *
 * @Flow\Scope("singleton")
 */
class HttpsInterceptor implements \TYPO3\Flow\Security\Authorization\InterceptorInterface
{
    /**
     * @var boolean
     * @todo this has to be set by configuration
     */
    protected $useSSL = false;

    /**
     * Constructor.
     *
     * @param \TYPO3\Flow\Security\Context $securityContext The current security context
     * @param \TYPO3\Flow\Security\Authentication\AuthenticationManagerInterface $authenticationManager The authentication Manager
     * @param \TYPO3\Flow\Log\SystemLoggerInterface $logger A logger to log security relevant actions
     */
    public function __construct(
        \TYPO3\Flow\Security\Context $securityContext,
        \TYPO3\Flow\Security\Authentication\AuthenticationManagerInterface $authenticationManager,
        \TYPO3\Flow\Log\SystemLoggerInterface $logger
    ) {
    }

    /**
     * Redirects the current request to HTTP or HTTPS depending on $this->useSSL;
     *
     * @return boolean TRUE if the security checks was passed
     */
    public function invoke()
    {
    }
}
