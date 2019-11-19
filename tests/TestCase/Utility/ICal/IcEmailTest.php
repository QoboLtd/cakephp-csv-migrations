<?php

namespace CsvMigrations\Test\TestCase\Utility\ICal;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CsvMigrations\Utility\ICal\IcEmail;

class IcEmailTest extends TestCase
{
    public $fixtures = ['plugin.CsvMigrations.articles'];

    private $table;
    private $entity;

    public function setUp(): void
    {
        $this->table = TableRegistry::getTableLocator()->get('Articles');
        $this->entity = $this->table->newEntity(['name' => 'Hello World!', 'status' => 'draft']);
        $this->table->saveOrFail($this->entity);
        $this->table->patchEntity($this->entity, ['status' => 'published']);
    }

    public function tearDown(): void
    {
        unset($this->entity);
        unset($this->table);
    }

    public function testGetEmailSubject(): void
    {
        $this->assertSame(
            '(Updated) Article: Hello World!',
            (new IcEmail($this->table, $this->entity))->getEmailSubject()
        );
    }

    public function testGetEmailSubjectWithNewEntity(): void
    {
        $this->assertSame(
            'Article: Foobar',
            (new IcEmail($this->table, $this->table->newEntity(['name' => 'Foobar'])))->getEmailSubject()
        );
    }

    public function testGetEventSubject(): void
    {
        $icEmail = new IcEmail($this->table, $this->entity);

        $this->assertSame(
            $icEmail->getEmailSubject(),
            $icEmail->getEventSubject()
        );
    }

    public function testGetEventSubjectWithNewEntity(): void
    {
        $icEmail = new IcEmail($this->table, $this->table->newEntity(['name' => 'Foobar']));

        $this->assertSame(
            $icEmail->getEmailSubject(),
            $icEmail->getEventSubject()
        );
    }

    public function testGetEntityUrl(): void
    {
        $this->assertSame(
            '/articles/view/' . $this->entity->get('id'),
            (new IcEmail($this->table, $this->entity))->getEntityUrl()
        );
    }

    public function testGetEntityUrlWithNewEntity(): void
    {
        $this->assertSame(
            '/articles/view',
            (new IcEmail($this->table, $this->table->newEntity(['name' => 'Foobar'])))->getEntityUrl()
        );
    }

    public function testGetEmailContent(): void
    {
        $this->assertSame(
            "Article \"Hello World!\" updated by System\n\n* Status: changed from \"draft\" to \"published\".\n\n\n\nSee more: /articles/view/" . $this->entity->get('id'),
            (new IcEmail($this->table, $this->entity))->getEmailContent()
        );
    }

    public function testGetEmailContentWithNewEntity(): void
    {
        $this->assertSame(
            "Article \"Foobar\" created by System\n\nSee more: /articles/view",
            (new IcEmail($this->table, $this->table->newEntity(['name' => 'Foobar'])))->getEmailContent()
        );
    }

    public function testGetEventContent(): void
    {
        $this->assertSame(
            "\n\nSee more: /articles/view/" . $this->entity->get('id'),
            (new IcEmail($this->table, $this->entity))->getEventContent()
        );
    }

    public function testSendCalendarEmail(): void
    {
        $icEmail = new IcEmail($this->table, $this->entity);
        $subject = $icEmail->getEmailSubject();
        $content = $icEmail->getEmailContent();
        $sent = $icEmail->sendCalendarEmail('foo@bar.com', $subject, $content, []);

        $pattern = '/Content-Disposition: attachment; filename="event.ics"\\r\\nContent-Type: text\/calendar\\r\\nContent-Transfer-Encoding: base64\\r/';

        $this->assertRegExp($pattern, $sent['message']);
    }
}
