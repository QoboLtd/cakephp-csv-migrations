<?php
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Network\Exception\ForbiddenException;
use Cake\Utility\Inflector;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;

$fhf = new FieldHandlerFactory($this);
$content = $data['tab'];

/*
Loading Linking Element (typeahead, link, plus components)
only for many-to-many relationship, as for others
we don't do the linkage - they would have hidden ID by default
*/
?>
<?php
try {
    if (!empty($content['class_name']) && !empty($content['foreign_key'])) {
        $emField = $content['class_name'] . '.' . $content['foreign_key'];
        $emFieldName = substr($emField, strrpos($emField, '.') + 1);

        $emDataTarget = Inflector::underscore(str_replace('.', '_', $emField));
        $emModal = sprintf("%s_modal", $emDataTarget);

        list($emPlugin, $emController) = pluginSplit(substr($emField, 0, strrpos($emField, '.')));

        $modalBody = $this->requestAction([
            'plugin' => $emPlugin,
            'controller' => $emController,
            'action' => 'add'
        ], [
            'query' => [
                'embedded' => $emController,
                'foreign_key' => $emFieldName,
                'modal_id' => $emModal,
                'related_model' => Inflector::delimit($this->request->controller, '-'),
                'related_id' => $this->request->pass[0],
            ]
        ]);
    }
} catch (ForbiddenException $e) {
    // just don't display anything if current user has no access to embedded module
}
?>
<?php if (isset($modalBody)) : ?>
<div class="row">
    <div class="typeahead-container col-xs-12">
    <?php
    $tableName = $this->request->controller;
    if (!is_null($this->request->plugin)) {
        $tableName = $this->request->plugin . '.' . $tableName;
    }
    $formOptions = [
        'url' => [
            'plugin' => $this->request->plugin,
            'controller' => $this->request->controller,
            'action' => 'link',
            $this->request->pass[0]
        ],
        'id' => 'link_related'
    ];
    $handlerOptions = [];
    // set associated table name to be used on input field's name
    $handlerOptions['associated_table_name'] = $content['table_name'];
    $handlerOptions['emDataTarget'] = $emDataTarget;
    $handlerOptions['emAssociationType'] = $data['tab']['associationType'];
    // set field type to 'has_many' and default parameters
    $handlerOptions['fieldDefinitions']['type'] = 'has_many(' . $content['class_name'] . ')';
    $handlerOptions['fieldDefinitions']['required'] = true;
    $handlerOptions['fieldDefinitions']['non-searchable'] = true;
    $handlerOptions['fieldDefinitions']['unique'] = false;

    echo $this->Form->create(null, $formOptions);
    // display typeahead field for associated module(s)

    echo $fhf->renderInput(
        $tableName,
        $content['foreign_key'],
        null,
        $handlerOptions
    );
    echo $this->Form->hidden('assocName', ['value' => $content['table_name']]);
    echo $this->Form->hidden('id', ['value' => $this->request->pass[0]]);
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
                <h4 class="modal-title">&nbsp;</h4>
            </div> <!-- modal-header -->

            <div class="modal-body">
                <?= $modalBody ?>
            </div>
        </div> <!-- modal-content -->
    </div> <!-- modal-dialog -->
</div> <!-- modal window -->
<?php endif; ?>
