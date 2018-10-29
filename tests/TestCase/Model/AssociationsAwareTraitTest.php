<?php
namespace CsvMigrations\Test\TestCase\Model;

use Cake\ORM\Association\BelongsTo;
use Cake\ORM\Association\BelongsToMany;
use Cake\ORM\Association\HasMany;
use Cake\ORM\TableRegistry;
use CsvMigrations\Model\AssociationsAwareTrait;
use PHPUnit\Framework\TestCase;

class AssociationsAwareTraitTest extends TestCase
{
    public function setUp()
    {
        // clear table registry to avoid ambiguous table instances during test runs
        TableRegistry::clear();
    }

    /**
     * @dataProvider associationNameProvider
     */
    public function testCreateAssociationName($expected, $module, $foreignKey)
    {
        $this->assertEquals($expected, AssociationsAwareTrait::generateAssociationName($module, $foreignKey));
    }

    /**
     * @dataProvider associationsProvider
     */
    public function testAssociations($table, $name, $type, $joinTable = '')
    {
        $association = TableRegistry::get($table)->association($name);

        $this->assertInstanceOf($type, $association);

        if ('' !== trim($joinTable)) {
            $this->assertEquals($joinTable, $association->junction()->getTable());
        }
    }

    /**
     * Assert that Table contains only specific associations.
     *
     * @return void
     */
    public function testAssociationsStrict()
    {
        $data = [];
        // normalize data
        foreach ($this->associationsProvider() as $value) {
            $data[$value[0]][] = strtolower($value[1]);
        }

        foreach ($data as $tableName => $associations) {
            $tableAssociations = TableRegistry::get($tableName)->associations()->keys();
            $this->assertEmpty(array_diff($tableAssociations, $associations));
        }
    }

    public function associationNameProvider()
    {
        return [
            ['BarFoo', 'Foo', 'bar'],
            ['KeyFoobar', 'Foobar', 'key'],
            ['Foobar', 'Foobar', ''],
            ['KeyFooBar', 'Foo.Bar', 'key'],
            ['ForeignKeyFooBar', 'Foo.Bar', 'foreign_key'],
            ['KeyFooBar', 'Vendor/Foo.Bar', 'key']
        ];
    }

    public function associationsProvider()
    {
        return [
            ['Articles', 'AuthorAuthors', BelongsTo::class],
            ['Articles', 'CategoryCategories', BelongsTo::class],
            ['Articles', 'ImageFileStorageFileStorage', HasMany::class],
            ['Articles', 'MainArticleArticles', BelongsTo::class],
            ['Authors', 'AuthorArticles', HasMany::class],
            ['Authors', 'OwnerPosts', HasMany::class],
            ['Categories', 'CategoryArticles', HasMany::class],
            ['Foo', 'CreatedByUsers', BelongsTo::class],
            ['Foo', 'LeadLeads', BelongsTo::class],
            ['Foo', 'ModifiedByUsers', BelongsTo::class],
            ['Leads', 'AssignedToUsers', BelongsTo::class],
            ['Leads', 'LeadFoo', HasMany::class],
            ['Posts', 'OwnerAuthors', BelongsTo::class],
            ['Posts', 'TagIdPostTags', BelongsToMany::class, 'post_tags'],
            ['Tags', 'PostIdPostTags', BelongsToMany::class, 'post_tags'],
            ['Users', 'AssignedToLeads', HasMany::class],
            ['Users', 'CreatedByFoo', HasMany::class],
            ['Users', 'ModifiedByFoo', HasMany::class],
        ];
    }
}
