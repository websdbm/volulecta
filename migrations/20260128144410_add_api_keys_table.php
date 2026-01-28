<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddApiKeysTable extends AbstractMigration
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
        $table = $this->table('api_keys');
        $table->addColumn('key_name', 'string', ['limit' => 100])
              ->addColumn('key_label', 'string', ['limit' => 255])
              ->addColumn('key_value', 'text')
              ->addColumn('description', 'text', ['null' => true])
              ->addColumn('is_active', 'boolean', ['default' => true])
              ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
              ->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
              ->addIndex(['key_name'], ['unique' => true])
              ->create();
    }
}
