<?php
namespace TYPO3\Flow\Persistence\Doctrine\Mapping;

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
 * A factory for Doctrine to create our ClassMetadata instances, aware of
 * the object manager.
 *
 */
class ClassMetadataFactory extends \Doctrine\ORM\Mapping\ClassMetadataFactory
{
    /**
     * Creates a new ClassMetadata instance for the given class name.
     *
     * @param string $className
     * @return \TYPO3\Flow\Persistence\Doctrine\Mapping\ClassMetadata
     */
    protected function newClassMetadataInstance($className)
    {
        return new \TYPO3\Flow\Persistence\Doctrine\Mapping\ClassMetadata($className);
    }
}
