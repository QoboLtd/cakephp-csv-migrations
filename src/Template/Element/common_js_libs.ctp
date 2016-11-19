<?php
use Cake\Core\Configure;

echo $this->Html->script('CsvMigrations.bootstrap-typeahead.min.js', ['block' => 'scriptBottom']);
echo $this->Html->scriptBlock(
    'typeahead_options = ' . json_encode(
        array_merge(
            Configure::read('CsvMigrations.typeahead'),
            Configure::read('CsvMigrations.api')
        )
    ) . ';',
    ['block' => 'scriptBottom']
);
echo $this->Html->scriptBlock(
    'api_options = ' . json_encode(Configure::read('CsvMigrations.api')) . ';',
    ['block' => 'scriptBottom']
);
echo $this->Html->script('CsvMigrations.typeahead', ['block' => 'scriptBottom']);
echo $this->Html->script('CsvMigrations.embedded', ['block' => 'scriptBottom']);
echo $this->Html->script('CsvMigrations.panels', ['block' => 'scriptBottom']);
