<?php
namespace CsvMigrations\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

/**
 * CsvView component
 */
class CsvMigrationComponent extends Component
{

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [];

    /**
     * Called before the controller action. You can use this method to configure and customize components
     * or perform logic that needs to happen before each controller action.
     *
     * @param \Cake\Event\Event $event An Event instance
     * @return void
     * @link http://book.cakephp.org/3.0/en/controllers.html#request-life-cycle-callbacks
     */
    public function beforeFilter(\Cake\Event\Event $event)
    {
        $this->_setForeignKeys($event);
    }

    /**
     * Method that passes Table's associated foreign keys to the View.
     * @param \Cake\Event\Event $event An Event instance
     * @return void
     */
    protected function _setForeignKeys(\Cake\Event\Event $event)
    {
        $controller = $event->subject();
        $tableName = $controller->name;

        $result = [];
        $table = TableRegistry::get($tableName);

        foreach ($table->associations() as $k) {
            $result[$k->foreignKey()] = $k;
        }

        $controller->set('foreignKeys', $result);
        $controller->set('_serialize', ['foreignKeys']);
    }
}
