<%
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         3.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
$tableMethod = $this->Migration->tableMethod($action);
%>
<?php
use CsvMigrations\CsvMigration;

class <%= $name %> extends CsvMigration
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
<% if ('create' === $tableMethod): %>
        if (!$this->hasTable('<%= $table%>')) {
            $table = $this->table('<%= $table%>');
            $table = $this->csv($table);
            $table-><%= $tableMethod %>();
        }
<% else: %>
        $table = $this->table('<%= $table%>');
        $table = $this->csv($table);
        $table-><%= $tableMethod %>();
<% endif; %>

        $joinedTables = $this->joins('<%= $table%>');
        if (!empty($joinedTables)) {
            foreach ($joinedTables as $joinedTable) {
                $joinedTable->create();
            }
        }
    }
}
