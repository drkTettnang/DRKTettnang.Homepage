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
 * Performs a logical negation of another constraint.
 *
 * To satisfy the Not constraint, the tuple must not satisfy $constraint.
 *
 * @api
 */
class LogicalNot extends \TYPO3\Flow\Persistence\Generic\Qom\Constraint
{
    /**
     * @var \TYPO3\Flow\Persistence\Generic\Qom\Constraint
     */
    protected $constraint;

    /**
     *
     * @param \TYPO3\Flow\Persistence\Generic\Qom\Constraint $constraint
     */
    public function __construct(\TYPO3\Flow\Persistence\Generic\Qom\Constraint $constraint)
    {
        $this->constraint = $constraint;
    }

    /**
     * Gets the constraint negated by this Not constraint.
     *
     * @return \TYPO3\Flow\Persistence\Generic\Qom\Constraint the constraint; non-null
     * @api
     */
    public function getConstraint()
    {
        return $this->constraint;
    }
}
