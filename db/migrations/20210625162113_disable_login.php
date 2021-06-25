<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class DisableLogin extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        $table = $this->table('Users');
        // https://github.com/cakephp/phinx/issues/764#issuecomment-304271946
        $table->addColumn('User_Login_Disabled', 'boolean', ['null'=>true])
             ->update();
    }
}
