<?php
require_once __DIR__ . '/../../src/db.php';
require_once __DIR__ . '/../../src/http.php';
require_once __DIR__ . '/../../src/validate.php';
require_once __DIR__ . '/../../src/auth.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

switch ($method) {
  case 'GET': lista_offerte(); break;
  case 'POST': require_role(['punto_vendita']); 
        crea_offerta(); 
        check_csrf_or_fail();
        break;
  default: json_error(405,'METHOD_NOT_ALLOWED','Metodo non consentito');
}

function lista_offerte() {
  $pdo = get_pdo();
  $sql = "SELECT o.id, o.puntoVenditaId, o.dataInizio,o.dataFine,
                 pv.nome as puntoVendita, count(d.id) as prodotti
          FROM Offerta o
          JOIN PuntoVendita pv ON pv.id=o.puntoVenditaId
          LEFT JOIN DettaglioOfferta d ON d.offertaId=o.id
          GROUP BY o.id,o.puntoVenditaId,o.dataInizio,o.dataFine,pv.nome
          ORDER BY o.dataInizio DESC LIMIT 50";
  $rows = $pdo->query($sql)->fetchAll();
  json_response(['items'=>$rows]);
}

function crea_offerta() {
  $raw = file_get_contents('php://input');
  $data = json_decode($raw,true);
  if (!is_array($data)) json_error(400,'VALIDATION_ERROR','JSON non valido');

  try {
    v_int($data,'id_punto_vendita',true,'Punto Vendita');
    v_date($data,'data_inizio',true,'Data inizio');
    v_date($data,'data_fine',true,'Data fine');
    if (!isset($data['righe']) || !is_array($data['righe']) || count($data['righe'])===0) {
      throw new InvalidArgumentException("Deve contenere almeno una riga prodotto");
    }
  } catch (InvalidArgumentException $e) {
    json_error(400,'VALIDATION_ERROR',$e->getMessage());
  }

  $pdo = get_pdo();
  try {
    $pdo->beginTransaction();
    $st = $pdo->prepare("INSERT INTO Offerta (puntoVenditaId,dataInizio,dataFine) VALUES (:pvid,:dinizio,:dfine)");
    $st->execute([
      ':pvid'=>$data['id_punto_vendita'],
      ':dinizio'=>$data['data_inizio'],
      ':dfine'=>$data['data_fine'],
    ]);
    $offertaId = (int)$pdo->lastInsertId();

    $st2 = $pdo->prepare("INSERT INTO DettaglioOfferta (offertaId,prodottoId,prezzoOfferta) VALUES (:oid,:pid,:prezzo)");
    foreach ($data['righe'] as $r) {
      if (!isset($r['id_prodotto'],$r['prezzo'])) continue;
      $st2->execute([
        ':oid'=>$offertaId,
        ':pid'=>(int)$r['id_prodotto'],
        ':prezzo'=>(float)$r['prezzo'],
      ]);
    }
    $pdo->commit();
  } catch (Throwable $e) {
    $pdo->rollBack();
    json_error(500,'SERVER_ERROR','Errore creazione offerta',['err'=>$e->getMessage()]);
  }

  json_response(['id'=>$offertaId],201,['Location'=>'/api/offerte/'.$offertaId]);
}
