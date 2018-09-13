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

            // If the view file does exist, it has to be parseable.
            if ($path && file_exists($path)) {
                $viewCounter++;
                $fields = [];
                try {
                    $fields = $mc->parse()->items;
                } catch (Exception $e) {
                    // We need errors and warnings irrelevant of the exception
                }
                $this->errors = array_merge($this->errors, $mc->getErrors());
                $this->warnings = array_merge($this->warnings, $mc->getWarnings());

                // If the view file does exist, it has to be parseable.
                if ($fields) {
                    foreach ($fields as $field) {
                        if (count($field) > 13) {
                            $this->errors[] = $module . " module [$view] view has more than 12 columns";
                        } elseif (count($field) > 1) {
                            // Get rid of the first column, which is the panel name
                            array_shift($field);
                            $isEmbedded = false;
                            foreach ($field as $column) {
                                // embedded field detection
                                preg_match(CsvField::PATTERN_TYPE, $column, $matches);
                                if (! empty($matches[1]) && 'EMBEDDED' === $matches[1]) {
                                    $column = $matches[2];
                                    $isEmbedded = true;
                                }
                                if ($isEmbedded) {
                                    list($embeddedModule, $embeddedModuleField) = explode('.', $column);
                                    if (empty($embeddedModule)) {
                                        $this->errors[] = $module . " module [$view] view reference EMBEDDED column without a module";
                                    } else {
                                        if (!Utility::isValidModule($embeddedModule)) {
                                            $this->errors[] = $module . " module [$view] view reference EMBEDDED column with unknown module '$embeddedModule'";
                                        }
                                    }
                                    if (empty($embeddedModuleField)) {
                                        $this->errors[] = $module . " module [$view] view reference EMBEDDED column without a module field";
                                    } else {
                                        if (!Utility::isValidModuleField($module, $embeddedModuleField)) {
                                            $this->errors[] = $module . " module [$view] view reference EMBEDDED column with unknown field '$embeddedModuleField' of module '$embeddedModule'";
                                        }
                                    }
                                    $isEmbedded = false;
                                } else {
                                    if ($column && !Utility::isValidModuleField($module, $column)) {
                                        $this->errors[] = $module . " module [$view] view references unknown field '$column'";
                                    }
                                }
                            }
                            if ($isEmbedded) {
                                $this->errors[] = $module . " module [$view] view incorrectly uses EMBEDDED in the last column";
                            }
                        } elseif (count($field) == 1) {
                            // index view
                            if ($field[0] && !Utility::isValidModuleField($module, $field[0])) {
                                $this->errors[] = $module . " module [$view] view references unknown field '" . $field[0] . "'";
                            }
                        }
                    }
                }
            } else {
                $this->warnings[] = $module . " module [$view] view file is missing";
            }
        }
        // Warn if the module is missing standard views
        if ($viewCounter < count($views)) {
            $this->warnings[] = $module . " module has only " . (int)$viewCounter . " views.";
        }

        return count($this->errors);
    }
}
