<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UpdateCmsPagesTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('cms_pages');
        $table->addColumn('template', 'string', ['limit' => 50, 'default' => 'interna', 'after' => 'title'])
              ->addColumn('blocks_json', 'text', ['limit' => Phinx\Db\Adapter\MysqlAdapter::TEXT_LONG, 'null' => true, 'after' => 'template'])
              ->update();
    }
}
