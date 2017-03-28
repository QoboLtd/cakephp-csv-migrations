<section class="content-header">
    <div class="row">
        <div class="col-xs-12 col-md-6">
        <h4>
            <?= __d('CsvMigrations', 'Database List Items') ?>
            <small><?= $list->get('name') ?></small>
        </h4>
        </div>
        <div class="col-xs-12 col-md-6">
            <div class="pull-right">
                <div class="btn-group btn-group-sm" role="group">
                    <?= $this->element('CsvMigrations.Menu/dblist_items_index_top', ['entity' => $list, 'user' => $user]) ?>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="content">
    <div class="box">
        <div class="box-body table-responsive">
            <table class="table table-condensed table-vertical-align">
                <thead>
                    <tr>
                        <th><?= __d('CsvMigrations', 'Name'); ?></th>
                        <th class="actions"><?= __d('CsvMigrations', 'Actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tree as $entity) : ?>
                    <tr class="<?= !($entity->get('active')) ? 'warning' : ''; ?>">
                        <td><?= $entity->get('spacer')?> (<?= $entity->get('value') ?>)</td>
                        <td class="actions">
                            <?= $this->element('CsvMigrations.Menu/dblist_items_index_actions', [
                                'entity' => $entity
                            ]) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
