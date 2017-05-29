<?php
namespace CsvMigrations;

use Cake\Core\Configure;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use Qobo\Utils\ModuleConfig\ModuleConfig;

trait ConfigurationTrait
{
    // Traits cannot have constants, so we rely on public static properties
    public static $CONFIG_OPTION_SEARCHABLE = 'table.searchable';
    public static $CONFIG_OPTION_ICON = 'table.icon';
    public static $CONFIG_OPTION_LOOKUP_FIELDS = 'table.lookup_fields';
    public static $CONFIG_OPTION_TYPEAHEAD_FIELDS = 'table.typeahead_fields';
    public static $CONFIG_OPTION_ALLOW_REMINDERS = 'table.allow_reminders';
    public static $CONFIG_OPTION_PARENT_MODULE = 'parent.module';
    public static $CONFIG_OPTION_PARENT_REDIRECT = 'parent.redirect';
    public static $CONFIG_OPTION_PARENT_RELATION = 'parent.relation';
    public static $CONFIG_OPTION_ASSOCIATION_LABELS = 'associationLabels';
    public static $CONFIG_OPTION_NOTIFICATIONS = 'notifications';
    public static $CONFIG_OPTION_HIDDEN_ASSOCIATIONS = 'associations.hide_associations';
    public static $CONFIG_OPTION_MODULE_ALIAS = 'table.alias';
    public static $CONFIG_OPTION_VIRTUAL_FIELDS = 'virtualFields';

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
        $mc = new ModuleConfig(ModuleConfig::CONFIG_TYPE_MODULE, Inflector::camelize($tableName));
        $this->_config = (array)json_decode(json_encode($mc->parse()), true);

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
     * getParentRelationField
     *
     * @return string
     */
    public function getParentModuleField()
    {
        $result = $this->getConfig(self::$CONFIG_OPTION_PARENT_MODULE);
        $result = isset($result[0]) ? (string)$result[0] : '';

        return $result;
    }

    /**
     * getParentRedirectField
     *
     * @return string
     */
    public function getParentRedirectField()
    {
        $result = $this->getConfig(self::$CONFIG_OPTION_PARENT_REDIRECT);
        $result = isset($result[0]) ? (string)$result[0] : '';

        return $result;
    }

    /**
     * getParentRelationField
     *
     * @return string
     */
    public function getParentRelationField()
    {
        $result = $this->getConfig(self::$CONFIG_OPTION_PARENT_RELATION);
        $result = isset($result[0]) ? (string)$result[0] : '';

        return $result;
    }

    /**
     * Returns notifications config
     *
     * @deprecated Use getConfig() directly instead
     * @return array
     */
    public function notifications()
    {
        return (array)$this->getConfig(self::$CONFIG_OPTION_ASSOCIATION_LABELS);
    }

    /**
     * Return the list of hidden Associations
     *
     * @deprecated Use getConfig() directly instead
     * @return array
     */
    public function hiddenAssociations()
    {
        return (array)$this->getConfig(self::$CONFIG_OPTION_HIDDEN_ASSOCIATIONS);
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
