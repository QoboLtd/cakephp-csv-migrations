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
        $fieldName = $this->_getFieldName();

        $uploadField = $this->cakeView->Form->file(
            $fieldName . '[]',
            [
                'multiple' => true,
                'data-upload-url' => sprintf("/api/%s/upload", Inflector::dasherize($this->table->table())),
            ]
        );

        $label = $this->cakeView->Form->label($this->field);

        $hiddenIds = $this->cakeView->Form->hidden(
            $this->_getFieldName($options) . '_ids][',
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
     * @param  mixed $data Data
     * @param  array $options Options
     * @return string HTML input field with data attribute.
     */
    protected function _renderInputWithData($data, $options)
    {
        $files = [];
        $hiddenIds = '';

        $fieldName = $this->_getFieldName();


        $fileUploadsUtils = new FileUploadsUtils($this->table);

        $entities = $fileUploadsUtils->getFiles($this->table, $this->field, $data);

        if ($entities instanceof \Cake\ORM\ResultSet) {
            if (!$entities->count()) {
                return $this->_renderInputWithoutData($options);
            }
        }

        // @TODO: check if we return null anywhere, apart of ResultSet.
        // IF NOT: remove this block
        if (is_null($entities)) {
            return $this->_renderInputWithoutData($options);
        }

        foreach ($entities as $file) {
            $files[] = [
                'id' => $file->id,
                'path' => $file->path
            ];

            $hiddenIds .= $this->cakeView->Form->hidden(
                $this->_getFieldName($options) . '_ids][',
                [
                    'class' => str_replace('.', '_', $fieldName . '_ids'),
                    'value' => $file->id
                ]
            );
        }

        $label = $this->cakeView->Form->label($this->field);

        $uploadField = $this->cakeView->Form->file(
            $fieldName . '[]',
            [
                'multiple' => true,
                'data-document-id' => $data,
                'data-upload-url' => sprintf("/api/%s/upload", Inflector::dasherize($this->table->table())),
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
    public function renderValue($data, array $options = [])
    {
        $result = null;

        $data = $this->_getFieldValueFromData($data);
        if (empty($data) && !empty($options['entity'])) {
            $data = $this->_getFieldValueFromData($options['entity'], 'id');
        }

        if (empty($data)) {
            return $result;
        }

        $fileUploadsUtils = new FileUploadsUtils($this->table);
        $entities = $fileUploadsUtils->getFiles($this->table, $this->field, $data);

        if (!empty($entities)) {
            $result = $this->_filesHtml($entities, $fileUploadsUtils);
        }

        return $result;
    }
}
