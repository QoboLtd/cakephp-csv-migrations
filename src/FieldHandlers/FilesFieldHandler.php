<?php
namespace CsvMigrations\FieldHandlers;

use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use Cake\View\Helper\IdGeneratorTrait;
use CsvMigrations\FieldHandlers\RelatedFieldHandler;

class FilesFieldHandler extends RelatedFieldHandler
{
    /**
     * Action name for file edit
     */
    const ACTION_EDIT = 'edit';

    /**
     * Action name for file add
     */
    const ACTION_ADD = 'add';

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
}
