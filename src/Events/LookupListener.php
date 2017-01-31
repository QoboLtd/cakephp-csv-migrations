<?php
namespace CsvMigrations\Events;

use Cake\Datasource\ResultSetDecorator;
use Cake\Event\Event;
use Cake\ORM\Query;
use Cake\ORM\Table;
use CsvMigrations\Events\BaseViewListener;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;
use CsvMigrations\FieldHandlers\RelatedFieldHandler;
use CsvMigrations\FieldHandlers\RelatedFieldTrait;
use RuntimeException;

class LookupListener extends BaseViewListener
{
    use RelatedFieldTrait;

    /**
     * {@inheritDoc}
     */
    public function implementedEvents()
    {
        return [
            'CsvMigrations.beforeLookup' => 'beforeLookup',
            'CsvMigrations.afterLookup' => 'afterLookup'
        ];
    }

    /**
     * Add conditions to Lookup Query.
     *
     * @param \Cake\Event\Event $event Event instance
     * @param \Cake\ORM\Query $query ORM Query
     * @return void
     */
    public function beforeLookup(Event $event, Query $query)
    {
        $request = $event->subject()->request;
        $table = $event->subject()->{$event->subject()->name};

        $fields = $this->_getTypeaheadFields($table);

        if (empty($fields)) {
            throw new RuntimeException("No typeahead or display field configured for " . $table->alias());
        }

        $query->order($this->_getOrderByFields($table, $query, $fields));

        if (!$request->query('query')) {
            return;
        }

        // add typeahead fields to where clause
        $value = $request->query('query');
        foreach ($fields as $field) {
            $query->orWhere([$field . ' LIKE' => '%' . $value . '%']);
        }
    }

    /**
     * Modify lookup entities after they have been fetched from the database
     *
     * @param \Cake\Event\Event $event Event instance
     * @param Cake\Datasource\ResultSetDecorator $entities Entities resultset
     * @return void
     */
    public function afterLookup(Event $event, ResultSetDecorator $entities)
    {
        if ($entities->isEmpty()) {
            return;
        }

        $table = $event->subject()->{$event->subject()->name};

        // Properly populate display values for the found entries.
        // This will recurse into related modules and get display
        // values as deep as needed.
        $fhf = new FieldHandlerFactory();

        $tableConfig = [];
        if (method_exists($table, 'getConfig') && is_callable([$table, 'getConfig'])) {
            $tableConfig = $table->getConfig();
        }

        $result = [];
        foreach ($entities as $k => $v) {
            $result[$k] = '';
            if (!empty($tableConfig['parent']['module'])) {
                $result[$k] .= $this->_prependParentModule($table, $k);
            }
            $result[$k] .= $fhf->renderValue(
                $table,
                $table->displayField(),
                $v,
                ['renderAs' => RelatedFieldHandler::RENDER_PLAIN_VALUE]
            );
        }

        $event->result = $result;
    }

    /**
     * Get module's type-ahead fields.
     *
     * @param \Cake\ORM\Table $table Table instance
     * @return array
     */
    protected function _getTypeaheadFields(Table $table)
    {
        // Get typeahead fields from configuration
        $result = [];
        if (method_exists($table, 'typeaheadFields') && is_callable([$table, 'typeaheadFields'])) {
            $result = $table->typeaheadFields();
        }
        // If there are no typeahead fields configured, use displayFields()
        if (empty($result)) {
            $result[] = $table->displayField();
        }

        foreach ($result as &$typeaheadField) {
            $typeaheadField = $table->aliasField($typeaheadField);
        }

        return $result;
    }

    /**
     * Get order by fields for lookup Query.
     *
     * @param \Cake\ORM\Table $table Table instance
     * @param \Cake\ORM\Query $query ORM Query
     * @param array $fields Optional fields to be used in order by clause
     * @return array
     */
    protected function _getOrderByFields(Table $table, Query $query, array $fields = [])
    {
        $tableConfig = [];
        if (method_exists($table, 'getConfig') && is_callable([$table, 'getConfig'])) {
            $tableConfig = $table->getConfig();
        }

        if (empty($tableConfig['parent']['module'])) {
            return $fields;
        }

        // order by parent module
        foreach ($table->associations() as $association) {
            if ($association->className() !== $tableConfig['parent']['module']) {
                continue;
            }

            $targetTable = $association->target();
            $primaryKey = $targetTable->aliasField($association->primaryKey());
            $foreignKey = $table->aliasField($association->foreignKey());
            // join parent table
            $query->join([
                'table' => 'projects',
                'alias' => $association->name(),
                'type' => 'INNER',
                'conditions' => $foreignKey . ' = ' . $primaryKey . ' OR ' . $foreignKey . ' IS NULL'
            ]);

            // add parent display field to order-by fields
            array_unshift($fields, $targetTable->aliasField($targetTable->displayField()));
            break;
        }

        return $fields;
    }

    /**
     * Prepend parent module display field value to resultset.
     *
     * @param \Cake\ORM\Table $table Table instance
     * @param string $id uuid
     * @return array
     */
    protected function _prependParentModule(Table $table, $id)
    {
        $result = '';
        $parentProperties = $this->_getRelatedParentProperties(
            $this->_getRelatedProperties($table->registryAlias(), $id)
        );

        if (empty($parentProperties['dispFieldVal'])) {
            return $result;
        }

        $result = $parentProperties['dispFieldVal'] . ' ' . $this->_separator . ' ';

        return $result;
    }
}
