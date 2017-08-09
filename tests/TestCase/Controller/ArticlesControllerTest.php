<?php
namespace CsvMigrations\Test\TestCase\Controller;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;

/**
 * CsvMigrations\Test\App\Controller\ArticlesController Test Case
 */
class ArticlesControllerTest extends IntegrationTestCase
{
    public $fixtures = [
        'plugin.csv_migrations.articles',
        'plugin.csv_migrations.users'
    ];

    public function testIndexUnauthenticatedFails()
    {
        // No session data set.
        $this->get('/articles');

        $this->assertRedirectContains('/users/login');
    }

    public function testIndex()
    {
        $this->session([
            'Auth.User.id' => '00000000-0000-0000-0000-000000000001'
        ]);
        $this->get('/articles');

        $this->assertResponseOk();
        $this->assertResponseContains('Name');
        $this->assertResponseContains('Created');
        $this->assertResponseContains('Modified');
        $this->assertResponseContains('Actions');
    }

    public function testViewUnauthenticatedFails()
    {
        // No session data set.
        $this->get('/articles/view/00000000-0000-0000-0000-000000000001');

        $this->assertRedirectContains('/users/login');
    }

    public function testView()
    {
        // @todo view.ctp always assumes that Translations plugin is loaded, this needs fixing
        $this->markTestSkipped();

        $this->session([
            'Auth.User.id' => '00000000-0000-0000-0000-000000000001'
        ]);
        $this->get('/articles/view/00000000-0000-0000-0000-000000000001');
        $this->assertResponseOk();
        $this->assertResponseContains('Name:');
        $this->assertResponseContains('Created:');
        $this->assertResponseContains('Modified:');
        $this->assertResponseContains('Foo');
    }

    public function testAddUnauthenticatedFails()
    {
        // No session data set.
        $this->get('/articles/add');

        $this->assertRedirectContains('/users/login');
    }

    public function testAdd()
    {
        $this->session([
            'Auth.User.id' => '00000000-0000-0000-0000-000000000001'
        ]);

        $this->get('/articles/add');
        $this->assertResponseOk();
        // form element and attributes
        $this->assertResponseContains('<form');
        $this->assertResponseContains('action="/articles/add"');
        $this->assertResponseContains('data-panels-url="/api/articles/panels"');
        // submit button
        $this->assertResponseContains('type="submit"');
        // input element(s) and attributes
        $this->assertResponseContains('Name');
        $this->assertResponseContains('name="Articles[name]"');
    }

    public function testAddPostData()
    {
        $this->session([
            'Auth.User.id' => '00000000-0000-0000-0000-000000000001'
        ]);

        $data = [
            'name' => 'Some Unique Name'
        ];

        $this->post('/articles/add', $data);
        $this->assertResponseSuccess();

        // fetch new record
        $query = TableRegistry::get('Articles')->find()->where(['name' => $data['name']]);

        $this->assertEquals(1, $query->count());
    }

    public function testEditUnauthenticatedFails()
    {
        // No session data set.
        $this->get('/articles/edit');

        $this->assertRedirectContains('/users/login');
    }

    public function testEdit()
    {
        $this->session([
            'Auth.User.id' => '00000000-0000-0000-0000-000000000001'
        ]);

        $this->get('/articles/edit/00000000-0000-0000-0000-000000000001');
        $this->assertResponseOk();
        // form element and attributes
        $this->assertResponseContains('<form');
        $this->assertResponseContains('action="/articles/edit/00000000-0000-0000-0000-000000000001"');
        $this->assertResponseContains('data-panels-url="/api/articles/panels"');
        // submit button
        $this->assertResponseContains('type="submit"');
        // input element(s) and attributes
        $this->assertResponseContains('Name');
        $this->assertResponseContains('name="Articles[name]"');
        $this->assertResponseContains('value="Foo"');
    }

    public function testEditPostData()
    {
        $this->session([
            'Auth.User.id' => '00000000-0000-0000-0000-000000000001'
        ]);

        $id = '00000000-0000-0000-0000-000000000001';

        $data = [
            'name' => 'Some Unique Name'
        ];

        $this->post('/articles/edit/' . $id, $data);
        $this->assertResponseSuccess();

        // fetch modified record
        $entity = TableRegistry::get('Articles')->get($id);

        $this->assertEquals($data['name'], $entity->name);
    }

    public function testEditPutData()
    {
        $this->session([
            'Auth.User.id' => '00000000-0000-0000-0000-000000000001'
        ]);

        $id = '00000000-0000-0000-0000-000000000001';

        $data = [
            'name' => 'Some Unique Name'
        ];

        $this->put('/articles/edit/' . $id, $data);
        $this->assertResponseSuccess();

        // fetch modified record
        $entity = TableRegistry::get('Articles')->get($id);

        $this->assertEquals($data['name'], $entity->name);
    }

    public function testDeleteUnauthenticatedFails()
    {
        // No session data set.
        $this->delete('/articles/delete/00000000-0000-0000-0000-000000000001');

        $this->assertRedirect(['controller' => 'Users', 'action' => 'login']);
    }

    public function testDeleteGetRequest()
    {
        $this->session([
            'Auth.User.id' => '00000000-0000-0000-0000-000000000001'
        ]);

        $this->get('/articles/delete/00000000-0000-0000-0000-000000000001');
        $this->assertResponseError();
    }

    public function testDeleteData()
    {
        $this->session([
            'Auth.User.id' => '00000000-0000-0000-0000-000000000001'
        ]);

        $id = '00000000-0000-0000-0000-000000000001';

        $this->delete('/articles/delete/' . $id);
        $this->assertResponseSuccess();

        // try to fetch deleted record
        $query = TableRegistry::get('Articles')->find()->where(['id' => $id]);
        $this->assertEquals(0, $query->count());
    }

    public function testDeletePostData()
    {
        $this->session([
            'Auth.User.id' => '00000000-0000-0000-0000-000000000001'
        ]);

        $id = '00000000-0000-0000-0000-000000000001';

        $this->post('/articles/delete/' . $id);
        $this->assertResponseSuccess();

        // try to fetch deleted record
        $query = TableRegistry::get('Articles')->find()->where(['id' => $id]);
        $this->assertEquals(0, $query->count());
    }
}
