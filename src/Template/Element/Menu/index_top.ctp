<div class="btn-group btn-group-sm" role="group">
    <?= $this->Form->button('<i class="fa fa-bars"></i> Batch', [
        'id' => 'batch-button',
        'type' => 'button',
        'class' => 'btn btn-default dropdown-toggle',
        'data-toggle' => 'dropdown',
        'aria-haspopup' => 'true',
        'aria-expanded' => 'false',
        'disabled' => true
    ]) ?>
    <ul class="dropdown-menu">
        <li><?= $this->Html->link(
            '<i class="fa fa-pencil"></i> ' . __('Edit'),
            ['plugin' => $this->plugin, 'controller' => $this->name, 'action' => 'batch'],
            [
                'id' => 'batch-edit-button',
                'escape' => false
            ]
        ) ?></li>
        <li><?= $this->Html->link(
            '<i class="fa fa-trash"></i> ' . __('Delete'),
            ['plugin' => $this->plugin, 'controller' => $this->name, 'action' => 'batch'],
            [
                'id' => 'batch-delete-button',
                'escape' => false
            ]
        ) ?></li>
    </ul>
    <?= $this->Html->link(
            '<i class="fa fa-upload"></i> ' . __('Import'),
            ['plugin' => $this->plugin, 'controller' => $this->name, 'action' => 'import'],
            ['escape' => false, 'title' => __('Import Data'), 'class' => 'btn btn-default']
        ) ?>
    <?= $this->Html->link(
        '<i class="fa fa-plus"></i> ' . __('Add'),
        ['plugin' => $this->plugin, 'controller' => $this->name, 'action' => 'add'],
        ['escape' => false, 'title' => __('Add'), 'class' => 'btn btn-default']
    ) ?>
</div>