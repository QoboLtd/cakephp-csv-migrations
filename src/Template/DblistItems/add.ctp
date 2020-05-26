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
            <h4><?= __d('Qobo/CsvMigrations', 'Create Database List Item') ?></h4>
        </div>
    </div>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-6">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= __d('Qobo/CsvMigrations', 'Details') ?></h3>
                </div>
                <div class="box-body">
                <?= $this->Form->create($entity); ?>
                    <div class="row">
                        <div class="col-md-6">
                            <?= $this->Form->control('parent_id', ['options' => $tree, 'escape' => false, 'empty' => true]); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <?= $this->Form->control('name'); ?>
                        </div>
                        <div class="col-md-6">
                            <?= $this->Form->control('value'); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <?= $this->Form->control('active', ['checked' => 'checked']); ?>
                        </div>
                    </div>
                <?= $this->Form->button(__d('Qobo/CsvMigrations', "Submit"), ['class' => 'btn btn-primary']); ?>
                <?= $this->Form->end() ?>
                </div>
            </div>
        </div>
    </div>
</section>
