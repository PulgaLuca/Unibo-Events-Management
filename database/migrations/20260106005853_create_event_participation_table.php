<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateEventParticipationTable extends AbstractMigration
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
        $this->table('event_participation', [
            'id' => false,
            'primary_key' => 'id',
            'engine' => 'InnoDB',
        ])
            ->addColumn('id', 'char', [
                'limit' => 36,
                'null' => false,
            ])
            ->addColumn('event_id', 'char', ['limit' => 36])
            ->addColumn('user_id', 'integer', [
                'null' => true,
                'signed' => false
            ])
            ->addColumn('team_id', 'char', [
                'limit' => 36,
                'null' => true,
            ])
            ->addColumn('role', 'enum', [
                'values' => ['Participant', 'Lead', 'Referee'],
            ])
            ->addColumn('registration_date', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
            ])
            ->addIndex(['event_id', 'user_id'], ['unique' => true])
            ->addIndex(['event_id', 'team_id'], ['unique' => true])
            ->addForeignKey('event_id', 'event', 'id', [
                'delete' => 'CASCADE',
            ])
            ->addForeignKey('user_id', 'users', 'id', [
                'delete' => 'CASCADE',
            ])
            ->addForeignKey('team_id', 'team', 'id', [
                'delete' => 'CASCADE',
            ])
            ->create();
    }
}
