<?php
namespace CsvMigrations\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\ORM\Association;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use CsvMigrations\Panel;
use CsvMigrations\PanelUtilTrait;
use Qobo\Utils\ModuleConfig\ModuleConfig;
use Exception;
use RuntimeException;

/**
 * CsvView component
 */
class CsvViewComponent extends Component
{
    use PanelUtilTrait;

    /**
     * Default configuration.
     * @var array
     */
    protected $_defaultConfig = [];

    /**
     * Current request's table instance.
     * @var \Cake\ORM\Table
     */
    protected $_tableInstance;

    /**
     * Current request's controller instance.
     * @var \Cake\Controller\Controller
     */
    protected $_controllerInstance;

    /**
     * Actions to pass associated records to.
     * @var array
     */
    protected $_assocActions = ['view'];

    /**
     * Supported association types.
     * @var array
     */
    protected $_assocTypes = ['oneToMany', 'manyToOne', 'manyToMany'];

    /**
     * Actions to arrange fields into panels.
     * @var array
     */
    protected $_panelActions = ['add', 'edit', 'view'];

    /**
     * Called before the controller action. You can use this method to configure and customize components
     * or perform logic that needs to happen before each controller action.
     *
     * @param \Cake\Event\Event $event An Event instance
     * @return void
     * @link http://book.cakephp.org/3.0/en/controllers.html#request-life-cycle-callbacks
     */
    public function beforeFilter(Event $event)
    {
        $this->_controllerInstance = $event->subject();
        $this->_setTableInstance($this->_controllerInstance->request->params);

        // skip passing table fields if action is not supported by the plugin
        if (in_array($this->request->action, Configure::readOrFail('CsvMigrations.actions'))) {
            $this->_setTableFields();
        }
    }

    /**
     * Check/do things before rendering the output.
     *
     * @param  Event  $event [description]
     * @return void
     */
    public function beforeRender(Event $event)
    {
        $tableConfig = [];
        if (method_exists($this->_tableInstance, 'getConfig')) {
            $tableConfig = $this->_tableInstance->getConfig();
        }
        $controller = $event->subject();
        if (!empty($tableConfig) &&
            !empty($controller->viewVars['fields']) &&
            !empty($controller->viewVars['entity']) &&
            $this->request->action === 'view') {
            $panelFields = $controller->viewVars['fields'];
            $entity = $controller->viewVars['entity'];
            $evalPanels = $this->getEvalPanels($tableConfig, $entity->toArray());
            if (!empty($evalPanels['fail'])) {
                $controller->viewVars['fields'] = array_diff_key($panelFields, array_flip($evalPanels['fail']));
            }
        }
    }

    /**
     * Method that instantiates Table based on request parameters.
     *
     * @param  array  $params  Request parameters
     * @return \Cake\ORM\Table
     */
    protected function _setTableInstance(array $params)
    {
        $table = $params['controller'];
        if (!is_null($params['plugin'])) {
            $table = $params['plugin'] . '.' . $table;
        }

        $this->_tableInstance = TableRegistry::get($table);

        return $this->_tableInstance;
    }

    /**
     * Method that passes csv defined Table fields to the View
     *
     * @return void
     */
    protected function _setTableFields()
    {
        $result = [];

        $mc = new ModuleConfig(ModuleConfig::CONFIG_TYPE_VIEW, $this->request->controller, $this->request->action);
        $result = $mc->parse();

        list($plugin, $model) = pluginSplit($this->_tableInstance->registryAlias());
        /*
        add plugin and model names to each of the fields
         */
        $result = $this->_setFieldPluginAndModel($result, $model, $plugin);

        /*
        If action requires panels, arrange the fields into the panels
         */
        if (in_array($this->request->action, $this->_panelActions)) {
            $result = $this->_arrangePanels($result);
        }
        $this->_controllerInstance->set('fields', $result);
        $this->_controllerInstance->set('_serialize', ['fields']);
    }

    /**
     * Add plugin and model name for each of the csv fields.
     *
     * @param array  $data   csv data
     * @param string $model  model name
     * @param string $plugin plugin name
     * @return array         csv data
     */
    protected function _setFieldPluginAndModel($data, $model = null, $plugin = null)
    {
        foreach ($data as &$row) {
            foreach ($row as &$col) {
                $col = [
                    'plugin' => $plugin,
                    'model' => $model,
                    'name' => $col
                ];
            }
        }

        return $data;
    }

    /**
     * Method that arranges csv fetched fields into panels.
     *
     * @param  array  $data fields
     * @throws \RuntimeException when csv field parameters count does not match
     * @return array        fields arranged in panels
     */
    protected function _arrangePanels(array $data)
    {
        $result = [];

        foreach ($data as $fields) {
            $panel = array_shift($fields);
            $result[$panel['name']][] = $fields;
        }

        return $result;
    }
}
