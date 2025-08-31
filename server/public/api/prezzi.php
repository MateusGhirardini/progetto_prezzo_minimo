<?php
require_once __DIR__ . '/../../src/db.php';
require_once __DIR__ . '/../../src/http.php';
require_once __DIR__ . '/../../src/validate.php';
require_once __DIR__ . '/../../src/auth.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

switch ($method) {
  case 'GET': lista_prezzi(); break;
  case 'POST': 
       require_role(['utente','punto_vendita']);
       check_csrf_or_fail();
       crea_prezzo(); 
       break;
  default: json_error(405,'METHOD_NOT_ALLOWED','Metodo non consentito');
}

function lista_prezzi() {
  $pdo = get_pdo();

  $prodottoId = get_query_int('prodotto', null);
  $puntoVenditaId = get_query_int('punto_vendita', null);
  $attivi = get_query_string('attivi', '1'); // default solo attivi

  $where = [];
  $params = [];
  if ($prodottoId) { $where[] = 'p.prodottoId = :pid'; $params[':pid'] = $prodottoId; }
  if ($puntoVenditaId) { $where[] = 'p.puntoVenditaId = :pvid'; $params[':pvid'] = $puntoVenditaId; }
  if ($attivi==='1') { $where[] = 'p.dataFine IS NULL OR p.dataFine >= CURRENT_DATE()'; }

  $sql = "SELECT p.id, p.prodottoId, p.puntoVenditaId, p.valore, p.dataInizio, p.dataFine, pv.nome as puntoVendita
          FROM Prezzo p
          JOIN PuntoVendita pv ON pv.id = p.puntoVenditaId".
          (count($where)?' WHERE '.implode(' AND ',$where):'').
          " ORDER BY p.dataInizio DESC LIMIT 50";

  $st = $pdo->prepare($sql);
  foreach ($params as $k=>$v) $st->bindValue($k,$v);
  $st->execute();
  $rows = $st->fetchAll();

  json_response(['items'=>$rows,'count'=>count($rows)]);
}

function crea_prezzo() {
  $raw = file_get_contents('php://input');
  $data = json_decode($raw,true);
  if (!is_array($data)) json_error(400,'VALIDATION_ERROR','JSON non valido');

  try {
    v_int($data,'id_prodotto',true,'Prodotto');
    v_int($data,'id_punto_vendita',true,'Punto Vendita');
    v_float($data,'prezzo',true,'Prezzo');
    v_date($data,'validita',true,'Data inizio validità');
  } catch (InvalidArgumentException $e) {
    json_error(400,'VALIDATION_ERROR',$e->getMessage());
  }

  $pdo = get_pdo();
  $sql = "INSERT INTO Prezzo (prodottoId,puntoVenditaId,valore,dataInizio,autoreId,fonte)
          VALUES (:pid,:pvid,:val,:dinizio,:autore,:fonte)";
  try {
    $st = $pdo->prepare($sql);
    $st->bindValue(':pid',$data['id_prodotto'],PDO::PARAM_INT);
    $st->bindValue(':pvid',$data['id_punto_vendita'],PDO::PARAM_INT);
    $st->bindValue(':val',$data['prezzo']);
    $st->bindValue(':dinizio',$data['validita']);
    $st->bindValue(':autore',null,PDO::PARAM_NULL); // TODO: session user
    $st->bindValue(':fonte','manuale');
    $st->execute();
    $id=(int)$pdo->lastInsertId();
  } catch(PDOException $e) {
    json_error(500,'SERVER_ERROR','Errore inserimento prezzo',['db'=>$e->getMessage()]);
  }

  json_response(['id'=>$id],201,['Location'=>'/api/prezzi/'.$id]);
}
