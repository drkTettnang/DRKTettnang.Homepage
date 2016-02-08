<?php
namespace TYPO3\Flow\Mvc\Controller;

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
 * A generic Controller exception
 *
 * @api
 */
class Exception extends \TYPO3\Flow\Mvc\Exception
{
    /**
     * @var \TYPO3\Flow\Mvc\RequestInterface
     */
    protected $request;

    /**
     * Overwrites parent constructor to be able to inject current request object.
     *
     * @param string $message
     * @param integer $code
     * @param \Exception $previousException
     * @param \TYPO3\Flow\Mvc\RequestInterface $request
     * @see \Exception
     */
    public function __construct($message = '', $code = 0, \Exception $previousException = null, \TYPO3\Flow\Mvc\RequestInterface $request)
    {
        $this->request = $request;
        parent::__construct($message, $code, $previousException);
    }

    /**
     * Returns the request object that exception belongs to.
     *
     * @return \TYPO3\Flow\Mvc\RequestInterface
     */
    protected function getRequest()
    {
        return $this->request;
    }
}
