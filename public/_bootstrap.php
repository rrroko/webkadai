<?php
$pdo = new PDO(
  'mysql:host=mysql;dbname=appdb;charset=utf8mb4',
  'appuser', 'apppass', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]
);

function h($s){ return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function link_res_anchors($text){
  $escaped = h($text);
  return preg_replace('/&gt;&gt;(\d+)/', '<a href="#${1}">&gt;&gt;${1}</a>', $escaped);
}
