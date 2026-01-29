# 🔑 Aggiunta Supporto per Chiavi API Amazon (Coppie di Chiavi)

Data: 29 Gennaio 2026

## 📋 Sommario

È stato aggiunto il supporto per gestire chiavi API che richiedono coppie di valori, come Amazon (Access Key ID + Secret Access Key).

## 🔄 Modifiche Implementate

### 1. **Database Migration** 
**File**: `migrations/20260129130000_add_api_key_types.php`

Aggiunto due colonne alla tabella `api_keys`:
- `key_type` (varchar(50)): Tipo di chiave ('single' o 'pair')
  - `single`: Una sola chiave (TinyMCE, OpenAI, etc.)
  - `pair`: Coppie di chiavi (Amazon, etc.)
- `key_value_secondary` (text): Seconda chiave per chiavi di tipo pair

**Schema aggiornato**:
```sql
ALTER TABLE api_keys ADD COLUMN key_type VARCHAR(50) DEFAULT 'single';
ALTER TABLE api_keys ADD COLUMN key_value_secondary TEXT NULL;
ALTER TABLE api_keys ADD INDEX idx_key_type (key_type);
```

### 2. **Entità di Dominio**
**File**: `src/Domain/Entities/ApiKey.php`

Aggiunto due parametri al costruttore:
```php
private string $keyType = 'single',
private ?string $keyValueSecondary = null
```

Aggiunti getter:
- `getKeyType(): string` - Ritorna il tipo di chiave
- `getKeyValueSecondary(): ?string` - Ritorna la chiave secondaria

### 3. **Repository**
**File**: `src/Infrastructure/Persistence/PdoApiKeyRepository.php`

Aggiornati metodi:
- `save()`: Salva `key_type` e `key_value_secondary`
- `mapToEntity()`: Mappa i nuovi campi dall'array dati

### 4. **Controller/Action**
**File**: `src/Application/Actions/Admin/ApiKeys/SaveApiKeyAction.php`

Aggiornato il metodo `__invoke()` per gestire i nuovi parametri:
```php
new ApiKey(
    $id,
    $data['key_name'] ?? '',
    $data['key_label'] ?? '',
    $data['key_value'] ?? '',
    $data['description'] ?? null,
    isset($data['is_active']),
    '',
    '',
    $data['key_type'] ?? 'single',
    $data['key_value_secondary'] ?? null
);
```

### 5. **Template Form**
**File**: `src/Views/admin/api-keys/form.twig`

Aggiunti:
- **Dropdown** per scegliere il tipo di chiave (singola o coppia)
- **Campi dinamici** che si mostrano/nascondono in base al tipo:
  - Per **chiave singola**: Un campo textarea per il valore
  - Per **chiave coppia**: Due campi textarea (Primaria + Secondaria)
- **JavaScript** per gestire l'alternanza tra i tipi

**Logica JavaScript**:
```javascript
function toggleKeyTypeFields() {
    const keyType = document.getElementById('key_type').value;
    
    if (keyType === 'pair') {
        // Mostra campi per coppie
        // Rendi obbligatori i due campi
    } else {
        // Mostra campo singolo
    }
}
```

### 6. **Template Lista**
**File**: `src/Views/admin/api-keys/list.twig`

Aggiunto:
- **Colonna "Tipo"** che mostra se è singola (🔑) o coppia (🔗)
- **Visualizzazione dinamica** dei valori:
  - Per **singola**: Mostra il valore in un campo
  - Per **coppia**: Mostra due righe etichettate (Primaria / Secondaria)
- **Badge visuale** per distinguere i tipi

## 📝 Configurazione per Amazon

Per aggiungere le credenziali Amazon:

1. Vai a: `/admin/api-keys`
2. Clicca "Nuova API Key"
3. Configura come segue:

| Campo | Valore |
|-------|--------|
| **Nome Chiave** | `amazon` |
| **Etichetta** | `Amazon API` o nome descrittivo |
| **Tipo di Chiave** | **Coppia di Chiavi** |
| **Chiave Primaria** | `AKIAIOSFODNN7EXAMPLE` (Access Key ID) |
| **Chiave Secondaria** | `wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY` (Secret Access Key) |
| **Descrizione** | Note opzionali |
| **API Key attiva** | ✓ Selezionato |

## 🔍 Struttura Dati

