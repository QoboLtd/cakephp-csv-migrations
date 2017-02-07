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
     * Set default options
     *
     * Set default options from the upstream classes and
     * add default image size.
     *
     * @return void
     */
    protected function setDefaultOptions()
    {
        parent::setDefaultOptions();
        $this->defaultOptions['imageSize'] = getenv('DEFAULT_IMAGE_SIZE');
    }

    /**
     * Check if specified image version exists
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
     * Generates thumbnails html markup.
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
     * Render new file input field with no value
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
            $result = $this->_thumbnailsHtml($entities, $fileUploadsUtils, $defaultOptions);
        }

        return $result;
    }
}
