<?php
date_default_timezone_set('Asia/Tokyo');
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }
function db(){ static $pdo;
  if(!$pdo){
    $pdo = new PDO('mysql:host=mysql;dbname=appdb;charset=utf8mb4','appuser','apppass',
      [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
  } return $pdo;
}

$ok = null; $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $pass = (string)($_POST['password'] ?? '');

  // 1) 空チェック
  if ($name === '' || $email === '' || $pass === '') {
    $error = '未入力の項目があります。';
  } else {
    // 2) 重複メールチェック
    $cnt = db()->prepare('SELECT COUNT(*) FROM users WHERE email=?');
    $cnt->execute([$email]);
    if ((int)$cnt->fetchColumn() > 0) {
      $error = 'そのメールアドレスは既に登録されています。';
    } else {
      // 3) 登録
      $stmt = db()->prepare('INSERT INTO users(name,email,password) VALUES(?,?,?)');
      $stmt->execute([$name,$email,$pass]);
      $ok = true;
    }
  }
}
?>
<h1>会員登録</h1>
<?php if ($ok): ?>
  <p>登録完了！ ようこそ、<?=h($_POST['name'])?> さん</p>
  <p><a href="3enshu2.php">ログインへ</a></p>
<?php else: ?>
  <?php if ($error): ?><p style="color:red;"><?=h($error)?></p><?php endif; ?>
  <form method="post" action="">
    <div>名前: <input name="name" required value="<?=h($_POST['name']??'')?>"></div>
    <div>メール: <input name="email" type="email" required value="<?=h($_POST['email']??'')?>"></div>
    <div>パスワード: <input name="password" type="password" required></div>
    <button>登録</button>
  </form>
<?php endif; ?>

