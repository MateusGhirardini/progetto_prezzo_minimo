<?php
// server/public/api/prodotti.php
require_once __DIR__ . '/../../src/db.php';
require_once __DIR__ . '/../../src/http.php';
require_once __DIR__ . '/../../src/validate.php';
require_once __DIR__ . '/../../src/auth.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

switch ($method) {
  case 'GET':
    $id = get_query_int('id', null);
    if ($id !== null) dettaglio_prodotto($id); else lista_prodotti();
    break;

  case 'POST':
    // Solo azienda/admin (in dev non blocchiamo, vedi auth.php)
    require_role(['azienda','admin']);
    crea_prodotto();
    break;

  case 'PUT':
  case 'PATCH':
    require_role(['azienda','admin']);
    $id = get_query_int('id', null);
    if ($id === null) {
      json_error(400, 'VALIDATION_ERROR', "Manca l'id prodotto nell'URL (/api/prodotti/{id})");
    }
    aggiorna_prodotto($id);
    break;

  default:
    json_error(405, 'METHOD_NOT_ALLOWED', 'Metodo non consentito');
}

// -------- GET: LISTA --------
function lista_prodotti() {
  $pdo = get_pdo();

  $page = max(1, (int)(get_query_int('page', 1)));
  $pageSize = (int)(get_query_int('page_size', 20));
  if ($pageSize < 1) $pageSize = 20;
  if ($pageSize > 100) $pageSize = 100;
  $offset = ($page - 1) * $pageSize;

  $q = get_query_string('q', '');
  $where = '';
  $params = [];
  if ($q !== '') {
    $where = 'WHERE p.nome LIKE :q OR p.marca LIKE :q';
    $params[':q'] = '%'.$q.'%';
  }

  $sort = get_query_string('sort', 'nome_asc');
  $orderMap = [
    'nome_asc' => 'p.nome ASC',
    'nome_desc'=> 'p.nome DESC',
    'marca_asc'=> 'p.marca ASC',
    'marca_desc'=>'p.marca DESC',
    'ean_asc'  => 'p.codiceEAN ASC',
    'ean_desc' => 'p.codiceEAN DESC',
  ];
  $orderBy = $orderMap[$sort] ?? $orderMap['nome_asc'];

  $st = $pdo->prepare("SELECT COUNT(*) FROM Prodotto p $where");
  foreach ($params as $k=>$v) $st->bindValue($k,$v);
  $st->execute();
  $total = (int)$st->fetchColumn();

  $sql = "SELECT p.id, p.nome, p.marca, p.codiceEAN, p.fotoURL
          FROM Prodotto p
          $where
          ORDER BY $orderBy
          LIMIT :limit OFFSET :offset";
  $st = $pdo->prepare($sql);
  foreach ($params as $k=>$v) $st->bindValue($k,$v);
  $st->bindValue(':limit', $pageSize, PDO::PARAM_INT);
  $st->bindValue(':offset', $offset, PDO::PARAM_INT);
  $st->execute();
  $items = $st->fetchAll();

  $headers = [
    'X-Total-Count' => (string)$total,
    'X-Page'        => (string)$page,
    'X-Page-Size'   => (string)$pageSize,
  ];

  json_response(['items'=>$items,'page'=>$page,'page_size'=>$pageSize,'total'=>$total], 200, $headers);
}

