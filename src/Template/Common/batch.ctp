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

$title = __('Batch edit {0}', strtolower(Inflector::humanize(Inflector::underscore($this->name))));

$options = [
    'title' => $title,
    'entity' => $entity,
    'fields' => $fields,
    'handlerOptions' => [
        'attributes' => [
            'data-batch' => 'field',
            'disabled' => true
        ]
    ]
];
echo $this->element('CsvMigrations.View/post', ['options' => $options]);

echo $this->Html->script('CsvMigrations.view-batch', ['block' => 'scriptBottom']);

$formUrl = $this->Url->build([
    'plugin' => $this->request->plugin,
    'controller' => $this->request->controller,
    'action' => $this->request->action
]);
echo $this->Html->scriptBlock(
    '$("form[action=\'' . $formUrl . '\']").viewBatch({
        batch_ids: ' . json_encode($this->request->data('batch.ids')) . ',
        redirect_url: "' . $this->request->referer() . '",
        target_id: "*[data-batch=\'field\']",
        disable_id: "*[data-batch=\'disable\']",
        enable_id: "*[data-batch=\'enable\']",
        wrapper_id: ".field-wrapper"
    })',
    ['block' => 'scriptBottom']
);
