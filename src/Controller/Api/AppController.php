<?php
namespace CsvMigrations\Controller\Api;

use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Datasource\ResultSetDecorator;
use Cake\Event\Event;
use Cake\ORM\AssociationCollection;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Crud\Controller\ControllerTrait;
use CsvMigrations\FieldHandlers\RelatedFieldTrait;
use CsvMigrations\FileUploadsUtils;
use CsvMigrations\MigrationTrait;
use CsvMigrations\Panel;
use CsvMigrations\PanelUtilTrait;
use CsvMigrations\PrettifyTrait;

class AppController extends Controller
{
    /**
     * Pretty format identifier
     */
    const FORMAT_PRETTY = 'pretty';

    use ControllerTrait;
    use MigrationTrait;
    use PanelUtilTrait;
    use PrettifyTrait;
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

    protected $_fileUploadsUtils;

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
     * {@inheritDoc}
     */
    public function initialize()
    {
        parent::initialize();

        $this->_fileUploadsUtils = new FileUploadsUtils($this->{$this->name});

        if (Configure::read('API.auth')) {
            // @link http://www.bravo-kernel.com/2015/04/how-to-add-jwt-authentication-to-a-cakephp-3-rest-api/
            $this->loadComponent('Auth', [
                // non-persistent storage, for stateless authentication
                'storage' => 'Memory',
                'authenticate' => [
                    // used for validating user credentials before the token is generated
                    'Form' => [
                        'scope' => ['Users.active' => 1]
                    ],
                    // used for token validation
                    'ADmad/JwtAuth.Jwt' => [
                        'parameter' => 'token',
                        'userModel' => 'Users',
                        'scope' => ['Users.active' => 1],
                        'fields' => [
                            'username' => 'id'
                        ],
                        'queryDatasource' => true
                    ]
                ],
                'unauthorizedRedirect' => false,
                'checkAuthIn' => 'Controller.initialize'
            ]);
        }
    }

