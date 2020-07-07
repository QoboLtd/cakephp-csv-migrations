<?php

namespace CsvMigrations\Test\TestCase\Event;

use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;
use CsvMigrations\Event\Model\ModelAfterSaveListener;
use CsvMigrations\Test\App\Model\Table\ArticlesTable;
use CsvMigrations\Test\App\Model\Table\LeadsTable;
use CsvMigrations\Test\App\Model\Table\UsersTable;

class ModelAfterSaveListenerTest extends IntegrationTestCase
{
    public $Articles;
    public $Leads;
    public $Users;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.CsvMigrations.Articles',
        'plugin.CsvMigrations.Leads',
        'plugin.CsvMigrations.Users',
    ];

    public function setUp(): void
    {
        parent::setUp();
        // Setup Articles table
        $config = TableRegistry::getTableLocator()->exists('Articles') ? [] : ['classname' => ArticlesTable::class];
        $this->Articles = TableRegistry::getTableLocator()->get('Articles', $config);
        // Setup Leads table
        $config = TableRegistry::getTableLocator()->exists('Leads') ? [] : ['classname' => LeadsTable::class];
        $this->Leads = TableRegistry::getTableLocator()->get('Leads', $config);
        // Setup Users table
        $config = TableRegistry::getTableLocator()->exists('Users') ? [] : ['classname' => UsersTable::class];
        $this->Users = TableRegistry::getTableLocator()->get('Users', $config);
    }

    public function tearDown(): void
    {
        unset($this->Articles);
        unset($this->Users);
        unset($this->Leads);
        parent::tearDown();
    }

    public function testSendCalendarReminderNonTable(): void
    {
        $event = new Event('CsvMigrations.Model.afterSave', $this);
        $entity = new Entity();
        $listener = new ModelAfterSaveListener();
        $result = $listener->sendCalendarReminder($event, $entity);
        $this->assertTrue(is_array($result), "sendCalendarReminder() returned a non-array result");
        $this->assertTrue(empty($result), "sendCalendarReminder() returned a non-empty result");
    }

    public function testSendCalendarReminderNonCsvTable(): void
    {
        $event = new Event('CsvMigrations.Model.afterSave', $this->Users);
        $entity = $this->Users->find('all')->firstOrFail();
        $listener = new ModelAfterSaveListener();
        $result = $listener->sendCalendarReminder($event, $entity);
        $this->assertTrue(is_array($result), "sendCalendarReminder() returned a non-array result");
        $this->assertTrue(empty($result), "sendCalendarReminder() returned a non-empty result");
    }

    public function testSendCalendarReminderTableNoConfig(): void
    {
        $event = new Event('CsvMigrations.Model.afterSave', $this->Articles);
        $entity = $this->Articles->find('all')->firstOrFail();
        $listener = new ModelAfterSaveListener();
        $result = $listener->sendCalendarReminder($event, $entity);
        $this->assertTrue(is_array($result), "sendCalendarReminder() returned a non-array result");
        $this->assertTrue(empty($result), "sendCalendarReminder() returned a non-empty result");
    }

    public function testSendCalendarReminderGoodAttempt(): void
    {
        // FIXME : Figure out why this is not loaded from configuration
        $this->Leads->belongsTo('Users', [
            'className' => 'Users',
            'foreignKey' => 'assigned_to',
        ]);
        $event = new Event('CsvMigrations.Model.afterSave', $this->Leads);

        // Use Leads entity which is assigned to a user who has an email address
        $entity = $this->Leads->get('00000000-0000-0000-0000-000000000001', [
            'contain' => ['Users'],
        ]);
        // Check entity before we rely on it for the rest of the testing
        $expected = 'user1@example.com';
        $this->assertEquals('00000000-0000-0000-0000-000000000001', $entity->assigned_to, "Lead without assigned_to user makes no sense for this test");
        $this->assertEquals($expected, $entity->user->email, "Unexpected email address in assigned user record");
        // Re-fetch Lead entity without contained User (minimal data)
        $entity = $this->Leads->get('00000000-0000-0000-0000-000000000001');

        // Emulate modified entity after saving
        $entity = $this->Leads->patchEntity($entity, [
            'follow_up_date' => date('Y-m-d H:i:s', time()),
        ]);

        $listener = new ModelAfterSaveListener();
        $result = $listener->sendCalendarReminder($event, $entity);
        $this->assertTrue(is_array($result), "sendCalendarReminder() returned a non-array result");
        $this->assertFalse(empty($result), "sendCalendarReminder() returned an empty result");
        $this->assertTrue(array_key_exists($expected, $result), "sendCalendarReminder() did not try to email [$expected]");
    }
}
