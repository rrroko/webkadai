<?php
date_default_timezone_set('Asia/Tokyo');
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }

$cookie = 'session_id';
$sessionId = $_COOKIE[$cookie] ?? base64_encode(random_bytes(64));
if (!isset($_COOKIE[$cookie])) {
  setcookie($cookie, $sessionId, [
    'path' => '/', 'httponly' => true, 'samesite' => 'Lax'
  ]);
}

$r = new Redis();
$r->connect('redis', 6379, 1.5);

$key  = "session:{$sessionId}";
$data = $r->exists($key) ? json_decode($r->get($key), true) : [];

$prevAt = $data['last_at'] ?? null;
$data['count']  = ($data['count'] ?? 0) + 1;
$data['last_at'] = date('Y-m-d H:i:s');
$r->set($key, json_encode($data, JSON_UNESCAPED_UNICODE));

echo "このセッションでの ".h($data['count'])." 回目のアクセスです。<br>";
echo "前回のアクセス日時: ".h($prevAt ?? '（初回）');


