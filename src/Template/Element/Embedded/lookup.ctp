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
use CsvMigrations\FieldHandlers\FieldHandlerFactory;

$factory = new FieldHandlerFactory($this);

// Loading Linking Element (typeahead, link, plus components) only for many-to-many relationship, as for others
// we don't do the linkage - they would have hidden ID by default.
$dataTarget = Inflector::underscore($association->className() . '_' . $association->getForeignKey());
$modalId = $dataTarget . '_modal';

list($plugin, $controller) = pluginSplit($association->className());

$modalBody = $this->requestAction(
    ['plugin' => $plugin, 'controller' => $controller, 'action' => 'add'],
    [
        'query' => [
            'embedded' => $association->getName(),
            'foreign_key' => $association->getForeignKey(),
            'related_model' => Inflector::delimit($this->request->getParam('controller'), '-'),
            'related_id' => $this->request->getParam('pass.0'),
        ]
    ]
);
?>
<?php if (isset($modalBody)) : ?>
    <div class="row">
        <div class="typeahead-container col-xs-12">
        <?php
        $formOptions = [
            'url' => [
                'plugin' => $this->plugin,
                'controller' => $this->name,
                'action' => 'link',
                $this->request->getParam('pass.0'),
                $association->getName()
            ],
            'id' => 'link_related'
        ];

        echo $this->Form->create(null, $formOptions);
        // display typeahead field for associated module(s)

        $handlerOptions = [];
        // set associated table name to be used on input field's name
        $handlerOptions['association'] = $association;
        $handlerOptions['emDataTarget'] = $dataTarget;
        // set field type to 'has_many' and default parameters
        $handlerOptions['fieldDefinitions']['type'] = 'hasMany(' . $association->className() . ')';
        $handlerOptions['fieldDefinitions']['required'] = true;
        $handlerOptions['fieldDefinitions']['non-searchable'] = true;
        $handlerOptions['fieldDefinitions']['unique'] = false;

        $tableName = $this->name;
        if ($this->plugin) {
            $tableName = $this->plugin . '.' . $tableName;
        }

        echo $factory->renderInput($tableName, $association->getForeignKey(), null, $handlerOptions);

        echo $this->Form->end();
        ?>
        </div>
    </div>
    <div id="<?= $modalId ?>" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-body">
                    <?= $modalBody ?>
                </div>
            </div> <!-- modal-content -->
        </div> <!-- modal-dialog -->
    </div> <!-- modal window -->
<?php endif; ?>
