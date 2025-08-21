USE prezzominimo;

-- 1) Vedi prezzi attivi
SELECT * FROM v_prezzi_attivi ORDER BY prodottoId, puntoVenditaId;

-- 2) Vedi offerte attive
SELECT * FROM v_offerte_attive ORDER BY prodottoId, puntoVenditaId;

-- 3) Prezzo corrente per (prodotto, PV)
SELECT * FROM v_prezzo_corrente_per_pv
WHERE prezzoCorrente IS NOT NULL
ORDER BY prodottoId, prezzoCorrente;

-- 4) Prezzo minimo per prodotto
SELECT p.nome, v.prezzoMinimo
FROM v_prezzo_minimo_prodotto v
JOIN Prodotto p ON p.id = v.prodottoId
ORDER BY p.nome;

-- 5) Prova trigger sovrapposizioni (DEVE FALLIRE)
-- (scommenta per test)
-- INSERT INTO Prezzo (prodottoId,puntoVenditaId,autoreId,valore,dataInizio,dataFine,fonte)
-- VALUES (1,1,NULL,1.10,'2025-08-20','2025-08-25','punto_vendita'); -- se esiste già un periodo su (1,1) sovrapposto
