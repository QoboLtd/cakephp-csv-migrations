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

use Cake\Utility\Inflector;

$url = ['plugin' => $this->plugin, 'controller' => $this->name, 'action' => 'index'];
$title = $this->Html->link(Inflector::humanize(Inflector::underscore($moduleAlias)), $url);

$options = [
    'entity' => $entity,
    'fields' => $fields,
    'title' => $title
];
echo $this->element('CsvMigrations.View/view', [
    'options' => $options
]);
