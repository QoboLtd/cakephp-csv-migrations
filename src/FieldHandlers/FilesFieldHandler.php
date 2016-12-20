<?php
namespace CsvMigrations\FieldHandlers;

use Cake\Core\Configure;
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
        $fieldName = $this->_getFieldName($table, $field);

        $uploadField = $this->cakeView->Form->file(
            $fieldName . '[]',
            [
                'multiple' => true,
                'data-upload-url' => sprintf("/api/%s/upload", Inflector::dasherize($table->table())),
            ]
        );

        $label = $this->cakeView->Form->label($field);

        $hiddenIds = $this->cakeView->Form->hidden(
            $this->_getFieldName($table, $field, $options) . '_ids][',
            [
                'class' => str_replace('.', '_', $fieldName . '_ids'),
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
     * @param  mixed $data Data
     * @return string HTML input field with data attribute.
     */
    protected function _renderInputWithData($table, $field, $data, $options)
    {
        $files = [];
        $hiddenIds = '';

        $fieldName = $this->_getFieldName($table, $field);


        $fileUploadsUtils = new FileUploadsUtils($table);

        $entities = $fileUploadsUtils->getFiles($table, $field, $data);

        if ($entities instanceof \Cake\ORM\ResultSet) {
            if (!$entities->count()) {
                return $this->_renderInputWithoutData($table, $field, $options);
            }
        }

        // @TODO: check if we return null anywhere, apart of ResultSet.
        // IF NOT: remove this block
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
                    'class' => str_replace('.', '_', $fieldName . '_ids'),
                    'value' => $file->id
                ]
            );
        }

        $label = $this->cakeView->Form->label($field);

        $uploadField = $this->cakeView->Form->file(
            $fieldName . '[]',
            [
                'multiple' => true,
                'data-document-id' => $data,
                'data-upload-url' => sprintf("/api/%s/upload", Inflector::dasherize($table->table())),
                //passed to generate previews
                'data-files' => json_encode($files),
            ]
        );

        return sprintf(self::WRAPPER, $label, $uploadField, $hiddenIds);
    }

    /**
     * Method that generates and returns file icons markup.
     *
     * @param ResultSet $entities File Entities
     * @param FileUploadsUtils $fileUploadsUtils fileUploadsUtils class object
     *
     * @return string
     */
    protected function _filesHtml($entities, FileUploadsUtils $fileUploadsUtils)
    {
        $result = null;
        $colWidth = static::GRID_COUNT / static::THUMBNAIL_LIMIT;
        $thumbnailUrl = 'CsvMigrations.thumbnails/' . static::NO_THUMBNAIL_FILE;

        foreach ($entities as $k => $entity) {
            if ($k >= static::THUMBNAIL_LIMIT) {
                break;
            }

            $thumbnailUrl = $this->_getFileIconUrl($entity->extension);
            $thumbnail = sprintf(
                static::THUMBNAIL_HTML,
                $this->cakeView->Html->image($thumbnailUrl, ['title' => $entity->filename])
            );

            $thumbnail = $this->cakeView->Html->link($thumbnail, $entity->path, ['escape' => false, 'target' => '_blank']);

            $result .= sprintf(
                static::GRID_COL_HTML,
                $colWidth,
                $colWidth,
                $colWidth,
                $colWidth,
                $thumbnail
            );
        }

        $result = sprintf(static::GRID_ROW_HTML, $result);

        return $result;
    }


    /**
     * {@inheritDoc}
     */
    public function renderValue($table, $field, $data, array $options = [])
    {
        $result = null;

        $data = $this->_getFieldValueFromData($field, $data);
        if (empty($data) && !empty($options['entity'])) {
            $data = $this->_getFieldValueFromData('id', $options['entity']);
        }

        if (empty($data)) {
            return $result;
        }

        $fileUploadsUtils = new FileUploadsUtils($table);
        $entities = $fileUploadsUtils->getFiles($table, $field, $data);

        if (!empty($entities)) {
            $result = $this->_filesHtml($entities, $fileUploadsUtils);
        }

        return $result;
    }
}
