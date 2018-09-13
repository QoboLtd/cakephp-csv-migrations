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
use CsvMigrations\FieldHandlers\CsvField;
use CsvMigrations\Utility\Validate\Utility;
use Exception;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;

class ViewsCheck extends AbstractCheck
{
    /**
     * Execute a check
     *
     * @param string $module Module name
     * @param array $options Check options
     * @return int Number of encountered errors
     */
    public function run($module, array $options = [])
    {
        $views = Configure::read('CsvMigrations.actions');

        $viewCounter = 0;
        foreach ($views as $view) {
            $path = '';
            try {
                $mc = new ModuleConfig(ConfigType::VIEW(), $module, $view, ['cacheSkip' => true]);
                $path = $mc->find();
            } catch (Exception $e) {
                // It's OK for view files to be missing.
                // For example, Files and Users modules.
            }

            if ('' === trim($path) || ! file_exists($path)) {
                $this->warnings[] = sprintf('%s module [%s] view file is missing', $module, $view);

                continue;
            }

            /**
             * If the view file does exist, it has to be parseable.
             */
            $viewCounter++;
            $fields = [];
            try {
                $fields = $mc->parse()->items;
            } catch (Exception $e) {
                // We need errors and warnings irrelevant of the exception
            }
            $this->errors = array_merge($this->errors, $mc->getErrors());
            $this->warnings = array_merge($this->warnings, $mc->getWarnings());

            if (empty($fields)) {
                continue;
            }

            foreach ($fields as $field) {
                if (count($field) > 13) {
                    $this->errors[] = $module . " module [$view] view has more than 12 columns";
                    continue;
                }

                if (count($field) === 1) {
                    // index view
                    if ($field[0] && !Utility::isValidModuleField($module, $field[0])) {
                        $this->errors[] = $module . " module [$view] view references unknown field '" . $field[0] . "'";
                    }

                    continue;
                }

                // Get rid of the first column, which is the panel name
                array_shift($field);
                foreach ($field as $column) {
                    // skip empty columns
                    if ('' === trim($column)) {
                        continue;
                    }

                    // embedded field detection
                    preg_match(CsvField::PATTERN_TYPE, $column, $matches);
                    // embedded field flag
                    $isEmbedded = ! empty($matches[1]) && 'EMBEDDED' === $matches[1];

                    // normal field
                    if (! $isEmbedded && ! Utility::isValidModuleField($module, $column)) {
                        $this->errors[] = sprintf(
                            '%s module [%s] view references unknown field "%s"',
                            $module,
                            $view,
                            $column
                        );

                        continue;
                    }

                    // skip for non-embedded field
                    if (! $isEmbedded) {
                        continue;
                    }

                    // extract embedded module and field
                    list($embeddedModule, $embeddedModuleField) = false !== strpos($matches[2], '.') ?
                        explode('.', $matches[2]) :
                        [null, $matches[2]];

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
            }
        }

        // Warn if the module is missing standard views
        if ($viewCounter < count($views)) {
            $this->warnings[] = sprintf('%s module has only %d views.', $module, (int)$viewCounter);
        }

        return count($this->errors);
    }
}
