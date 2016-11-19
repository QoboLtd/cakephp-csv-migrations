<?php
namespace CsvMigrations\View\Cell;

use Cake\ORM\ResultSet;
use Cake\View\Cell;

/**
 * TabContent cell
 */
class TabContentCell extends Cell
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
    public function display(array $data)
    {
        // we might work with ResultSet of Array of records,
        // thus we abstract to length of records
        if ($data['content']['records'] instanceof ResultSet) {
            $data['content']['length'] = $data['content']['records']->count();

            //@TODO: make sure casting toArray() won't break existing functionality
            $data['content']['records'] = $data['content']['records']->toArray();

        } elseif (is_array($data['content']['records'])) {
            $data['content']['length'] = count($data['content']['records']);
        }

        $this->set(compact('data'));
    }
}
