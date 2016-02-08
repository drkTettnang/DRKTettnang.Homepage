<?php
namespace TYPO3\Flow\Session;

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
 * Interface for a session manager
 *
 * In order to stay compatible with future features and create more portable apps,
 * make sure to inject this interface instead of the concrete SessionManager
 * implementation.
 *
 * @api
 */
interface SessionManagerInterface
{
    /**
     * Returns the currently active session which stores session data for the
     * current HTTP request on this local system.
     *
     * @return \TYPO3\Flow\Session\SessionInterface
     * @api
     */
    public function getCurrentSession();

    /**
     * Returns the specified session. If no session with the given identifier exists,
     * NULL is returned.
     *
     * @param string $sessionIdentifier The session identifier
     * @return \TYPO3\Flow\Session\SessionInterface
     * @api
     */
    public function getSession($sessionIdentifier);

    /**
     * Returns all active sessions, even remote ones.
     *
     * @return array<\TYPO3\Flow\Session\Session>
     * @api
     */
    public function getActiveSessions();

    /**
     * Returns all sessions which are tagged by the specified tag.
     *
     * @param string $tag A valid Cache Frontend tag
     * @return array A collection of Session objects or an empty array if tag did not match
     * @api
     */
    public function getSessionsByTag($tag);

    /**
     * Destroys all sessions which are tagged with the specified tag.
     *
     * @param string $tag A valid Cache Frontend tag
     * @param string $reason A reason to mention in log output for why the sessions have been destroyed. For example: "The corresponding account was deleted"
     * @return integer Number of sessions which have been destroyed
     * @api
     */
    public function destroySessionsByTag($tag, $reason = '');
}
