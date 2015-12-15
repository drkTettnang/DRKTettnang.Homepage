<?php
namespace TYPO3\Flow\Tests\Functional\Mvc;

/*                                                                        *
 * This script belongs to the TYPO3 Flow framework.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Http\Request;
use TYPO3\Flow\Http\Uri;

/**
 * Functional tests for the ActionController
 */
class ActionControllerTest extends \TYPO3\Flow\Tests\FunctionalTestCase {

	/**
	 * @var boolean
	 */
	static protected $testablePersistenceEnabled = TRUE;

	/**
	 * Additional setup: Routes
	 */
	public function setUp() {
		parent::setUp();

		$this->registerRoute('testa', 'test/mvc/actioncontrollertesta(/{@action})', array(
			'@package' => 'TYPO3.Flow',
			'@subpackage' => 'Tests\Functional\Mvc\Fixtures',
			'@controller' => 'ActionControllerTestA',
			'@action' => 'first',
			'@format' =>'html'
		));

		$this->registerRoute('testb', 'test/mvc/actioncontrollertestb(/{@action})', array(
			'@package' => 'TYPO3.Flow',
			'@subpackage' => 'Tests\Functional\Mvc\Fixtures',
			'@controller' => 'ActionControllerTestB',
			'@format' =>'html'
		));

		$this->registerRoute('testc', 'test/mvc/actioncontrollertestc/{entity}', array(
			'@package' => 'TYPO3.Flow',
			'@subpackage' => 'Tests\Functional\Mvc\Fixtures',
			'@controller' => 'Entity',
			'@action' => 'show',
			'@format' =>'html'
		));
	}

	/**
	 * Checks if a simple request is handled correctly. The route matching the
	 * specified URI defines a default action "first" which results in firstAction
	 * being called.
	 *
	 * @test
	 */
	public function defaultActionSpecifiedInrouteIsCalledAndResponseIsReturned() {
		$response = $this->browser->request('http://localhost/test/mvc/actioncontrollertesta');
		$this->assertEquals('First action was called', $response->getContent());
		$this->assertEquals('200 OK', $response->getStatus());
	}

	/**
	 * Checks if a simple request is handled correctly if another than the default
	 * action is specified.
	 *
	 * @test
	 */
	public function actionSpecifiedInActionRequestIsCalledAndResponseIsReturned() {
		$response = $this->browser->request('http://localhost/test/mvc/actioncontrollertesta/second');
		$this->assertEquals('Second action was called', $response->getContent());
		$this->assertEquals('200 OK', $response->getStatus());
	}

	/**
	 * Checks if query parameters are handled correctly and default arguments are
	 * respected / overridden.
	 *
	 * @test
	 */
	public function queryStringOfAGetRequestIsParsedAndPassedToActionAsArguments() {
		$response = $this->browser->request('http://localhost/test/mvc/actioncontrollertesta/third?secondArgument=bar&firstArgument=foo&third=baz');
		$this->assertEquals('thirdAction-foo-bar-baz-default', $response->getContent());
	}

	/**
	 * @test
	 */
	public function defaultTemplateIsResolvedAndUsedAccordingToConventions() {
		$response = $this->browser->request('http://localhost/test/mvc/actioncontrollertesta/fourth?emailAddress=example@typo3.org');
		$this->assertEquals('Fourth action <b>example@typo3.org</b>', $response->getContent());
	}

	/**
	 * Bug #36913
	 *
	 * @test
	 */
	public function argumentsOfPutRequestArePassedToAction() {
		$request = Request::create(new Uri('http://localhost/test/mvc/actioncontrollertesta/put?getArgument=getValue'), 'PUT');
		$request->setContent("putArgument=first value");
		$request->setHeader('Content-Type', 'application/x-www-form-urlencoded');
		$request->setHeader('Content-Length', 54);

		$response = $this->browser->sendRequest($request);
		$this->assertEquals('putAction-first value-getValue', $response->getContent());
	}

	/**
	 * RFC 2616 / 10.4.5 (404 Not Found)
	 *
	 * @test
	 */
	public function notFoundStatusIsReturnedIfASpecifiedObjectCantBeFound() {
		$request = Request::create(new Uri('http://localhost/test/mvc/actioncontrollertestc/non-existing-id'), 'GET');

		$response = $this->browser->sendRequest($request);
		$this->assertSame(404, $response->getStatusCode());
	}


	/**
	 * RFC 2616 / 10.4.7 (406 Not Acceptable)
	 *
	 * @test
	 */
	public function notAcceptableStatusIsReturnedIfMediaTypeDoesNotMatchSupportedMediaTypes() {
		$request = Request::create(new Uri('http://localhost/test/mvc/actioncontrollertesta'), 'GET');
		$request->setHeader('Content-Type', 'application/xml');
		$request->setHeader('Accept', 'application/xml');
		$request->setContent('<xml></xml>');

		$response = $this->browser->sendRequest($request);
		$this->assertSame(406, $response->getStatusCode());
	}

