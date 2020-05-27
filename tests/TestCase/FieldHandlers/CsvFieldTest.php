<?php

namespace CsvMigrations\Test\TestCase\FieldHandlers;

use Cake\Core\Configure;
use CsvMigrations\FieldHandlers\CsvField;
use PHPUnit\Framework\TestCase;
use Qobo\Utils\ModuleConfig\ConfigType;
use Qobo\Utils\ModuleConfig\ModuleConfig;

class CsvFieldTest extends TestCase
{
    private $csvData;

    public function setUp(): void
    {
        parent::setUp();

        Configure::write('CsvMigrations.modules.path', TESTS . 'config' . DS . 'Modules' . DS);

        $mc = new ModuleConfig(ConfigType::MIGRATION(), 'Foo');
        $config = json_encode($mc->parse());
        $this->csvData = false !== $config ? json_decode($config, true) : [];
    }

    public function tearDown(): void
    {
        unset($this->csvData);

        parent::tearDown();
    }

    /**
     * Test that default values are set correct
     *
     * @see Task #2431
     */
    public function testDefaults(): void
    {
        $csvField = new CsvField(['name' => 'foobar']);

        $this->assertEquals('foobar', $csvField->getName(), "Field name was not set correctly");

        $this->assertEquals('string', $csvField->getType(), "Default field type was not set to string");
        $this->assertEquals(CsvField::DEFAULT_FIELD_TYPE, $csvField->getType(), "Default field type was not set to correctly");

        $this->assertEquals(null, $csvField->getLimit(), "Field limit was not set to null");
        $this->assertEquals(CsvField::DEFAULT_FIELD_LIMIT, $csvField->getLimit(), "Default field limit was not set coorectly");

        $this->assertEquals(false, $csvField->getRequired(), "Field required was not set to false");
        $this->assertEquals(CsvField::DEFAULT_FIELD_REQUIRED, $csvField->getRequired(), "Default field required was not set coorectly");

        $this->assertEquals(false, $csvField->getNonSearchable(), "Field non-searchable was not set to false");
        $this->assertEquals(CsvField::DEFAULT_FIELD_NON_SEARCHABLE, $csvField->getNonSearchable(), "Default field non-searchable was not set coorectly");

        $this->assertEquals(false, $csvField->getUnique(), "Field unique was not set to false");
        $this->assertEquals(CsvField::DEFAULT_FIELD_UNIQUE, $csvField->getUnique(), "Default field unique was not set coorectly");
    }

