<?php
namespace CsvMigrations\Events;

use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\Log\Log;
use Cake\Network\Request;
use Cake\ORM\AssociationCollection;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\Table;
use Cake\Utility\Inflector;
use CsvMigrations\FieldHandlers\CsvField;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;
use CsvMigrations\PrettifyTrait;
use InvalidArgumentException;
use Qobo\Utils\ModuleConfig\ModuleConfig;

abstract class BaseViewListener implements EventListenerInterface
{
    use PrettifyTrait;

    /**
     * Pretty format identifier
     */
    const FORMAT_PRETTY = 'pretty';

    /**
     * Datatables format identifier
     */
    const FORMAT_DATATABLES = 'datatables';

    /**
     * File association class name
     */
    const FILE_CLASS_NAME = 'Burzum/FileStorage.FileStorage';

    /**
     * Current module fields list which are associated with files
     *
     * @var array
     */
    protected $_fileAssociationFields = [];

    /**
     * Wrapper method that checks if Table instance has method 'findByLookupFields'
     * and if it does, it calls it, passing along the required arguments.
     *
     * @param  \Cake\ORM\Query   $query the Query
     * @param  \Cake\Event\Event $event Event instance
     * @return void
     */
    protected function _lookupFields(Query $query, Event $event)
    {
        $methodName = 'findByLookupFields';
        $table = $event->subject()->{$event->subject()->name};
        if (!method_exists($table, $methodName) || !is_callable([$table, $methodName])) {
            return;
        }
        $id = $event->subject()->request['pass'][0];

        $table->{$methodName}($query, $id);
    }

    /**
     * Wrapper method that checks if Table instance has method 'setAssociatedByLookupFields'
     * and if it does, it calls it, passing along the required arguments.
     *
     * @param  \Cake\ORM\Entity  $entity Entity
     * @param  \Cake\Event\Event $event  Event instance
     * @return void
     */
    protected function _associatedByLookupFields(Entity $entity, Event $event)
    {
        $methodName = 'setAssociatedByLookupFields';
        $table = $event->subject()->{$event->subject()->name};
        if (!method_exists($table, $methodName) || !is_callable([$table, $methodName])) {
            return;
        }

        $table->{$methodName}($entity);
    }

    /**
     * Method that retrieves and returns csv migration fields.
     *
     * @param  Request $request Request object
     * @return array
     */
    protected function _getMigrationFields(Request $request)
    {
        $result = [];

        try {
            $mc = new ModuleConfig(ModuleConfig::CONFIG_TYPE_MIGRATION, $request->controller);
            $result = $mc->parse()->items;
        } catch (InvalidArgumentException $e) {
            Log::error($e);
        }

        return $result;
    }

    /**
     * Method that fetches action fields from the corresponding csv file.
     *
     * @param  \Cake\Network\Request $request Request object
     * @param  string                $action  Action name
     * @return array
     */
    protected function _getActionFields(Request $request, $action = null)
    {
        $result = [];

        $controller = $request->controller;

        if (is_null($action)) {
            $action = $request->action;
        }

        try {
            $mc = new ModuleConfig(ModuleConfig::CONFIG_TYPE_VIEW, $controller, $action);
            $result = $mc->parse()->items;
        } catch (InvalidArgumentException $e) {
            Log::error($e);
        }

        return $result;
    }

    /**
     * Method that converts csv action fields to database fields and returns their names.
     *
     * @param  array  $fields action fields
     * @param  Event  $event  Event instance
     * @return array
     */
    protected function _databaseFields(array $fields, Event $event)
    {
        $result = [];

        $table = $event->subject()->{$event->subject()->name};

        $migrationFields = $this->_getMigrationFields($event->subject()->request);
        if (empty($migrationFields)) {
            return $result;
        }

        $fhf = new FieldHandlerFactory();
        foreach ($fields as $field) {
            if (!array_key_exists($field, $migrationFields)) {
                $result[] = $field;
                continue;
            }

            $csvField = new CsvField($migrationFields[$field]);
            foreach ($fhf->fieldToDb($csvField, $table, $field) as $dbField) {
                $result[] = $dbField->getName();
            }
        }

        $virtualFields = $table->getVirtualFields();

        if (empty($virtualFields)) {
            return $result;
        }

        // handle virtual fields
        foreach ($fields as $k => $field) {
            if (!isset($virtualFields[$field])) {
                continue;
            }
            // remove virtual field
            unset($result[$k]);

            // add db fields
            $result = array_merge($result, $virtualFields[$field]);
        }

        return $result;
    }

    /**
     * Method for including files.
     *
     * @param \Cake\ORM\Entity $entity Entity
     * @param \Cake\ORM\Table $table Table instance
     * @return void
     * @todo this method is very hardcoded and has been added because of an issue with the soft delete
     *       plugin (https://github.com/UseMuffin/Trash), which affects contain() functionality with
     *       belongsTo associations. Once the issue is resolved this method can be removed.
     */
    protected function _restructureFiles(Entity $entity, Table $table)
    {
        $fileAssociationFields = $this->_getFileAssociationFields($table);
        foreach ($fileAssociationFields as $associationName => $fieldName) {
            $associatedFieldName = Inflector::underscore($associationName);

            $entity->set($fieldName, $entity->get($associatedFieldName));
            $entity->unsetProperty($associatedFieldName);
        }
    }

    /**
     * Method that generates property name for belongsTo and HasOne associations.
     *
     * @param  string $name Association name
     * @return string
     */
    protected function _associationPropertyName($name)
    {
        list(, $name) = pluginSplit($name);

        return Inflector::underscore(Inflector::singularize($name));
    }

    /**
     * Method responsible for retrieving current Table's file associations
     *
     * @param  Cake\ORM\Table $table Table instance
     * @return array
     */
    protected function _getFileAssociations(Table $table)
    {
        $result = [];

        foreach ($table->associations() as $association) {
            if (static::FILE_CLASS_NAME !== $association->className()) {
                continue;
            }

            $result[] = $association->name();
        }

        return $result;
    }

    /**
     * Method responsible for retrieving file associations field names
     *
     * @param  Cake\ORM\Table $table Table instance
     * @return array
     */
    protected function _getFileAssociationFields(Table $table)
    {
        $result = [];

        if (!empty($this->_fileAssociationFields)) {
            return $this->_fileAssociationFields;
        }

        foreach ($table->associations() as $association) {
            if (static::FILE_CLASS_NAME !== $association->className()) {
                continue;
            }
            $result[$association->name()] = $association->conditions()['model_field'];
        }

        $this->_fileAssociationFields = $result;

        return $this->_fileAssociationFields;
    }

    /**
     * Convert Entity resource values to strings.
     * Temporary fix for bug with resources and json_encode() (see link).
     *
     * @param  \Cake\ORM\Entity $entity Entity
     * @return void
     * @link   https://github.com/cakephp/cakephp/issues/9658
     */
    protected function _resourceToString(Entity $entity)
    {
        $fields = array_keys($entity->toArray());
        foreach ($fields as $field) {
            // handle belongsTo associated data
            if ($entity->{$field} instanceof Entity) {
                $this->_resourceToString($entity->{$field});
            }

            // handle hasMany associated data
            if (is_array($entity->{$field})) {
                if (empty($entity->{$field})) {
                    continue;
                }

                foreach ($entity->{$field} as $associatedEntity) {
                    if (!$associatedEntity instanceof Entity) {
                        continue;
                    }

                    $this->_resourceToString($associatedEntity);
                }
            }

            if (is_resource($entity->{$field})) {
                $entity->{$field} = stream_get_contents($entity->{$field});
            }
        }
    }
}
