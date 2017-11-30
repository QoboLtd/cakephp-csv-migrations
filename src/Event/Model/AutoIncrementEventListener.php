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
namespace CsvMigrations\Event\Model;

use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\Utility\Inflector;
use CsvMigrations\Table;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;

class AutoIncrementEventListener implements EventListenerInterface
{
    /**
     * Implemented Events
     *
     * @return array
     */
    public function implementedEvents()
    {
        return [
            'Model.beforeSave' => 'autoIncrementFieldValue',
        ];
    }

    /**
     * Auto-increment reference number.
     *
     * @param  \Cake\Event\Event $event Event object
     * @param  \Cake\Datasource\EntityInterface $entity Translation entity
     * @param  \ArrayObject $options entity options
     * @return void
     */
    public function autoIncrementFieldValue(Event $event, EntityInterface $entity, ArrayObject $options)
    {
        $table = $event->subject();

        if (!$table instanceof Table) {
            return;
        }

        $autoIncrementFields = $this->_getAutoIncrementFields($table);

        // skip if no auto-increment fields are defined
        if (empty($autoIncrementFields)) {
            return;
        }

        // skip modifying auto-increment field(s) on existing records.
        if (!$entity->isNew()) {
            foreach (array_keys($autoIncrementFields) as $field) {
                $entity->unsetProperty($field);
            }

            return;
        }

        foreach ($autoIncrementFields as $field => $options) {
            // get max value
            $query = $event->subject()->find('withTrashed');
            $query->select([$field => $query->func()->max($field)]);
            $max = $query->first()->toArray();
            $max = (float)$max[$field];

            if (empty($options['min'])) {
                $entity->{$field} = $max + 1;
            } else {
                // if value is less than the allowed minimum, then set it to the minimum.
                $entity->{$field} = $max < $options['min'] ? $options['min'] : $max + 1;
            }
        }
    }

    /**
     * Retrieves auto-increment fields for specified Module.
     *
     * Retrieves and returns auto-increment fields along with their
     * related properties (such as 'min' value).
     *
     * @param \CsvMigrations\Table $table Table instance
     * @return array
     */
    protected function _getAutoIncrementFields(table $table)
    {
        $result = [];

        $moduleName = Inflector::camelize($table->table());
        $mc = new ModuleConfig(ConfigType::FIELDS(), $moduleName);
        $config = (array)json_decode(json_encode($mc->parse()), true);

        if (empty($config)) {
            return $result;
        }

        $moduleFields = $table->getFieldsDefinitions();
        foreach (array_keys($moduleFields) as $field) {
            $autoIncrement = empty($config[$field]['auto-increment']) ? false : (bool)$config[$field]['auto-increment'];
            if (!$autoIncrement) {
                continue;
            }
            $result[$field] = [];

            $min = empty($config[$field]['min']) ? null : $config[$field]['min'];
            if (!is_int($min) && !is_float($min)) {
                continue;
            }

            $result[$field] = [
                'min' => $min
            ];
        }

        return $result;
    }
}
