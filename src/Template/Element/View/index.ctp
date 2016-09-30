<?php
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Utility\Inflector;
?>

<?php
// Setup Index View js logic
echo $this->Html->css('CsvMigrations.datatables.min', ['block' => 'cssBottom']);
echo $this->Html->script('CsvMigrations.datatables.min', ['block' => 'scriptBottom']);
echo $this->Html->script('CsvMigrations.view-index', ['block' => 'scriptBottom']);
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
        fields: '. json_encode($options['fields']) . ',
        menus: ' . json_encode($menus) . ',
        format: \'pretty\',
        menu_property: \'' . Configure::read('CsvMigrations.api.menus_property') . '\'
    });',
    ['block' => 'scriptBottom']
);
?>

<?php
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

<div class="row">
    <div class="col-xs-6">
        <h3><strong><?= $options['title'] ?></strong></h3>
    </div>
    <div class="col-xs-6">
        <div class="h3 text-right">
            <?php
                $event = new Event('View.Index.Menu.Top', $this, [
                    'request' => $this->request,
                    'options' => $options
                ]);
                $this->eventManager()->dispatch($event);
                if (!empty($event->result)) {
                    echo $event->result;
                }
            ?>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12">
        <div class="table-responsive">
            <table class="table table-hover table-datatable">
                <thead>
                    <tr>
                    <?php foreach ($options['fields'] as $field) : ?>
                        <th><?= Inflector::humanize($field[0]['name']); ?></th>
                    <?php endforeach; ?>
                    <?php foreach ($menus as $menu) : ?>
                        <th><?= Inflector::humanize($menu); ?></th>
                    <?php endforeach; ?>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>