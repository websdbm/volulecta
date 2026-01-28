# CMS Builder - Miglioramenti Implementati

## 🎯 Riassunto Modifiche

Ho implementato le osservazioni richieste sulla gestione del builder CMS. Ecco cosa è stato fatto:

### ✅ 1. Database & Infrastruttura

**Nuova migration:** `20260128140000_add_is_homepage_to_cms_pages.php`
- Campo `is_homepage` (boolean, default: false)
- Consente di marcare una pagina come homepage (radice `/`)

**Entity aggiornata:** `CmsPage.php`
- Aggiunto parametro `isHomepage: bool`
- Nuovo getter: `isHomepage(): bool`

**Repository aggiornato:** `PdoCmsRepository.php`
- INSERT e UPDATE includono `is_homepage`
- MapToEntity gestisce il nuovo campo

### ✅ 2. Google Fonts

**Utility class:** `src/Application/Utils/GoogleFonts.php`
- 4 font Google Fonts pre-configurati:
  1. **Roboto** (sans-serif) - pesi: 300, 400, 500, 700
  2. **Poppins** (sans-serif) - pesi: 300, 400, 500, 700
  3. **Inter** (sans-serif) - pesi: 300, 400, 500, 700
  4. **Playfair Display** (serif) - pesi: 400, 700

Uso nel template:
```php
use App\Application\Utils\GoogleFonts;

$fonts = GoogleFonts::getAvailableFonts();
// Output: ['roboto' => 'Roboto', 'poppins' => 'Poppins', ...]
```

### ✅ 3. Nuove Azioni Admin

#### PublishPageAction
**File:** `src/Application/Actions/Admin/Cms/PublishPageAction.php`
**Route:** `POST /admin/cms/{id}/publish`

Trasforma una pagina da draft a published:
- Status: `draft` → `published`
- published_at: impostato a NOW()
- Updatable anche se già pubblicata (può tornare a draft)

**Risposta JSON:**
```json
{
  "status": "success",
  "message": "Pagina pubblicata con successo"
}
```

#### SetHomepageAction
**File:** `src/Application/Actions/Admin/Cms/SetHomepageAction.php`
**Route:** `POST /admin/cms/{id}/set-homepage`

Imposta una pagina come homepage:
- Deseleziona tutte le altre: `UPDATE cms_pages SET is_homepage = 0`
- Seleziona questa: `UPDATE cms_pages SET is_homepage = 1 WHERE id = ?`
- Solo una pagina può essere homepage alla volta

**Risposta JSON:**
```json
{
  "status": "success",
  "message": "Homepage impostata con successo"
}
```

### ✅ 4. Routes Aggiunte

```php
// In POST /admin/cms group
POST /admin/cms/{id}/publish        // PublishPageAction
POST /admin/cms/{id}/set-homepage   // SetHomepageAction
```

### ✅ 5. Schema JSON Blocchi (Nuovo Formato)

Tutti i blocchi ora supporteranno:

```json
{
  "type": "title",
  "content": {
    "text": "Titolo pagina"
  },
  "settings": {
    "desktop": {
      "font_family": "roboto",      // NEW: roboto|poppins|inter|playfair
      "font_size": "32px",          // NEW: 12px-72px
      "font_weight": "700",         // NEW: 300|400|500|700 (dipendente font)
      "letter_spacing": "0px",      // NEW: 0px-10px
      "word_spacing": "0px",        // NEW: 0px-5px
      "color": "#333333",           // EXISTING
      "padding": "10px",            // EXISTING
      "align": "left"               // EXISTING
    },
    "tablet": {},
    "mobile": {}
  }
}
```

## 📝 Cosa Manca (Prossima Implementazione)

### Nel Builder UI (builder.twig)

1. **Sidebar - Checkbox "Imposta come Homepage"**
   ```html
   <div class="form-group">
     <label>
       <input type="checkbox" id="set-homepage" />
       Imposta come homepage
     </label>
   </div>
   ```

2. **Sidebar - Bottone "Pubblica"**
   ```html
   <button id="publish-btn" class="btn-publish">
     📤 Pubblica Pagina
   </button>
   ```

3. **Settings Panel - Font Controls (per blocchi Titolo/Testo)**
   - Select: Font Family (roboto, poppins, inter, playfair)
   - Input: Font Size (12-72px) + Slider
   - Select: Font Weight (dipendente dal font scelto)
   - Input: Letter Spacing (0-10px)
   - Input: Word Spacing (0-5px)
   - Color picker (già presente, con migliore UX)

4. **Permessi di Modifica**
   - ✅ Template sempre modificabile (rimosso vincolo)
   - ✅ Status sempre modificabile (draft ↔ published)
   - ✅ Blocchi sempre modificabili

## 🧪 Testing

### Test Publish Action
```bash
# Pubblica una pagina (ID 1)
curl -X POST http://localhost:8080/admin/cms/1/publish \
  -b /tmp/cookies.txt \
  -H "Content-Type: application/json"

# Response: {"status": "success", "message": "Pagina pubblicata con successo"}
```

### Test Set Homepage
```bash
# Imposta pagina ID 2 come homepage
curl -X POST http://localhost:8080/admin/cms/2/set-homepage \
  -b /tmp/cookies.txt \
  -H "Content-Type: application/json"

# Response: {"status": "success", "message": "Homepage impostata con successo"}
```

### Verifica Database
```bash
# Verificare homepage
docker-compose exec -T db mysql -u volulecta -pvolulecta volulecta \
  -e "SELECT id, slug, status, is_homepage FROM cms_pages;"
```

## 📚 File Modificati

| File | Stato | Descrizione |
|------|-------|-------------|
| `src/Domain/Entities/CmsPage.php` | ✅ DONE | Aggiunto `isHomepage` |
| `src/Infrastructure/Persistence/PdoCmsRepository.php` | ✅ DONE | CRUD per `is_homepage` |
| `migrations/20260128140000_*` | ✅ DONE | Migration aggiunta |
| `src/Application/Utils/GoogleFonts.php` | ✅ DONE | 4 Google Fonts |
| `src/Application/Actions/Admin/Cms/PublishPageAction.php` | ✅ DONE | Pubblica pagina |
| `src/Application/Actions/Admin/Cms/SetHomepageAction.php` | ✅ DONE | Imposta homepage |
| `config/routes.php` | ✅ DONE | Route aggiunte |
| `src/Views/admin/cms/builder.twig` | ⏳ IN PROGRESS | UI da aggiornare |
| `BUILDER_IMPROVEMENTS.md` | ✅ DONE | Roadmap |

## 🚀 Prossimi Passi

1. Aggiornare `builder.twig` con i nuovi controlli font
2. Testare l'UI con i 4 font disponibili
3. Implementare cascata responsiva (Desktop → Tablet → Mobile)
4. Aggiornare SaveBuilderAction per validare font_weight in base a font_family selezionato

---

**Status:** Infrastruttura backend ✅ | UI Frontend ⏳
