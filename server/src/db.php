<?php
function get_pdo() {
  $host = $_ENV['DB_HOST'] ?? 'db';
  $port = $_ENV['DB_PORT'] ?? '3306';
  $db   = $_ENV['DB_NAME'] ?? 'prezzominimo';
  $user = $_ENV['DB_USER'] ?? 'appuser';
  $pass = $_ENV['DB_PASS'] ?? 'app_pass';

  $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
  $opt = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ];
  return new PDO($dsn, $user, $pass, $opt);
}
