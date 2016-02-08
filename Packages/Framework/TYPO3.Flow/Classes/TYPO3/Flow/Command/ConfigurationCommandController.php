<?php
namespace TYPO3\Flow\Command;

/*
 * This file is part of the TYPO3.Flow package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Symfony\Component\Yaml\Yaml;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Cli\CommandController;
use TYPO3\Flow\Configuration\ConfigurationManager;
use TYPO3\Flow\Configuration\ConfigurationSchemaValidator;
use TYPO3\Flow\Configuration\Exception\SchemaValidationException;
use TYPO3\Flow\Error\Error;
use TYPO3\Flow\Error\Notice;
use TYPO3\Flow\Utility\Arrays;
use TYPO3\Flow\Utility\SchemaGenerator;

/**
 * Configuration command controller for the TYPO3.Flow package
 *
 * @Flow\Scope("singleton")
 */
class ConfigurationCommandController extends CommandController
{
    /**
     * @Flow\Inject
     * @var ConfigurationManager
     */
    protected $configurationManager;

    /**
     * @Flow\Inject(lazy = FALSE)
     * @var ConfigurationSchemaValidator
     */
    protected $configurationSchemaValidator;

    /**
     * @Flow\Inject
     * @var SchemaGenerator
     */
    protected $schemaGenerator;

    /**
     * Show the active configuration settings
     *
     * The command shows the configuration of the current context as it is used by Flow itself.
     * You can specify the configuration type and path if you want to show parts of the configuration.
     *
     * ./flow configuration:show --type Settings --path TYPO3.Flow.persistence
     *
     * @param string $type Configuration type to show
     * @param string $path path to subconfiguration separated by "." like "TYPO3.Flow"
     * @return void
     */
    public function showCommand($type = null, $path = null)
    {
        $availableConfigurationTypes = $this->configurationManager->getAvailableConfigurationTypes();
        if (in_array($type, $availableConfigurationTypes)) {
            $configuration = $this->configurationManager->getConfiguration($type);
            if ($path !== null) {
                $configuration = Arrays::getValueByPath($configuration, $path);
            }
            $typeAndPath = $type . ($path ? ': ' . $path : '');
            if ($configuration === null) {
                $this->outputLine('<b>Configuration "%s" was empty!</b>', array($typeAndPath));
            } else {
                $yaml = Yaml::dump($configuration, 99);
                $this->outputLine('<b>Configuration "%s":</b>', array($typeAndPath));
                $this->outputLine();
                $this->outputLine($yaml . chr(10));
            }
        } else {
            if ($type !== null) {
                $this->outputLine('<b>Configuration type "%s" was not found!</b>', array($type));
            }
            $this->outputLine('<b>Available configuration types:</b>');
            foreach ($availableConfigurationTypes as $availableConfigurationType) {
                $this->outputLine('  ' . $availableConfigurationType);
            }
            $this->outputLine();
            $this->outputLine('Hint: <b>%s configuration:show --type <configurationType></b>', array($this->getFlowInvocationString()));
            $this->outputLine('      shows the configuration of the specified type.');
        }
    }

    /**
     * List registered configuration types
     *
     * @return void
     */
    public function listTypesCommand()
    {
        $this->outputLine('The following configuration types are registered:');
        $this->outputLine();

        foreach ($this->configurationManager->getAvailableConfigurationTypes() as $type) {
            $this->outputFormatted('- %s', array($type));
        }
    }

    /**
     * Validate the given configuration
     *
     * <b>Validate all configuration</b>
     * ./flow configuration:validate
     *
     * <b>Validate configuration at a certain subtype</b>
     * ./flow configuration:validate --type Settings --path TYPO3.Flow.persistence
     *
     * You can retrieve the available configuration types with:
     * ./flow configuration:listtypes
     *
     * @param string $type Configuration type to validate
     * @param string $path path to the subconfiguration separated by "." like "TYPO3.Flow"
     * @param boolean $verbose if TRUE, output more verbose information on the schema files which were used
     * @return void
     */
    public function validateCommand($type = null, $path = null, $verbose = false)
    {
        if ($type === null) {
            $this->outputLine('Validating <b>all</b> configuration');
        } else {
            $this->outputLine('Validating <b>' . $type . '</b> configuration' . ($path !== null ? ' on path <b>' . $path . '</b>' : ''));
        }
        $this->outputLine();

        $validatedSchemaFiles = array();
        try {
            $result = $this->configurationSchemaValidator->validate($type, $path, $validatedSchemaFiles);
        } catch (SchemaValidationException $exception) {
            $this->outputLine('<b>Exception:</b>');
            $this->outputFormatted($exception->getMessage(), array(), 4);
            $this->quit(2);
            return;
        }

        if ($verbose) {
            $this->outputLine('<b>Loaded Schema Files:</b>');
            foreach ($validatedSchemaFiles as $validatedSchemaFile) {
                $this->outputLine('- ' . substr($validatedSchemaFile, strlen(FLOW_PATH_ROOT)));
            }
            $this->outputLine();
            if ($result->hasNotices()) {
                $notices = $result->getFlattenedNotices();
                $this->outputLine('<b>%d notices:</b>', array(count($notices)));
                /** @var Notice $notice */
                foreach ($notices as $path => $pathNotices) {
                    foreach ($pathNotices as $notice) {
                        $this->outputLine(' - %s -> %s', array($path, $notice->render()));
                    }
                }
                $this->outputLine();
            }
        }

        if ($result->hasErrors()) {
            $errors = $result->getFlattenedErrors();
            $this->outputLine('<b>%d errors were found:</b>', array(count($errors)));
            /** @var Error $error */
            foreach ($errors as $path => $pathErrors) {
                foreach ($pathErrors as $error) {
                    $this->outputLine(' - %s -> %s', array($path, $error->render()));
                }
            }
            $this->quit(1);
        } else {
            $this->outputLine('<b>All Valid!</b>');
        }
    }

    /**
     * Generate a schema for the given configuration or YAML file.
     *
     * ./flow configuration:generateschema --type Settings --path TYPO3.Flow.persistence
     *
     * The schema will be output to standard output.
     *
     * @param string $type Configuration type to create a schema for
     * @param string $path path to the subconfiguration separated by "." like "TYPO3.Flow"
     * @param string $yaml YAML file to create a schema for
     * @return void
     */
    public function generateSchemaCommand($type = null, $path = null, $yaml = null)
    {
        $data = null;
        if ($yaml !== null && is_file($yaml) && is_readable($yaml)) {
            $data = Yaml::parse($yaml);
        } elseif ($type !== null) {
            $data = $this->configurationManager->getConfiguration($type);
            if ($path !== null) {
                $data = Arrays::getValueByPath($data, $path);
            }
        }

        if (empty($data)) {
            $this->outputLine('Data was not found or is empty');
            $this->quit(1);
        }

        $this->outputLine(Yaml::dump($this->schemaGenerator->generate($data), 99));
    }
}
