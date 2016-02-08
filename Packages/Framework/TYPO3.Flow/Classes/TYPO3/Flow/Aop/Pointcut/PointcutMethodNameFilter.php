<?php
namespace TYPO3\Flow\Aop\Pointcut;

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
 * A little filter which filters for method names
 *
 * @Flow\Proxy(false)
 */
class PointcutMethodNameFilter implements \TYPO3\Flow\Aop\Pointcut\PointcutFilterInterface
{
    const PATTERN_MATCHVISIBILITYMODIFIER = '/^(|public|protected)$/';

    /**
     * @var \TYPO3\Flow\Reflection\ReflectionService
     */
    protected $reflectionService;

    /**
     * @var string The method name filter expression
     */
    protected $methodNameFilterExpression;

    /**
     * @var string The method visibility
     */
    protected $methodVisibility = null;

    /**
     * @var \TYPO3\Flow\Log\SystemLoggerInterface
     */
    protected $systemLogger;

    /**
     * @var array Array with constraints for method arguments
     */
    protected $methodArgumentConstraints = array();

    /**
     * Constructor - initializes the filter with the name filter pattern
     *
     * @param string $methodNameFilterExpression A regular expression which filters method names
     * @param string $methodVisibility The method visibility modifier (public, protected or private). Specifiy NULL if you don't care.
     * @param array $methodArgumentConstraints array of method constraints
     * @throws \TYPO3\Flow\Aop\Exception\InvalidPointcutExpressionException
     */
    public function __construct($methodNameFilterExpression, $methodVisibility = null, array $methodArgumentConstraints = array())
    {
        $this->methodNameFilterExpression = $methodNameFilterExpression;
        if (preg_match(self::PATTERN_MATCHVISIBILITYMODIFIER, $methodVisibility) !== 1) {
            throw new \TYPO3\Flow\Aop\Exception\InvalidPointcutExpressionException('Invalid method visibility modifier "' . $methodVisibility . '".', 1172494794);
        }
        $this->methodVisibility = $methodVisibility;
        $this->methodArgumentConstraints = $methodArgumentConstraints;
    }

    /**
     * Injects the reflection service
     *
     * @param \TYPO3\Flow\Reflection\ReflectionService $reflectionService The reflection service
     * @return void
     */
    public function injectReflectionService(\TYPO3\Flow\Reflection\ReflectionService $reflectionService)
    {
        $this->reflectionService = $reflectionService;
    }

    /**
     * @param \TYPO3\Flow\Log\SystemLoggerInterface $systemLogger
     * @return void
     */
    public function injectSystemLogger(\TYPO3\Flow\Log\SystemLoggerInterface $systemLogger)
    {
        $this->systemLogger = $systemLogger;
    }

    /**
     * Checks if the specified method matches against the method name
     * expression.
     *
     * Returns TRUE if method name, visibility and arguments constraints match and the target
     * method is not final.
     *
     * @param string $className Ignored in this pointcut filter
     * @param string $methodName Name of the method to match against
     * @param string $methodDeclaringClassName Name of the class the method was originally declared in
     * @param mixed $pointcutQueryIdentifier Some identifier for this query - must at least differ from a previous identifier. Used for circular reference detection.
     * @return boolean TRUE if the class matches, otherwise FALSE
     * @throws \TYPO3\Flow\Aop\Exception
     */
    public function matches($className, $methodName, $methodDeclaringClassName, $pointcutQueryIdentifier)
    {
        $matchResult = preg_match('/^' . $this->methodNameFilterExpression . '$/', $methodName);

        if ($matchResult === false) {
            throw new \TYPO3\Flow\Aop\Exception('Error in regular expression', 1168876915);
        } elseif ($matchResult !== 1) {
            return false;
        }

        switch ($this->methodVisibility) {
            case 'public':
                if (!($methodDeclaringClassName !== null && $this->reflectionService->isMethodPublic($methodDeclaringClassName, $methodName))) {
                    return false;
                }
                break;
            case 'protected':
                if (!($methodDeclaringClassName !== null && $this->reflectionService->isMethodProtected($methodDeclaringClassName, $methodName))) {
                    return false;
                }
                break;
        }

        if ($methodDeclaringClassName !== null && $this->reflectionService->isMethodFinal($methodDeclaringClassName, $methodName)) {
            return false;
        }

        $methodArguments = ($methodDeclaringClassName === null ? array() : $this->reflectionService->getMethodParameters($methodDeclaringClassName, $methodName));
        foreach (array_keys($this->methodArgumentConstraints) as $argumentName) {
            $objectAccess = explode('.', $argumentName, 2);
            $argumentName = $objectAccess[0];
            if (!array_key_exists($argumentName, $methodArguments)) {
                $this->systemLogger->log('The argument "' . $argumentName . '" declared in pointcut does not exist in method ' . $methodDeclaringClassName . '->' . $methodName, LOG_NOTICE);
                return false;
            }
        }
        return true;
    }

    /**
     * Returns TRUE if this filter holds runtime evaluations for a previously matched pointcut
     *
     * @return boolean TRUE if this filter has runtime evaluations
     */
    public function hasRuntimeEvaluationsDefinition()
    {
        return (count($this->methodArgumentConstraints) > 0);
    }

    /**
     * Returns runtime evaluations for a previously matched pointcut
     *
     * @return array Runtime evaluations
     */
    public function getRuntimeEvaluationsDefinition()
    {
        return array(
            'methodArgumentConstraints' => $this->methodArgumentConstraints
        );
    }

    /**
     * Returns the method name filter expression
     *
     * @return string
     */
    public function getMethodNameFilterExpression()
    {
        return $this->methodNameFilterExpression;
    }

    /**
     * Returns the method visibility
     *
     * @return string
     */
    public function getMethodVisibility()
    {
        return $this->methodVisibility;
    }

    /**
     * Returns the method argument constraints
     *
     * @return array
     */
    public function getMethodArgumentConstraints()
    {
        return $this->methodArgumentConstraints;
    }

    /**
     * This method is used to optimize the matching process.
     *
     * @param \TYPO3\Flow\Aop\Builder\ClassNameIndex $classNameIndex
     * @return \TYPO3\Flow\Aop\Builder\ClassNameIndex
     */
    public function reduceTargetClassNames(\TYPO3\Flow\Aop\Builder\ClassNameIndex $classNameIndex)
    {
        return $classNameIndex;
    }
}
