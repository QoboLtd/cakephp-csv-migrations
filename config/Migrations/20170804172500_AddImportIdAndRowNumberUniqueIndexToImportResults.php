<?php
use Migrations\AbstractMigration;

class AddImportIdAndRowNumberUniqueIndexToImportResults extends AbstractMigration
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
        $table = $this->table('import_results');
        $table->addIndex(['import_id', 'row_number'], ['unique' => true]);
        $table->update();
    }
}
