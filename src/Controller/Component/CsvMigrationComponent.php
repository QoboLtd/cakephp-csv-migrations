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
    protected $_listFieldsActions = ['add', 'edit'];

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
        if (in_array($this->request->params['action'], $this->_listFieldsActions)) {
            $this->_setListFieldOptions($event);
        }
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

        $controller->set('csvForeignKeys', $result);
        $controller->set('_serialize', ['csvForeignKeys']);
    }

    /**
     * Method that passes Table's list fields options to the View.
     * @param \Cake\Event\Event $event An Event instance
     * @return void
     * @todo   need to handle more than one migration csv file
     */
    protected function _setListFieldOptions(\Cake\Event\Event $event)
    {
        $controller = $event->subject();

        $path = Configure::readOrFail('CsvAssociations.path') . $this->request->controller . DS;

        // get csv file
        $path = $this->_getCsvFile($path);

        // get migrations csv data
        $csvData = $this->_getCsvData($path);

        $result = [];
        foreach ($csvData as $row) {
            $listName = $this->_getListName($row[1]);
            if ('' !== $listName) {
                $path = Configure::readOrFail('CsvListsOptions.path') . $listName . '.csv';
                $listData = $this->_getCsvData($path);
                if (!empty($listData)) {
                    $result[$row[0]] = $this->_prepareListOptions($listData);
                }
            }
        }

        $controller->set('csvListsOptions', $result);
        $controller->set('_serialize', ['csvListsOptions']);
    }

    /**
     * Method that retrieves csv file path from specified directory.
     * @param  string $path directory to search in
     * @return string       csv file path
     */
    protected function _getCsvFile($path)
    {
        $result = '';
        if (file_exists($path)) {
            foreach (new \DirectoryIterator($path) as $fileInfo) {
                if ($fileInfo->isFile() && 'csv' === $fileInfo->getExtension()) {
                    $result = $fileInfo->getPathname();
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Method that retrieves csv file data.
     * @param  string $path csv file path
     * @return array        csv data
     */
    protected function _getCsvData($path)
    {
        $result = [];
        if (file_exists($path)) {
            if (false !== ($handle = fopen($path, 'r'))) {
                while (false !== ($data = fgetcsv($handle, 0, ','))) {
                    $result[] = $data;
                }
                fclose($handle);
            }
        }

        return $result;
    }

    /**
     * Method that extracts list name from field type definition.
     * @param  string $name field type
     * @return string
     */
    protected function _getListName($name)
    {
        $result = '';
        $pattern = 'list:';
        if (false !== $pos = strpos($name, $pattern)) {
            $result = str_replace($pattern, '', $name);
        }

        return $result;
    }

    /**
     * Method that restructures list options csv data for better handling.
     * @param  array  $data csv data
     * @return array
     */
    protected function _prepareListOptions($data)
    {
        $result = [];

        foreach ($data as $row) {
            $result[$row[0]] = $row[1];
        }

        return $result;
    }
}
