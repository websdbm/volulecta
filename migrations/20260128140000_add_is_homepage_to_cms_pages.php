<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddIsHomepageToCmsPages extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('cms_pages');
        $table->addColumn('is_homepage', 'boolean', ['default' => false, 'after' => 'status'])
              ->update();
    }
}
