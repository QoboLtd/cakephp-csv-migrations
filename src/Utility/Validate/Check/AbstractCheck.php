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
use Qobo\Utils\ErrorTrait;

abstract class AbstractCheck implements CheckInterface
{
    use ErrorTrait;

    /**
     * Add fields from migration config to schema.
     *
     * @param string[] $schema Schema.
     * @param string $module Module name
     * @return string[] Schema.
     */
    protected function addFieldsToSchema(array $schema, string $module): array
    {
        if (isset($schema['definitions']['fieldName'])) {
            $fields = Utility::getRealModuleFields($module);
            if (!empty($fields)) {
                $schema['definitions']['fieldName']['enum'] = $fields;
            }
        }

        return $schema;
    }

    /**
     * Add virtual fields from migration config to schema.
     *
     * @param string[] $schema Schema.
     * @param string $module Module name
     * @return string[] Schema.
     */
    protected function addVirtualFieldsToSchema(array $schema, string $module): array
    {
        if (isset($schema['definitions']['virtualFieldName'])) {
            $virtualFields = Utility::getVirtualModuleFields($module);
            if (!empty($virtualFields)) {
                $schema['definitions']['virtualFieldName']['enum'] = array_keys($virtualFields);
            }
        }

        return $schema;
    }

    /**
     * Add available modules to schema.
     *
     * @param string[] $schema Schema.
     * @param string $module Module name
     * @return string[] Schema.
     */
    protected function addModulesToSchema(array $schema, string $module): array
    {
        if (isset($schema['definitions']['moduleName'])) {
            $modules = Utility::getModules();
            if (!empty($modules)) {
                $schema['definitions']['moduleName']['enum'] = array_values($modules);
            }
        }

        return $schema;
    }
}
