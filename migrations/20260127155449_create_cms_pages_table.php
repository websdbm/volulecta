<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateCmsPagesTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('cms_pages');
        $table->addColumn('slug', 'string', ['limit' => 255])
              ->addColumn('title', 'string', ['limit' => 255])
              ->addColumn('content_markdown', 'text', ['limit' => Phinx\Db\Adapter\MysqlAdapter::TEXT_LONG])
              ->addColumn('seo_title', 'string', ['limit' => 255, 'null' => true])
              ->addColumn('seo_description', 'string', ['limit' => 255, 'null' => true])
              ->addColumn('status', 'enum', ['values' => ['draft', 'published'], 'default' => 'draft'])
              ->addColumn('published_at', 'datetime', ['null' => true])
              ->addTimestamps()
              ->addIndex(['slug'], ['unique' => true])
              ->create();
    }
}

