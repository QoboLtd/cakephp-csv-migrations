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
        'Edit {0}',
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
                                echo '<div class="col-xs-12 col-md-6">';
                                if ('' !== trim($field['name']) && !$embeddedDirty) {
                                    /*
                                    embedded field
                                     */
                                    if ('EMBEDDED' === $field['name']) {
                                        $embeddedDirty = true;
                                    }

                                    /*
                                    non-embedded field
                                     */
                                    if (!$embeddedDirty) {
                                        $tableName = $field['model'];
                                        if (!is_null($field['plugin'])) {
                                            $tableName = $field['plugin'] . '.' . $tableName;
                                        }
                                        $handlerOptions = [];
                                        if (!empty($this->request->query['embedded'])) {
                                            $handlerOptions['embedded'] = $this->request->query['embedded'];
                                        }
                                        echo $fhf->renderInput(
                                            $tableName,
                                            $field['name'],
                                            $options['entity']->$field['name'],
                                            $handlerOptions
                                        );
                                    }
                                } elseif ('' !== trim($field['name'])) {
                                    $embeddedFields[] = $field;
                                    $embeddedDirty = false;
                                    echo '&nbsp;';
                                } else {
                                    echo '&nbsp;';
                                }
                                echo '</div>';
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

                            /*
                            If embedded record is set load edit View
                             */
                            if (!empty($options['entity']->$embeddedFieldName)) {
                                echo $this->Form->hidden(
                                    $this->request->controller . '.' . $embeddedAssocName . '.id',
                                    ['value' => $options['entity']->$embeddedFieldName]
                                );
                                echo $this->requestAction(
                                    [
                                        'plugin' => $embeddedPlugin,
                                        'controller' => $embeddedController,
                                        'action' => $this->request->action
                                    ],
                                    [
                                        'query' => ['embedded' => $this->request->controller . '.' . $embeddedAssocName],
                                        'pass' => [$options['entity']->$embeddedFieldName]
                                    ]
                                );
                            }
                            /*
                            else load the add View
                             */
                            else {
                                echo $this->requestAction(
                                    [
                                        'plugin' => $embeddedPlugin,
                                        'controller' => $embeddedController,
                                        'action' => 'add'
                                    ],
                                    [
                                        'query' => ['embedded' => $this->request->controller . '.' . $embeddedAssocName]
                                    ]
                                );
                            }
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