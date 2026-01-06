<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateTeamTable extends AbstractMigration
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
        $this->table('team', [
            'id' => false,
            'primary_key' => 'id',
            'engine' => 'InnoDB',
        ])
            ->addColumn('id', 'char', [
                'limit' => 36,
                'null' => false,
            ])
            ->addColumn('name', 'string', ['limit' => 255])
            ->addColumn('description', 'text', ['null' => true])
            ->addColumn('status', 'enum', [
                'values' => ['Searching', 'Full', 'Inactive'],
            ])
            ->addColumn('max_participants', 'smallinteger')
            ->addColumn('min_participants', 'smallinteger', ['default' => 1])
            ->addColumn('mentor_id', 'integer', [
                'null' => true,
                'signed' => false
            ])
            ->addColumn('created_at', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
            ])
            ->addForeignKey('mentor_id', 'users', 'id', [
                'delete' => 'SET_NULL',
            ])
            ->create();
    }
}
