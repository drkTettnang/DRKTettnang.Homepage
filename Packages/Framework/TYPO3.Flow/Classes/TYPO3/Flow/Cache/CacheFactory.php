<?php
namespace TYPO3\Flow\Cache;

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
use TYPO3\Flow\Object\ObjectManagerInterface;

/**
 * This cache factory takes care of instantiating a cache frontend and injecting
 * a certain cache backend. After creation of the new cache, the cache object
 * is registered at the cache manager.
 *
 * @Flow\Scope("singleton")
 * @api
 */
class CacheFactory
{
    /**
     * The current Flow context ("Production", "Development" etc.)
     *
     * @var \TYPO3\Flow\Core\ApplicationContext
     */
    protected $context;

    /**
     * A reference to the cache manager
     *
     * @var \TYPO3\Flow\Cache\CacheManager
     */
    protected $cacheManager;

    /**
     * @var \TYPO3\Flow\Utility\Environment
     */
    protected $environment;

    /**
     * Constructs this cache factory
     *
     * @param \TYPO3\Flow\Core\ApplicationContext $context The current Flow context
     * @param \TYPO3\Flow\Cache\CacheManager $cacheManager
     * @param \TYPO3\Flow\Utility\Environment $environment
     */
    public function __construct(\TYPO3\Flow\Core\ApplicationContext $context, \TYPO3\Flow\Cache\CacheManager $cacheManager, \TYPO3\Flow\Utility\Environment $environment)
    {
        $this->context = $context;
        $this->cacheManager = $cacheManager;
        $this->cacheManager->injectCacheFactory($this);
        $this->environment = $environment;
    }

    /**
     * Factory method which creates the specified cache along with the specified kind of backend.
     * After creating the cache, it will be registered at the cache manager.
     *
     * @param string $cacheIdentifier The name / identifier of the cache to create
     * @param string $cacheObjectName Object name of the cache frontend
     * @param string $backendObjectName Object name of the cache backend
     * @param array $backendOptions (optional) Array of backend options
     * @param boolean $persistent If the new cache should be marked as "persistent"
     * @return Frontend\FrontendInterface The created cache frontend
     * @throws Exception\InvalidBackendException
     * @throws Exception\InvalidCacheException
     * @api
     */
    public function create($cacheIdentifier, $cacheObjectName, $backendObjectName, array $backendOptions = array(), $persistent = false)
    {
        $backend = new $backendObjectName($this->context, $backendOptions);
        if (!$backend instanceof Backend\BackendInterface) {
            throw new Exception\InvalidBackendException('"' . $backendObjectName . '" is not a valid cache backend object.', 1216304301);
        }
        $backend->injectEnvironment($this->environment);
        if (is_callable(array($backend, 'injectCacheManager'))) {
            $backend->injectCacheManager($this->cacheManager);
        }
        if (is_callable(array($backend, 'initializeObject'))) {
            $backend->initializeObject(ObjectManagerInterface::INITIALIZATIONCAUSE_CREATED);
        }

        $cache = new $cacheObjectName($cacheIdentifier, $backend);
        if (!$cache instanceof Frontend\FrontendInterface) {
            throw new Exception\InvalidCacheException('"' . $cacheObjectName . '" is not a valid cache frontend object.', 1216304300);
        }

        $this->cacheManager->registerCache($cache, $persistent);

        if (is_callable(array($cache, 'initializeObject'))) {
            $cache->initializeObject(ObjectManagerInterface::INITIALIZATIONCAUSE_CREATED);
        }
        return $cache;
    }
}
