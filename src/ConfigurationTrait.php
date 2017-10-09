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

use Cake\Core\Configure;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;

trait ConfigurationTrait
{
    // Traits cannot have constants, so we rely on public static properties
    public static $CONFIG_OPTION_SEARCHABLE = 'table.searchable';
    public static $CONFIG_OPTION_ICON = 'table.icon';
    public static $CONFIG_OPTION_LOOKUP_FIELDS = 'table.lookup_fields';
    public static $CONFIG_OPTION_TYPEAHEAD_FIELDS = 'table.typeahead_fields';
    public static $CONFIG_OPTION_ALLOW_REMINDERS = 'table.allow_reminders';
    public static $CONFIG_OPTION_PARENT = 'parent';
    public static $CONFIG_OPTION_ASSOCIATION_LABELS = 'associationLabels';
    public static $CONFIG_OPTION_NOTIFICATIONS = 'notifications';
    public static $CONFIG_OPTION_HIDDEN_ASSOCIATIONS = 'associations.hide_associations';
    public static $CONFIG_OPTION_MODULE_ALIAS = 'table.alias';
    public static $CONFIG_OPTION_VIRTUAL_FIELDS = 'virtualFields';
    public static $CONFIG_OPTION_MANY_TO_MANY_MODULES = 'manyToMany.modules';

    /**
     * Table/module configuration
     *
     * @var array
     */
    protected $_config = [];

    /**
     * Get table configuration
     *
     * Get the full table configuration, or some subset
     * based on the given path.
     *
     * NOTE: Before this functionality makes any sense, you
     *       need to call setConfig().
     *
     * @param string $path Path to subset of configuration
     * @return array
     */
    public function getConfig($path = null)
    {
        $result = [];

        // Return everything if no subset specified
        if (empty($path)) {
            return $this->_config;
        }

        $result = Hash::extract($this->_config, $path);

        return $result;
    }

    /**
     * Set table configuration
     *
     * @param string $tableName table name
     * @return void
     */
    public function setConfig($tableName)
    {
        $config = new ModuleConfig(ConfigType::MODULE(), Inflector::camelize($tableName));
        $this->_config = (array)json_decode(json_encode($config->parse()), true);

        // display field from configuration file
        if (isset($this->_config['table']['display_field']) && method_exists($this, 'displayField')) {
            $this->displayField($this->_config['table']['display_field']);
        }
    }

    /**
     * Returns the searchable flag
     *
     * @return bool
     */
    public function isSearchable()
    {
        $result = $this->getConfig(self::$CONFIG_OPTION_SEARCHABLE);
        $result = isset($result[0]) ? (bool)$result[0] : false;

        return $result;
    }

    /**
     * Returns the module icon
     *
     * @return string
     */
    public function icon()
    {
        $result = $this->getConfig(self::$CONFIG_OPTION_ICON);
        $result = isset($result[0]) ? (string)$result[0] : '';

        return $result;
    }

    /**
     * Returns the module alias
     *
     * @return string
     */
    public function moduleAlias()
    {
        $result = $this->getConfig(self::$CONFIG_OPTION_MODULE_ALIAS);
        $result = isset($result[0]) ? (string)$result[0] : '';
        if (empty($result)) {
            $result = $this->alias();
        }

        return $result;
    }
}
