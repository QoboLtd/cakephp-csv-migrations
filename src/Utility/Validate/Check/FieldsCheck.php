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

class FieldsCheck extends AbstractCheck
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
        try {
            $mc->parse();
        } catch (InvalidArgumentException $e) {
            // We need errors and warnings irrelevant of the exception
            $this->errors = array_merge($this->errors, $mc->getErrors());
        }
        $this->warnings = array_merge($this->warnings, $mc->getWarnings());

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
        $mc = new ModuleConfig(ConfigType::FIELDS(), $module, null, ['cacheSkip' => true]);

        /** @var \Qobo\Utils\ModuleConfig\Parser\SchemaInterface&\Cake\Core\InstanceConfigTrait */
        $schema = $mc->createSchema(['lint' => true]);
        $schema->setCallback(function (array $schema) use ($module) {
            $schema = $this->addFieldOptionsToSchema($schema, $module);

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
     * @param string $module Module name.
     * @return string[] Schema.
     */
    protected function addFieldOptionsToSchema(array $schema, string $module): array
    {
        $fields = Utility::getRealModuleFields($module);

        if (isset($schema['definitions']['fieldOptions'])) {
            if (!empty($fields)) {
                $schema['additionalProperties'] = false;
                foreach ($fields as $field) {
                    $schema['properties'][$field]['$ref'] = "#/definitions/fieldOptions";
                }
            }
        }

        return $schema;
    }
}
