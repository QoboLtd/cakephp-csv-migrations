<?php
namespace CsvMigrations\View\Cell;

use Cake\View\Cell;

/**
 * DbList cell
 */
class DbListCell extends Cell
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

    public function renderValue($name = null, $data)
    {
        $name = 'contact-types';
        $this->loadModel('CsvMigrations.Dblists');
        $list = $this->Dblists->findByName($name)->first();
        if (!$list) {
            return false;
        }
        $this->set('data', $data);
    }
}
