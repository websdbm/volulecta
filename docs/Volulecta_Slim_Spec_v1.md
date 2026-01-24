# Volulecta — Specifica tecnica v1 (Slim PHP)
**Data:** 2026-01-24  
**Goal:** documento “agent-ready” per implementare Volulecta in modo autonomo, senza ambiguità.

---

## 0) Sommario esecutivo
Volulecta è una web app con:
- **Frontend pubblico** con pagine gestite da CMS integrato (home + pagine informative).
- **Area autenticata** con tre ruoli: **admin**, **bibliofilo**, **utente**.
- **Core flow:** l’utente si registra → compila un questionario strutturato (solo opzioni) → l’app assegna un bibliofilo in automatico → chat asincrona + raccomandazioni libri → acquisto su Amazon con link affiliato → tracking click e statistiche.
- **Monetizzazione v1:** affiliazioni Amazon (IT).  
- **Monetizzazione v2 (già prevista in design):** piano gratuito con **limite raccomandazioni** + piano mensile basso “prioritario e illimitato”.

---

## 1) Requisiti funzionali

### 1.1 Ruoli e permessi (RBAC + ownership)
Ruoli:
- **Admin**
- **Bibliofilo**
- **Utente**

Regole assolute:
- Un **utente** può vedere/modificare **solo** i propri dati (profilo, chat, raccomandazioni, notifiche).
- Un **bibliofilo** può vedere/modificare **solo** gli utenti assegnati a lui, le loro chat e le raccomandazioni create per loro.
- Un **admin** può vedere/modificare tutto.

Azioni per ruolo (minimo v1):

**Admin**
- Gestione CMS pagine (CRUD, publish/draft).
- Gestione utenti (CRUD, sospensione, reset password).
- Gestione bibliofili (CRUD, capacità slot, attivazione/disattivazione).
- Trigger ricalcolo assegnazioni (opzionale manuale).
- Statistiche aggregate questionari e funnel (base).
- Audit log (consultazione).

**Bibliofilo**
- Dashboard: elenco utenti assegnati, stato chat, nuovi messaggi.
- Vista singolo utente: profilo Q&A, progressi, note interne.
- Chat asincrona (solo testo + emoji).
- Raccomandazioni libri: cercare su Amazon (API), selezionare libro, aggiungere motivazione, proporre all’utente.
- Strumenti AI: pre-selezione libri, sintesi libro, sintesi profilo utente (con log).

**Utente**
- Compilazione questionario guidato.
- Chat asincrona (solo testo + emoji) con bibliofilo assegnato.
- Vista raccomandazioni: elenco libri consigliati + motivazione + link Amazon affiliato.
- Notifiche: gestione preferenze (email + push web).

---

## 2) Requisiti non funzionali
- **Framework:** Slim 4, PHP 8.2+
- **DB:** MySQL/MariaDB (default), schema progettato in modo compatibile con Postgres (niente funzioni proprietarie).
- **Templating:** Twig.
- **Auth:** sessioni server-side + cookie HttpOnly + SameSite=Lax, CSRF per tutte le POST/PUT/DELETE da browser.
- **Sicurezza:** rate limiting login, Argon2id per password, validazione input server-side, output escaping.
- **Logging:** Monolog, audit log DB per azioni rilevanti.
- **Deploy:** docker-compose (php-fpm + nginx + db) + env vars.

---

## 3) Architettura di progetto (cartelle e layering)
Struttura consigliata (clean-ish, pragmatica):

