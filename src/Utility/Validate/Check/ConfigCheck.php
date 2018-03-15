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
use Exception;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;

class ConfigCheck extends AbstractCheck
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
        $config = [];

        try {
            $mc = new ModuleConfig(ConfigType::MODULE(), $module, null, ['cacheSkip' => true]);
            $config = json_decode(json_encode($mc->parse()), true);
        } catch (Exception $e) {
            // We need errors and warnings irrelevant of the exception
        }
        $this->errors = array_merge($this->errors, $mc->getErrors());
        $this->warnings = array_merge($this->warnings, $mc->getWarnings());

        if (empty($config)) {
            return count($this->errors);
        }

        //
        //Check configuration options
        //

        // [table] section
        if (!empty($config['table'])) {
            // 'display_field' key is optional, but must contain valid field if specified
            if (!empty($config['table']['display_field'])) {
                if (!Utility::isValidModuleField($module, $config['table']['display_field'])) {
                    $this->errors[] = $module . " config [table] section references unknown field '" . $config['table']['display_field'] . "' in 'display_field' key";
                }
                if (!empty($options['display_field_bad_values']) && in_array($config['table']['display_field'], $options['display_field_bad_values'])) {
                    $this->errors[] = $module . " config [table] section uses bad value '" . $config['table']['display_field'] . "' in 'display_field' key";
                }
            }
            // 'icon' key is optional, but must contain good values if specified
            if (!empty($config['table']['icon'])) {
                if (!empty($options['icon_bad_values']) && in_array($config['table']['icon'], $options['icon_bad_values'])) {
                    $this->errors[] = $module . " config [table] section uses bad value '" . $config['table']['icon'] . "' in 'icon' key";
                }
            }

            // 'typeahead_fields' key is optional, but must contain valid fields if specified
            if (!empty($config['table']['typeahead_fields'])) {
                foreach ($config['table']['typeahead_fields'] as $typeaheadField) {
                    if (!Utility::isValidModuleField($module, $typeaheadField)) {
                        $this->errors[] = $module . " config [table] section references unknown field '" . $typeaheadField . "' in 'typeahead_fields' key";
                    }
                }
            }
            // 'lookup_fields' key is optional, but must contain valid fields if specified
            if (!empty($config['table']['lookup_fields'])) {
                foreach ($config['table']['lookup_fields'] as $lookupField) {
                    if (!Utility::isValidModuleField($module, $lookupField)) {
                        $this->errors[] = $module . " config [table] section references unknown field '" . $lookupField . "' in 'lookup_fields' key";
                    }
                }
            }
        }

        // [parent] section
        if (!empty($config['parent'])) {
            if (!empty($config['parent']['module'])) {
                if (!Utility::isValidModule($config['parent']['module'])) {
                    $this->errors[] = $module . " config [parent] section references unknown module '" . $config['parent']['module'] . "' in 'module' key";
                }
            }
            if (!empty($config['parent']['relation'])) {
                if (!Utility::isRealModuleField($config['parent']['relation'], $module)) {
                    $this->errors[] = $module . " config [parent] section references non-real field '" . $config['parent']['relation'] . "' in 'relation' key";
                }
            }
            if (!empty($config['parent']['redirect'])) {
                if (!in_array($config['parent']['redirect'], ['self', 'parent'])) {
                    $this->errors[] = $module . " config [parent] section references unknown redirect type '" . $config['parent']['redirect'] . "' in 'redirect key";
                }

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

        // [virtualFields] section
        if (!empty($config['virtualFields'])) {
            foreach ($config['virtualFields'] as $virtualField => $realFields) {
                if (empty($realFields)) {
                    $this->errors[] = $module . " config [virtualFields] section does not define real fields for '$virtualField' virtual field";
                    continue;
                }
                foreach ($realFields as $realField) {
                    if (!Utility::isRealModuleField($module, $realField)) {
                        $this->errors[] = $module . " config [virtualFields] section uses a non-real field in '$virtualField' virtual field";
                    }
                }
            }
        }

        // [manyToMany] section
        if (!empty($config['manyToMany'])) {
            // 'modules' key is required and must contain valid modules
            if (!empty($config['manyToMany']['modules'])) {
                $manyToManyModules = $config['manyToMany']['modules'];
                foreach ($manyToManyModules as $manyToManyModule) {
                    if (!Utility::isValidModule($manyToManyModule)) {
                        $this->errors[] = $module . " config [manyToMany] section references unknown module '$manyToManyModule' in 'modules' key";
                    }
                }
            }
        }

        // [notifications] section
        if (!empty($config['notifications'])) {
            // 'ignored_fields' key is optional, but must contain valid fields if specified
            if (!empty($config['notifications']['ignored_fields'])) {
                $ignoredFields = explode(',', trim($config['notifications']['ignored_fields']));
                foreach ($ignoredFields as $ignoredField) {
                    if (!Utility::isValidModuleField($module, $ignoredField)) {
                        $this->errors[] = $module . " config [notifications] section references unknown field '" . $ignoredField . "' in 'typeahead_fields' key";
                    }
                }
            }
        }

        // [conversion] section
        if (!empty($config['conversion'])) {
            // 'module' key is required and must contain valid modules
            if (!empty($config['conversion']['modules'])) {
                $conversionModules = explode(',', $config['conversion']['modules']);
                foreach ($conversionModules as $conversionModule) {
                    // Only check for simple modules, not the vendor/plugin ones
                    if (preg_match('/^\w+$/', $conversionModule) && !Utility::isValidModule($conversionModule)) {
                        $this->errors[] = $module . " config [conversion] section references unknown module '$conversionModule' in 'modules' key";
                    }
                }
            }
            // 'inherit' key is optional, but must contain valid modules if defined
            if (!empty($config['conversion']['inherit'])) {
                $inheritModules = explode(',', $config['conversion']['inherit']);
                foreach ($inheritModules as $inheritModule) {
                    if (!Utility::isValidModule($inheritModule)) {
                        $this->errors[] = $module . " config [conversion] section references unknown module '$inheritModule' in 'inherit' key";
                    }
                }
            }
            // 'field' key is optional, but must contain valid field and 'value' if defined
            if (!empty($config['conversion']['field'])) {
                // 'field' key is optional, but must contain valid field is specified
                if (!Utility::isValidModuleField($module, $config['conversion']['field'])) {
                    $this->errors[] = $module . " config [conversion] section references unknown field '" . $config['conversion']['field'] . "' in 'field' key";
                }
                // 'value' key must be set
                if (!isset($config['conversion']['value'])) {
                    $this->errors[] = $module . " config [conversion] section references 'field' but does not set a 'value' key";
                }
            }
        }

        return count($this->errors);
    }
}
