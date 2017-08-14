<?php
namespace CsvMigrations\Test\TestCase\Event;

use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;
use CsvMigrations\Event\Model\ModelAfterSaveListener;
use CsvMigrations\Model\Table\ArticlesTable;
use CsvMigrations\Model\Table\LeadsTable;
use CsvMigrations\Model\Table\UsersTable;

class ModelAfterSaveListenerTest extends IntegrationTestCase
{
    /**
     * Test subject
     *
     * @var \CsvMigrations\Model\Table\ArticlesTable
     */
    public $Articles;

    /**
     * Test subject
     *
     * @var \CsvMigrations\Model\Table\LeadsTable
     */
    public $Leads;

    /**
     * Test subject
     *
     * @var \CsvMigrations\Model\Table\UsersTable
     */
    public $Users;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.csv_migrations.articles',
        'plugin.csv_migrations.leads',
        'plugin.csv_migrations.users',
    ];

    public function setUp()
    {
        parent::setUp();
        // Setup Articles table
        $config = TableRegistry::exists('Articles') ? [] : ['classname' => ArticlesTable::class];
        $this->Articles = TableRegistry::get('Articles', $config);
        // Setup Leads table
        $config = TableRegistry::exists('Leads') ? [] : ['classname' => LeadsTable::class];
        $this->Leads = TableRegistry::get('Leads', $config);
        // Setup Users table
        $config = TableRegistry::exists('Users') ? [] : ['classname' => UsersTable::class];
        $this->Users = TableRegistry::get('Users', $config);
    }

    public function tearDown()
    {
        unset($this->Articles);
        unset($this->Users);
        unset($this->Leads);
        parent::tearDown();
    }

    public function testSendCalendarReminderNonTable()
    {
        $event = new Event('CsvMigrations.Model.afterSave', $this);
        $entity = new Entity();
        $listener = new ModelAfterSaveListener();
        $result = $listener->sendCalendarReminder($event, $entity);
        $this->assertTrue(is_array($result), "sendCalendarReminder() returned a non-array result");
        $this->assertTrue(empty($result), "sendCalendarReminder() returned a non-empty result");
    }

    public function testSendCalendarReminderNonCsvTable()
    {
        $event = new Event('CsvMigrations.Model.afterSave', $this->Users);
        $entity = $this->Users->find('all')->first();
        $listener = new ModelAfterSaveListener();
        $result = $listener->sendCalendarReminder($event, $entity);
        $this->assertTrue(is_array($result), "sendCalendarReminder() returned a non-array result");
        $this->assertTrue(empty($result), "sendCalendarReminder() returned a non-empty result");
    }

    public function testSendCalendarReminderTableNoConfig()
    {
        $event = new Event('CsvMigrations.Model.afterSave', $this->Articles);
        $entity = $this->Articles->find('all')->first();
        $listener = new ModelAfterSaveListener();
        $result = $listener->sendCalendarReminder($event, $entity);
        $this->assertTrue(is_array($result), "sendCalendarReminder() returned a non-array result");
        $this->assertTrue(empty($result), "sendCalendarReminder() returned a non-empty result");
    }

    public function testSendCalendarReminderGoodAttempt()
    {
        // FIXME : Figure out why this is not loaded from configuration
        $this->Leads->belongsTo('Users', [
            'className' => 'Users',
            'foreignKey' => 'assigned_to',
        ]);
        $event = new Event('CsvMigrations.Model.afterSave', $this->Leads);

        // Use Leads entity which is assigned to a user who has an email address
        $entity = $this->Leads->get('00000000-0000-0000-0000-000000000001', [
            'contain' => ['Users']
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
