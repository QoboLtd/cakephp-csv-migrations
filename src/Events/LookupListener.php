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

        $query->order($this->_getOrderByFields($table, $query, $fields));

        $this->_joinParentTables($table, $query);

        if (empty($fields)) {
            return;
        }

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

        foreach ($entities as $id => $label) {
            $event->result[$id] = $fhf->renderValue(
                $table,
                $table->displayField(),
                $label,
                ['renderAs' => RelatedFieldHandler::RENDER_PLAIN_VALUE]
            );
        }

        $parentModule = $this->_getParentModule($table);
        if (empty($parentModule)) {
            return;
        }

        foreach ($event->result as $id => &$label) {
            $label = $this->_prependParentModule($table->registryAlias(), $parentModule, $id, $label);
        }
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
        $parentModule = $this->_getParentModule($table);

        if (empty($parentModule)) {
            return $fields;
        }

        $parentAssociation = null;
        foreach ($table->associations() as $association) {
            if ($association->className() !== $parentModule) {
                continue;
            }
            $parentAssociation = $association;
            break;
        }

        if (is_null($parentAssociation)) {
            return $fields;
        }

        $targetTable = $parentAssociation->target();

        // add parent display field to order-by fields
        array_unshift($fields, $targetTable->aliasField($targetTable->displayField()));

        $fields = $this->_getOrderByFields($targetTable, $query, $fields);

        return $fields;
    }

    /**
     * Join parent modules.
     *
     * @param \Cake\ORM\Table $table Table instance
     * @param \Cake\ORM\Query $query ORM Query
     * @return void
     */
    protected function _joinParentTables(Table $table, Query $query)
    {
        $parentModule = $this->_getParentModule($table);

        if (empty($parentModule)) {
            return;
        }

        $parentAssociation = null;
        foreach ($table->associations() as $association) {
            if ($association->className() !== $parentModule) {
                continue;
            }
            $parentAssociation = $association;
            break;
        }

        if (is_null($parentAssociation)) {
            return;
        }

        $targetTable = $parentAssociation->target();
        $primaryKey = $targetTable->aliasField($parentAssociation->primaryKey());
        $foreignKey = $table->aliasField($parentAssociation->foreignKey());

        // join parent table
        $query->join([
            'table' => $targetTable->table(),
            'alias' => $parentAssociation->name(),
            'type' => 'INNER',
            'conditions' => $foreignKey . ' = ' . $primaryKey . ' OR ' . $foreignKey . ' IS NULL'
        ]);

        $this->_joinParentTables($targetTable, $query);
    }

    /**
     * Returns parent module name for provided Table instance.
     * If parent module is not defined then it returns null.
     *
     * @param \Cake\ORM\Table $table Table instance
     * @return string|null
     */
    protected function _getParentModule(Table $table)
    {
        if (!method_exists($table, 'getConfig') || !is_callable([$table, 'getConfig'])) {
            return null;
        }

        $tableConfig = $table->getConfig();
        if (empty($tableConfig['parent']['module'])) {
            return null;
        }

        return $tableConfig['parent']['module'];
    }

    /**
     * Prepend parent module display field to label.
     *
     * @param string $tableName Table name
     * @param string $parentModule Parent module name
     * @param string $id uuid
     * @param string $label Label
     * @return array
     */
    protected function _prependParentModule($tableName, $parentModule, $id, $label)
    {
        $properties = $this->_getRelatedParentProperties(
            $this->_getRelatedProperties($tableName, $id)
        );

        if (empty($properties['dispFieldVal'])) {
            return $label;
        }

        $prefix = $properties['dispFieldVal'] . ' ' . $this->_separator . ' ';

        if (empty($properties['config']['parent']['module']) || empty($properties['id'])) {
            return $prefix . $label;
        }

        $prefix = $this->_prependParentModule(
            $parentModule,
            $properties['config']['parent']['module'],
            $properties['id'],
            $prefix
        );

        return $prefix . $label;
    }
}
