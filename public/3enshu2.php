<?php
date_default_timezone_set('Asia/Tokyo');
session_start();
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }
function db(){ static $pdo;
  if(!$pdo){
    $pdo = new PDO('mysql:host=mysql;dbname=appdb;charset=utf8mb4','appuser','apppass',
      [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
  } return $pdo;
}

$msg = '';
if (($_POST['email'] ?? '') !== '' && ($_POST['password'] ?? '') !== '') {
  $email = trim($_POST['email']);
  $pass  = (string)$_POST['password'];

  $st = db()->prepare('SELECT id,name,email,password,created_at FROM users WHERE email=? LIMIT 1');
  $st->execute([$email]);
  $u = $st->fetch();

  if (!$u || $u['password'] !== $pass) {        // ※今回は平文比較
    $msg = 'メールまたはパスワードが違います。';
  } else {
    $_SESSION['login_user'] = ['id'=>$u['id'], 'name'=>$u['name'], 'email'=>$u['email']];
    header('Location: 3enshu2.php'); exit;
  }
}

if (isset($_GET['logout'])) {
  $_SESSION = []; session_destroy();
  header('Location: 3enshu2.php'); exit;
}

$login = $_SESSION['login_user'] ?? null;
?>
<h1>3enshu2: ログイン</h1>

<?php if(!$login): ?>
  <?php if ($msg): ?><p style="color:red;"><?=h($msg)?></p><?php endif; ?>
  <form method="post" action="">
    <div>メール: <input name="email" type="email" required></div>
    <div>パスワード: <input name="password" type="password" required></div>
    <button>ログイン</button>
  </form>
  <p><a href="3enshu1.php">新規登録はこちら</a></p>
<?php else: ?>
  <p>ログイン中: <?=h($login['name'])?>（<?=h($login['email'])?>）</p>
  <p><a href="?logout=1">ログアウト</a></p>

  <hr>
  <h2>マイページ</h2>
  <?php
  if (isset($_POST['new_name'])) {
    $new = trim($_POST['new_name']);
    if ($new !== '') {
      $u = $_SESSION['login_user'];
      $st = db()->prepare('UPDATE users SET name=? WHERE id=?');
      $st->execute([$new, $u['id']]);
      $_SESSION['login_user']['name'] = $new;
      echo '<p style="color:green;">名前を更新しました。</p>';
    }
  }
  ?>
  <form method="post">
    <div>新しい名前: <input name="new_name" value="<?=h($_SESSION['login_user']['name'])?>"></div>
    <button>変更</button>
  </form>
<?php endif; ?>

