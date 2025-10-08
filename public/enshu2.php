<?php
function h($s){return htmlspecialchars((string)$s, ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
$r = new Redis(); $r->connect('redis', 6379, 1.5);
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $body = trim($_POST['body'] ?? '');
  $r->set('last_post', $body);
  header('Location: '.$_SERVER['PHP_SELF']); exit;
}
$last = (string)($r->get('last_post') ?? '');
?>
<form method="post">
  <textarea name="body" rows="4" cols="40" required></textarea><br>
  <button>投稿</button>
</form>
<hr>
<p>最新の投稿：</p>
<pre><?= h($last) ?></pre>

