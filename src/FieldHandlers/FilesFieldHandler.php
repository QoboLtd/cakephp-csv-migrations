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
     * Render field input
     *
     * This method prepares the form input for the given field,
     * including the input itself, label, pre-populated value,
     * and so on.  The result can be controlled via the variety
     * of options.
     *
     * @param  string $data    Field data
     * @param  array  $options Field options
     * @return string          Field input HTML
     */
    public function renderInput($data = '', array $options = [])
    {
        $options = array_merge($this->defaultOptions, $this->fixOptions($options));
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
     * Renders new file input field with no value
     *
     * Applicable for add action.
     *
     * @param  array $options Options
     * @return string HTML input field.
     */
    protected function _renderInputWithoutData($options)
    {
        $fieldName = $this->table->aliasField($this->field);

        $uploadField = $this->cakeView->Form->file(
            $fieldName . '[]',
            [
                'multiple' => true,
                'data-upload-url' => sprintf("/api/%s/upload", Inflector::dasherize($this->table->table())),
            ]
        );

        $label = $options['label'] ? $this->cakeView->Form->label($fieldName . '[]', $options['label']) : '';

        $hiddenIds = $this->cakeView->Form->hidden(
            $fieldName . '_ids][',
            [
                'class' => str_replace('.', '_', $fieldName . '_ids'),
                'value' => ''
            ]
        );

        return sprintf(self::WRAPPER, $label, $uploadField, $hiddenIds);
    }

    /**
     * Render new file input field with value
     *
     * Applicable for edit action.
     *
     * @param  mixed $data Data
     * @param  array $options Options
     * @return string HTML input field with data attribute.
     */
    protected function _renderInputWithData($data, $options)
    {
        $files = [];
        $hiddenIds = '';

        $fieldName = $this->table->aliasField($this->field);

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
                $fieldName . '_ids][',
                [
                    'class' => str_replace('.', '_', $fieldName . '_ids'),
                    'value' => $file->id
                ]
            );
        }

        $label = $options['label'] ? $this->cakeView->Form->label($fieldName . '[]', $options['label']) : '';

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
     * Generates file icons markup
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
     * Render field value
     *
     * This method prepares the output of the value for the given
     * field.  The result can be controlled via the variety of
     * options.
     *
     * @param  string $data    Field data
     * @param  array  $options Field options
     * @return string          Field value
     */
    public function renderValue($data, array $options = [])
    {
        $result = null;
        $options = array_merge($this->defaultOptions, $this->fixOptions($options));

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
