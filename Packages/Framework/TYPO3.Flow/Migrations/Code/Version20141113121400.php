<?php
namespace TYPO3\Flow\Core\Migrations;

/*                                                                        *
 * This script belongs to the TYPO3 Flow framework.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Configuration\ConfigurationManager;

/**
 * Adjust "Policy.yaml" to new syntax
 */
class Version20141113121400 extends AbstractMigration {

	/**
	 * @return void
	 */
	public function up() {
		$this->processConfiguration(ConfigurationManager::CONFIGURATION_TYPE_POLICY,
			function (array &$configuration) {
				$this->processRoles($configuration);
				$this->processResources($configuration);
				$this->processAcls($configuration);

				// remove empty arrays as they would reset previously defined values
				if (isset($configuration['privilegeTargets']) && $configuration['privilegeTargets'] === array()) {
					unset($configuration['privilegeTargets']);
				}
				if (isset($configuration['roles']) && $configuration['roles'] === array()) {
					unset($configuration['roles']);
				}

				// make sure "privilegeTargets" are defined before "roles" (only for better readability)
				ksort($configuration);
			},
			TRUE
		);
	}

	/**
	 * Replaces local role identifiers ("SomeRole") by their global representation ("Current.Package:SomeRole")
	 * and sets "parentRoles"
	 *
	 * @param array $configuration
	 * @return void
	 */
	public function processRoles(array &$configuration) {
		if (!isset($configuration['roles'])) {
			return;
		}
		$newRolesConfiguration = array();
		foreach ($configuration['roles'] as $roleIdentifier => $roleConfiguration) {
			$roleIdentifier = $this->expandRoleIdentifier($roleIdentifier);
			$newRolesConfiguration[$roleIdentifier] = array();
			if (!is_array($roleConfiguration) || $roleConfiguration === array()) {
				continue;
			}
			if (isset($roleConfiguration['privileges'])) {
				$newRolesConfiguration[$roleIdentifier] = $roleConfiguration;
				continue;
			}
			$newRolesConfiguration[$roleIdentifier]['parentRoles'] = array();
			foreach ($roleConfiguration as $parentRoleIdentifier) {
				if (strpos($parentRoleIdentifier, ':') === FALSE) {
					$parentRoleIdentifier = $this->expandRoleIdentifier($parentRoleIdentifier);
				}
				$newRolesConfiguration[$roleIdentifier]['parentRoles'][] = $parentRoleIdentifier;
			}
		}
		$configuration['roles'] = $newRolesConfiguration;
	}

	/**
	 * Replaces the "resource" configuration by the new "privilegeTargets" syntax
	 *
	 * @param array $configuration
	 * @return void
	 */
	public function processResources(array &$configuration) {
		if (!isset($configuration['resources']) || !is_array($configuration['resources'])) {
			return;
		}
		$newPrivilegeTargetConfiguration = array();
		foreach ($configuration['resources'] as $resourceType => $resourceConfiguration) {
			switch ($resourceType) {
				case 'methods':
					$privilegeClassName = 'TYPO3\\Flow\\Security\\Authorization\\Privilege\\Method\MethodPrivilege';
					if (!isset($newPrivilegeTargetConfiguration[$privilegeClassName])) {
						$newPrivilegeTargetConfiguration[$privilegeClassName] = array();
					}
					foreach ($resourceConfiguration as $resourceName => $resourceMatcher) {
						$newPrivilegeTargetConfiguration[$privilegeClassName][$resourceName] = array(
							'matcher' => $resourceMatcher,
						);
					}
					break;
				case 'entities':
					$privilegeClassName = 'TYPO3\\Flow\\Security\\Authorization\\Privilege\\Entity\\Doctrine\\EntityPrivilege';
					foreach ($resourceConfiguration as $entityType => $entityConfiguration) {
						if (!isset($newPrivilegeTargetConfiguration[$privilegeClassName])) {
							$newPrivilegeTargetConfiguration[$privilegeClassName] = array();
						}
						foreach ($entityConfiguration as $resourceName => $resourceMatcher) {
							$newPrivilegeTargetConfiguration[$privilegeClassName][$resourceName] = array(
								'matcher' => $this->convertEntityResourceMatcher($entityType, $resourceMatcher)
							);
						}
					}
					break;
				default:
					$this->showWarning('Resource type "' . $resourceType . '" is not supported...');
			}

		}
		unset($configuration['resources']);
		$configuration['privilegeTargets'] = $newPrivilegeTargetConfiguration;
	}

	/**
	 * Converts the given $resourceMatcher string to the new syntax
	 *
	 * @param string $entityType
	 * @param string $resourceMatcher
	 * @return string
	 */
	protected function convertEntityResourceMatcher($entityType, $resourceMatcher) {
		$newMatcher = 'isType("' . $entityType . '")';
		if (trim($resourceMatcher) !== 'ANY') {
			$newMatcher .= ' && ' . preg_replace(array('/\bcurrent\./', '/\bthis\.([^\s]+)/'), array('context.', 'property("$1")'), $resourceMatcher);
		}
		return $newMatcher;
	}

	/**
	 * Removes the "acls" configuration and adds privileges to related roles
	 *
	 * @param array $configuration
	 * @return void
	 */
	public function processAcls(array &$configuration) {
		if (!isset($configuration['acls'])) {
			return;
		}
		$newRolesConfiguration = isset($configuration['roles']) ? $configuration['roles'] : array();
		foreach ($configuration['acls'] as $roleIdentifier => $aclConfiguration) {
			$roleIdentifier = $this->expandRoleIdentifier($roleIdentifier);
			if (!isset($newRolesConfiguration[$roleIdentifier])) {
				$newRolesConfiguration[$roleIdentifier] = array();
			}
			if (!isset($newRolesConfiguration[$roleIdentifier]['privileges'])) {
				$newRolesConfiguration[$roleIdentifier]['privileges'] = array();
			}
			foreach ($aclConfiguration as $resourceType => $permissions) {
				if ($resourceType !== 'methods' && $resourceType !== 'entities') {
					$this->showWarning('Resource type "' . $resourceType . '" is not supported...');
					continue;
				}
				foreach ($permissions as $resourceName => $permission) {
					$newRolesConfiguration[$roleIdentifier]['privileges'][] = array('privilegeTarget' => $resourceName, 'permission' => $permission);
				}
			}
		}
		unset($configuration['acls']);
		$configuration['roles'] = $newRolesConfiguration;
	}

	/**
	 * Convert a "relative" role identifier to one that includes the package key
	 *
	 * @param string $roleIdentifier
	 * @return string
	 */
	protected function expandRoleIdentifier($roleIdentifier) {
		if (strpos($roleIdentifier, ':') !== FALSE) {
			return $roleIdentifier;
		}
		if (in_array($roleIdentifier, array('Everybody', 'Anonymous', 'AuthenticatedUser'))) {
			return 'TYPO3.Flow:' . $roleIdentifier;
		}
		return $this->targetPackageData['packageKey'] . ':' . $roleIdentifier;
	}

}
