<?php
// server/src/auth.php

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

function current_role(): ?string {
  // In attesa del Giorno 20 (login), in dev consenti tutto.
  // Se vuoi simulare un ruolo: $_SESSION['ruolo'] = 'admin';
  return $_SESSION['ruolo'] ?? null;
}

function require_role(array $roles) {
  $env = $_ENV['APP_ENV'] ?? 'local';
  if ($env === 'local') {
    // In DEV non blocchiamo: utile per sviluppare prima del login
    return;
  }
  $role = current_role();
  if (!$role || !in_array($role, $roles, true)) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error'=>['code'=>'FORBIDDEN','message'=>'Permesso negato']]);
    exit;
  }
}
