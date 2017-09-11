<?php
use Cake\Utility\Inflector;
use Cake\Core\Configure;
?>
<?php if (!empty($tab['url'])) : ?>
<div class="">
    <table class="table table-hover table-condensed table-vertical-align" id="table-<?= $tab['containerId'];?>">
        <thead>
            <tr>
            <?php foreach ($tab['fields'] as $k => $field) :?>
                <th><?= Inflector::humanize($field);?></th>
            <?php endforeach; ?>
            <!--
                <th><?= __('Actions');?></th>
            -->
            </tr>
        </thead>
    </table>
</div>
<?php
    echo $this->Html->scriptBlock(
        '$("#table-'.$tab['containerId'].'").dataTable({
            searching: false,
            processing:true,
            serverSide:true,
            paging:true,
            ajax: {
                type: "GET",
                url: "'.$tab['url'].'",
                headers: {
                    "Authorization": "Bearer " + "'.Configure::read('CsvMigrations.api.token').'"
                },
                data: function (d) {
                    return $.extend( {}, d, '.json_encode(
                        array_merge(
                            $tab, [
                                'format' => 'datatables',
                                'id' => $this->request->params['pass'][0],
                                'controller' => $this->request->params['controller'],
                                'menus' => false
                            ]
                        )
                    ).');
                }
            },
        });',
        ['block' => 'scriptBottom']
    );?>
<?php endif; ?>

