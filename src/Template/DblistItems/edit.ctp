<?php
/**
 * Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
?>
<section class="content-header">
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <h4><?= __d('CsvMigrations', 'Edit Database List Item') ?></h4>
        </div>
    </div>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-6">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= __d('CsvMigrations', 'Details') ?></h3>
                </div>
                <div class="box-body">
                <?= $this->Form->create($dblistItem); ?>
                    <div class="row">
                        <div class="col-xs-6">
                            <?= $this->Form->input('parent_id', ['options' => $tree, 'escape' => false, 'empty' => true]); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-6">
                            <?= $this->Form->input('name'); ?>
                        </div>
                        <div class="col-xs-6">
                            <?= $this->Form->input('value'); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-6">
                            <?= $this->Form->input('active'); ?>
                        </div>
                    </div>
                <?= $this->Form->hidden('dblist_id', ['value' => $list['id']]); ?>
                <?= $this->Form->button(__d('CsvMigrations', "Submit"), ['class' => 'btn btn-primary']); ?>
                <?= $this->Form->end() ?>
                </div>
            </div>
        </div>
    </div>
</section>
