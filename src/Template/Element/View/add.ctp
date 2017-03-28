<?php
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use CsvMigrations\CsvMigrationsUtils;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;

$fhf = new FieldHandlerFactory($this);
$defaultOptions = [
    'title' => null,
    'entity' => null,
    'fields' => [],
];
if (empty($options)) {
    $options = [];
}
$options = array_merge($defaultOptions, $options);

// Get title from the entity
if (empty($options['title'])) {
    $options['title'] = __(
        'Create {0}',
        Inflector::singularize(Inflector::humanize(Inflector::underscore($moduleAlias)))
    );
}

$formOptions = [
    'url' => [
        'plugin' => $this->request->plugin,
        'controller' => $this->request->controller,
        'action' => $this->request->action
    ],
    'data-panels-url' => $this->Url->build([
        'prefix' => 'api',
        'plugin' => $this->request->plugin,
        'controller' => $this->request->controller,
        'action' => 'panels'
    ]),
    'name' => Inflector::dasherize($moduleAlias),
    'type' => 'file',
];

if (!empty($this->request->query['embedded'])) {
    $embControllerName = $this->request->controller;
    $formOptions['url']['prefix'] = 'api';
    $formOptions['class'] = 'embeddedForm';

    if (!empty($this->request->query['modal_id'])) {
        $formOptions['data-modal_id'] = $this->request->query['modal_id'];
    } else {
        $formOptions['data-modal_id'] = $this->request->query['foreign_key'] . '_modal';
    }

    if (!empty($this->request->plugin)) {
        $embControllerName = $this->request->plugin . '.' . $embControllerName;
    }
    $formOptions['data-display_field'] = TableRegistry::get($embControllerName)->displayField();
    $formOptions['data-field_id'] = $this->request->query['foreign_key'];
    $formOptions['data-embedded'] = $this->request->query['embedded'];
}
?>
<section class="content-header">
    <h4><?= $options['title'] ?></h4>
</section>
<section class="content">
    <?php
    /**
     * Conversion logic
     * @todo probably this has to be moved to another plugin
     */
    if (!$this->request->param('pass.conversion')) {
        echo $this->Form->create($options['entity'], $formOptions);
    }
    ?>
    <?php
    if (!empty($options['fields'])) {
        $embeddedFields = [];
        $embeddedForms = [];
        $embeddedDirty = false;
        foreach ($options['fields'] as $panelName => $panelFields) {
            echo '<div class="box box-default" data-provide="dynamic-panel">';
            echo '<div class="box-header with-border">';
            echo '<h3 class="box-title" data-title="dynamic-panel-title">' . $panelName . '</h3>';
            echo '</div>';
            echo '<div class="box-body">';
            foreach ($panelFields as $subFields) {
                echo '<div class="row">';
                foreach ($subFields as $field) {
                    if ('' !== trim($field['name'])) {
                        // embedded field
                        if ('EMBEDDED' === $field['name']) {
                            $embeddedDirty = true;
                            continue;
                        }

                        $handlerOptions = [
                            'entity' => $this->request
                        ];

                        if ($embeddedDirty) {
                            $embeddedFields[] = $field;
                            $handlerOptions['embModal'] = true;
                            $field['name'] = substr($field['name'], strrpos($field['name'], '.') + 1);
                        }

                        echo '<div class="col-xs-12 col-md-6">';
                        /*
                        non-embedded field
                         */
                        $tableName = $field['model'];
                        if (!is_null($field['plugin'])) {
                            $tableName = $field['plugin'] . '.' . $tableName;
                        }

                        // allowing query params to define field values.
                        $fieldValue = null;
                        if (!empty($this->request->query($field['name']))) {
                            $fieldValue = $this->request->query($field['name']);
                        }

                        if (isset($this->request->data[$field['name']])) {
                            $fieldValue = $this->request->data[$field['name']];
                        }

                        $input = $fhf->renderInput(
                            $tableName,
                            $field['name'],
                            $fieldValue,
                            $handlerOptions
                        );

                        if (is_string($input)) {
                            echo $input;
                        } elseif (is_array($input)) {
                            echo $input['html'];
                            if (isset($input['embeddedForm'])) {
                                $embeddedForms[] = $input['embeddedForm'];
                            }
                        }
                        echo '</div>';
                        $embeddedDirty = false;
                    } else {
                        echo '<div class="col-xs-12 col-md-6">';
                        echo '&nbsp;';
                        echo '</div>';
                    }
                }
                echo '</div>';
            }
            echo '</div>';
            echo '</div>';
        }
    }
    ?>
    <?php
    /**
     * Conversion logic
     * @todo probably this has to be moved to another plugin
     */
    if (!$this->request->param('pass.conversion')) {
        echo $this->Form->button(__('Submit'), ['name' => 'btn_operation', 'value' => 'submit', 'class' => 'btn btn-primary']);
        echo "&nbsp;";

        $cancelBtnOptions = [
            'name' => 'btn_operation',
            'value' => 'cancel',
            'class' => 'btn remove-client-validation',
        ];

        if ($this->request->query('embedded')) {
            $cancelBtnOptions = array_merge($cancelBtnOptions, [
                'type' => 'button',
                'aria-label' => 'Close',
                'data-dismiss' => 'modal',
            ]);
        }

        echo $this->Form->button(__('Cancel'), $cancelBtnOptions);
        echo $this->Form->end();
    }
    ?>
    <?php
    // Fetch embedded module(s) using CakePHP's requestAction() method, if request is not coming from requestAction()
    if (!empty($embeddedFields) && !$this->request->param('pass.conversion')) :
        foreach ($embeddedFields as $embeddedField) :
            $embeddedFieldName = substr($embeddedField['name'], strrpos($embeddedField['name'], '.') + 1);
            list($embeddedPlugin, $embeddedController) = pluginSplit(
                substr($embeddedField['name'], 0, strrpos($embeddedField['name'], '.'))
            );
        ?>
        <!-- Modal -->
        <div id="<?= $embeddedFieldName ?>_modal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                    <?php echo $this->requestAction(
                        [
                            'plugin' => $embeddedPlugin,
                            'controller' => $embeddedController,
                            'action' => $this->request->action
                        ],
                        [
                            'query' => [
                                'embedded' => $embeddedController,
                                'foreign_key' => $embeddedFieldName
                            ]
                        ]
                    ); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
        endforeach;
    endif;

    // print embedded forms
    if (!empty($embeddedForms)) {
        foreach ($embeddedForms as $embeddedForm) {
            echo $embeddedForm;
        }
    }
    ?>
</section>
<?php
/**
 * @todo  Load when needed.
 * - When there is file input
 * - load these files only if foreign/related field exists
 */
echo $this->element('CsvMigrations.common_js_libs');
