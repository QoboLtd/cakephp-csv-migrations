<?php
namespace CsvMigrations\Controller\Api;

use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Datasource\ResultSetDecorator;
use Cake\Event\Event;
use Cake\ORM\Entity;
use Crud\Controller\ControllerTrait;
use CsvMigrations\FieldHandlers\RelatedFieldTrait;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;
use CsvMigrations\CsvTrait;
use CsvMigrations\MigrationTrait;

class AppController extends Controller
{
    /**
     * Pretty format identifier
     */
    const FORMAT_PRETTY = 'pretty';

    use ControllerTrait;
    use MigrationTrait;
    use RelatedFieldTrait;

    public $components = [
        'RequestHandler',
        'Crud.Crud' => [
            'actions' => [
                'Crud.Index',
                'Crud.View',
                'Crud.Add',
                'Crud.Edit',
                'Crud.Delete',
                'Crud.Lookup'
            ],
            'listeners' => [
                'Crud.Api',
                'Crud.ApiPagination',
                'Crud.ApiQueryLog'
            ]
        ]
    ];

    public $paginate = [
        'page' => 1,
        'limit' => 10,
        'maxLimit' => 100,
    ];

    /**
     * View CRUD action events handling logic.
     *
     * @return \Cake\Network\Response
     */
    public function view()
    {
        $this->Crud->on('beforeFind', function(Event $event) {
            $uniqueFields = $event->subject()->repository->getUniqueFields();

            /**
             * check for record by table's unique fields (not only by id)
             * @todo currently if two unique fields have the same value the query will only return the first one
             */
            foreach ($uniqueFields as $uniqueField) {
                $event->subject()->query->orWhere([$uniqueField => $event->subject()->id]);
            }
        });

        $this->Crud->on('afterFind', function(Event $event) {
            if (static::FORMAT_PRETTY === $this->request->query('format')) {
                $table = $event->subject()->repository->registryAlias();
                $fields = $this->getFieldsDefinitions($event->subject()->repository->alias());
                $fhf = new FieldHandlerFactory();
                $entity = $event->subject()->entity;
                $event->subject()->entity = $this->_getPrettyValues($entity, $table, $fields, $fhf);
            }
        });

        return $this->Crud->execute();
    }

    /**
     * Index CRUD action events handling logic.
     *
     * @return \Cake\Network\Response
     */
    public function index()
    {
        $this->Crud->on('afterPaginate', function(Event $event) {
            if (static::FORMAT_PRETTY === $this->request->query('format')) {
                $table = $event->subject()->query->repository()->registryAlias();
                $fields = $this->getFieldsDefinitions($event->subject()->query->repository()->alias());
                $fhf = new FieldHandlerFactory();
                foreach ($event->subject()->entities as $entity) {
                    $entity = $this->_getPrettyValues($entity, $table, $fields, $fhf);
                }
            }
        });

        return $this->Crud->execute();
    }

    /**
     * Lookup CRUD action events handling logic.
     *
     * @return \Cake\Network\Response
     */
    public function lookup()
    {
        $this->Crud->on('beforeLookup', function (Event $event) {
            if (!empty($this->request->query['query'])) {
                $displayField = $this->{$this->name}->displayField();
                // $primaryKey = $this->{$this->name}->primaryKey();
                $this->paginate['conditions'] = [$displayField . ' LIKE' => '%' . $this->request->query['query'] . '%'];
            }
        });

        $this->Crud->on('afterLookup', function(Event $event) {
            $tableConfig = [];
            if (method_exists($this->{$this->name}, 'getConfig') && is_callable([$this->{$this->name}, 'getConfig'])) {
                $tableConfig = $this->{$this->name}->getConfig();
            }

            if (!empty($tableConfig['parent']['module'])) {
                $event->subject()->entities = $this->_prependParentModule($event->subject()->entities);
            }
        });

        return $this->Crud->execute();
    }

    /**
     * Prepend parent module display field value to resultset.
     *
     * @param  \Cake\Datasource\ResultSetDecorator $entities
     * @return array
     */
    protected function _prependParentModule(ResultSetDecorator $entities)
    {
        $result = $entities->toArray();

        foreach ($result as $id => &$value) {
            $parentProperties = $this->_getRelatedParentProperties(
                 $this->_getRelatedProperties($this->{$this->name}->registryAlias(), $id)
            );
            if (!empty($parentProperties['dispFieldVal'])) {
                $value = implode(' ' . $this->_separator . ' ', [
                    $parentProperties['dispFieldVal'],
                    $value
                ]);
            }
        }

        return $result;
    }

    /**
     * Before filter handler.
     *
     * @param  \Cake\Event\Event $event The event.
     * @return mixed
     * @link   http://book.cakephp.org/3.0/en/controllers/request-response.html#setting-cross-origin-request-headers-cors
     */
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);

        $this->response->cors($this->request)
            ->allowOrigin(['*'])
            ->allowMethods(['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'])
            ->allowHeaders(['X-CSRF-Token', 'Origin', 'X-Requested-With', 'Content-Type', 'Accept'])
            ->maxAge(300)
            ->build();

        // if request method is OPTIONS just return the response with appropriate headers.
        if ('OPTIONS' === $this->request->method()) {
            return $this->response;
        }
    }

    /**
     * Method that renders Entity values through Field Handler Factory.
     *
     * @param  Cake\ORM\Entity     $entity    Entity instance
     * @param  [type]              $tableName Table name
     * @param  array               $fields    Migration fields
     * @param  FieldHandlerFactory $fhf       Field Handler Factory instance
     * @return Cake\ORM\Entity
     */
    protected function _getPrettyValues(Entity $entity, $tableName, array $fields, FieldHandlerFactory $fhf)
    {
        foreach ($fields as $field => $definitions) {
            $renderOptions = ['entity' => $entity];
            $entity->{$field} = $fhf->renderValue(
                $tableName,
                $field,
                $entity->{$field},
                $renderOptions
            );
        }

        return $entity;
    }
}
