<?php
use Cake\Event\Event;
use Cake\Utility\Inflector;
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
$embFields = [];
if (!empty($csvAssociatedRecords['manyToMany'])) {
    foreach ($csvAssociatedRecords['manyToMany'] as $tabName => $assocData) {
        /*
        add to embedded fields
         */
        $embFields[] = $assocData['class_name'] . '.' . $assocData['foreign_key'];
        $panels[$tabName] = $csvAssociatedRecords['manyToMany'][$tabName];
    }
}
?>

<?php if (!empty($panels)) : ?>
<div class="row associated_records">
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
            $embField = $assocData['class_name'] . '.' . $assocData['foreign_key'];
            if (in_array($embField, $embFields)) : ?>
            <div class="row">
                <div class="typeahead-container col-md-4 col-md-offset-8">
                <?php
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
                ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($assocData['records'])) : ?>
                <div class=" table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                            <?php foreach ($assocData['fields'] as $assocField) : ?>
                                <th><?= $this->Paginator->sort($assocField); ?></th>
                            <?php endforeach; ?>
                                <th class="actions"><?= __('Actions'); ?></th>
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
                            <td class="actions">
                            <?php
                                $event = new Event('View.Associated.Menu.Actions', $this, [
                                    'request' => $this->request,
                                    'options' => [
                                        'entity' => $entity,
                                        'assoc_entity' => $record,
                                        'assoc_name' => $assocData['assoc_name']
                                    ]
                                ]);
                                $this->eventManager()->dispatch($event);
                                if (!empty($event->result)) {
                                    echo $event->result;
                                }
                            ?>
                            </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else : ?>
                <div class="well">
                    <?= __('No records found.'); ?>
                </div>
            <?php endif; ?>
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
if (!empty($embFields)) :
    foreach ($embFields as $embField) :
        $embFieldName = substr($embField, strrpos($embField, '.') + 1);
        list($embPlugin, $embController) = pluginSplit(
            substr($embField, 0, strrpos($embField, '.'))
        );
    ?>
    <!-- Modal -->
    <div id="<?= $embFieldName ?>_modal" class="modal fade" tabindex="-1" role="dialog">
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
                        'plugin' => $embPlugin,
                        'controller' => $embController,
                        'action' => 'add'
                    ],
                    [
                        'query' => [
                            'embedded' => $this->request->controller,
                            'foreign_key' => $embFieldName
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