USE prezzominimo;

INSERT INTO Azienda (nome,piva,email) VALUES
 ('Barilla','00123456789','info@barilla.it'),
 ('Parmalat','00234567890','info@parmalat.it');

INSERT INTO Prodotto (nome,marca,codiceEAN,descrizione,fotoURL,aziendaId) VALUES
 ('Pasta Spaghetti 500g','Barilla','8076809511006','Spaghetti n.5',NULL,1),
 ('Latte UHT 1L','Parmalat','8001234567897','Latte intero',NULL,2);

INSERT INTO PuntoVendita (nome,indirizzo,citta,telefono,email) VALUES
 ('Conad Centro','Via Roma 1','Bologna','0510000001','bo@conad.it'),
 ('Esselunga Est','Via Emilia 200','Modena','0590000002','mo@esselunga.it');

INSERT INTO Utente (nome,email,passwordHash,ruolo) VALUES
 ('Mario Rossi','mario@example.com','$2y$10$hashfinto','utente'),
 ('Admin','admin@example.com','$2y$10$hashfinto','admin');

INSERT INTO Prezzo (prodottoId,puntoVenditaId,autoreId,valore,dataInizio,dataFine,fonte) VALUES
 (1,1,NULL,1.29,'2025-08-01','2025-08-31','punto_vendita'),
 (1,2,NULL,1.19,'2025-08-10',NULL,'punto_vendita'),
 (2,1,1,1.09,'2025-08-05',NULL,'utente');

INSERT INTO Offerta (puntoVenditaId,dataInizio,dataFine) VALUES
 (1,'2025-08-15','2025-08-22');

INSERT INTO DettaglioOfferta (offertaId,prodottoId,prezzoOfferta) VALUES
 (1,1,0.99);

INSERT INTO Watchlist (utenteId,prodottoId,sogliaPrezzo) VALUES
 (1,1,1.00);

INSERT INTO Notifica (utenteId,prodottoId,tipo,sogliaPrezzo,dataInvio,letto)
VALUES (1,1,'email',1.00,NULL,0);
