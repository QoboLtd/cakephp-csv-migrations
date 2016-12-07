<?php
namespace CsvMigrations\FieldHandlers;

use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use CsvMigrations\FieldHandlers\BaseFileFieldHandler;
use CsvMigrations\FileUploadsUtils;

class FilesFieldHandler extends BaseFileFieldHandler
{

    /**
     * Defines the layout of the wrapper
     * Expects the label and the actual field.
     */
    const WRAPPER = '<div class="form-group">%s%s%s</div>';

    /**
     * {@inheritDoc}
     * @todo To avoid confusion: data param is not used because
     * it has no value. We do not store anything in the file field on DB.
     *
     * In this case, it renders the output based on the given value of data.
     */
    public function renderInput($table, $field, $data = '', array $options = [])
    {
        $entity = Hash::get($options, 'entity');
        if (empty($entity)) {
            $result = $this->_renderInputWithoutData($table, $field, $options);
        } else {
            $result = $this->_renderInputWithData($table, $field, $options);
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

        $hiddenIds = $this->cakeView->Form->hidden(
            $this->_getFieldName($table, $field, $options) . '_ids][',
            [
                'class' => Inflector::underscore(str_replace('.', '_',$this->_getFieldName($table, $field, $options) . '_ids')),
                'value' => ''
            ]
        );

        return sprintf(self::WRAPPER, $label, $uploadField, $hiddenIds);
    }

    /**
     * Renders new file input field with value. Applicable for edit action.
     *
     * @param  Table $table Table
     * @param  string $field Field
     * @param  array $options Options
     * @return string HTML input field with data attribute.
     */
    protected function _renderInputWithData($table, $field, $options)
    {
        $files = [];
        $hiddenIds = '';

        $fileUploadsUtils = new FileUploadsUtils($table);
        $entity = Hash::get($options, 'entity');

        $entities = $fileUploadsUtils->getFiles($entity->get('id'));

        if (is_null($entities)) {
            return $this->_renderInputWithoutData($table, $field, $options);
        }

        foreach ($entities as $file) {
            $files[] = [
                'id' => $file->id,
                'path' => $file->path
            ];

            $hiddenIds .= $this->cakeView->Form->hidden(
                $this->_getFieldName($table, $field, $options) . '_ids][',
                [
                    'class' => Inflector::underscore(str_replace('.', '_',$this->_getFieldName($table, $field, $options) . '_ids')),
                    'value' => $file->id
                ]
            );
        }

        $label = $this->cakeView->Form->label($field);

        $uploadField = $this->cakeView->Form->file(
            $this->_getFieldName($table, $field, $options) . '[]',
            [
                'multiple' => true,
                'data-document-id' => $entity->get('id'),
                'data-files' => json_encode($files),
            ]
        );

        return sprintf(self::WRAPPER, $label, $uploadField, $hiddenIds);
    }

    /**
     * {@inheritDoc}
     */
    public function renderValue($table, $field, $data, array $options = [])
    {
        $data = $options['entity']['id'];

        return parent::renderValue($table, $field, $data, $options);
    }
}
