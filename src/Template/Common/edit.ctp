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
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;

$config = (new ModuleConfig(ConfigType::MODULE(), $this->name))->parse();

$title = Inflector::singularize(isset($config->table->alias) ?
    $config->table->alias : Inflector::humanize(Inflector::underscore($this->name))
);

$options = [
    'entity' => $entity,
    'fields' => $fields,
    'title' => __('Edit {0}', $title),
    'handlerOptions' => ['entity' => $entity]
];
echo $this->element('CsvMigrations.View/post', ['options' => $options]);
