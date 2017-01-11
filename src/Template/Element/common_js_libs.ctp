<?php
use Cake\Core\Configure;
use Cake\Utility\Inflector;

//@TODO: merge the stuff in one scriptBlock, not dozens;
echo $this->Html->css(
    [
        'AdminLTE./plugins/select2/select2.min',
        'CsvMigrations.select2-bootstrap.min'
    ],
    [
        'block' => 'css'
    ]
);
echo $this->Html->script(
    [
        'AdminLTE./plugins/select2/select2.full.min',
        'CsvMigrations.select2'
    ],
    [
        'block' => 'scriptBotton'
    ]
);
echo $this->Html->scriptBlock(
    'csv_migrations_select2.setup(' . json_encode(
        array_merge(
            Configure::read('CsvMigrations.select2'),
            Configure::read('CsvMigrations.api')
        )
    ) . ');',
    ['block' => 'scriptBotton']
);

echo $this->Html->scriptBlock(
    'api_options = ' . json_encode(Configure::read('CsvMigrations.api')) . ';',
    ['block' => 'scriptBotton']
);


$fileInputOptions = Configure::read('CsvMigrations.BootstrapFileInput');
echo $this->Html->scriptBlock(
    'fileInputOptions = ' . json_encode($fileInputOptions) . ';',
    ['block' => 'scriptBotton']
);

echo $this->Html->script(
    [
        'CsvMigrations.embedded',
        'CsvMigrations.panels',
        'CsvMigrations.canvas-to-blob.min',
        'CsvMigrations.fileinput.min',
        'CsvMigrations.fileinput-load',
        'CsvMigrations.jquery.dynamicSelect',
        'CsvMigrations.jquery.dynamicSelectInit'
    ],
    [
        'block' => 'scriptBotton'
    ]
);
