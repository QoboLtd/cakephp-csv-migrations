<?php
namespace CsvMigrations;

use Cake\Core\Configure;
use Cake\Utility\Inflector;
use Qobo\Utils\ModuleConfig\ModuleConfig;

trait ConfigurationTrait
{
    /**
     * Table/module configuration
     *
     * @var array
     */
    protected $_config = [];

    /**
     * Method that returns table configuration.
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * Method that sets table configuration.
     *
     * @param string $tableName table name
     * @return void
     */
    protected function _setConfiguration($tableName)
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
        $config = $this->getConfig();

        return (bool)$config['table']['searchable'];
    }

    /**
     * Returns the module icon
     *
     * @return string
     */
    public function icon()
    {
        $config = $this->getConfig();

        return (string)$config['table']['icon'];
    }

    /**
     * Returns the lookup fields
     *
     * @return array
     */
    public function lookupFields()
    {
        $config = $this->getConfig();

        return $config['table']['lookup_fields'];
    }

    /**
     * getTableAllowRemindersField
     *
     * @return array
     */
    public function getTableAllowRemindersField()
    {
        $config = $this->getConfig();

        return $config['table']['allow_reminders'];
    }

    /**
     * getParentRelationField
     *
     * @return string
     */
    public function getParentModuleField()
    {
        $result = '';

        $config = $this->getConfig();
        if (!empty($config['parent']['module'])) {
            $result = $config['parent']['module'];
        }

        return $result;
    }

    /**
     * getParentRedirectField
     * @return string
     */
    public function getParentRedirectField()
    {
        $result = '';

        $config = $this->getConfig();
        if (!empty($config['parent']['redirect'])) {
            $result = $config['parent']['redirect'];
        }

        return $result;
    }

    /**
     * getParentRelationField
     * @return string
     */
    public function getParentRelationField()
    {
        $result = '';

        $config = $this->getConfig();
        if (!empty($config['parent']['relation'])) {
            $result = $config['parent']['relation'];
        }

        return $result;
    }

    /**
     * Returns the typeahead fields
     *
     * @return array
     */
    public function typeaheadFields()
    {
        $config = $this->getConfig();

        return $config['table']['typeahead_fields'];
    }

    /**
     * Return association labels if any present
     *
     * @return array
     */
    public function associationLabels()
    {
        $config = $this->getConfig();

        return $config['associationLabels'];
    }

    /**
     * Returns notifications config
     *
     * @return array
     */
    public function notifications()
    {
        $config = $this->getConfig();

        return $config['notifications'];
    }

    /**
     * Return the list of hidden Associations for the config
     * file
     * @return array
     */
    public function hiddenAssociations()
    {
        $result = [];

        $config = $this->getConfig();
        if (!empty($config['associations']['hide_associations'])) {
            $result = $config['associations']['hide_associations'];
        }

        return $result;
    }

    /**
     * Returns the module alias
     *
     * @return string
     */
    public function moduleAlias()
    {
        $config = $this->getConfig();
        $result = (string)$config['table']['alias'];
        if (empty($result)) {
            $result = $this->alias();
        }

        return $result;
    }

    /**
     * Virtual fields getter method.
     *
     * @return array
     */
    public function getVirtualFields()
    {
        $result = [];

        $config = $this->getConfig();
        if (!empty($config['virtualFields'])) {
            $result = $config['virtualFields'];
        }

        return $result;
    }
}
