<?php

/**
 * Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

namespace CsvMigrations\Test\TestCase\Event\Model;

use Cake\Event\EventManager;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CsvMigrations\Event\Model\AutoIncrementEventListener;

class AutoIncrementEventListenerTest extends TestCase
{
    public $fixtures = ['plugin.CsvMigrations.Foo'];

    private $table;
    private $autoincrementField = 'reference';
    private $data = ['name' => 'auto-increment test', 'status' => 'active', 'type' => 'bronze.used'];

    public function setUp(): void
    {
        parent::setUp();

        $this->table = TableRegistry::getTableLocator()->get('Foo');

        EventManager::instance()->on(new AutoIncrementEventListener());
    }

    public function tearDown(): void
    {
        unset($this->data);
        unset($this->autoincrementField);
        unset($this->table);

        parent::tearDown();
    }

    public function testAutoIncrementWithNewEntities(): void
    {
        $firstEntity = $this->table->newEntity($this->data);

        $this->table->saveOrFail($firstEntity);
        $this->assertSame(10, $firstEntity->get($this->autoincrementField));

        $secondEntity = $this->table->newEntity($this->data);
        $this->table->saveOrFail($secondEntity);

        $this->assertSame(11.0, $secondEntity->get($this->autoincrementField));
    }

    public function testAutoIncrementWithExistingEntity(): void
    {
        $newEntity = $this->table->newEntity($this->data);
        $this->table->saveOrFail($newEntity);

        $this->assertSame(10, $newEntity->get($this->autoincrementField));

        $existingEntity = $this->table->get($newEntity->get('id'));
        $this->table->patchEntity($existingEntity, ['status' => 'inactive']);
        $this->table->saveOrFail($existingEntity);
        $this->assertSame(10, $existingEntity->get($this->autoincrementField));
    }

    public function testAutoIncrementWithExistingPartialEntity(): void
    {
        $newEntity = $this->table->newEntity($this->data);
        $this->table->saveOrFail($newEntity);

        $this->assertSame(10, $newEntity->get($this->autoincrementField));

        $existingEntity = $this->table->get($newEntity->get('id'), ['fields' => ['id', 'name']]);
        $this->table->patchEntity($existingEntity, ['status' => 'inactive']);
        $this->table->saveOrFail($existingEntity);

        $existingEntity = $this->table->get($existingEntity->get('id'));
        $this->assertSame(10, $existingEntity->get($this->autoincrementField));
    }
}
