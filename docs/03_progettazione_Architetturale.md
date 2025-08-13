# Progettazione Architetturale - Giorno 3

## 1. Obiettivi
Definire l'architettura software dell’applicazione “Prezzo Minimo Supermercati”, identificando i componenti principali, la struttura del sistema e le tecnologie da utilizzare.

---

## 2. Architettura Generale

### 2.1 Modello Client-Server  
L’applicazione sarà basata su architettura client-server, con un frontend web statico e un backend PHP che espone API REST per la gestione dati.

### 2.2 Componenti principali  
- **Frontend (Client):**  
  - Interfaccia utente responsiva (HTML5, CSS3, JavaScript, jQuery)  
  - Gestione delle chiamate API REST  
- **Backend (Server):**  
  - Server web Apache con PHP  
  - API REST per CRUD su prodotti, prezzi, utenti, offerte  
  - Autenticazione e gestione sessioni  
  - Log e moderazione dati  
- **Database:**  
  - MySQL per dati persistenti (prodotti, utenti, prezzi, ecc.)

---

## 3. Struttura delle Cartelle
/project-root
│
├── /docs # Documentazione del progetto
├── /src # Codice sorgente backend
│ ├── /api # Endpoint API REST
│ ├── /auth # Gestione autenticazione
│ ├── /models # Classi modello dati
│ └── /utils # Funzioni di utilità
│
├── /public # Frontend statico e risorse pubbliche
│ ├── /css
│ ├── /js
│ └── index.html
│
├── /config # File configurazione (database, server)
└── README.md

## 4. Descrizione dei Moduli

### 4.1 Frontend  
- Pagina principale con ricerca prodotti e visualizzazione prezzi  
- Pagine di login/registrazione  
- Gestione watchlist e notifiche  
- Visualizzazione offerte e volantini  

### 4.2 Backend  
- API REST per ogni entità (prodotti, prezzi, utenti, notifiche)  
- Validazione e sicurezza input  
- Gestione sessioni e ruoli  
- Moderazione dati e logging  
- Integrazione con database MySQL  

---

## 5. Tecnologie Utilizzate

| Tecnologia       | Scopo                     |
|------------------|---------------------------|
| PHP              | Backend server API        |
| MySQL            | Database relazionale      |
| Apache HTTP Server| Server web                |
| HTML5/CSS3       | Frontend statico          |
| JavaScript/jQuery| Frontend dinamico         |
| JSON             | Formato dati API          |

---

## 6. Flusso dati (esempio ricerca prezzo)

1. Utente inserisce nome prodotto nel frontend  
2. Frontend invia richiesta GET API `/api/prezzi?prodotto=nome`  
3. Backend elabora query su database  
4. Backend risponde con lista prezzi JSON  
5. Frontend visualizza i dati all’utente  

---

## 7. Considerazioni sulla sicurezza

- Sanitizzazione input e prevenzione SQL injection  
- Uso di HTTPS (da configurare in produzione)  
- Sessioni con timeout e gestione cookie sicuri  
- Ruoli e permessi per operazioni CRUD  

---

## 8. Passi successivi

- Progettazione dettagliata database  
- Definizione API REST completa  
- Mockup interfacce utente  
- Setup ambiente sviluppo  

---


