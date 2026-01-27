<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateUsersTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('users');
        $table->addColumn('email', 'string', ['limit' => 255])
              ->addColumn('password_hash', 'string', ['limit' => 255])
              ->addColumn('role', 'enum', ['values' => ['admin', 'bibliophile', 'user'], 'default' => 'user'])
              ->addColumn('status', 'enum', ['values' => ['active', 'suspended'], 'default' => 'active'])
              ->addColumn('waiting_list', 'integer', ['limit' => Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'default' => 0])
              ->addColumn('email_verified_at', 'datetime', ['null' => true])
              ->addTimestamps()
              ->addIndex(['email'], ['unique' => true])
              ->create();
    }
}

