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

use Cake\ORM\Association;

$associations = [];
foreach ($table->associations() as $association) {
    // skip association(s) with Burzum/FileStorage, because it is rendered within the respective field handler
    if ('Burzum/FileStorage.FileStorage' === $association->className()) {
        continue;
    }

    if (!in_array($association->type(), [Association::MANY_TO_MANY, Association::ONE_TO_MANY])) {
        continue;
    }

    $associations[] = $association;
}

if (!empty($associations)) : ?>
    <?= $this->Html->scriptBlock(
        'var url = document.location.toString();
            if (matches = url.match(/(.*)(#.*)/)) {
                $(".nav-tabs a[href=\'" + matches["2"] + "\']").tab("show");
                history.pushState("", document.title, window.location.pathname + window.location.search);
            }
        ',
        ['block' => 'scriptBottom']
    ); ?>
    <div class="nav-tabs-custom">
        <?= $this->element('CsvMigrations.Associated/tabs-list', [
            'table' => $table, 'associations' => $associations
        ]); ?>
        <?= $this->element('CsvMigrations.Associated/tabs-content', [
            'table' => $table, 'associations' => $associations, 'factory' => $factory, 'entity' => $entity
        ]); ?>
    </div>
<?php endif ?>