<?php
use Cake\Utility\Inflector;
use Cake\Event\Event;

$defaultOptions = [
    'add' => [
        'display' => false,
        'url' => null,
        'title' => __d('CsvMigrations', 'Add new'),
        'ccsClass' => 'btn btn-default glyphicon glyphicon-plus',
    ],
    'back' => [
        'display' => false,
        'url' => null,
        'title' => __d('CsvMigrations', 'Back'),
        'ccsClass' => 'btn btn-default glyphicon glyphicon-arrow-left',
    ],
];
if (empty($options)) {
    $options = $defaultOptions;
} else {
    $options = array_replace_recursive($defaultOptions, $options);
}
?>
<div class="row">
    <div class="col-xs-6">
        <h3><strong><?= $title ?></strong></h3>
    </div>
    <div class="col-xs-6">
        <div class="h3 text-right">
        <?php
            $event = new Event('View.View.Menu.Top.Row', $this, [
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