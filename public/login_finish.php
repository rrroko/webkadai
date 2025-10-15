<?php
session_start();
function db(){ static $pdo;
  if(!$pdo){ $pdo=new PDO('mysql:host=mysql;dbname=appdb;charset=utf8mb4','appuser','apppass',
    [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]); }
  return $pdo;
}
function h($s){return htmlspecialchars((string)$s, ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}

$email = trim($_POST['email'] ?? '');
$pass  = (string)($_POST['password'] ?? '');

$stmt = db()->prepare('SELECT id,name,email,password,created_at FROM users WHERE email=? LIMIT 1');
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$user || $user['password'] !== $pass){
  echo "<p>メールまたはパスワードが違います。</p>";
  echo '<p><a href="login.php">戻る</a></p>';
  exit;
}

// セッションにログイン情報を保存
$_SESSION['login_user'] = [
  'id'=>$user['id'],'name'=>$user['name'],'email'=>$user['email']
];

echo "<h1>ログイン完了</h1>";
echo "<p>ようこそ、".h($user['name'])." さん</p>";
