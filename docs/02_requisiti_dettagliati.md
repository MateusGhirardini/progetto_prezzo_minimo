# Documento Requisiti Dettagliati

## 1. Introduzione
**Scopo del documento**  
Definire in modo dettagliato i requisiti funzionali e non funzionali dell’applicazione “Prezzo Minimo Supermercati”, per guidare la progettazione e lo sviluppo del sistema.

**Ambito del sistema**  
Applicazione web client-server per la ricerca e gestione dei prezzi di prodotti nei supermercati, con funzionalità di notifica, gestione utenti, punti vendita e produttori.

---

## 2. Requisiti Funzionali

### RF01 — Ricerca prezzo minimo per prodotto  
**Descrizione**:  
L’utente (visitatori e registrati) può cercare un prodotto inserendo nome e/o marca, per ottenere la lista dei punti vendita con i relativi prezzi e la data di validità. Il risultato deve evidenziare il prezzo più basso.  
**Attori**: Visitatore, Utente registrato  
**Input**: Nome prodotto, Marca (facoltativo)  
**Output**: Lista supermercati con prezzo, data validità e evidenza del minimo prezzo  
**Precondizioni**: Il prodotto deve essere presente nel database  
**Postcondizioni**: Viene mostrato un elenco ordinato per prezzo crescente  
**Regole**:  
- Il prezzo mostrato deve essere aggiornato e valido  
- Se il prezzo è scaduto, deve essere segnalato  
- Ordinamento per prezzo crescente  

### RF02 — Elenco punti vendita e ultimo prezzo noto  
**Descrizione**:  
Permette a tutti gli utenti di consultare i prezzi attuali di un prodotto e la data dell’ultimo aggiornamento.  
**Attori**: Tutti  
**Input**: Selezione prodotto  
**Output**: Lista punti vendita con ultimo prezzo e data  
**Precondizioni**: Prodotto esistente  
**Postcondizioni**: Visualizzazione elenco aggiornato  
**Regole**: Evidenziare prezzi scaduti  

### RF03 — Inserimento/aggiornamento prezzo  
**Descrizione**:  
Gli utenti registrati e i punti vendita possono inserire o aggiornare il prezzo di un prodotto.  
**Attori**: Utente registrato, Punto vendita  
**Input**: Prodotto, punto vendita, prezzo, validità  
**Output**: Conferma inserimento/modifica  
**Precondizioni**: Utente autenticato  
**Postcondizioni**: Prezzo aggiornato nel database  
**Regole**: Validare campi obbligatori  

### RF04 — Offerte/Volantini  
**Descrizione**:  
I punti vendita possono pubblicare offerte multiple in un’unica operazione tramite caricamento volantini.  
**Attori**: Punto vendita  
**Input**: Elenco prodotti, prezzi, validità  
**Output**: Conferma caricamento  
**Precondizioni**: Utente punto vendita autenticato  
**Postcondizioni**: Offerte visibili agli utenti  
**Regole**: Date valide obbligatorie  

### RF05 — Gestione prodotti (anagrafica)  
**Descrizione**:  
Le aziende produttrici e gli amministratori possono creare o modificare schede prodotto.  
**Attori**: Azienda produttrice, Admin  
**Input**: Nome, marca, foto, dettagli  
**Output**: Scheda prodotto aggiornata  
**Precondizioni**: Utente autenticato e autorizzato  
**Postcondizioni**: Scheda aggiornata o creata  
**Regole**: Blocco modifiche se impostato  

### RF06 — Liste di interesse (watchlist)  
**Descrizione**:  
Gli utenti registrati possono salvare prodotti preferiti in una lista di interesse.  
**Attori**: Utente registrato  
**Input**: Selezione prodotto  
**Output**: Lista aggiornata  
**Precondizioni**: Utente autenticato  
**Postcondizioni**: Prodotto aggiunto o rimosso dalla lista  
**Regole**: Evitare duplicati  

### RF07 — Notifiche offerte/prezzi  
**Descrizione**:  
Il sistema invia notifiche agli utenti quando i prezzi scendono sotto una soglia impostata.  
**Attori**: Servizio notifiche  
**Input**: Lista desideri, variazione prezzo  
**Output**: Notifica inviata  
**Precondizioni**: Utente con lista desideri attiva  
**Postcondizioni**: Notifica recapitata  
**Regole**: Invia solo se sotto soglia  

### RF08 — Autenticazione e ruoli  
**Descrizione**:  
Garantire accesso sicuro e personalizzato con gestione ruoli.  
**Attori**: Tutti  
**Input**: Credenziali  
**Output**: Sessione attiva o errore  
**Precondizioni**: Credenziali valide  
**Postcondizioni**: Utente autenticato  
**Regole**: Gestione scadenza sessione (timeout 30 min)  

### RF09 — Moderazione e qualità dati  
**Descrizione**:  
Gli amministratori moderano i contenuti e garantiscono la qualità dei dati.  
**Attori**: Admin  
**Input**: Segnalazioni, richieste modifica  
**Output**: Conferma moderazione  
**Precondizioni**: Utente Admin autenticato  
**Postcondizioni**: Dati aggiornati o bloccati  
**Regole**: Log modifiche  

### RF10 — Cronologia prezzi  
**Descrizione**:  
Visualizzare lo storico dei prezzi per un prodotto nel tempo.  
**Attori**: Tutti  
**Input**: Selezione prodotto  
**Output**: Tabella prezzi nel tempo  
**Precondizioni**: Prodotto con dati storici  
**Postcondizioni**: Visualizzazione dati  
**Regole**: Ordinamento per data  

### RF11 — Caricamento immagini  
**Descrizione**:  
Associare foto ai prodotti.  
**Attori**: Azienda produttrice, Admin  
**Input**: File immagine  
**Output**: Anteprima foto, immagine salvata  
**Precondizioni**: Formato e dimensioni valide  
**Postcondizioni**: Foto disponibile nella scheda prodotto  
**Regole**: Formati accettati: JPG, PNG; max dimensione 2MB  

### RF12 — Localizzazione punti vendita  
**Descrizione**:  
Filtrare punti vendita per area geografica.  
**Attori**: Tutti  
**Input**: Indirizzo o città  
**Output**: Lista negozi filtrata  
**Precondizioni**: Dati geolocalizzati disponibili  
**Postcondizioni**: Visualizzazione filtrata  
**Regole**: Coordinate valide  

---

## 3. Requisiti Non Funzionali

### Prestazioni  
- Le API devono rispondere in meno di 1 secondo con un carico di 10.000 record.

### Sicurezza  
- Validazione completa dei dati lato server e client.  
- Timeout sessione dopo 30 minuti di inattività.

### Usabilità  
- Interfaccia responsive, design mobile-first con larghezza minima 360px.

### Portabilità  
- Server Docker con Apache, PHP, MySQL.  
- Client web statico con comunicazione REST.

### Manutenibilità  
- Codice e documentazione commentati e aggiornati.

---

## 4. Vincoli e Assunzioni

- Comunicazione client-server esclusivamente via RESTful API CRUD.  
- Linguaggi ammessi: PHP (server), JavaScript, HTML5, CSS3 (client).  
- Non sono ammessi framework architetturali come Laravel o Node.js, salvo jQuery.  
- Uso consentito di librerie grafiche gratuite per componenti UI.

---

## 5. Glossario

- **API**: Application Programming Interface  
- **CRUD**: Create, Read, Update, Delete  
- **MVP**: Model-View-Presenter  
- **EAN**: European Article Number (codice a barre)  
- **UI**: User Interface  

---

