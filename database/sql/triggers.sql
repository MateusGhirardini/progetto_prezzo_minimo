USE prezzominimo;

-- Drop sicuro (ri-eseguibile)
DROP TRIGGER IF EXISTS before_insert_prezzo_no_overlap;
DROP TRIGGER IF EXISTS before_update_prezzo_no_overlap;

DELIMITER $$

-- Impedisci sovrapposizioni su INSERT in Prezzo
CREATE TRIGGER before_insert_prezzo_no_overlap
BEFORE INSERT ON Prezzo
FOR EACH ROW
BEGIN
  DECLARE v_count INT DEFAULT 0;
  DECLARE v_di DATE;
  DECLARE v_df DATE;

  SET v_di = NEW.dataInizio;
  SET v_df = IFNULL(NEW.dataFine, '9999-12-31');

  SELECT COUNT(*)
    INTO v_count
    FROM Prezzo p
   WHERE p.prodottoId = NEW.prodottoId
     AND p.puntoVenditaId = NEW.puntoVenditaId
     -- Overlap se NON (fine_esistente < inizio_nuovo OR inizio_esistente > fine_nuovo)
     AND NOT (IFNULL(p.dataFine,'9999-12-31') < v_di
              OR p.dataInizio > v_df);

  IF v_count > 0 THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Periodo prezzo sovrapposto per lo stesso prodotto e punto vendita';
  END IF;
END$$

-- Impedisci sovrapposizioni su UPDATE in Prezzo
CREATE TRIGGER before_update_prezzo_no_overlap
BEFORE UPDATE ON Prezzo
FOR EACH ROW
BEGIN
  DECLARE v_count INT DEFAULT 0;
  DECLARE v_di DATE;
  DECLARE v_df DATE;

  SET v_di = NEW.dataInizio;
  SET v_df = IFNULL(NEW.dataFine, '9999-12-31');

  SELECT COUNT(*)
    INTO v_count
    FROM Prezzo p
   WHERE p.prodottoId = NEW.prodottoId
     AND p.puntoVenditaId = NEW.puntoVenditaId
     AND p.id <> NEW.id
     AND NOT (IFNULL(p.dataFine,'9999-12-31') < v_di
              OR p.dataInizio > v_df);

  IF v_count > 0 THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Periodo prezzo sovrapposto (UPDATE) per lo stesso prodotto e punto vendita';
  END IF;
END$$

DELIMITER ;
