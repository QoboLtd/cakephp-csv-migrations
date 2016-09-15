<?php
use Migrations\AbstractMigration;

class AlterDblistItems extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     * @return void
     */
    public function change()
    {
        $table = $this->table('dblist_items');
        $table->addIndex([
            'dblist_id', 'name', 'value'
        ], [
            'name' => 'UNIQUE_INDEX',
            'unique' => true,
        ]);
        $table->update();
    }
}
