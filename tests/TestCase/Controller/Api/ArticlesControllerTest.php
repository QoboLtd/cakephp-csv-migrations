<?php
namespace App\Test\TestCase\Controller\Api;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;
use Cake\Utility\Security;
use Firebase\JWT\JWT;

class ArticlesControllerTest extends IntegrationTestCase
{
    public $fixtures = [
        'plugin.CsvMigrations.articles',
        'plugin.CsvMigrations.users'
    ];

    /**
     * JWT token.
     *
     * @var string
     */
    protected $_token = null;

    public function setUp()
    {
        $this->_token = JWT::encode(
            ['sub' => '00000000-0000-0000-0000-000000000001', 'exp' => time() + 604800],
            Security::salt()
        );

        parent::setUp();
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

        $this->configRequest([
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'authorization' => 'Bearer ' . $this->_token
            ]
        ]);
        $this->get('/api/articles.json');

        $this->assertResponseOk();
        $this->assertContentType('application/json');
        $this->assertResponseContains('"name": "Foo"');
        $this->assertResponseContains('"name": "Bar"');
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
        $this->configRequest([
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'authorization' => 'Bearer ' . $this->_token
            ]
        ]);
        $this->get('/api/articles/view/00000000-0000-0000-0000-000000000001.json');

        $this->assertResponseOk();
        $this->assertContentType('application/json');
        $this->assertResponseContains('"name": "Foo"');
    }

    public function testAddGetRequest()
    {
        $this->configRequest([
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'authorization' => 'Bearer ' . $this->_token
            ]
        ]);

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

    public function testAddPostData()
    {
        $this->configRequest([
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'authorization' => 'Bearer ' . $this->_token
            ]
        ]);

        $data = [
            'name' => 'Some Unique Name'
        ];

        $this->post('/api/articles/add.json', json_encode($data));

        $this->assertResponseSuccess();

        // fetch new record
        $response = json_decode($this->_response->body());
        $entity = TableRegistry::get('Articles')->get($response->data->id);

        $this->assertNotEmpty($entity);
    }

    public function testEditGetRequest()
    {
        $this->configRequest([
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'authorization' => 'Bearer ' . $this->_token
            ]
        ]);

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

    public function testEditPostData()
    {
        $this->configRequest([
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'authorization' => 'Bearer ' . $this->_token
            ]
        ]);

        $id = '00000000-0000-0000-0000-000000000001';

        $data = [
            'name' => 'Some Unique Name'
        ];

        $this->post('/api/articles/edit/' . $id . '.json', json_encode($data));

        $this->assertResponseSuccess();

        // fetch modified record
        $entity = TableRegistry::get('Articles')->get($id);

        $this->assertEquals($data['name'], $entity->name);
    }

    public function testEditPutData()
    {
        $this->configRequest([
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'authorization' => 'Bearer ' . $this->_token
            ]
        ]);

        $id = '00000000-0000-0000-0000-000000000001';

        $data = [
            'name' => 'Some Unique Name'
        ];

        $this->put('/api/articles/edit/' . $id . '.json', json_encode($data));

        $this->assertResponseSuccess();

        // fetch modified record
        $entity = TableRegistry::get('Articles')->get($id);

        $this->assertEquals($data['name'], $entity->name);
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

    public function testDeleteData()
    {
        $this->configRequest([
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'authorization' => 'Bearer ' . $this->_token
            ]
        ]);

        $id = '00000000-0000-0000-0000-000000000001';

        $this->delete('/api/articles/delete/' . $id . '.json');

        $this->assertResponseSuccess();

        // try to fetch deleted record
        $query = TableRegistry::get('Articles')->find()->where(['id' => $id]);
        $this->assertEquals(0, $query->count());
    }

    public function testDeletePostData()
    {
        $this->configRequest([
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'authorization' => 'Bearer ' . $this->_token
            ]
        ]);

        $id = '00000000-0000-0000-0000-000000000001';

        $this->post('/api/articles/delete/' . $id . '.json');

        $this->assertResponseSuccess();

        // try to fetch deleted record
        $query = TableRegistry::get('Articles')->find()->where(['id' => $id]);
        $this->assertEquals(0, $query->count());
    }
}
