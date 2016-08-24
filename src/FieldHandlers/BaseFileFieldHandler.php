<?php
namespace CsvMigrations\FieldHandlers;

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
     * Thumbnail html markup
     */
    const THUMBNAIL_HTML = '<div class="thumbnail"><img src="%s" /></div>';

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
            'value' => $relatedProperties['dispFieldVal'],
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

        $input['html'] .= $this->cakeView->Form->input($fieldName, ['type' => 'hidden', 'value' => $data]);

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
            'action' => !is_null($data) ? static::ACTION_EDIT : static::ACTION_ADD,
            !is_null($data) ? $data : null
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
        $fileUploadsUtils = new FileUploadsUtils($table);
        $result = null;

        if (empty($data)) {
            return $result;
        }

        $entities = $fileUploadsUtils->getFiles($data);

        if (empty($entities)) {
            return $result;
        }

        $result = $this->_thumbnailsHtml($entities);

        return $result;
    }

    /**
     * Method that generates and returns thumbnails html markup.
     *
     * @param  \Cake\ORM\ResultSet $entities File Entities
     * @return string
     */
    protected function _thumbnailsHtml($entities)
    {
        $result = null;
        $colWidth = static::GRID_COUNT / static::THUMBNAIL_LIMIT;
        $count = 0;
        $rows = [];

        foreach ($entities as $k => $entity) {
            if ($k >= static::THUMBNAIL_LIMIT) {
                break;
            }

            $result .= sprintf(
                static::GRID_COL_HTML,
                $colWidth,
                $colWidth,
                $colWidth,
                $colWidth,
                sprintf(static::THUMBNAIL_HTML, $entity->path)
            );
        }

        $result = sprintf(static::GRID_ROW_HTML, $result);

        return $result;
    }
}
