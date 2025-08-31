-- =========================================================
-- Progetto: Prezzo Minimo Supermercati
-- Giorno 11 - Schema tabelle MySQL
-- Requisiti: MySQL 8.x, InnoDB, utf8mb4
-- =========================================================

-- 0) DB e impostazioni
CREATE DATABASE IF NOT EXISTS prezzominimo
  CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE prezzominimo;
SET NAMES utf8mb4;

-- ---------------------------------------------------------
-- 1) Tabelle anagrafiche
-- ---------------------------------------------------------

CREATE TABLE Azienda (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  nome        VARCHAR(150) NOT NULL,
  piva        VARCHAR(16)  NOT NULL,
  email       VARCHAR(255) NOT NULL,
  UNIQUE KEY uq_azienda_piva (piva)
) ENGINE=InnoDB;

CREATE TABLE Prodotto (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  nome        VARCHAR(150) NOT NULL,
  marca       VARCHAR(100) NOT NULL,
  codiceEAN   VARCHAR(13)  NOT NULL,
  descrizione TEXT NULL,
  fotoURL     VARCHAR(512) NULL,
  aziendaId   INT NOT NULL,
  CONSTRAINT fk_prodotto_azienda
    FOREIGN KEY (aziendaId) REFERENCES Azienda(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  UNIQUE KEY uq_prodotto_ean (codiceEAN),
  KEY ix_prodotto_nome_marca (nome, marca)
) ENGINE=InnoDB;

CREATE TABLE PuntoVendita (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  nome        VARCHAR(120) NOT NULL,
  indirizzo   VARCHAR(255) NOT NULL,
  citta       VARCHAR(120) NOT NULL,
  telefono    VARCHAR(25)  NULL,
  email       VARCHAR(255) NULL,
  KEY ix_pv_citta_nome (citta, nome)
) ENGINE=InnoDB;

CREATE TABLE Utente (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  nome          VARCHAR(100) NOT NULL,
  email         VARCHAR(255) NOT NULL,
  passwordHash  VARCHAR(255) NOT NULL,   -- compatibile bcrypt
  ruolo         ENUM('utente','punto_vendita','azienda','admin') NOT NULL DEFAULT 'utente',
  UNIQUE KEY uq_utente_email (email)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 2) Prezzi e Offerte
-- ---------------------------------------------------------

CREATE TABLE Prezzo (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  prodottoId      INT NOT NULL,
  puntoVenditaId  INT NOT NULL,
  autoreId        INT NULL, -- utente che inserisce (NULL se ufficiale negozio)
  valore          DECIMAL(10,2) NOT NULL,
  dataInizio      DATE NOT NULL,
  dataFine        DATE NULL,
  fonte           ENUM('utente','punto_vendita') NOT NULL,
  CONSTRAINT chk_prezzo_valore_pos CHECK (valore > 0),
  CONSTRAINT chk_prezzo_date CHECK (dataFine IS NULL OR dataInizio <= dataFine),
  CONSTRAINT fk_prezzo_prodotto
    FOREIGN KEY (prodottoId) REFERENCES Prodotto(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_prezzo_pv
    FOREIGN KEY (puntoVenditaId) REFERENCES PuntoVendita(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_prezzo_autore
    FOREIGN KEY (autoreId) REFERENCES Utente(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  KEY ix_prezzo_lookup (prodottoId, puntoVenditaId, dataInizio),
  KEY ix_prezzo_validita (dataInizio, dataFine)
) ENGINE=InnoDB;

CREATE TABLE Offerta (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  puntoVenditaId  INT NOT NULL,
  dataInizio      DATE NOT NULL,
  dataFine        DATE NOT NULL,
  CONSTRAINT chk_offerta_date CHECK (dataInizio <= dataFine),
  CONSTRAINT fk_offerta_pv
    FOREIGN KEY (puntoVenditaId) REFERENCES PuntoVendita(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  KEY ix_offerta_validita (dataInizio, dataFine)
) ENGINE=InnoDB;

CREATE TABLE DettaglioOfferta (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  offertaId      INT NOT NULL,
  prodottoId     INT NOT NULL,
  prezzoOfferta  DECIMAL(10,2) NOT NULL,
  CONSTRAINT chk_dofferta_val CHECK (prezzoOfferta > 0),
  CONSTRAINT fk_dofferta_offerta
    FOREIGN KEY (offertaId) REFERENCES Offerta(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_dofferta_prodotto
    FOREIGN KEY (prodottoId) REFERENCES Prodotto(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  UNIQUE KEY uq_offerta_prodotto (offertaId, prodottoId),
  KEY ix_dofferta_lookup (prodottoId)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 3) Watchlist e Notifiche
-- ---------------------------------------------------------

CREATE TABLE Watchlist (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  utenteId     INT NOT NULL,
  prodottoId   INT NOT NULL,
  sogliaPrezzo DECIMAL(10,2) NULL,
  CONSTRAINT fk_watchlist_utente
    FOREIGN KEY (utenteId) REFERENCES Utente(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_watchlist_prodotto
    FOREIGN KEY (prodottoId) REFERENCES Prodotto(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  UNIQUE KEY uq_watchlist (utenteId, prodottoId)
) ENGINE=InnoDB;

CREATE TABLE Notifica (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  utenteId     INT NOT NULL,
  prodottoId   INT NOT NULL,
  tipo         ENUM('email','push') NOT NULL,
  sogliaPrezzo DECIMAL(10,2) NULL,
  dataInvio    DATETIME NULL,
  letto        BOOLEAN NOT NULL DEFAULT 0,
  CONSTRAINT fk_notifica_utente
    FOREIGN KEY (utenteId) REFERENCES Utente(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_notifica_prodotto
    FOREIGN KEY (prodottoId) REFERENCES Prodotto(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  KEY ix_notifica_utente (utenteId, dataInvio),
  KEY ix_notifica_prodotto (prodottoId)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- NOTE
-- - La regola "periodi non sovrapposti per (prodotto,puntoVendita)"
--   va validata lato applicativo o con TRIGGER.
-- - Priorità prezzi in ricerca:
--   1) DettaglioOfferta attiva > 2) Prezzo fonte 'punto_vendita' > 3) Prezzo fonte 'utente'.
-- ---------------------------------------------------------
