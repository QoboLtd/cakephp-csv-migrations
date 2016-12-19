<?php
use Cake\Core\Configure;
use Cake\Utility\Inflector;

//@TODO: merge the stuff in one scriptBlock, not dozens;
echo $this->Html->css('CsvMigrations.select2.min', ['block' => 'cssBottom']);
echo $this->Html->css('CsvMigrations.select2-bootstrap.min', ['block' => 'cssBottom']);
echo $this->Html->script('CsvMigrations.select2.full.min', ['block' => 'scriptBottom']);
echo $this->Html->script('CsvMigrations.select2', ['block' => 'scriptBottom']);
echo $this->Html->scriptBlock(
    'csv_migrations_select2.setup(' . json_encode(
        array_merge(
            Configure::read('CsvMigrations.select2'),
            Configure::read('CsvMigrations.api')
        )
    ) . ');',
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
echo $this->Html->script('CsvMigrations.embedded', ['block' => 'scriptBottom']);
echo $this->Html->script('CsvMigrations.panels', ['block' => 'scriptBottom']);
echo $this->Html->script('CsvMigrations.canvas-to-blob.min', ['block' => 'scriptBottom']);
echo $this->Html->script('CsvMigrations.fileinput.min', ['block' => 'scriptBottom']);
echo $this->Html->script('CsvMigrations.fileinput-load', ['block' => 'scriptBottom']);
echo $this->Html->script('CsvMigrations.jquery.dynamicSelect', ['block' => 'scriptBottom']);

// load field assets
if (!empty($assets['post'])) {
    foreach ($assets['post'] as $asset) {
        echo $this->Html->{$asset['type']}($asset['content'], [
            'block' => !empty($asset['block']) ? $asset['block'] : true
        ]);
    }
}