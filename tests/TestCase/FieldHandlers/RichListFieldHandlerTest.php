<?php
namespace CsvMigrations\Test\TestCase\FieldHandlers;

use Cake\Core\Configure;
use CsvMigrations\FieldHandlers\Config\ConfigFactory;
use CsvMigrations\FieldHandlers\CsvField;
use CsvMigrations\FieldHandlers\FieldHandler;
use PHPUnit_Framework_TestCase;

class RichListFieldHandlerTest extends PHPUnit_Framework_TestCase
{
    protected $table = 'fields';
    protected $field = 'field_list';
    protected $type = 'rich_list';

    protected $fh;

    protected function setUp()
    {
        $dir = dirname(__DIR__) . DS . '..' . DS . 'config' . DS . 'Modules' . DS;
        Configure::write('CsvMigrations.modules.path', $dir);

        $config = ConfigFactory::getByType($this->type, $this->field, $this->table);
        $this->fh = new FieldHandler($config);
    }

    public function getRenderedValues()
    {
        return [
            ['', ''],
            ['cy', '<strong>Cyprus</strong>'],
            ['usa', '<em>USA</em>'],
        ];
    }

    /**
     * @dataProvider getRenderedValues
     */
    public function testRenderValue($value, $expected)
    {
        $options['fieldDefinitions'] = new CsvField([
            'name' => $this->field,
            'type' => 'rich_list(colors)',
            'required' => false,
            'non-searchable' => false,
            'unique' => false
        ]);

        $result = $this->fh->renderValue($value, $options);
    }
}
