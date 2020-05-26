<?php

namespace CsvMigrations\Test\TestCase\FieldHandlers\Provider\ValidationRules;

use Cake\TestSuite\TestCase;
use Cake\Validation\ValidationRule;
use Cake\Validation\Validator;
use CsvMigrations\FieldHandlers\Config\AggregatedConfig;
use CsvMigrations\FieldHandlers\Config\BlobConfig;
use CsvMigrations\FieldHandlers\Config\BooleanConfig;
use CsvMigrations\FieldHandlers\Config\DateConfig;
use CsvMigrations\FieldHandlers\Config\DatetimeConfig;
use CsvMigrations\FieldHandlers\Config\DblistConfig;
use CsvMigrations\FieldHandlers\Config\DecimalConfig;
use CsvMigrations\FieldHandlers\Config\EmailConfig;
use CsvMigrations\FieldHandlers\Config\IntegerConfig;
use CsvMigrations\FieldHandlers\Config\ListConfig;
use CsvMigrations\FieldHandlers\Config\PhoneConfig;
use CsvMigrations\FieldHandlers\Config\RelatedConfig;
use CsvMigrations\FieldHandlers\Config\StringConfig;
use CsvMigrations\FieldHandlers\Config\TextConfig;
use CsvMigrations\FieldHandlers\Config\TimeConfig;
use CsvMigrations\FieldHandlers\Config\UrlConfig;
use CsvMigrations\FieldHandlers\Config\UuidConfig;
use CsvMigrations\FieldHandlers\CsvField;
use CsvMigrations\FieldHandlers\Provider\ValidationRules\AggregatedValidationRules;
use CsvMigrations\FieldHandlers\Provider\ValidationRules\BlobValidationRules;
use CsvMigrations\FieldHandlers\Provider\ValidationRules\BooleanValidationRules;
use CsvMigrations\FieldHandlers\Provider\ValidationRules\DatetimeValidationRules;
use CsvMigrations\FieldHandlers\Provider\ValidationRules\DateValidationRules;
use CsvMigrations\FieldHandlers\Provider\ValidationRules\DblistValidationRules;
use CsvMigrations\FieldHandlers\Provider\ValidationRules\DecimalValidationRules;
use CsvMigrations\FieldHandlers\Provider\ValidationRules\EmailValidationRules;
use CsvMigrations\FieldHandlers\Provider\ValidationRules\IntegerValidationRules;
use CsvMigrations\FieldHandlers\Provider\ValidationRules\ListValidationRules;
use CsvMigrations\FieldHandlers\Provider\ValidationRules\PhoneValidationRules;
use CsvMigrations\FieldHandlers\Provider\ValidationRules\RelatedValidationRules;
use CsvMigrations\FieldHandlers\Provider\ValidationRules\StringValidationRules;
use CsvMigrations\FieldHandlers\Provider\ValidationRules\TextValidationRules;
use CsvMigrations\FieldHandlers\Provider\ValidationRules\TimeValidationRules;
use CsvMigrations\FieldHandlers\Provider\ValidationRules\UrlValidationRules;
use CsvMigrations\FieldHandlers\Provider\ValidationRules\UuidValidationRules;

