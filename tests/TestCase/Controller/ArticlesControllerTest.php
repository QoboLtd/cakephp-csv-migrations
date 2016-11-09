<?php
namespace App\Test\TestCase\Controller;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;

class ArticlesControllerTest extends IntegrationTestCase
{
    public $fixtures = ['plugin.CsvMigrations.articles'];

    public function testIndexUnauthenticatedFails()
    {
        // No session data set.
        $this->get('/articles');

        $this->assertRedirect(['controller' => 'Users', 'action' => 'login']);
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

        $this->assertRedirect(['controller' => 'Users', 'action' => 'login']);
    }

    public function testView()
    {
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

        $this->assertRedirect(['controller' => 'Users', 'action' => 'login']);
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

    public function testEditUnauthenticatedFails()
    {
        // No session data set.
        $this->get('/articles/edit');

        $this->assertRedirect(['controller' => 'Users', 'action' => 'login']);
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
}
