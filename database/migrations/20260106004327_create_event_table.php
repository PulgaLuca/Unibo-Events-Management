<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateEventTable extends AbstractMigration
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
        $this->table('event', [
            'id' => false,
            'primary_key' => 'id',
            'engine' => 'InnoDB',
        ])
            ->addColumn('id', 'char', [
                'limit' => 36,
                'null' => false,
            ])
            ->addColumn('title', 'string', ['limit' => 255])
            ->addColumn('description', 'text', ['null' => true])
            ->addColumn('start_date', 'datetime')
            ->addColumn('end_date', 'datetime', ['null' => true])
            ->addColumn('image_url', 'text', ['null' => true])
            ->addColumn('location', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('url', 'string', ['limit' => 512, 'null' => true])
            ->addColumn('registration_deadline', 'datetime', ['null' => true])
            ->addColumn('min_participants', 'integer', ['default' => 0])
            ->addColumn('max_participants', 'integer', ['null' => true])
            ->addColumn('status', 'enum', [
                'values' => ['Active', 'Completed', 'Cancelled', 'Draft'],
            ])
            ->addColumn('type_id', 'char', ['limit' => 36])
            ->addColumn('participation_type_id', 'char', ['limit' => 36])
            ->addColumn('creator_user_id', 'integer', [
                'null' => true,
                'signed' => false
            ])
            ->addForeignKey('type_id', 'event_type', 'id', [
                'delete' => 'RESTRICT',
            ])
            ->addForeignKey('participation_type_id', 'participation_type', 'id', [
                'delete' => 'RESTRICT',
            ])
            ->addForeignKey('creator_user_id', 'users', 'id', [
                'delete' => 'SET_NULL',
            ])
            ->create();
    }
}
