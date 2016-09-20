<?php
echo $this->element(
    'top-row',
    ['title' => __d('CsvMigrations', 'Database Lists')]
);
?>
<table class="table table-striped">
    <thead>
        <tr>
            <th><?= $this->Paginator->sort('name'); ?></th>
            <th><?= $this->Paginator->sort('created'); ?></th>
            <th><?= $this->Paginator->sort('modified'); ?></th>
            <th class="actions"><?= __d('CsvMigrations', 'Actions'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($dblists as $dblist): ?>
        <tr>
            <td><?= h($dblist->name) ?></td>
            <td><?= h($dblist->created) ?></td>
            <td><?= h($dblist->modified) ?></td>
            <td class="actions">
                <?= $this->Html->link('', ['controller' => 'dblist-items', 'action' => 'index', $dblist->id], ['title' => __d('CsvMigrations', 'View list items of {0}', $dblist->name), 'class' => 'btn btn-default glyphicon glyphicon-list-alt']) ?>
                <?= $this->Html->link('', ['action' => 'edit', $dblist->id], ['title' => __d('CsvMigrations', 'Edit'), 'class' => 'btn btn-default glyphicon glyphicon-pencil']) ?>
                <?= $this->Form->postLink('', ['action' => 'delete', $dblist->id], ['confirm' => __d('CsvMigrations', 'Are you sure you want to delete # {0}?', $dblist->name), 'title' => __('Delete'), 'class' => 'btn btn-default glyphicon glyphicon-trash']) ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<div class="paginator">
    <ul class="pagination">
        <?= $this->Paginator->prev('< ' . __d('CsvMigrations', 'previous')) ?>
        <?= $this->Paginator->numbers(['before' => '', 'after' => '']) ?>
        <?= $this->Paginator->next(__d('CsvMigrations', 'next') . ' >') ?>
    </ul>
    <p><?= $this->Paginator->counter() ?></p>
</div>