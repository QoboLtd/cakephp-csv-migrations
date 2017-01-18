<?php
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Utility\Inflector;

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
        format: \'datatables\'
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
    <h1><?= $options['title'] ?>
        <div class="pull-right">
            <div class="btn-group btn-group-sm" role="group">
                <?= $this->element('CsvMigrations.Menu/index_top', ['user' => $user]) ?>
            </div>
        </div>
    </h1>
</section>
<section class="content">
    <div class="box">
        <div class="box-body">
            <table class="table table-hover table-condensed table-vertical-align table-datatable" width="100%">
                <thead>
                    <tr>
                    <?php foreach ($options['fields'] as $field) : ?>
                        <th><?= Inflector::humanize($field[0]['name']); ?></th>
                    <?php endforeach; ?>
                    <th><?= __('Actions'); ?></th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</section>