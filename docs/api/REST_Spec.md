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
