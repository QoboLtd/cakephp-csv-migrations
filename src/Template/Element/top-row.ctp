<?php
use Cake\Event\Event;
use Cake\Utility\Inflector;

$defaultOptions = [
    'add' => [
        'url' => null,
        'title' => __d('CsvMigrations', 'Add new'),
        'class' => 'btn btn-primary',
        'icon' => 'plus',
    ],
    'back' => [
        'url' => null,
        'title' => __d('CsvMigrations', 'Back'),
        'class' => 'btn btn-default',
        'icon' => 'arrow-left',
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
