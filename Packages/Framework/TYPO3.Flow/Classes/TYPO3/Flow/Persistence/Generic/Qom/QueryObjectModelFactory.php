<?php
namespace TYPO3\Flow\Persistence\Generic\Qom;

/*
 * This file is part of the TYPO3.Flow package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */


/**
 * The Query Object Model Factory
 *
 * @api
 */
class QueryObjectModelFactory
{
    /**
     * @var \TYPO3\Flow\Object\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Injects the object factory
     *
     * @param \TYPO3\Flow\Object\ObjectManagerInterface $objectManager
     * @return void
     */
    public function injectObjectManager(\TYPO3\Flow\Object\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Performs a logical conjunction of two other constraints.
     *
     * @param \TYPO3\Flow\Persistence\Generic\Qom\Constraint $constraint1 the first constraint; non-null
     * @param \TYPO3\Flow\Persistence\Generic\Qom\Constraint $constraint2 the second constraint; non-null
     * @return \TYPO3\Flow\Persistence\Generic\Qom\LogicalAnd the And constraint; non-null
     * @api
     */
    public function _and(\TYPO3\Flow\Persistence\Generic\Qom\Constraint $constraint1, \TYPO3\Flow\Persistence\Generic\Qom\Constraint $constraint2)
    {
        return new \TYPO3\Flow\Persistence\Generic\Qom\LogicalAnd($constraint1, $constraint2);
    }

    /**
     * Performs a logical disjunction of two other constraints.
     *
     * @param \TYPO3\Flow\Persistence\Generic\Qom\Constraint $constraint1 the first constraint; non-null
     * @param \TYPO3\Flow\Persistence\Generic\Qom\Constraint $constraint2 the second constraint; non-null
     * @return \TYPO3\Flow\Persistence\Generic\Qom\LogicalOr the Or constraint; non-null
     * @api
     */
    public function _or(\TYPO3\Flow\Persistence\Generic\Qom\Constraint $constraint1, \TYPO3\Flow\Persistence\Generic\Qom\Constraint $constraint2)
    {
        return new \TYPO3\Flow\Persistence\Generic\Qom\LogicalOr($constraint1, $constraint2);
    }

    /**
     * Performs a logical negation of another constraint.
     *
     * @param \TYPO3\Flow\Persistence\Generic\Qom\Constraint $constraint the constraint to be negated; non-null
     * @return \TYPO3\Flow\Persistence\Generic\Qom\LogicalNot the Not constraint; non-null
     * @api
     */
    public function not(\TYPO3\Flow\Persistence\Generic\Qom\Constraint $constraint)
    {
        return new \TYPO3\Flow\Persistence\Generic\Qom\LogicalNot($constraint);
    }

    /**
     * Filters tuples based on the outcome of a binary operation.
     *
     * @param \TYPO3\Flow\Persistence\Generic\Qom\DynamicOperand $operand1 the first operand; non-null
     * @param string $operator the operator; one of QueryObjectModelConstants.JCR_OPERATOR_*
     * @param mixed $operand2 the second operand; non-null
     * @return \TYPO3\Flow\Persistence\Generic\Qom\Comparison the constraint; non-null
     * @api
     */
    public function comparison(\TYPO3\Flow\Persistence\Generic\Qom\DynamicOperand $operand1, $operator, $operand2 = null)
    {
        return new \TYPO3\Flow\Persistence\Generic\Qom\Comparison($operand1, $operator, $operand2);
    }

    /**
     * Evaluates to the value (or values, if multi-valued) of a property in the specified or default selector.
     *
     * @param string $propertyName the property name; non-null
     * @param string $selectorName the selector name; non-null
     * @return \TYPO3\Flow\Persistence\Generic\Qom\PropertyValue the operand; non-null
     * @api
     */
    public function propertyValue($propertyName, $selectorName = '')
    {
        return new \TYPO3\Flow\Persistence\Generic\Qom\PropertyValue($propertyName, $selectorName);
    }

    /**
     * Evaluates to the lower-case string value (or values, if multi-valued) of an operand.
     *
     * @param \TYPO3\Flow\Persistence\Generic\Qom\DynamicOperand $operand the operand whose value is converted to a lower-case string; non-null
     * @return \TYPO3\Flow\Persistence\Generic\Qom\LowerCase the operand; non-null
     * @api
     */
    public function lowerCase(\TYPO3\Flow\Persistence\Generic\Qom\DynamicOperand $operand)
    {
        return new \TYPO3\Flow\Persistence\Generic\Qom\LowerCase($operand);
    }

    /**
     * Evaluates to the upper-case string value (or values, if multi-valued) of an operand.
     *
     * @param \TYPO3\Flow\Persistence\Generic\Qom\DynamicOperand $operand the operand whose value is converted to a upper-case string; non-null
     * @return \TYPO3\Flow\Persistence\Generic\Qom\UpperCase the operand; non-null
     * @api
     */
    public function upperCase(\TYPO3\Flow\Persistence\Generic\Qom\DynamicOperand $operand)
    {
        return new \TYPO3\Flow\Persistence\Generic\Qom\UpperCase($operand);
    }
}
