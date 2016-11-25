<?php
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Utility\Inflector;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;

$fhf = new FieldHandlerFactory($this);

$content = $data['content'];
/*
Loading Linking Element (typeahead, link, plus components)
only for many-to-many relationship, as for others
we don't do the linkage - they would have hidden ID by default
*/
$emField = $content['class_name'] . '.' . $content['foreign_key'];
$emFieldName = substr($emField, strrpos($emField, '.') + 1);

$emDataTarget = Inflector::underscore(str_replace('.', '_', $emField));
$emModal = sprintf("%s_modal", $emDataTarget);

list($emPlugin, $emController) = pluginSplit(substr($emField, 0, strrpos($emField, '.')));

$tableName = $this->request->controller;
if (!is_null($this->request->plugin)) {
    $tableName = $this->request->plugin . '.' . $tableName;
}

$formOptions = [
    'url' => [
        'plugin' => $this->request->plugin,
        'controller' => $this->request->controller,
        'action' => 'edit',
        $this->request->pass[0]
    ]
];

$handlerOptions = [];
/*
set associated table name to be used on input field's name
 */
$handlerOptions['associated_table_name'] = $content['table_name'];
/*
set embedded modal flag
 */
$handlerOptions['embModal'] = true;
$handlerOptions['emDataTarget'] = $emDataTarget;
$handlerOptions['emAssociationType'] = $data['tab']['associationType'];
/*
set field type to 'has_many' and default parameters
 */
$handlerOptions['fieldDefinitions']['type'] = 'has_many(' . $content['class_name'] . ')';
$handlerOptions['fieldDefinitions']['required'] = true;
$handlerOptions['fieldDefinitions']['non-searchable'] = true;
$handlerOptions['fieldDefinitions']['unique'] = false;

?>
<?php if (in_array($data['tab']['associationType'], ['manyToMany'])) : ?>
<div class="row">
    <div class="typeahead-container col-md-4 col-md-offset-8">
    <?php
        echo $this->Form->create(null, $formOptions);
           /*
        display typeahead field for associated module(s)
         */
        echo $fhf->renderInput(
            $tableName,
            $content['foreign_key'],
            null,
            $handlerOptions
        );

        /*
        set existing related records as hidden fields
         */
        foreach ($content['records'] as $record) {
            echo $this->Form->hidden($content['table_name'] . '._ids[]', [
                'value' => $record[$content['primary_key']]
            ]);
        }

        echo $this->Form->end();
    ?>
    </div>
</div>
<div id="<?= $emModal?>" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div> <!-- modal-header -->

            <div class="modal-body">
            <?php
                echo $this->requestAction([
                    'plugin' => $emPlugin,
                    'controller' => $emController,
                    'action' => 'add'
                ], [
                    'query' => [
                        'embedded' => $this->request->controller,
                        'foreign_key' => $emFieldName,
                        'modal_id'    => $emModal,
                    ]
                ]);
            ?>
            </div>
        </div> <!-- modal-content -->
    </div> <!-- modal-dialog -->
</div> <!-- modal window -->
<?php endif; ?>
<?php if ($content['length']) : ?>
<div class="table-responsive">
    <table class="table table-hover <?= $data['tab']['containerId']?>">
        <thead>
            <tr>
                <?php foreach ($content['fields'] as $field) :?>
                    <th><?= Inflector::humanize($field) ?></th>
                <?php endforeach; ?>

                <th class="actions"><?= __('Actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($content['records'] as $k => $record) :?>
            <tr>
                <?php foreach ($content['fields'] as $field) : ?>
                <td>
                    <?php
                        $renderOptions = ['entity' => $record, 'renderAs' => 'plain'];

                        $value = $fhf->renderValue(
                            $content['class_name'],
                            $field,
                            $record[$field],
                            $renderOptions
                        );

                        echo empty($value) ? '&nbsp;' : $value;
                    ?>
                </td>
                <?php endforeach; ?>
                <td class="actions">
                <?php
                    // get action buttons if any allowed
                    $event = new Event('View.Associated.Menu.Actions', $this, [
                        'request' => $this->request,
                        'options' => [
                            'entity' => $record,
                            'associated' => [
                                'entity' => $record,
                                'name' => $content['assoc_name'],
                                'className' => $content['class_name'],
                                'displayField' => $content['display_field'],
                                'type' => $data['tab']['associationType'],
                            ]
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
    </table> <!-- .table .table-hover -->
</div> <!-- table-responsive -->
<?php else : ?>
    <div class="well">
        <div class="row text-center"><?= __('No records found') ?></div>
    </div>
<?php endif; /* if content.length */ ?>
