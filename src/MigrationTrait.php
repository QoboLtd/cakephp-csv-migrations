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
namespace CsvMigrations;

use Cake\Core\App;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;

/**
 * @deprecated 28.0.2 getFieldsDefinitions method should be moved to CsvMigrations\Table,
 * but since this method is used heavily it will require a lot of testing before moving and
 * dropping this Trait class altogether.
 */
trait MigrationTrait
{
    /**
     * Cached CSV field definitions for the current module
     *
     * @var array
     */
    protected $_fieldDefinitions = [];

    /**
     * Get fields from CSV file
     *
     * This method gets all fields defined in the CSV and returns
     * them as an associative array.
     *
     * Additionally, an associative array of stub fields can be
     * passed, which will be included in the returned definitions.
     * This is useful when working with fields which are NOT part
     * of the migration.csv definitions, such as combined fields
     * and virtual fields.
     *
     * If the field exists in the CSV configuration and is passed
     * as a stub field, then the CSV definition will be preferred.
     *
     * Note that this method is called very frequently during the
     * rendering of the views, so performance is important.  For
     * this reason, parsed definitions are stored in the property
     * to avoid unnecessary processing of files and conversion of
     * data. Stub fields, however, won't be cached as they are not
     * real definitions and might vary from call to call.
     *
     * There are cases, when no field definitions are available at
     * all.  For example, external, non-CSV modules.  For those
     * cases, all exceptions and errors are silenced and an empty
     * array of field definitions is returned.  Unless, of course,
     * there are stub fields provided.
     *
     * @param mixed[] $stubFields Stub fields
     * @return mixed[] Associative array of fields and their definitions
     */
    public function getFieldsDefinitions(array $stubFields = []) : array
    {
        $result = [];

        // Get cached definitions
        if (! empty($this->_fieldDefinitions)) {
            $result = $this->_fieldDefinitions;
        }

        // Fetch definitions from CSV if cache is empty
        if (empty($result)) {
            $moduleName = App::shortName(get_class($this), 'Model/Table', 'Table');
            list(, $moduleName) = pluginSplit($moduleName);

            $mc = new ModuleConfig(ConfigType::MIGRATION(), $moduleName);
            $config = json_encode($mc->parse());
            $result = false === $config ? [] : json_decode($config, true);
            if (! empty($result)) {
                $this->_fieldDefinitions = $result;
            }
        }

        if (empty($stubFields)) {
            return $result;
        }

        // Merge $result with $stubFields
        foreach ($stubFields as $field => $definition) {
            if (!array_key_exists($field, $result)) {
                $result[$field] = $definition;
            }
        }

        return $result;
    }
}
