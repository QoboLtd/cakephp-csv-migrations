<section class="content-header">
    <h1>
        <?= __d('CsvMigrations', 'Database Lists') ?>
        <div class="pull-right">
            <div class="btn-group btn-group-sm" role="group">
                <?= $this->element('CsvMigrations.Menu/dblists_index_top', ['user' => $user]) ?>
            </div>
        </div>
    </h1>
</section>
<section class="content">
    <div class="box">
        <div class="box-body table-responsive">
            <table class="table table-condensed table-vertical-align">
                <thead>
                    <tr>
                        <th><?= $this->Paginator->sort('name'); ?></th>
                        <th><?= $this->Paginator->sort('created'); ?></th>
                        <th><?= $this->Paginator->sort('modified'); ?></th>
                        <th class="actions"><?= __d('CsvMigrations', 'Actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dblists as $dblist) : ?>
                    <tr>
                        <td><?= h($dblist->name) ?></td>
                        <td><?= h($dblist->created) ?></td>
                        <td><?= h($dblist->modified) ?></td>
                        <td class="actions">
                            <?= $this->element('CsvMigrations.Menu/dblists_index_actions', [
                                'entity' => $dblist
                            ]) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="box-footer">
            <div class="paginator">
                <ul class="pagination pagination-sm no-margin pull-right">
                    <?= $this->Paginator->prev('&laquo;', ['escape' => false]) ?>
                    <?= $this->Paginator->numbers() ?>
                    <?= $this->Paginator->next('&raquo;', ['escape' => false]) ?>
                </ul>
            </div>
        </div>
    </div>
</section>