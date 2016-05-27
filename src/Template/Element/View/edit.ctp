<?php
use Cake\Core\Configure;
use Cake\Utility\Inflector;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;
use CsvMigrations\CsvMigrationsUtils;

$fhf = new FieldHandlerFactory();

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
    ]
];
if (!empty($this->request->query['embedded'])) {
    $formOptions['class'] = 'embeddedForm';
    $formOptions['data-modal_id'] = $this->request->query['foreign_key'] . '_modal';
    $formOptions['data-field_name'] = $this->request->query['foreign_key'] . '_label';
    $parts = explode('.', $this->request->query['embedded']);
    $first = array_shift($parts);
    $formOptions['data-embedded'] = $first . (!empty($parts) ? '[' . implode('][', $parts) . ']' : '');
    $formOptions['url']['prefix'] = 'api';
}
/**
 * @todo Need to handle this for the forms without upload field.
 * @var array
 */
$formOptions = ['type' => 'file'];
?>
<div class="row">
    <div class="col-xs-12">
        <?= $this->Form->create($options['entity'], $formOptions); ?>
        <fieldset>
            <legend><?= $options['title'] ?></legend>
            <?php
                if (!empty($options['fields'])) {
                    $embeddedFields = [];
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
                                    echo $fhf->renderInput(
                                        $tableName,
                                        $field['name'],
                                        $options['entity']->$field['name'],
                                        $renderOptions
                                    );
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
        <?= $this->Form->button(__('Submit'), ['class' => 'btn btn-primary']) ?>
        <?= $this->Form->end() ?>
        <?php
        /*
        Fetch embedded module(s) using CakePHP's requestAction() method
         */
        if (!empty($embeddedFields)) :
            foreach ($embeddedFields as $embeddedField) :
                $embeddedFieldName = substr($embeddedField['name'], strrpos($embeddedField['name'], '.') + 1);
                list($embeddedPlugin, $embeddedController) = pluginSplit(
                    substr($embeddedField['name'], 0, strrpos($embeddedField['name'], '.'))
                );

                $embeddedAssocName = CsvMigrationsUtils::createAssociationName(
                    $embeddedPlugin . $embeddedController,
                    $embeddedFieldName
                );

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
                            [
                                'plugin' => $embeddedPlugin,
                                'controller' => $embeddedController,
                                'action' => 'add'
                            ],
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
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<?php
/**
 * @todo  Load when needed.
 * - When there is file input
 * - load these files only if foreign/related field exists
 */
echo $this->Html->css('QoboAdminPanel.fileinput.min', ['block' => 'cssBottom']);
echo $this->Html->script('QoboAdminPanel.canvas-to-blob.min', ['block' => 'scriptBottom']);
echo $this->Html->script('QoboAdminPanel.fileinput.min', ['block' => 'scriptBottom']);
echo $this->Html->script('QoboAdminPanel.fileinput-load', ['block' => 'scriptBottom']);
echo $this->Html->script('CsvMigrations.bootstrap-typeahead.min.js', ['block' => 'scriptBottom']);
echo $this->Html->scriptBlock(
    'typeahead_options = ' . json_encode(Configure::read('CsvMigrations.typeahead')) . ';',
    ['block' => 'scriptBottom']
);
echo $this->Html->script('CsvMigrations.typeahead', ['block' => 'scriptBottom']);
echo $this->Html->script('CsvMigrations.embedded', ['block' => 'scriptBottom']);
?>
