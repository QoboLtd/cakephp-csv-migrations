<?php
use \Cake\Utility\Inflector;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;

$fhf = new FieldHandlerFactory();

$panels = [];
if (!empty($csvAssociatedRecords['oneToMany'])) {
    foreach ($csvAssociatedRecords['oneToMany'] as $tabName => $assocData) {
        if (0 === $assocData['records']->count()) {
            unset($csvAssociatedRecords['oneToMany'][$tabName]);
        } else {
            $panels[$tabName] = $csvAssociatedRecords['oneToMany'][$tabName];
        }
    }
}

/*
list of embedded fields to generate modals from
 */
$embeddedFields = [];
if (!empty($csvAssociatedRecords['manyToMany'])) {
    foreach ($csvAssociatedRecords['manyToMany'] as $tabName => $assocData) {
        /*
        add to embedded fields
         */
        $embeddedFields[] = $assocData['class_name'] . '.' . $assocData['foreign_key'];
        if (0 === count($assocData['records'])) {
            unset($csvAssociatedRecords['manyToMany'][$tabName]);
        } else {
            $panels[$tabName] = $csvAssociatedRecords['manyToMany'][$tabName];
        }
    }
}
?>

<?php if (!empty($panels)) : ?>
<div class="row">
    <div class="col-xs-12">
        <hr />
        <ul id="relatedTabs" class="nav nav-tabs" role="tablist">
<?php
    $active = 'active';
    foreach ($panels as $tabName => $assocData) :
?>
            <li role="presentation" class="<?= $active; ?>">
                <a href="#<?= $tabName; ?>" aria-controls="<?= $tabName; ?>" role="tab" data-toggle="tab">
                    <?php
                        $tableName = Inflector::humanize($assocData['table_name']);
                        $fieldName = trim(str_replace($tableName, '', Inflector::humanize(Inflector::tableize($tabName))));
                        if (!empty($fieldName)) {
                            $fieldName = ' <small>(' . $fieldName . ')</small>';
                        }
                    ?>
                    <?= $tableName . $fieldName ?>
                </a>
            </li>
<?php
    $active = '';
    endforeach;
?>
        </ul>
        <div class="tab-content">
<?php
    $active = 'active';
    foreach ($panels as $assocName => $assocData) {
    ?>
            <div role="tabpanel" class="tab-pane <?= $active; ?>" id="<?= $assocName; ?>">
            <?php
            /*
            display typeahead field for adding/linking associated records,
            filtered from embeddedFields array.
             */
            if (in_array($assocData['class_name'] . '.' . $assocData['foreign_key'], $embeddedFields)) {
                $formOptions = [
                    'url' => [
                        'plugin' => $this->request->plugin,
                        'controller' => $this->request->controller,
                        'action' => 'edit',
                        $this->request->pass[0]
                    ]
                ];

                echo $this->Form->create(null, $formOptions);
                /*
                non-embedded field
                 */
                $tableName = $this->request->controller;
                if (!is_null($this->request->plugin)) {
                    $tableName = $this->request->plugin . '.' . $tableName;
                }

                $handlerOptions = [];
                /*
                set associated table name to be used on input field's name
                 */
                $handlerOptions['associated_table_name'] = $assocData['table_name'];
                /*
                set embedded modal flag
                 */
                $handlerOptions['embModal'] = true;
                /*
                set field type to 'hasMany'
                 */
                $handlerOptions['fieldDefinitions']['type'] = 'hasMany:' . $assocData['class_name'];
                /*
                set field as required
                 */
                $handlerOptions['fieldDefinitions']['required'] = true;

                /*
                display typeahead field for associated module(s)
                 */
                echo $fhf->renderInput(
                    $tableName,
                    $assocData['foreign_key'],
                    null,
                    $handlerOptions
                );

                /*
                set existing related records as hidden fields
                 */
                foreach ($assocData['records'] as $record) {
                    echo $this->Form->hidden($assocData['table_name'] . '._ids[]', [
                        'value' => $record->{$assocData['primary_key']}
                    ]);
                }

                echo $this->Form->end();
            }
            ?>
                <div class=" table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                            <?php foreach ($assocData['fields'] as $assocField) : ?>
                                <th><?= $this->Paginator->sort($assocField); ?></th>
                            <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($assocData['records'] as $record) : ?>
                            <tr>
                            <?php foreach ($assocData['fields'] as $assocField) : ?>
                                <?php if ('' !== trim($record->$assocField)) : ?>
                                <td>
                                <?php
                                    if (is_bool($record->$assocField)) {
                                        echo $record->$assocField ? __('Yes') : __('No');
                                    } else {
                                        if ($assocData['display_field'] === $assocField) {
                                            list($assocPlugin, $assocModel) = pluginSplit($assocData['class_name']);
                                            echo $this->Html->link(
                                                h($record->$assocField), [
                                                    'plugin' => $assocPlugin,
                                                    'controller' => $assocModel,
                                                    'action' => 'view',
                                                    $record->$assocData['primary_key']
                                                ]
                                            );
                                        } else {
                                            echo h($record->$assocField);
                                        }
                                    }
                                ?>
                                </td>
                                <?php else : ?>
                                <td>&nbsp;</td>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php $active = '';
    }
?>
        </div>
    </div>
</div>

<?php
/*
Fetch embedded module(s) using CakePHP's requestAction() method
 */
if (!empty($embeddedFields)) :
    foreach ($embeddedFields as $embeddedField) :
        $embeddedFieldName = substr($embeddedField, strrpos($embeddedField, '.') + 1);
        list($embeddedPlugin, $embeddedController) = pluginSplit(
            substr($embeddedField, 0, strrpos($embeddedField, '.'))
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
                        'action' => 'add'
                    ],
                    [
                        'query' => [
                            'embedded' => $this->request->controller,
                            'foreign_key' => $embeddedFieldName
                        ]
                    ]
                ); ?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
<?php
// enable typeahead and embedded library
echo $this->Html->script('CsvMigrations.bootstrap-typeahead.min.js', ['block' => 'scriptBottom']);
echo $this->Html->script('CsvMigrations.typeahead', ['block' => 'scriptBottom']);
echo $this->Html->script('CsvMigrations.embedded', ['block' => 'scriptBottom']);
?>
<?php endif; ?>

<?php endif; ?>