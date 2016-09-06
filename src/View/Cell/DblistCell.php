<?php
namespace CsvMigrations\View\Cell;

use Cake\View\Cell;

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
     * Default display method.
     *
     * @return void
     */
    public function display($field, $list, array $options = [])
    {
        $this->loadModel('CsvMigrations.Dblists');
        $selOptions = $this->Dblists->find('options', ['name' => $list]);
        $this->set(compact('field', 'list', 'options', 'selOptions'));
    }

    /**
     * Match and render the sucessfull value.
     *
     * Checks the given list if it has the given value in its list items.
     *
     * @throws RunTimeException If the value is not found
     * @param  string $listItemValue List item value
     * @param  string $name Name of the list
     * @return void
     */
    public function renderValue($listItemValue, $name = null)
    {
        $this->loadModel('CsvMigrations.Dblists');
        $query = $this->Dblists
            ->findByName($name);
        $query = $query->matching('DblistItems', function ($q) use ($listItemValue) {
            return $q->where(['DblistItems.value' => $listItemValue]);
        });
        if (!$query->isEmpty()) {
            $data = $query->first()->_matchingData['DblistItems']->get('name');
        } else {
            $data = __d('CsvMigrations', 'The value "{0}" cannot be found in the list "{1}"', $listItemValue, $name);
        }

        $this->set('data', $data);
    }
}
