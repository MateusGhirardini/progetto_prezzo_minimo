# Giorno 4 — Progettazione Database e API REST

## 1. Obiettivo  
Progettare lo schema relazionale del database e definire le API REST necessarie per le funzionalità principali.

---

## 2. Schema Database

### Tabelle principali:

| Tabella          | Descrizione                                | Campi principali                          |
|------------------|--------------------------------------------|------------------------------------------|
| `utenti`         | Gestisce gli utenti della piattaforma     | `id`, `username`, `email`, `password_hash`, `ruolo` |
| `prodotti`       | Anagrafica prodotti                        | `id`, `nome`, `marca`, `descrizione`, `ean`, `immagine_url` |
| `punti_vendita`  | Dettagli supermercati o negozi             | `id`, `nome`, `indirizzo`, `città`, `latitudine`, `longitudine` |
| `prezzi`         | Prezzi prodotti associati a punti vendita  | `id`, `id_prodotto`, `id_punto_vendita`, `prezzo`, `data_validita`, `inserito_da` |
| `offerte`        | Volantini e offerte promozionali            | `id`, `id_punto_vendita`, `data_inizio`, `data_fine`, `descrizione` |
| `offerte_prodotti`| Prodotti inclusi nelle offerte             | `id_offerta`, `id_prodotto`, `prezzo_offerta` |
| `watchlist`      | Prodotti seguiti dagli utenti               | `id_utente`, `id_prodotto`               |
| `notifiche`      | Storico notifiche inviate                   | `id`, `id_utente`, `tipo`, `contenuto`, `data_invio` |

---

## 3. Esempio di schema SQL (semplificato)

```sql
CREATE TABLE utenti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    ruolo ENUM('visitatore', 'utente', 'punto_vendita', 'azienda', 'admin') NOT NULL DEFAULT 'utente'
);

CREATE TABLE prodotti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    marca VARCHAR(50),
    descrizione TEXT,
    ean VARCHAR(13) UNIQUE,
    immagine_url VARCHAR(255)
);

CREATE TABLE punti_vendita (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    indirizzo VARCHAR(255),
    città VARCHAR(100),
    latitudine DECIMAL(10,7),
    longitudine DECIMAL(10,7)
);

CREATE TABLE prezzi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_prodotto INT NOT NULL,
    id_punto_vendita INT NOT NULL,
    prezzo DECIMAL(10,2) NOT NULL,
    data_validita DATE NOT NULL,
    inserito_da INT NOT NULL,
    FOREIGN KEY (id_prodotto) REFERENCES prodotti(id),
    FOREIGN KEY (id_punto_vendita) REFERENCES punti_vendita(id),
    FOREIGN KEY (inserito_da) REFERENCES utenti(id)
);


## 4. Definizione API REST (principali endpoint)

| Metodo | Endpoint                    | Descrizione                        | Accesso               |
| ------ | --------------------------- | ---------------------------------- | --------------------- |
| GET    | `/api/prodotti`             | Lista prodotti                     | Pubblico              |
| GET    | `/api/prodotti/{id}`        | Dettaglio prodotto                 | Pubblico              |
| GET    | `/api/prezzi?prodotto={id}` | Prezzi per un prodotto             | Pubblico              |
| POST   | `/api/prezzi`               | Inserimento o aggiornamento prezzo | Utenti registrati, PV |
| GET    | `/api/punti-vendita`        | Lista punti vendita                | Pubblico              |
| POST   | `/api/utenti/login`         | Autenticazione                     | Pubblico              |
| POST   | `/api/watchlist`            | Aggiungi prodotto a watchlist      | Utenti registrati     |
| GET    | `/api/notifiche`            | Elenco notifiche utente            | Utenti registrati     |


## 5. Esempio chiamata API (GET prezzi)

Request:
GET /api/prezzi?prodotto=42
Host: esempio.com
Accept: application/json
Response:
[
  {
    "id_punto_vendita": 5,
    "nome_punto_vendita": "Supermercato ABC",
    "prezzo": 1.99,
    "data_validita": "2025-08-15"
  },
  {
    "id_punto_vendita": 8,
    "nome_punto_vendita": "Negozio XYZ",
    "prezzo": 2.05,
    "data_validita": "2025-08-14"
  }
]

