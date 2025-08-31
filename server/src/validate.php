<?php
// server/src/validate.php

function v_trim(?string $s, int $maxLen=null): ?string {
  if ($s === null) return null;
  $s = trim($s);
  if ($maxLen !== null && mb_strlen($s) > $maxLen) {
    return mb_substr($s, 0, $maxLen);
  }
  return $s === '' ? null : $s;
}

function v_required(array $data, array $fields): array {
  $errors = [];
  foreach ($fields as $f) {
    if (!isset($data[$f]) || $data[$f] === null || $data[$f] === '') {
      $errors[$f] = 'Campo obbligatorio';
    }
  }
  return $errors;
}

function v_string(array &$data, string $key, int $maxLen=255, bool $required=false, string $label=null, bool $onlyDigits=false): ?string {
  $label = $label ?? $key;
  $val = $data[$key] ?? null;
  $val = is_string($val) ? trim($val) : $val;
  if ($val === null || $val === '') {
    if ($required) throw new InvalidArgumentException("$label è obbligatorio");
    return null;
  }
  if ($onlyDigits && !preg_match('/^\d+$/', $val)) {
    throw new InvalidArgumentException("$label deve contenere solo cifre");
  }
  if (mb_strlen($val) > $maxLen) {
    throw new InvalidArgumentException("$label troppo lungo (max $maxLen)");
  }
  $data[$key] = $val;
  return $val;
}

function v_int(array &$data, string $key, bool $required=false, string $label=null): ?int {
  $label = $label ?? $key;
  if (!isset($data[$key]) || $data[$key] === '' || $data[$key] === null) {
    if ($required) throw new InvalidArgumentException("$label è obbligatorio");
    return null;
  }
  if (!is_numeric($data[$key])) throw new InvalidArgumentException("$label deve essere numerico");
  $data[$key] = (int)$data[$key];
  return $data[$key];
}
