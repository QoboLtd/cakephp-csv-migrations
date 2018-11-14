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
namespace CsvMigrations\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\Event\Event;
use CsvMigrations\Controller\Traits\PanelsTrait;
use CsvMigrations\FieldHandlers\CsvField;
use CsvMigrations\Utility\Field;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;

/**
 * CsvView component
 */
class CsvViewComponent extends Component
{
    use PanelsTrait;

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
        $table = $event->getSubject()->{$event->getSubject()->getName()};

        // skip passing table fields if action is not supported by the plugin
        if (in_array($this->request->getParam('action'), Configure::readOrFail('CsvMigrations.actions'))) {
            // if action requires panels, arrange the fields into the panels
            $panels = in_array($this->request->getParam('action'), (array)Configure::read('CsvMigrations.panels.actions'));
            $fields = Field::getCsvView($table, $this->request->getParam('action'), true, $panels);

            $event->getSubject()->set('fields', $fields);
            $event->getSubject()->set('_serialize', ['fields']);
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
        if (!in_array($this->request->getParam('action'), array_diff($panelActions, $dynamicPanelActions))) {
            return;
        }

        $config = new ModuleConfig(ConfigType::MODULE(), $event->getSubject()->getName());
        $tableConfig = json_decode(json_encode($config->parse()), true);

        $panels = $this->getPanels($tableConfig, $event->getSubject()->viewVars['entity']->toArray());
        if (!empty($panels['fail'])) {
            // filter out fields of hidden panels
            $event->getSubject()->viewVars['fields'] = array_diff_key(
                $event->getSubject()->viewVars['fields'],
                array_flip($panels['fail'])
            );
        }

        if ((string)Configure::read('CsvMigrations.batch.action') === $this->request->getParam('action')) {
            $this->filterBatchFields($event);
        }
    }

    /**
     * Filter batch fields.
     *
     * @param \Cake\Event\Event $event Event instance
     * @return void
     */
    protected function filterBatchFields(Event $event)
    {
        $config = new ModuleConfig(ConfigType::MIGRATION(), $this->request->getParam('controller'));
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

        $fields = $event->getSubject()->viewVars['fields'];
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

        $event->getSubject()->viewVars['fields'] = $fields;
    }
}
