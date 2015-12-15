<?php
namespace TYPO3\Flow\Security\Authorization;

/*                                                                        *
 * This script belongs to the TYPO3 Flow framework.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */


/**
 * A RequestFilter is configured to match specific \TYPO3\Flow\Mvc\RequestInterfaces and call
 * a \TYPO3\Flow\Security\Authorization\InterceptorInterface if needed.
 *
 */
class RequestFilter {

	/**
	 * @var \TYPO3\Flow\Security\RequestPatternInterface
	 */
	protected $pattern = NULL;

	/**
	 * @var \TYPO3\Flow\Security\Authorization\InterceptorInterface
	 */
	protected $securityInterceptor = NULL;

	/**
	 * Constructor.
	 *
	 * @param \TYPO3\Flow\Security\RequestPatternInterface $pattern The pattern this filter matches
	 * @param \TYPO3\Flow\Security\Authorization\InterceptorInterface $securityInterceptor The interceptor called on pattern match
	 */
	public function __construct(\TYPO3\Flow\Security\RequestPatternInterface $pattern, \TYPO3\Flow\Security\Authorization\InterceptorInterface $securityInterceptor) {
		$this->pattern = $pattern;
		$this->securityInterceptor = $securityInterceptor;
	}

	/**
	 * Returns the set request pattern
	 *
	 * @return \TYPO3\Flow\Security\RequestPatternInterface The set request pattern
	 */
	public function getRequestPattern() {
		return $this->pattern;
	}

	/**
	 * Returns the set security interceptor
	 *
	 * @return \TYPO3\Flow\Security\Authorization\InterceptorInterface The set security interceptor
	 */
	public function getSecurityInterceptor() {
		return $this->securityInterceptor;
	}

	/**
	 * Tries to match the given request against this filter and calls the set security interceptor on success.
	 *
	 * @param \TYPO3\Flow\Mvc\RequestInterface $request The request to be matched
	 * @return boolean Returns TRUE if the filter matched, FALSE otherwise
	 */
	public function filterRequest(\TYPO3\Flow\Mvc\RequestInterface $request) {
		if ($this->pattern->matchRequest($request)) {
			$this->securityInterceptor->invoke();
			return TRUE;
		}
		return FALSE;
	}
}
