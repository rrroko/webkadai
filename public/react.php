<?php
require __DIR__.'/_bootstrap.php';
header('Content-Type: application/json; charset=utf-8');

// Allow CORS within same origin by default; nothing extra

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$kind = isset($_POST['kind']) ? (string)$_POST['kind'] : '';

$allowed = ['heart','plusone','joy','tada'];
if (!$id || !in_array($kind, $allowed, true)) {
  http_response_code(400);
  echo json_encode(['ok'=>false, 'error'=>'bad_request']); exit;
}

// Ensure entry exists
$stmt = $pdo->prepare('SELECT 1 FROM bbs_entries WHERE id=?');
$stmt->execute([$id]);
if (!$stmt->fetchColumn()) { http_response_code(404); echo json_encode(['ok'=>false, 'error'=>'not_found']); exit; }

// Upsert reaction
$pdo->exec('CREATE TABLE IF NOT EXISTS bbs_reactions (
  entry_id BIGINT UNSIGNED NOT NULL,
  kind VARCHAR(16) NOT NULL,
  count INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY(entry_id, kind)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

$stmt = $pdo->prepare('INSERT INTO bbs_reactions(entry_id, kind, count) VALUES(?, ?, 1)
  ON DUPLICATE KEY UPDATE count = count + 1');
$stmt->execute([$id, $kind]);

$stmt = $pdo->prepare('SELECT count FROM bbs_reactions WHERE entry_id=? AND kind=?');
$stmt->execute([$id, $kind]);
$cnt = (int)$stmt->fetchColumn();

echo json_encode(['ok'=>true, 'id'=>$id, 'kind'=>$kind, 'count'=>$cnt]);
