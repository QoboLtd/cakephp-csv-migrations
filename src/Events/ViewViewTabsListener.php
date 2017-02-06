<?php
namespace CsvMigrations\Events;

use Cake\Datasource\ConnectionManager;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\Network\Request;
use Cake\ORM\Association;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use CsvMigrations\MigrationTrait;
use CsvMigrations\Panel;
use CsvMigrations\PanelUtilTrait;
use CsvMigrations\Parser\Csv\ViewParser;
use CsvMigrations\PathFinder\ViewPathFinder;

class ViewViewTabsListener implements EventListenerInterface
{
    use MigrationTrait;

    const ASSOC_FIELDS_ACTION = 'index';

    /**
     * Current Table instance
     *
     * @var \Cake\ORM\Table
     */
    protected $_tableInstance;

    /**
     * Mapping of association name to method name
     *
     * @var array
     */
    protected $_associationsMap = [
        'manyToMany' => '_manyToManyAssociatedRecords',
        'oneToMany' => '_oneToManyAssociatedRecords'
    ];

    /**
     * Implemented Events
     * @return array
     */
    public function implementedEvents()
    {
        return [
            'CsvMigrations.View.View.TabsList' => 'getTabsList',
            //'CsvMigrations.View.View.TabContent.beforeContent' => 'getBeforeTabContent',
            'CsvMigrations.View.View.TabContent' => 'getTabContent',
            'CsvMigrations.View.View.TabContent.afterContent' => 'getAfterTabContent',
        ];
    }

    /**
     * getBeforeTabContent
     * @param Cake\Event $event passed
     * @param Cake\Network\Request $request - containing tab content
     * @param Cake\ORM\Entity $entity Entity
     * @param array $options Options
     * @return array
     */
    public function getBeforeTabContent(Event $event, Request $request, $entity, $options)
    {
        $result = [
          'title' => __('beforeTab Title'),
          'content' => [
            'records' => [],
            'length' => 0,
          ],
          'options' => [
            'displayTemplate' => 'panel_table',
            'order' => 'beforeTabContent',
            ],
        ];

        return $result;
    }

    /**
     * getAfterTabContent
     * @param Cake\Event $event passed
     * @param array $data containing tab content
     * @return null
     */
    public function getAfterTabContent(Event $event, array $data)
    {
        return null;
    }

    /**
     * getTabsList method
     * Return the list of associations for the Entity
     * as the tabs
     * @param Cake\Event $event passed
     * @param Cake\Request $request from the view
     * @param Cake\ORM\Entity $entity passed
     * @param array $options extra setup
     * @return array $tabs list with its labels and classes
     */
    public function getTabsList(Event $event, $request, $entity, $options)
    {
        $tabs = [];
        $labels = [];
        $params = $request->params;
        $table = $params['controller'];
        if (!is_null($params['plugin'])) {
            $table = $params['plugin'] . '.' . $table;
        }

        $this->_tableInstance = TableRegistry::get($table);

        $config = $this->_tableInstance->getConfig();
        $hiddenAssociations = $this->_tableInstance->hiddenAssociations();

        if (!empty($config['associationLabels'])) {
            $labels = $this->_tableInstance->associationLabels($config['associationLabels']);
        }

        $tabLabels = $this->_getTabLabels($this->_tableInstance, $config);

        foreach ($this->_tableInstance->associations() as $association) {
            if (in_array($association->name(), $hiddenAssociations)) {
                continue;
            }

            if (!in_array($association->type(), array_keys($this->_associationsMap))) {
                continue;
            }

            // We hide from associations file_storage,
            // as it's rendered within field handlers.
            if ('Burzum/FileStorage.FileStorage' == $association->className()) {
                continue;
            }

            list($namespace, $class) = namespaceSplit(get_class($association));

            $tab = [
                'label' => $tabLabels[$association->alias()],
                'alias' => $association->alias(),
                'table' => $association->table(),
                'containerId' => Inflector::underscore($association->alias()),
                'associationName' => $association->name(),
                'associationType' => $association->type(),
                'associationObject' => $class,
                'targetClass' => $association->className(),
            ];

            if (!empty($tab['targetClass'])) {
                array_push($tabs, $tab);
            }
        }

        return compact('tabs');
    }

