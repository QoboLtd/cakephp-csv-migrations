<?php
namespace CsvMigrations\Model\Behavior;

use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\ORM\Behavior;

class FootprintBehavior extends Behavior
{
    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'created_by' => 'created_by',
        'modified_by' => 'modified_by'
    ];

    /**
     * Initialize method.
     *
     * @param array $config Behavior configuration
     * @return void
     */
    public function initialize(array $config)
    {
        $this->setConfig($config);
    }

    /**
     * BeforeSave callback method.
     *
     * @param \Cake\Event\Event $event Event object
     * @param \Cake\Datasource\EntityInterface $entity Entity instance
     * @param \ArrayObject $options Query options
     * @return void
     */
    public function beforeSave(Event $event, EntityInterface $entity, ArrayObject $options)
    {
        if (! method_exists($this->getTable(), 'getCurrentUser')) {
            return;
        }

        if (! is_callable([$this->getTable(), 'getCurrentUser'])) {
            return;
        }

        $user = $this->getTable()->getCurrentUser();
        if (empty($user['id'])) {
            return;
        }

        $entity->set($this->getConfig('modified_by'), $user['id']);
        if ($entity->isNew()) {
            $entity->set($this->getConfig('created_by'), $user['id']);
        }
    }
}
