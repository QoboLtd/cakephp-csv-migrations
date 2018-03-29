<?php
namespace CsvMigrations\Test\TestCase\Model;

use Cake\ORM\Association\BelongsTo;
use Cake\ORM\Association\BelongsToMany;
use Cake\ORM\Association\HasMany;
use Cake\ORM\TableRegistry;
use CsvMigrations\Model\AssociationsAwareTrait;
use PHPUnit_Framework_TestCase;

class AssociationsAwareTraitTest extends PHPUnit_Framework_TestCase
{
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
    public function testAssociations($table, $name, $type)
    {
        $this->assertInstanceOf($type, TableRegistry::getTableLocator()->get($table)->association($name));
    }

    /**
     * @dataProvider manyToManyAssociationsProvider
     */
    public function testManyToManyAssociations($table, $name, $joinTable)
    {
        $association = TableRegistry::getTableLocator()->get($table)->association($name);
        $this->assertInstanceOf(BelongsToMany::class, $association);
        $this->assertEquals($joinTable, $association->junction()->getTable());
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
            ['Articles', 'AuthorUsers', BelongsTo::class],
            ['Articles', 'CategoryCategories', BelongsTo::class],
            ['Articles', 'MainArticleArticles', BelongsTo::class],
            ['Leads', 'AssignedToUsers', BelongsTo::class],
            ['Posts', 'OwnerAuthors', BelongsTo::class],
            ['Users', 'AuthorArticles', HasMany::class],
            ['Categories', 'CategoryArticles', HasMany::class],
            ['Users', 'AssignedToLeads', HasMany::class],
            ['Authors', 'OwnerPosts', HasMany::class],
        ];
    }

    public function manyToManyAssociationsProvider()
    {
        return [
            ['Posts', 'TagIdPostTags', 'post_tags'],
            ['Tags', 'PostIdPostTags', 'post_tags']
        ];
    }
}
