<?php
namespace CsvMigrations\Event\View;

use Cake\Datasource\ConnectionManager;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\Network\Request;
use Cake\ORM\Association;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use CsvMigrations\ConfigurationTrait;
use CsvMigrations\Event\EventName;
use CsvMigrations\MigrationTrait;
use CsvMigrations\Panel;
use CsvMigrations\PanelUtilTrait;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;

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
        'manyToMany',
        'oneToMany'
    ];

    /**
     * Implemented Events
     * @return array
     */
    public function implementedEvents()
    {
        return [
            (string)EventName::VIEW_TABS_LIST() => 'getTabsList',
            (string)EventName::VIEW_TAB_CONTENT() => 'getTabContent',
        ];
    }

    /**
     * getTabsList method
     * Return the list of associations for the Entity as the tabs.
     *
     * @param Cake\Event $event passed
     * @param Cake\Request $request from the view
     * @param Cake\ORM\Entity $entity passed
     * @param array $user User info
     * @param array $options extra setup
     * @return void
     */
    public function getTabsList(Event $event, $request, Entity $entity, array $user, array $options)
    {
        $tabs = [];
        $params = $request->params;
        $table = $params['controller'];
        if (!is_null($params['plugin'])) {
            $table = $params['plugin'] . '.' . $table;
        }

        $this->_tableInstance = TableRegistry::get($table);
        $hiddenAssociations = $this->_tableInstance->getConfig(ConfigurationTrait::$CONFIG_OPTION_HIDDEN_ASSOCIATIONS);

        $tabLabels = $this->_getTabLabels($this->_tableInstance);

        foreach ($this->_tableInstance->associations() as $association) {
            if (in_array($association->name(), $hiddenAssociations)) {
                continue;
            }

            if (!in_array($association->type(), $this->_associationsMap)) {
                continue;
            }

            // We hide from associations file_storage,
            // as it's rendered within field handlers.
            if ('Burzum/FileStorage.FileStorage' == $association->className()) {
                continue;
            }

            list($namespace, $class) = namespaceSplit(get_class($association));

            // @NOTE: tabs hold a lot of duplicated properties.
            // It should be standardized.

            $tab = [
                'label' => $tabLabels[$association->alias()],
                'alias' => $association->alias(),
                'table' => $association->table(),
                'containerId' => Inflector::underscore($association->alias()),
                'associationName' => $association->name(),
                'associationType' => $association->type(),
                'associationObject' => $class,
                'targetClass' => $association->className(),
                'originTable' => $this->_tableInstance->table(),
            ];

            $tab['url'] = $event->subject()->Url->build([
                'prefix' => 'api',
                'controller' => $request->params['controller'],
                'action' => 'related',
            ]);

            $associationFields = $this->_tableInstance->getAssociationFields($association);

            $tab = array_merge($tab, $associationFields);

            array_push($tabs, $tab);
        }

        $event->result['tabs'] = $tabs;
    }

    /**
     * _getTabLabels
     *
     * Re-arrange tabs naming based on the config.ini and fieldNames
     * to avoid repetitive namings
     *
     * @param Cake\ORM\TableRegistry $tableInstance passed
     * @return array $labels with key/value storage of alias/name
     */
    protected function _getTabLabels($tableInstance)
    {
        $labels = [];
        $associationLabels = $tableInstance->getConfig(ConfigurationTrait::$CONFIG_OPTION_ASSOCIATION_LABELS);

        $labelCounts = [];
        // Gather labels for all associations
        foreach ($tableInstance->associations() as $association) {
            if (!in_array($association->type(), $this->_associationsMap)) {
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
                $table = TableRegistry::get($association->className());
                if (method_exists($table, 'moduleAlias') && is_callable([$table, 'moduleAlias'])) {
                    $labels[$assocAlias]['label'] = $table->moduleAlias();
                } else {
                    $labels[$assocAlias]['label'] = Inflector::humanize($association->table());
                }
            }

            // Initialize counter for current label, if needed
            if (empty($labelCounts[$labels[$assocAlias]['label']])) {
                $labelCounts[$labels[$assocAlias]['label']] = 0;
            }
            // Bump up label counter
            $labelCounts[$labels[$assocAlias]['label']]++;
        }

        // Now that we have all the labels, check if we have any duplicate labels
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
            $className = $tableInstance->association($assocAlias)->className();
            $fieldName = Inflector::humanize(Inflector::tableize($assocAlias));
            $fieldName = trim(str_replace($className, '', $fieldName));
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
     *
     * @param Cake\Event $event passed from AppView
     * @param Cake\Request $request from the view
     * @param Cake\ORM\Entity $entity of the record
     * @param array $user User
     * @param array $options for extra setup
     * @return array $content returned
     */
    public function getTabContent(Event $event, $request, $entity, array $user, array $options)
    {
        $content = [];

        $params = $request->params;
        $table = $params['controller'];

        if (!is_null($params['plugin'])) {
            $table = $params['plugin'] . '.' . $table;
        }

        $content = [];

        $content[] = $event->subject()->element('CsvMigrations.View/related', ['tab' => $options['tab']]);

        $event->result = join("\n", $content);
    }
}
