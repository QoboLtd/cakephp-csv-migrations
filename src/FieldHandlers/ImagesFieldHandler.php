<?php
namespace CsvMigrations\FieldHandlers;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use CsvMigrations\FieldHandlers\BaseFileFieldHandler;
use CsvMigrations\FileUploadsUtils;

class ImagesFieldHandler extends BaseFileFieldHandler
{

    /**
     * Defines the layout of the wrapper
     * Expects the label and the actual field.
     */
    const WRAPPER = '<div class="form-group">%s%s%s</div>';

    /**
     * Method that checks if specified image version exists.
     *
     * @param  Entity $entity  Entity
     * @param  string           $version Image version
     * @param  \CsvMigrations\FileUploadsUtils $fileUploadsUtils fileUploadsUtils class object
     * @return bool
     */
    protected function _checkThumbnail($entity, $version, FileUploadsUtils $fileUploadsUtils)
    {
        // image version directory path
        $dir = realpath(WWW_ROOT . trim($entity->path, DS));
        $dir = dirname($dir) . DS . basename($dir, $entity->extension);
        $dir .= $version . '.' . $entity->extension;

        return file_exists($dir);
    }

    /**
     * Method that generates and returns thumbnails html markup.
     *
     * @param ResultSet $entities File Entities
     * @param FileUploadsUtils $fileUploadsUtils fileUploadsUtils class object
     * @param array $options for default thumbs versions and other setttings
     *
     * @return string
     */
    protected function _thumbnailsHtml($entities, FileUploadsUtils $fileUploadsUtils, $options = [])
    {
        $result = null;
        $colWidth = static::GRID_COUNT / static::THUMBNAIL_LIMIT;
        $thumbnailUrl = 'CsvMigrations.thumbnails/' . static::NO_THUMBNAIL_FILE;

        $hashes = Configure::read('FileStorage.imageHashes.file_storage');
        $extensions = $fileUploadsUtils->getImgExtensions();

        foreach ($entities as $k => $entity) {
            if ($k >= static::THUMBNAIL_LIMIT) {
                break;
            }

            if (in_array($entity->extension, $extensions)) {
                $thumbnailUrl = $entity->path;

                if (isset($hashes[$options['imageSize']])) {
                    $version = $hashes[$options['imageSize']];

                    $exists = $this->_checkThumbnail($entity, $version, $fileUploadsUtils);

                    if ($exists) {
                        $path = dirname($entity->path) . '/' . basename($entity->path, $entity->extension);
                        $path .= $version . '.' . $entity->extension;
                        $thumbnailUrl = $path;
                    }
                }
            }

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
            $this->_getFieldName($table, $field, $options) . '[]',
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
     * {@inheritDoc}
     */
    public function renderValue($table, $field, $data, array $options = [])
    {
        $result = null;
        $defaultOptions = ['imageSize' => getenv('DEFAULT_IMAGE_SIZE')];

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
            $result = $this->_thumbnailsHtml($entities, $fileUploadsUtils, $defaultOptions);
        }

        return $result;
    }
}
