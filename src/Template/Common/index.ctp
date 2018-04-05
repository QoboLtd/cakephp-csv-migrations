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
 * @deprecated    29.1.4
 */
trigger_error(
    '"CsvMigrations.Common/index" template is deprecated. To continue using it copy the file' .
    ' to your application\'s template directory and point your controller action to it' .
    ' (https://book.cakephp.org/3.0/en/controllers.html#rendering-a-specific-template)',
    E_USER_DEPRECATED
);

$options = [
    'fields' => $fields
];
echo $this->element('CsvMigrations.View/index', [
    'options' => $options
]);
