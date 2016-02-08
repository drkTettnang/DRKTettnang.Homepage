<?php
namespace TYPO3\Flow\Security;

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
 * A factory for conveniently creating new accounts
 *
 * @Flow\Scope("singleton")
 */
class AccountFactory
{
    /**
     * @var \TYPO3\Flow\Security\Cryptography\HashService
     * @Flow\Inject
     */
    protected $hashService;

    /**
     * @var \TYPO3\Flow\Security\Policy\PolicyService
     * @Flow\Inject
     */
    protected $policyService;

    /**
     * Creates a new account and sets the given password and roles
     *
     * @param string $identifier Identifier of the account, must be unique
     * @param string $password The clear text password
     * @param array $roleIdentifiers Optionally an array of role identifiers to assign to the new account
     * @param string $authenticationProviderName Optional name of the authentication provider the account is affiliated with
     * @param string $passwordHashingStrategy Optional password hashing strategy to use for the password
     * @return \TYPO3\Flow\Security\Account A new account, not yet added to the account repository
     */
    public function createAccountWithPassword($identifier, $password, $roleIdentifiers = array(), $authenticationProviderName = 'DefaultProvider', $passwordHashingStrategy = 'default')
    {
        $account = new \TYPO3\Flow\Security\Account();
        $account->setAccountIdentifier($identifier);
        $account->setCredentialsSource($this->hashService->hashPassword($password, $passwordHashingStrategy));
        $account->setAuthenticationProviderName($authenticationProviderName);

        $roles = array();
        foreach ($roleIdentifiers as $roleIdentifier) {
            $roles[] = $this->policyService->getRole($roleIdentifier);
        }
        $account->setRoles($roles);

        return $account;
    }
}
