<?php
namespace CsvMigrations\Test\TestCase\Validation;

use Cake\TestSuite\TestCase;
use Cake\Validation\Validator as CakeValidator;

use CsvMigrations\Validation\Validator;

class ValidatorTest extends TestCase
{
    /**
     * Cake's validator
     * @var \Cake\Validation\Validator
     */
    protected $CakeValidator;

    /**
     * Validator instance
     * @var \CsvMigrations\Validation\Validator
     */
    protected $Validator;

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->CakeValidator = new CakeValidator();
        $this->Validator = new Validator();
    }

    /**
     * {@inheritDoc}
     */
    public function tearDown(): void
    {
        unset($this->CakeValidator);
        unset($this->Validator);

        parent::tearDown();
    }

    /**
     * Test that the validator can be attached to Cake's validator
     *
     * @return void
     */
    public function testValidatorCanBeCalledFromCake(): void
    {
        $this->CakeValidator->setProvider('testProvider', $this->Validator);
        $this->CakeValidator->add('foo', 'inModuleList', [
            'rule' => ['inModuleList', 'Common', 'currencies'],
            'provider' => 'testProvider',
        ]);

        // Good run
        $errors = $this->CakeValidator->errors(['foo' => 'EUR']);
        $this->assertEmpty($errors);

        // Bad run
        $errors = $this->CakeValidator->errors(['foo' => 'BAR']);
        $this->assertNotEmpty($errors);
        $this->assertArrayHasKey('foo', $errors);
        $this->assertArrayHasKey('inModuleList', $errors['foo']);
    }

    /**
     * Test that an item from a list can be validated successfully
     *
     * @return void
     */
    public function testInModuleListModuleConfigError(): void
    {
        $result = $this->Validator->inModuleList('EUR', 'Common', 'bad_currencies_list');
        $this->assertTrue(is_string($result), 'Expected error string');
        $this->assertContains('Path does not', $result);
        $this->assertContains('bad_currencies_list', $result);
    }

    /**
     *  Test that empty list returns an error
     *
     * @return void
     */
    public function testInModuleListBadItem(): void
    {
        $result = $this->Validator->inModuleList('FOO', 'Common', 'currencies');
        $this->assertTrue(is_string($result), 'Expected error string');
        $this->assertContains('Invalid list item', $result);
    }
}
