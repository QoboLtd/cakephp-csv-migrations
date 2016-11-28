<?php
namespace CsvMigrations\View\Cell;

use Cake\Utility\Inflector;
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
     * List of accessible displayTemplates for TabContent
     * @var array
     */
    protected $_displayTemplates = ['display', 'generalPanelTable'];


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
     * @return void
     */
    public function display(array $data)
    {
        $this->template = $this->_getDisplayTemplate($data['content']);

        // @NOTE: if there's no order identifier,
        // we assume it's the main TabContent, and do all required
        // data manipulations.
        // if order variable is present, we assume that the data is
        // prepared for being sent into the view.
        if (empty($data['content']['options']) && !isset($data['content']['options']['order'])) {
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
        }

        $this->set(compact('data'));
    }
}
