<?php
// server/src/auth.php

// Avvia sessione con cookie sicuri
if (session_status() === PHP_SESSION_NONE) {
  // Imposta nome cookie (opzionale, più chiaro)
  session_name('PMSSESSID');
  // Cookie sicuri (Lax basta per REST con stesso dominio)
  session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax',
    // 'secure' => true, // abilita in HTTPS
  ]);
  session_start();
}

/* ------------------------- UTENTE CORRENTE ------------------------- */
function current_user_id(): ?int { return $_SESSION['uid'] ?? null; }
function current_role(): ?string { return $_SESSION['ruolo'] ?? null; }
function current_user_info(): ?array {
  if (!current_user_id()) return null;
  return [
    'id'    => $_SESSION['uid'],
    'nome'  => $_SESSION['nome'] ?? null,
    'email' => $_SESSION['email'] ?? null,
    'ruolo' => $_SESSION['ruolo'] ?? null,
  ];
}

/* --------------------------- LOGIN/LOGOUT -------------------------- */
function login_user(array $row): void {
  // $row deve avere: id, nome, email, ruolo
  $_SESSION['uid']   = (int)$row['id'];
  $_SESSION['nome']  = $row['nome'] ?? null;
  $_SESSION['email'] = $row['email'] ?? null;
  $_SESSION['ruolo'] = $row['ruolo'] ?? 'utente';
  // rinnova token CSRF ad ogni login
  unset($_SESSION['csrf']);
}

function logout_user(): void {
  $_SESSION = [];
  if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', $params['secure'] ?? false, $params['httponly'] ?? true);
  }
  session_destroy();
}

/* ----------------------------- GUARDIE ----------------------------- */
function require_auth(): void {
  if (!current_user_id()) {
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error'=>['code'=>'UNAUTHORIZED','message'=>'Devi autenticarti']]);
    exit;
  }
}

function require_role(array $roles): void {
  require_auth();
  $role = current_role();
  if (!$role || !in_array($role, $roles, true)) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error'=>['code'=>'FORBIDDEN','message'=>'Permesso negato']]);
    exit;
  }
}

/* ------------------------------ CSRF ------------------------------- */
function ensure_csrf_token(): string {
  if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['csrf'];
}

function check_csrf_or_fail(): void {
  $hdr = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
  $tok = $_SESSION['csrf'] ?? '';
  if (!$tok || !$hdr || !hash_equals($tok, $hdr)) {
    http_response_code(419); // Authentication Timeout / CSRF
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error'=>['code'=>'CSRF_FAILED','message'=>'Token CSRF mancante o non valido']]);
    exit;
  }
}
