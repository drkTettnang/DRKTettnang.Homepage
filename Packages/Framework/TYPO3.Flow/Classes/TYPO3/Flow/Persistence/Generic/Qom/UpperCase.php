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
 * Evaluates to the upper-case string value (or values, if multi-valued) of
 * operand.
 *
 * If operand does not evaluate to a string value, its value is first converted
 * to a string.
 *
 * If operand evaluates to null, the UpperCase operand also evaluates to null.
 *
 * @api
 */
class UpperCase
{
    /**
     * @var \TYPO3\Flow\Persistence\Generic\Qom\DynamicOperand
     */
    protected $operand;

    /**
     * Constructs this UpperCase instance
     *
     * @param \TYPO3\Flow\Persistence\Generic\Qom\DynamicOperand $operand
     */
    public function __construct(\TYPO3\Flow\Persistence\Generic\Qom\DynamicOperand $operand)
    {
        $this->operand = $operand;
    }

    /**
     * Gets the operand whose value is converted to a upper-case string.
     *
     * @return \TYPO3\Flow\Persistence\Generic\Qom\DynamicOperand the operand; non-null
     * @api
     */
    public function getOperand()
    {
        return $this->operand;
    }
}
