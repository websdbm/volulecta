<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreatePasswordResetsTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('password_resets');
        $table->addColumn('user_id', 'integer', ['signed' => false])
              ->addColumn('token', 'string', ['limit' => 255])
              ->addColumn('expires_at', 'datetime')
              ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
              ->addForeignKey('user_id', 'users', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
              ->addIndex(['token'], ['unique' => true])
              ->create();
    }
}

