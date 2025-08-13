Usare l’anteprima Markdown: Premi Ctrl+Shift+V (Windows/Linux) oppure Cmd+Shift+V (Mac).
Oppure clicca in alto a destra sull’icona con una lente e un foglio

# Progetto “Prezzo Minimo Supermercati” — Giorno 1
Versione: bozza v0.2 – completata Giorno 1

## Scopo del documento
Definire attori principali e l’elenco preliminare delle funzionalità dell’applicazione, come base per la progettazione successiva.

## Attori
| Attore               | Descrizione                        | Permessi/Responsabilità principali                                | Esempi di azioni |
|----------------------|----------------------------------|-------------------------------------------------------------------|------------------|
| **Visitatore**       | Utente non autenticato.            | Ricerca prodotti e visualizzazione prezzi/ultimi prezzi noti, consultazione offerte pubbliche. | Cerca “pasta Barilla 500g”, vede elenco supermercati e prezzo più basso. |
| **Utente registrato**| Consumatore con account.           | Gestione lista desideri, ricezione notifiche, inserimento prezzi osservati. | Aggiunge “Latte X” alla lista; viene avvisato quando scende sotto una soglia. |
| **Punto vendita**    | Account ufficiale di un supermercato/negozio. | Inserimento/aggiornamento prezzi e offerte, caricamento volantini. | Pubblica volantino settimanale. |
| **Azienda produttrice** | Produttore.                   | Gestione anagrafica prodotto, blocco modifiche.                  | Registra “Olio XYZ 1L”, blocca modifiche ai metadati. |
| **Amministratore di sistema** | Gestore della piattaforma. | Moderazione, gestione ruoli, audit.                              | Approva un nuovo punto vendita. |
| **Servizio notifiche** | Componente interno.              | Invio notifiche.                                                  | Invia avviso quando prezzo scende sotto soglia. |

### Note su ciascun attore
- **Visitatore**
  - Obiettivo: consultare prezzi e offerte senza registrarsi.
  - Può fare: cercare prodotti, vedere offerte.
  - Esempio: Mario cerca “pasta Barilla 500g” e vede i prezzi.
- **Utente registrato**
  - Obiettivo: gestire lista desideri e ricevere notifiche.
  - Può fare: aggiungere prodotti preferiti, impostare soglie prezzo.
  - Esempio: Luca aggiunge “Latte X” e riceve avviso di sconto.
- **Punto vendita**
  - Obiettivo: pubblicare e aggiornare offerte.
  - Può fare: inserire prezzi, caricare volantini.
  - Esempio: Conad pubblica volantino valido 12–18 Agosto.
- **Azienda produttrice**
  - Obiettivo: garantire accuratezza dati prodotto.
  - Può fare: registrare prodotti, bloccare modifiche.
  - Esempio: Barilla registra un nuovo formato di pasta.
- **Amministratore**
  - Obiettivo: mantenere qualità e correttezza del sistema.
  - Può fare: moderare contenuti, gestire ruoli.
  - Esempio: rimuove prodotto duplicato.
- **Servizio notifiche**
  - Obiettivo: informare gli utenti di variazioni di prezzo.
  - Può fare: inviare email o notifiche push.
  - Esempio: invia avviso per “Latte X” sotto i 2€.

## Funzionalità principali (Requisiti funzionali)
**RF01 — Ricerca prezzo minimo per prodotto**  
- Scopo: permettere all’utente di trovare il punto vendita col prezzo più basso.  
- Attori: Visitatore, Utente registrato.  
- Input: nome/marca prodotto.  
- Output: lista negozi con prezzo, data validità, evidenza del minimo.  
- Regole: ordinamento per prezzo crescente.

**RF02 — Elenco punti vendita e ultimo prezzo noto**  
- Scopo: consultare i prezzi attuali e la data di aggiornamento.  
- Attori: tutti.  
- Input: selezione prodotto.  
- Output: lista punti vendita con ultimo prezzo e data.  
- Regole: evidenziare prezzi scaduti.

**RF03 — Inserimento/aggiornamento prezzo**  
- Scopo: aggiungere o modificare il prezzo di un prodotto in un punto vendita.  
- Attori: Utente registrato, Punto vendita.  
- Input: prodotto, punto vendita, prezzo, validità.  
- Output: conferma inserimento.  
- Regole: validare campi obbligatori.

**RF04 — Offerte/Volantini**  
- Scopo: pubblicare più offerte in un’unica operazione.  
- Attori: Punto vendita.  
- Input: elenco prodotti, prezzi, validità.  
- Output: conferma caricamento.  
- Regole: date valide obbligatorie.

**RF05 — Gestione prodotti (anagrafica)**  
- Scopo: creare o modificare schede prodotto.  
- Attori: Azienda produttrice, Admin.  
- Input: nome, marca, foto, dettagli.  
- Output: scheda prodotto aggiornata.  
- Regole: blocco modifiche se impostato.

**RF06 — Liste di interesse (watchlist)**  
- Scopo: salvare prodotti preferiti.  
- Attori: Utente registrato.  
- Input: selezione prodotto.  
- Output: lista aggiornata.  
- Regole: evitare duplicati.

**RF07 — Notifiche offerte/prezzi**  
- Scopo: avvisare di variazioni di prezzo.  
- Attori: Servizio notifiche.  
- Input: lista desideri, variazione prezzo.  
- Output: notifica inviata.  
- Regole: inviare solo se sotto soglia impostata.

**RF08 — Autenticazione e ruoli**  
- Scopo: garantire accesso sicuro e personalizzato.  
- Attori: tutti.  
- Input: credenziali.  
- Output: sessione attiva.  
- Regole: gestione scadenza sessione.

**RF09 — Moderazione e qualità dati**  
- Scopo: mantenere dati corretti.  
- Attori: Admin.  
- Input: segnalazioni.  
- Output: conferma moderazione.  
- Regole: log modifiche.

**RF10 — Cronologia prezzi**  
- Scopo: visualizzare storico prezzi.  
- Attori: tutti.  
- Input: selezione prodotto.  
- Output: tabella prezzi nel tempo.  
- Regole: ordinamento per data.

**RF11 — Caricamento immagini**  
- Scopo: associare foto al prodotto.  
- Attori: Azienda, Admin.  
- Input: file immagine.  
- Output: anteprima foto.  
- Regole: formato e dimensioni valide.

**RF12 — Localizzazione punti vendita**  
- Scopo: filtrare per area geografica.  
- Attori: tutti.  
- Input: indirizzo/città.  
- Output: lista negozi filtrata.  
- Regole: coordinate valide.

## Requisiti non funzionali
- Prestazioni: risposta API < 1s per 10.000 record.  
- Sicurezza: validazione input, sessioni con timeout 30 min.  
- Tracciabilità: ogni prezzo ha autore, timestamp, validità.  
- Usabilità: UI responsive (mobile-first 360px).  
- Portabilità: server Docker (Apache+PHP+MySQL), client statico.

## Questioni aperte
- Q1: usare codice EAN obbligatorio per deduplicare prodotti?  
- Q2: priorità tra prezzi: offerte punto vendita > prezzi utente?  
- Q3: notifiche predefinite via email o push browser?  
- Q4: verifica account punto vendita/azienda con documenti ufficiali?
