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
namespace CsvMigrations\Event\Controller\Api;

use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\Network\Request;
use Cake\ORM\Query;
use Cake\ORM\ResultSet;
use Cake\View\View;
use CsvMigrations\ConfigurationTrait;
use CsvMigrations\Event\EventName;
use CsvMigrations\FieldHandlers\CsvField;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;

class IndexActionListener extends BaseActionListener
{
    /**
     * Include menus identifier
     */
    const FLAG_INCLUDE_MENUS = 'menus';

    /**
     * Include primary key
     */
    const FLAG_INCLUDE_PRIMARY_KEY = 'primary_key';

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
            (string)EventName::API_INDEX_BEFORE_PAGINATE() => 'beforePaginate',
            (string)EventName::API_INDEX_AFTER_PAGINATE() => 'afterPaginate',
            (string)EventName::API_INDEX_BEFORE_RENDER() => 'beforeRender'
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

        if ((bool)$event->subject()->request->query(static::FLAG_INCLUDE_MENUS)) {
            $this->_includeMenus($entities, $event);
        }
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

        $column = $event->subject()->request->query('order.0.column') ?: 0;

        $direction = $event->subject()->request->query('order.0.dir') ?: 'asc';
        if (!in_array($direction, ['asc', 'desc'])) {
            $direction = 'asc';
        }

        $fields = $this->_getActionFields($event->subject()->request);
        if (empty($fields)) {
            return;
        }

        // skip if sort column is not found in the action fields
        if (!isset($fields[$column])) {
            return;
        }

        $column = $fields[$column];

        $schema = $table->getSchema();
        // virtual or combined field
        if (!in_array($column, $schema->columns())) {
            $mc = new ModuleConfig(ConfigType::MODULE(), $event->subject()->name);
            $config = $mc->parse();
            $virtualFields = (array)$config->virtualFields;
            // handle virtual field
            if (isset($virtualFields[$column])) {
                $column = $virtualFields[$column];
            }

            // handle combined field
            if (!isset($virtualFields[$column])) {
                $factory = new FieldHandlerFactory();
                $mc = new ModuleConfig(ConfigType::MIGRATION(), $event->subject()->name);
                $config = $mc->parse();
                $csvField = new CsvField((array)$config->{$column});

                $combined = [];
                foreach ($factory->fieldToDb($csvField, $table, $column) as $dbField) {
                    $combined[] = $dbField->getName();
                }

                $column = $combined;
            }
        }

        $columns = (array)$column;

        // prefix table name
        foreach ($columns as &$v) {
            $v = $table->aliasField($v);
        }

        // add sort direction to all columns
        $conditions = array_fill_keys($columns, $direction);

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
        $appView = new View();
        $plugin = $event->subject()->request->plugin;
        $controller = $event->subject()->request->controller;
        $displayField = $event->subject()->{$event->subject()->name}->displayField();

        foreach ($entities as $entity) {
            $entity->{static::MENU_PROPERTY_NAME} = $appView->element('CsvMigrations.Menu/index_actions', [
                'plugin' => $event->subject()->request->plugin,
                'controller' => $event->subject()->request->controller,
                'displayField' => $displayField,
                'entity' => $entity,
                'user' => $event->subject()->Auth->user()
            ]);
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

        // include primary key to the response fields
        if ((bool)$event->subject()->request->query(static::FLAG_INCLUDE_PRIMARY_KEY)) {
            array_unshift($fields, $event->subject()->{$event->subject()->name}->primaryKey());
        }

        // include actions menu to the response fields
        if ((bool)$event->subject()->request->query(static::FLAG_INCLUDE_MENUS)) {
            $fields[] = static::MENU_PROPERTY_NAME;
        }

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
