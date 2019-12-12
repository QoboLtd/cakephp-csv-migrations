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

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use CsvMigrations\FieldHandlers\CsvField;
use CsvMigrations\Utility\Validate\Utility;
use InvalidArgumentException;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;
use Qobo\Utils\ModuleConfig\Parser\Parser;

class ViewsCheck extends AbstractCheck
{
    /**
     * Execute a check
     *
     * @param string $module Module name
     * @param array $options Check options
     * @return int Number of encountered errors
     */
    public function run(string $module, array $options = []): int
    {
        $views = Configure::read('CsvMigrations.actions');
        $options = $this->applyOptionDefaults($options);

        $viewCounter = 0;
        foreach ($views as $view) {
            $path = '';
            $mc = $this->getModuleConfig($module, $view, $options);

            try {
                $path = $mc->find();
            } catch (InvalidArgumentException $e) {
                // It's OK for view files to be missing.
                // For example, Files and Users modules.
                $this->warnings[] = sprintf('%s module [%s] view file is missing', $module, $view);
                continue;
            }

            /**
             * If the view file does exist, it has to be parseable.
             */
            $viewCounter++;
            $seenFields = $fields = [];
            try {
                $config = $mc->parse();
                $fields = property_exists($config, 'items') ? $config->items : [];
            } catch (InvalidArgumentException $e) {
                $this->errors = array_merge($this->errors, $mc->getErrors());
                $this->warnings = array_merge($this->warnings, $mc->getWarnings());

                continue;
            }

            if (empty($fields)) {
                $this->warnings[] = sprintf('%s module [%s] view file is empty', $module, $view);
                continue;
            }

            foreach ($fields as $field) {
                $field = array_map('trim', $field);

                // Get rid of the first column, which is the panel name
                if (count($field) > 1) {
                    array_shift($field);
                }

                foreach ($field as $column) {
                    if ($column === '') {
                        continue;
                    }

                    // embedded field detection
                    preg_match(CsvField::PATTERN_TYPE, $column, $matches);

                    // embedded field flag
                    $isEmbedded = ! empty($matches[1]) && 'EMBEDDED' === $matches[1];

                    // association field flag
                    $isAssociation = ! empty($matches[1]) && 'ASSOCIATION' === $matches[1];

                    // normal field
                    if (! $isAssociation && ! $isEmbedded && ! Utility::isValidModuleField($module, $column)) {
                        $this->errors[] = sprintf(
                            '%s module [%s] view references unknown field "%s"',
                            $module,
                            $view,
                            $column
                        );

                        continue;
                    }

                    // Check for field duplicates
                    if (in_array($view, $options['duplicateCheck'])) {
                        if (in_array($column, $seenFields)) {
                            $this->errors[] = $module . " module [$view] specifies field '" . $column . "' more than once";
                            continue;
                        }
                    }
                    $seenFields[] = $column;

                    if ($isEmbedded) {
                        $this->validateEmbedded($matches[2], $module, $view);
                    }

                    if ($isAssociation) {
                        $this->validateAssociation($matches[2], $module, $view);
                    }
                }
            }
        }

        // Warn if the module is missing standard views
        if ($viewCounter < count($views)) {
            $this->warnings[] = sprintf('%s module has only %d views.', $module, $viewCounter);
        }

        return count($this->errors);
    }

    /**
     * Validates values enclosed in EMBEDDED
     *
     * @param string $embeddedValue Value enclosed by EMBEDDED(...)
     * @param string $module Module's name
     * @param string $view View's name
     * @return void
     */
    protected function validateEmbedded(string $embeddedValue, string $module, string $view): void
    {
        // extract embedded module and field
        list($embeddedModule, $embeddedModuleField) = false !== strpos($embeddedValue, '.') ?
            explode('.', $embeddedValue) :
            [null, $embeddedValue];

        if (empty($embeddedModule)) {
            $this->errors[] = sprintf(
                '%s module [%s] view reference EMBEDDED column without a module',
                $module,
                $view
            );
        }

        if (! empty($embeddedModule) && ! Utility::isValidModule($embeddedModule)) {
            $this->errors[] = sprintf(
                '%s module [%s] view reference EMBEDDED column with unknown module "%s"',
                $module,
                $view,
                $embeddedModule
            );
        }

        if (empty($embeddedModuleField)) {
            $this->errors[] = sprintf(
                '%s module [%s] view reference EMBEDDED column without a module field',
                $module,
                $view
            );
        }

        if (! empty($embeddedModuleField) && ! Utility::isValidModuleField($module, $embeddedModuleField)) {
            $this->errors[] = sprintf(
                '%s module [%s] view reference EMBEDDED column with unknown field "%s" of module "%s"',
                $module,
                $view,
                $embeddedModuleField,
                $embeddedModule
            );
        }
    }

    /**
     * Validates values enclosed in ASSOCIATION
     *
     * @param string $associationValue Value enclosed by ASSOCIATION(...)
     * @param string $module Module's name
     * @param string $view View's name
     * @return void
     */
    protected function validateAssociation(string $associationValue, string $module, string $view): void
    {
        $table = TableRegistry::getTableLocator()->get($module);
        if (!$table->hasAssociation($associationValue)) {
            $this->errors[] = sprintf(
                '%s module [%s] view reference ASSOCIATION column with unknown association "%s"',
                $module,
                $view,
                $associationValue
            );
        }
    }

    /**
     * Creates a custom instance of `ModuleConfig` with a parser, schema and
     * extra validation.
     *
     * @param string $module Module.
     * @param string $view View.
     * @param string[] $options Options.
     * @return ModuleConfig Module Config.
     */
    protected function getModuleConfig(string $module, string $view, array $options = []): ModuleConfig
    {
        $mc = new ModuleConfig(ConfigType::VIEW(), $module, $view, ['cacheSkip' => true]);

        $schema = $mc->createSchema(['lint' => true]);
        $schema->setCallback(function (array $schema) use ($module) {
            //Add fields from migration file
            $schema = $this->addFieldsToSchema($schema, $module);
            //Add relation fields
            $schema = $this->addRelationToSchema($schema, $module);

            return $schema;
        });

        $mc->setParser(new Parser($schema, ['lint' => true, 'validate' => true]));

        return $mc;
    }

    /**
     * Applies default values to the options array.
     *
     * Options:
     *  - duplicateCheck: array of view files where duplicate fields check is enabled.
     *
     * @param mixed[] $options Options
     * @return mixed[] Options with applied defaults
     */
    protected function applyOptionDefaults(array $options): array
    {
        $defaults = [
            'duplicateCheck' => [
                // By default only the index view performs unique field check
                'index',
            ],
        ];

        return $options + $defaults;
    }
}
