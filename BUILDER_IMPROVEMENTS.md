# Miglioramenti CMS Builder - Implementazione Richieste

## Modifiche Implementate ✅

### 1. Database
- [x] Migration aggiunta: `is_homepage` (boolean)
- [x] Entity CmsPage aggiornata con `isHomepage()`
- [x] PdoCmsRepository aggiornato per gestire il nuovo campo

### 2. Google Fonts
- [x] Utility class `GoogleFonts.php` con 4 font predefiniti:
  - Roboto (sans-serif)
  - Poppins (sans-serif)
  - Inter (sans-serif)
  - Playfair Display (serif)
  - Tutti con pesi: 300, 400, 500, 700 (Playfair: 400, 700)

### 3. Struttura JSON Blocchi (nuovo schema)
```json
{
  "type": "title",
  "content": {
    "text": "Titolo pagina"
  },
  "settings": {
    "desktop": {
      "font_family": "roboto",
      "font_size": "32px",
      "font_weight": "700",
      "letter_spacing": "0px",
      "word_spacing": "0px",
      "color": "#333333",
      "padding": "10px",
      "align": "left"
    },
    "tablet": {},
    "mobile": {}
  }
}
```

## Modifiche Necessarie nell'UI Builder

### Nel Sidebar
- Aggiungere selector "Imposta come Homepage" (checkbox)
- Aggiungere bottone "Pubblica" per trasformare draft in published
- Permette modifica template anche su pagine pubblicate

### Nei Settings del Blocco (soprattutto Titolo)
**Nuovi campi:**

1. **Font Family** (select)
   - Opzioni: Roboto, Poppins, Inter, Playfair Display
   
2. **Font Size** (input + slider)
   - Range: 12px - 72px
   - Default: 16px
   
3. **Font Weight** (select)
   - Dipendente dal font selezionato
   - Roboto/Poppins/Inter: 300, 400, 500, 700
   - Playfair: 400, 700
   
4. **Letter Spacing** (input)
   - Input: 0px - 10px
   - Step: 0.1px
   
5. **Word Spacing** (input)
   - Input: 0px - 5px
   - Step: 0.1px

6. **Colore Testo** (color picker)
   - Mantenere il precedente con validazione migliore

### Flussi Modificati

1. **Creazione pagina**: template e status rimangono modificabili sempre
2. **Draft → Published**: 
   - Nuovo button "Pubblica"
   - Esegue un PATCH su `/admin/cms/{id}/publish`
   - Set status = 'published' e published_at = NOW()
3. **Homepage**:
   - Checkbox nella sidebar
   - Se attivato, deseleziona le altre homepage
   - Query al DB: `UPDATE cms_pages SET is_homepage = 0; UPDATE cms_pages SET is_homepage = 1 WHERE id = ?`

## File da Modificare

1. ✅ `src/Domain/Entities/CmsPage.php` - DONE
2. ✅ `src/Infrastructure/Persistence/PdoCmsRepository.php` - DONE
3. ✅ `migrations/20260128140000_add_is_homepage_to_cms_pages.php` - DONE
4. ✅ `src/Application/Utils/GoogleFonts.php` - DONE
5. ⏳ `src/Views/admin/cms/builder.twig` - IN PROGRESS
6. ⏳ `src/Application/Actions/Admin/Cms/SaveBuilderAction.php` - TO DO
7. ⏳ Nuovo: `src/Application/Actions/Admin/Cms/PublishPageAction.php` - TO DO
8. ⏳ Nuovo: `src/Application/Actions/Admin/Cms/SetHomepageAction.php` - TO DO
9. ⏳ `config/routes.php` - TO DO (aggiungi routes)

## Output Atteso

- ✅ Font selector con 4 opzioni Google Fonts
- ✅ Font size (12-72px)
- ✅ Font weight (dipendente dal font)
- ✅ Letter spacing e word spacing
- ✅ Homepage selector
- ✅ Draft → Published workflow
- ✅ Template modificabile sempre
