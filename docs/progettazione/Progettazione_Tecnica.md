# Progettazione Tecnica — Progetto “Prezzo Minimo Supermercati”
Versione: v1.0 • Data: {{31/08/2025}} • Autore: {Mateus Ghirardini}

## 1. Scopo e contesto
Questo documento descrive la progettazione tecnica dell’applicazione:
- architettura **client–server** con **API REST CRUD**,
- modello dati (MySQL),
- endpoint e contratti JSON,
- aspetti di sicurezza, logging, performance, deploy (Docker).

> Vincoli didattici: **Server PHP**, **Client HTML/CSS/JS + jQuery**, niente framework architetturali, solo REST.

---

## 2. Architettura del sistema (MVP + REST)
**Visione d’insieme**
- **Client (View + Presenter)**: pagine HTML/CSS + JS/jQuery. Il *Presenter* effettua chiamate AJAX alle API, prepara i dati e aggiorna la View.
- **Server (Model + Controller REST)**: script PHP che espongono endpoint REST; accesso a MySQL tramite PDO (prepared statements).
- **Database**: MySQL (InnoDB, utf8mb4). Vincoli/trigger per integrità.

**Flusso tipico**
1. L’utente usa la UI → Presenter invia richiesta AJAX.
2. Controller PHP valida input → Model interroga DB.
3. Risposta JSON → Presenter aggiorna la UI.

**Componenti**
- `client/` (HTML, CSS, JS+jQuery)
- `server/public/api/` (PHP REST controllers)
- `server/src/` (Model/DAO, helper sicurezza/validazione)
- `database/sql/` (schema, viste, trigger, seed)
- `deploy/` (Docker Compose, Dockerfile — vedi Giorno 16)

---

## 3. Modello dati (riassunto coerente col DB)
Riferimento completo in: `database/sql/schema.sql` (Giorno 11) + `triggers.sql`/`views.sql` (Giorno 13).

**Tabelle principali**
- **Azienda**(id, nome, piva UNIQUE, email)
- **Prodotto**(id, nome, marca, codiceEAN UNIQUE, descrizione, fotoURL, aziendaId FK→Azienda)
- **PuntoVendita**(id, nome, indirizzo, citta, telefono, email)
- **Utente**(id, nome, email UNIQUE, passwordHash, ruolo ENUM)
- **Prezzo**(id, prodottoId FK, puntoVenditaId FK, autoreId FK NULL, valore>0, dataInizio, dataFine?, fonte ENUM)
- **Offerta**(id, puntoVenditaId FK, dataInizio≤dataFine)
- **DettaglioOfferta**(id, offertaId FK, prodottoId FK, prezzoOfferta>0, UNIQUE(offertaId, prodottoId))
- **Watchlist**(id, utenteId FK, prodottoId FK, sogliaPrezzo?, UNIQUE(utenteId, prodottoId))
- **Notifica**(id, utenteId FK, prodottoId FK, tipo ENUM, sogliaPrezzo?, dataInvio?, letto BOOL)

**Regole DB chiave**
- **Trigger anti-sovrapposizione** periodi in `Prezzo` (Giorno 13).
- **Viste**: `v_prezzo_corrente_per_pv`, `v_prezzo_minimo_prodotto` per letture efficienti.

---

## 4. API REST (contratti, rimando a REST_Spec)
Riferimento completo: `documentazione/api/REST_Spec.md` (Giorno 14).

**Mapping rapido risorse → endpoint**
- **Prodotti**: `GET/POST/PUT/DELETE /api/prodotti(/:id)`
- **Prezzi**: `GET /api/prezzi`, `POST /api/prezzi`
- **Offerte**: `GET /api/offerte`, `POST /api/offerte`
- **Auth/Utenti**: `POST /auth/register|login|logout`, `GET/PUT/DELETE /utenti/:id`
- **Watchlist**: `GET/POST/DELETE /watchlist`
- **Notifiche**: `GET /notifiche`, `POST /notifiche/test`

**Errori standard JSON**
```json
{ "error": { "code": "VALIDATION_ERROR", "message": "Campo mancante: prodotto_id", "details": { "campo": "prodotto_id" } } }
```
---
## 5. Sicurezza

### Autenticazione (sessione PHP)
- **Login** → crea sessione (`PHPSESSID`, `HttpOnly`, `SameSite=Lax`).  
- **Logout** → invalida sessione (`session_destroy()`).  

### Autorizzazione (ruoli)
Ruoli previsti: `utente`, `punto_vendita`, `azienda`, `admin`.  
Esempi:  
- `POST /offerte` → `punto_vendita`  
- `DELETE /prodotti/:id` → `admin`

### Protezione CSRF
Per richieste mutative (`POST/PUT/DELETE`) con cookie: usa **token CSRF**.
- `GET /auth/csrf` → restituisce `{ "csrf_token": "..." }` e lo salva in `$_SESSION`.  
- Il client invia header `X-CSRF-Token` ad ogni mutazione.  
- Il server verifica `$_SESSION['csrf'] === header`.  

Alternative minime: `SameSite` cookie + double submit token.

### Validazione input
- Uso di **PHP PDO con prepared statements**.  
- Sanitizzazione:  
  - Stringhe → `trim`, limite lunghezza  
  - Numeri → `filter_var`  
  - Date → regex `YYYY-MM-DD` + controlli range  

### Crittografia
- Password: `password_hash()` (bcrypt) + `password_verify()`.

### Header consigliati
- `Content-Type: application/json; charset=utf-8`  
- `Cache-Control: no-store` per auth, `max-age` per letture pubbliche  
- `X-Content-Type-Options: nosniff`  
- `X-Frame-Options: DENY`  
- `Referrer-Policy: strict-origin-when-cross-origin`  

