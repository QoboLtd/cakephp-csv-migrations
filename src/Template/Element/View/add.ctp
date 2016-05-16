<?php
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
        'Add {0}',
        Inflector::singularize(Inflector::humanize(Inflector::underscore($moduleAlias)))
    );
}
?>

<div class="row">
    <div class="col-xs-12">
        <?php if (empty($this->request->query['embedded'])) : ?>
            <?= $this->Form->create($options['entity']); ?>
        <?php endif; ?>
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

                                    $handlerOptions = [];

                                    if ($embeddedDirty) {
                                        $embeddedFields[] = $field;
                                        $handlerOptions['collapseEmbedded'] = true;
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
                                        $handlerOptions['embedded'] = $this->request->query['embedded'];
                                    }
                                    echo $fhf->renderInput(
                                        $tableName,
                                        $field['name'],
                                        isset($this->request->data[$field['name']])
                                            ? $this->request->data[$field['name']]
                                            : null,
                                        $handlerOptions
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

                        if (empty($embeddedFields)) {
                            continue;
                        }

                        /*
                        Fetch embedded module(s) using CakePHP's requestAction() method
                         */
                        foreach ($embeddedFields as $embeddedField) {
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
                            <div class="collapse" id="<?= $embeddedFieldName ?>_collapse">
                                <div class="well">
                                <?php echo $this->requestAction(
                                    [
                                        'plugin' => $embeddedPlugin,
                                        'controller' => $embeddedController,
                                        'action' => $this->request->action
                                    ],
                                    [
                                        'query' => ['embedded' => $this->request->controller . '.' . $embeddedAssocName]
                                    ]
                                ); ?>
                                </div>
                            </div>
                        <?php
                        }
                        $embeddedFields = [];
                    }
                }
            ?>
        </fieldset>
        <?php if (empty($this->request->query['embedded'])) : ?>
            <?= $this->Form->button(__('Submit'), ['class' => 'btn btn-primary']) ?>
            <?= $this->Form->end() ?>
        <?php endif; ?>
    </div>
</div>

<?php
// enable typeahead library
// @todo load these files only if foreign/related field exists
echo $this->Html->script('CsvMigrations.bootstrap-typeahead.min.js', ['block' => 'scriptBottom']);
echo $this->Html->script('CsvMigrations.typeahead', ['block' => 'scriptBottom']);
?>