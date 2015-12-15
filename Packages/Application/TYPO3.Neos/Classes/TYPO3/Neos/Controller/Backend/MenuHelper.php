<?php
namespace TYPO3\Neos\Controller\Backend;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Neos".            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Mvc\Controller\ControllerContext;
use TYPO3\Flow\Utility\Arrays;

/**
 * A helper class for menu generation in backend controllers / view helpers
 *
 * @Flow\Scope("singleton")
 */
class MenuHelper {

	/**
	 * @var \TYPO3\Neos\Domain\Repository\SiteRepository
	 * @Flow\Inject
	 */
	protected $siteRepository;

	/**
	 * @var \TYPO3\Flow\Security\Authorization\PrivilegeManagerInterface
	 * @Flow\Inject
	 */
	protected $privilegeManager;

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @param array $settings
	 */
	public function injectSettings(array $settings) {
		$this->settings = $settings;
	}

	/**
	 * Build a list of sites
	 *
	 * @param \TYPO3\Flow\Mvc\Controller\ControllerContext $controllerContext
	 * @return array
	 */
	public function buildSiteList(ControllerContext $controllerContext) {
		$requestUri = $controllerContext->getRequest()->getHttpRequest()->getUri();
		$baseUri = $controllerContext->getRequest()->getHttpRequest()->getBaseUri();

		$domainsFound = FALSE;
		$sites = array();
		foreach ($this->siteRepository->findOnline() as $site) {
			$uri = NULL;
			/** @var $site \TYPO3\Neos\Domain\Model\Site */
			if ($site->hasActiveDomains()) {
				$uri = $controllerContext->getUriBuilder()
					->reset()
					->uriFor('index', array(), 'Backend\Backend', 'TYPO3.Neos');
				$uri = sprintf('%s://%s%s%s',
					$requestUri->getScheme(),
					$site->getFirstActiveDomain()->getHostPattern(),
					rtrim($baseUri->getPath(), '/'), // remove trailing slash, $uri has leading slash already
					$uri
				);
				$domainsFound = TRUE;
			}

			$sites[] = array(
				'name' => $site->getName(),
				'nodeName' => $site->getNodeName(),
				'uri' => $uri,
				'active' => parse_url($uri, PHP_URL_HOST) === $requestUri->getHost() ? TRUE : FALSE
			);
		}

		if ($domainsFound === FALSE) {
			$uri = $controllerContext->getUriBuilder()
				->reset()
				->setCreateAbsoluteUri(TRUE)
				->uriFor('index', array(), 'Backend\Backend', 'TYPO3.Neos');
			$sites[0]['uri'] = $uri;
		}

		return $sites;
	}

	/**
	 * @param \TYPO3\Flow\Mvc\Controller\ControllerContext $controllerContext
	 * @return array
	 */
	public function buildModuleList(ControllerContext $controllerContext) {
		$modules = array();
		foreach ($this->settings['modules'] as $module => $moduleConfiguration) {
			if (!$this->isModuleEnabled($module)) {
				continue;
			}
			if (isset($moduleConfiguration['privilegeTarget']) && !$this->privilegeManager->isPrivilegeTargetGranted($moduleConfiguration['privilegeTarget'])) {
				continue;
			}
			$submodules = array();
			if (isset($moduleConfiguration['submodules'])) {
				foreach ($moduleConfiguration['submodules'] as $submodule => $submoduleConfiguration) {
					if (!$this->isModuleEnabled($module . '/' . $submodule)) {
						continue;
					}
					if (isset($submoduleConfiguration['privilegeTarget']) && !$this->privilegeManager->isPrivilegeTargetGranted($submoduleConfiguration['privilegeTarget'])) {
						continue;
					}
					$submodules[] = $this->collectModuleData($controllerContext, $submodule, $submoduleConfiguration, $module . '/' . $submodule);
				}
			}
			$modules[] = array_merge(
				$this->collectModuleData($controllerContext, $module, $moduleConfiguration, $module),
				array('group' => $module, 'submodules' => $submodules)
			);
		}
		return $modules;
	}

	/**
	 * Checks whether a module is enabled or disabled in the configuration
	 *
	 * @param string $modulePath name of the module including parent modules ("mainModule/subModule/subSubModule")
	 * @return boolean TRUE if module is enabled (default), FALSE otherwise
	 */
	public function isModuleEnabled($modulePath) {
		$modulePathSegments = explode('/', $modulePath);
		$moduleConfiguration = Arrays::getValueByPath($this->settings['modules'], implode('.submodules.', $modulePathSegments));
		if (isset($moduleConfiguration['enabled']) && $moduleConfiguration['enabled'] !== TRUE) {
			return FALSE;
		}
		array_pop($modulePathSegments);
		if ($modulePathSegments === []) {
			return TRUE;
		}
		return $this->isModuleEnabled(implode('/', $modulePathSegments));
	}

	/**
	 * @param \TYPO3\Flow\Mvc\Controller\ControllerContext $controllerContext
	 * @param string $module
	 * @param array $moduleConfiguration
	 * @param string $modulePath
	 * @return array
	 */
	protected function collectModuleData(ControllerContext $controllerContext, $module, $moduleConfiguration, $modulePath) {
		$moduleUri = $controllerContext->getUriBuilder()
			->reset()
			->setCreateAbsoluteUri(TRUE)
			->uriFor('index', array('module' => $modulePath), 'Backend\Module', 'TYPO3.Neos');
		return array(
			'module' => $module,
			'modulePath' => $modulePath,
			'uri' => $moduleUri,
			'label' => isset($moduleConfiguration['label']) ? $moduleConfiguration['label'] : '',
			'description' => isset($moduleConfiguration['description']) ? $moduleConfiguration['description'] : '',
			'icon' => isset($moduleConfiguration['icon']) ? $moduleConfiguration['icon'] : '',
			'hideInMenu' => isset($moduleConfiguration['hideInMenu']) ? (boolean)$moduleConfiguration['hideInMenu'] : FALSE
		);
	}
}
