<?php
namespace CsvMigrations\FieldHandlers;

use Cake\Utility\Inflector;
use CsvMigrations\FileUploadsUtils;

/**
 * BaseFileFieldHandler
 *
 * This class provides the fallback functionality that
 * is common to all file field handlers.
 */
class BaseFileFieldHandler extends BaseFieldHandler
{
    /**
     * HTML form field type
     */
    const INPUT_FIELD_TYPE = 'file';

    /**
     * CSS Framework grid columns number
     */
    const GRID_COUNT = 12;

    /**
     * Limit of thumbnails to display
     */
    const THUMBNAIL_LIMIT = 3;

    /**
     * CSS Framework row html markup
     */
    const GRID_ROW_HTML = '<div class="row">%s</div>';

    /**
     * CSS Framework row html markup
     */
    const GRID_COL_HTML = '<div class="col-xs-%d col-sm-%d col-md-%d col-lg-%d">%s</div>';

    /**
     * Default thumbnail file
     */
    const NO_THUMBNAIL_FILE = 'no-thumbnail.jpg';

    /**
     * Thumbnail html markup
     */
    const THUMBNAIL_HTML = '<div class="thumbnail">%s</div>';

    /**
     * Icon extension
     */
    const ICON_EXTENSION = 'png';

    /**
     * Icon size
     */
    const ICON_SIZE = '48';

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

        $fieldName = $this->table->aliasField($this->field);

        $entities = null;
        if (!empty($data)) {
            $fileUploadsUtils = new FileUploadsUtils($this->table);
            $entities = $fileUploadsUtils->getFiles($this->table, $this->field, $data);
        }

        $params = [
            'field' => $this->field,
            'name' => $fieldName,
            'type' => static::INPUT_FIELD_TYPE,
            'label' => $options['label'],
            'required' => $options['fieldDefinitions']->getRequired(),
            'value' => $data,
            'entities' => $entities,
            'table' => Inflector::dasherize($this->table->table())
        ];

        return $this->_renderElement('renderInput', $params, $options);
    }

    /**
     * Get options for field search
     *
     * This method prepares an array of search options, which includes
     * label, form input, supported search operators, etc.  The result
     * can be controlled with a variety of options.
     *
     * @param  array  $options Field options
     * @return array           Array of field input HTML, pre and post CSS, JS, etc
     */
    public function getSearchOptions(array $options = [])
    {
        return [];
    }

    /**
     * Get file icon url by file extension
     *
     * @param  string $extension File extension
     * @return string
     */
    protected function _getFileIconUrl($extension)
    {
        $file = strtolower($extension);
        $webroot = dirname(__FILE__) . DS . '..' . DS . '..' . DS . 'webroot' . DS;
        $filesDir = $webroot . 'img' . DS . 'icons' . DS . 'files' . DS . '48px' . DS;

        if (!file_exists($filesDir . $file . '.' . static::ICON_EXTENSION)) {
            $file = '_blank';
        }

        return $this->cakeView->Url->image(
            'CsvMigrations.icons/files/' . static::ICON_SIZE . 'px/' . $file . '.' . static::ICON_EXTENSION
        );
    }
}
