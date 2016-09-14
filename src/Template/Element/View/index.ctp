<?php
use Cake\Event\Event;
use Cake\Utility\Inflector;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;

$fhf = new FieldHandlerFactory();

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
                        <?php
                            foreach ($options['fields'] as $field) {
                                echo '<th>' . Inflector::humanize($field[0]['name']) . '</th>';
                            }
                            echo '<th class="actions">' . __('Actions') . '</th>';
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($options['entities'] as $entity): ?>
                    <tr>
                        <?php foreach ($options['fields'] as $field): ?>
                            <td>
                            <?php
                                $tableName = $this->name;
                                if (!is_null($this->plugin)) {
                                    $tableName = $this->plugin . '.' . $tableName;
                                }
                                $renderOptions = [
                                    'entity' => $entity,
                                    'imageSize' => 'tiny'
                                ];
                                echo $fhf->renderValue(
                                    $tableName,
                                    $field[0]['name'],
                                    $entity->$field[0]['name'],
                                    $renderOptions
                                );
                            ?>
                            </td>
                        <?php endforeach; ?>
                        <td class="actions">
                            <?php
                                $event = new Event('View.Index.Menu.Actions', $this, [
                                    'request' => $this->request,
                                    'options' => $entity,
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
    </div>
</div>

<div class="paginator">
    <ul class="pagination">
        <?= $this->Paginator->prev('< ' . __('previous')) ?>
        <?= $this->Paginator->numbers(['before' => '', 'after' => '']) ?>
        <?= $this->Paginator->next(__('next') . ' >') ?>
    </ul>
    <p><?= $this->Paginator->counter() ?></p>
</div>
<?php
echo $this->Html->css('CsvMigrations.datatables.min', ['block' => 'cssBottom']);
echo $this->Html->script('CsvMigrations.datatables.min', ['block' => 'scriptBottom']);
echo $this->Html->script('CsvMigrations.data-tables', ['block' => 'scriptBottom']);
?>