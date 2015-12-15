<?php
namespace TYPO3\Neos\TypoScript;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Neos".            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Log\SystemLoggerInterface;
use TYPO3\Neos\Service\LinkingService;
use TYPO3\TypoScript\TypoScriptObjects\AbstractTypoScriptObject;
use TYPO3\Neos\Exception as NeosException;

/**
 * Create a link to a node
 */
class NodeUriImplementation extends AbstractTypoScriptObject {

	/**
	 * @Flow\Inject
	 * @var SystemLoggerInterface
	 */
	protected $systemLogger;

	/**
	 * @Flow\Inject
	 * @var LinkingService
	 */
	protected $linkingService;

	/**
	 * A node object or a string node path or NULL to resolve the current document node
	 *
	 * @return mixed
	 */
	public function getNode() {
		return $this->tsValue('node');
	}

	/**
	 * Additional arguments to be passed to the UriBuilder (for example pagination parameters)
	 *
	 * @return array
	 */
	public function getArguments() {
		return $this->tsValue('arguments');
	}

	/**
	 * The requested format, for example "html"
	 *
	 * @return string
	 */
	public function getFormat() {
		return $this->tsValue('format');
	}

	/**
	 * The anchor to be appended to the URL
	 *
	 * @return string
	 */
	public function getSection() {
		return (string)$this->tsValue('section');
	}

	/**
	 * Additional query parameters that won't be prefixed like $arguments (overrule $arguments)
	 *
	 * @return array
	 */
	public function getAdditionalParams() {
		return $this->tsValue('additionalParams');
	}

	/**
	 * Arguments to be removed from the URI. Only active if addQueryString = TRUE
	 *
	 * @return array
	 */
	public function getArgumentsToBeExcludedFromQueryString() {
		return $this->tsValue('argumentsToBeExcludedFromQueryString');
	}

	/**
	 * If TRUE, the current query parameters will be kept in the URI
	 *
	 * @return boolean
	 */
	public function getAddQueryString() {
		return (boolean)$this->tsValue('addQueryString');
	}

	/**
	 * If TRUE, an absolute URI is rendered
	 *
	 * @return boolean
	 */
	public function isAbsolute() {
		return (boolean)$this->tsValue('absolute');
	}

	/**
	 * The name of the base node inside the TypoScript context to use for resolving relative paths.
	 *
	 * @return string
	 */
	public function getBaseNodeName() {
		return $this->tsValue('baseNodeName');
	}

	/**
	 * Render the Uri.
	 *
	 * @return string The rendered URI or NULL if no URI could be resolved for the given node
	 * @throws NeosException
	 */
	public function evaluate() {
		$baseNode = NULL;
		$baseNodeName = $this->getBaseNodeName() ?: 'documentNode';
		$currentContext = $this->tsRuntime->getCurrentContext();
		if (isset($currentContext[$baseNodeName])) {
			$baseNode = $currentContext[$baseNodeName];
		} else {
			throw new NeosException(sprintf('Could not find a node instance in TypoScript context with name "%s" and no node instance was given to the node argument. Set a node instance in the TypoScript context or pass a node object to resolve the URI.', $baseNodeName), 1373100400);
		}

		try {
			return $this->linkingService->createNodeUri(
				$this->tsRuntime->getControllerContext(),
				$this->getNode(),
				$baseNode,
				$this->getFormat(),
				$this->isAbsolute(),
				$this->getAdditionalParams(),
				$this->getSection(),
				$this->getAddQueryString(),
				$this->getArgumentsToBeExcludedFromQueryString()
			);
		} catch (NeosException $exception) {
			$this->systemLogger->logException($exception);
			return '';
		}
	}

}