class ValidationRulesTest extends TestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.CsvMigrations.Dblists',
        'plugin.CsvMigrations.DblistItems',
    ];

    /**
     * @dataProvider getValdationRulesByType
     * @param mixed[] $fieldConfig
     * @param mixed[] $rulesConfig
     */
    public function testProvide(string $providerClass, string $configClass, array $fieldConfig, array $rulesConfig): void
    {
        $provider = new $providerClass(new $configClass($fieldConfig['name']));
        $result = $provider->provide(new Validator(), [
            'fieldDefinitions' => new CsvField([
                'name' => $fieldConfig['name'],
                'type' => $fieldConfig['type'],
            ]),
        ]);

        $this->assertInstanceOf(Validator::class, $result);
        $this->assertTrue($result->field($fieldConfig['name'])->isEmptyAllowed());
        $this->assertFalse($result->field($fieldConfig['name'])->isPresenceRequired());
        $this->assertEquals(count($rulesConfig), $result->field($fieldConfig['name'])->count());

        foreach ($rulesConfig as $ruleConfig) {
            $rule = $result->field($fieldConfig['name'])->rule($ruleConfig['name']);
            $this->assertInstanceOf(ValidationRule::class, $rule);
            $this->assertEquals($ruleConfig['rule'], $rule->get('rule'));
        }
    }

    /**
     * @dataProvider getValdationRulesByType
     * @param mixed[] $fieldConfig
     * @param mixed[] $rulesConfig
     */
    public function testProvideWithRequired(string $providerClass, string $configClass, array $fieldConfig, array $rulesConfig): void
    {
        $provider = new $providerClass(new $configClass($fieldConfig['name']));
        $result = $provider->provide(new Validator(), [
            'fieldDefinitions' => new CsvField([
                'name' => $fieldConfig['name'],
                'type' => $fieldConfig['type'],
                'required' => true,
            ]),
        ]);

        $this->assertFalse($result->field($fieldConfig['name'])->isEmptyAllowed());
        $this->assertEquals('create', $result->field($fieldConfig['name'])->isPresenceRequired());
        $this->assertEquals(count($rulesConfig) + 1, $result->field($fieldConfig['name'])->count());

        $validatorRules = [];
        foreach ($result->field($fieldConfig['name'])->rules() as $rule) {
            $this->assertInstanceOf(ValidationRule::class, $rule);
            $validatorRules[] = $rule->get('rule');
        }
        sort($validatorRules);

        $expected = array_merge(['notBlank'], array_column($rulesConfig, 'rule'));
        sort($expected);

        $this->assertEquals($expected, $validatorRules);
    }

    public function testProvideWithoutValidationRules(): void
    {
        $provider = new AggregatedValidationRules(new AggregatedConfig('aggregated_field'));
        $validator = new Validator();
        $result = $provider->provide($validator, [
            'fieldDefinitions' => new CsvField([
                'name' => 'aggregated_field',
                'type' => 'aggregated',
            ]),
        ]);

        $this->assertSame($validator, $result);
    }

    /**
     * @return mixed[]
     */
    public function getValdationRulesByType(): array
    {
        return [
            [
                BlobValidationRules::class,
                BlobConfig::class,
                ['name' => 'bio', 'type' => 'blob'],
                [['name' => 'scalar', 'rule' => 'isScalar']],
            ],
            [
                BooleanValidationRules::class,
                BooleanConfig::class,
                ['name' => 'active', 'type' => 'boolean'],
                [['name' => 'boolean', 'rule' => 'boolean']],
            ],
            [
                DatetimeValidationRules::class,
                DatetimeConfig::class,
                ['name' => 'appointment', 'type' => 'datetime'],
                [['name' => 'dateTime', 'rule' => 'datetime']],
            ],
            [
                DateValidationRules::class,
                DateConfig::class,
                ['name' => 'birthdate', 'type' => 'date'],
                [['name' => 'date', 'rule' => 'date']],
            ],
            [
                DblistValidationRules::class,
                DblistConfig::class,
                ['name' => 'shift', 'type' => 'dblist(categories)'],
                [['name' => 'inList', 'rule' => 'inList'], ['name' => 'scalar', 'rule' => 'isScalar']],
            ],
            [
                DecimalValidationRules::class,
                DecimalConfig::class,
                ['name' => 'rate', 'type' => 'decimal'],
                [['name' => 'decimal', 'rule' => 'decimal']],
            ],
            [
                EmailValidationRules::class,
                EmailConfig::class,
                ['name' => 'company_email', 'type' => 'email'],
                [['name' => 'email', 'rule' => 'email']],
            ],
            [
                IntegerValidationRules::class,
                IntegerConfig::class,
                ['name' => 'meters', 'type' => 'integer'],
                [['name' => 'integer', 'rule' => 'isInteger']],
            ],
            [
                ListValidationRules::class,
                ListConfig::class,
                ['name' => 'status', 'type' => 'list(list)'],
                [['name' => 'inList', 'rule' => 'inList'], ['name' => 'scalar', 'rule' => 'isScalar']],
            ],
            [
                PhoneValidationRules::class,
                PhoneConfig::class,
                ['name' => 'mobile_phone', 'type' => 'phone'],
                [['name' => 'regex', 'rule' => 'custom']],
            ],
            [
                RelatedValidationRules::class,
                RelatedConfig::class,
                ['name' => 'post_id', 'type' => 'related(Posts)'],
                [['name' => 'uuid', 'rule' => 'uuid']],
            ],
            [
                StringValidationRules::class,
                StringConfig::class,
                ['name' => 'first_name', 'type' => 'string'],
                [['name' => 'scalar', 'rule' => 'isScalar']],
            ],
            [
                TextValidationRules::class,
                TextConfig::class,
                ['name' => 'description', 'type' => 'text'],
                [['name' => 'scalar', 'rule' => 'isScalar']],
            ],
            [
                TimeValidationRules::class,
                TimeConfig::class,
                ['name' => 'shift', 'type' => 'time'],
                [['name' => 'time', 'rule' => 'time']],
            ],
            [
                UrlValidationRules::class,
                UrlConfig::class,
                ['name' => 'website', 'type' => 'url'],
                [['name' => 'url', 'rule' => 'url']],
            ],
            [
                UuidValidationRules::class,
                UuidConfig::class,
                ['name' => 'rule', 'type' => 'uuid'],
                [['name' => 'uuid', 'rule' => 'uuid']],
            ],
        ];
    }
}
