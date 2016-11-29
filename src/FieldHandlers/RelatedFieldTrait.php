<?php
namespace CsvMigrations\FieldHandlers;

use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use Cake\View\Helper\IdGeneratorTrait;
use CsvMigrations\FieldHandlers\BaseFieldHandler;

trait RelatedFieldTrait
{
    /**
     * Field value separator
     *
     * @var string
     */
    protected $_separator = '&gt;';

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

        // get associated entity record
        $result['entity'] = $this->_getAssociatedRecord($table, $data);
        // get related table's displayField value
        if (!empty($result['entity'])) {
            $result['dispFieldVal'] = !empty($result['entity']->{$table->displayField()})
                ? $result['entity']->{$table->displayField()}
                : null;
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
        $query = $table->find('all', [
            'conditions' => [$table->primaryKey() => $value],
            'limit' => 1
        ]);

        return $query->first();
    }
}
