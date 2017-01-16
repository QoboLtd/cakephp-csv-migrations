<?php
namespace CsvMigrations\Events;

use Cake\Datasource\ResultSetDecorator;
use Cake\Event\Event;
use Cake\ORM\Query;
use CsvMigrations\Events\BaseViewListener;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;
use CsvMigrations\FieldHandlers\RelatedFieldHandler;
use RuntimeException;

class LookupListener extends BaseViewListener
{
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

        $controller = $event->subject();

        // Properly populate display values for the found entries.
        // This will recurse into related modules and get display
        // values as deep as needed.
        $result = $entities->toArray();
        foreach ($result as &$entity) {
            $fhf = new FieldHandlerFactory();
            // We need plain display value. It'll be properly wrapped
            // in styling only at the top level.
            $entity = $fhf->renderValue(
                $controller->{$controller->name},
                $controller->{$controller->name}->displayField(),
                $entity,
                ['renderAs' => RelatedFieldHandler::RENDER_PLAIN_VALUE]
            );
        }

        $entities = $result;
    }
}
