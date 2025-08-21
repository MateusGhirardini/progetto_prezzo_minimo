# Controlli di Qualità DB – Giorno 13

## Trigger anti-sovrapposizione (Prezzo)
- Impediscono l’inserimento/aggiornamento di periodi che si sovrappongono per la stessa coppia (prodotto, punto vendita).
- Errore: `SQLSTATE 45000` con messaggio esplicativo.

## Viste
- `v_offerte_attive`: righe di volantino attive oggi.
- `v_prezzi_attivi`: prezzi non scaduti.
- `v_prezzo_corrente_per_pv`: applica priorità Offerta > Prezzo PV > Prezzo Utente.
- `v_prezzo_minimo_prodotto`: prezzo minimo corrente per prodotto.

## Test rapidi
- Esegui `database/sql/test_queries.sql` e verifica output non vuoto.
- Prova un INSERT sovrapposto su `Prezzo`: deve fallire.
