<?php
namespace CsvMigrations\Controller\Api;

use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Datasource\ResultSetDecorator;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Crud\Controller\ControllerTrait;
use CsvMigrations\CsvMigrationsUtils;
use CsvMigrations\FieldHandlers\RelatedFieldTrait;
use CsvMigrations\FileUploadsUtils;
use CsvMigrations\Panel;
use CsvMigrations\PanelUtilTrait;
use ReflectionMethod;

class AppController extends Controller
{
    use ControllerTrait;
    use PanelUtilTrait;
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
     * Authentication config
     *
     * @var array
     */
    protected $_authConfig = [
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
    ];

    protected $_fileUploadsUtils;

    /**
     * {@inheritDoc}
     */
    public function initialize()
    {
        parent::initialize();

        $this->_fileUploadsUtils = new FileUploadsUtils($this->{$this->name});

        $this->_authentication();

        $this->_acl();
    }

    /**
     * Method that sets up API Authentication.
     *
     * @link http://www.bravo-kernel.com/2015/04/how-to-add-jwt-authentication-to-a-cakephp-3-rest-api/
     * @return void
     */
    protected function _authentication()
    {
        $this->loadComponent('Auth', $this->_authConfig);

        // set auth user from token
        $user = $this->Auth->getAuthenticate('ADmad/JwtAuth.Jwt')->getUser($this->request);
        $this->Auth->setUser($user);

        // If API authentication is disabled, allow access to all actions. This is useful when using some
        // other kind of access control check.
        // @todo currently, even if API authentication is disabled, we are always generating an API token
        // within the Application for internal system use. That way we populate the Auth->user() information
        // which allows other access control systems to work as expected. This logic can be removed if API
        // authentication is always forced.
        if (!Configure::read('CsvMigrations.api.auth')) {
            $this->Auth->allow();
        }
    }

    /**
     * Method that handles ACL checks from third party libraries,
     * if the associated parameters are set in the plugin's configuration.
     *
     * @return void
     * @todo currently only copes with Table class instances. Probably there is better way to handle this.
     */
    protected function _acl()
    {
        $className = Configure::read('CsvMigrations.acl.class');
        $methodName = Configure::read('CsvMigrations.acl.method');
        $componentName = Configure::read('CsvMigrations.acl.component');

        if ($componentName) {
            $this->loadComponent($componentName, [
                'currentRequest' => $this->request->params
            ]);
        }

        if (!$className || !$methodName) {
            return;
        }

        $class = TableRegistry::get($className);

        if (!method_exists($class, $methodName)) {
            return;
        }

        $method = new ReflectionMethod($class, $methodName);

        if (!$method->isPublic()) {
            return;
        }

        if ($method->isStatic()) {
            $class::{$methodName}($this->request->params, $this->Auth->user());
        } else {
            $class->{$methodName}($this->request->params, $this->Auth->user());
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
            $ev = new Event('CsvMigrations.View.beforeFind', $this, [
                'query' => $event->subject()->query
            ]);
            $this->eventManager()->dispatch($ev);
        });

        $this->Crud->on('afterFind', function (Event $event) {
            $ev = new Event('CsvMigrations.View.afterFind', $this, [
                'entity' => $event->subject()->entity
            ]);
            $this->eventManager()->dispatch($ev);
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
            $ev = new Event('CsvMigrations.Index.beforePaginate', $this, [
                'query' => $event->subject()->query
            ]);
            $this->eventManager()->dispatch($ev);
        });

        $this->Crud->on('afterPaginate', function (Event $event) {
            $ev = new Event('CsvMigrations.Index.afterPaginate', $this, [
                'entities' => $event->subject()->entities
            ]);
            $this->eventManager()->dispatch($ev);
        });

        $this->Crud->on('beforeRender', function (Event $event) {
            $ev = new Event('CsvMigrations.Index.beforeRender', $this, [
                'entities' => $event->subject()->entities
            ]);
            $this->eventManager()->dispatch($ev);
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
            $ev = new Event('CsvMigrations.Add.beforeSave', $this, [
                'entity' => $event->subject()->entity
            ]);
            $this->eventManager()->dispatch($ev);
        });

        $this->Crud->on('afterSave', function (Event $event) {
            // handle file uploads if found in the request data
            if (isset($this->request->data['file'])) {
                $this->_fileUploadsUtils->save($event->subject()->entity, $this->request->data['file']);
            }
            $ev = new Event('CsvMigrations.Add.afterSave', $this, [
                'entity' => $event->subject()->entity
            ]);
            $this->eventManager()->dispatch($ev);
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
            $ev = new Event('CsvMigrations.Edit.beforeFind', $this, [
                'query' => $event->subject()->query
            ]);
            $this->eventManager()->dispatch($ev);
        });

        $this->Crud->on('afterFind', function (Event $event) {
            $ev = new Event('CsvMigrations.Edit.afterFind', $this, [
                'entity' => $event->subject()->entity
            ]);
            $this->eventManager()->dispatch($ev);
        });

        $this->Crud->on('beforeSave', function (Event $event) {
            $ev = new Event('CsvMigrations.Edit.beforeSave', $this, [
                'entity' => $event->subject()->entity
            ]);
            $this->eventManager()->dispatch($ev);
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
     * Delete CRUD action events handling logic.
     *
     * @return \Cake\Network\Response
     */
    public function delete()
    {
        return $this->Crud->execute();
    }

    /**
     * upload function shared among API controllers
     *
     * @return void
     */
    public function upload()
    {
        $this->autoRender = false;

        $saved = null;
        $response = [];

        foreach ($this->request->data() as $model => $files) {
            if (is_array($files)) {
                foreach ($files as $modelField => $fileInfo) {
                    $saved = $this->_fileUploadsUtils->ajaxSave($this->{$this->name}, $modelField, $fileInfo, ['ajax' => true]);
                }
            }
        }

        if ($saved) {
            $response = $saved;
        } else {
            $this->response->statusCode(400);
            $response['errors'] = "Couldn't save the File";
        }

        echo json_encode($response);
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
            ->maxAge($this->_getSessionTimeout())
            ->build();

        // if request method is OPTIONS just return the response with appropriate headers.
        if ('OPTIONS' === $this->request->method()) {
            return $this->response;
        }
    }

    /**
     * Get session timeout in seconds
     *
     * @return int Session lifetime in seconds
     */
    protected function _getSessionTimeout()
    {
        // Read from Session.timeout configuration
        $result = Configure::read('Session.timeout');
        if ($result) {
            $result = $result * 60; // Convert minutes to seconds
        }

        // Read from PHP configuration
        if (!$result) {
            $result = ini_get('session.gc_maxlifetime');
        }

        // Fallback on default
        if (!$result) {
            $result = 1800; // 30 minutes
        }

        return $result;
    }
}
