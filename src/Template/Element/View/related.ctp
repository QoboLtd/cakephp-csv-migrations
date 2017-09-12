<?php
use Cake\Core\Configure;
use Cake\Utility\Inflector;
?>
<?php if (!empty($tab['url'])) : ?>
<?php
    if (in_array($tab['associationType'], ['manyToMany'])) {
        if (isset($tab['permission_allow_link']) && true === $tab['permission_allow_link']) {
            echo $this->element('CsvMigrations.View/embedded_lookup', ['data' => [
                'tab' => $tab,
            ]]);
        }
    }
?>
<div class="">
    <table class="table table-hover table-condensed table-vertical-align table-datatable" id="table-<?= $tab['containerId'];?>">
        <thead>
            <tr>
            <?php foreach ($tab['fields'] as $k => $field) :?>
                <th><?= Inflector::humanize($field);?></th>
            <?php endforeach; ?>
                <th><?= __('Actions');?></th>
            </tr>
        </thead>
    </table>
</div>
<?php
    // @codingStandardsIgnoreStart
    echo $this->Html->scriptBlock(
        '$("#table-' . $tab['containerId'] . '").dataTable({
            searching: false,
            processing:true,
            serverSide:true,
            paging:true,
            ajax: {
                type: "GET",
                url: "' . $tab['url'] . '",
                headers: {
                    "Authorization": "Bearer " + "' . Configure::read('CsvMigrations.api.token') . '"
                },
                data: function (d) {

                    return $.extend( {}, d, ' . json_encode(array_merge($tab,
                    [
                        'format' => 'datatables',
                        'id' => $this->request->params['pass'][0],
                        'controller' => $this->request->params['controller'],
                        'menus' => true,
                    ])) . ');
                }
            },
        });',
        ['block' => 'scriptBottom']
    );
    // @codingStandardsIgnoreEnd
?>
<?php endif; ?>

