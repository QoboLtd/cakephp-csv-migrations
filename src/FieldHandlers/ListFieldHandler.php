<?php
namespace CsvMigrations\FieldHandlers;

use App\View\AppView;
use Cake\Core\Configure;
use CsvMigrations\FieldHandlers\BaseFieldHandler;

class ListFieldHandler extends BaseFieldHandler
{
    /**
     * Field type match pattern
     */
    const FIELD_TYPE_PATTERN = 'list:';

    /**
     * Field parameters
     * @var array
     */
    protected $_fieldParams = ['value', 'label', 'inactive'];

    /**
     * Input field html markup
     */
    const INPUT_HTML = '<div class="form-group">%s</div>';

    /**
     * Method responsible for rendering field's input.
     * @param  mixed  $table   name or instance of the Table
     * @param  string $field   field name
     * @param  string $data    field data
     * @param  array  $options field options
     * @return string          field input
     */
    public function renderInput($table, $field, $data = '', array $options = [])
    {
        // load AppView
        $cakeView = new AppView();

        $listName = $this->_getListName($options['fieldDefinitions']['type']);
        $fieldOptions = $this->_getListFieldOptions($listName);
        $fieldOptions = $this->_filterOptions($fieldOptions);

        $input = $cakeView->Form->label($field);
        $input .= $cakeView->Form->select($field, $fieldOptions, [
            'class' => 'form-control',
            'required' => (bool)$options['fieldDefinitions']['required'],
            'value' => $data,
            'empty' => true
        ]);

        return sprintf(static::INPUT_HTML, $input);
    }

    /**
     * Method that renders list field's value.
     * @param  mixed  $table   name or instance of the Table
     * @param  string $field   field name
     * @param  string $data    field data
     * @param  array  $options field options
     * @return string
     */
    public function renderValue($table, $field, $data, array $options = [])
    {
        $result = $data;
        $listName = $this->_getListName($options['fieldDefinitions']['type']);
        $fieldOptions = $this->_getListFieldOptions($listName);

        if (!empty($fieldOptions[$data]['label'])) {
            $result = h($fieldOptions[$data]['label']);
        }

        return $result;
    }

    /**
     * Method that extracts list name from field type definition.
     * @param  string $type field type
     * @return string       list name
     */
    protected function _getListName($type)
    {
        $result = str_replace(static::FIELD_TYPE_PATTERN, '', $type);

        return $result;
    }

    /**
     * Method that retrieves list field options.
     * @param string $listName list name
     * @return array
     */
    protected function _getListFieldOptions($listName)
    {
        $result = [];
        $path = Configure::readOrFail('CsvMigrations.lists.path') . $listName . '.csv';
        $listData = $this->_getCsvData($path);
        if (!empty($listData)) {
            $result = $this->_prepareListOptions($listData);
        }

        return $result;
    }

    /**
     * Method that filters list options, excluding non-active ones
     * @param  array $options list options
     * @return array
     */
    protected function _filterOptions($options)
    {
        $result = [];
        foreach ($options as $k => $v) {
            if ($v['inactive']) {
                continue;
            }
            $result[$k] = $v['label'];
        }

        return $result;
    }

    /**
     * Method that retrieves csv file data.
     * @param  string $path csv file path
     * @return array        csv data
     * @todo this method should be moved to a Trait class as is used throught Csv Migrations and Csv Views plugins
     */
    protected function _getCsvData($path)
    {
        $result = [];
        if (file_exists($path)) {
            if (false !== ($handle = fopen($path, 'r'))) {
                $row = 0;
                while (false !== ($data = fgetcsv($handle, 0, ','))) {
                    // skip first row
                    if (0 === $row) {
                        $row++;
                        continue;
                    }
                    $result[] = $data;
                }
                fclose($handle);
            }
        }

        return $result;
    }

    /**
     * Method that restructures list options csv data for better handling.
     * @param  array  $data csv data
     * @return array
     * @todo   Validation of CVS files should probably be done separately, elsewhere.
     *         Note: the number of columns can vary per record.
     */
    protected function _prepareListOptions($data)
    {
        $result = [];
        $paramsCount = count($this->_fieldParams);

        foreach ($data as $row) {
            $colCount = count($row);
            if ($colCount !== $paramsCount) {
                throw new \RuntimeException(sprintf($this->_errorMessages[__FUNCTION__], $colCount, $paramsCount));
            }
            $field = array_combine($this->_fieldParams, $row);

            $result[$field['value']] = [
                'label' => $field['label'],
                'inactive' => (bool)$field['inactive']
            ];
        }

        return $result;
    }
}
