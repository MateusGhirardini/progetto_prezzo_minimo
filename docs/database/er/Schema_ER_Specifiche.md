# Schema E-R — Specifiche e Vincoli (Versione finale)

Progetto: **Prezzo Minimo Supermercati** — Giorno 10

## 1. Chiavi primarie e tipi (proposta SQL)
- **Utente**
  - id INT PK AUTO_INCREMENT
  - nome VARCHAR(100), email VARCHAR(255) UNIQUE
  - passwordHash VARCHAR(255)  -- compatibile bcrypt
  - ruolo ENUM('utente','punto_vendita','azienda','admin')
- **Azienda**
  - id INT PK, nome VARCHAR(150), piva VARCHAR(11) UNIQUE, email VARCHAR(255)
- **Prodotto**
  - id INT PK, nome VARCHAR(150), marca VARCHAR(100)
  - codiceEAN VARCHAR(13) UNIQUE NOT NULL
  - descrizione TEXT, fotoURL VARCHAR(512)
  - aziendaId INT FK → Azienda(id)
- **PuntoVendita**
  - id INT PK, nome VARCHAR(120)
  - indirizzo VARCHAR(255), citta VARCHAR(120)
  - telefono VARCHAR(25), email VARCHAR(255)
- **Prezzo**
  - id INT PK
  - prodottoId INT FK → Prodotto(id)
  - puntoVenditaId INT FK → PuntoVendita(id)
  - autoreId INT NULL FK → Utente(id)
  - valore DECIMAL(10,2) CHECK (valore > 0)
  - dataInizio DATE NOT NULL, dataFine DATE NULL
  - fonte ENUM('utente','punto_vendita')
- **Offerta**
  - id INT PK, puntoVenditaId INT FK → PuntoVendita(id)
  - dataInizio DATE, dataFine DATE
- **DettaglioOfferta**
  - id INT PK, offertaId INT FK → Offerta(id)
  - prodottoId INT FK → Prodotto(id)
  - prezzoOfferta DECIMAL(10,2) CHECK (prezzoOfferta > 0)
- **Watchlist**
  - id INT PK, utenteId INT FK → Utente(id)
  - prodottoId INT FK → Prodotto(id)
  - sogliaPrezzo DECIMAL(10,2) NULL
- **Notifica**
  - id INT PK, utenteId INT FK → Utente(id)
  - prodottoId INT FK → Prodotto(id)
  - tipo ENUM('email','push')
  - sogliaPrezzo DECIMAL(10,2) NULL
  - dataInvio DATETIME NULL, letto BOOLEAN DEFAULT 0

## 2. Vincoli e regole
- **Unicità**
  - Prodotto.codiceEAN **UNIQUE**
  - Watchlist **UNIQUE (utenteId, prodottoId)**
  - DettaglioOfferta **UNIQUE (offertaId, prodottoId)** (evita doppioni)
- **Integrità referenziale (azioni su DELETE)**
  - Azienda → Prodotto: `ON DELETE RESTRICT`
  - Prodotto → Prezzo/DettaglioOfferta/Watchlist/Notifica: `ON DELETE CASCADE`
  - PuntoVendita → Prezzo/Offerta: `ON DELETE CASCADE`
  - Offerta → DettaglioOfferta: `ON DELETE CASCADE`
  - Utente → Prezzo.autoreId: `ON DELETE SET NULL`
  - Utente → Watchlist/Notifica: `ON DELETE CASCADE`
- **Validità prezzi**
  - `dataInizio <= dataFine` (se `dataFine` non è NULL)
  - **Regola applicativa**: per una stessa coppia (prodotto, puntoVendita) i periodi **non devono sovrapporsi**. (Verificata a livello applicativo o con trigger.)
- **Priorità** prezzi in ricerca:
  1. **DettaglioOfferta.prezzoOfferta** (se Offerta in validità)
  2. **Prezzo** di **punto vendita**
  3. **Prezzo** inserito da **utente**
- **Notifiche**
  - Generare notifica quando un prezzo/ offerta scende **≤ soglia** impostata.

## 3. Indici consigliati
- `Prodotto(codiceEAN)`, `Prodotto(nome, marca)`
- `Prezzo(prodottoId, puntoVenditaId, dataInizio)` + indice su `dataFine`
- `DettaglioOfferta(offertaId, prodottoId)`
- `Notifica(utenteId, prodottoId, dataInvio)`
- `Watchlist(utenteId, prodottoId)`
- `PuntoVendita(citta, nome)`

## 4. Note di normalizzazione
- I dati di **Azienda** sono separati da **Prodotto** (1–N).  
- **Offerta** aggrega righe (`DettaglioOfferta`) per più prodotti in un intervallo.  
- **Watchlist** modella la relazione M–N tra Utente e Prodotto.

