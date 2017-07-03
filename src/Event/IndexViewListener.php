<?php
namespace CsvMigrations\Event;

use App\View\AppView;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\Network\Request;
use Cake\ORM\Query;
use Cake\ORM\ResultSet;
use CsvMigrations\ConfigurationTrait;
use CsvMigrations\Event\BaseViewListener;

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
        $table = $event->subject()->{$event->subject()->name};
        $request = $event->subject()->request;

        if (!in_array($request->query('format'), [static::FORMAT_PRETTY, static::FORMAT_DATATABLES])) {
            $query->contain($this->_getFileAssociations($table));
        }
        $this->_filterByConditions($query, $event);
        $this->_selectActionFields($query, $event);
        $this->_handleDtSorting($query, $event);
    }

    /**
     * {@inheritDoc}
     */
    public function afterPaginate(Event $event, ResultSet $entities)
    {
        if ($entities->isEmpty()) {
            return;
        }

        $table = $event->subject()->{$event->subject()->name};
        $request = $event->subject()->request;

        foreach ($entities as $entity) {
            $this->_resourceToString($entity);
        }

        if (in_array($request->query('format'), [static::FORMAT_PRETTY, static::FORMAT_DATATABLES])) {
            $fields = [];
            if (static::FORMAT_DATATABLES === $request->query('format')) {
                $fields = $this->_getActionFields($event->subject()->request);
            }

            foreach ($entities as $entity) {
                $this->_prettify($entity, $table, $fields);
            }
        } else { // @todo temporary functionality, please see _includeFiles() method documentation.
            foreach ($entities as $entity) {
                $this->_restructureFiles($entity, $table);
            }
        }
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

    /**
     * {@inheritDoc}
     */
    protected function _getActionFields(Request $request, $action = null)
    {
        $fields = parent::_getActionFields($request, $action);

        if (empty($fields)) {
            return $fields;
        }

        foreach ($fields as &$field) {
            $field = current((array)$field);
        }

        return $fields;
    }

    /**
     * Method that adds SELECT clause to the Query to only include
     * action fields (as defined in the csv file).
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

        $query->select($this->_databaseFields($fields, $event), true);
    }

    /**
     * Handle datatables sorting parameters to match Query order() accepted parameters.
     *
     * @param  \Cake\ORM\Query   $query Query object
     * @param  \Cake\Event\Event $event The event
     * @return void
     */
    protected function _handleDtSorting(Query $query, Event $event)
    {
        if (!in_array($event->subject()->request->query('format'), [static::FORMAT_DATATABLES])) {
            return;
        }

        if (!$event->subject()->request->query('order')) {
            return;
        }

        $table = $event->subject()->{$event->subject()->name};

        $sortCol = $event->subject()->request->query('order.0.column') ?: 0;

        $sortDir = $event->subject()->request->query('order.0.dir') ?: 'asc';
        if (!in_array($sortDir, ['asc', 'desc'])) {
            $sortDir = 'asc';
        }

        $fields = $this->_getActionFields($event->subject()->request);
        if (empty($fields)) {
            return;
        }

        // skip if sort column is not found in the action fields
        if (!isset($fields[$sortCol])) {
            return;
        }

        $sortCols = $fields[$sortCol];
        // handle virtual field
        if (method_exists($table, 'getConfig') && is_callable([$table, 'getConfig'])) {
            $virtualFields = $table->getConfig(ConfigurationTrait::$CONFIG_OPTION_VIRTUAL_FIELDS);
            if (!empty($virtualFields) && isset($virtualFields[$sortCols])) {
                $sortCols = $virtualFields[$sortCols];
            }
        }
        $sortCols = (array)$sortCols;

        // prefix table name
        foreach ($sortCols as &$v) {
            $v = $table->aliasField($v);
        }

        // add sort direction to all columns
        $conditions = array_fill_keys($sortCols, $sortDir);

        $query->order($conditions);
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

        $appView = new AppView();
        $plugin = $event->subject()->request->plugin;
        $controller = $event->subject()->request->controller;
        $displayField = $event->subject()->{$event->subject()->name}->displayField();

        $appView->element('CsvMigrations.Menu/index_actions', [
            'plugin' => $event->subject()->request->plugin,
            'controller' => $event->subject()->request->controller,
            'displayField' => $displayField,
            'entities' => $entities,
            'user' => $event->subject()->Auth->user(),
            'propertyName' => static::MENU_PROPERTY_NAME
        ]);
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

        foreach ($entities as $entity) {
            $savedEntity = $entity->toArray();
            // remove non-action fields property
            foreach (array_diff($entity->visibleProperties(), $fields) as $field) {
                $entity->unsetProperty($field);
            }

            // set fields with numeric index
            foreach ($fields as $k => $v) {
                $entity->{$k} = $savedEntity[$v];
                $entity->unsetProperty($v);
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
