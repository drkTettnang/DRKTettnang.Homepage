<?php
namespace TYPO3\Flow\Annotations;

/*                                                                        *
 * This script belongs to the TYPO3 Flow framework.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Used to disable autowiring for Dependency Injection on the
 * whole class or on the annotated property only.
 *
 * @Annotation
 * @Target({"METHOD", "CLASS"})
 */
final class Autowiring {

	/**
	 * Whether autowiring is enabled. (Can be given as anonymous argument.)
	 * @var boolean
	 */
	public $enabled = TRUE;

	/**
	 * @param array $values
	 */
	public function __construct(array $values) {
		if (isset($values['enabled'])) {
			$this->enabled = (boolean)$values['enabled'];
		} elseif (isset($values['value'])) {
			$this->enabled = (boolean)$values['value'];
		}
	}

}
