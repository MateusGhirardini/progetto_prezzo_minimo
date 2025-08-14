# Specifiche dei Casi d'Uso
Progetto: **Prezzo Minimo Supermercati**  
Versione: v1.0 — Giorno 8

## Convenzioni
- **Attori:** Visitatore, Utente registrato, Punto vendita, Azienda produttrice, Amministratore, Servizio Notifiche.
- **RFxx:** riferimento ai Requisiti Funzionali definiti nel documento requisiti.
- Formato: *Precondizioni*, *Flusso principale*, *Varianti/Alternative*, *Postcondizioni*, *Eccezioni*.

---

## UC01 — Ricerca prezzo minimo (RF01)
**Attori primari:** Visitatore, Utente registrato  
**Scopo:** Trovare il punto vendita col prezzo più basso per un prodotto.

**Precondizioni**
- Il prodotto esiste nel sistema.

**Flusso principale**
1. L’utente inserisce nome/marca o EAN del prodotto.
2. Il sistema cerca i prezzi validi (in data odierna o entro validità).
3. Il sistema ordina i risultati per prezzo crescente.
4. Il sistema evidenzia il **prezzo minimo** e mostra la validità.

**Varianti/Alternative**
- V1: L’utente applica il filtro posizione (→ **UC04**).
- V2: L’utente ordina per distanza o per data aggiornamento.

**Postcondizioni**
- Lista di punti vendita con prezzo e validità mostrata.

**Eccezioni**
- E1: Nessun prezzo disponibile → messaggio “Nessuna offerta trovata”.

---

## UC02 — Elenco punti vendita e ultimo prezzo noto (RF02)
**Attori:** Visitatore, Utente registrato  
**Scopo:** Vedere tutti i negozi che vendono il prodotto e l’ultimo prezzo noto.

**Precondizioni**
- Prodotto selezionato o ricercato.

**Flusso principale**
1. L’utente apre la scheda del prodotto.
2. Il sistema mostra la lista punti vendita con ultimo prezzo e **data aggiornamento**.
3. Evidenza grafica per prezzi **scaduti** o fuori validità.

**Varianti/Alternative**
- V1: Filtri per città/raggio (→ **UC04**).

**Postcondizioni**
- Vista aggiornata della disponibilità per punto vendita.

**Eccezioni**
- E1: Prodotto non trovato.

---

## UC03 — Visualizza cronologia prezzi (RF10)
**Attori:** Visitatore, Utente registrato  
**Scopo:** Consultare lo storico dei prezzi nel tempo.

**Precondizioni**
- Prodotto esistente.

**Flusso principale**
1. L’utente apre la “Cronologia prezzi”.
2. Il sistema carica la serie storica (prezzo, data, fonte).
3. Il sistema visualizza tabella e (opzionale) grafico.
4. Filtri per intervallo date.

**Varianti/Alternative**
- V1: Nessun dato storico → messaggio dedicato.

**Postcondizioni**
- Timeline prezzi visualizzata/filtrata.

---

## UC04 — Filtra per localizzazione (RF12) *(«include» in UC01/UC02)*
**Attori:** Visitatore, Utente registrato  
**Scopo:** Restringere risultati a una zona (città/raggio).

**Precondizioni**
- L’utente fornisce indirizzo o consente posizione browser.

**Flusso principale**
1. L’utente imposta città/indirizzo e raggio (es. 5–20 km).
2. Il sistema geocodifica/valida i dati.
3. I risultati vengono **filtrati** per distanza e ordinati.

**Eccezioni**
- E1: Indirizzo non valido/permessi negati → mantenere risultati senza filtro.

**Postcondizioni**
- Risultati limitati all’area indicata.

---

## UC05 — Login / Registrazione (RF08)
**Attori:** Visitatore (per registrarsi), Utente registrato (per login)  
**Scopo:** Accedere alle funzionalità personali.

**Precondizioni**
- Nessuna (per registrazione) / account esistente (per login).

**Flusso principale**
1. Registrazione: l’utente inserisce email, password e accetta termini.
2. (Opzionale) Verifica email.
3. Login: l’utente inserisce credenziali.
4. Il sistema crea **sessione** con ruolo utente.

**Varianti/Alternative**
- V1: Recupero password via email.

**Postcondizioni**
- Sessione attiva, ruolo associato.

**Eccezioni**
- E1: Email già in uso / password errata.

---

## UC06 — Gestione watchlist (RF06)
**Attori:** Utente registrato  
**Scopo:** Salvare prodotti preferiti e gestire soglie.

**Precondizioni**
- Utente autenticato.

**Flusso principale**
1. L’utente aggiunge un prodotto alla watchlist.
2. Imposta (opzionale) una **soglia prezzo**.
3. Visualizza/rimuove elementi dalla lista.

**Varianti/Alternative**
- V1: Aggiunta rapida da risultati di ricerca.
- V2: Evitare duplicati nella watchlist.

