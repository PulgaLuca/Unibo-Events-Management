<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateLocationTableAndLinkToEvent extends AbstractMigration
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
        // Location table
        $this->table('location', [
            'id' => false,
            'primary_key' => 'id',
            'engine' => 'InnoDB',
        ])
            ->addColumn('id', 'char', [
                'limit' => 36,
                'null' => false,
            ])
            ->addColumn('country', 'string', [
                'limit' => 255,
            ])
            ->addColumn('city', 'string', [
                'limit' => 255,
            ])
            ->addColumn('description', 'string', [
                'limit' => 255,
                'null' => true,
            ])
            ->create();

        // Updated with location_id
        $eventTable = $this->table('event');

        if ($eventTable->hasColumn('location')) {
            $eventTable->removeColumn('location');
        }

        $eventTable
            ->addColumn('location_id', 'char', [
                'limit' => 36,
                'null' => true,
                'after' => 'image_url',
            ])
            ->addForeignKey(
                'location_id',
                'location',
                'id',
                [
                    'delete' => 'SET_NULL',
                    'update' => 'CASCADE',
                ]
            )
            ->update();
    }
}
