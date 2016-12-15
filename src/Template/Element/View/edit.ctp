<?php
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
        'Edit {0}',
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
];
if (!empty($this->request->query['embedded'])) {
    $formOptions['class'] = 'embeddedForm';

    if (!empty($this->request->query['modal_id'])) {
        $formOptions['data-modal_id'] = $this->request->query['modal_id'];
    } else {
        $formOptions['data-modal_id'] = $this->request->query['foreign_key'] . '_modal';
    }

    $formOptions['data-field_name'] = $this->request->query['foreign_key'];
    $parts = explode('.', $this->request->query['embedded']);
    $first = array_shift($parts);
    $formOptions['data-embedded'] = $first . (!empty($parts) ? '[' . implode('][', $parts) . ']' : '');
    $formOptions['url']['prefix'] = 'api';
}

/**
 * @todo Need to handle this for the forms without upload field.
 * @var array
 */
$formOptions['type'] = 'file';
?>
<div class="row">
    <div class="col-xs-12">
    <?php
        /**
         * Conversion logic
         * @todo probably this has to be moved to another plugin
         */
    if (!$this->request->param('pass.conversion')) {
        echo $this->Form->create($options['entity'], $formOptions);
    }
    ?>
        <fieldset>
            <legend><?= $options['title'] ?></legend>
            <?php
            if (!empty($options['fields'])) {
                $embeddedFields = [];
                $embeddedForms = [];
                $embeddedDirty = false;
                foreach ($options['fields'] as $panelName => $panelFields) {
                    echo '<div class="panel panel-default">';
                    echo '<div class="panel-heading">';
                    echo '<h3 class="panel-title"><strong>' . $panelName . '</strong></h3>';
                    echo '</div>';
                    echo '<div class="panel-body">';
                    foreach ($panelFields as $subFields) {
                        echo '<div class="row">';
                        foreach ($subFields as $field) {
                            if ('' !== trim($field['name'])) {
                                /*
                                embedded field
                                 */
                                if ('EMBEDDED' === $field['name']) {
                                    $embeddedDirty = true;
                                    continue;
                                }

                                $renderOptions = [
                                    'entity' => $options['entity']
                                ];

                                if ($embeddedDirty) {
                                    $embeddedFields[] = $field;
                                    $renderOptions['embModal'] = true;
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
                                if (!empty($this->request->query['embedded'])) {
                                    $renderOptions['embedded'] = $this->request->query['embedded'];
                                }
                                $input = $fhf->renderInput(
                                    $tableName,
                                    $field['name'],
                                    $options['entity']->{$field['name']},
                                    $renderOptions
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
        </fieldset>
    <?php
        /**
         * Conversion logic
         * @todo probably this has to be moved to another plugin
         */
    if (!$this->request->param('pass.conversion')) {
        echo $this->Form->button(__('Submit'), ['name' => 'btn_operation', 'value' => 'submit', 'class' => 'btn btn-primary']);

        if (empty($this->request->query['embedded'])) {
            echo $this->Form->button(__('Cancel'), ['name' => 'btn_operation', 'value' => 'cancel', 'class' => 'btn']);
        }

        echo $this->Form->end();
    }
    ?>
        <?php
        /*
        Fetch embedded module(s) using CakePHP's requestAction() method
         */
        if (!empty($embeddedFields) && !$this->request->param('pass.conversion')) :
            foreach ($embeddedFields as $embeddedField) :
                $embeddedFieldName = substr($embeddedField['name'], strrpos($embeddedField['name'], '.') + 1);
                list($embeddedPlugin, $embeddedController) = pluginSplit(
                    substr($embeddedField['name'], 0, strrpos($embeddedField['name'], '.'))
                );

                $embeddedAssocName = CsvMigrationsUtils::createAssociationName(
                    $embeddedPlugin . $embeddedController,
                    $embeddedFieldName
                );

                $url = [
                    'plugin' => $embeddedPlugin,
                    'controller' => $embeddedController,
                    'action' => 'add'
                ];

                /*
                @note this only works for belongsTo for now.
                 */
                $embeddedAssocName = Inflector::underscore(Inflector::singularize($embeddedAssocName));
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
                            $url,
                            [
                                'query' => [
                                    'embedded' => $this->request->controller . '.' . $embeddedAssocName,
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
    </div>
</div>
<?php
/**
 * @todo  Load when needed.
 * - When there is file input
 * - load these files only if foreign/related field exists
 */
echo $this->element('CsvMigrations.common_js_libs');
?>
