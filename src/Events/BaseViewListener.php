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
use CsvMigrations\Parser\Csv\ViewParser;
use CsvMigrations\PrettifyTrait;
use InvalidArgumentException;

abstract class BaseViewListener implements EventListenerInterface
{
    use PrettifyTrait;

    /**
     * Datatables format identifier
     */
    const FORMAT_DATATABLES = 'datatables';

    /**
     * Here we list forced associations when fetching record(s) associated data.
     * This is useful, for example, when trying to fetch a record(s) associated
     * documents (such as photos, pdfs etc), which are nested two levels deep.
     *
     * To detect these nested associations, since our association names
     * are constructed dynamically, we use the associations class names
     * as identifiers.
     *
     * @var array
     */
    protected $_nestedAssociations = [];

    /**
     * Nested association chain for retrieving associated file records
     *
     * @var array
     */
    protected $_fileAssociations = [
        ['Documents', 'Files', 'Burzum/FileStorage.FileStorage']
    ];

    /**
     * Wrapper method that checks if Table instance has method 'findByLookupFields'
     * and if it does, it calls it, passing along the required arguments.
     *
     * @param  \Cake\ORM\Entity  $entity Entity
     * @param  \Cake\Event\Event $event  Event instance
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

        $path = Configure::read('CsvMigrations.views.path') . $controller . DS . $action . '.csv';

        try {
            $parser = new ViewParser();
            $result = $parser->parseFromPath($path);
        } catch (InvalidArgumentException $e) {
            Log::error($e);
        }

        return $result;
    }

    /**
     * Method responsible for retrieving current Table's associations
     *
     * @param  Cake\Event\Event $event Event instance
     * @return array
     */
    protected function _getAssociations(Event $event)
    {
        $result = [];

        if (!$event->subject()->request->query('associated')) {
            return $result;
        }

        $table = $event->subject()->{$event->subject()->name};
        $associations = $table->associations();

        if (empty($associations)) {
            return $result;
        }

        $result = $this->_containAssociations(
            $associations,
            $this->_nestedAssociations
        );

        // always include file associations
        $result = array_merge(
            $result,
            $this->_containAssociations(
                $associations,
                $this->_fileAssociations,
                true
            )
        );

        return $result;
    }

    /**
     * Method that retrieve's Table association names
     * to be passed to the ORM Query.
     *
     * Nested associations can travel as many levels deep
     * as defined in the parameter array. Using the example
     * array below, our code will look for a direct association
     * with class name 'Documents'. If found, it will add the
     * association's name to the result array and it will loop
     * through its associations to look for a direct association
     * with class name 'Files'. If found again, it will add it to
     * the result array (nested within the Documents association name)
     * and will carry on until it runs out of nesting levels or
     * matching associations.
     *
     * Example array:
     * ['Documents', 'Files', 'Burzum/FileStorage.FileStorage']
     *
     * Example result:
     * [
     *     'PhotosDocuments' => [
     *         'DocumentIdFiles' => [
     *             'FileIdFileStorageFileStorage' => []
     *         ]
     *     ]
     * ]
     *
     * @param  Cake\ORM\AssociationCollection $associations       Table associations
     * @param  array                          $nestedAssociations Nested associations
     * @param  bool                           $onlyNested         Flag for including only nested associations
     * @return array
     */
    protected function _containAssociations(
        AssociationCollection $associations,
        array $nestedAssociations = [],
        $onlyNested = false
    ) {
        $result = [];

        foreach ($associations as $association) {
            if (!$onlyNested) {
                $result[$association->name()] = [];
            }

            if (empty($nestedAssociations)) {
                continue;
            }

            foreach ($nestedAssociations as $levels) {
                if (current($levels) !== $association->className()) {
                    continue;
                }

                if (!next($levels)) {
                    continue;
                }

                $result[$association->name()] = $this->_containNestedAssociations(
                    $association->target()->associations(),
                    array_slice($levels, key($levels))
                );
            }
        }

        return $result;
    }

    /**
     * Method that retrieve's Table association nested associations
     * names to be passed to the ORM Query.
     *
     * @param  Cake\ORM\AssociationCollection $associations Table associations
     * @param  array                          $levels       Nested associations
     * @return array
     */
    protected function _containNestedAssociations(AssociationCollection $associations, array $levels)
    {
        $result = [];
        foreach ($associations as $association) {
            if (current($levels) !== $association->className()) {
                continue;
            }
            $result[$association->name()] = [];

            if (!next($levels)) {
                continue;
            }

            $result[$association->name()] = $this->_containNestedAssociations(
                $association->target()->associations(),
                array_slice($levels, key($levels))
            );
        }

        return $result;
    }
}
