# Schema Relazionale — Versione 1.0
Progetto: **Prezzo Minimo Supermercati**  
Data: {{oggi}}  

## 0. Regole di mapping ER → Relazionale adottate
- **Entità** ⇒ **Relazioni** con PK surrogate `INT AUTO_INCREMENT`.
- **Relazioni 1—N** ⇒ FK sul lato **N** (con vincoli ON DELETE / ON UPDATE).
- **Relazioni opzionali** ⇒ FK **NULL** (es. `Prezzo.autoreId`).
- **M—N** ⇒ tabella ponte con **chiave unica** composta (es. `Watchlist(utenteId, prodottoId)`).
- **Attributi univoci** ⇒ **Alternate Key** con `UNIQUE` (es. `Prodotto.codiceEAN`, `Azienda.piva`).
- **Vincoli applicativi** non esprimibili facilmente in SQL (es. periodi non sovrapposti) documentati come **regole** e da gestire in applicazione o trigger.

---

## 1. Relazioni (notazione relazionale)

**AZIENDA**(  
  **id** PK,  
  nome, piva **UNIQUE**, email  
)

**PRODOTTO**(  
  **id** PK,  
  nome, marca, codiceEAN **UNIQUE**, descrizione, fotoURL,  
  aziendaId **FK→AZIENDA.id**  
)

**PUNTOVENDITA**(  
  **id** PK,  
  nome, indirizzo, citta, telefono, email  
)

**UTENTE**(  
  **id** PK,  
  nome, email **UNIQUE**, passwordHash,  
  ruolo ∈ {utente, punto_vendita, azienda, admin}  
)

**PREZZO**(  
  **id** PK,  
  prodottoId **FK→PRODOTTO.id**,  
  puntoVenditaId **FK→PUNTOVENDITA.id**,  
  autoreId **FK→UTENTE.id NULL**,  
  valore (>0), dataInizio, dataFine NULL,  
  fonte ∈ {utente, punto_vendita}  
)  
- Indici: `(prodottoId, puntoVenditaId, dataInizio)`, `(dataInizio, dataFine)`  
- **Regola applicativa**: per ogni `(prodottoId, puntoVenditaId)` gli intervalli `[dataInizio, dataFine]` **non si sovrappongono**.

**OFFERTA**(  
  **id** PK,  
  puntoVenditaId **FK→PUNTOVENDITA.id**,  
  dataInizio ≤ dataFine  
)  
- Indice: `(dataInizio, dataFine)`

**DETTAGLIO_OFFERTA**(  
  **id** PK,  
  offertaId **FK→OFFERTA.id**,  
  prodottoId **FK→PRODOTTO.id**,  
  prezzoOfferta (>0),  
  **UNIQUE(offertaId, prodottoId)**  
)  
- Indice: `(prodottoId)`

**WATCHLIST**(  
  **id** PK,  
  utenteId **FK→UTENTE.id**,  
  prodottoId **FK→PRODOTTO.id**,  
  sogliaPrezzo NULL,  
  **UNIQUE(utenteId, prodottoId)**  
)

**NOTIFICA**(  
  **id** PK,  
  utenteId **FK→UTENTE.id**,  
  prodottoId **FK→PRODOTTO.id**,  
  tipo ∈ {email, push},  
  sogliaPrezzo NULL, dataInvio NULL, letto BOOLEAN DEFAULT 0  
)  
- Indici: `(utenteId, dataInvio)`, `(prodottoId)`

---

