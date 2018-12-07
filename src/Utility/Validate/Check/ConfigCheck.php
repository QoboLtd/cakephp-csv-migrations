<?php
/**
 * Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace CsvMigrations\Utility\Validate\Check;

use CsvMigrations\Utility\Validate\Utility;
use InvalidArgumentException;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;

use Qobo\Utils\ModuleConfig\Parser\Parser;

class ConfigCheck extends AbstractCheck
{
    /**
     * Execute a check
     *
     * @param string $module Module name
     * @param mixed[] $options Check options
     * @return int Number of encountered errors
     */
    public function run(string $module, array $options = []) : int
    {
        $mc = $this->getModuleConfig($module, $options);

        $config = [];
        try {
            $conf = json_encode($mc->parse());
            $config = false === $conf ? [] : json_decode($conf, true);
        } catch (InvalidArgumentException $e) {
            // We need errors and warnings irrelevant of the exception
            $this->errors = array_merge($this->errors, $mc->getErrors());
        }
        $this->warnings = array_merge($this->warnings, $mc->getWarnings());

        if (empty($config)) {
            return count($this->errors);
        }

        // Extra parent validation
        $this->checkParent($module, $options, $config);

        return count($this->errors);
    }

    /**
     * Creates a custom instance of `ModuleConfig` with a parser, schema and
     * extra validation.
     *
     * @param string $module Module.
     * @param string[] $options Options.
     * @return ModuleConfig Module Config.
     */
    protected function getModuleConfig(string $module, array $options = []): ModuleConfig
    {
        $mc = new ModuleConfig(ConfigType::MODULE(), $module, null, ['cacheSkip' => true]);
        /** @var \Qobo\Utils\ModuleConfig\Parser\SchemaInterface&\Cake\Core\InstanceConfigTrait */
        $schema = $mc->createSchema(['lint' => true]);
        $schema->setCallback(function (array $schema) use ($module, $options) {
            // phpstan
            $displayBad = !empty($options['display_field_bad_values'])? (array)$options['display_field_bad_values'] : [];
            $iconBad = !empty($options['icon_bad_values'])? (array)$options['icon_bad_values'] : [];

            $schema = $this->addFieldsToSchema($schema, $module);
            $schema = $this->addVirtualFieldsToSchema($schema, $module);
            $schema = $this->addModulesToSchema($schema, $module);
            $schema = $this->addConversionToSchema($schema);
            $schema = $this->addDisplayFieldValidationToSchema($schema, $displayBad);
            $schema = $this->addIconValidationToSchema($schema, $iconBad);

            return $schema;
        });

        $mc->setParser(new Parser($schema, ['lint' => true]));

        return $mc;
    }

    /**
     * Expand `conversionItem` sceham definition to include all available
     * modules.
     *
     * This is a workaround regarding the issue with schema whereby we can't
     * validate object key names reliably.
     *
     * @param string[] $schema Schema.
     * @return string[] Schema.
     */
    protected function addConversionToSchema(array $schema): array
    {
        if (isset($schema['definitions']['conversionItem'])) {
            $modules = Utility::getModules();
            if (!empty($modules)) {
                $rule = $schema['definitions']['conversionItem']['properties']['modules']['additionalProperties']['anyOf'][0];
                foreach ($modules as $module) {
                    $schema['definitions']['conversionItem']['properties']['modules']['additionalProperties'] = false;
                    $schema['definitions']['conversionItem']['properties']['modules']['properties'][$module] = $rule;
                }
            }
        }

        return $schema;
    }

    /**
     * Validate `display_field` values.
     *
     * @param string[] $schema Schema.
     * @param string[] $values Bad values for display field.
     * @return string[] Schema.
     */
    protected function addDisplayFieldValidationToSchema(array $schema, array $values = []): array
    {
        if (!empty($values)) {
            $schema['definitions']['displayFieldBad']['enum'] = $values;
        }

        return $schema;
    }

    /**
     * Validate `icon` values.
     *
     * @param string[] $schema Schema.
     * @param string[] $values Bad values for icon.
     * @return string[] Schema.
     */
    protected function addIconValidationToSchema(array $schema, array $values = []): array
    {
        if (!empty($values)) {
            $schema['definitions']['iconBad']['enum'] = $values;
        }

        return $schema;
    }

    /**
     * Check parent section of the configuration
     *
     * @param string $module Module name
     * @param mixed[] $options Check options
     * @param mixed[] $config Configuration
     * @return void
     */
    protected function checkParent(string $module, array $options = [], array $config = []) : void
    {
        if (empty($config['parent'])) {
            return;
        }

        if (!empty($config['parent']['redirect'])) {
            //if redirect = parent, we force the user to mention the relation and module
            if (in_array($config['parent']['redirect'], ['parent'])) {
                if (empty($config['parent']['module'])) {
                    $this->errors[] = $module . " config [parent] requires 'module' value when redirect = parent.";
                }

                if (empty($config['parent']['relation'])) {
                    $this->errors[] = $module . " config [parent] requires 'relation' when redirect = parent.";
                }
            }
        }
    }
}
