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

use Cake\Core\Configure;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\Event;
use Cake\Network\Exception\ForbiddenException;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use CsvMigrations\CsvMigrationsUtils;
use CsvMigrations\Event\EventName;
use CsvMigrations\FieldHandlers\FieldHandlerFactory;

$factory = new FieldHandlerFactory($this);
echo $this->Html->script(['Translations.translation'], ['block' => 'scriptBottom']);

$defaultOptions = [
    'title' => null,
    'entity' => null,
    'fields' => [],
];
if (empty($options)) {
    $options = [];
}
$options = array_merge($defaultOptions, $options);

// get table name
$tableName = $this->name;
if (!empty($this->plugin)) {
    $tableName = $this->plugin . '.' . $tableName;
}

// get table instance
$table = TableRegistry::get($tableName);

// get display field
$displayField = $table->getDisplayField();

$handlerOptions = ['entity' => $options['entity']];

// append to title
$options['title'] .= ' &raquo; ';
$displayValue = $options['entity']->get($displayField);
$options['title'] .= $factory->renderValue($tableName, $displayField, $displayValue, $handlerOptions);

if (!$this->request->query('embedded')) : ?>
<section class="content-header">
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <h4><?= $options['title'] ?></h4>
        </div>
        <div class="col-xs-12 col-md-6">
            <div class="pull-right">
            <div class="btn-group btn-group-sm" role="group">
                <?= $this->element('CsvMigrations.Menu/view_top', [
                    'options' => $options,
                    'user' => $user,
                    'displayField' => $displayField
                ]); ?>
            </div>
            </div>
        </div>
    </div>
</section>
<section class="content">
<?php endif; ?>
        <?php
        if (!empty($options['fields'])) :
            $embeddedFields = [];
            $embeddedDirty = false;
            foreach ($options['fields'] as $panelName => $panelFields) :
                if (!empty($this->request->query['embedded'])) {
                    $panelName = Inflector::singularize(
                        Inflector::humanize($this->request->controller)
                    ) . ' : ' . $panelName;
                }

        ?>
        <div class="box box-solid">
            <div class="box-header with-border">
                <h3 class="box-title"><?= $panelName; ?></h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
            <?php foreach ($panelFields as $subFields) : ?>
                <div class="row">
                <?php foreach ($subFields as $field) : ?>
                    <?php if ('' !== trim($field['name']) && !$embeddedDirty) : ?>
                        <?php
                        // embedded field
                        if ('EMBEDDED' === $field['name']) {
                            $embeddedDirty = true;
                        }
                        ?>
                        <?php if (!$embeddedDirty) : // non-embedded field ?>
                            <?= $this->element('CsvMigrations.Field/value', [
                                'factory' => $factory, 'field' => $field, 'options' => $options, 'user' => $user
                            ]) ?>
                        <?php endif; ?>
                    <?php elseif ('' !== trim($field['name'])) : ?>
                            <?php
                            $embeddedFields[] = $field['name'];
                            $embeddedDirty = false;
                            ?>
                    <?php else : ?>
                            <div class="col-xs-4 col-md-2 text-right">&nbsp;</div>
                            <div class="col-xs-8 col-md-4">&nbsp;</div>
                    <?php endif; ?>
                    <div class="clearfix visible-xs visible-sm"></div>
                <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
            </div>
        </div>
            <?php
            if (empty($embeddedFields)) {
                continue;
            }

            // Fetch embedded module(s) using CakePHP's requestAction() method
            foreach ($embeddedFields as $embeddedField) {
                $embeddedFieldName = substr($embeddedField, strrpos($embeddedField, '.') + 1);
                list($embeddedPlugin, $embeddedController) = pluginSplit(
                    substr($embeddedField, 0, strrpos($embeddedField, '.'))
                );

                $embeddedAssocName = CsvMigrationsUtils::createAssociationName(
                    $embeddedPlugin . $embeddedController,
                    $embeddedFieldName
                );

                // @note this only works for belongsTo for now.
                $embeddedAssocName = Inflector::underscore(Inflector::singularize($embeddedAssocName));

                if (!empty($options['entity']->$embeddedFieldName)) {
                    try {
                        echo $this->requestAction(
                            [
                                'plugin' => $embeddedPlugin,
                                'controller' => $embeddedController,
                                'action' => $this->request->action
                            ],
                            [
                                'query' => ['embedded' => $this->request->controller . '.' . $embeddedAssocName],
                                'pass' => [$options['entity']->$embeddedFieldName]
                            ]
                        );
                    } catch (RecordNotFoundException $e) {
                        // just don't display anything if embedded record was not found
                    } catch (ForbiddenException $e) {
                        // just don't display anything if current user has no access to embedded record
                    }
                }
            }
            $embeddedFields = [];
            endforeach;
        endif;
?>
<?php if (empty($this->request->query['embedded'])) : ?>
<?php
// loading common setup for typeahead/panel/etc libs for tabs
echo $this->element('CsvMigrations.common_js_libs');
?>
<hr />
<div class="row associated-records">
    <div class="col-xs-12">
        <?= $this->element('CsvMigrations.View/associated', [
            'user' => $user, 'options' => $options, 'table' => $table
        ]) ?>
    </div>
</div> <!-- .associated-records -->
<?php endif;?>
<?php if (empty($this->request->query['embedded'])) : ?>
</section>
<?php endif;?>
<?php
// Event dispatcher for bottom section
$event = new Event(
    (string)EventName::VIEW_BODY_BOTTOM(),
    $this,
    ['request' => $this->request, 'options' => $options]
);
$this->eventManager()->dispatch($event);

echo $event->result;
?>
