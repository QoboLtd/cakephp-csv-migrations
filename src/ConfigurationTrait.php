<?php
namespace CsvMigrations;

use Cake\Core\Configure;
use Cake\Utility\Inflector;
use Qobo\Utils\ModuleConfig\ModuleConfig;

trait ConfigurationTrait
{
    /**
     * Default icon
     *
     * When all else fails, use this icon.
     */
    public $defaultIcon = 'cube';

    /**
     * Table/module configuration
     *
     * @var array
     */
    protected $_config = [];

    /**
     * Searchable flag
     *
     * @var boolean
     */
    protected $_searchable = false;

    /**
     * Module icon
     *
     * @var string
     */
    protected $_icon;
    /**
     * Lookup fields used for fetching record(s) through the API
     *
     * @var array
     */
    protected $_lookupFields;

    /**
     * Each table might have a parent
     * @var string
     */
    protected $_parentModuleField;

    /**
     * relation field that identifies parent_id field
     * @var string
     */
    protected $_parentRelationField;

    /**
     * redirect flag whether it should be self|parent
     * to identify where to redirect
     * @var string
     */
    protected $_parentRedirectField;

    /**
     * allow_reminders array
     * @var array
     */
    protected $_tableAllowRemindersField = [];

    /**
     * Typeahead fields used for searching in related fields
     *
     * @var array
     */
    protected $_typeaheadFields;

    /**
     * Virtual fields
     *
     * @var array
     */
    protected $_virtualFields = [];

    /**
     * Hidden associations
     * @var array
     */
    protected $_hiddenAssociations = [];

    /**
     * Association Labels
     * @var array
     */
    protected $_associationLabels = [];

    /**
     * Module alias
     *
     * @var string
     */
    protected $_moduleAlias;

    /**
     * Notifications config
     *
     * @var array
     */
    protected $_notifications = [
        'enable' => false,
        'ignored_fields' => []
    ];

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
        $result = [];
        try {
            $mc = new ModuleConfig(ModuleConfig::CONFIG_TYPE_MODULE, Inflector::camelize($tableName));
            $result = (array)json_decode(json_encode($mc->parse()), true);
        } catch (Exception $e) {
            // It's OK for the configuration not to exist. We should probably log it.
        }
        $this->_config = $result;

        // display field from configuration file
        if (isset($this->_config['table']['display_field']) && method_exists($this, 'displayField')) {
            $this->displayField($this->_config['table']['display_field']);
        }

        // module icon from configuration file
        if (isset($this->_config['table']['icon'])) {
            $this->icon($this->_config['table']['icon']);
        }

        // lookup field(s) from configuration file
        if (isset($this->_config['table']['lookup_fields'])) {
            $this->lookupFields($this->_config['table']['lookup_fields']);
        }

        // typeahead field(s) from configuration file
        if (isset($this->_config['table']['typeahead_fields'])) {
            $this->typeaheadFields($this->_config['table']['typeahead_fields']);
        }

        // set module alias from configuration file
        if (isset($this->_config['table']['alias'])) {
            $this->moduleAlias($this->_config['table']['alias']);
        }

        if (isset($this->_config['table']['allow_reminders'])) {
            $this->tableSection($this->_config['table']);
        }

        // set searchable flag from configuration file
        if (isset($this->_config['table']['searchable'])) {
            $this->isSearchable($this->_config['table']['searchable']);
        }

        if (isset($this->_config['associations']['hide_associations'])) {
            $this->hiddenAssociations($this->_config['associations']['hide_associations']);
        }

        if (isset($this->_config['associationLabels'])) {
            $this->associationLabels($this->_config['associationLabels']);
        }

        if (isset($this->_config['parent'])) {
            $this->parentSection($this->_config['parent']);
        }

        if (isset($this->_config['notifications'])) {
            $this->notifications($this->_config['notifications']);
        }

