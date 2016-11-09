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
}