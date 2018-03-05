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

/**
 * @deprecated 28.0.2 Use ModuleConfig instead, for parsing configuration files
 * @see \Qobo\Utils\ModuleConfig\ModuleConfig
 */
trait ConfigurationTrait
{
    /**
     * Traits cannot have constants, so we rely on public static properties
     */

    /**
     * @deprecated 28.0.2
     */
    public static $CONFIG_OPTION_SEARCHABLE = 'table.searchable';

    /**
     * @deprecated 28.0.2
     */
    public static $CONFIG_OPTION_ICON = 'table.icon';

    /**
     * @deprecated 28.0.2
     */
    public static $CONFIG_OPTION_LOOKUP_FIELDS = 'table.lookup_fields';

    /**
     * @deprecated 28.0.2
     */
    public static $CONFIG_OPTION_TYPEAHEAD_FIELDS = 'table.typeahead_fields';

    /**
     * @deprecated 28.0.2
     */
    public static $CONFIG_OPTION_ALLOW_REMINDERS = 'table.allow_reminders';

    /**
     * @deprecated 28.0.2
     */
    public static $CONFIG_OPTION_PARENT = 'parent';

    /**
     * @deprecated 28.0.2
     */
    public static $CONFIG_OPTION_ASSOCIATION_LABELS = 'associationLabels';

    /**
     * @deprecated 28.0.2
     */
    public static $CONFIG_OPTION_NOTIFICATIONS = 'notifications';

    /**
     * @deprecated 28.0.2
     */
    public static $CONFIG_OPTION_HIDDEN_ASSOCIATIONS = 'associations.hide_associations';

    /**
     * @deprecated 28.0.2
     */
    public static $CONFIG_OPTION_MODULE_ALIAS = 'table.alias';

    /**
     * @deprecated 28.0.2
     */
    public static $CONFIG_OPTION_VIRTUAL_FIELDS = 'virtualFields';

    /**
     * @deprecated 28.0.2
     */
    public static $CONFIG_OPTION_MANY_TO_MANY_MODULES = 'manyToMany.modules';

    /**
     * Table/module configuration
     *
     * @var array
     * @deprecated 28.0.2
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
     * @deprecated 28.0.2
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
     * @deprecated 28.0.2
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
     * @deprecated 28.0.2
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
     * @deprecated 28.0.2
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
     * @deprecated 28.0.2
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