## 2. Dipendenze funzionali (sintesi) e Normalizzazione
- **AZIENDA**: `id → {nome, piva, email}`, `piva → {id, nome, email}` ⇒ 3NF (alt key su `piva`).
- **PRODOTTO**: `id → {...}`, `codiceEAN → {id, nome, marca, ...}` ⇒ 3NF (alt key su `codiceEAN`).
- **PUNTOVENDITA**: `id → {...}` ⇒ 3NF.
- **UTENTE**: `id → {...}`, `email → {...}` ⇒ 3NF (alt key su `email`).
- **PREZZO**: `id → {...}`; business key candidata `BK = (prodottoId, puntoVenditaId, dataInizio)` se imponi “un periodo che inizia in una certa data per coppia (prodotto, PV) è unico”. 3NF.
- **DETTAGLIO_OFFERTA**: `id → {...}`, `UNIQUE(offertaId, prodottoId)` come key candidata ⇒ 3NF.
- **WATCHLIST**: `id → {...}`, `UNIQUE(utenteId, prodottoId)` come key candidata ⇒ 3NF.
- **NOTIFICA**: `id → {...}` ⇒ 3NF.

Tutte le relazioni sono in **3NF**; molte sono anche in **BCNF** (dove le sole dipendenze sono chiavi → attributi non chiave).

---

## 3. Vincoli di integrità referenziale (azioni su DELETE/UPDATE)
- `PRODOTTO.aziendaId → AZIENDA.id` : **RESTRICT** (non puoi cancellare un’Azienda con prodotti associati).  
- `PREZZO.prodottoId → PRODOTTO.id` : **CASCADE**  
- `PREZZO.puntoVenditaId → PUNTOVENDITA.id` : **CASCADE**  
- `PREZZO.autoreId → UTENTE.id` : **SET NULL**  
- `OFFERTA.puntoVenditaId → PUNTOVENDITA.id` : **CASCADE**  
- `DETTAGLIO_OFFERTA.offertaId → OFFERTA.id` : **CASCADE**  
- `DETTAGLIO_OFFERTA.prodottoId → PRODOTTO.id` : **CASCADE**  
- `WATCHLIST.utenteId → UTENTE.id` : **CASCADE**  
- `WATCHLIST.prodottoId → PRODOTTO.id` : **CASCADE**  
- `NOTIFICA.utenteId → UTENTE.id` : **CASCADE**  
- `NOTIFICA.prodottoId → PRODOTTO.id` : **CASCADE**

---

## 4. Regole applicative (non-SQL) importanti
- **Periodi prezzi non sovrapposti** per stessa coppia `(prodottoId, puntoVenditaId)`.  
- **Priorità prezzo** in ricerca:  
  1) `DETTAGLIO_OFFERTA` attiva;  
  2) `PREZZO` con `fonte='punto_vendita'`;  
  3) `PREZZO` con `fonte='utente'`.  
- **Notifica** generata quando un nuovo prezzo/ offerta `≤ sogliaPrezzo` impostata in `WATCHLIST`.

---

## 5. Vista di supporto (opzionale per la documentazione)
Esempio di **vista** per recuperare il **prezzo attuale** di un prodotto per ogni punto vendita, considerando le offerte:

```sql
CREATE OR REPLACE VIEW v_prezzo_attuale AS
SELECT
  pv.id AS puntoVenditaId,
  p.id  AS prodottoId,
  COALESCE(doff.prezzoOfferta, pr.valore) AS prezzoCorrente,
  CASE WHEN doff.prezzoOfferta IS NOT NULL THEN 'offerta'
       WHEN pr.fonte = 'punto_vendita' THEN 'punto_vendita'
       ELSE 'utente' END AS fonte,
  GREATEST(COALESCE(off.dataInizio, pr.dataInizio)) AS dataInizioEff,
  LEAST(COALESCE(off.dataFine, pr.dataFine)) AS dataFineEff
FROM Prodotto p
JOIN PuntoVendita pv
LEFT JOIN (
  Offerta off
  JOIN DettaglioOfferta doff ON doff.offertaId = off.id
  AND CURRENT_DATE BETWEEN off.dataInizio AND off.dataFine
) ON doff.prodottoId = p.id
LEFT JOIN (
  SELECT x.* FROM Prezzo x
  WHERE (x.dataFine IS NULL OR CURRENT_DATE <= x.dataFine)
    AND CURRENT_DATE >= x.dataInizio
) pr ON pr.prodottoId = p.id AND pr.puntoVenditaId = pv.id;
