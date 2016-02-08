<?php
namespace TYPO3\Flow\SignalSlot;

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
 * Aspect which connects signal methods with the Signal Dispatcher
 *
 * @Flow\Scope("singleton")
 * @Flow\Aspect
 */
class SignalAspect
{
    /**
     * @Flow\Inject
     * @var \TYPO3\Flow\SignalSlot\Dispatcher
     */
    protected $dispatcher;

    /**
     * Passes the signal over to the Dispatcher
     *
     * @Flow\AfterReturning("methodAnnotatedWith(TYPO3\Flow\Annotations\Signal)")
     * @param \TYPO3\Flow\Aop\JoinPointInterface $joinPoint The current join point
     * @return void
     */
    public function forwardSignalToDispatcher(\TYPO3\Flow\Aop\JoinPointInterface $joinPoint)
    {
        $signalName = lcfirst(str_replace('emit', '', $joinPoint->getMethodName()));
        $this->dispatcher->dispatch($joinPoint->getClassName(), $signalName, $joinPoint->getMethodArguments());
    }
}
