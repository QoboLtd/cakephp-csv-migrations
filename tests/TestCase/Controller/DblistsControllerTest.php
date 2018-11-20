<?php
namespace CsvMigrations\Test\TestCase\Controller;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;

/**
 * CsvMigrations\Controller\DblistsController Test Case
 */
class DblistsControllerTest extends IntegrationTestCase
{
    public $fixtures = [
        'plugin.CsvMigrations.dblists',
        'plugin.CsvMigrations.dblist_items'
    ];

    private $table;

    public function setUp() : void
    {
        parent::setUp();

        $this->table = TableRegistry::get('CsvMigrations.Dblists');

        $this->session([
            'Auth' => [
                'User' => [
                    'id' => '1',
                    'username' => 'testing'
                ],
            ]
        ]);
    }

    public function tearDown() : void
    {
        unset($this->table);

        parent::tearDown();
    }

    public function testIndex() : void
    {
        $this->get('/csv-migrations/dblists');
        $this->assertResponseOk();
    }

    public function testAdd() : void
    {
        $data = ['name' => 'some really really random name'];

        $this->get('/csv-migrations/dblists/add');
        $this->assertResponseOk();

        $this->post('/csv-migrations/dblists/add', $data);
        $this->assertRedirect();

        $query = $this->table->find()->where($data);
        $this->assertEquals(1, $query->count());
    }

    public function testAddWithInvalidData() : void
    {
        $this->enableRetainFlashMessages();

        $count = $this->table->find()->count();

        // trying to save entity without any data
        $this->post('/csv-migrations/dblists/add/', []);
        $this->assertResponseOk();
        $this->assertEquals($count, $this->table->find()->count());
        $this->assertSession('The database list could not be saved. Please, try again.', 'Flash.flash.0.message');
    }

    public function testEdit() : void
    {
        $id = '35ded6f1-e886-4f3e-bcdd-47d9c55c3ce4';
        $data = ['name' => 'some random name'];

        $this->get('/csv-migrations/dblists/edit/' . $id);
        $this->assertResponseOk();

        $this->put('/csv-migrations/dblists/edit/' . $id, $data);
        $this->assertRedirect();

        $entity = $this->table->get($id);
        $this->assertEquals($data['name'], $entity->get('name'));
    }

    public function testEditWithInvalidData() : void
    {
        $this->enableRetainFlashMessages();

        $id = '35ded6f1-e886-4f3e-bcdd-47d9c55c3ce4';
        $data = ['name' => 'some random name'];

        // create and persist a new entity
        $this->table->save($this->table->newEntity($data));

        $entity = $this->table->get($id);

        // trying to modify another entity's data and set it to the same data as the new persisted entity (created above)
        $this->put('/csv-migrations/dblists/edit/' . $id, $data);
        $this->assertResponseOk();
        $this->assertEquals($entity, $this->table->get($id));
        $this->assertSession('The database list could not be saved. Please, try again.', 'Flash.flash.0.message');
    }

    public function testDelete() : void
    {
        $id = '35ded6f1-e886-4f3e-bcdd-47d9c55c3ce4';

        $this->delete('/csv-migrations/dblists/delete/' . $id);
        $this->assertRedirect();

        $query = $this->table->find()->where(['id' => $id]);
        $this->assertTrue($query->isEmpty());
    }
}