        // set virtual field(s)
        if (isset($this->_config['virtualFields'])) {
            $this->setVirtualFields($this->_config['virtualFields']);
        }
    }

    /**
     * Returns the searchable flag or sets a new one
     *
     * @param  bool|null $searchable sets module as (not) searchable
     * @return bool
     */
    public function isSearchable($searchable = null)
    {
        if (!is_null($searchable)) {
            $this->_searchable = (bool)$searchable;
        }

        return $this->_searchable;
    }

    /**
     * Returns the module icon or sets a new one
     *
     * @param string|null $icon sets the icon
     * @return string
     */
    public function icon($icon = null)
    {
        if ($icon !== null) {
            $this->_icon = (string)$icon;
        }

        // Default icon if none is set
        if (empty($this->_icon)) {
            $this->_icon = (string)Configure::read('CsvMigrations.default_icon');
        }

        // To avoid unncessary checks in the views for the missing
        // icon, we should ALWAYS return something.
        if (empty($this->_icon)) {
            $this->_icon = $this->defaultIcon;
        }

        return $this->_icon;
    }

    /**
     * Returns the lookup fields or sets a new one
     *
     * @param string|null $fields sets lookup fields
     * @return string
     */
    public function lookupFields($fields = null)
    {
        if ($fields !== null) {
            $this->_lookupFields = explode(',', $fields);
        }

        return $this->_lookupFields;
    }

    /**
     * parse 'parent' section variables
     * @TODO: Currently we use only scalars for parent vars
     * It should be expanded to support arrays if any appear.
     * @param array $section containing 'parent' block from ini
     * @return void
     */
    public function parentSection($section = [])
    {
        if (!empty($section)) {
            foreach ($section as $fieldName => $fieldValues) {
                $field = Inflector::camelize($fieldName);
                $property = sprintf('_parent%sField', $field);
                if (property_exists($this, $property)) {
                    $this->{$property} = $fieldValues;
                }
            }
        }
    }

    /**
     * tableSection parser for the protected variables
     *
     * @TODO: currently parses only allow_reminders,
     * not to break existing properties of 'table' section
     *
     * @param array $tableSection Section to parse
     * @return void
     */
    public function tableSection($tableSection = [])
    {
        if (!empty($tableSection)) {
            foreach ($tableSection as $fieldName => $fieldValues) {
                if ($fieldName == 'allow_reminders') {
                    $field = Inflector::camelize($fieldName);
                    $property = sprintf('_table%sField', $field);

                    if (property_exists($this, $property)) {
                        if (!is_array($this->{$property})) {
                            continue;
                        }

                        $this->{$property} = explode(',', $fieldValues);
                    }
                }
            }
        }
    }

    /**
     * getTableAllowRemindersField
     * @return array
     */
    public function getTableAllowRemindersField()
    {
        return $this->_tableAllowRemindersField;
    }

    /**
     * getParentRelationField
     * @return string
     */
    public function getParentModuleField()
    {
        return $this->_parentModuleField;
    }

    /**
     * getParentRedirectField
     * @return string
     */
    public function getParentRedirectField()
    {
        return $this->_parentRedirectField;
    }

    /**
     * getParentRelationField
     * @return string
     */
    public function getParentRelationField()
    {
        return $this->_parentRelationField;
    }

    /**
     * Returns the typeahead fields or sets a new one
     *
     * @param string|null $fields sets typeahead fields
     * @return string
     */
    public function typeaheadFields($fields = null)
    {
        if ($fields !== null) {
            $this->_typeaheadFields = explode(',', $fields);
        }

        return $this->_typeaheadFields;
    }

    /**
     * Return association labels if any present
     * @param array $fields received from CSV
     * @return array
     */
    public function associationLabels($fields = [])
    {
        if (!empty($fields)) {
            foreach ($fields as $name => $label) {
                $this->_associationLabels[$name] = $label;
            }
        }

        return $this->_associationLabels;
    }

    /**
     * Returns notifications config or sets a new one.
     *
     * @param array $notifications sets notifications config
     * @return array
     */
    public function notifications($notifications = [])
    {
        if (!empty($notifications)) {
            foreach ($this->_notifications as $k => $v) {
                if (empty($notifications[$k])) {
                    continue;
                }

                $type = gettype($v);

                $v = $notifications[$k];
                switch ($type) {
                    case 'boolean':
                        $v = (bool)$v;
                        break;

                    case 'array':
                        $v = is_string($v) ? explode(',', $v) : $v;
                        break;
                }

                $this->_notifications[$k] = $v;
            }
        }

        return $this->_notifications;
    }

    /**
     * Return the list of hidden Associations for the config
     * file
     * @param string|null $fields received
     * @return array
     */
    public function hiddenAssociations($fields = null)
    {
        if ($fields !== null) {
            $this->_hiddenAssociations = explode(',', $fields);
        }

        return $this->_hiddenAssociations;
    }

    /**
     * Returns the module alias or sets a new one
     *
     * @param  string|null $alias sets a new name to be used as module alias
     * @return string
     */
    public function moduleAlias($alias = null)
    {
        if (!is_null($alias)) {
            $this->_moduleAlias = $alias;
        }

        if (is_null($this->_moduleAlias)) {
            $this->_moduleAlias = $this->alias();
        }

        return $this->_moduleAlias;
    }

    /**
     * Virtual fields setter method.
     *
     * Sets Table's virtual fields as an associated array with the
     * virtual field name as the key and the db fields name as value.
     * @param array|null $fields passed to method from CSV
     * @return void
     */
    public function setVirtualFields(array $fields = [])
    {
        if (empty($fields)) {
            return;
        }

        foreach ($fields as $v => $k) {
            $this->_virtualFields[$v] = explode(',', $k);
        }
    }

    /**
     * Virtual fields getter method.
     *
     * @return array
     */
    public function getVirtualFields()
    {
        return $this->_virtualFields;
    }
}
