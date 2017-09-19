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
use CsvMigrations\FieldHandlers\CsvField;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;
use CsvMigrations\Panel;
use CsvMigrations\PanelUtilTrait;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;

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
     * @param \Cake\Event\Event $event Event instance
     * @return void
     */
    public function beforeRender(Event $event)
    {
        $this->filterFields($event);
    }

    /**
     * Filter csv fields.
     *
     * @param \Cake\Event\Event $event Event instance
     * @return void
     */
    protected function filterFields(Event $event)
    {
        $panelActions = (array)Configure::read('CsvMigrations.panels.actions');
        $dynamicPanelActions = (array)Configure::read('CsvMigrations.panels.dynamic_actions');
        if (!in_array($this->request->action, array_diff($panelActions, $dynamicPanelActions))) {
            return;
        }

        if (!method_exists($this->_tableInstance, 'getConfig')) {
            return;
        }

        $tableConfig = $this->_tableInstance->getConfig();
        $evalPanels = $this->getEvalPanels($tableConfig, $event->subject()->viewVars['entity']->toArray());
        if (empty($evalPanels['fail'])) {
            return;
        }

        // filter out fields of hidden panels
        $event->subject()->viewVars['fields'] = array_diff_key(
            $event->subject()->viewVars['fields'],
            array_flip($evalPanels['fail'])
        );

        if ((string)Configure::read('CsvMigrations.batch.action') !== $this->request->action) {
            return;
        }

        $this->filterBatchFields($event);
    }

    /**
     * Filter batch fields.
     *
     * @param \Cake\Event\Event $event Event instance
     * @return void
     */
    protected function filterBatchFields(Event $event)
    {
        $config = new ModuleConfig(ConfigType::MIGRATION(), $this->request->controller);
        $fields = json_decode(json_encode($config->parse()), true);

        $batchFields = (array)Configure::read('CsvMigrations.batch.types');

        $nonBatchFields = [];
        foreach ($fields as $field) {
            $csvField = new CsvField($field);
            if (in_array($csvField->getType(), $batchFields)) {
                continue;
            }

            $nonBatchFields[] = $csvField->getName();
        }

        if (empty($nonBatchFields)) {
            return;
        }

        $fields = $event->subject()->viewVars['fields'];
        foreach ($fields as $panel => $panelFields) {
            foreach ($panelFields as $section => $sectionFields) {
                foreach ($sectionFields as $key => $field) {
                    if (!in_array($field['name'], $nonBatchFields)) {
                        continue;
                    }

                    $fields[$panel][$section][$key]['name'] = '';
                }
            }
        }

        $event->subject()->viewVars['fields'] = $fields;
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

        $config = new ModuleConfig(ConfigType::VIEW(), $this->request->controller, $this->request->action);
        $result = $config->parse()->items;

        list($plugin, $model) = pluginSplit($this->_tableInstance->registryAlias());
        // add plugin and model names to each of the fields
        $result = $this->_setFieldPluginAndModel($result, $model, $plugin);

        // if action requires panels, arrange the fields into the panels
        if (in_array($this->request->action, (array)Configure::read('CsvMigrations.panels.actions'))) {
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