    /**
     * View CRUD action events handling logic.
     *
     * @return \Cake\Network\Response
     */
    public function view()
    {
        $this->Crud->on('beforeFind', function (Event $event) {
            $event->subject()->repository->findByLookupFields($event->subject()->query, $event->subject()->id);

            $event->subject()->query->contain($this->_getAssociations($event));
        });

        $this->Crud->on('afterFind', function (Event $event) {
            $event = $this->_prettifyEntity($event);
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
        $this->Crud->on('beforePaginate', function (Event $event) {
            $event->subject()->query->contain($this->_getAssociations($event));
            $event = $this->_filterByConditions($event);
        });

        $this->Crud->on('afterPaginate', function (Event $event) {
            $event = $this->_prettifyEntity($event);
        });

        return $this->Crud->execute();
    }

    /**
     * Add CRUD action events handling logic.
     *
     * @return \Cake\Network\Response
     */
    public function add()
    {
        $this->Crud->on('beforeSave', function (Event $event) {
            // get Entity's Table instance
            $table = TableRegistry::get($event->subject()->entity->source());
            $table->setAssociatedByLookupFields($event->subject()->entity);
        });

        $this->Crud->on('afterSave', function (Event $event) {
            // handle file uploads if found in the request data
            if (isset($this->request->data['file'])) {
                $this->_fileUploadsUtils->save($event->subject()->entity, $this->request->data['file']);
            }
        });

        return $this->Crud->execute();
    }

    /**
     * Edit CRUD action events handling logic.
     *
     * @return \Cake\Network\Response
     */
    public function edit()
    {
        $this->Crud->on('beforeFind', function (Event $event) {
            $event->subject()->repository->findByLookupFields($event->subject()->query, $event->subject()->id);
        });

        $this->Crud->on('afterFind', function (Event $event) {
            $event = $this->_prettifyEntity($event);
        });

        $this->Crud->on('beforeSave', function (Event $event) {
            $event->subject()->repository->setAssociatedByLookupFields($event->subject()->entity);
        });

        $this->Crud->on('afterSave', function (Event $event) {
            // handle file uploads if found in the request data
            if (isset($this->request->data['file'])) {
                $this->_fileUploadsUtils->save($event->subject()->entity, $this->request->data['file']);
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
                $typeaheadFields = [];

                // Get typeahead fields from configuration
                if (method_exists($this->{$this->name}, 'typeaheadFields') && is_callable([$this->{$this->name}, 'typeaheadFields'])) {
                    $typeaheadFields = $this->{$this->name}->typeaheadFields();
                }

                // If there are no typeahead fields configured, use displayFields()
                if (empty($typeaheadFields)) {
                    $typeaheadFields[] = $this->{$this->name}->displayField();
                }

                $conditions = [];
                if (count($typeaheadFields) > 1) {
                    $conditions['OR'] = [];
                    foreach ($typeaheadFields as $field) {
                        $conditions['OR'][] = [ $field . ' LIKE' => '%' . $this->request->query['query'] . '%'];
                    }
                } elseif (count($typeaheadFields) == 1) {
                        $conditions[] = [ $typeaheadFields[0] . ' LIKE' => '%' . $this->request->query['query'] . '%'];
                } else {
                    throw new \RuntimeException("No typeahead or display field configured for " . $this->name);
                }

                $this->paginate['conditions'] = $conditions;
            }
        });

        $this->Crud->on('afterLookup', function (Event $event) {
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
     * @param  \Cake\Datasource\ResultSetDecorator $entities Entities
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
     * Panels to show.
     *
     * @return array|void
     */
    public function panels()
    {
        $this->request->allowMethod(['ajax', 'post']);
        $result = [
            'success' => false,
            'data' => [],
        ];
        $table = $this->loadModel();
        $tableConfig = $table->getConfig();
        $data = $this->request->data;
        if (empty($data) || !is_array($data)) {
            return $result;
        }
        $key = key($data);
        if (is_array($data[$key])) {
            $innerKey = key($data[$key]);
            if (!is_array($data[$key][$innerKey])) {
                //Regular form format - [module][inputName]
                $data = $data[$key];
            } else {
                //Embedded form - [module][dynamicField][inputName]
                $data = $data[$key][$innerKey];
            }
        }
        $evalPanels = $this->getEvalPanels($tableConfig, $data);
        if (!empty($evalPanels)) {
            $result['success'] = true;
            $result['data'] = $evalPanels;
        }

        $this->set('result', $result);
        $this->set('_serialize', 'result');
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
     * Method that filters ORM records by provided conditions.
     *
     * @param  \Cake\Event\Event $event The event.
     * @return \Cake\Event\Event
     */
    protected function _filterByConditions(Event $event)
    {
        $conditions = $this->request->query('conditions');
        if (!is_null($conditions)) {
            $event->subject()->query->applyOptions(['conditions' => $conditions]);
        }

        return $event;
    }

    /**
     * Method that prepares entity(ies) to run through pretiffy logic.
     * It then returns the event object.
     *
     * @param  Cake\Event\Event $event Event instance
     * @return Cake\Event\Event
     */
    protected function _prettifyEntity(Event $event)
    {
        if (static::FORMAT_PRETTY === $this->request->query('format')) {
            $table = $event->subject()->query->repository()->registryAlias();
            $fields = array_keys($this->getFieldsDefinitions($event->subject()->query->repository()->alias()));

            if (isset($event->subject()->entities)) {
                foreach ($event->subject()->entities as $entity) {
                    $entity = $this->_prettify($entity, $table, $fields);
                }
            }

            if (isset($event->subject()->entity)) {
                $event->subject()->entity = $this->_prettify($event->subject()->entity, $table, $fields);
            }
        }

        return $event;
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

        $associations = $event->subject()->query->repository()->associations();

        if ($this->request->query('associated')) {
            $result = $this->_containAssociations(
                $associations,
                $this->_nestedAssociations
            );
        }

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
     * @return array
     */
    protected function _containAssociations(
        AssociationCollection $associations,
        array $nestedAssociations = []
    ) {
        $result = [];

        foreach ($associations as $association) {
            $result[$association->name()] = [];

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
