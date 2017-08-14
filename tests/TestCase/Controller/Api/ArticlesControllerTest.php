<?php
namespace CsvMigrations\Test\TestCase\Controller\Api;

use Cake\Core\Configure;
use Cake\Event\EventList;
use Cake\Event\EventManager;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;
use Cake\Utility\Security;
use CsvMigrations\Event\AddViewListener;
use CsvMigrations\Event\EditViewListener;
use CsvMigrations\Event\IndexViewListener;
use CsvMigrations\Event\LookupListener;
use CsvMigrations\Event\ViewViewListener;
use Firebase\JWT\JWT;

class ArticlesControllerTest extends IntegrationTestCase
{
    public $fixtures = [
        'plugin.CsvMigrations.articles',
        'plugin.CsvMigrations.categories',
        'plugin.CsvMigrations.users'
    ];

    public function setUp()
    {
        parent::setUp();

        $token = JWT::encode(
            ['sub' => '00000000-0000-0000-0000-000000000001', 'exp' => time() + 604800],
            Security::salt()
        );

        $this->Articles = TableRegistry::get('Articles');

        // enable event tracking
        $this->Articles->eventManager()->setEventList(new EventList());

        $this->configRequest([
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'authorization' => 'Bearer ' . $token
            ]
        ]);

        EventManager::instance()->on(new AddViewListener());
        EventManager::instance()->on(new EditViewListener());
        EventManager::instance()->on(new IndexViewListener());
        EventManager::instance()->on(new LookupListener());
        EventManager::instance()->on(new ViewViewListener());
    }

    public function testIndexUnauthenticatedFails()
    {
        $this->configRequest([
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ]
        ]);

        $this->get('/api/articles.json');

        $this->assertResponseError();
        $this->assertContentType('application/json');
    }

    public function testIndex()
    {
        $this->get('/api/articles.json');

        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $this->assertResponseContains('"status": "draft"');
        $this->assertResponseContains('"status": "published"');
        $this->assertResponseContains('"author": "00000000-0000-0000-0000-000000000001"');
        $this->assertResponseContains('"author": "00000000-0000-0000-0000-000000000002"');
    }

    public function testIndexWithConditions()
    {
        $this->get('/api/articles.json?conditions[name]=Foo');

        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $response = json_decode($this->_response->body());
        $this->assertEquals(1, count($response->data));
        $this->assertEquals('Foo', $response->data[0]->name);
    }

    public function testIndexPrettified()
    {
        $this->get('/api/articles.json?format=pretty');

        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $this->assertResponseContains('"status": "Draft"');
        $this->assertResponseContains('"status": "Published"');
        $this->assertResponseContains('user1');
        $this->assertResponseContains('user2');
    }

    public function testIndexDatatables()
    {
        $this->get('/api/articles.json?format=datatables&menus=1&order[0][column]=0');

        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $response = json_decode($this->_response->body());
        $this->assertEquals(2, count($response->data));
        $this->assertEquals([0, 1, 2, 3, 4, 5], array_keys($response->data[0]));
        $this->assertContains('Bar', $response->data[0][0]);
        $this->assertContains('Foo', $response->data[1][0]);
        $this->assertNotEmpty($response->pagination);
        $this->assertTrue($response->success);
    }

    public function testViewUnauthenticatedFails()
    {
        $this->configRequest([
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ]
        ]);

        $this->get('/api/articles/view/00000000-0000-0000-0000-000000000001.json');

        $this->assertResponseError();
        $this->assertContentType('application/json');
    }

    public function testView()
    {
        $this->get('/api/articles/view/00000000-0000-0000-0000-000000000001.json');

        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $response = json_decode($this->_response->body());

        $this->assertEquals('Foo', $response->data->name);
        $this->assertEquals('draft', $response->data->status);
        $this->assertEquals('00000000-0000-0000-0000-000000000001', $response->data->author);
    }

    public function testViewByLookupField()
    {
        $this->get('/api/articles/view/Bar.json');

        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $response = json_decode($this->_response->body());

        $this->assertEquals('Bar', $response->data->name);
        $this->assertEquals('published', $response->data->status);
        $this->assertEquals('00000000-0000-0000-0000-000000000002', $response->data->author);
    }

    public function testViewPrettified()
    {
        $this->get('/api/articles/view/00000000-0000-0000-0000-000000000001.json?format=pretty');

        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $response = json_decode($this->_response->body());
        $this->assertEquals('Foo', $response->data->name);
        $this->assertEquals('Draft', $response->data->status);
        $this->assertContains('user1', $response->data->author);
    }

    public function testAddGet()
    {
        // No session data set.
        $this->get('/api/articles/add.json');

        $this->assertContentType('application/json');
        $this->assertResponseError();
    }

    public function testAddUnauthenticatedFails()
    {
        $this->configRequest([
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ]
        ]);

        $this->post('/api/articles/add.json', []);

        $this->assertContentType('application/json');
        $this->assertResponseError();
    }

    public function testAdd()
    {
        $data = [
            'name' => 'Some Unique Name',
        ];

        $this->post('/api/articles/add.json', json_encode($data));

        $this->assertResponseOk();

        // fetch new record
        $response = json_decode($this->_response->body());
        $entity = $this->Articles->get($response->data->id);

        $this->assertNotEmpty($entity);
    }

    public function testAddWithAssociatedByLookupFields()
    {
        $data = [
            'name' => 'Some Unique Name',
            'author' => 'user2'
        ];

        $this->post('/api/articles/add.json', json_encode($data));

        $this->assertResponseOk();

        // fetch new record
        $response = json_decode($this->_response->body());
        $entity = $this->Articles->get($response->data->id);

        $this->assertEquals($data['name'], $entity->get('name'));
        $this->assertEquals('00000000-0000-0000-0000-000000000002', $entity->get('author'));
    }

    public function testEditGet()
    {
        // No session data set.
        $this->get('/api/articles/edit/00000000-0000-0000-0000-000000000001.json');

        $this->assertContentType('application/json');
        $this->assertResponseError();
    }

    public function testEditUnauthenticatedFails()
    {
        $this->configRequest([
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ]
        ]);

        $this->put('/api/articles/edit/00000000-0000-0000-0000-000000000001.json', []);

        $this->assertContentType('application/json');
        $this->assertResponseError();
    }

    public function testEdit()
    {
        $id = '00000000-0000-0000-0000-000000000001';

        $data = [
            'name' => 'Some Unique Name',
            'status' => 'published'
        ];

        $this->put('/api/articles/edit/' . $id . '.json', json_encode($data));

        $this->assertResponseOk();
        $this->assertContentType('application/json');

        // fetch modified record
        $entity = $this->Articles->get($id);

        $this->assertEquals($data['name'], $entity->get('name'));
        $this->assertEquals($data['status'], $entity->get('status'));
    }

    public function testEditByLookupField()
    {
        $id = '00000000-0000-0000-0000-000000000002';

        $data = [
            'name' => 'Some Unique Name'
        ];

        $this->put('/api/articles/edit/Bar.json', json_encode($data));

        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $response = json_decode($this->_response->body());

        $entity = $this->Articles->get($id);

        $this->assertEquals($data['name'], $entity->name);
    }

    public function testEditPost()
    {
        $id = '00000000-0000-0000-0000-000000000001';

        $data = [
            'name' => 'Some Unique Name'
        ];

        $this->post('/api/articles/edit/' . $id . '.json', json_encode($data));

        $this->assertResponseOk();

        // fetch modified record
        $entity = $this->Articles->get($id);

        $this->assertEquals($data['name'], $entity->name);
    }

    public function testEditWithAssociatedByLookupFields()
    {
        $id = '00000000-0000-0000-0000-000000000002';

        $data = [
            'name' => 'Some Unique Name',
            'author' => 'user2'
        ];

        $this->post('/api/articles/edit/Bar.json', json_encode($data));

        $this->assertResponseOk();

        // fetch modified record
        $entity = $this->Articles->get($id);

        $this->assertEquals($data['name'], $entity->get('name'));
        $this->assertEquals('00000000-0000-0000-0000-000000000002', $entity->get('author'));
    }

    public function testDeleteUnauthenticatedFails()
    {
        $this->configRequest([
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ]
        ]);

        $this->delete('/api/articles/delete/00000000-0000-0000-0000-000000000001.json');

        $this->assertContentType('application/json');
        $this->assertResponseError();
    }

    public function testDelete()
    {
        $id = '00000000-0000-0000-0000-000000000001';

        $this->delete('/api/articles/delete/' . $id . '.json');

        $this->assertResponseOk();

        // try to fetch deleted record
        $query = $this->Articles->find()->where(['id' => $id]);
        $this->assertTrue($query->isEmpty());
    }

    public function testDeletePost()
    {
        $id = '00000000-0000-0000-0000-000000000001';

        $this->post('/api/articles/delete/' . $id . '.json');

        $this->assertResponseOk();

        // try to fetch deleted record
        $query = $this->Articles->find()->where(['id' => $id]);
        $this->assertTrue($query->isEmpty());
    }

    public function testLookupUnauthenticatedFails()
    {
        $this->configRequest([
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ]
        ]);

        $this->get('/api/articles/lookup.json');

        $this->assertResponseError();
        $this->assertContentType('application/json');
    }

    public function testLookup()
    {
        $this->get('/api/articles/lookup.json?query=foo');

        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $response = json_decode($this->_response->body());

        $this->assertTrue($response->success);
        $this->assertNotEmpty($response->pagination);
        $this->assertEquals(1, count($response->data));
        $this->assertEquals(['00000000-0000-0000-0000-000000000001' => 'Second Category » Foo'], (array)$response->data);
    }

    public function testLookupByRelatedTypeaheadField()
    {
        $this->get('/api/articles/lookup.json?query=user2');

        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $response = json_decode($this->_response->body());

        $this->assertEquals(['00000000-0000-0000-0000-000000000002' => 'First Category » Bar'], (array)$response->data);
    }
}