// -------- GET: DETTAGLIO --------
function dettaglio_prodotto(int $id) {
  $pdo = get_pdo();
  $st = $pdo->prepare("SELECT p.id, p.nome, p.marca, p.codiceEAN, p.descrizione, p.fotoURL, p.aziendaId
                       FROM Prodotto p WHERE p.id = :id");
  $st->bindValue(':id', $id, PDO::PARAM_INT);
  $st->execute();
  $row = $st->fetch();
  if (!$row) json_error(404, 'NOT_FOUND', 'Prodotto non trovato', ['id'=>$id]);
  json_response($row);
}

// -------- POST: CREA --------
function crea_prodotto() {
  $raw = file_get_contents('php://input');
  $data = json_decode($raw, true);
  if (!is_array($data)) json_error(400, 'VALIDATION_ERROR', 'JSON non valido');

  try {
    // Campi obbligatori minimi: nome, marca
    v_string($data, 'nome', 150, true, 'Nome');
    v_string($data, 'marca', 100, true, 'Marca');

    // Opzionali
    v_string($data, 'codiceEAN', 20, false, 'Codice EAN', true); // solo cifre se presente
    v_string($data, 'descrizione', 1000, false, 'Descrizione');
    v_string($data, 'fotoURL', 500, false, 'Foto URL');
    v_int($data, 'aziendaId', false, 'Azienda');

  } catch (InvalidArgumentException $e) {
    json_error(400, 'VALIDATION_ERROR', $e->getMessage());
  }

  $pdo = get_pdo();
  $sql = "INSERT INTO Prodotto (nome, marca, codiceEAN, descrizione, fotoURL, aziendaId)
          VALUES (:nome, :marca, :ean, :descr, :foto, :azienda)";
  try {
    $st = $pdo->prepare($sql);
    $st->bindValue(':nome',  $data['nome']);
    $st->bindValue(':marca', $data['marca']);
    $st->bindValue(':ean',   $data['codiceEAN'] ?? null);
    $st->bindValue(':descr', $data['descrizione'] ?? null);
    $st->bindValue(':foto',  $data['fotoURL'] ?? null);
    // se non passato, settiamo NULL (accettato solo se schema lo consente)
    if (array_key_exists('aziendaId', $data) && $data['aziendaId'] !== null) {
      $st->bindValue(':azienda', $data['aziendaId'], PDO::PARAM_INT);
    } else {
      $st->bindValue(':azienda', null, PDO::PARAM_NULL);
    }
    $st->execute();
    $id = (int)$pdo->lastInsertId();

  } catch (PDOException $e) {
    $msg = $e->getMessage();
    // Violazione UNIQUE (EAN duplicato) o FK
    if ($e->getCode() === '23000') {
      if (stripos($msg, 'codiceEAN') !== false || stripos($msg, 'Duplicate') !== false) {
        json_error(409, 'CONFLICT', 'codiceEAN già esistente');
      }
      if (stripos($msg, 'foreign key') !== false) {
        json_error(400, 'FK_VIOLATION', 'Azienda inesistente o non valida');
      }
    }
    json_error(500, 'SERVER_ERROR', 'Errore DB in inserimento');
  }

  $location = '/api/prodotti/'.$id;
  json_response(['id'=>$id, 'location'=>$location], 201, ['Location'=>$location]);
}

// -------- PUT/PATCH: AGGIORNA --------
function aggiorna_prodotto(int $id) {
  $raw = file_get_contents('php://input');
  $data = json_decode($raw, true);
  if (!is_array($data)) json_error(400, 'VALIDATION_ERROR', 'JSON non valido');

  // Verifica esistenza
  $pdo = get_pdo();
  $chk = $pdo->prepare("SELECT id FROM Prodotto WHERE id=:id");
  $chk->bindValue(':id',$id,PDO::PARAM_INT);
  $chk->execute();
  if (!$chk->fetchColumn()) json_error(404,'NOT_FOUND','Prodotto non trovato',['id'=>$id]);

  // Normalizza campi (tutti opzionali in PUT parziale)
  try {
    if (array_key_exists('nome',$data))        v_string($data,'nome',150,false,'Nome');
    if (array_key_exists('marca',$data))       v_string($data,'marca',100,false,'Marca');
    if (array_key_exists('codiceEAN',$data))   v_string($data,'codiceEAN',20,false,'Codice EAN',true);
    if (array_key_exists('descrizione',$data)) v_string($data,'descrizione',1000,false,'Descrizione');
    if (array_key_exists('fotoURL',$data))     v_string($data,'fotoURL',500,false,'Foto URL');
    if (array_key_exists('aziendaId',$data))   v_int($data,'aziendaId',false,'Azienda');
  } catch (InvalidArgumentException $e) {
    json_error(400,'VALIDATION_ERROR',$e->getMessage());
  }

  // Costruisci UPDATE dinamico solo con i campi presenti
  $fieldsMap = [
    'nome' => ':nome',
    'marca'=> ':marca',
    'codiceEAN'=> ':ean',
    'descrizione'=> ':descr',
    'fotoURL'=> ':foto',
    'aziendaId'=> ':azienda'
  ];
  $set = [];
  $params = [':id'=>$id];

  foreach ($fieldsMap as $k=>$ph) {
    if (array_key_exists($k,$data)) {
      $set[] = "$k = $ph";
      if ($k === 'aziendaId') {
        if ($data[$k] === null) $params[$ph] = null;
        else $params[$ph] = (int)$data[$k];
      } else {
        $params[$ph] = $data[$k];
      }
    }
  }

  if (empty($set)) {
    json_error(400, 'VALIDATION_ERROR', 'Nessun campo da aggiornare');
  }

  $sql = "UPDATE Prodotto SET ".implode(', ',$set)." WHERE id = :id";

  try {
    $st = $pdo->prepare($sql);
    foreach ($params as $k=>$v) {
      if ($k === ':azienda' && $v === null) $st->bindValue($k, null, PDO::PARAM_NULL);
      else $st->bindValue($k, $v);
    }
    $st->execute();
  } catch (PDOException $e) {
    $msg = $e->getMessage();
    if ($e->getCode() === '23000') {
      if (stripos($msg, 'codiceEAN') !== false || stripos($msg, 'Duplicate') !== false) {
        json_error(409, 'CONFLICT', 'codiceEAN già esistente');
      }
      if (stripos($msg, 'foreign key') !== false) {
        json_error(400, 'FK_VIOLATION', 'Azienda inesistente o non valida');
      }
    }
    json_error(500, 'SERVER_ERROR', 'Errore DB in aggiornamento');
  }

  json_response(['updated'=>true]);
}
