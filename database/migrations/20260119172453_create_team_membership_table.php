<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateTeamMembershipTable extends AbstractMigration
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
        $table = $this->table('team_membership', ['id' => false, 'primary_key' => ['team_id', 'user_id']]);

        $table->addColumn('team_id', 'char', ['limit' => 36, 'null' => false])
            ->addColumn('user_id', 'integer', ['null' => false, 'signed' => false]) 
            ->addColumn('status', 'enum', ['values' => ['Lead', 'Member', 'Pending'], 'null' => false])
            ->addColumn('joined_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
            
            ->addForeignKey('team_id', 'team', 'id', ['delete'=> 'CASCADE', 'update' => 'NO_ACTION'])
            ->addForeignKey('user_id', 'users', 'id', ['delete'=> 'CASCADE', 'update' => 'NO_ACTION'])
            
            ->create();
    }
}
