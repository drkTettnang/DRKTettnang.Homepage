<?php
namespace TYPO3\Flow\Persistence\Doctrine\Logging;

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
 * A SQL logger that logs to a Flow logger.
 *
 */
class SqlLogger implements \Doctrine\DBAL\Logging\SQLLogger
{
    /**
     * @var \TYPO3\Flow\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Logs a SQL statement to the system logger (DEBUG priority).
     *
     * @param string $sql The SQL to be executed
     * @param array $params The SQL parameters
     * @param array $types The SQL parameter types.
     * @return void
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        // this is a safeguard for when no logger might be available...
        if ($this->logger !== null) {
            $this->logger->log($sql, LOG_DEBUG, array('params' => $params, 'types' => $types));
        }
    }

    /**
     * @return void
     */
    public function stopQuery()
    {
    }
}
