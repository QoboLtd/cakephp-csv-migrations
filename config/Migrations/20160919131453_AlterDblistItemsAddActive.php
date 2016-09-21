<?php
use Migrations\AbstractMigration;

class AlterDblistItemsAddActive extends AbstractMigration
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
        $table->addColumn('active', 'boolean', [
                'after' => 'value',
                'default' => true,
                'null' => false,
            ]);
        $table->update();
    }
}
