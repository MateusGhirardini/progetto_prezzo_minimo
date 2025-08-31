<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../src/db.php';

$status = ['web' => 'ok', 'db' => 'down'];

try {
  $pdo = get_pdo();
  $pdo->query("SELECT 1");
  $status['db'] = 'ok';
} catch (Throwable $e) {
  $status['db_error'] = $e->getMessage();
}

echo json_encode([
  'service' => 'Prezzo Minimo Supermercati',
  'status'  => $status,
  'time'    => date('c'),
]);
