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
    public function autoIncrementFieldValue(Event $event, EntityInterface $entity, ArrayObject $options) : void
    {
        $table = $event->getSubject();

        if (!$table instanceof Table) {
            return;
        }

        $fields = $this->getAutoIncrementFields($table);

        // skip if no auto-increment fields are defined
        if (empty($fields)) {
            return;
        }

        // skip modifying auto-increment field(s) on existing records.
        if (! $entity->isNew()) {
            foreach (array_keys($fields) as $field) {
                $entity->unsetProperty($field);
            }

            return;
        }

        foreach ($fields as $field => $options) {
            // get max value
            $query = $table->find('withTrashed');

            /** @var \Cake\Datasource\EntityInterface|null */
            $max = $query->select([$field => $query->func()->max($field)])
                ->enableHydration(true)
                ->first();

            $max = null === $max ? 0 : (float)$max->get($field);

            if (empty($options['min'])) {
                $entity->set($field, $max + 1);

                continue;
            }

            // if value is less than the allowed minimum, then set it to the minimum.
            $max = $max < $options['min'] ? $options['min'] : $max + 1;

            $entity->set($field, $max);
        }
    }

    /**
     * Retrieves auto-increment fields for specified Module.
     *
     * Retrieves and returns auto-increment fields along with their
     * related properties (such as 'min' value).
     *
     * @param \CsvMigrations\Table $table Table instance
     * @return mixed[]
     */
    private function getAutoIncrementFields(Table $table) : array
    {
        $moduleName = Inflector::camelize($table->table());
        $mc = new ModuleConfig(ConfigType::FIELDS(), $moduleName);
        $config = json_encode($mc->parse());
        $config = false === $config ? [] : json_decode($config, true);

        if (empty($config)) {
            return [];
        }

        $result = [];
        foreach (array_keys($table->getFieldsDefinitions()) as $field) {
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