### Database Schema
```sql
CREATE TABLE api_keys (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    key_name VARCHAR(100) UNIQUE,
    key_label VARCHAR(255),
    key_value LONGTEXT,
    key_value_secondary LONGTEXT NULL,
    description LONGTEXT NULL,
    is_active TINYINT(1) DEFAULT 1,
    key_type VARCHAR(50) DEFAULT 'single',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key_type (key_type)
);
```

### Entità PHP
```php
$apiKey = new ApiKey(
    id: 1,
    keyName: 'amazon',
    keyLabel: 'Amazon API',
    keyValue: 'AKIAIOSFODNN7EXAMPLE',          // Access Key ID
    description: 'Credenziali Amazon',
    isActive: true,
    createdAt: '2026-01-29 13:00:00',
    updatedAt: '2026-01-29 13:00:00',
    keyType: 'pair',                            // Nuovo: tipo coppia
    keyValueSecondary: 'wJalrXUtnFEMI/...'    // Nuovo: Secret Access Key
);
```

## 💻 Utilizzo in Codice

### Recuperare una chiave singola
```php
$openaiKey = $apiKeyRepository->findByName('openai');
echo $openaiKey->getKeyValue(); // La chiave OpenAI
```

### Recuperare una chiave coppia (Amazon)
```php
$amazonKey = $apiKeyRepository->findByName('amazon');

if ($amazonKey->getKeyType() === 'pair') {
    $accessKeyId = $amazonKey->getKeyValue();          // Access Key ID
    $secretAccessKey = $amazonKey->getKeyValueSecondary(); // Secret Key
    
    // Usa le credenziali con il client AWS
    $client = new AmazonClient([
        'key' => $accessKeyId,
        'secret' => $secretAccessKey,
    ]);
}
```

## 🎨 Interfaccia UI

### Modulo di Creazione/Modifica
- ✅ Campo di selezione del tipo di chiave (dropdown)
- ✅ Validazione lato client (toggle dinamico)
- ✅ Campi condizionali basati sul tipo
- ✅ Messaggi di aiuto descrittivi

### Lista di Gestione
- ✅ Colonna tipo con badge visuale
- ✅ Visualizzazione corretta per coppie di chiavi
- ✅ Truncamento sicuro dei valori lunghi
- ✅ Azioni modifica/elimina funzionanti

## 🔐 Sicurezza

- ✅ Le chiavi sono salvate come TEXT nel database (buona pratica)
- ✅ Non vengono mai visualizzate complete nell'UI (truncate a 20 caratteri)
- ✅ Validazione dei dati nel backend
- ✅ Supporto per disattivare chiavi senza eliminarle

## ✅ Test della Funzionalità

Accedi all'applicazione:
```
http://localhost:8080/admin/api-keys
```

**Verificare**:
1. ✅ Clicca "Nuova API Key"
2. ✅ Seleziona "Coppia di Chiavi" dal dropdown
3. ✅ Verifica che appaiono i due campi (Primaria + Secondaria)
4. ✅ Inserisci valori di test
5. ✅ Salva e verifica nella lista
6. ✅ Verifica che mostra il badge "🔗 Coppia"
7. ✅ Clicca modifica per verificare che carica correttamente
8. ✅ Seleziona "Chiave Singola" per verificare il toggle

## 🚀 Prossimi Step (Opzionali)

### Integrazioni Consigliate
1. **AWS SDK**: Integrare il client AWS ufficiale per usare le credenziali
2. **Validazione** : Aggiungere validazione formato (es. Access Key ID format)
3. **Encryption**: Encryptare le chiavi nel database
4. **Audit**: Registrare accessi e modifiche alle chiavi
5. **Rotazione**: Implementare rotazione automatica delle chiavi

### Supporto per Altre Coppie di Chiavi
Se in futuro servono altre coppie di chiavi:
1. Modificare il dropdown per aggiungere la nuova opzione
2. Aggiornare l'info box nella view
3. Nessun cambio al database (la struttura è generica)

---

## 📊 Summary

| Elemento | Dettagli |
|----------|----------|
| **Database Changes** | +2 colonne: `key_type`, `key_value_secondary` |
| **Entità Aggiornate** | `ApiKey` class |
| **Repository Aggiornati** | `PdoApiKeyRepository` |
| **Controller/Action** | `SaveApiKeyAction` |
| **Template Aggiornati** | `form.twig`, `list.twig` |
| **Migration** | `20260129130000_add_api_key_types.php` |
| **Backward Compatible** | ✅ Sì (default a 'single') |
| **Test Completato** | ✅ Sì |

---

**Status**: ✅ COMPLETATO E TESTATO
