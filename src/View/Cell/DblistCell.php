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

use Cake\View\Cell;
use CsvMigrations\FieldHandlers\Provider\RenderValue\ListRenderer;

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
     * @throws RunTimeException If the value is not found
     * @param string $value List item value
     * @param string $list Name of the list
     * @return void
     */
    public function renderValue($value, $list = null)
    {
        $this->loadModel('CsvMigrations.Dblists');
        $this->_createList($list);
        $query = $this->Dblists->findByName($list);
        $query = $query->matching('DblistItems', function ($q) use ($value) {
            return $q->where(['DblistItems.value' => $value]);
        });

        if (! $query->isEmpty()) {
            $this->set('data', $query->first()->_matchingData['DblistItems']->get('name'));

            return;
        }

        if ($query->isEmpty() && '' === trim($value)) {
            $this->set('data', '');

            return;
        }

        $this->set('data', sprintf(ListRenderer::VALUE_NOT_FOUND_HTML, $value));
    }

    /**
     * Create new list.
     *
     * It will fail to create a new list if the given name already exists.
     *
     * @param  string $name List's name
     * @return bool         True on sucess.
     */
    protected function _createList($name = '')
    {
        $this->loadModel('CsvMigrations.Dblists');
        if (!$this->Dblists->exists(['name' => $name])) {
            $entity = $this->Dblists->newEntity(['name' => $name]);

            return $this->Dblists->save($entity);
        }

        return false;
    }
}
