<?php
namespace CsvMigrations\FieldHandlers;

use Cake\Core\Configure;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use CsvMigrations\FieldHandlers\RelatedFieldHandler;
use CsvMigrations\FileUploadsUtils;

class BaseFileFieldHandler extends RelatedFieldHandler
{
    /**
     * Action name for file edit
     */
    const ACTION_EDIT = 'edit';

    /**
     * Action name for file add
     */
    const ACTION_ADD = 'add';

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
     * Embedded Form html markup
     */
    const EMBEDDED_FORM_HTML = '
        <div id="%s_modal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">%s</div>
                </div>
            </div>
        </div>
    ';

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
     * {@inheritDoc}
     */
    public function renderInput($table, $field, $data = '', array $options = [])
    {
        $relatedProperties = $this->_getRelatedProperties($options['fieldDefinitions']->getLimit(), $data);

        $fieldName = $this->_getFieldName($table, $field, $options);

        $input['html'] = '';
        $input['html'] .= '<div class="form-group' . ((bool)$options['fieldDefinitions']->getRequired() ? ' required' : '') . '">';
        $input['html'] .= $this->cakeView->Form->label($field);
        $input['html'] .= '<div class="input-group">';

        $input['html'] .= $this->cakeView->Form->input($field, [
            'label' => false,
            'name' => false,
            'id' => $field . static::LABEL_FIELD_SUFFIX,
            'type' => 'text',
            'disabled' => true,
            'value' => (!empty($relatedProperties['entity'])) ? $relatedProperties['dispFieldVal'] : '',
            'escape' => false,
            'data-id' => $this->_domId($fieldName),
            'required' => (bool)$options['fieldDefinitions']->getRequired()
        ]);

        $input['html'] .= '<div class="input-group-btn">';
        $input['html'] .= '<button type="button" class="btn btn-default" data-toggle="modal" data-target="#' . $field . '_modal">';
        $input['html'] .= '<span class="glyphicon glyphicon-edit" aria-hidden="true"></span>';
        $input['html'] .= '</button>';
        $input['html'] .= '</div>';

        $input['html'] .= '</div>';
        $input['html'] .= '</div>';

        // @NOTE: trashed records will return null entity,
        // we must pay attention for embedded forms passing $data.
        // Trashed entity should generate /documents/add URL, not edit.
        if (empty($relatedProperties['entity'])) {
            $data = null;
        }

        $input['html'] .= $this->cakeView->Form->input($fieldName, ['type' => 'hidden', 'value' => (!is_null($data) ? $data : '')]);

        $embeddedAssocName = null;
        foreach ($table->associations() as $association) {
            if ($association->foreignKey() === $field) {
                $embeddedAssocName = $association->name();
                break;
            }
        }

        list($filePlugin, $fileController) = pluginSplit($options['fieldDefinitions']->getLimit());

        $url = $this->cakeView->Url->build([
            'plugin' => $filePlugin,
            'controller' => $fileController,
            'action' => !empty($data) ? static::ACTION_EDIT : static::ACTION_ADD,
            !empty($data) ? $data : null
        ]);

        $embeddedAssocName = Inflector::underscore(Inflector::singularize($embeddedAssocName));

        $embeddedForm = $this->cakeView->requestAction(
            $url,
            [
                'query' => [
                    'embedded' => $fileController . '.' . $embeddedAssocName,
                    'foreign_key' => $field
                ]
            ]
        );
        $input['embeddedForm'] = sprintf(static::EMBEDDED_FORM_HTML, $field, $embeddedForm);

        return $input;
    }

    /**
     * {@inheritDoc}
     */
    public function renderValue($table, $field, $data, array $options = [])
    {
        $result = null;

        if (empty($data)) {
            return $result;
        }

        if (empty($entities)) {
            return $result;
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function renderSearchInput($table, $field, array $options = [])
    {
        return [];
    }

    /**
     * Get appropriate file icon url by file extension.
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
