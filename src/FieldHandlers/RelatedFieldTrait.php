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
namespace CsvMigrations\FieldHandlers;

use BadMethodCallException;
use Cake\Core\Configure;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use CsvMigrations\FieldHandlers\RelatedFieldHandler;

trait RelatedFieldTrait
{
    /**
     * Field value separator
     *
     * @var string
     */
    protected $_separator = '»';

    /**
     * Get related model's parent model properties.
     *
     * @param  array $relatedProperties related model properties
     * @return mixed $result containing parent properties
     */
    protected function _getRelatedParentProperties($relatedProperties)
    {
        $result = [];
        $parentTable = TableRegistry::get($relatedProperties['config']['parent']['module']);
        $modelName = $relatedProperties['controller'];

        /*
        prepend plugin name
         */
        if (!is_null($relatedProperties['plugin'])) {
            $modelName = $relatedProperties['plugin'] . '.' . $modelName;
        }

        $foreignKey = $this->_getForeignKey($parentTable, $modelName);

        if (empty($relatedProperties['entity']) || empty($relatedProperties['entity']->{$foreignKey})) {
            return $result;
        }

        $related = $this->_getRelatedProperties($parentTable, $relatedProperties['entity']->{$foreignKey});

        if (!empty($related['entity'])) {
            $result = $related;
        }

        return $result;
    }

    /**
     * Get related model's properties.
     *
     * @param  mixed $table related table instance or name
     * @param  sting $data  query parameter value
     * @return mixed
     */
    protected function _getRelatedProperties($table, $data)
    {
        if (!is_object($table)) {
            $tableName = $table;
            $table = TableRegistry::get($tableName);
        } else {
            $tableName = $table->registryAlias();
        }

        $result['id'] = $data;

        if (method_exists($table, 'getConfig') && is_callable([$table, 'getConfig'])) {
            $result['config'] = $table->getConfig();
        }
        // display field
        $result['displayField'] = $table->displayField();
        // get associated entity record
        $result['entity'] = $this->_getAssociatedRecord($table, $data);
        // get related table's displayField value
        if (!empty($result['entity'])) {
            // Pass the value through related field handler
            // to properly display the user-friendly label.
            $fhf = new FieldHandlerFactory();
            $result['dispFieldVal'] = $fhf->renderValue(
                $table,
                $table->displayField(),
                $result['entity']->{$table->displayField()},
                ['renderAs' => RelatedFieldHandler::RENDER_PLAIN_VALUE]
            );
        } else {
            $result['dispFieldVal'] = null;
        }
        // get plugin and controller names
        list($result['plugin'], $result['controller']) = pluginSplit($tableName);

        return $result;
    }

    /**
     * Get parent model association's foreign key.
     *
     * @param  \Cake\ORM\Table $table     Table instance
     * @param  string          $modelName Model name
     * @return string
     */
    protected function _getForeignKey(Table $table, $modelName)
    {
        $result = null;
        foreach ($table->associations() as $association) {
            if ($modelName === $association->className()) {
                $result = $association->foreignKey();
            }
        }

        return $result;
    }

    /**
     * Retrieve and return associated record Entity, by primary key value.
     * If the record has been trashed - query will return NULL.
     *
     * @param  \Cake\ORM\Table $table Table instance
     * @param  string          $value Primary key value
     * @return object
     */
    protected function _getAssociatedRecord(Table $table, $value)
    {
        $options = [
            'conditions' => [$table->primaryKey() => $value],
            'limit' => 1
        ];
        // try to fetch with trashed if finder method exists, otherwise fallback to find all
        try {
            $query = $table->find('withTrashed', $options);
        } catch (BadMethodCallException $e) {
            $query = $table->find('all', $options);
        }

        return $query->first();
    }

    /**
     * Generate input help string
     *
     * Can be used as a value for placeholder or title attributes.
     *
     * @param array $properties Input properties
     * @return string
     */
    protected function _getInputHelp($properties)
    {
        $result = '';
        // use typeahead fields
        if (!empty($properties['config']['table']['typeahead_fields'])) {
            $result = $properties['config']['table']['typeahead_fields'];
            if (!empty($result)) {
                $result = implode(', or ', array_map(function ($value) {
                    return Inflector::humanize($value);
                }, $result));
            }
        }
        // if typeahead fields were not defined, use display field
        if (empty($result)) {
            $result = Inflector::humanize($properties['displayField']);
        }

        return $result;
    }

    /**
     * Get input field associated icon
     *
     * @param array $properties Input properties
     * @return string
     */
    protected function _getInputIcon($properties)
    {
        // return default icon if none is defined
        if (empty($properties['config']['table']['icon'])) {
            return Configure::read('CsvMigrations.default_icon');
        }

        return $properties['config']['table']['icon'];
    }
}
