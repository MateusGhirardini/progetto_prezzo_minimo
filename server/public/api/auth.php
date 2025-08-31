<?php
// server/public/api/auth.php
require_once __DIR__ . '/../../src/db.php';
require_once __DIR__ . '/../../src/http.php';
require_once __DIR__ . '/../../src/validate.php';
require_once __DIR__ . '/../../src/auth.php';

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

/*
  Routing molto semplice:
  POST /api/auth/register
  POST /api/auth/login
  POST /api/auth/logout
  GET  /api/auth/csrf
  GET  /api/auth/me   (facoltativo, utile in debug)
*/
if ($method === 'POST' && preg_match('#/api/auth/register$#', $path)) register();
elseif ($method === 'POST' && preg_match('#/api/auth/login$#', $path)) login();
elseif ($method === 'POST' && preg_match('#/api/auth/logout$#', $path)) logout();
elseif ($method === 'GET'  && preg_match('#/api/auth/csrf$#', $path)) csrf();
elseif ($method === 'GET'  && preg_match('#/api/auth/me$#', $path)) me();
else json_error(404, 'NOT_FOUND', 'Endpoint auth non trovato');

/* ----------------------------- HANDLERS ---------------------------- */
function register() {
  $raw = file_get_contents('php://input');
  $data = json_decode($raw, true);
  if (!is_array($data)) json_error(400,'VALIDATION_ERROR','JSON non valido');

  try {
    v_string($data,'nome',100,true,'Nome');
    v_string($data,'email',150,true,'Email');
    v_string($data,'password',200,true,'Password');
    // ruolo opzionale; default utente
    $ruolo = isset($data['ruolo']) ? trim((string)$data['ruolo']) : 'utente';
    if (!in_array($ruolo, ['utente','punto_vendita','azienda','admin'], true)) {
      $ruolo = 'utente';
    }
  } catch (InvalidArgumentException $e) {
    json_error(400,'VALIDATION_ERROR',$e->getMessage());
  }

  $pdo = get_pdo();

  // Email già esistente?
  $st = $pdo->prepare("SELECT id FROM Utente WHERE email=:email");
  $st->execute([':email'=>$data['email']]);
  if ($st->fetch()) json_error(400,'VALIDATION_ERROR','Email già registrata');

  $hash = password_hash($data['password'], PASSWORD_BCRYPT);

  $ins = $pdo->prepare("INSERT INTO Utente (nome,email,passwordHash,ruolo) VALUES (:n,:e,:h,:r)");
  try {
    $ins->execute([':n'=>$data['nome'], ':e'=>$data['email'], ':h'=>$hash, ':r'=>$ruolo]);
    $id = (int)$pdo->lastInsertId();
  } catch (Throwable $e) {
    json_error(500,'SERVER_ERROR','Errore registrazione');
  }

  json_response(['id'=>$id], 201, ['Location'=>"/api/utenti/$id"]);
}

function login() {
  $raw = file_get_contents('php://input');
  $data = json_decode($raw, true);
  if (!is_array($data)) json_error(400,'VALIDATION_ERROR','JSON non valido');

  try {
    v_string($data,'email',150,true,'Email');
    v_string($data,'password',200,true,'Password');
  } catch (InvalidArgumentException $e) {
    json_error(400,'VALIDATION_ERROR',$e->getMessage());
  }

  $pdo = get_pdo();
  $st = $pdo->prepare("SELECT id,nome,email,passwordHash,ruolo FROM Utente WHERE email=:email");
  $st->execute([':email'=>$data['email']]);
  $u = $st->fetch();
  if (!$u || !password_verify($data['password'], $u['passwordHash'])) {
    json_error(401,'UNAUTHORIZED','Credenziali non valide');
  }

  login_user($u);
  json_response(['user'=>['id'=>$u['id'],'nome'=>$u['nome'],'email'=>$u['email'],'ruolo'=>$u['ruolo']]]);
}

function logout() {
  require_auth();
  logout_user();
  json_response(['logout'=>true]);
}

function csrf() {
  // Fornisce/rigenera il token CSRF per la sessione corrente
  $token = ensure_csrf_token();
  json_response(['csrf_token'=>$token]);
}

function me() {
  $info = current_user_info();
  if (!$info) json_error(401,'UNAUTHORIZED','Non autenticato');
  json_response(['user'=>$info]);
}
