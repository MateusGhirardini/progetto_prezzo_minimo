<?php
// server/src/http.php

function json_response($data, int $status=200, array $headers=[]) {
  http_response_code($status);
  header('Content-Type: application/json; charset=utf-8');
  foreach ($headers as $k=>$v) header("$k: $v");
  echo json_encode($data);
  exit;
}

function json_error(int $status, string $code, string $message, array $details=[]) {
  json_response(['error'=>['code'=>$code,'message'=>$message,'details'=>$details]], $status);
}

function require_method(string $method) {
  if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== $method) {
    json_error(405, 'METHOD_NOT_ALLOWED', 'Metodo non consentito');
  }
}

function get_query_int(string $key, ?int $default=null): ?int {
  if (!isset($_GET[$key]) || $_GET[$key]==='') return $default;
  if (!is_numeric($_GET[$key])) json_error(400,'VALIDATION_ERROR',"Parametro '$key' non numerico");
  return (int)$_GET[$key];
}

function get_query_string(string $key, ?string $default=null): ?string {
  if (!isset($_GET[$key])) return $default;
  return trim((string)$_GET[$key]);
}