    /**
     * @dataProvider nameSetterProvider
     * @param mixed $expected
     * @param mixed $name
     */
    public function testSetName($expected, $name): void
    {
        $csvField = new CsvField(current($this->csvData));
        $csvField->setName($name);
        $this->assertEquals($expected, $csvField->getName());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetNameThrowsException(): void
    {
        $csvField = new CsvField(current($this->csvData));
        $csvField->setName('');
    }

    public function testGetName(): void
    {
        foreach ($this->getterProvider() as $v) {
            $csvField = new CsvField(array_shift($this->csvData));
            $this->assertEquals($v[0], $csvField->getName());
        }
    }

    /**
     * @dataProvider typeSetterProvider
     */
    public function testSetType(string $expected, string $type): void
    {
        $csvField = new CsvField(current($this->csvData));
        $csvField->setType($type);
        $this->assertEquals($expected, $csvField->getType());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetTypeEmptyValueThrowsException(): void
    {
        $csvField = new CsvField(current($this->csvData));
        $csvField->setType('');
    }

    public function testGetType(): void
    {
        foreach ($this->getterProvider() as $v) {
            $csvField = new CsvField(array_shift($this->csvData));
            $this->assertEquals($v[1], $csvField->getType());
        }
    }

    /**
     * @dataProvider limitSetterProvider
     * @param mixed $expected
     * @param mixed $type
     */
    public function testSetLimit($expected, $type): void
    {
        $csvField = new CsvField(current($this->csvData));
        $csvField->setLimit($type);
        $this->assertEquals($expected, $csvField->getLimit());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetLimitEmptyValueThrowsException(): void
    {
        $csvField = new CsvField(current($this->csvData));
        $csvField->setLimit('');
    }

    /**
     * @dataProvider limitSetterProvider
     * @param mixed $expected
     * @param mixed $type
     */
    public function testGetLimit($expected, $type): void
    {
        $csvField = new CsvField(current($this->csvData));
        $csvField->setLimit($type);
        $this->assertEquals($expected, $csvField->getLimit());
    }

    /**
     * @dataProvider limitSetterProvider
     * @param mixed $expected
     * @param mixed $type
     */
    public function testGetListName($expected, $type): void
    {
        $csvField = new CsvField(current($this->csvData));
        $csvField->setLimit($type);
        $expected = $csvField->getLimit();
        $actual = $csvField->getListName();
        $this->assertEquals($expected, $actual);
    }

    public function testGetAssocCsvModule(): void
    {
        foreach ($this->getterProvider() as $v) {
            $csvField = new CsvField(array_shift($this->csvData));
            $expected = $v[2];
            if (!empty($csvField->getAssocCsvModule())) {
                $expected = 'Common.' . $expected;
            }
            $this->assertEquals($expected, $csvField->getAssocCsvModule());
        }
    }

    /**
     * @dataProvider booleanSetterProvider
     * @param mixed $boolean
     */
    public function testSetRequired(bool $expected, $boolean): void
    {
        $csvField = new CsvField(current($this->csvData));
        $csvField->setRequired($boolean);
        $this->assertEquals($expected, $csvField->getRequired());
    }

    public function testGetRequired(): void
    {
        foreach ($this->getterProvider() as $v) {
            $csvField = new CsvField(array_shift($this->csvData));
            $this->assertEquals($v[3], $csvField->getRequired());
        }
    }

    /**
     * @dataProvider booleanSetterProvider
     * @param mixed $boolean
     */
    public function testSetNonSearchable(bool $expected, $boolean): void
    {
        $csvField = new CsvField(current($this->csvData));
        $csvField->setNonSearchable($boolean);
        $this->assertEquals($expected, $csvField->getNonSearchable());
    }

    public function testGetNonSearchable(): void
    {
        foreach ($this->getterProvider() as $v) {
            $csvField = new CsvField(array_shift($this->csvData));
            $this->assertEquals($v[4], $csvField->getNonSearchable());
        }
    }

    /**
     * @dataProvider booleanSetterProvider
     * @param mixed $boolean
     */
    public function testSetUnique(bool $expected, $boolean): void
    {
        $csvField = new CsvField(current($this->csvData));
        $csvField->setUnique($boolean);
        $this->assertEquals($expected, $csvField->getUnique());
    }

    public function testGetUnique(): void
    {
        foreach ($this->getterProvider() as $v) {
            $csvField = new CsvField(array_shift($this->csvData));
            $this->assertEquals($v[5], $csvField->getUnique());
        }
    }

    /**
     * @return mixed[]
     */
    public function getterProvider(): array
    {
        return [
            ['id', 'uuid', '', '', '', false],
            ['description', 'text', '', '', true, false],
            ['name', 'string', '', '1', '', true],
            ['status', 'list', 'foo_statuses', '1', '', false],
            ['type', 'list', 'foo_types', '1', '', false],
            ['gender', 'list', 'genders', '', '', false],
            ['city', 'list', 'cities', '', '', false],
            ['country', 'list', 'countries', '', '', false],
            ['cost', 'money', 'currencies', '', '', false],
            ['birthdate', 'date', '', '', '', false],
            ['reminder_date', 'reminder', '', '', '', false],
            ['created', 'datetime', '', '', '', false],
            ['modified', 'datetime', '', '', '', false],
        ];
    }

    /**
     * @return mixed[]
     */
    public function nameSetterProvider(): array
    {
        return [
            ['foo', 'foo'],
            ['bar', 'bar'],
            ['FooBar', 'FooBar'],
            [123, 123],
            ['123', '123'],
        ];
    }

    /**
     * @return mixed[]
     */
    public function typeSetterProvider(): array
    {
        return [
            ['related', 'related(Foobar)'],
            ['has_many', 'has_many(Foobar)'],
            ['money', 'money(Foobar)'],
            ['metric', 'metric(Foobar)'],
            ['list', 'list(Foobar)'],
            ['datetime', 'datetime'],
            ['date', 'date'],
            ['time', 'time'],
            ['text', 'text'],
            ['uuid', 'uuid'],
            ['boolean', 'boolean'],
            ['string', 'string'],
            ['integer', 'integer'],
            ['files', 'files'],
            ['images', 'images'],
        ];
    }

    /**
     * @return mixed[]
     */
    public function limitSetterProvider(): array
    {
        return [
            ['Foobar', 'related(Foobar)'],
            ['Foobar', 'has_many(Foobar)'],
            ['Foobar', 'money(Foobar)'],
            ['Foobar', 'metric(Foobar)'],
            ['Foobar', 'list(Foobar)'],
            [null, 'datetime'],
            [null, 'date'],
            [null, 'time'],
            [null, 'text'],
            [null, 'uuid'],
            [35, 'uuid(35)'],
            [null, 'boolean'],
            [10, 'boolean(10)'],
            [255, 'string(255)'],
            [100, 'string(100)'],
            [null, 'string'],
            [null, 'integer'],
            [5, 'integer(5)'],
            [11, 'integer(11)'],
            [null, 'file'],
            [null, null],
            [5, '5'],
            [5, 5],
            [5, -5],
            [null, 0],
        ];
    }

    /**
     * @return mixed[]
     */
    public function booleanSetterProvider(): array
    {
        return [
            [true, 1],
            [true, 'foo'],
            [true, 100],
            [true, true],
            [true, ['foo']],
            [false, 0],
            [false, false],
            [false, null],
            [false, []],
        ];
    }
}
