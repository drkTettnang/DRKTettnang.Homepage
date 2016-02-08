<?php
namespace TYPO3\Flow\Tests\Functional\Property\Fixtures;

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
 * A simple valueobject for PropertyMapper test
 *
 * @Flow\ValueObject
 */
class TestValueobject
{
    /**
     * @var string
     */
    protected $name;

    /**
     *
     * @var integer
     */
    protected $age;

    /**
     *
     * @param string $name
     * @param integer $age
     */
    public function __construct($name, $age)
    {
        $this->name = $name;
        $this->age = $age;
    }

    /**
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     *
     * @return integer
     */
    public function getAge()
    {
        return $this->age;
    }
}
