<?php
namespace CsvMigrations\FieldHandlers;

use Cake\Utility\Hash;
use CsvMigrations\FieldHandlers\BaseFileFieldHandler;
use CsvMigrations\FileUploadsUtils;

/**
 * @deprecated 10.0.0 Obsolete class since new File Uploads implementation
 */
class FileFieldHandler extends BaseFileFieldHandler
{
    /**
     * Database field type
     */
    const DB_FIELD_TYPE = 'uuid';

    /**
     * Defines the layout of the wrapper
     * Expects the label and the actual field.
     */
    const WRAPPER = '<div class="form-group">%s%s</div>';

    /**
     * {@inheritDoc}
     * @todo To avoid confusion: data param is not used because
     * it has no value. We do not store anything in the file field on DB.
     *
     * In this case, it renders the output based on the given value of data.
     */
    public function renderInput($table, $field, $data = '', array $options = [])
    {
        $data = $this->_getFieldValueFromData($field, $data);
        if (empty($data) && !empty($options['entity'])) {
            $data = $this->_getFieldValueFromData('id', $options['entity']);
        }
        if (empty($data)) {
            $result = $this->_renderInputWithoutData($table, $field, $options);
        } else {
            $result = $this->_renderInputWithData($table, $field, $data, $options);
        }

        return $result;
    }

    /**
     * Renders new file input field with no value. Applicable for add action.
     *
     * @param  Table $table Table
     * @param  string $field Field
     * @param  array $options Options
     * @return string HTML input field.
     */
    protected function _renderInputWithoutData($table, $field, $options)
    {
        $uploadField = $this->cakeView->Form->file(
            $this->_getFieldName($table, $field, $options) . '[]',
            ['multiple' => true]
        );
        $label = $this->cakeView->Form->label($field);

        return sprintf(self::WRAPPER, $label, $uploadField);
    }

    /**
     * Renders new file input field with value. Applicable for edit action.
     *
     * @param  Table $table Table
     * @param  string $field Field
     * @param  mixed $data Data
     * @param  array $options Options
     * @return string HTML input field with data attribute.
     */
    protected function _renderInputWithData($table, $field, $data, $options)
    {
        $fileUploadsUtils = new FileUploadsUtils($table);
        $entities = $fileUploadsUtils->getFiles($data);

        if (is_null($entities)) {
            return $this->_renderInputWithoutData($table, $field, $options);
        }

        $files = [];
        foreach ($entities as $file) {
            $files[] = [
                'id' => $file->id,
                'path' => $file->path
            ];
        }

        $uploadField = $this->cakeView->Form->file(
            $this->_getFieldName($table, $field, $options) . '[]',
            [
                'multiple' => true,
                'data-document-id' => $data,
                'data-files' => json_encode($files),
            ]
        );
        $label = $this->cakeView->Form->label($field);

        return sprintf(self::WRAPPER, $label, $uploadField);
    }

    /**
     * {@inheritDoc}
     */
    public function renderValue($table, $field, $data, array $options = [])
    {
        $data = $this->_getFieldValueFromData($field, $data);
        if (empty($data) && !empty($options['entity'])) {
            $data = $this->_getFieldValueFromData('id', $options['entity']);
        }

        return parent::renderValue($table, $field, $data, $options);
    }
}
