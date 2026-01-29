<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddApiKeyTypes extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('api_keys');
        
        // Aggiungi colonna per il tipo di chiave
        $table->addColumn('key_type', 'string', [
            'limit' => 50,
            'default' => 'single',
            'comment' => 'single: una sola chiave, pair: coppie di chiavi (es. Amazon)'
        ]);
        
        // Aggiungi colonna per la seconda chiave (per chiavi di tipo pair)
        $table->addColumn('key_value_secondary', 'text', [
            'null' => true,
            'comment' => 'Seconda chiave per servizi che richiedono coppie (es. Amazon Secret Access Key)'
        ]);
        
        // Aggiungi indice sul tipo di chiave
        $table->addIndex(['key_type']);
        
        $table->update();
    }
}
