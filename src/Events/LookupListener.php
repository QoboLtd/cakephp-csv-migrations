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
        if (!empty($request->query['query'])) {
            $table = $event->subject()->{$event->subject()->name};

            $typeaheadFields = [];
            // Get typeahead fields from configuration
            if (method_exists($table, 'typeaheadFields') && is_callable([$table, 'typeaheadFields'])) {
                $typeaheadFields = $table->typeaheadFields();
            }
            // If there are no typeahead fields configured, use displayFields()
            if (empty($typeaheadFields)) {
                $typeaheadFields[] = $table->displayField();
            }

            if (!empty($typeaheadFields)) {
                foreach ($typeaheadFields as $field) {
                    $query->orWhere([$field . ' LIKE' => '%' . $request->query['query'] . '%']);
                }
            } else {
                throw new RuntimeException("No typeahead or display field configured for " . $table->alias());
            }
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
