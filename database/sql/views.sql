USE prezzominimo;

-- Drop per ri-eseguibilità
DROP VIEW IF EXISTS v_offerte_attive;
DROP VIEW IF EXISTS v_prezzi_attivi;
DROP VIEW IF EXISTS v_prezzo_corrente_per_pv;
DROP VIEW IF EXISTS v_prezzo_minimo_prodotto;

-- Offerte attive oggi
CREATE VIEW v_offerte_attive AS
SELECT
  o.id           AS offertaId,
  doff.prodottoId,
  o.puntoVenditaId,
  doff.prezzoOfferta,
  o.dataInizio,
  o.dataFine
FROM Offerta o
JOIN DettaglioOfferta doff ON doff.offertaId = o.id
WHERE CURRENT_DATE BETWEEN o.dataInizio AND o.dataFine;

-- Prezzi attivi oggi (non scaduti)
CREATE VIEW v_prezzi_attivi AS
SELECT
  pr.id,
  pr.prodottoId,
  pr.puntoVenditaId,
  pr.valore,
  pr.fonte,
  pr.dataInizio,
  pr.dataFine
FROM Prezzo pr
WHERE pr.dataInizio <= CURRENT_DATE
  AND (pr.dataFine IS NULL OR pr.dataFine >= CURRENT_DATE);

-- Prezzo corrente per (prodotto, punto vendita) con priorità: offerta > prezzo PV > prezzo utente
CREATE VIEW v_prezzo_corrente_per_pv AS
SELECT
  p.id  AS prodottoId,
  pv.id AS puntoVenditaId,
  COALESCE(oa.prezzoOfferta,
           MAX(CASE WHEN pa.fonte='punto_vendita' THEN pa.valore END),
           MAX(CASE WHEN pa.fonte='utente'        THEN pa.valore END)) AS prezzoCorrente,
  CASE
    WHEN oa.prezzoOfferta IS NOT NULL THEN 'offerta'
    WHEN MAX(CASE WHEN pa.fonte='punto_vendita' THEN pa.valore END) IS NOT NULL THEN 'punto_vendita'
    WHEN MAX(CASE WHEN pa.fonte='utente' THEN pa.valore END) IS NOT NULL THEN 'utente'
    ELSE NULL
  END AS fonte
FROM Prodotto p
CROSS JOIN PuntoVendita pv
LEFT JOIN v_offerte_attive oa
       ON oa.prodottoId = p.id AND oa.puntoVenditaId = pv.id
LEFT JOIN v_prezzi_attivi pa
       ON pa.prodottoId = p.id AND pa.puntoVenditaId = pv.id
GROUP BY p.id, pv.id, oa.prezzoOfferta;

-- Prezzo minimo per prodotto (tra tutti i PV)
CREATE VIEW v_prezzo_minimo_prodotto AS
SELECT
  cpp.prodottoId,
  MIN(cpp.prezzoCorrente) AS prezzoMinimo
FROM v_prezzo_corrente_per_pv cpp
WHERE cpp.prezzoCorrente IS NOT NULL
GROUP BY cpp.prodottoId;