---

## 6. Logging & Audit
- **Access log** → web server (Apache).  
- **App log (PHP)** → `server/storage/logs/app.log`:  
  - Eventi: login, create/put/delete, errori DB  
  - Formato: JSON lines (`timestamp, level, route, userId, message`)  
- **Audit DB (opzionale)** → tabella append-only per modifiche critiche.  

---

## 7. Prestazioni & Scalabilità
- **Paginazione** ovunque (`page`, `page_size`).  
- **Indici**: `Prodotto(nome, marca)`, `Prezzo(prodottoId, pvId, dataInizio)`.  
- **Viste**: per calcoli prezzo corrente/minimo.  
- **JOIN** per evitare N+1 query.  
- **Caching**: header per liste prodotti (se necessario).  
- **Rate limit**: tabella `rate_limit` per IP/utente (opzionale).  

---

## 8. Gestione errori (mappatura)

| Errore                       | HTTP | Codice             |
|------------------------------|------|-------------------|
| Validazione fallita          | 400  | VALIDATION_ERROR  |
| Non autenticato              | 401  | UNAUTHORIZED      |
| Permesso negato              | 403  | FORBIDDEN         |
| Risorsa non trovata          | 404  | NOT_FOUND         |
| Conflitto (periodo sovrapp.) | 409  | CONFLICT          |
| Errore interno               | 500  | SERVER_ERROR      |

**Esempio PHP**
```php
function json_error($http, $code, $msg, $details=[]) {
  http_response_code($http);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode(['error'=>['code'=>$code,'message'=>$msg,'details'=>$details]]);
  exit;
}
```
```bash
---

##9. Struttura delle directory (server/client)

progetto_prezzo_minimo/
├─ client/
│  ├─ index.html
│  ├─ assets/css/main.css
│  └─ assets/js/app.js
├─ server/
│  ├─ public/
│  │  ├─ index.php            # router base / pagina home
│  │  └─ api/                 # REST endpoints (PHP)
│  │     ├─ prodotti.php
│  │     ├─ prezzi.php
│  │     ├─ offerte.php
│  │     ├─ auth.php
│  │     ├─ utenti.php
│  │     ├─ watchlist.php
│  │     └─ notifiche.php
│  └─ src/
│     ├─ db.php               # connessione PDO
│     ├─ auth.php             # session & csrf helper
│     ├─ validate.php         # funzioni di validazione
│     ├─ log.php              # logging applicativo
│     └─ dao/                 # DAO per entità
├─ database/
│  └─ sql/
│     ├─ schema.sql
│     ├─ triggers.sql
│     ├─ views.sql
│     └─ seed_demo.sql
├─ documentazione/
│  ├─ api/REST_Spec.md
│  ├─ progettazione/Progettazione_Tecnica.md
│  └─ uml/...
└─ deploy/
   ├─ docker-compose.yml
   └─ server.Dockerfile
   ```

## 10. Piano test (riferimento)

- **Unit (PHP)** → testare validazione input e funzioni helper.  
- **SQL** → eseguire `test_queries.sql` (creato al Giorno 13) per verificare viste e trigger.  
- **API** → usare Postman per verificare CRUD di prodotti, prezzi, offerte, watchlist.  
- **Integrazione** → simulare flow completo utente: ricerca prodotto → inserimento prezzo → gestione watchlist.  

---

## 11. Deploy (preview — dettaglio al Giorno 16)

- **Docker Compose** con due servizi principali:  
  - `web` (Apache + PHP)  
  - `db` (MySQL 8)  

- **Variabili ambiente in `.env`:**  
  - `MYSQL_ROOT_PASSWORD`  
  - `MYSQL_DATABASE`  
  - `APP_ENV`  

- **Inizializzazione DB** automatica da `database/sql/*.sql`.  

---

## 12. Rischi & Mitigazioni

- **Dati incoerenti** → risolti con trigger e validazioni lato server.  
- **Prestazioni query prezzo minimo** → viste dedicate + indici ottimizzati.  
- **Sicurezza sessioni** → cookie sicuri + token CSRF.  
- **Upload immagini prodotto** → validazione tipo/size; in alternativa, disabilitare upload se non richiesto.  

---

## 13. Tracciabilità requisiti

- **RF01 Ricerca prezzo minimo** → viste SQL + endpoint `/prezzi` e `/offerte`.  
- **RF03 Inserimento prezzo** → `POST /prezzi` + trigger overlap.  
- **RF06 Watchlist** → endpoint `/watchlist` + sistema notifiche.  
- **RF07 Notifiche** → endpoint `/notifiche`, generazione quando superata soglia.  
- **RNF Sicurezza/Prestazioni/Portabilità** → sezioni 5, 7 e 11 del presente documento.  

---

## 14. Appendice — Snippet utili

### Connessione PDO (`server/src/db.php`)
```php
<?php
function get_pdo() {
  $dsn = "mysql:host=".($_ENV['DB_HOST'] ?? '127.0.0.1').";dbname=prezzominimo;charset=utf8mb4";
  $user = $_ENV['DB_USER'] ?? 'appuser';
  $pass = $_ENV['DB_PASS'] ?? 'app_pass';
  $opt = [
    PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC
  ];
  return new PDO($dsn, $user, $pass, $opt);
}
```
Header JSON & CORS minimo (per test locale)
```php
header('Content-Type: application/json; charset=utf-8');
// Solo per test locali:
// header('Access-Control-Allow-Origin: http://localhost:8080');
// header('Access-Control-Allow-Credentials: true');
// header('Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS');
// header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');

---

