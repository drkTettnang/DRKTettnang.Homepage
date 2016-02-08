<?php
namespace TYPO3\Flow\Fixtures;

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
 * A dummy class with setters for testing data mapping
 *
 */
class ClassWithSettersAndConstructor
{
    /**
     * @var mixed
     */
    protected $property1;

    /**
     * @var mixed
     */
    protected $property2;

    public function __construct($property1)
    {
        $this->property1 = $property1;
    }

    public function getProperty1()
    {
        return $this->property1;
    }

    public function getProperty2()
    {
        return $this->property2;
    }

    public function setProperty2($property2)
    {
        $this->property2 = $property2;
    }
}
