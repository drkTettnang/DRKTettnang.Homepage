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
 * Introduces the given interface or property into any target class matching
 * the given pointcut expression.
 *
 * @Annotation
 * @Target({"CLASS", "PROPERTY"})
 */
final class Introduce {

	/**
	 * The pointcut expression. (Can be given as anonymous argument.)
	 * @var string
	 */
	public $pointcutExpression;

	/**
	 * The interface name to introduce.
	 * @var string
	 */
	public $interfaceName;

	/**
	 * @param array $values
	 * @throws \InvalidArgumentException
	 */
	public function __construct(array $values) {
		if (!isset($values['value']) && !isset($values['pointcutExpression'])) {
			throw new \InvalidArgumentException('An Introduce annotation must specify a pointcut expression.', 1318456624);
		}
		$this->pointcutExpression = isset($values['pointcutExpression']) ? $values['pointcutExpression'] : $values['value'];

		if (isset($values['interfaceName'])) {
			$this->interfaceName = $values['interfaceName'];
		}
	}

}
