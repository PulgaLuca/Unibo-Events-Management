<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateEventRequiredSkillTable extends AbstractMigration
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
        $this->table('event_required_skill', [
            'id' => false,
            'primary_key' => ['event_id', 'skill_id'],
            'engine' => 'InnoDB',
        ])
            ->addColumn('event_id', 'char', [
                'limit' => 36,
                'null' => false,
            ])
            ->addColumn('skill_id', 'integer', [
                'limit' => 36,
                'null' => false,
                'signed' => false
            ])
            ->addForeignKey('event_id', 'event', 'id', [
                'delete' => 'CASCADE',
            ])
            ->addForeignKey('skill_id', 'skills', 'id', [
                'delete' => 'RESTRICT',
            ])
            ->create();
    }
}
