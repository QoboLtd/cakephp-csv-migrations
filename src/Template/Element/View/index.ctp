<?php
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
    $options['title'] = __(
        'Add {0}',
        Inflector::singularize(Inflector::humanize(Inflector::underscore($this->request->controller)))
    );
}
?>

<div class="row">
    <div class="col-xs-12">
        <p class="text-right">
            <?php echo $this->Html->link(
                $options['title'],
                ['plugin' => $this->request->plugin, 'controller' => $this->request->controller, 'action' => 'add'],
                ['class' => 'btn btn-primary']
            ); ?>
        </p>
    </div>
</div>

<div class="row">
    <div class="col-xs-12">
        <table class="table table-hover">
            <thead>
                <tr>
                    <?php
                        foreach ($options['fields'] as $field) {
                            echo '<th>' . $this->Paginator->sort($field[0]) . '</th>';
                        }
                        echo '<th class="actions">' . __('Actions') . '</th>';
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($options['entities'] as $entity): ?>
                <tr>
                    <?php foreach ($fields as $field): ?>
                        <td>
                        <?php
                            $tableName = $this->name;
                            if (!is_null($this->plugin)) {
                                $tableName = $this->plugin . '.' . $tableName;
                            }
                            echo $fhf->renderValue($tableName, $field[0], $entity->$field[0]);
                        ?>
                        </td>
                    <?php endforeach; ?>
                    <td class="actions">
                        <?= $this->Html->link('', ['action' => 'view', $entity->id], ['title' => __('View'), 'class' => 'btn btn-default glyphicon glyphicon-eye-open']) ?>
                        <?= $this->Html->link('', ['action' => 'edit', $entity->id], ['title' => __('Edit'), 'class' => 'btn btn-default glyphicon glyphicon-pencil']) ?>
                        <?= $this->Form->postLink('', ['action' => 'delete', $entity->id], ['confirm' => __('Are you sure you want to delete # {0}?', $entity->id), 'title' => __('Delete'), 'class' => 'btn btn-default glyphicon glyphicon-trash']) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
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