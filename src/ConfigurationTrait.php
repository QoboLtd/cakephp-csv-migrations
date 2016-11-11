<?php
namespace CsvMigrations;

use Cake\Core\Configure;
use Cake\Utility\Inflector;
use CsvMigrations\Parser\Ini\Parser;
use CsvMigrations\PathFinder\ConfigPathFinder;

trait ConfigurationTrait
{
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
     * Lookup fields used for fetching record(s) through the API
     *
     * @var array
     */
    protected $_lookupFields;

    /**
     * Each table might have a parent
     * @var string
     */
    protected $_parentField;


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
        $pathFinder = new ConfigPathFinder;
        $path = $pathFinder->find(Inflector::camelize($tableName));
        $parser = new Parser();
        $this->_config = $parser->parseFromPath($path);
        // display field from configuration file
        if (isset($this->_config['table']['display_field']) && method_exists($this, 'displayField')) {
            $this->displayField($this->_config['table']['display_field']);
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

        if (isset($this->_config['parent']['module'])) {
            $this->parentField($this->_config['parent']['module']);
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
     * CSV Table might have a parent
     * that helps us redirect things on working
     * with modal forms.
     * @param string $field received
     * @return string
     */
    public function parentField($field = null)
    {
        if ($field !== null) {
            $this->_parentField = $field;
        }

        return $this->_parentField;
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
