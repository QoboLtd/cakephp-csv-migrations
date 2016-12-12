<?php
use Cake\Core\Configure;
use Cake\Utility\Inflector;

//@TODO: merge the stuff in one scriptBlock, not dozens;

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


$fileInputOptions = Configure::read('CsvMigrations.BootstrapFileInput');
echo $this->Html->scriptBlock(
    'fileInputOptions = ' . json_encode($fileInputOptions) . ';',
    ['block' => 'scriptBottom']
);
echo $this->Html->script('CsvMigrations.typeahead', ['block' => 'scriptBottom']);
echo $this->Html->script('CsvMigrations.embedded', ['block' => 'scriptBottom']);
echo $this->Html->script('CsvMigrations.panels', ['block' => 'scriptBottom']);
echo $this->Html->script('CsvMigrations.canvas-to-blob.min', ['block' => 'scriptBottom']);
echo $this->Html->script('CsvMigrations.fileinput.min', ['block' => 'scriptBottom']);
echo $this->Html->script('CsvMigrations.fileinput-load', ['block' => 'scriptBottom']);
