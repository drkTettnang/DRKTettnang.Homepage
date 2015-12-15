<?php
namespace TYPO3\Flow\Tests\Unit\Cache\Backend;

/*                                                                        *
 * This script belongs to the TYPO3 Flow framework.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Core\ApplicationContext;

/**
 * Testcase for the cache to memcached backend
 *
 * @requires extension memcache
 */
class MemcachedBackendTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @var \TYPO3\Flow\Utility\Environment
	 */
	protected $mockEnvironment;

	/**
	 * Sets up this testcase
	 *
	 * @return void
	 */
	public function setUp() {
		try {
			if (!@fsockopen('localhost', 11211)) {
				$this->markTestSkipped('memcached not reachable');
			}
		} catch (\Exception $e) {
			$this->markTestSkipped('memcached not reachable');
		}

		$this->mockEnvironment = $this->getMock('TYPO3\Flow\Utility\Environment', array(), array(), '', FALSE);
	}

	/**
	 * @test
	 * @expectedException \TYPO3\Flow\Cache\Exception
	 */
	public function setThrowsExceptionIfNoFrontEndHasBeenSet() {
		$backendOptions = array('servers' => array('localhost:11211'));
		$backend = new \TYPO3\Flow\Cache\Backend\MemcachedBackend(new ApplicationContext('Testing'), $backendOptions);
		$backend->injectEnvironment($this->mockEnvironment);
		$backend->initializeObject();
		$data = 'Some data';
		$identifier = 'MyIdentifier' . md5(uniqid(mt_rand(), TRUE));
		$backend->set($identifier, $data);
	}

	/**
	 * @test
	 * @expectedException \TYPO3\Flow\Cache\Exception
	 */
	public function initializeObjectThrowsExceptionIfNoMemcacheServerIsConfigured() {
		$backend = new \TYPO3\Flow\Cache\Backend\MemcachedBackend(new ApplicationContext('Testing'));
		$backend->initializeObject();
	}

	/**
	 * @test
	 * @expectedException \TYPO3\Flow\Cache\Exception
	 */
	public function setThrowsExceptionIfConfiguredServersAreUnreachable() {
		$backend = $this->setUpBackend(array('servers' => array('localhost:11212')));
		$data = 'Somedata';
		$identifier = 'MyIdentifier' . md5(uniqid(mt_rand(), TRUE));
		$backend->set($identifier, $data);
	}

	/**
	 * @test
	 */
	public function itIsPossibleToSetAndCheckExistenceInCache() {
		$backend = $this->setUpBackend();
		$data = 'Some data';
		$identifier = 'MyIdentifier' . md5(uniqid(mt_rand(), TRUE));
		$backend->set($identifier, $data);
		$inCache = $backend->has($identifier);
		$this->assertTrue($inCache, 'Memcache failed to set and check entry');
	}

	/**
	 * @test
	 */
	public function itIsPossibleToSetAndGetEntry() {
		$backend = $this->setUpBackend();
		$data = 'Some data';
		$identifier = 'MyIdentifier' . md5(uniqid(mt_rand(), TRUE));
		$backend->set($identifier, $data);
		$fetchedData = $backend->get($identifier);
		$this->assertEquals($data, $fetchedData, 'Memcache failed to set and retrieve data');
	}

	/**
	 * @test
	 */
	public function itIsPossibleToRemoveEntryFromCache() {
		$backend = $this->setUpBackend();
		$data = 'Some data';
		$identifier = 'MyIdentifier' . md5(uniqid(mt_rand(), TRUE));
		$backend->set($identifier, $data);
		$backend->remove($identifier);
		$inCache = $backend->has($identifier);
		$this->assertFalse($inCache, 'Failed to set and remove data from Memcache');
	}

	/**
	 * @test
	 */
	public function itIsPossibleToOverwriteAnEntryInTheCache() {
		$backend = $this->setUpBackend();
		$data = 'Some data';
		$identifier = 'MyIdentifier' . md5(uniqid(mt_rand(), TRUE));
		$backend->set($identifier, $data);
		$otherData = 'some other data';
		$backend->set($identifier, $otherData);
		$fetchedData = $backend->get($identifier);
		$this->assertEquals($otherData, $fetchedData, 'Memcache failed to overwrite and retrieve data');
	}

	/**
	 * @test
	 */
	public function findIdentifiersByTagFindsCacheEntriesWithSpecifiedTag() {
		$backend = $this->setUpBackend();

		$data = 'Some data';
		$identifier = 'MyIdentifier' . md5(uniqid(mt_rand(), TRUE));
		$backend->set($identifier, $data, array('UnitTestTag%tag1', 'UnitTestTag%tag2'));

		$retrieved = $backend->findIdentifiersByTag('UnitTestTag%tag1');
		$this->assertEquals($identifier, $retrieved[0], 'Could not retrieve expected entry by tag.');

		$retrieved = $backend->findIdentifiersByTag('UnitTestTag%tag2');
		$this->assertEquals($identifier, $retrieved[0], 'Could not retrieve expected entry by tag.');
	}

	/**
	 * @test
	 */
	public function setRemovesTagsFromPreviousSet() {
		$backend = $this->setUpBackend();

		$data = 'Some data';
		$identifier = 'MyIdentifier' . md5(uniqid(mt_rand(), TRUE));
		$backend->set($identifier, $data, array('UnitTestTag%tag1', 'UnitTestTag%tagX'));
		$backend->set($identifier, $data, array('UnitTestTag%tag3'));

		$retrieved = $backend->findIdentifiersByTag('UnitTestTag%tagX');
		$this->assertEquals(array(), $retrieved, 'Found entry which should no longer exist.');
	}

	/**
	 * @test
	 */
	public function hasReturnsFalseIfTheEntryDoesntExist() {
		$backend = $this->setUpBackend();
		$identifier = 'NonExistingIdentifier' . md5(uniqid(mt_rand(), TRUE));
		$inCache = $backend->has($identifier);
		$this->assertFalse($inCache,'"has" did not return false when checking on non existing identifier');
	}

	/**
	 * @test
	 */
	public function removeReturnsFalseIfTheEntryDoesntExist() {
		$backend = $this->setUpBackend();
		$identifier = 'NonExistingIdentifier' . md5(uniqid(mt_rand(), TRUE));
		$inCache = $backend->remove($identifier);
		$this->assertFalse($inCache,'"remove" did not return false when checking on non existing identifier');
	}

	/**
	 * @test
	 */
	public function flushByTagRemovesCacheEntriesWithSpecifiedTag() {
		$backend = $this->setUpBackend();

		$data = 'some data' . microtime();
		$backend->set('BackendMemcacheTest1', $data, array('UnitTestTag%test', 'UnitTestTag%boring'));
		$backend->set('BackendMemcacheTest2', $data, array('UnitTestTag%test', 'UnitTestTag%special'));
		$backend->set('BackendMemcacheTest3', $data, array('UnitTestTag%test'));

		$backend->flushByTag('UnitTestTag%special');

		$this->assertTrue($backend->has('BackendMemcacheTest1'), 'BackendMemcacheTest1');
		$this->assertFalse($backend->has('BackendMemcacheTest2'), 'BackendMemcacheTest2');
		$this->assertTrue($backend->has('BackendMemcacheTest3'), 'BackendMemcacheTest3');
	}

	/**
	 * @test
	 */
	public function flushRemovesAllCacheEntries() {
		$backend = $this->setUpBackend();

		$data = 'some data' . microtime();
		$backend->set('BackendMemcacheTest1', $data);
		$backend->set('BackendMemcacheTest2', $data);
		$backend->set('BackendMemcacheTest3', $data);

		$backend->flush();

		$this->assertFalse($backend->has('BackendMemcacheTest1'), 'BackendMemcacheTest1');
		$this->assertFalse($backend->has('BackendMemcacheTest2'), 'BackendMemcacheTest2');
		$this->assertFalse($backend->has('BackendMemcacheTest3'), 'BackendMemcacheTest3');
	}

	/**
	 * @test
	 */
	public function flushRemovesOnlyOwnEntries() {
		$backendOptions = array('servers' => array('localhost:11211'));

		$thisCache = $this->getMock('TYPO3\Flow\Cache\Frontend\AbstractFrontend', array(), array(), '', FALSE);
		$thisCache->expects($this->any())->method('getIdentifier')->will($this->returnValue('thisCache'));
		$thisBackend = new \TYPO3\Flow\Cache\Backend\MemcachedBackend(new ApplicationContext('Testing'), $backendOptions);
		$thisBackend->injectEnvironment($this->mockEnvironment);
		$thisBackend->setCache($thisCache);
		$thisBackend->initializeObject();

		$thatCache = $this->getMock('TYPO3\Flow\Cache\Frontend\AbstractFrontend', array(), array(), '', FALSE);
		$thatCache->expects($this->any())->method('getIdentifier')->will($this->returnValue('thatCache'));
		$thatBackend = new \TYPO3\Flow\Cache\Backend\MemcachedBackend(new ApplicationContext('Testing'), $backendOptions);
		$thatBackend->injectEnvironment($this->mockEnvironment);
		$thatBackend->setCache($thatCache);
		$thatBackend->initializeObject();

		$thisBackend->set('thisEntry', 'Hello');
		$thatBackend->set('thatEntry', 'World!');
		$thatBackend->flush();

		$this->assertEquals('Hello', $thisBackend->get('thisEntry'));
		$this->assertFalse($thatBackend->has('thatEntry'));
	}

	/**
	 * Check if we can store ~5 MB of data, this gives some headroom for the
	 * reflection data.
	 *
	 * @test
	 */
	public function largeDataIsStored() {
		$backend = $this->setUpBackend();

		$data = str_repeat('abcde', 1024 * 1024);
		$backend->set('tooLargeData', $data);

		$this->assertTrue($backend->has('tooLargeData'));
		$this->assertEquals($backend->get('tooLargeData'), $data);
	}

	/**
	 * Sets up the memcached backend used for testing
	 *
	 * @param array $backendOptions Options for the memcache backend
	 * @return \TYPO3\Flow\Cache\Backend\MemcachedBackend
	 */
	protected function setUpBackend(array $backendOptions = array()) {
		$cache = $this->getMock('TYPO3\Flow\Cache\Frontend\FrontendInterface', array(), array(), '', FALSE);
		if ($backendOptions == array()) {
			$backendOptions = array('servers' => array('localhost:11211'));
		}
		$backend = new \TYPO3\Flow\Cache\Backend\MemcachedBackend(new ApplicationContext('Testing'), $backendOptions);
		$backend->injectEnvironment($this->mockEnvironment);
		$backend->setCache($cache);
		$backend->initializeObject();
		return $backend;
	}

}
