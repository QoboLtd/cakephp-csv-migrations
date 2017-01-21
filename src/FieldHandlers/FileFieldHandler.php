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
    public function renderInput($data = '', array $options = [])
    {
        $data = $this->_getFieldValueFromData($data);
        if (empty($data) && !empty($options['entity'])) {
            $data = $this->_getFieldValueFromData($options['entity'], 'id');
        }
        if (empty($data)) {
            $result = $this->_renderInputWithoutData($options);
        } else {
            $result = $this->_renderInputWithData($data, $options);
        }

        return $result;
    }

    /**
     * Renders new file input field with no value. Applicable for add action.
     *
     * @param  array $options Options
     * @return string HTML input field.
     */
    protected function _renderInputWithoutData($options)
    {
        $uploadField = $this->cakeView->Form->file(
            $this->_getFieldName($this->table, $this->field, $options) . '[]',
            ['multiple' => true]
        );
        $label = $this->cakeView->Form->label($this->field);

        return sprintf(self::WRAPPER, $label, $uploadField);
    }

    /**
     * Renders new file input field with value. Applicable for edit action.
     *
     * @param  mixed $data Data
     * @param  array $options Options
     * @return string HTML input field with data attribute.
     */
    protected function _renderInputWithData($data, $options)
    {
        $fileUploadsUtils = new FileUploadsUtils($this->table);
        $entities = $fileUploadsUtils->getFiles($data);

        if (is_null($entities)) {
            return $this->_renderInputWithoutData($options);
        }

        $files = [];
        foreach ($entities as $file) {
            $files[] = [
                'id' => $file->id,
                'path' => $file->path
            ];
        }

        $uploadField = $this->cakeView->Form->file(
            $this->_getFieldName($options) . '[]',
            [
                'multiple' => true,
                'data-document-id' => $data,
                'data-files' => json_encode($files),
            ]
        );
        $label = $this->cakeView->Form->label($this->field);

        return sprintf(self::WRAPPER, $label, $uploadField);
    }

    /**
     * {@inheritDoc}
     */
    public function renderValue($data, array $options = [])
    {
        $data = $this->_getFieldValueFromData($data);
        if (empty($data) && !empty($options['entity'])) {
            $data = $this->_getFieldValueFromData($options['entity'], 'id');
        }

        return parent::renderValue($table, $field, $data, $options);
    }
}