```
/public
  index.php
  /assets
  /sw.js (service worker web push)

/config
  settings.php
  routes.php
  dependencies.php

/src
  /Application
    /Actions        (controller/handler per route)
    /Middleware     (auth, rbac, csrf, rate limit, etc.)
    /Validators     (request validation)
    /Policies       (RBAC + ACL ownership)
    /DTO            (request/response shape)
  /Domain
    /Entities       (User, Profile, Question, Recommendation, ...)
    /Services       (AssignmentService, RecommendationService, AiService, AmazonService, ...)
    /Repositories   (interfacce)
  /Infrastructure
    /Persistence    (implementazioni repo DB)
    /External
      /Amazon       (Product Advertising API client)
      /AI           (provider abstraction + OpenAI implementation)
    /Mail           (SMTP sender)
    /Push           (Web Push VAPID sender)
    /Storage        (uploads se necessari in futuro)
  /Views            (Twig templates)

/migrations
/seeds
/logs
/docker
``

Regola: **Actions** chiamano **Services** (Domain), che usano **Repositories**. Niente query SQL dentro Actions.

---

## 4) Flussi applicativi chiave

### 4.1 Registrazione e onboarding utente
1. Utente si registra (email+password).
2. Conferma email (v1: opzionale ma consigliata; implementare comunque).
3. Utente accede e viene portato a **Questionario**.
4. Completato questionario → creato/aggiornato `user_profiles` + `user_answers`.
5. Sistema assegna automaticamente un bibliofilo (AssignmentService).
6. Si crea (o recupera) una conversazione (thread unico) tra utente e bibliofilo.
7. Utente vede dashboard con:
   - bibliofilo assegnato
   - chat
   - raccomandazioni (inizialmente vuote)

### 4.2 Assegnazione automatica bibliofilo (v1 deterministica)
**Obiettivo v1:** semplice, stabile, senza parametri “da definire”.

Regola assegnazione v1 (obbligatoria):
- Ogni bibliofilo ha `capacity_max` (slot) e `is_active=true`.
- Calcola `active_assigned_count` (utenti assegnati con assignment attivo).
- Scegli il bibliofilo con **minore saturazione**:  
  `saturazione = active_assigned_count / capacity_max`
- In caso di parità, scegli quello con `last_assigned_at` più vecchio (round-robin).
- Se non esiste alcun bibliofilo disponibile (capienza piena):  
  assegnare a **bibliofilo “fallback”** definito in settings (admin lo imposta) **oppure** mettere utente in stato `waiting_list=true` e mostrare messaggio “in attesa di assegnazione”.
  - In v1 **scegliamo waiting list** per evitare sovraccarichi:  
    `users.waiting_list=true` e nessuna conversazione finché non assegnato.

Trigger assegnazione:
- Automatico al termine questionario.
- Manuale da admin: “Run assignment” per assegnare utenti in waiting list.

### 4.3 Chat asincrona (testo + emoji)
- Thread unico per utente in v1.
- Tabella `conversations` (user_id, bibliophile_id, last_message_at).
- Tabella `messages` (conversation_id, sender_user_id, body, created_at, read_at).
- Read receipts: `read_at` valorizzato quando il destinatario apre la chat.
- Notifiche (email+push) alla ricezione di un messaggio non letto.

### 4.4 Raccomandazioni libri + link Amazon affiliato
- Il bibliofilo cerca un libro via **Amazon Product Advertising API** (IT marketplace).
- Seleziona il risultato → viene creato/aggiornato record `books`.
- Crea una `recommendation` per l’utente con:
  - motivazione (testo breve)
  - status = `proposed`
  - sort_order
- L’utente vede la raccomandazione e può cliccare “Acquista su Amazon”:
  - link passa da endpoint interno `/app/books/{rec_id}/out`
  - l’endpoint registra click in `click_events`
  - redireziona a URL Amazon con `tag` affiliato configurato.

Limitazioni v1:
- Non si traccia acquisto reale (solo click). L’utente può marcare manualmente “acquistato” (campo boolean) **opzionale**: in v1 **NON** implementare per evitare ambiguità.

### 4.5 Strumenti AI “assistiti” per bibliofilo
Obiettivo: aumentare produttività senza sostituire il giudizio umano.

Funzioni AI v1 (tutte auditabili e disattivabili):
1. **Profile summary:** genera sintesi del profilo utente dalle risposte del questionario.
2. **Book shortlist:** propone 5–10 libri (titolo+autore+reasoning breve) dato un profilo + eventuali vincoli.
3. **Book summary:** riassume rapidamente un libro (da dati API: descrizione, titolo, autore, categorie).

Regole:
- Il bibliofilo può ignorare/bypassare completamente l’AI.
- Ogni chiamata AI va loggata su `ai_runs` (prompt template id, input hash, output, token/costi se disponibili, created_at, actor_user_id).

Provider AI:
- Implementare abstraction `AiProviderInterface`.
- Implementare `OpenAiProvider` (API key via env).  
  Se API key non presente: funzionalità AI disabilitata e UI mostra “AI non configurata”.

---

## 5) Frontend pubblico + CMS integrato

### 5.1 Pagine
- Home (`/`)
- Pagina CMS (`/p/{slug}`)
- Login / Register / Forgot password

### 5.2 CMS (admin)
CRUD pagine:
- `slug` (unico)
- `title`
- `content_markdown` (default) + renderer Markdown → HTML con whitelist
- SEO: `seo_title`, `seo_description`
- `status`: draft/published
- `published_at`

Nota: niente media gallery in v1.

---

## 6) Notifiche (Email + Push Web)

### 6.1 Email
- Provider: SMTP (env: host, port, user, pass, from).
- Eventi email v1:
  - Welcome/verify email (se conferma attiva)
  - Password reset
  - Nuovo messaggio in chat (se utente/bibliofilo ha opt-in)
  - Nuova raccomandazione proposta (utente)

### 6.2 Push (Web Push)
Implementare Web Push standard:
- Service worker `/public/sw.js`
- Endpoint per salvare subscription:
  - `POST /app/push/subscribe`
  - `POST /app/push/unsubscribe`
- DB: `push_subscriptions` (user_id, endpoint, p256dh, auth, user_agent, created_at)
- Invio push via libreria PHP WebPush (VAPID):
  - env: `VAPID_PUBLIC_KEY`, `VAPID_PRIVATE_KEY`, `VAPID_SUBJECT`

Eventi push v1:
- Nuovo messaggio chat
- Nuova raccomandazione proposta

Preferenze:
- Tabella `notification_prefs` (user_id, email_enabled, push_enabled, created_at)

---

## 7) Monetizzazione (v1 + design per v2)

### 7.1 v1 (gratis + affiliazione)
- Nessun pagamento.
- Tracciamento click su Amazon.
- Admin configura `amazon_affiliate_tag_it` e credenziali API.

### 7.2 v2 (già predisposta, ma NON implementata in v1)
- Piano Free: limite raccomandazioni mese (es. 5) e/o priorità bassa.
- Piano Pro: mensile basso, raccomandazioni illimitate + priorità.
Predisposizione DB in v1:
- `user_plans` (user_id, plan=free|pro, valid_until, created_at)
- `usage_counters` (user_id, month, recommendations_created_count)
In v1 creare tabelle ma non applicare enforcement.

---

## 8) Integrazione Amazon Product Advertising API (IT)

### 8.1 Scopo v1
- Ricerca libri su Amazon dal backend bibliofilo.
- Recupero dati minimi per salvare `books` e generare link affiliato.

### 8.2 Design (obbligatorio)
- Implementare `AmazonClientInterface` con metodi:
  - `searchBooks(query, limit=10): AmazonBookResult[]`
  - `getBookByAsin(asin): AmazonBookResult`
- `AmazonBookResult` include:
  - asin, title, author, detailPageUrl, imageUrl, publisher (se presente), isbn13 (se presente), categories (se presenti), description (se disponibile)
- Gestione errori:
  - rate limit → retry con backoff (max 2 tentativi)
  - credenziali mancanti → feature disabilitata con messaggio UI

Nota: PA-API è “strict” e richiede firme. Implementare con libreria affidabile oppure implementazione custom con test.  
In assenza di chiavi in ambiente dev, usare `FakeAmazonClient` con fixture JSON.

### 8.3 Link affiliato
- Base: usare `detailPageUrl` da API e aggiungere `?tag=AFFILIATE_TAG` o parametro richiesto dal formato URL restituito.
- L’endpoint di redirect interno **deve** registrare evento e poi redirect 302.

---

## 9) Data model (DB) — definitivo v1
Le tabelle sotto sono **obbligatorie** in v1.

### 9.1 Auth & ruoli
**users**
- id (PK)
- email (unique)
- password_hash
- role ENUM('admin','bibliophile','user')
- status ENUM('active','suspended') default 'active'
- waiting_list TINYINT default 0
- email_verified_at DATETIME nullable
- created_at, updated_at

**password_resets**
- id
- user_id
- token (unique)
- expires_at
- created_at

### 9.2 Bibliofili
**bibliophile_profiles**
- user_id (PK, FK users.id)
- display_name
- bio TEXT nullable
- capacity_max INT default 10
- is_active TINYINT default 1
- last_assigned_at DATETIME nullable
- created_at, updated_at

### 9.3 Assegnazioni
**assignments**
- id (PK)
- user_id (FK users.id)  -- utente
- bibliophile_id (FK users.id) -- bibliofilo
- is_active TINYINT default 1
- assigned_at DATETIME
- created_at, updated_at

Constraint: in v1 un solo assignment attivo per user. Enforce via unique index su (user_id, is_active=1) gestito applicativamente + indice parziale simulato (in MySQL usare controllo in service + unique su (user_id, is_active) e tenere storico solo con is_active=0).

### 9.4 Questionario strutturato
**questions**
- id
- code (unique, es: "fav_genres")
- text
- type ENUM('single','multi')
- sort_order INT
- is_active TINYINT default 1
- created_at, updated_at

**question_options**
- id
- question_id (FK)
- value (string, stable)
- label (string)
- sort_order INT
- is_active TINYINT default 1

**user_answers**
- id
- user_id (FK)
- question_id (FK)
- option_value (string)   -- per multi: una riga per opzione selezionata
- created_at

**user_profiles**
- user_id (PK, FK)
- completed_at DATETIME nullable
- ai_summary TEXT nullable  -- generata da AI tool (opzionale)
- created_at, updated_at

Statistiche aggregate:
- query su user_answers join questions per conteggi (no tabella extra necessaria v1).

### 9.5 Chat
**conversations**
- id
- user_id (FK users.id)
- bibliophile_id (FK users.id)
- last_message_at DATETIME nullable
- created_at, updated_at

**messages**
- id
- conversation_id (FK)
- sender_user_id (FK users.id)
- body TEXT
- created_at
- read_at DATETIME nullable

### 9.6 Libri e raccomandazioni
**books**
- id
- amazon_asin (unique)
- title
- author
- isbn13 nullable
- publisher nullable
- description TEXT nullable
- image_url nullable
- detail_url TEXT  -- URL amazon base (senza tracking interno)
- created_at, updated_at

**recommendations**
- id
- user_id (FK users.id)
- bibliophile_id (FK users.id)
- book_id (FK books.id)
- rationale TEXT  -- motivazione del bibliofilo
- status ENUM('draft','proposed','archived') default 'proposed'
- sort_order INT default 0
- created_at, updated_at

### 9.7 Tracking e audit
**click_events**
- id
- user_id (FK)
- recommendation_id (FK)
- type ENUM('amazon_out') default 'amazon_out'
- meta_json JSON nullable
- created_at

**audit_log**
- id
- actor_user_id (FK users.id)
- action VARCHAR(64)  -- es: "USER_SUSPEND", "RECOMMENDATION_CREATE"
- entity_type VARCHAR(64)
- entity_id INT
- meta_json JSON nullable
- created_at

### 9.8 CMS
**cms_pages**
- id
- slug (unique)
- title
- content_markdown LONGTEXT
- seo_title nullable
- seo_description nullable
- status ENUM('draft','published') default 'draft'
- published_at nullable
- created_at, updated_at

### 9.9 Notifiche
**notification_prefs**
- user_id (PK, FK)
- email_enabled TINYINT default 1
- push_enabled TINYINT default 1
- created_at, updated_at

**push_subscriptions**
- id
- user_id (FK)
- endpoint TEXT
- p256dh TEXT
- auth TEXT
- user_agent TEXT nullable
- created_at

### 9.10 AI logging
**ai_runs**
- id
- actor_user_id (FK users.id) -- bibliofilo
- user_id (FK users.id) nullable -- utente target (se presente)
- type ENUM('profile_summary','book_shortlist','book_summary')
- input_json JSON
- output_text LONGTEXT
- provider VARCHAR(32) default 'openai'
- created_at

---

## 10) API / Routing (definitivo v1)
### 10.1 Pubblico
- `GET /` home
- `GET /p/{slug}` pagina CMS
- `GET /login` / `POST /login`
- `GET /register` / `POST /register`
- `GET /logout`
- `GET /forgot` / `POST /forgot`
- `GET /reset/{token}` / `POST /reset/{token}`

### 10.2 Utente (`/app`)
- `GET /app/dashboard`
- `GET /app/questionnaire`
- `POST /app/questionnaire/submit`  (salva risposte, marca completed, tenta assegnazione)
- `GET /app/chat`
- `POST /app/chat/message`
- `POST /app/chat/read` (marca tutti come letti)
- `GET /app/recommendations`
- `GET /app/recommendations/{id}`
- `GET /app/recommendations/{id}/out` (log click + redirect Amazon)
- `GET /app/settings/notifications`
- `POST /app/settings/notifications`
- `POST /app/push/subscribe`
- `POST /app/push/unsubscribe`

### 10.3 Bibliofilo (`/bibliofilo`)
- `GET /bibliofilo/dashboard`
- `GET /bibliofilo/users`
- `GET /bibliofilo/users/{userId}` (profilo + risposte)
- `GET /bibliofilo/users/{userId}/chat`
- `POST /bibliofilo/users/{userId}/chat/message`
- `POST /bibliofilo/users/{userId}/chat/read`
- `GET /bibliofilo/users/{userId}/recommendations`
- `POST /bibliofilo/users/{userId}/recommendations` (crea)
- `PUT /bibliofilo/recommendations/{id}` (update rationale/status/order)
- `GET /bibliofilo/amazon/search?q=...` (proxy backend verso Amazon API)

AI tools:
- `POST /bibliofilo/ai/profile-summary` (input: userId)
- `POST /bibliofilo/ai/book-shortlist` (input: userId + optional constraints)
- `POST /bibliofilo/ai/book-summary` (input: asin OR bookId)

### 10.4 Admin (`/admin`)
- `GET /admin/dashboard`

CMS:
- `GET /admin/pages`
- `GET /admin/pages/create`
- `POST /admin/pages`
- `GET /admin/pages/{id}/edit`
- `PUT /admin/pages/{id}`
- `POST /admin/pages/{id}/publish`
- `POST /admin/pages/{id}/unpublish`

Utenti/bibliofili:
- `GET /admin/users`
- `GET /admin/users/{id}`
- `POST /admin/users/{id}/suspend`
- `POST /admin/users/{id}/activate`
- `POST /admin/users/{id}/reset-password`

- `GET /admin/bibliofili`
- `GET /admin/bibliofili/{id}`
- `PUT /admin/bibliofili/{id}` (capacity_max, is_active, profile fields)

Assegnazioni:
- `POST /admin/assignments/run` (assegna waiting list)
- `POST /admin/assignments/{userId}/reassign` (forzatura manuale con bibliophile_id esplicito)

Statistiche:
- `GET /admin/stats/questionnaire`
- `GET /admin/stats/funnel` (base: registrati, questionario completato, assegnati, primo msg, prime rec)

Audit:
- `GET /admin/audit`

Impostazioni:
- `GET /admin/settings`
- `PUT /admin/settings` (Amazon tag, VAPID, SMTP display-only; le credenziali reali stanno in env)

---

## 11) UI minima (v1) — pagine e componenti
### 11.1 Pubblico
- Home (CTA: registrati)
- Pagina CMS
- Login/Registration/Forgot/Reset

### 11.2 Utente
- Dashboard: stato assegnazione (waiting list vs assegnato), CTA chat, CTA raccomandazioni
- Questionario multi-step (progress bar, step per domanda)
- Chat: thread con timestamp, indicatori “non letto”
- Raccomandazioni: cards libro + motivazione + “Acquista”
- Settings notifiche: toggle email/push

### 11.3 Bibliofilo
- Dashboard: lista utenti con badge nuovi messaggi / nuove richieste
- Dettaglio utente: risposte questionario in forma leggibile + sintesi AI (se presente)
- Chat per utente
- Raccomandazioni: elenco + form nuova raccomandazione + ricerca Amazon
- AI tools: pannello con output incollabile nelle raccomandazioni

### 11.4 Admin
- Lista utenti, lista bibliofili, assegnazioni, CMS pagine, stats, audit

---

## 12) Sicurezza (checklist implementativa)
Obbligatorio v1:
- Password: Argon2id
- CSRF su tutte le mutazioni
- Rate limit su login/register/forgot
- Session fixation protection (regenerate on login)
- Cookies: HttpOnly, Secure (in https), SameSite=Lax
- Validazione input (server) per:
  - form questionario
  - messaggi chat (max lunghezza, no HTML)
  - rationale raccomandazione
  - CMS markdown (sanitize HTML se consentito)
- Output escaping in Twig per default
- ACL ownership: check per ogni route con param userId/recId/conversationId
- Audit log per: suspend/activate, reassign, create/update recommendation, page publish/unpublish, AI run

---

## 13) Piano di implementazione (step discreti, agent-ready)
Ogni step deve includere: (a) codice + test base, (b) migrazioni, (c) UI minima per validare.

### Step 1 — Bootstrap progetto
- Setup Slim 4 + Twig + DI container
- Config env + docker-compose (nginx+php+db)
- Struttura cartelle + PSR-4 autoload
- Monolog + error handler
Output: homepage statica + ping route `/health`

### Step 2 — DB layer + migrations
- Integra migrations (Phinx o Laravel migrations)
- Crea tabelle base: users, bibliophile_profiles, cms_pages
- Repo layer + unit test minimi
Output: admin seed + login non ancora

### Step 3 — Auth completo
- Register/login/logout
- Forgot/reset password
- (Opzionale consigliato) email verification
- Middleware Auth + CSRF
Output: area protetta “/app/dashboard” placeholder

### Step 4 — RBAC + ACL Policies
- Middleware RBAC per prefissi /admin /bibliofilo /app
- Policy ownership per userId / conversationId / recommendationId
- Audit log foundation
Output: access control robusto

### Step 5 — CMS pagine pubbliche (admin)
- CRUD cms_pages + publish/draft
- Render `/p/{slug}` solo se published
Output: sito pubblico gestibile

### Step 6 — Questionario (admin editor + utente compilazione)
- CRUD questions + options (admin UI minima)
- UI utente multi-step solo opzioni singole/multiple
- Salvataggio user_answers + completed_at
Output: utente completa profilo strutturato

### Step 7 — AssignmentService + waiting list
- Implementa algoritmo v1 (min saturazione + round robin)
- Se nessun bibliofilo disponibile → waiting_list=true
- Admin action “run assignment”
Output: utenti assegnati automaticamente

### Step 8 — Chat asincrona + notifiche email
- conversations/messages + UI user/bibliofilo
- read markers
- email on new message (rispetta notification_prefs)
Output: chat funzionante end-to-end

### Step 9 — Web Push
- Service worker + subscribe/unsubscribe
- VAPID send on new message e nuove raccomandazioni
Output: push funzionante

### Step 10 — Amazon search + Books + Recommendations
- AmazonClient + FakeAmazonClient
- UI bibliofilo ricerca + creazione raccomandazione
- UI utente elenco raccomandazioni
- redirect interno con click_events
Output: funnel monetizzazione attivo

### Step 11 — AI tools (backend + UI) + logging
- AiProviderInterface + OpenAiProvider
- Endpoints AI + ai_runs log
- UI bibliofilo: profile summary, book shortlist, book summary
Output: bibliofilo “assistito”

### Step 12 — Stats admin (base)
- Stats questionario (conteggi per opzione)
- Funnel base
- Audit log browser
Output: visibilità gestione

---

## 14) Test plan minimo
- Unit test: AssignmentService (casi saturazione e round robin)
- Unit test: RBAC middleware e policy ownership
- Integration: register → questionnaire → assignment → chat → recommendation → amazon out redirect
- Security: rate limit login, CSRF enforcement, XSS (chat e CMS)

---

## 15) Configurazione (env vars)
Obbligatori:
- `APP_ENV`, `APP_URL`, `APP_SECRET`
- `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`
- `SMTP_HOST`, `SMTP_PORT`, `SMTP_USER`, `SMTP_PASS`, `SMTP_FROM`
- `AMAZON_ASSOCIATE_TAG_IT`
- `AMAZON_ACCESS_KEY`, `AMAZON_SECRET_KEY`, `AMAZON_PARTNER_TAG` (se richiesto da PA-API), `AMAZON_REGION`
- `VAPID_PUBLIC_KEY`, `VAPID_PRIVATE_KEY`, `VAPID_SUBJECT`
- `OPENAI_API_KEY` (opzionale; se assente AI off)

---

## 16) Decisioni fissate (per evitare ambiguità)
- v1 **gratis**; pagamenti solo v2 (non implementati).
- Registrazione **self-service**.
- Assegnazione bibliofilo **automatica** con algoritmo deterministico v1.
- Questionario: editor admin, risposte **solo opzioni** (single/multi), niente testo libero.
- Amazon: ricerca via API; marketplace **IT**.
- Chat: solo testo + emoji; niente allegati.
- Notifiche: **email + web push**.
- AI: integrata backend con log, bibliofilo può bypassare.

---

## 17) Seed iniziali (obbligatori)
- 1 admin (email/password da env o script).
- 1 pagina CMS: “Come funziona”.
- Set iniziale di domande questionario (minimo 8–12).

Esempio questionario iniziale (solo come seed, modificabile da admin):
1. Generi preferiti (multi)
2. Cosa cerchi ora? (single: intrattenimento / crescita personale / lavoro / emozioni / altro)
3. Lunghezza preferita (single)
4. Lingua (single)
5. Autori amati (multi)
6. Frequenza lettura (single)
7. Temi da evitare (multi)
8. Formato (single: cartaceo / ebook / entrambi)