	/**
	 * @test
	 */
	public function ignoreValidationAnnotationsAreObservedForPost() {
		$arguments = array(
			'argument' => array(
				'name' => 'Foo',
				'emailAddress' => '-invalid-'
			)
		);
		$response = $this->browser->request('http://localhost/test/mvc/actioncontrollertestb/showobjectargument', 'POST', $arguments);

		$expectedResult = '-invalid-';
		$this->assertEquals($expectedResult, $response->getContent());
	}

	/**
	 * See http://forge.typo3.org/issues/37385
	 * @test
	 */
	public function ignoreValidationAnnotationIsObservedWithAndWithoutDollarSign() {
		$response = $this->browser->request('http://localhost/test/mvc/actioncontrollertesta/ignorevalidation?brokenArgument1=toolong&brokenArgument2=tooshort');
		$this->assertEquals('action was called', $response->getContent());
	}

	/**
	 * @test
	 */
	public function argumentsOfPutRequestWithJsonOrXmlTypeAreAlsoPassedToAction() {
		$request = Request::create(new Uri('http://localhost/test/mvc/actioncontrollertesta/put?getArgument=getValue'), 'PUT');
		$request->setHeader('Content-Type', 'application/json');
		$request->setHeader('Content-Length', 29);
		$request->setContent('{"putArgument":"first value"}');

		$response = $this->browser->sendRequest($request);
		$this->assertEquals('putAction-first value-getValue', $response->getContent());
	}

	/**
	 * @test
	 */
	public function objectArgumentsAreValidatedByDefault() {
		$arguments = array(
			'argument' => array(
				'name' => 'Foo',
				'emailAddress' => '-invalid-'
			)
		);
		$response = $this->browser->request('http://localhost/test/mvc/actioncontrollertestb/requiredobject', 'POST', $arguments);

		$expectedResult = 'Validation failed while trying to call TYPO3\Flow\Tests\Functional\Mvc\Fixtures\Controller\ActionControllerTestBController->requiredObjectAction().' . PHP_EOL;
		$this->assertEquals($expectedResult, $response->getContent());
	}

	/**
	 * @test
	 */
	public function optionalObjectArgumentsAreValidatedByDefault() {
		$arguments = array(
			'argument' => array(
				'name' => 'Foo',
				'emailAddress' => '-invalid-'
			)
		);
		$response = $this->browser->request('http://localhost/test/mvc/actioncontrollertestb/optionalobject', 'POST', $arguments);

		$expectedResult = 'Validation failed while trying to call TYPO3\Flow\Tests\Functional\Mvc\Fixtures\Controller\ActionControllerTestBController->optionalObjectAction().' . PHP_EOL;
		$this->assertEquals($expectedResult, $response->getContent());
	}

	/**
	 * @test
	 */
	public function optionalObjectArgumentsCanBeOmitted() {
		$response = $this->browser->request('http://localhost/test/mvc/actioncontrollertestb/optionalobject');

		$expectedResult = 'null';
		$this->assertEquals($expectedResult, $response->getContent());
	}

	/**
	 * @test
	 */
	public function notValidatedGroupObjectArgumentsAreNotValidated() {
		$arguments = array(
			'argument' => array(
				'name' => 'Foo',
				'emailAddress' => '-invalid-'
			)
		);
		$response = $this->browser->request('http://localhost/test/mvc/actioncontrollertestb/notvalidatedgroupobject', 'POST', $arguments);

		$expectedResult = '-invalid-';
		$this->assertEquals($expectedResult, $response->getContent());
	}

	/**
	 * @test
	 */
	public function validatedGroupObjectArgumentsAreValidated() {
		$arguments = array(
			'argument' => array(
				'name' => 'Foo',
				'emailAddress' => '-invalid-'
			)
		);
		$response = $this->browser->request('http://localhost/test/mvc/actioncontrollertestb/validatedgroupobject', 'POST', $arguments);

		$expectedResult = 'Validation failed while trying to call TYPO3\Flow\Tests\Functional\Mvc\Fixtures\Controller\ActionControllerTestBController->validatedGroupObjectAction().' . PHP_EOL;
		$this->assertEquals($expectedResult, $response->getContent());
	}

