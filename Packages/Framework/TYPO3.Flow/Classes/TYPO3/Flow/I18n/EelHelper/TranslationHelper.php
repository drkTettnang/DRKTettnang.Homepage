<?php
namespace TYPO3\Flow\I18n\EelHelper;

/*                                                                        *
 * This script belongs to the TYPO3 Flow framework.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Eel\ProtectedContextAwareInterface;

/**
 * Translation helpers for Eel contexts
 */
class TranslationHelper implements ProtectedContextAwareInterface {

	const I18N_LABEL_ID_PATTERN = '/^[a-z0-9]+\.(?:[a-z0-9][\.a-z0-9]*)+:[a-z0-9.]+:.+$/i';

	/**
	 * Get the translated value for an id or original label
	 *
	 * If only id is set and contains a translation shorthand string, translate
	 * according to that shorthand
	 *
	 * In all other cases:
	 *
	 * Replace all placeholders with corresponding values if they exist in the
	 * translated label.
	 *
	 * @param string $id Id to use for finding translation (trans-unit id in XLIFF)
	 * @param string $originalLabel The original translation value (the untranslated source string).
	 * @param array $arguments Numerically indexed array of values to be inserted into placeholders
	 * @param string $source Name of file with translations
	 * @param string $package Target package key. If not set, the current package key will be used
	 * @param mixed $quantity A number to find plural form for (float or int), NULL to not use plural forms
	 * @param string $locale An identifier of locale to use (NULL for use the default locale)
	 * @return string Translated label or source label / ID key
	 */
	public function translate($id, $originalLabel = NULL, array $arguments = [], $source = 'Main', $package = NULL, $quantity = NULL, $locale = NULL) {
		if (
			$originalLabel === NULL &&
			$arguments === [] &&
			$source === 'Main' &&
			$package === NULL &&
			$quantity === NULL &&
			$locale === NULL
		) {
			return preg_match(self::I18N_LABEL_ID_PATTERN, $id) === 1 ? $this->translateByShortHandString($id) : $id;
		}

		return $this->translateByExplicitlyPassedOrderedArguments($id, $originalLabel, $arguments, $source, $package, $quantity, $locale);
	}

	/**
	 * Fetches a translation by its id.
	 *
	 * Examples::
	 *
	 *     Translation.translateById('some.title', 'Acme.Site') == 'Acme Inc.'
	 *
	 *     Translation.translateById('str1407180613', 'Acme.Site', 'Ui') == 'Login'
	 *
	 * @param string $id The ID to translate
	 * @param string $packageKey The package key where to find the translation file
	 * @param string $sourceName The source name, defaults to "Main"
	 * @return mixed
	 * @deprecated use the translate method instead.
	 */
	public function translateById($id, $packageKey, $sourceName = 'Main') {
		return $this->translateByExplicitlyPassedOrderedArguments($id, NULL, [], $sourceName, $packageKey);
	}

	/**
	 * Start collection of parameters for translation by id
	 *
	 * @param string $id Id to use for finding translation (trans-unit id in XLIFF)
	 * @return TranslationParameterToken
	 */
	public function id($id) {
		return $this->createTranslationParameterToken($id);
	}

	/**
	 * Start collection of parameters for translation by original label
	 *
	 * @param string $value
	 * @return TranslationParameterToken
	 */
	public function value($value) {
		return $this->createTranslationParameterToken(NULL, $value);
	}

	/**
	 * All methods are considered safe
	 *
	 * @param string $methodName
	 * @return boolean
	 */
	public function allowsCallOfMethod($methodName) {
		return TRUE;
	}


	/**
	 * Get the translated value for an id or original label
	 *
	 * Replace all placeholders with corresponding values if they exist in the
	 * translated label.
	 *
	 * @param string $id Id to use for finding translation (trans-unit id in XLIFF)
	 * @param string $originalLabel The original translation value (the untranslated source string).
	 * @param array $arguments Numerically indexed array of values to be inserted into placeholders
	 * @param string $source Name of file with translations
	 * @param string $package Target package key. If not set, the current package key will be used
	 * @param mixed $quantity A number to find plural form for (float or int), NULL to not use plural forms
	 * @param string $locale An identifier of locale to use (NULL for use the default locale)
	 * @return string Translated label or source label / ID key
	 */
	protected function translateByExplicitlyPassedOrderedArguments($id, $originalLabel = NULL, array $arguments = [], $source = 'Main', $package = NULL, $quantity = NULL, $locale = NULL) {
		$translationParameterToken = $this->createTranslationParameterToken($id);
		$translationParameterToken
			->value($originalLabel)
			->arguments($arguments)
			->source($source)
			->package($package)
			->quantity($quantity);

		if ($locale !== NULL) {
			$translationParameterToken->locale($locale);
		}

		return $translationParameterToken->translate();
	}

	/**
	 * Translate by shorthand string
	 *
	 * @param string $shortHandString (PackageKey:Source:trans-unit-id)
	 * @return string Translated label or source label / ID key
	 * @throws \InvalidArgumentException
	 */
	protected function translateByShortHandString($shortHandString) {
		$shortHandStringParts = explode(':', $shortHandString);
		if (count($shortHandStringParts) === 3) {
			list($package, $source, $id) = $shortHandStringParts;
			return $this->createTranslationParameterToken($id)
				->package($package)
				->source(str_replace('.', '/', $source))
				->translate();
		}

		throw new \InvalidArgumentException(sprintf('The translation shorthand string "%s" has the wrong format', $shortHandString), 1436865829);
	}

	/**
	 * Create and return a TranslationParameterToken.
	 *
	 * @param string $id
	 * @param string $originalLabel
	 * @return TranslationParameterToken
	 */
	protected function createTranslationParameterToken($id = NULL, $originalLabel = NULL) {
		return new TranslationParameterToken($id, $originalLabel);
	}

}
