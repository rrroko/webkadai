<?php
function h($s){return htmlspecialchars((string)$s, ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
$r = new Redis(); $r->connect('redis', 6379, 1.5);
$key = 'posts_json';      // JSONで配列保存

if ($_SERVER['REQUEST_METHOD']==='POST') {
  $body = trim($_POST['body'] ?? '');
  $posts = json_decode($r->get($key) ?: '[]', true);
  $posts[] = ['body'=>$body, 'created_at'=>date('Y-m-d H:i:s')];
  $r->set($key, json_encode($posts, JSON_UNESCAPED_UNICODE));
  header('Location: '.$_SERVER['PHP_SELF']); exit;
}

$posts = json_decode($r->get($key) ?: '[]', true);
?>
<form method="post">
  <textarea name="body" rows="4" cols="40" required></textarea><br>
  <button>投稿</button>
</form>
<hr>
<ol>
<?php foreach (array_reverse($posts) as $p): ?>
  <li>
    <div><?= h($p['body'] ?? '') ?></div>
    <small><?= h($p['created_at'] ?? '') ?></small>
  </li>
<?php endforeach; ?>
</ol>

