<?php
// server/public/api/prodotti.php
require_once __DIR__ . '/../../src/db.php';
require_once __DIR__ . '/../../src/http.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method !== 'GET') {
  json_error(405, 'METHOD_NOT_ALLOWED', 'Solo GET è consentito');
}

// Se c'è ?id=... → dettaglio; altrimenti lista
$id = get_query_int('id', null);
if ($id !== null) {
  dettaglio_prodotto($id);
} else {
  lista_prodotti();
}

// -------- funzioni --------
function lista_prodotti() {
  $pdo = get_pdo();

  // Paginazione
  $page = max(1, (int)(get_query_int('page', 1)));
  $pageSize = (int)(get_query_int('page_size', 20));
  if ($pageSize < 1) $pageSize = 20;
  if ($pageSize > 100) $pageSize = 100;
  $offset = ($page - 1) * $pageSize;

  // Ricerca testuale
  $q = get_query_string('q', '');
  $where = '';
  $params = [];
  if ($q !== '') {
    $where = 'WHERE p.nome LIKE :q OR p.marca LIKE :q';
    $params[':q'] = '%'.$q.'%';
  }

  // Ordinamento (solo whitelist)
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

  // Conteggio totale
  $sqlCount = "SELECT COUNT(*) AS cnt FROM Prodotto p $where";
  $st = $pdo->prepare($sqlCount);
  foreach ($params as $k=>$v) $st->bindValue($k,$v);
  $st->execute();
  $total = (int)$st->fetchColumn();

  // Dati
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

  // Header paginazione facoltativi
  $headers = [
    'X-Total-Count' => (string)$total,
    'X-Page'        => (string)$page,
    'X-Page-Size'   => (string)$pageSize,
  ];

  json_response([
    'items' => $items,
    'page' => $page,
    'page_size' => $pageSize,
    'total' => $total
  ], 200, $headers);
}

function dettaglio_prodotto(int $id) {
  $pdo = get_pdo();
  $st = $pdo->prepare("SELECT p.id, p.nome, p.marca, p.codiceEAN, p.descrizione, p.fotoURL, p.aziendaId
                       FROM Prodotto p WHERE p.id = :id");
  $st->bindValue(':id', $id, PDO::PARAM_INT);
  $st->execute();
  $row = $st->fetch();
  if (!$row) {
    json_error(404, 'NOT_FOUND', 'Prodotto non trovato', ['id'=>$id]);
  }
  json_response($row);
}