    /**
     * _getTabLabels
     *
     * Re-arrange tabs naming based on the config.ini and fieldNames
     * to avoid repetitive namings
     * @param Cake\ORM\TableRegistry $tableInstance passed
     * @param array $config of the config.ini
     *
     * @return array $labels with key/value storage of alias/name
     */
    protected function _getTabLabels($tableInstance, $config = [])
    {
        $labels = [];
        $associationLabels = [];

        if (!empty($config['associationLabels'])) {
            $associationLabels = $tableInstance->associationLabels($config['associationLabels']);
        }

        $labelCounts = [];
        // Gather labels for all associations
        foreach ($tableInstance->associations() as $association) {
            if (!in_array($association->type(), array_keys($this->_associationsMap))) {
                continue;
            }
            $assocTableInstance = $association->target();

            $icon = $this->_getTableIcon($assocTableInstance);
            $assocAlias = $association->alias();

            // Initialize association label array with the icon
            if (empty($labels[$assocAlias])) {
                $labels[$assocAlias] = ['icon' => $icon];
            }

            if (in_array($assocAlias, array_keys($associationLabels))) {
                // If label exists in config.ini, use it.
                $labels[$assocAlias]['label'] = $associationLabels[$assocAlias];
            } else {
                // Otherwise use table alias or name as label
                $labels[$assocAlias]['label'] = Inflector::humanize($association->table());
                if (method_exists($assocTableInstance, 'moduleAlias')) {
                    $labels[$assocAlias]['label'] = Inflector::humanize($assocTableInstance->moduleAlias());
                }
            }

            // Initialize counter for current label, if needed
            if (empty($labelCounts[$labels[$assocAlias]['label']])) {
                $labelCounts[$labels[$assocAlias]['label']] = 0;
            }
            // Bump up label counter
            $labelCounts[$labels[$assocAlias]['label']]++;
        }

        // Not that we have all the labels, check if we have any duplicate labels
        // and append the association field name to those that are not unique.
        // Also, while we are at it, construct the actual label string from the
        // icon, label, and field name.
        foreach ($labels as $assocAlias => $label) {
            $labels[$assocAlias] = $label['icon'] . $label['label'];

            if ($labelCounts[$label['label']] <= 1) {
                // If the label is unique, we have nothing else to do
                continue;
            }

            // Get the association field and clean it up, removing the association name
            // from the field.  So, for 'primaryContactIdLeads', we'll do the following:
            // * Tableize: primary_contact_id_leads
            // * Humanize: Primary Contact Id Leads
            // * Remove association table: Primary Contact Id
            $fieldName = Inflector::humanize(Inflector::tableize($assocAlias));
            $fieldName = str_replace($assocAlias, '', Inflector::humanize(Inflector::tableize($assocAlias)));
            $fieldName = trim(str_replace($label['label'], '', $fieldName));

            // Field can be empty in case of, for example, many-to-many relationships.
            if (!empty($fieldName)) {
                $labels[$assocAlias] .= sprintf(" (%s)", $fieldName);
            }
        }

        return $labels;
    }

    /**
     * Get module icon for a given table
     *
     * @param Table $tableInstance Instance of a table for which to get the icon
     * @return string HTML string with the module icon
     */
    protected function _getTableIcon($tableInstance)
    {
        $result = 'cube';
        if (method_exists($tableInstance, 'icon')) {
            $result = $tableInstance->icon();
        }
        $result = '<span class="fa fa-' . $result . '"></span> ';

        return $result;
    }

    /**
     * getTabContent method
     * @param Cake\Event $event passed from AppView
     * @param Cake\Request $request from the view
     * @param Cake\ORM\Entity $entity of the record
     * @param array $options for extra setup
     * @return array $content returned
     */
    public function getTabContent(Event $event, $request, $entity, $options)
    {
        $content = [];
        $params = $request->params;
        $table = $params['controller'];

        if (!is_null($params['plugin'])) {
            $table = $params['plugin'] . '.' . $table;
        }

        $this->_tableInstance = TableRegistry::get($table);

        foreach ($this->_tableInstance->associations() as $association) {
            if ($options['tab']['associationName'] == $association->name()) {
                $type = $association->type();

                if (in_array($type, array_keys($this->_associationsMap))) {
                    $content = $this->{$this->_associationsMap[$type]}($association, $request);
                    if (!empty($content['records'])) {
                        break;
                    }
                }
            }
        }

        return $content;
    }



