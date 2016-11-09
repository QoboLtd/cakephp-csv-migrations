<?php
namespace App\Test\TestCase\Controller;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;

class ArticlesControllerTest extends IntegrationTestCase
{
    public $fixtures = ['plugin.CsvMigrations.articles'];

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

    public function testView()
    {
        $this->session([
            'Auth.User.id' => '00000000-0000-0000-0000-000000000001'
        ]);
        $this->get('/articles/view/d8c3ba90-c418-4e58-8cb6-b65c9095a2dc');
        $this->assertResponseOk();
        $this->assertResponseContains('Name:');
        $this->assertResponseContains('Created:');
        $this->assertResponseContains('Modified:');
        $this->assertResponseContains('Foo');
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

    public function testEdit()
    {
        $this->session([
            'Auth.User.id' => '00000000-0000-0000-0000-000000000001'
        ]);

        $this->get('/articles/edit/d8c3ba90-c418-4e58-8cb6-b65c9095a2dc');
        $this->assertResponseOk();
        // form element and attributes
        $this->assertResponseContains('<form');
        $this->assertResponseContains('action="/articles/edit/d8c3ba90-c418-4e58-8cb6-b65c9095a2dc"');
        $this->assertResponseContains('data-panels-url="/api/articles/panels"');
        // submit button
        $this->assertResponseContains('type="submit"');
        // input element(s) and attributes
        $this->assertResponseContains('Name');
        $this->assertResponseContains('name="Articles[name]"');
        $this->assertResponseContains('value="Foo"');
    }
}
