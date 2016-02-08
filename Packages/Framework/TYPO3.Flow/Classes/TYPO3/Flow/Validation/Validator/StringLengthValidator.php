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
 * Validator for string length.
 *
 * @api
 */
class StringLengthValidator extends AbstractValidator
{
    /**
     * @var array
     */
    protected $supportedOptions = array(
        'minimum' => array(0, 'Minimum length for a valid string', 'integer'),
        'maximum' => array(PHP_INT_MAX, 'Maximum length for a valid string', 'integer')
    );

    /**
     * Checks if the given value is a valid string (or can be cast to a string
     * if an object is given) and its length is between minimum and maximum
     * specified in the validation options.
     *
     * @param mixed $value The value that should be validated
     * @return void
     * @throws \TYPO3\Flow\Validation\Exception\InvalidValidationOptionsException
     * @api
     */
    protected function isValid($value)
    {
        if ($this->options['maximum'] < $this->options['minimum']) {
            throw new \TYPO3\Flow\Validation\Exception\InvalidValidationOptionsException('The \'maximum\' is less than the \'minimum\' in the StringLengthValidator.', 1238107096);
        }

        if (is_object($value)) {
            if (!method_exists($value, '__toString')) {
                $this->addError('The given object could not be converted to a string.', 1238110957);
                return;
            }
        } elseif (!is_string($value)) {
            $this->addError('The given value was not a valid string.', 1269883975);
            return;
        }

        $stringLength = \TYPO3\Flow\Utility\Unicode\Functions::strlen($value);
        $isValid = true;
        if ($stringLength < $this->options['minimum']) {
            $isValid = false;
        }
        if ($stringLength > $this->options['maximum']) {
            $isValid = false;
        }

        if ($isValid === false) {
            if ($this->options['minimum'] > 0 && $this->options['maximum'] < PHP_INT_MAX) {
                $this->addError('The length of this text must be between %1$d and %2$d characters.', 1238108067, array($this->options['minimum'], $this->options['maximum']));
            } elseif ($this->options['minimum'] > 0) {
                $this->addError('This field must contain at least %1$d characters.', 1238108068, array($this->options['minimum']));
            } else {
                $this->addError('This text may not exceed %1$d characters.', 1238108069, array($this->options['maximum']));
            }
        }
    }
}
