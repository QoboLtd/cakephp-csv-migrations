<?php
use Cake\Core\Configure;

echo $this->Html->css('QoboAdminPanel.fileinput.min', ['block' => 'cssBottom']);
echo $this->Html->script('QoboAdminPanel.canvas-to-blob.min', ['block' => 'scriptBottom']);
echo $this->Html->script('QoboAdminPanel.fileinput.min', ['block' => 'scriptBottom']);
echo $this->Html->script('QoboAdminPanel.fileinput-load', ['block' => 'scriptBottom']);
echo $this->Html->script('CsvMigrations.bootstrap-typeahead.min.js', ['block' => 'scriptBottom']);
echo $this->Html->scriptBlock(
    'typeahead_options = ' . json_encode(Configure::read('CsvMigrations.typeahead')) . ';',
    ['block' => 'scriptBottom']
);
echo $this->Html->script('CsvMigrations.typeahead', ['block' => 'scriptBottom']);
echo $this->Html->script('CsvMigrations.embedded', ['block' => 'scriptBottom']);