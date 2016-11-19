<?php
use Cake\Event\Event;
use Cake\Utility\Inflector;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;

$fhf = new FieldHandlerFactory();

$panels = [];

// used for modal forms to draf "plus" icon
$embFields = [];

foreach(['oneToMany', 'manyToMany'] as $relation) {
    if (!empty($csvAssociatedRecords[$relation])) {

        foreach ($csvAssociatedRecords[$relation] as $tabName => $assocData) {
            if( $relation === 'oneToMany') {
                $embFields[] = $assocData['class_name'] . '.' . $assocData['foreign_key'];
                $panels[$tabName] = $csvAssociatedRecords[$relation][$tabName];
            }
        }
    }
}
pr($embFields);
if (!empty($panels)) : ?>
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
                        //prettifying the tabs names
                        if (!empty($csvAssociationLabels) && in_array($tabName, array_keys($csvAssociationLabels))) {
                            echo $csvAssociationLabels[$tabName];
                        } else {
                            $tableName = Inflector::humanize($assocData['table_name']);
                            $fieldName = trim(str_replace($tableName, '', Inflector::humanize(Inflector::tableize($tabName))));
                            if (!empty($fieldName)) {
                                $fieldName = '<small>(' . $fieldName . ')</small>';
                            }
                            echo $tableName . $fieldName;
                        }
                    ?>
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
                    pr($tableName);
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
                    set field type to 'has_many' and default parameters
                     */
                    $handlerOptions['fieldDefinitions']['type'] = 'has_many(' . $assocData['class_name'] . ')';
                    $handlerOptions['fieldDefinitions']['required'] = true;
                    $handlerOptions['fieldDefinitions']['non-searchable'] = true;
                    $handlerOptions['fieldDefinitions']['unique'] = false;

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
            <?php
                endif;

                // @NOTE: based on different associations,
                // we might deal with arrays and ResultSet objects

                $emptyRecords = false;

                if (is_array($assocData['records'])) {
                    $emptyRecords = empty($assocData['records']) ? true : false;
                }

                if ($assocData['records'] instanceof \Cake\ORM\ResultSet) {
                    $emptyRecords = (0 === $assocData['records']->count()) ? true : false;
                }

                if (!$emptyRecords) :
            ?>
                <div class=" table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                            <?php foreach ($assocData['fields'] as $assocField) : ?>
                                <th><?= Inflector::humanize($assocField); ?></th>
                            <?php endforeach; ?>
                                <th class="actions"><?= __('Actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($assocData['records'] as $record) : ?>
                            <tr>
                            <?php foreach ($assocData['fields'] as $assocField) : ?>
                                <td>
                                <?php
                                    $renderOptions = [
                                        'entity' => $record,
                                        'renderAs' => 'plain'
                                    ];
                                    $value = $fhf->renderValue(
                                        $assocData['class_name'],
                                        $assocField,
                                        $record->$assocField,
                                        $renderOptions
                                    );

                                    if ($assocData['display_field'] === $assocField) {
                                        list($assocPlugin, $assocModel) = pluginSplit($assocData['class_name']);
                                        // Not doing any escaping as it messes up display of
                                        // things like email fields with the mailto: link.
                                        echo $this->Html->link(
                                            $value, [
                                                'plugin' => $assocPlugin,
                                                'controller' => $assocModel,
                                                'action' => 'view',
                                                $record->$assocData['primary_key']
                                            ],
                                            ['escape' => false]
                                        );
                                    } else {
                                        echo !empty($value) ? $value : '&nbsp;';
                                    }
                                ?>
                                </td>
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
/**
 * @todo  Load when needed.
 * - When there is file input
 * - load these files only if foreign/related field exists
 */
echo $this->element('CsvMigrations.common_js_libs');
?>
<?php endif; ?>

<?php endif; ?>