**Postcondizioni**
- Watchlist aggiornata (con soglie opzionali).

**Eccezioni**
- E1: Prodotto non esistente/duplicato.

---

## UC07 — Configura notifiche prezzi/offerte (RF07)
**Attori:** Utente registrato; **Secondario:** Servizio Notifiche  
**Scopo:** Ricevere avvisi quando il prezzo scende o appare un’offerta.

**Precondizioni**
- Utente autenticato; canale notifiche configurabile (email/push).

**Flusso principale**
1. L’utente abilita canali (email, push).
2. Imposta soglie e preferenze frequenza.
3. Il sistema salva le preferenze.

**Varianti/Alternative**
- V1: Test notifica (invio di prova).

**Postcondizioni**
- Preferenze attive; notifiche inviate quando le condizioni si verificano.

**Eccezioni**
- E1: Permessi push negati / email non valida.

---

## UC08 — Inserisci prezzo osservato (RF03)
**Attori:** Utente registrato (consumatore)  
**Scopo:** Contribuire inserendo un **prezzo** visto in negozio.

**Precondizioni**
- Utente autenticato; prodotto e punto vendita presenti nel sistema.

**Flusso principale**
1. L’utente seleziona prodotto e punto vendita.
2. Inserisce valore, data inizio (e fine, se nota).
3. Il sistema valida i campi (numerici, date).
4. Il sistema salva prezzo con **autore** e **timestamp**.

**Varianti/Alternative**
- V1: Foto scontrino (opzionale, se abilitato).
- V2: Prezzo sovrascritto da offerta ufficiale (priorità negozio).

**Postcondizioni**
- Prezzo disponibile nelle ricerche; può attivare notifiche (RF07).

**Eccezioni**
- E1: Prezzo non valido (<0) o date incoerenti.

---

## UC09 — Gestisci prezzi (punto vendita) (RF03)
**Attori:** Punto vendita  
**Scopo:** Inserire/aggiornare prezzi ufficiali.

**Precondizioni**
- Account punto vendita verificato; login eseguito.

**Flusso principale**
1. Il negozio seleziona prodotto.
2. Inserisce prezzo e **validità** (da/a).
3. Salva e pubblica.

**Varianti/Alternative**
- V1: Import massivo (CSV/volantino).

**Postcondizioni**
- Prezzi ufficiali aggiornati (con priorità rispetto a prezzi utente).

**Eccezioni**
- E1: Periodi sovrapposti / dati mancanti.

---

## UC10 — Pubblica offerte/volantini (RF04)
**Attori:** Punto vendita  
**Scopo:** Pubblicare più offerte in un’unica operazione.

**Precondizioni**
- Login punto vendita.

**Flusso principale**
1. Caricamento elenco prodotti/prezzi/validità.
2. Verifica coerenza date.
3. Pubblicazione volantino.

**Relazioni**
- «include» → **UC09 Gestisci prezzi**.

**Postcondizioni**
- Offerte visibili; ricerche aggiornate.

**Eccezioni**
- E1: Formato file non valido / date errate.

---

## UC11 — Gestisci prodotti (azienda) (RF05)
**Attori:** Azienda produttrice  
**Scopo:** Gestire l’anagrafica prodotto e **blocco modifiche**.

**Precondizioni**
- Account azienda verificato.

**Flusso principale**
1. Creazione/aggiornamento scheda prodotto (nome, marca, EAN, foto).
2. (Opzionale) **Blocca modifiche** metadati per utenti/negozi.

**Relazioni**
- «include» → **UC12 Carica immagini prodotto**  
- «extend» → **Blocca modifiche metadati**

**Postcondizioni**
- Scheda aggiornata e, se bloccata, non modificabile da terzi.

**Eccezioni**
- E1: EAN duplicato.

---

## UC12 — Carica immagini prodotto (RF11)
**Attori:** Azienda produttrice, Amministratore  
**Scopo:** Associare immagini valide al prodotto.

**Precondizioni**
- Prodotto esistente.

**Flusso principale**
1. Upload file (jpg/png/webp) entro limiti di dimensione.
2. Validazione e salvataggio URL immagine.

**Postcondizioni**
- Immagine disponibile nella scheda prodotto.

**Eccezioni**
- E1: Formato/dimensioni non valide.

---

## UC13 — Moderazione e qualità dati (RF09)
**Attori:** Amministratore  
**Scopo:** Garantire qualità, rimuovere duplicati e contenuti impropri.

**Precondizioni**
- Accesso admin.

**Flusso principale**
1. Revisione segnalazioni e log modifiche.
2. Unione/eliminazione duplicati.
3. Eventuali sanzioni/limitazioni account.

**Postcondizioni**
- Dati puliti, tracciabilità mantenuta.

**Eccezioni**
- E1: Conflitti di modifica → risoluzione manuale.

---
