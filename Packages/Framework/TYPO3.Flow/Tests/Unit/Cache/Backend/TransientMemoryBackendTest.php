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
 * Testcase for the Transient Memory Backend
 *
 */
class TransientMemoryBackendTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @expectedException \TYPO3\Flow\Cache\Exception
	 * @test
	 */
	public function setThrowsExceptionIfNoFrontEndHasBeenSet() {
		$backend = new \TYPO3\Flow\Cache\Backend\TransientMemoryBackend(new ApplicationContext('Testing'));

		$data = 'Some data';
		$identifier = 'MyIdentifier';
		$backend->set($identifier, $data);
	}

	/**
	 * @test
	 */
	public function itIsPossibleToSetAndCheckExistenceInCache() {
		$cache = $this->getMock('TYPO3\Flow\Cache\Frontend\FrontendInterface', array(), array(), '', FALSE);
		$backend = new \TYPO3\Flow\Cache\Backend\TransientMemoryBackend(new ApplicationContext('Testing'));
		$backend->setCache($cache);

		$data = 'Some data';
		$identifier = 'MyIdentifier';
		$backend->set($identifier, $data);
		$inCache = $backend->has($identifier);
		$this->assertTrue($inCache);
	}

	/**
	 * @test
	 */
	public function itIsPossibleToSetAndGetEntry() {
		$cache = $this->getMock('TYPO3\Flow\Cache\Frontend\FrontendInterface', array(), array(), '', FALSE);
		$backend = new \TYPO3\Flow\Cache\Backend\TransientMemoryBackend(new ApplicationContext('Testing'));
		$backend->setCache($cache);

		$data = 'Some data';
		$identifier = 'MyIdentifier';
		$backend->set($identifier, $data);
		$fetchedData = $backend->get($identifier);
		$this->assertEquals($data, $fetchedData);
	}

	/**
	 * @test
	 */
	public function itIsPossibleToRemoveEntryFromCache() {
		$cache = $this->getMock('TYPO3\Flow\Cache\Frontend\FrontendInterface', array(), array(), '', FALSE);
		$backend = new \TYPO3\Flow\Cache\Backend\TransientMemoryBackend(new ApplicationContext('Testing'));
		$backend->setCache($cache);

		$data = 'Some data';
		$identifier = 'MyIdentifier';
		$backend->set($identifier, $data);
		$backend->remove($identifier);
		$inCache = $backend->has($identifier);
		$this->assertFalse($inCache);
	}

	/**
	 * @test
	 */
	public function itIsPossibleToOverwriteAnEntryInTheCache() {
		$cache = $this->getMock('TYPO3\Flow\Cache\Frontend\FrontendInterface', array(), array(), '', FALSE);
		$backend = new \TYPO3\Flow\Cache\Backend\TransientMemoryBackend(new ApplicationContext('Testing'));
		$backend->setCache($cache);

		$data = 'Some data';
		$identifier = 'MyIdentifier';
		$backend->set($identifier, $data);
		$otherData = 'some other data';
		$backend->set($identifier, $otherData);
		$fetchedData = $backend->get($identifier);
		$this->assertEquals($otherData, $fetchedData);
	}

	/**
	 * @test
	 */
	public function findIdentifiersByTagFindsCacheEntriesWithSpecifiedTag() {
		$cache = $this->getMock('TYPO3\Flow\Cache\Frontend\FrontendInterface', array(), array(), '', FALSE);
		$backend = new \TYPO3\Flow\Cache\Backend\TransientMemoryBackend(new ApplicationContext('Testing'));
		$backend->setCache($cache);

		$data = 'Some data';
		$entryIdentifier = 'MyIdentifier';
		$backend->set($entryIdentifier, $data, array('UnitTestTag%tag1', 'UnitTestTag%tag2'));

		$retrieved = $backend->findIdentifiersByTag('UnitTestTag%tag1');
		$this->assertEquals($entryIdentifier, $retrieved[0]);

		$retrieved = $backend->findIdentifiersByTag('UnitTestTag%tag2');
		$this->assertEquals($entryIdentifier, $retrieved[0]);
	}

	/**
	 * @test
	 */
	public function hasReturnsFalseIfTheEntryDoesntExist() {
		$cache = $this->getMock('TYPO3\Flow\Cache\Frontend\FrontendInterface', array(), array(), '', FALSE);
		$backend = new \TYPO3\Flow\Cache\Backend\TransientMemoryBackend(new ApplicationContext('Testing'));
		$backend->setCache($cache);

		$identifier = 'NonExistingIdentifier';
		$inCache = $backend->has($identifier);
		$this->assertFalse($inCache);
	}

	/**
	 * @test
	 */
	public function removeReturnsFalseIfTheEntryDoesntExist() {
		$cache = $this->getMock('TYPO3\Flow\Cache\Frontend\FrontendInterface', array(), array(), '', FALSE);
		$backend = new \TYPO3\Flow\Cache\Backend\TransientMemoryBackend(new ApplicationContext('Testing'));
		$backend->setCache($cache);

		$identifier = 'NonExistingIdentifier';
		$inCache = $backend->remove($identifier);
		$this->assertFalse($inCache);
	}

	/**
	 * @test
	 */
	public function flushByTagRemovesCacheEntriesWithSpecifiedTag() {
		$cache = $this->getMock('TYPO3\Flow\Cache\Frontend\FrontendInterface', array(), array(), '', FALSE);
		$backend = new \TYPO3\Flow\Cache\Backend\TransientMemoryBackend(new ApplicationContext('Testing'));
		$backend->setCache($cache);

		$data = 'some data' . microtime();
		$backend->set('TransientMemoryBackendTest1', $data, array('UnitTestTag%test', 'UnitTestTag%boring'));
		$backend->set('TransientMemoryBackendTest2', $data, array('UnitTestTag%test', 'UnitTestTag%special'));
		$backend->set('TransientMemoryBackendTest3', $data, array('UnitTestTag%test'));

		$backend->flushByTag('UnitTestTag%special');

		$this->assertTrue($backend->has('TransientMemoryBackendTest1'), 'TransientMemoryBackendTest1');
		$this->assertFalse($backend->has('TransientMemoryBackendTest2'), 'TransientMemoryBackendTest2');
		$this->assertTrue($backend->has('TransientMemoryBackendTest3'), 'TransientMemoryBackendTest3');
	}

	/**
	 * @test
	 */
	public function flushRemovesAllCacheEntries() {
		$cache = $this->getMock('TYPO3\Flow\Cache\Frontend\FrontendInterface', array(), array(), '', FALSE);
		$backend = new \TYPO3\Flow\Cache\Backend\TransientMemoryBackend(new ApplicationContext('Testing'));
		$backend->setCache($cache);

		$data = 'some data' . microtime();
		$backend->set('TransientMemoryBackendTest1', $data);
		$backend->set('TransientMemoryBackendTest2', $data);
		$backend->set('TransientMemoryBackendTest3', $data);

		$backend->flush();

		$this->assertFalse($backend->has('TransientMemoryBackendTest1'), 'TransientMemoryBackendTest1');
		$this->assertFalse($backend->has('TransientMemoryBackendTest2'), 'TransientMemoryBackendTest2');
		$this->assertFalse($backend->has('TransientMemoryBackendTest3'), 'TransientMemoryBackendTest3');
	}
}
