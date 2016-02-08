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

/**
 * Contract for firewall
 *
 */
interface FirewallInterface
{
    /**
     * Analyzes a request against the configured firewall rules and blocks
     * any illegal request.
     *
     * @param \TYPO3\Flow\Mvc\ActionRequest $request The request to be analyzed
     * @return void
     */
    public function blockIllegalRequests(\TYPO3\Flow\Mvc\ActionRequest $request);
}
