<?php
namespace CsvMigrations\View\Cell;

use Cake\ORM\Entity;
use Cake\ORM\ResultSet;
use Cake\Utility\Inflector;
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
     * List of accessible displayTemplates for TabContent
     * @var array
     */
    protected $_displayTemplates = ['display', 'panel'];


    /**
     * _getDisplayTemplate
     * Cells operate in different ctp files.
     * Currently, for simplicity reasons, we define
     * the list of templates (ctp files) that we use
     * so display() becomes a basic wrapper for TabContent.
     * @param array $data passed from the event
     *
     * @return string $result containing template.
     */
    protected function _getDisplayTemplate(array $data)
    {
        $result = 'display';
        $displayTemplate = null;
        if (!empty($data['options']) && isset($data['options']['displayTemplate'])) {
            $displayTemplate = $data['options']['displayTemplate'];
        }

        if (in_array($displayTemplate, $this->_displayTemplates)) {
            $result = Inflector::underscore($displayTemplate);
        }

        return $result;
    }

    /**
     * Default display method.
     *
     * Works as the wrapper for all the displayTemplate
     * options. Data passed might contain ResultSets and arrays.
     * In order to abstract from it, we cast it all into arrays,
     * and add 'length' property of the content, to do fast check
     * if content is empty or not.
     *
     * @param array $data passed into the cell.
     * @return void
     */
    public function display(array $data)
    {
        $this->template = $this->_getDisplayTemplate($data['content']);

        if ($data['content']['records'] instanceof ResultSet) {
            $data['content']['length'] = $data['content']['records']->count();
            $data['content']['records'] = $data['content']['records']->toArray();
        } elseif ($data['content']['records'] instanceof Entity) {
            $tmp[] = $data['content']['records'];
            $data['content']['records'] = $tmp;
            $data['content']['length'] = count($data['content']['records']);
        } elseif (is_array($data['content']['records'])) {
            $data['content']['length'] = count($data['content']['records']);
        }

        $this->set(compact('data'));
    }
}
