<?php
namespace TYPO3\Flow\Mvc\Exception;

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
 * An "no matching route" exception that is thrown if the router could not
 * find a route that matches/resolves the given uri pattern/route values
 *
 * @api
 */
class NoMatchingRouteException extends \TYPO3\Flow\Mvc\Exception
{
    /**
     * @var integer
     */
    protected $statusCode = 404;
}
