<?php
use \Cake\Utility\Inflector;

if (!empty($csvAssociatedRecords['oneToMany'])) : ?>
<div class="row">
    <div class="col-xs-12">
        <h3><?= __('Associated Records'); ?></h3>
        <ul id="relatedTabs" class="nav nav-tabs" role="tablist">
<?php
    $active = 'active';
    foreach ($csvAssociatedRecords['oneToMany'] as $tabName => $assocData) :
?>
            <li role="presentation" class="<?= $active; ?>">
                <a href="#<?= $tabName; ?>" aria-controls="<?= $tabName; ?>" role="tab" data-toggle="tab">
                    <?= Inflector::humanize($assocData['table_name']); ?>
                </a>
            </li>
<?php
    $active = '';
    endforeach;
?>
        </ul>
        <div class="tab-content">
<?php
    $active = 'active';
    foreach ($csvAssociatedRecords['oneToMany'] as $assocName => $assocData) {
    ?>
            <div role="tabpanel" class="tab-pane <?= $active; ?>" id="<?= $assocName; ?>">
                <table class="table table-hover">
                    <thead>
                        <tr>
                        <?php foreach ($assocData['fields'] as $assocField) : ?>
                            <th><?= $this->Paginator->sort($assocField); ?></th>
                        <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($assocData['records'] as $record) : ?>
                        <tr>
                        <?php foreach ($assocData['fields'] as $assocField) : ?>
                            <?php if ('' !== trim($record->$assocField)) : ?>
                            <td>
                            <?php
                                if (is_bool($record->$assocField)) {
                                    echo $record->$assocField ? __('Yes') : __('No');
                                } else {
                                    if ('id' === $assocField) {
                                        echo $this->Html->link(
                                            h($record->$assocField), [
                                                'controller' => $assocData['table_name'],
                                                'action' => 'view',
                                                $record->$assocField
                                            ]
                                        );
                                    } else {
                                        echo h($record->$assocField);
                                    }
                                }
                            ?>
                            </td>
                            <?php else : ?>
                            <td>&nbsp;</td>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php $active = '';
    }
?>
        </div>
    </div>
</div>
<?php endif; ?>