    /**
     * Method that retrieves many to many associated records
     *
     * @param  \Cake\ORM\Association $association Association object
     * @param \Cake\Network\Request $request current request
     * @return array associated records
     * @todo  find better way to fetch associated data, without including current table's data
     */
    protected function _manyToManyAssociatedRecords(Association $association, Request $request)
    {
        $result = [];
        $assocName = $association->name();
        $assocTableName = $association->table();
        $assocForeignKey = $association->foreignKey();

        $csvFields = $this->_getAssociationCsvFields($association, static::ASSOC_FIELDS_ACTION);
        if (empty($csvFields)) {
            return $result;
        }
        // get associated index View csv fields
        $fields = array_unique(
            array_merge(
                [$association->displayField()],
                $csvFields
            )
        );
        $query = $this->_tableInstance->find('all', [
            'conditions' => [$this->_tableInstance->primaryKey() => $request->params['pass'][0]],
            'contain' => [
                $assocName
            ]
        ]);
        $records = $query->first()->{$assocTableName};
        // store association name
        $result['assoc_name'] = $assocName;
        // store associated table name
        $result['table_name'] = $assocTableName;
        // store associated table class name
        $result['class_name'] = $association->className();
        // store associated table display field
        $result['display_field'] = $association->displayField();
        // store associated table primary key
        $result['primary_key'] = $association->primaryKey();
        // store associated table foreign key
        $result['foreign_key'] = Inflector::singularize($assocTableName) . '_' . $association->primaryKey();
        // store associated table fields
        $result['fields'] = $fields;
        // store associated table records
        $result['records'] = $records;

        return $result;
    }

    /**
     * Method that retrieves one to many associated records
     *
     * @param  \Cake\ORM\Association $association Association object
     * @param \Cake\Network\Request $request passed
     * @return array associated records
     */
    protected function _oneToManyAssociatedRecords(Association $association, Request $request)
    {
        $result = [];
        $assocName = $association->name();
        $assocTableName = $association->table();
        $assocForeignKey = $association->foreignKey();
        $recordId = $request->params['pass'][0];

        $csvFields = $this->_getAssociationCsvFields($association, static::ASSOC_FIELDS_ACTION);
        if (empty($csvFields)) {
            return $result;
        }

        // get associated index View csv fields
        $fields = array_unique(
            array_merge(
                [$association->displayField()],
                $csvFields
            )
        );

        $query = $association->target()->find('all', [
            'conditions' => [$assocForeignKey => $recordId]
        ]);
        $records = $query->all();
        // store association name
        $result['assoc_name'] = $assocName;
        // store associated table name
        $result['table_name'] = $assocTableName;
        // store associated table class name
        $result['class_name'] = $association->className();
        // store associated table display field
        $result['display_field'] = $association->displayField();
        // store associated table primary key
        $result['primary_key'] = $association->primaryKey();
        // store associated table foreign key
        $result['foreign_key'] = $association->foreignKey();
        // store associated table fields
        $result['fields'] = $fields;
        // store associated table records
        $result['records'] = $records;

        return $result;
    }

    /**
     * Get association CSV fields
     * @param Cake\ORM\Associations $association ORM association
     * @param object $action action passed
     * @return array
     */
    protected function _getAssociationCsvFields(Association $association, $action)
    {
        list($plugin, $controller) = pluginSplit($association->className());
        $fields = $this->_getCsvFields($controller, $action);

        return $fields;
    }

    /**
     * Method that retrieves table csv fields, by specified action.
     *
     * @param  string $tableName Table name
     * @param  string $action    Action name
     * @return array             table fields
     */
    protected function _getCsvFields($tableName, $action)
    {
        $result = [];

        if (empty($tableName) || empty($action)) {
            return $result;
        }

        try {
            $pathFinder = new ViewPathFinder;
            $path = $pathFinder->find($tableName, $action);
            $csvFields = $this->_getFieldsFromCsv($path);
        } catch (Exception $e) {
            return $result;
        }

        if (empty($csvFields)) {
            return $result;
        }

        $result = array_map(function ($v) {
            return $v[0];
        }, $csvFields);

        return $result;
    }

    /**
     * Method that gets fields from a csv file
     *
     * @param  string $path   csv file path
     * @return array          csv data
     */
    protected function _getFieldsFromCsv($path)
    {
        $result = [];
        if (is_readable($path)) {
            $parser = new ViewParser();
            $result = $parser->parseFromPath($path);
        }

        return $result;
    }
}
