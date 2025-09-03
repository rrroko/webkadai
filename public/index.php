<?php
require __DIR__.'/_bootstrap.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $body = trim($_POST['body'] ?? '');
  if ($body === '') { $errors[] = '本文を入力してください'; }

  $filename = null;
  if (!empty($_FILES['image']['name'])) {
    if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
      if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
        $errors[] = '画像は5MB以下にしてください';
      } else {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($_FILES['image']['tmp_name']);
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
        if (!isset($allowed[$mime])) {
          $errors[] = '画像ファイルのみアップロードできます';
        } else {
          $ext = $allowed[$mime];
          $filename = bin2hex(random_bytes(8)).".$ext";
          $dest = '/var/www/upload/image/'.$filename;
          if (!move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
            $errors[] = '画像の保存に失敗しました';
          }
        }
      }
    } elseif ($_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
      $errors[] = '画像アップロードエラー';
    }
  }

  if (!$errors) {
    $stmt = $pdo->prepare('INSERT INTO bbs_entries(body, image_filename) VALUES(?, ?)');
    $stmt->execute([$body, $filename]);
    header('Location: /');
    exit;
  }
}

$per = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $per;
$total = (int)$pdo->query('SELECT COUNT(*) FROM bbs_entries')->fetchColumn();
$pages = max(1, (int)ceil($total / $per));

$rows = $pdo->query("SELECT * FROM bbs_entries ORDER BY id DESC LIMIT $per OFFSET $offset")->fetchAll();
$ids = array_column($rows, 'id');
$reactions = [];
if ($ids) {
  $in = implode(',', array_fill(0, count($ids), '?'));
  $stmt = $pdo->prepare("SELECT entry_id, kind, count FROM bbs_reactions WHERE entry_id IN ($in)");
  $stmt->execute($ids);
  foreach ($stmt as $row) {
    $entryId = (int)$row['entry_id'];
    $kind = (string)$row['kind'];
    $cnt = (int)$row['count'];
    if (!isset($reactions[$entryId])) $reactions[$entryId] = [];
    $reactions[$entryId][$kind] = $cnt;
  }
}
function react_count($reactions, $id, $kind){
  return isset($reactions[$id][$kind]) ? (int)$reactions[$id][$kind] : 0;
}
?>
<!doctype html>
<html lang="ja">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>掲示板</title>
<link rel="stylesheet" href="/assets/style.css">
<script defer src="/assets/app.js"></script>
</head>
<body>
<main class="wrap">
  <h1>掲示板</h1>

  <?php if ($errors): ?>
  <div class="errors">
    <ul>
      <?php foreach ($errors as $e): ?>
        <li><?php echo h($e); ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
  <?php endif; ?>


  <form method="post" enctype="multipart/form-data" id="post-form">
    <textarea name="body" rows="4" placeholder="本文" required></textarea>
    <div class="row">
      <input type="file" name="image" id="image" accept="image/*">
      <button type="submit">投稿</button>
    </div>
    <p class="hint">※ 画像は自動的に 5MB 以下に縮小されます</p>
  </form>

  <hr>

  <ol class="list">
    <?php foreach ($rows as $r): ?>
      <li id="<?php echo (int)$r['id']; ?>">
        <header>
          <span class="id">#<?php echo (int)$r['id']; ?></span>
          <time><?php echo h($r['created_at']); ?></time>
        </header>
        <div class="body"><?php echo link_res_anchors($r['body']); ?></div>
        <?php if ($r['image_filename']): ?>
          <figure class="thumb"><img src="/image/<?php echo h($r['image_filename']); ?>" alt=""></figure>
        <?php endif; ?>
        <div class="reactions" data-id="<?php echo (int)$r['id']; ?>">
          <button class="react" data-kind="heart" type="button">♥ <span class="cnt"><?php echo react_count($reactions, (int)$r['id'], 'heart'); ?></span></button>
          <button class="react" data-kind="plusone" type="button">👍 <span class="cnt"><?php echo react_count($reactions, (int)$r['id'], 'plusone'); ?></span></button>
          <button class="react" data-kind="joy" type="button">😂 <span class="cnt"><?php echo react_count($reactions, (int)$r['id'], 'joy'); ?></span></button>
          <button class="react" data-kind="tada" type="button">🎉 <span class="cnt"><?php echo react_count($reactions, (int)$r['id'], 'tada'); ?></span></button>
        </div>
      </li>
    <?php endforeach; ?>
  </ol>

  <nav class="pager">
    <?php for ($i=1; $i<=$pages; $i++): ?>
      <a class="<?php echo $i===$page?'current':''; ?>" href="/?page=<?php echo $i; ?>"><?php echo $i; ?></a>
    <?php endfor; ?>
  </nav>
</main>
</body>
</html>
