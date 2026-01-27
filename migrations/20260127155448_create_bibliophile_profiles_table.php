<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateBibliophileProfilesTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('bibliophile_profiles', ['id' => false, 'primary_key' => ['user_id']]);
        $table->addColumn('user_id', 'integer', ['signed' => false])
              ->addColumn('display_name', 'string', ['limit' => 255])
              ->addColumn('bio', 'text', ['null' => true])
              ->addColumn('capacity_max', 'integer', ['default' => 10])
              ->addColumn('is_active', 'boolean', ['default' => true])
              ->addColumn('last_assigned_at', 'datetime', ['null' => true])
              ->addTimestamps()
              ->addForeignKey('user_id', 'users', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
              ->create();
    }
}

