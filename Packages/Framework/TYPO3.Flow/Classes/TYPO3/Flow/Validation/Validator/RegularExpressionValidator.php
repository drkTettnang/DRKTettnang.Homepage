<?php
namespace TYPO3\Flow\Validation\Validator;

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
 * Validator based on regular expressions.
 *
 * @api
 */
class RegularExpressionValidator extends AbstractValidator
{
    /**
     * @var array
     */
    protected $supportedOptions = array(
        'regularExpression' => array('', 'The regular expression to use for validation, used as given', 'string', true)
    );

    /**
     * Checks if the given value matches the specified regular expression.
     *
     * @param mixed $value The value that should be validated
     * @return void
     * @throws \TYPO3\Flow\Validation\Exception\InvalidValidationOptionsException
     * @api
     */
    protected function isValid($value)
    {
        $result = preg_match($this->options['regularExpression'], $value);
        if ($result === 0) {
            $this->addError('The given subject did not match the pattern. Got: %1$s', 1221565130, array($value));
        }
        if ($result === false) {
            throw new \TYPO3\Flow\Validation\Exception\InvalidValidationOptionsException('regularExpression "' . $this->options['regularExpression'] . '" in RegularExpressionValidator contained an error.', 1298273089);
        }
    }
}
