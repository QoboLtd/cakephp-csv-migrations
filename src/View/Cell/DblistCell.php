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

namespace CsvMigrations\View\Cell;

use Cake\Datasource\EntityInterface;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\View\Cell;
use CsvMigrations\FieldHandlers\Provider\RenderValue\ListRenderer;
use Webmozart\Assert\Assert;

/**
 * DbList cell
 */
class DblistCell extends Cell
{

    /**
     * List of valid options that can be passed into this
     * cell's constructor.
     *
     * @var array
     */
    protected $_validCellOptions = [];

    /**
     * Match and render the sucessfull value.
     *
     * Checks the given list if it has the given value in its list items.
     *
     * @param string $value List item value
     * @param string $list Name of the list
     * @return void
     */
    public function renderValue(string $value, string $list): void
    {
        /**
         * @var \CsvMigrations\Model\Table\DblistsTable
         */
        $table = $this->loadModel('CsvMigrations.Dblists');
        $this->_createList($list);

        $query = $table->find()
            ->enableHydration(true)
            ->where([$table->aliasField('name') => $list])
            ->matching('DblistItems', function ($q) use ($value) {
                return $q->where(['DblistItems.value' => $value]);
            });

        try {
            $entity = $query->firstOrFail();
        } catch (RecordNotFoundException $e) {
            $this->set('data', '' !== trim($value) ? sprintf(ListRenderer::VALUE_NOT_FOUND_HTML, $value) : '');

            return;
        }

        Assert::isInstanceOf($entity, EntityInterface::class);
        $this->set('data', $entity->get('_matchingData')['DblistItems']->get('name'));
    }

    /**
     * Create new list.
     *
     * It will fail to create a new list if the given name already exists.
     *
     * @param string $name List name
     * @return bool
     */
    protected function _createList(string $name): bool
    {
        /**
         * @var \CsvMigrations\Model\Table\DblistsTable
         */
        $table = $this->loadModel('CsvMigrations.Dblists');

        if ($table->exists(['name' => $name])) {
            return false;
        }

        $entity = $table->newEntity(['name' => $name]);

        return (bool)$table->save($entity);
    }
}
