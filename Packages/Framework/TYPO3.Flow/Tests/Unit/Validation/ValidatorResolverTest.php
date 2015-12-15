<?php
namespace TYPO3\Flow\Tests\Unit\Validation;

/*                                                                        *
 * This script belongs to the TYPO3 Flow framework.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Object\Configuration\Configuration;

/**
 * Testcase for the validator resolver
 *
 */
class ValidatorResolverTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @var \TYPO3\Flow\Validation\ValidatorResolver
	 */
	protected $validatorResolver;

	/**
	 * @var \TYPO3\Flow\Object\ObjectManagerInterface
	 */
	protected $mockObjectManager;

	/**
	 * @var \TYPO3\Flow\Reflection\ReflectionService
	 */
	protected $mockReflectionService;

	public function setUp() {
		$this->mockObjectManager = $this->getMock('TYPO3\Flow\Object\ObjectManagerInterface');
		$this->mockReflectionService = $this->getMock('TYPO3\Flow\Reflection\ReflectionService');

		$this->validatorResolver = $this->getAccessibleMock('TYPO3\Flow\Validation\ValidatorResolver', array('dummy'));
		$this->inject($this->validatorResolver, 'objectManager', $this->mockObjectManager);
	}

	/**
	 * @test
	 */
	public function resolveValidatorObjectNameReturnsFalseIfValidatorCantBeResolved() {
		$this->mockReflectionService->expects($this->any())->method('getAllImplementationClassNamesForInterface')->with('TYPO3\Flow\Validation\Validator\ValidatorInterface')->will($this->returnValue(array('Foo')));

		$this->mockObjectManager->expects($this->at(0))->method('get')->with('TYPO3\Flow\Reflection\ReflectionService')->will($this->returnValue($this->mockReflectionService));
		$this->mockObjectManager->expects($this->at(1))->method('isRegistered')->with('Foo')->will($this->returnValue(FALSE));
		$this->mockObjectManager->expects($this->at(2))->method('isRegistered')->with('TYPO3\Flow\Validation\Validator\FooValidator')->will($this->returnValue(FALSE));

		$this->assertSame(FALSE, $this->validatorResolver->_call('resolveValidatorObjectName', 'Foo'));
	}

	/**
	 * @test
	 */
	public function resolveValidatorObjectNameReturnsTheGivenArgumentIfAnObjectOfThatNameIsRegisteredAndImplementsValidatorInterface() {
		$this->mockObjectManager->expects($this->any())->method('get')->with('TYPO3\Flow\Reflection\ReflectionService')->will($this->returnValue($this->mockReflectionService));
		$this->mockObjectManager->expects($this->any())->method('isRegistered')->with('Foo')->will($this->returnValue(TRUE));
		$this->mockReflectionService->expects($this->any())->method('getAllImplementationClassNamesForInterface')->with('TYPO3\Flow\Validation\Validator\ValidatorInterface')->will($this->returnValue(array('Foo')));

		$this->assertSame('Foo', $this->validatorResolver->_call('resolveValidatorObjectName', 'Foo'));
	}

	/**
	 * @test
	 */
	public function resolveValidatorObjectNameReturnsFalseIfAnObjectOfTheArgumentNameIsRegisteredButDoesNotImplementValidatorInterface() {
		$this->mockObjectManager->expects($this->at(0))->method('get')->with('TYPO3\Flow\Reflection\ReflectionService')->will($this->returnValue($this->mockReflectionService));
		$this->mockObjectManager->expects($this->at(1))->method('isRegistered')->with('Foo')->will($this->returnValue(TRUE));
		$this->mockObjectManager->expects($this->at(2))->method('isRegistered')->with('TYPO3\Flow\Validation\Validator\FooValidator')->will($this->returnValue(FALSE));
		$this->mockReflectionService->expects($this->any())->method('getAllImplementationClassNamesForInterface')->with('TYPO3\Flow\Validation\Validator\ValidatorInterface')->will($this->returnValue(array('Bar')));

		$this->assertFalse($this->validatorResolver->_call('resolveValidatorObjectName', 'Foo'));
	}

	/**
	 * @test
	 */
	public function resolveValidatorObjectNameReturnsValidatorObjectNameIfAnObjectOfTheArgumentNameIsRegisteredAndDoesNotImplementValidatorInterfaceAndAValidatorForTheObjectExists() {
		$this->mockObjectManager->expects($this->at(0))->method('get')->with('TYPO3\Flow\Reflection\ReflectionService')->will($this->returnValue($this->mockReflectionService));
		$this->mockObjectManager->expects($this->at(1))->method('isRegistered')->with('DateTime')->will($this->returnValue(TRUE));
		$this->mockObjectManager->expects($this->at(2))->method('isRegistered')->with('TYPO3\Flow\Validation\Validator\DateTimeValidator')->will($this->returnValue(TRUE));
		$this->mockReflectionService->expects($this->any())->method('getAllImplementationClassNamesForInterface')->with('TYPO3\Flow\Validation\Validator\ValidatorInterface')->will($this->returnValue(array('TYPO3\Flow\Validation\Validator\DateTimeValidator')));

		$this->assertSame('TYPO3\Flow\Validation\Validator\DateTimeValidator', $this->validatorResolver->_call('resolveValidatorObjectName', 'DateTime'));
	}

	/**
	 * @test
	 */
	public function resolveValidatorObjectNameRemovesALeadingBackslashFromThePassedType() {
		$this->mockObjectManager->expects($this->any())->method('get')->with('TYPO3\Flow\Reflection\ReflectionService')->will($this->returnValue($this->mockReflectionService));
		$this->mockObjectManager->expects($this->any())->method('isRegistered')->with('Foo\Bar')->will($this->returnValue(TRUE));
		$this->mockReflectionService->expects($this->any())->method('getAllImplementationClassNamesForInterface')->with('TYPO3\Flow\Validation\Validator\ValidatorInterface')->will($this->returnValue(array('Foo\Bar')));

		$this->assertSame('Foo\Bar', $this->validatorResolver->_call('resolveValidatorObjectName', '\Foo\Bar'));
	}

	/**
	 * @test
	 */
	public function resolveValidatorObjectNameCanResolveShorthandValidatornames() {
		$this->mockObjectManager->expects($this->at(0))->method('get')->with('TYPO3\Flow\Reflection\ReflectionService')->will($this->returnValue($this->mockReflectionService));
		$this->mockObjectManager->expects($this->at(1))->method('isRegistered')->with('Mypkg:My')->will($this->returnValue(FALSE));
		$this->mockObjectManager->expects($this->at(2))->method('isRegistered')->with('Mypkg\Validation\Validator\MyValidator')->will($this->returnValue(TRUE));

		$this->mockReflectionService->expects($this->any())->method('getAllImplementationClassNamesForInterface')->with('TYPO3\Flow\Validation\Validator\ValidatorInterface')->will($this->returnValue(array('Mypkg\Validation\Validator\MyValidator')));

		$this->assertSame('Mypkg\Validation\Validator\MyValidator', $this->validatorResolver->_call('resolveValidatorObjectName', 'Mypkg:My'));
	}

	/**
	 * @test
	 */
	public function resolveValidatorObjectNameCanResolveShorthandValidatornamesForHierarchicalPackages() {
		$this->mockObjectManager->expects($this->at(0))->method('get')->with('TYPO3\Flow\Reflection\ReflectionService')->will($this->returnValue($this->mockReflectionService));
		$this->mockObjectManager->expects($this->at(1))->method('isRegistered')->with('Mypkg.Foo:My')->will($this->returnValue(FALSE));
		$this->mockObjectManager->expects($this->at(2))->method('isRegistered')->with('Mypkg\Foo\Validation\Validator\\MyValidator')->will($this->returnValue(TRUE));

		$this->mockReflectionService->expects($this->any())->method('getAllImplementationClassNamesForInterface')->with('TYPO3\Flow\Validation\Validator\ValidatorInterface')->will($this->returnValue(array('Mypkg\Foo\Validation\Validator\MyValidator')));

		$this->assertSame('Mypkg\Foo\Validation\Validator\MyValidator', $this->validatorResolver->_call('resolveValidatorObjectName', 'Mypkg.Foo:My'));
	}

	/**
	 * @test
	 */
	public function resolveValidatorObjectNameCanResolveShortNamesOfBuiltInValidators() {
		$this->mockObjectManager->expects($this->at(0))->method('get')->with('TYPO3\Flow\Reflection\ReflectionService')->will($this->returnValue($this->mockReflectionService));
		$this->mockObjectManager->expects($this->at(1))->method('isRegistered')->with('Foo')->will($this->returnValue(FALSE));
		$this->mockObjectManager->expects($this->at(2))->method('isRegistered')->with('TYPO3\Flow\Validation\Validator\FooValidator')->will($this->returnValue(TRUE));
		$this->mockReflectionService->expects($this->any())->method('getAllImplementationClassNamesForInterface')->with('TYPO3\Flow\Validation\Validator\ValidatorInterface')->will($this->returnValue(array('TYPO3\Flow\Validation\Validator\FooValidator')));
		$this->assertSame('TYPO3\Flow\Validation\Validator\FooValidator', $this->validatorResolver->_call('resolveValidatorObjectName', 'Foo'));
	}

	/**
	 * @test
	 */
	public function resolveValidatorObjectNameCallsGetValidatorType() {
		$mockObjectManager = $this->getMock('TYPO3\Flow\Object\ObjectManagerInterface');
		$mockObjectManager->expects($this->any())->method('get')->with('TYPO3\Flow\Reflection\ReflectionService')->will($this->returnValue($this->mockReflectionService));

		$this->mockReflectionService->expects($this->any())->method('getAllImplementationClassNamesForInterface')->with('TYPO3\Flow\Validation\Validator\ValidatorInterface')->will($this->returnValue(array()));

		$validatorResolver = $this->getAccessibleMock('TYPO3\Flow\Validation\ValidatorResolver', array('getValidatorType'));
		$validatorResolver->_set('objectManager', $mockObjectManager);

		$validatorResolver->expects($this->once())->method('getValidatorType')->with('someDataType');
		$validatorResolver->_call('resolveValidatorObjectName', 'someDataType');
	}

	/**
	 * @test
	 */
	public function createValidatorResolvesAndReturnsAValidatorAndPassesTheGivenOptions() {
		$className = 'Test' . md5(uniqid(mt_rand(), TRUE));
		eval('class ' . $className . ' implements \TYPO3\Flow\Validation\Validator\ValidatorInterface {
				protected $options = array();
				public function __construct(array $options = array()) {
					$this->options = $options;
				}
				public function validate($subject) {}
				public function getOptions() { return $this->options; }
			}');
		$mockObjectManager = $this->getMock('TYPO3\Flow\Object\ObjectManagerInterface');
		$mockObjectManager->expects($this->any())->method('getScope')->with($className)->will($this->returnValue(Configuration::SCOPE_PROTOTYPE));

		$validatorResolver = $this->getAccessibleMock('TYPO3\Flow\Validation\ValidatorResolver', array('resolveValidatorObjectName'));
		$validatorResolver->_set('objectManager', $mockObjectManager);
		$validatorResolver->expects($this->once())->method('resolveValidatorObjectName')->with($className)->will($this->returnValue($className));
		$validator = $validatorResolver->createValidator($className, array('foo' => 'bar'));
		$this->assertInstanceOf($className, $validator);
		$this->assertEquals(array('foo' => 'bar'), $validator->getOptions());
	}

	/**
	 * @test
	 */
	public function createValidatorReturnsNullIfAValidatorCouldNotBeResolved() {
		$validatorResolver = $this->getMock('TYPO3\Flow\Validation\ValidatorResolver', array('resolveValidatorObjectName'), array(), '', FALSE);
		$validatorResolver->expects($this->once())->method('resolveValidatorObjectName')->with('Foo')->will($this->returnValue(FALSE));
		$validator = $validatorResolver->createValidator('Foo', array('foo' => 'bar'));
		$this->assertNull($validator);
	}

	/**
	 * @test
	 * @expectedException \TYPO3\Flow\Validation\Exception\InvalidValidationConfigurationException
	 */
	public function createValidatorThrowsExceptionForSingletonValidatorsWithOptions() {
		$mockObjectManager = $this->getMock('TYPO3\Flow\Object\ObjectManagerInterface');
		$mockObjectManager->expects($this->once())->method('getScope')->with('FooType')->will($this->returnValue(Configuration::SCOPE_SINGLETON));

		$validatorResolver = $this->getMock('TYPO3\Flow\Validation\ValidatorResolver', array('resolveValidatorObjectName'), array(), '', FALSE);
		$this->inject($validatorResolver, 'objectManager', $mockObjectManager);
		$validatorResolver->expects($this->once())->method('resolveValidatorObjectName')->with('FooType')->will($this->returnValue('FooType'));
		$validatorResolver->createValidator('FooType', array('foo' => 'bar'));
	}

	/**
	 * @test
	 */
	public function buildBaseValidatorCachesTheResultOfTheBuildBaseValidatorConjunctionCalls() {
		$mockReflectionService = $this->getMock('TYPO3\Flow\Reflection\ReflectionService');
		$mockReflectionService->expects($this->at(0))->method('getAllImplementationClassNamesForInterface')->with('TYPO3\Flow\Validation\Validator\ValidatorInterface')->will($this->returnValue(array()));
		$mockReflectionService->expects($this->at(1))->method('getAllImplementationClassNamesForInterface')->with('TYPO3\Flow\Validation\Validator\PolyTypeObjectValidatorInterface')->will($this->returnValue(array()));
		$mockObjectManager = $this->getMock('TYPO3\Flow\Object\ObjectManagerInterface', array(), array(), '', FALSE);
		$mockObjectManager->expects($this->any())->method('get')->will($this->returnValue($mockReflectionService));
		$this->validatorResolver->_set('objectManager', $mockObjectManager);
		$this->validatorResolver->_set('reflectionService', $mockReflectionService);

		$result1 = $this->validatorResolver->getBaseValidatorConjunction('TYPO3\Virtual\Foo');
		$this->assertInstanceOf('TYPO3\Flow\Validation\Validator\ConjunctionValidator', $result1, '#1');

		$result2 = $this->validatorResolver->getBaseValidatorConjunction('TYPO3\Virtual\Foo');
		$this->assertSame($result1, $result2, '#2');
	}

	/**
	 * @test
	 */
	public function buildMethodArgumentsValidatorConjunctionsReturnsEmptyArrayIfMethodHasNoArguments() {
		$mockController = $this->getAccessibleMock('TYPO3\Flow\Mvc\Controller\ActionController', array('fooAction'), array(), '', FALSE);

		$mockReflectionService = $this->getMock('TYPO3\Flow\Reflection\ReflectionService', array(), array(), '', FALSE);
		$mockReflectionService->expects($this->once())->method('getMethodParameters')->with(get_class($mockController), 'fooAction')->will($this->returnValue(array()));

		$this->validatorResolver = $this->getAccessibleMock('TYPO3\Flow\Validation\ValidatorResolver', array('createValidator'), array(), '', FALSE);
		$this->validatorResolver->_set('reflectionService', $mockReflectionService);

		$result = $this->validatorResolver->buildMethodArgumentsValidatorConjunctions(get_class($mockController), 'fooAction');
		$this->assertSame(array(), $result);
	}

	/**
	 * @test
	 */
	public function buildMethodArgumentsValidatorConjunctionsBuildsAConjunctionFromValidateAnnotationsOfTheSpecifiedMethod() {
		$mockObject = new \stdClass();

		$methodParameters = array(
			'arg1' => array(
				'type' => 'string'
			),
			'arg2' => array(
				'type' => 'array'
			)

		);
		$validateAnnotations = array(
			new \TYPO3\Flow\Annotations\Validate(array(
				'type' => 'Foo',
				'options' => array('bar' => 'baz'),
				'argumentName' => '$arg1'
			)),
			new \TYPO3\Flow\Annotations\Validate(array(
				'type' => 'Bar',
				'argumentName' => '$arg1'
			)),
			new \TYPO3\Flow\Annotations\Validate(array(
				'type' => 'TYPO3\TestPackage\Quux',
				'argumentName' => '$arg2'
			)),
		);

		$mockReflectionService = $this->getMock('TYPO3\Flow\Reflection\ReflectionService', array(), array(), '', FALSE);
		$mockReflectionService->expects($this->once())->method('getMethodParameters')->with(get_class($mockObject), 'fooAction')->will($this->returnValue($methodParameters));
		$mockReflectionService->expects($this->once())->method('getMethodAnnotations')->with(get_class($mockObject), 'fooAction', 'TYPO3\Flow\Annotations\Validate')->will($this->returnValue($validateAnnotations));

		$mockStringValidator = $this->getMock('TYPO3\Flow\Validation\Validator\ValidatorInterface', array(), array(), '', FALSE);
		$mockArrayValidator = $this->getMock('TYPO3\Flow\Validation\Validator\ValidatorInterface', array(), array(), '', FALSE);
		$mockFooValidator = $this->getMock('TYPO3\Flow\Validation\Validator\ValidatorInterface', array(), array(), '', FALSE);
		$mockBarValidator = $this->getMock('TYPO3\Flow\Validation\Validator\ValidatorInterface', array(), array(), '', FALSE);
		$mockQuuxValidator = $this->getMock('TYPO3\Flow\Validation\Validator\ValidatorInterface', array(), array(), '', FALSE);

		$conjunction1 = $this->getMock('TYPO3\Flow\Validation\Validator\ConjunctionValidator', array(), array(), '', FALSE);
		$conjunction1->expects($this->at(0))->method('addValidator')->with($mockStringValidator);
		$conjunction1->expects($this->at(1))->method('addValidator')->with($mockFooValidator);
		$conjunction1->expects($this->at(2))->method('addValidator')->with($mockBarValidator);

		$conjunction2 = $this->getMock('TYPO3\Flow\Validation\Validator\ConjunctionValidator', array(), array(), '', FALSE);
		$conjunction2->expects($this->at(0))->method('addValidator')->with($mockArrayValidator);
		$conjunction2->expects($this->at(1))->method('addValidator')->with($mockQuuxValidator);

		$validatorResolver = $this->getAccessibleMock('TYPO3\Flow\Validation\ValidatorResolver', array('createValidator'), array(), '', FALSE);
		$validatorResolver->expects($this->at(0))->method('createValidator')->with('TYPO3\Flow\Validation\Validator\ConjunctionValidator')->will($this->returnValue($conjunction1));
		$validatorResolver->expects($this->at(1))->method('createValidator')->with('string')->will($this->returnValue($mockStringValidator));
		$validatorResolver->expects($this->at(2))->method('createValidator')->with('TYPO3\Flow\Validation\Validator\ConjunctionValidator')->will($this->returnValue($conjunction2));
		$validatorResolver->expects($this->at(3))->method('createValidator')->with('array')->will($this->returnValue($mockArrayValidator));
		$validatorResolver->expects($this->at(4))->method('createValidator')->with('Foo', array('bar' => 'baz'))->will($this->returnValue($mockFooValidator));
		$validatorResolver->expects($this->at(5))->method('createValidator')->with('Bar')->will($this->returnValue($mockBarValidator));
		$validatorResolver->expects($this->at(6))->method('createValidator')->with('TYPO3\TestPackage\Quux')->will($this->returnValue($mockQuuxValidator));

		$validatorResolver->_set('reflectionService', $mockReflectionService);

		$result = $validatorResolver->buildMethodArgumentsValidatorConjunctions(get_class($mockObject), 'fooAction');
		$this->assertEquals(array('arg1' => $conjunction1, 'arg2' => $conjunction2), $result);
	}

	/**
	 * @test
	 */
	public function buildMethodArgumentsValidatorConjunctionsReturnsEmptyConjunctionIfNoValidatorIsFoundForMethodParameter() {
		$mockObject = new \stdClass();

		$methodParameters = array(
			'arg' => array(
				'type' => 'FLOW8\Blog\Domain\Model\Blog'
			)
		);

		$mockReflectionService = $this->getMock('TYPO3\Flow\Reflection\ReflectionService', array(), array(), '', FALSE);
		$mockReflectionService->expects($this->once())->method('getMethodParameters')->with(get_class($mockObject), 'fooAction')->will($this->returnValue($methodParameters));
		$mockReflectionService->expects($this->once())->method('getMethodAnnotations')->with(get_class($mockObject), 'fooAction', 'TYPO3\Flow\Annotations\Validate')->will($this->returnValue(array()));

		$conjunction = $this->getMock('TYPO3\Flow\Validation\Validator\ConjunctionValidator', array(), array(), '', FALSE);
		$conjunction->expects($this->never())->method('addValidator');

		$validatorResolver = $this->getAccessibleMock('TYPO3\Flow\Validation\ValidatorResolver', array('createValidator'), array(), '', FALSE);
		$validatorResolver->expects($this->at(0))->method('createValidator')->with('TYPO3\Flow\Validation\Validator\ConjunctionValidator')->will($this->returnValue($conjunction));

		$validatorResolver->_set('reflectionService', $mockReflectionService);

		$validatorResolver->buildMethodArgumentsValidatorConjunctions(get_class($mockObject), 'fooAction');
	}

	/**
	 * @test
	 * @expectedException \TYPO3\Flow\Validation\Exception\InvalidValidationConfigurationException
	 */
	public function buildMethodArgumentsValidatorConjunctionsThrowsExceptionIfValidationAnnotationForNonExistingArgumentExists() {
		$mockObject = new \stdClass();

		$methodParameters = array(
			'arg1' => array(
				'type' => 'string'
			)
		);
		$validateAnnotations = array(
			new \TYPO3\Flow\Annotations\Validate(array(
				'type' => 'TYPO3\TestPackage\Quux',
				'argumentName' => '$arg2'
			)),
		);

		$mockReflectionService = $this->getMock('TYPO3\Flow\Reflection\ReflectionService', array(), array(), '', FALSE);
		$mockReflectionService->expects($this->once())->method('getMethodAnnotations')->with(get_class($mockObject), 'fooAction', 'TYPO3\Flow\Annotations\Validate')->will($this->returnValue($validateAnnotations));
		$mockReflectionService->expects($this->once())->method('getMethodParameters')->with(get_class($mockObject), 'fooAction')->will($this->returnValue($methodParameters));

		$mockStringValidator = $this->getMock('TYPO3\Flow\Validation\Validator\ValidatorInterface', array(), array(), '', FALSE);
		$mockQuuxValidator = $this->getMock('TYPO3\Flow\Validation\Validator\ValidatorInterface', array(), array(), '', FALSE);
		$conjunction1 = $this->getMock('TYPO3\Flow\Validation\Validator\ConjunctionValidator', array(), array(), '', FALSE);
		$conjunction1->expects($this->at(0))->method('addValidator')->with($mockStringValidator);

		$validatorResolver = $this->getAccessibleMock('TYPO3\Flow\Validation\ValidatorResolver', array('createValidator'), array(), '', FALSE);
		$validatorResolver->expects($this->at(0))->method('createValidator')->with('TYPO3\Flow\Validation\Validator\ConjunctionValidator')->will($this->returnValue($conjunction1));
		$validatorResolver->expects($this->at(1))->method('createValidator')->with('string')->will($this->returnValue($mockStringValidator));
		$validatorResolver->expects($this->at(2))->method('createValidator')->with('TYPO3\TestPackage\Quux')->will($this->returnValue($mockQuuxValidator));

		$validatorResolver->_set('reflectionService', $mockReflectionService);

		$validatorResolver->buildMethodArgumentsValidatorConjunctions(get_class($mockObject), 'fooAction');
	}

	/**
	 * @test
	 */
	public function buildBaseValidatorConjunctionAddsCustomValidatorToTheReturnedConjunction() {
		$modelClassName = 'Page' . md5(uniqid(mt_rand(), TRUE));
		$validatorClassName = 'Domain\Validator\Content\\' . $modelClassName . 'Validator';
		eval('namespace Domain\Model\Content; class ' . $modelClassName . '{}');

		$modelClassName = 'Domain\Model\Content\\' . $modelClassName;

		$mockReflectionService = $this->getMock('\TYPO3\Flow\Reflection\ReflectionService');
		$mockReflectionService->expects($this->any())->method('getClassPropertyNames')->will($this->returnValue(array()));
		$mockObjectManager = $this->getMock('TYPO3\Flow\Object\ObjectManagerInterface');
		$mockObjectManager->expects($this->any())->method('get')->with('TYPO3\Flow\Reflection\ReflectionService')->will($this->returnValue($mockReflectionService));
		$validatorResolver = $this->getAccessibleMock('TYPO3\Flow\Validation\ValidatorResolver', array('resolveValidatorObjectName', 'createValidator'));
		$validatorResolver->_set('reflectionService', $mockReflectionService);
		$validatorResolver->_set('objectManager', $mockObjectManager);
		$validatorResolver->expects($this->once())->method('createValidator')->with($validatorClassName)->will($this->returnValue(new \TYPO3\Flow\Validation\Validator\EmailAddressValidator()));
		$mockReflectionService->expects($this->any())->method('getAllImplementationClassNamesForInterface')->with('TYPO3\Flow\Validation\Validator\PolyTypeObjectValidatorInterface')->will($this->returnValue(array()));

		$validatorResolver->_call('buildBaseValidatorConjunction', $modelClassName, $modelClassName, array('Default'));
		$builtValidators = $validatorResolver->_get('baseValidatorConjunctions');

		$this->assertFalse($builtValidators[$modelClassName]->validate('foo@example.com')->hasErrors());
		$this->assertTrue($builtValidators[$modelClassName]->validate('foo')->hasErrors());
	}

	/**
	 * @test
	 */
	public function addCustomValidatorsAddsExpectedPolyTypeValidatorToTheConjunction() {
		$highPriorityValidatorClassName = 'RandomHighPrio' . md5(uniqid(mt_rand(), TRUE)) . 'PolyTypeValidator';
		$lowPriorityValidatorClassName = 'RandomLowPrio' . md5(uniqid(mt_rand(), TRUE)) . 'PolyTypeValidator';
		$modelClassName = 'Acme\Test\Content\Page' . md5(uniqid(mt_rand(), TRUE));

		$mockLowPriorityValidator = $this->getMock('TYPO3\Flow\Validation\Validator\PolyTypeObjectValidatorInterface', array(), array(), $lowPriorityValidatorClassName);
		$mockLowPriorityValidator->expects($this->atLeastOnce())->method('canValidate')->with($modelClassName)->will($this->returnValue(TRUE));
		$mockLowPriorityValidator->expects($this->atLeastOnce())->method('getPriority')->will($this->returnValue(100));
		$mockHighPriorityValidator = $this->getMock('TYPO3\Flow\Validation\Validator\PolyTypeObjectValidatorInterface', array(), array(), $highPriorityValidatorClassName);
		$mockHighPriorityValidator->expects($this->atLeastOnce())->method('canValidate')->with($modelClassName)->will($this->returnValue(TRUE));
		$mockHighPriorityValidator->expects($this->atLeastOnce())->method('getPriority')->will($this->returnValue(200));

		$mockConjunctionValidator = $this->getMock('TYPO3\Flow\Validation\Validator\ConjunctionValidator', array('addValidator'));
		$mockConjunctionValidator->expects($this->once())->method('addValidator')->with($mockHighPriorityValidator);

		$mockReflectionService = $this->getMock('\TYPO3\Flow\Reflection\ReflectionService');
		$mockReflectionService->expects($this->any())->method('getAllImplementationClassNamesForInterface')->with('TYPO3\Flow\Validation\Validator\PolyTypeObjectValidatorInterface')->will($this->returnValue(array($highPriorityValidatorClassName, $lowPriorityValidatorClassName)));
		$mockObjectManager = $this->getMock('TYPO3\Flow\Object\ObjectManagerInterface');
		$mockObjectManager->expects($this->any())->method('get')->with('TYPO3\Flow\Reflection\ReflectionService')->will($this->returnValue($mockReflectionService));
		$validatorResolver = $this->getAccessibleMock('TYPO3\Flow\Validation\ValidatorResolver', array('createValidator'));
		$validatorResolver->_set('reflectionService', $mockReflectionService);
		$validatorResolver->_set('objectManager', $mockObjectManager);
		$validatorResolver->expects($this->at(0))->method('createValidator')->will($this->returnValue(NULL));
		$validatorResolver->expects($this->at(1))->method('createValidator')->with($highPriorityValidatorClassName)->will($this->returnValue($mockHighPriorityValidator));
		$validatorResolver->expects($this->at(2))->method('createValidator')->with($lowPriorityValidatorClassName)->will($this->returnValue($mockLowPriorityValidator));

		$validatorResolver->_callRef('addCustomValidators', $modelClassName, $mockConjunctionValidator);
	}

	/**
	 * @test
	 */
	public function buildBaseValidatorConjunctionAddsValidatorsOnlyForPropertiesHoldingPrototypes() {
		$entityClassName = 'Entity' . md5(uniqid(mt_rand(), TRUE));
		eval('class ' . $entityClassName . '{}');
		$otherClassName = 'Other' . md5(uniqid(mt_rand(), TRUE));
		eval('class ' . $otherClassName . '{}');
		$modelClassName = 'Model' . md5(uniqid(mt_rand(), TRUE));
		eval('class ' . $modelClassName . '{}');

		$mockObjectManager = $this->getMock('TYPO3\Flow\Object\ObjectManagerInterface', array(), array(), '', FALSE);
		$mockObjectManager->expects($this->any())->method('isRegistered')->will($this->returnValue(TRUE));
		$mockObjectManager->expects($this->at(1))->method('getScope')->with($entityClassName)->will($this->returnValue(Configuration::SCOPE_PROTOTYPE));
		$mockObjectManager->expects($this->at(3))->method('getScope')->with($otherClassName)->will($this->returnValue(NULL));

		$mockReflectionService = $this->getMock('\TYPO3\Flow\Reflection\ReflectionService');
		$mockReflectionService->expects($this->any())->method('getAllImplementationClassNamesForInterface')->with('TYPO3\Flow\Validation\Validator\PolyTypeObjectValidatorInterface')->will($this->returnValue(array()));
		$mockReflectionService->expects($this->any())->method('getClassPropertyNames')->will($this->returnValue(array('entityProperty', 'otherProperty')));
		$mockReflectionService->expects($this->at(1))->method('getPropertyTagsValues')->with($modelClassName, 'entityProperty')->will($this->returnValue(array('var' => array($entityClassName))));
		$mockReflectionService->expects($this->at(2))->method('isPropertyAnnotatedWith')->will($this->returnValue(FALSE));
		$mockReflectionService->expects($this->at(3))->method('getPropertyAnnotations')->with($modelClassName, 'entityProperty', 'TYPO3\Flow\Annotations\Validate')->will($this->returnValue(array()));
		$mockReflectionService->expects($this->at(4))->method('getPropertyTagsValues')->with($modelClassName, 'otherProperty')->will($this->returnValue(array('var' => array($otherClassName))));
		$mockReflectionService->expects($this->at(5))->method('isPropertyAnnotatedWith')->will($this->returnValue(FALSE));
		$mockReflectionService->expects($this->at(6))->method('getPropertyAnnotations')->with($modelClassName, 'otherProperty', 'TYPO3\Flow\Annotations\Validate')->will($this->returnValue(array()));

		$mockObjectManager->expects($this->any())->method('get')->with('TYPO3\Flow\Reflection\ReflectionService')->will($this->returnValue($mockReflectionService));
		$validatorResolver = $this->getAccessibleMock('TYPO3\Flow\Validation\ValidatorResolver', array('resolveValidatorObjectName', 'createValidator', 'getBaseValidatorConjunction'));
		$validatorResolver->_set('objectManager', $mockObjectManager);
		$validatorResolver->_set('reflectionService', $mockReflectionService);
		$validatorResolver->expects($this->once())->method('getBaseValidatorConjunction')->will($this->returnValue($this->getMock('TYPO3\Flow\Validation\Validator\ValidatorInterface')));

		$validatorResolver->_call('buildBaseValidatorConjunction', $modelClassName, $modelClassName, array('Default'));
	}

	/**
	 * @test
	 */
	public function buildBaseValidatorConjunctionSkipsPropertiesAnnotatedWithIgnoreValidation() {
		$modelClassName = 'Model' . md5(uniqid(mt_rand(), TRUE));
		eval('class ' . $modelClassName . '{}');

		$mockReflectionService = $this->getMock('\TYPO3\Flow\Reflection\ReflectionService');
		$mockReflectionService->expects($this->any())->method('getAllImplementationClassNamesForInterface')->will($this->returnValue(array()));
		$mockReflectionService->expects($this->at(0))->method('getClassPropertyNames')->will($this->returnValue(array('entityProperty')));
		$mockReflectionService->expects($this->at(1))->method('getPropertyTagsValues')->with($modelClassName, 'entityProperty')->will($this->returnValue(array('var' => array('ToBeIgnored'))));
		$mockReflectionService->expects($this->at(2))->method('isPropertyAnnotatedWith')->with($modelClassName, 'entityProperty', 'TYPO3\Flow\Annotations\IgnoreValidation')->will($this->returnValue(TRUE));
		$mockObjectManager = $this->getMock('TYPO3\Flow\Object\ObjectManagerInterface');
		$mockObjectManager->expects($this->any())->method('get')->with('TYPO3\Flow\Reflection\ReflectionService')->will($this->returnValue($mockReflectionService));

		$validatorResolver = $this->getAccessibleMock('TYPO3\Flow\Validation\ValidatorResolver', array('resolveValidatorObjectName', 'createValidator', 'getBaseValidatorConjunction'));
		$validatorResolver->_set('reflectionService', $mockReflectionService);
		$validatorResolver->_set('objectManager', $mockObjectManager);
		$validatorResolver->expects($this->never())->method('getBaseValidatorConjunction');

		$validatorResolver->_call('buildBaseValidatorConjunction', $modelClassName, $modelClassName, array('Default'));
	}

	/**
	 * @test
	 */
	public function buildBaseValidatorConjunctionReturnsNullIfNoValidatorBuilt() {
		$mockReflectionService = $this->getMock('TYPO3\Flow\Reflection\ReflectionService');
		$mockReflectionService->expects($this->at(0))->method('getAllImplementationClassNamesForInterface')->with('TYPO3\Flow\Validation\Validator\ValidatorInterface')->will($this->returnValue(array()));
		$mockReflectionService->expects($this->at(1))->method('getAllImplementationClassNamesForInterface')->with('TYPO3\Flow\Validation\Validator\PolyTypeObjectValidatorInterface')->will($this->returnValue(array()));
		$mockObjectManager = $this->getMock('TYPO3\Flow\Object\ObjectManagerInterface', array(), array(), '', FALSE);
		$mockObjectManager->expects($this->any())->method('get')->will($this->returnValue($mockReflectionService));
		$validatorResolver = $this->getAccessibleMock('TYPO3\Flow\Validation\ValidatorResolver', array('dummy'));
		$validatorResolver->_set('objectManager', $mockObjectManager);
		$validatorResolver->_set('reflectionService', $mockReflectionService);

		$this->assertNull($validatorResolver->_call('buildBaseValidatorConjunction', 'NonExistingClassName', 'NonExistingClassName', array('Default')));
	}

	/**
	 * @test
	 */
	public function buildBaseValidatorConjunctionAddsValidatorsDefinedByAnnotationsInTheClassToTheReturnedConjunction() {
		$mockObject = $this->getMock('stdClass');
		$className = get_class($mockObject);

		$propertyTagsValues = array(
			'foo' => array(
				'var' => array('string'),
			),
			'bar' => array(
				'var' => array('integer'),
			),
			'baz' => array(
				'var' => array('array<TYPO3\TestPackage\Quux>')
			)
		);
		$validateAnnotations = array(
			'foo' => array(
				new \TYPO3\Flow\Annotations\Validate(array(
					'type' => 'Foo',
					'options' => array('bar' => 'baz'),
				)),
				new \TYPO3\Flow\Annotations\Validate(array(
					'type' => 'Bar',
				)),
				new \TYPO3\Flow\Annotations\Validate(array(
					'type' => 'Baz',
				)),
			),
			'bar' => array(
				new \TYPO3\Flow\Annotations\Validate(array(
					'type' => 'TYPO3\TestPackage\Quux',
				)),
			),
		);

		$mockReflectionService = $this->getMock('TYPO3\Flow\Reflection\ReflectionService', array(), array(), '', FALSE);
		$mockReflectionService->expects($this->any())->method('getAllImplementationClassNamesForInterface')->with('TYPO3\Flow\Validation\Validator\PolyTypeObjectValidatorInterface')->will($this->returnValue(array()));
		$mockReflectionService->expects($this->at(0))->method('getClassPropertyNames')->with($className)->will($this->returnValue(array('foo', 'bar', 'baz')));
		$mockReflectionService->expects($this->at(1))->method('getPropertyTagsValues')->with($className, 'foo')->will($this->returnValue($propertyTagsValues['foo']));
		$mockReflectionService->expects($this->at(2))->method('isPropertyAnnotatedWith')->will($this->returnValue(FALSE));
		$mockReflectionService->expects($this->at(3))->method('getPropertyAnnotations')->with(get_class($mockObject), 'foo', 'TYPO3\Flow\Annotations\Validate')->will($this->returnValue($validateAnnotations['foo']));
		$mockReflectionService->expects($this->at(4))->method('getPropertyTagsValues')->with($className, 'bar')->will($this->returnValue($propertyTagsValues['bar']));
		$mockReflectionService->expects($this->at(5))->method('isPropertyAnnotatedWith')->will($this->returnValue(FALSE));
		$mockReflectionService->expects($this->at(6))->method('getPropertyAnnotations')->with(get_class($mockObject), 'bar', 'TYPO3\Flow\Annotations\Validate')->will($this->returnValue($validateAnnotations['bar']));
		$mockReflectionService->expects($this->at(7))->method('getPropertyTagsValues')->with($className, 'baz')->will($this->returnValue($propertyTagsValues['baz']));
		$mockReflectionService->expects($this->at(8))->method('isPropertyAnnotatedWith')->will($this->returnValue(FALSE));
		$mockReflectionService->expects($this->at(9))->method('getPropertyAnnotations')->with(get_class($mockObject), 'baz', 'TYPO3\Flow\Annotations\Validate')->will($this->returnValue(array()));
		$mockObjectManager = $this->getMock('TYPO3\Flow\Object\ObjectManagerInterface');
		$mockObjectManager->expects($this->any())->method('get')->with('TYPO3\Flow\Reflection\ReflectionService')->will($this->returnValue($mockReflectionService));

		$mockObjectValidator = $this->getMock('TYPO3\Flow\Validation\Validator\GenericObjectValidator', array(), array(), '', FALSE);

		$validatorResolver = $this->getAccessibleMock('TYPO3\Flow\Validation\ValidatorResolver', array('resolveValidatorObjectName', 'createValidator'));
		$validatorResolver->_set('reflectionService', $mockReflectionService);
		$validatorResolver->_set('objectManager', $mockObjectManager);

		$validatorResolver->expects($this->at(0))->method('createValidator')->with('Foo', array('bar' => 'baz'))->will($this->returnValue($mockObjectValidator));
		$validatorResolver->expects($this->at(1))->method('createValidator')->with('Bar')->will($this->returnValue($mockObjectValidator));
		$validatorResolver->expects($this->at(2))->method('createValidator')->with('Baz')->will($this->returnValue($mockObjectValidator));
		$validatorResolver->expects($this->at(3))->method('createValidator')->with('TYPO3\TestPackage\Quux')->will($this->returnValue($mockObjectValidator));
		$validatorResolver->expects($this->at(4))->method('createValidator')->with('TYPO3\Flow\Validation\Validator\CollectionValidator', array('elementType' => 'TYPO3\TestPackage\Quux', 'validationGroups' => array('Default')))->will($this->returnValue($mockObjectValidator));

		$validatorResolver->_call('buildBaseValidatorConjunction', $className . 'Default', $className, array('Default'));
		$builtValidators = $validatorResolver->_get('baseValidatorConjunctions');
		$this->assertInstanceOf('TYPO3\Flow\Validation\Validator\ConjunctionValidator', $builtValidators[$className . 'Default']);
	}

	/**
	 * @test
	 */
	public function getValidatorTypeCorrectlyRenamesPhpDataTypes() {
		$mockObjectManager = $this->getMock('TYPO3\Flow\Object\ObjectManagerInterface');
		$validatorResolver = $this->getAccessibleMock('TYPO3\Flow\Validation\ValidatorResolver', array('dummy'));
		$validatorResolver->_set('objectManager', $mockObjectManager);

		$this->assertEquals('Integer', $validatorResolver->_call('getValidatorType', 'integer'));
		$this->assertEquals('Integer', $validatorResolver->_call('getValidatorType', 'int'));
		$this->assertEquals('String', $validatorResolver->_call('getValidatorType', 'string'));
		$this->assertEquals('Array', $validatorResolver->_call('getValidatorType', 'array'));
		$this->assertEquals('Float', $validatorResolver->_call('getValidatorType', 'float'));
		$this->assertEquals('Float', $validatorResolver->_call('getValidatorType', 'double'));
		$this->assertEquals('Boolean', $validatorResolver->_call('getValidatorType', 'boolean'));
		$this->assertEquals('Boolean', $validatorResolver->_call('getValidatorType', 'bool'));
		$this->assertEquals('Number', $validatorResolver->_call('getValidatorType', 'number'));
		$this->assertEquals('Number', $validatorResolver->_call('getValidatorType', 'numeric'));
	}

	/**
	 * @test
	 */
	public function getValidatorTypeRenamesMixedToRaw() {
		$mockObjectManager = $this->getMock('TYPO3\Flow\Object\ObjectManagerInterface');
		$validatorResolver = $this->getAccessibleMock('TYPO3\Flow\Validation\ValidatorResolver', array('dummy'));
		$validatorResolver->_set('objectManager', $mockObjectManager);
		$this->assertEquals('Raw', $validatorResolver->_call('getValidatorType', 'mixed'));
	}

	/**
	 * @test
	 */
	public function resetEmptiesBaseValidatorConjunctions() {
		$validatorResolver = $this->getAccessibleMock('TYPO3\Flow\Validation\ValidatorResolver', array('dummy'));
		$mockConjunctionValidator = $this->getMock('TYPO3\Flow\Validation\Validator\ConjunctionValidator');
		$validatorResolver->_set('baseValidatorConjunctions', array('SomeId##' => $mockConjunctionValidator));

		$validatorResolver->reset();
		$this->assertEmpty($validatorResolver->_get('baseValidatorConjunctions'));
	}
}
