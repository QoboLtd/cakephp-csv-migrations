<?php
use CsvMigrations\FieldHandlers\FieldHandlerFactory;

$factory = new FieldHandlerFactory();

list($plugin, $controller) = pluginSplit($options['targetClass']);
?>
<div class="btn-group btn-group-xs" role="group">
<?php
$url = ['prefix' => false, 'plugin' => $plugin, 'controller' => $controller, 'action' => 'view', $entity->id];
echo $this->Html->link('<i class="fa fa-eye"></i>', $url, [
    'title' => __('View'), 'class' => 'btn btn-default btn-sm', 'escape' => false
]);

$url = ['prefix' => false, 'plugin' => $plugin, 'controller' => $controller, 'action' => 'edit', $entity->id];
echo $this->Html->link('<i class="fa fa-pencil"></i>', $url, [
    'title' => __('Edit'), 'class' => 'btn btn-default btn-sm', 'escape' => false
]);

$url = ['prefix' => false, 'plugin' => $plugin, 'controller' => $controller, 'action' => 'delete', $entity->id];
echo $this->Form->postLink('<i class="fa fa-trash"></i>', $url, [
    'confirm' => __(
        'Are you sure you want to delete {0}?',
        $factory->renderValue(
            $options['class_name'],
            $options['display_field'],
            $entity->{$options['display_field']},
            ['renderAs' => 'plain']
        )
    ),
    'title' => __('Delete'),
    'class' => 'btn btn-default btn-sm',
    'escape' => false
]);

if (isset($options['associationType']) && in_array($options['associationType'], ['manyToMany'])) {
    $url = [
        'prefix' => false,
        'plugin' => $this->request->plugin,
        'controller' => $this->request->controller,
        'action' => 'unlink',
        $options['id'],
        $options['associationName'],
        $entity->id
    ];
    echo $this->Form->postLink('<i class="fa fa-chain-broken"></i>', $url, [
        'title' => __('Unlink'), 'class' => 'btn btn-default btn-sm', 'escape' => false
    ]);
}
?>
</div>