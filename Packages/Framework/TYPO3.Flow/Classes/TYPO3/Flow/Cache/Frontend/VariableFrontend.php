<?php
namespace TYPO3\Flow\Cache\Frontend;

/*                                                                        *
 * This script belongs to the TYPO3 Flow framework.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Cache\Backend\IterableBackendInterface;
use TYPO3\Flow\Cache\Exception\NotSupportedByBackendException;

/**
 * A cache frontend for any kinds of PHP variables
 *
 * @api
 */
class VariableFrontend extends AbstractFrontend {

	/**
	 * If the extension "igbinary" is installed, use it for increased performance.
	 * Caching the result of extension_loaded() here is faster than calling extension_loaded() multiple times.
	 *
	 * @var boolean
	 */
	protected $useIgBinary = FALSE;

	/**
	 * Initializes this cache frontend
	 *
	 * @return void
	 */
	public function initializeObject() {
		$this->useIgBinary = extension_loaded('igbinary');
		parent::initializeObject();
	}

	/**
	 * Saves the value of a PHP variable in the cache. Note that the variable
	 * will be serialized if necessary.
	 *
	 * @param string $entryIdentifier An identifier used for this cache entry
	 * @param mixed $variable The variable to cache
	 * @param array $tags Tags to associate with this cache entry
	 * @param integer $lifetime Lifetime of this cache entry in seconds. If NULL is specified, the default lifetime is used. "0" means unlimited lifetime.
	 * @return void
	 * @throws \InvalidArgumentException
	 * @api
	 */
	public function set($entryIdentifier, $variable, array $tags = array(), $lifetime = NULL) {
		if (!$this->isValidEntryIdentifier($entryIdentifier)) {
			throw new \InvalidArgumentException('"' . $entryIdentifier . '" is not a valid cache entry identifier.', 1233058264);
		}
		foreach ($tags as $tag) {
			if (!$this->isValidTag($tag)) {
				throw new \InvalidArgumentException('"' . $tag . '" is not a valid tag for a cache entry.', 1233058269);
			}
		}
		if ($this->useIgBinary === TRUE) {
			$this->backend->set($entryIdentifier, igbinary_serialize($variable), $tags, $lifetime);
		} else {
			$this->backend->set($entryIdentifier, serialize($variable), $tags, $lifetime);
		}
	}

	/**
	 * Finds and returns a variable value from the cache.
	 *
	 * @param string $entryIdentifier Identifier of the cache entry to fetch
	 * @return mixed The value
	 * @throws \InvalidArgumentException
	 * @api
	 */
	public function get($entryIdentifier) {
		if (!$this->isValidEntryIdentifier($entryIdentifier)) {
			throw new \InvalidArgumentException('"' . $entryIdentifier . '" is not a valid cache entry identifier.', 1233058294);
		}

		$rawResult = $this->backend->get($entryIdentifier);
		if ($rawResult === FALSE) {
			return FALSE;
		} else {
			return ($this->useIgBinary === TRUE) ? igbinary_unserialize($rawResult) : unserialize($rawResult);
		}
	}

	/**
	 * Finds and returns all cache entries which are tagged by the specified tag.
	 *
	 * @param string $tag The tag to search for
	 * @return array An array with the identifier (key) and content (value) of all matching entries. An empty array if no entries matched
	 * @throws \InvalidArgumentException
	 * @api
	 */
	public function getByTag($tag) {
		if (!$this->isValidTag($tag)) {
			throw new \InvalidArgumentException('"' . $tag . '" is not a valid tag for a cache entry.', 1233058312);
		}

		$entries = array();
		$identifiers = $this->backend->findIdentifiersByTag($tag);
		foreach ($identifiers as $identifier) {
			$rawResult = $this->backend->get($identifier);
			if ($rawResult !== FALSE) {
				$entries[$identifier] = ($this->useIgBinary === TRUE) ? igbinary_unserialize($rawResult) : unserialize($rawResult);
			}
		}
		return $entries;
	}

	/**
	 * Returns an iterator over the entries of this cache
	 *
	 * @param integer $chunkSize Determines the number of entries fetched by the backend at once (not supported yet, for future use)
	 * @return \TYPO3\Flow\Cache\Frontend\CacheEntryIterator
	 * @throws NotSupportedByBackendException
	 */
	public function getIterator($chunkSize = NULL) {
		if (!$this->backend instanceof IterableBackendInterface) {
			throw new NotSupportedByBackendException('The cache backend (%s) configured for cach "%s" does cannot be used as an iterator. Please choose a different cache backend or adjust the code using this cache.', 1371463860);
		}
		return new CacheEntryIterator($this, $this->backend, $chunkSize);
	}

}
