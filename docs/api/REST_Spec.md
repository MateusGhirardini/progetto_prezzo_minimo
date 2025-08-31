# Specifica API REST — Progetto Prezzo Minimo Supermercati

Versione: v1.0  
Data: 2025-08-21  
Autore: [Il tuo nome]

---

## 1. Introduzione
Questo documento definisce le API REST CRUD per l’applicazione “Prezzo Minimo Supermercati”.  
Lo scopo è garantire una comunicazione standard tra client e server, rispettando il vincolo di architettura REST-full.

---

## 2. Regole generali
- Formato dati: **JSON**
- Codici risposta HTTP:
  - `200 OK`: richiesta completata
  - `201 Created`: risorsa creata
  - `400 Bad Request`: input errato
  - `401 Unauthorized`: credenziali mancanti o errate
  - `404 Not Found`: risorsa non trovata
  - `500 Internal Server Error`: errore generico server
- Autenticazione: sessione tramite login, con gestione cookie PHPSESSID.

---

## 3. Endpoints

### 3.1 Prodotti
- **GET /api/prodotti**  
  Ritorna lista di tutti i prodotti.  
  **Risposta**:
  ```json
  [
    { "id": 1, "nome": "Latte", "marca": "Parmalat" },
    { "id": 2, "nome": "Pasta", "marca": "Barilla" }
  ]
  
  **GET /api/prodotti/{id}
  Dettagli di un singolo prodotto.
  
  **POST /api/prodotti
  Crea un nuovo prodotto.
  Input:

  { "nome": "Olio", "marca": "De Cecco", "dettagli": "1L" }

  **PUT /api/prodotti/{id}
  Aggiorna un prodotto esistente.

  **DELETE /api/prodotti/{id}
  (solo Admin/Azienda) Elimina un prodotto.

### 3.2 Prezzi

  **GET /api/prezzi?prodotto=ID
  Ritorna prezzi per un prodotto con ultimo aggiornamento.

  **POST /api/prezzi
  Inserisce un nuovo prezzo.
  Input:

  { "id_prodotto": 1, "id_punto_vendita": 2, "prezzo": 1.49, "validita": "2025-09-01" }

###3.3 Offerte

  **POST /api/offerte
  Inserisce un volantino con più prodotti.

  **GET /api/offerte
  Lista di tutte le offerte attive.

###3.4 Utenti

  **POST /api/utenti/register
  Registra un nuovo utente.

  **POST /api/utenti/login
  Esegue login e restituisce sessione.

  **POST /api/utenti/logout
  Termina la sessione attiva.

###3.5 Watchlist

  **GET /api/watchlist
  Recupera lista prodotti preferiti dell’utente loggato.

  **POST /api/watchlist
  Aggiunge prodotto alla lista desideri.

  **DELETE /api/watchlist/{id_prodotto}
  Rimuove prodotto dalla lista.

###3.6 Notifiche

  **GET /api/notifiche
  Ritorna notifiche attive per l’utente.

---
