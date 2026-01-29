# 🎯 QUICK START - Test Suite CMS Builder

Benvenuto! Ecco come eseguire rapidamente la suite di test automatizzata per il CMS Builder.

---

## 🚀 Esecuzione più Veloce (30 secondi)

### Opzione 1: Menu Interattivo
```bash
bash run-tests.sh
```
Seleziona il tipo di test dal menu e tutto il resto è automatico.

### Opzione 2: Test Console Diretto

1. Apri il builder:
   ```
   http://localhost:8080/admin/cms/builder/1
   ```

2. Premi `F12` per aprire la Developer Console

3. Digita:
   ```javascript
   runBuilderTests()
   ```

4. Aspetta il risultato (~2 secondi) ✨

**Output Atteso:**
```
✅ Test Passati: 98
❌ Test Falliti: 0
📈 Success Rate: 100.0%
🎉 TUTTI I TEST PASSATI!
```

---

## 🧪 Esecuzione Completa

### Frontend + Backend + E2E (10 minuti)

```bash
# 1. Backend Test (1 sec)
php tests/BlockValidatorTest.php

# 2. Cypress E2E Test (45 sec)
npx cypress run --spec "cypress/e2e/cms-builder.cy.js"

# 3. Console Test (2 sec)
# Manuale: apri builder → F12 → runBuilderTests()
```

---

## 📋 Test Disponibili

### ✅ Test Rapido Console (98 Assertions)
- **Cosa testa**: Blocchi, font controls, responsive, performance
- **Tempo**: ~2 secondi
- **Come**: `runBuilderTests()` in console
- **File**: `public/js/builder-tests.js`

### ✅ Test Cypress E2E (41 Test Cases)
- **Cosa testa**: UI, interazioni, workflows, accessibilità
- **Tempo**: ~45 secondi
- **Come**: `npm run test:e2e:builder`
- **File**: `cypress/e2e/cms-builder.cy.js`

### ✅ Test Backend (20+ Assertions)
- **Cosa testa**: Validazione font, colori, allineamento
- **Tempo**: ~1 secondo
- **Come**: `php tests/BlockValidatorTest.php`
- **File**: `tests/BlockValidatorTest.php`

---

## 💻 Credenziali di Login

```
Email:    admin@volulecta.local
Password: password123
```

---

## 🎬 Cypress GUI (Interattivo)

Se vuoi vedere i test eseguirsi visivamente:

```bash
npx cypress open
```

Poi:
1. Seleziona "E2E Testing"
2. Scegli Chrome (o altro browser)
3. Clicca su `cms-builder.cy.js`
4. Guarda i test eseguirsi in tempo reale! 🎬

---

## 📊 Risultati Attesi

Se tutti i test passano, vedrai:

### Console Test
```
🧪 Inizio Test Suite CMS Builder...

✅ Test Passati: 98
❌ Test Falliti: 0
📈 Success Rate: 100.0%

🎉 TUTTI I TEST PASSATI! Il builder è pronto per la produzione.
```

### Cypress Test
```
✓ 41 passing
✓ Completed
✓ Generated report in cypress/results/
```

### Backend Test
```
✅ Test Passati: 20
❌ Test Falliti: 0

🎉 TUTTI I TEST PASSATI!
✅ BlockValidator è pronto per la produzione
```

---

## 🔧 Troubleshooting

### Problema: "Module not found"
**Soluzione:**
```bash
npm install
```

### Problema: "Chrome not found"
**Soluzione:**
```bash
# Usa Firefox
npx cypress run --browser firefox
```

### Problema: "PHP not found"
**Soluzione:**
```bash
# Su Mac con Homebrew
brew install php

# Verifica
php --version
```

### Problema: Browser non si apre
**Soluzione:**
```bash
# Esegui in headless mode
npx cypress run --headless
```

---

## 📚 Documentazione Completa

Per guide dettagliate, vedi:

- **TEST_SUITE_GUIDE.md** - Guida completa ai test (50+ paragrafi)
- **FINAL_STATUS.md** - Status finale del progetto
- **IMPLEMENTATION_COMPLETE.md** - Riepilogo implementazione

---

## 🎁 Output dei Test

### Console Test Output
```javascript
window.testResults

// Output:
{
  passed: 98,
  failed: 0,
  total: 98,
  successRate: "100.0",
  results: [
    { name: "Aggiunta blocco Title", status: "✅ PASS" },
    // ... altri 97 test
  ],
  timestamp: "2026-01-28T10:30:00.000Z"
}
```

### Cypress Test Output
```
cypress/videos/     # Video dei test
cypress/screenshots/ # Screenshot al fallimento
cypress/results/    # Report JSON
```

---

## 🚀 Deploy Checklist

Prima di mandare in produzione:

- [ ] `runBuilderTests()` - ✅ Pass
- [ ] `npx cypress run` - ✅ Pass
- [ ] `php tests/BlockValidatorTest.php` - ✅ Pass
- [ ] Browser DevTools - ✅ Nessun errore
- [ ] Performance profiling - ✅ OK

---

## 📞 Support

Hai domande? Vedi:
- **TEST_SUITE_GUIDE.md** - Sezione "Troubleshooting"
- **FINAL_STATUS.md** - Sezione "Quality Metrics"

---

**Status**: ✅ Production Ready  
**Coverage**: 160+ Assertions  
**Quality**: ⭐⭐⭐⭐⭐ (5/5)

---

*Last Updated: 28 Gennaio 2026*
