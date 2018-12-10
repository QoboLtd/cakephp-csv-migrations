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

class MigrationCheck extends AbstractCheck
{
    /**
     * Execute a check
     *
     * @param string $module Module name
     * @param array $options Check options
     * @return int Number of encountered errors
     */
    public function run(string $module, array $options = []) : int
    {
        $mc = $this->getModuleConfig($module, $options);
        $fields = [];
        try {
            $config = json_encode($mc->parse());
            $fields = false === $config ? [] : json_decode($config, true);
        } catch (InvalidArgumentException $e) {
            // We need errors and warnings irrelevant of the exception
            $this->errors = array_merge($this->errors, $mc->getErrors());
            $this->warnings = array_merge($this->warnings, $mc->getWarnings());

            return count($this->errors);
        }

        if (empty($fields)) {
            return count($this->errors);
        }

        //
        // Check fields
        //
        $this->checkFields($module, $fields);

        return count($this->errors);
    }

    /**
     * Check fields and their types.
     *
     * @param string $module Module name.
     * @param mixed[] $fields List of field definitions.
     * @return void
     */
    public function checkFields(string $module, array $fields = []): void
    {
        $seenFields = [];

        // Check each field one by one
        foreach ($fields as $field) {
            // Check for field duplicates
            if (in_array($field['name'], $seenFields)) {
                $this->errors[] = $module . " migration specifies field '" . $field['name'] . "' more than once";
                continue;
            }

            $seenFields[] = $field['name'];

            $type = $field['type'];
            $limit = null;
            // Matches:
            // * date, time, string, and other simple types
            // * list(something), related(Others) and other simple limits
            // * related(Vendor/Plugin.Model) and other complex limits
            // * aggregated(CsvMigrations\\Aggregator\\MaxAggregator,TableName,field_name) aggregated configuration
            if (preg_match('/^(\w+?)\(([\w\/\.\,\\\]+?)\)$/', $field['type'], $matches)) {
                $type = $matches[1];
                $limit = $matches[2];
            }

            // Field type must be valid
            if (!Utility::isValidFieldType($type)) {
                $this->errors[] = $module . " migration specifies invalid type '" . $type . "' for field  '" . $field['name'] . "'";
                continue;
            }

            switch ($type) {
                case 'related':
                    // Only check for simple modules, not the vendor/plugin ones
                    if (preg_match('/^\w+$/', $limit) && !Utility::isValidModule($limit)) {
                        $errors[] = $module . " migration relates to unknown module '$limit' in '" . $field['name'] . "' field";
                    }
                    // Documents module can be used as `files(Documents)` for a container of the uploaded files,
                    // or as `related(Documents)` as a regular module relationship.  It's often easy to overlook
                    // which one was desired.  Failing on either one is incorrect, as both are valid.  A
                    // warning is needed instead for the `related(Documents)` case instead.
                    // The only known legitimate case is in the Files, which is join table between Documents and FileStorage.
                    if (('Documents' == $limit) && ('Files' != $module)) {
                        $this->warnings[] = $module . " migration uses 'related' type for 'Documents' in '" . $field['name'] . "'. Maybe wanted 'files(Documents)'?";
                    }
                    break;
                case 'list':
                case 'money':
                case 'metric':
                    if (!Utility::isValidList($limit, $module)) {
                        $this->errors[] = $module . " migration uses unknown or empty list '$limit' in '" . $field['name'] . "' field";
                    }
                    break;
            }
        }
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
        $configFile = empty($options['configFile']) ? null : $options['configFile'];
        $mc = new ModuleConfig(ConfigType::MIGRATION(), $module, $configFile, ['cacheSkip' => true]);

        /** @var \Qobo\Utils\ModuleConfig\Parser\SchemaInterface&\Cake\Core\InstanceConfigTrait */
        $schema = $mc->createSchema(['lint' => true]);
        $mc->setParser(new Parser($schema, ['lint' => true]));

        return $mc;
    }
}