	/**
	 * Data provider for argumentTests()
	 *
	 * @TODO Using 'optional float - default value'    => array('optionalFloat', NULL, 12.34),
	 * this fails (on some machines) because the value is 12.33999999...
	 *
	 * @return array
	 */
	public function argumentTestsDataProvider() {
		$requiredArgumentExceptionText = 'Uncaught Exception in Flow #1298012500: Required argument "argument" is not set.';
		return array(
			'required string            '       => array('requiredString', 'some String', '\'some String\''),
			'required string - missing value'   => array('requiredString', NULL, $requiredArgumentExceptionText),
			'optional string'                   => array('optionalString', '123', '\'123\''),
			'optional string - default'         => array('optionalString', NULL, '\'default\''),
			'required integer'                  => array('requiredInteger', '234', 234),
			'required integer - missing value'  => array('requiredInteger', NULL, $requiredArgumentExceptionText),
			'required integer - mapping error'  => array('requiredInteger', 'not an integer', 'Validation failed while trying to call TYPO3\Flow\Tests\Functional\Mvc\Fixtures\Controller\ActionControllerTestBController->requiredIntegerAction().'),
			'required integer - empty value'    => array('requiredInteger', '', 'NULL'),
			'optional integer'                  => array('optionalInteger', 456, 456),
			'optional integer - default value'  => array('optionalInteger', NULL, 123),
			'optional integer - mapping error'  => array('optionalInteger', 'not an integer', 'Validation failed while trying to call TYPO3\Flow\Tests\Functional\Mvc\Fixtures\Controller\ActionControllerTestBController->optionalIntegerAction().'),
			'optional integer - empty value'    => array('optionalInteger', '', 123),
			'required float'                    => array('requiredFloat', 34.56, 34.56),
			'required float - integer'          => array('requiredFloat', 485, '485'),
			'required float - integer2'         => array('requiredFloat', '888', '888'),
			'required float - missing value'    => array('requiredFloat', NULL, $requiredArgumentExceptionText),
			'required float - mapping error'    => array('requiredFloat', 'not a float', 'Validation failed while trying to call TYPO3\Flow\Tests\Functional\Mvc\Fixtures\Controller\ActionControllerTestBController->requiredFloatAction().'),
			'required float - empty value'      => array('requiredFloat', '', 'NULL'),
			'optional float'                    => array('optionalFloat', 78.90, 78.9),
			'optional float - default value'    => array('optionalFloat', NULL, 112.34),
			'optional float - mapping error'    => array('optionalFloat', 'not a float', 'Validation failed while trying to call TYPO3\Flow\Tests\Functional\Mvc\Fixtures\Controller\ActionControllerTestBController->optionalFloatAction().'),
			'optional float - empty value'      => array('optionalFloat', '', 112.34),
			'required date'                     => array('requiredDate', array('date' => '1980-12-13', 'dateFormat' => 'Y-m-d'), '1980-12-13'),
			'required date string'              => array('requiredDate', '1980-12-13T14:22:12+02:00', '1980-12-13'),
			'required date - missing value'     => array('requiredDate', NULL, $requiredArgumentExceptionText),
			'required date - mapping error'     => array('requiredDate', 'no date', 'Validation failed while trying to call TYPO3\Flow\Tests\Functional\Mvc\Fixtures\Controller\ActionControllerTestBController->requiredDateAction().'),
			'required date - empty value'       => array('requiredDate', '', 'Uncaught Exception in Flow #1: Catchable Fatal Error: Argument 1 passed to TYPO3\Flow\Tests\Functional\Mvc\Fixtures\Controller\ActionControllerTestBController_Original::requiredDateAction() must be an instance of DateTime, null given'),
			'optional date string'              => array('optionalDate', '1980-12-13T14:22:12+02:00', '1980-12-13'),
			'optional date - default value'     => array('optionalDate', NULL, 'null'),
			'optional date - mapping error'     => array('optionalDate', 'no date', 'Validation failed while trying to call TYPO3\Flow\Tests\Functional\Mvc\Fixtures\Controller\ActionControllerTestBController->optionalDateAction().'),
			'optional date - missing value'     => array('optionalDate', NULL, 'null'),
			'optional date - empty value'       => array('optionalDate', '', 'null'),
		);
	}

	/**
	 * Tut Dinge.
	 *
	 * @param string $action
	 * @param mixed $argument
	 * @param string $expectedResult
	 * @test
	 * @dataProvider argumentTestsDataProvider
	 */
	public function argumentTests($action, $argument, $expectedResult) {
		$arguments = array(
			'argument' => $argument,
		);

		$uri = str_replace('{@action}', strtolower($action), 'http://localhost/test/mvc/actioncontrollertestb/{@action}');
		$response = $this->browser->request($uri, 'POST', $arguments);
		$this->assertTrue(strpos(trim($response->getContent()), (string)$expectedResult) === 0, sprintf('The resulting string did not start with the expected string. Expected: "%s", Actual: "%s"', $expectedResult, $response->getContent()));
	}

}
