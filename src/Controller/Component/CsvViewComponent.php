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

deprecationWarning(
    '"CsvMigrations\Controller\Component" class is deprecated. This functionality is ' .
    'now handled by "App\Controller\Component\CsvViewComponent" in ' .
    'https://github.com/QoboLtd/project-template-cakephp/blob/v49.0.0/src/Controller/Component/CsvViewComponent.php'
);

use Cake\Controller\Component;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\Event;
use CsvMigrations\Controller\Traits\PanelsTrait;
use CsvMigrations\FieldHandlers\CsvField;
use CsvMigrations\Utility\Field;
use Qobo\Utils\Module\ModuleRegistry;
use Webmozart\Assert\Assert;

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
    public function beforeFilter(Event $event): void
    {
        $controller = $event->getSubject();
        Assert::isInstanceOf($controller, Controller::class);

        $table = $controller->loadModel();

        // skip passing table fields if action is not supported by the plugin
        $request = $this->getController()->getRequest();
        if (in_array($request->getParam('action'), Configure::readOrFail('CsvMigrations.actions'))) {
            // if action requires panels, arrange the fields into the panels
            $panels = in_array($request->getParam('action'), (array)Configure::read('CsvMigrations.panels.actions'));
            $fields = Field::getCsvView($table, $request->getParam('action'), true, $panels);

            $controller->set('fields', $fields);
            $controller->set('_serialize', ['fields']);
        }
    }

    /**
     * Check/do things before rendering the output.
     *
     * @param \Cake\Event\Event $event Event instance
     * @return void
     */
    public function beforeRender(Event $event): void
    {
        $this->filterFields($event);
    }

    /**
     * Filter csv fields.
     *
     * @param \Cake\Event\Event $event Event instance
     * @return void
     */
    protected function filterFields(Event $event): void
    {
        $panelActions = (array)Configure::read('CsvMigrations.panels.actions');
        $dynamicPanelActions = (array)Configure::read('CsvMigrations.panels.dynamic_actions');
        $request = $this->getController()->getRequest();
        if (!in_array($request->getParam('action'), array_diff($panelActions, $dynamicPanelActions))) {
            return;
        }

        $controller = $event->getSubject();
        Assert::isInstanceOf($controller, Controller::class);

        $config = ModuleRegistry::getModule($controller->getName())->getConfig();

        $panels = $this->getPanels($config, $controller->viewVars['entity']->toArray());
        if (! empty($panels['fail'])) {
            // filter out fields of hidden panels
            $controller->viewVars['fields'] = array_diff_key(
                $controller->viewVars['fields'],
                array_flip($panels['fail'])
            );
        }

        if ((string)Configure::read('CsvMigrations.batch.action') === $request->getParam('action')) {
            $this->filterBatchFields($event);
        }
    }

    /**
     * Filter batch fields.
     *
     * @param \Cake\Event\Event $event Event instance
     * @return void
     */
    protected function filterBatchFields(Event $event): void
    {
        $fields = ModuleRegistry::getModule($this->request->getParam('controller'))->getMigration();

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

        $controller = $event->getSubject();
        Assert::isInstanceOf($controller, Controller::class);

        $fields = $controller->viewVars['fields'];
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

        $controller->viewVars['fields'] = $fields;
    }
}
