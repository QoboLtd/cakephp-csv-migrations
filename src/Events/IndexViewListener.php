<?php
namespace CsvMigrations\Events;

use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\Network\Request;
use Cake\ORM\Query;
use Cake\ORM\ResultSet;
use CsvMigrations\Events\BaseViewListener;


class IndexViewListener extends BaseViewListener
{
    /**
     * Include menus identifier
     */
    const FLAG_INCLUDE_MENUS = 'menus';

    /**
     * Property name for menu items
     */
    const MENU_PROPERTY_NAME = '_Menus';

    /**
     * Pretty format identifier
     */
    const FORMAT_PRETTY = 'pretty';

    /**
     * {@inheritDoc}
     */
    public function implementedEvents()
    {
        return [
            'CsvMigrations.Index.beforePaginate' => 'beforePaginate',
            'CsvMigrations.Index.afterPaginate' => 'afterPaginate',
            'CsvMigrations.Index.beforeRender' => 'beforeRender'
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function beforePaginate(Event $event, Query $query)
    {
        $query->contain($this->_getAssociations($event));
        $this->_filterByConditions($query, $event);
        $this->_selectActionFields($query, $event);
    }

    /**
     * {@inheritDoc}
     */
    public function afterPaginate(Event $event, ResultSet $entities)
    {
        if ($entities->isEmpty()) {
            return;
        }

        $this->_prettifyEntities($entities, $event);
        $this->_includeMenus($entities, $event);
    }

    /**
     * {@inheritDoc}
     */
    public function beforeRender(Event $event, ResultSet $entities)
    {
        if ($entities->isEmpty()) {
            return;
        }

        $this->_datatablesStructure($entities, $event);
    }

    protected function _getActionFields(Request $request, $action = null)
    {
        $fields = parent::_getActionFields($request, $action);

        if (empty($fields)) {
            return $fields;
        }

        foreach ($fields as &$field) {
            if (!array($field)) {
                continue;
            }
            $field = current($field);
        }

        return $fields;
    }

    /**
     *
     *
     * @param  \Cake\ORM\Query   $query Query object
     * @param  \Cake\Event\Event $event The event
     * @return void
     */
    protected function _selectActionFields(Query $query, Event $event)
    {
        if (!in_array($event->subject()->request->query('format'), [static::FORMAT_DATATABLES])) {
            return;
        }

        $fields = $this->_getActionFields($event->subject()->request);

        if (empty($fields)) {
            return;
        }

        $primaryKey = $event->subject()->{$event->subject()->name}->primaryKey();
        // always include primary key, useful for menus URLs
        if (!in_array($primaryKey, $fields)) {
            array_push($fields, $primaryKey);
        }

        $query->select($fields, true);
    }

    /**
     * Method that prepares entities to run through pretiffy logic.
     *
     * @param  \Cake\ORM\ResultSet $entities Entities
     * @param  \Cake\Event\Event   $event    Event instance
     * @return void
     */
    protected function _prettifyEntities(ResultSet $entities, Event $event)
    {
        if (!in_array($event->subject()->request->query('format'), [static::FORMAT_PRETTY, static::FORMAT_DATATABLES])) {
            return;
        }

        $fields = [];
        if (static::FORMAT_DATATABLES === $event->subject()->request->query('format')) {
            $fields = $this->_getActionFields($event->subject()->request);
        }

        $tableName = $event->subject()->name;
        foreach ($entities as $entity) {
            $this->_prettify($entity, $tableName, $fields);
        }
    }

    /**
     * Method that retrieves and attaches menu elements to API response.
     *
     * @param  \Cake\ORM\ResultSet $entities Entities
     * @param  \Cake\Event\Event   $event    Event instance
     * @return void
     */
    protected function _includeMenus(ResultSet $entities, Event $event)
    {
        if (!$event->subject()->request->query(static::FLAG_INCLUDE_MENUS)) {
            return;
        }

        foreach ($entities as $entity) {
            // broadcast menu event
            $ev = new Event('View.Index.Menu.Actions', $event->subject(), [
                'request' => $event->subject()->request,
                'options' => $entity
            ]);
            EventManager::instance()->dispatch($ev);

            $entity->{static::MENU_PROPERTY_NAME} = $ev->result;
        }
    }

    /**
     * Method that re-formats entities to Datatables supported format.
     *
     * @param  \Cake\ORM\ResultSet $entities Entities
     * @param  \Cake\Event\Event   $event    Event instance
     * @return void
     */
    protected function _datatablesStructure(ResultSet $entities, Event $event)
    {
        if (static::FORMAT_DATATABLES !== $event->subject()->request->query('format')) {
            return;
        }

        $fields = $this->_getActionFields($event->subject()->request);

        if (empty($fields)) {
            return;
        }

        $fields[] = static::MENU_PROPERTY_NAME;

        $primaryKey = $event->subject()->{$event->subject()->name}->primaryKey();
        $unsetPrimaryKey = !in_array($primaryKey, $fields);
        foreach ($entities as $entity) {
            foreach ($fields as $k => $v) {
                $entity->{$k} = $entity->{$v};
                $entity->unsetProperty($v);
            }

            if ($unsetPrimaryKey) {
                $entity->unsetProperty($primaryKey);
            }
        }
    }

    /**
     * Method that filters ORM records by provided conditions.
     *
     * @param  \Cake\ORM\Query   $query Query object
     * @param  \Cake\Event\Event $event The event
     * @return void
     */
    protected function _filterByConditions(Query $query, Event $event)
    {
        if (empty($event->subject()->request->query('conditions'))) {
            return;
        }

        $conditions = [];
        $tableName = $event->subject()->name;
        foreach ($event->subject()->request->query('conditions') as $k => $v) {
            if (false === strpos($k, '.')) {
                $k = $tableName . '.' . $k;
            }

            $conditions[$k] = $v;
        };

        $query->applyOptions(['conditions' => $conditions]);
    }
}
