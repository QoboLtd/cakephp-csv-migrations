<?php
namespace App\Model\Table;

class <%= $name %>Table extends AppTable
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('<%= $table %>');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');
    }
}
