<?php
require __DIR__.'/_bootstrap.php';
$q = trim($_GET['q'] ?? '');
$rows = [];
if ($q !== '') {
  $stmt = $pdo->prepare('SELECT * FROM bbs_entries WHERE body LIKE ? ORDER BY id DESC LIMIT 100');
  $stmt->execute(['%'.$q.'%']);
  $rows = $stmt->fetchAll();
}
?><!doctype html>
<html lang="ja"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>検索</title></head>
<body>
<form>
  <input name="q" value="<?php echo h($q); ?>" placeholder="本文を検索">
  <button>検索</button>
  <a href="/">一覧へ</a>
</form>
<ul>
<?php foreach ($rows as $r): ?>
  <li>
    <a href="/show.php?id=<?php echo (int)$r['id']; ?>">#<?php echo (int)$r['id']; ?></a>
    <?php echo link_res_anchors($r['body']); ?>
  </li>
<?php endforeach; ?>
</ul>
</body></html>
