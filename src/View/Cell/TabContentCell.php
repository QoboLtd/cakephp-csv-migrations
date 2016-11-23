<?php
namespace CsvMigrations\View\Cell;

use Cake\ORM\Entity;
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
        // @TODO: trigger template change for the
        // before/after Content events.
        $this->template = 'display';

        // we might work with ResultSet of Array of records,
        // thus we abstract to length of records
        if ($data['content']['records'] instanceof ResultSet) {
            $data['content']['length'] = $data['content']['records']->count();
            //@TODO: make sure casting toArray() won't break existing functionality
            $data['content']['records'] = $data['content']['records']->toArray();

        //NOTE: in case of ManyToOne we have Cake\ORM\Entity instead of ResultSet
        } elseif ($data['content']['records'] instanceof Entity) {
            $tmp[] = $data['content']['records'];
            $data['content']['records'] = $tmp;
            $data['content']['length'] = count($data['content']['records']);
        } elseif (is_array($data['content'])) {
            $data['content']['length'] = count($data['content']['records']);
        }

        $this->set(compact('data'));
    }
}
