<?php
use Migrations\AbstractMigration;

class AlterDblistItemsTreeBehavior extends AbstractMigration
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
        $table
            ->addColumn('parent_id', 'uuid', [
                'after' => 'value',
                'default' => null,
                'null' => true,
            ])
            ->addColumn('lft', 'integer', [
                'after' => 'parent_id',
                'default' => null,
                'null' => false,
            ])
            ->addColumn('rght', 'integer', [
                'after' => 'lft',
                'default' => null,
                'null' => false,
            ]);
        $table->update();
    }
}
