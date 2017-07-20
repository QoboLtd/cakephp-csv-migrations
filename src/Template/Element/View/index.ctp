<?php
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Utility\Inflector;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;

$fhf = new FieldHandlerFactory($this);

// Setup Index View js logic
echo $this->Html->css('AdminLTE./plugins/datatables/dataTables.bootstrap', ['block' => 'css']);
echo $this->Html->script(
    [
        'AdminLTE./plugins/datatables/jquery.dataTables.min',
        'AdminLTE./plugins/datatables/dataTables.bootstrap.min',
        'CsvMigrations.view-index'
    ],
    [
        'block' => 'scriptBotton'
    ]
);
echo $this->Html->scriptBlock(
    'view_index.init({
        table_id: \'.table-datatable\',
        api_url: \'' . $this->Url->build([
            'prefix' => 'api',
            'plugin' => $this->request->plugin,
            'controller' => $this->request->controller,
            'action' => $this->request->action
        ]) . '\',
        api_ext: \'json\',
        api_token: ' . json_encode(Configure::read('CsvMigrations.api.token')) . ',
        menus: true,
        format: \'datatables\',
        state_duration: ' . (int)(Configure::read('Session.timeout') * 60) . '
    });',
    ['block' => 'scriptBotton']
);

$defaultOptions = [
    'title' => null,
    'fields' => [],
];
if (empty($options)) {
    $options = [];
}
$options = array_merge($defaultOptions, $options);

// Get title from controller
if (empty($options['title'])) {
    $options['title'] = Inflector::humanize(Inflector::underscore($moduleAlias));
}
?>
<section class="content-header">
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <h4><?= $options['title'] ?></h4>
        </div>
        <div class="col-xs-12 col-md-6">
            <div class="pull-right">
                <div class="btn-group btn-group-sm" role="group">
                    <?= $this->element('CsvMigrations.Menu/index_top', ['user' => $user]) ?>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="content">
    <div class="box box-solid">
        <div class="box-body">
            <table class="table table-hover table-condensed table-vertical-align table-datatable" width="100%">
                <thead>
                    <tr>
                    <?php foreach ($options['fields'] as $field) : ?>
                    <?php

                    $tableName = $field[0]['model'];
                    if (!is_null($field[0]['plugin'])) {
                        $tableName = $field[0]['plugin'] . '.' . $tableName;
                    }
                    $renderOptions = [];

                    $label = $fhf->renderName(
                        $tableName,
                        $field[0]['name'],
                        $renderOptions
                    );

                    ?>
                        <th><?= $label ?></th>
                    <?php endforeach; ?>
                    <th><?= __('Actions'); ?></th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</section>
