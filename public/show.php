<?php
require __DIR__.'/_bootstrap.php';
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM bbs_entries WHERE id=?');
$stmt->execute([$id]);
$r = $stmt->fetch();
if (!$r) { http_response_code(404); echo 'not found'; exit; }
?><!doctype html>
<html lang="ja"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>#<?php echo $id; ?></title></head>
<body>
<p><a href="/">←戻る</a></p>
<h1>#<?php echo (int)$r['id']; ?></h1>
<p><?php echo link_res_anchors($r['body']); ?></p>
<?php if ($r['image_filename']): ?><img src="/image/<?php echo h($r['image_filename']); ?>" alt=""><?php endif; ?>
<p><?php echo h($r['created_at']); ?></p>
</body></html>
