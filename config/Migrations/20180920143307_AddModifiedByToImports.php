<?php
use Migrations\AbstractMigration;

class AddModifiedByToImports extends AbstractMigration
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
        $table = $this->table('imports');
        $table->addColumn('modified_by', 'uuid', [
            'default' => null,
            'null' => true,
        ]);
        $table->update();
    }
}
