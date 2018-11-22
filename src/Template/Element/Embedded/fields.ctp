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

use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Exception\ForbiddenException;
use Cake\Utility\Inflector;

// Fetch embedded module(s) using CakePHP's requestAction() method
foreach ($fields as $field) {
    $fieldName = substr($field, strrpos($field, '.') + 1);

    if (!$options['entity']->get($fieldName)) {
        continue;
    }

    $associationName = '';
    foreach ($table->associations() as $association) {
        if ('manyToOne' !== $association->type()) {
            continue;
        }
        if ($association->getForeignKey() !== $fieldName) {
            continue;
        }

        $associationName = Inflector::underscore(Inflector::singularize($association->getName()));
    }

    if (empty($associationName)) {
        continue;
    }

    $tableName = substr($field, 0, strrpos($field, '.'));
    list($plugin, $controller) = pluginSplit($tableName);
    try {
        echo $this->requestAction(
            ['plugin' => $plugin, 'controller' => $controller, 'action' => $this->request->getParam('action')],
            [
                'query' => ['embedded' => $this->name . '.' . $associationName],
                'pass' => [$options['entity']->get($fieldName)]
            ]
        );
    } catch (RecordNotFoundException $e) {
        // just don't display anything if embedded record was not found
    } catch (ForbiddenException $e) {
        // just don't display anything if current user has no access to embedded record
    }
}
