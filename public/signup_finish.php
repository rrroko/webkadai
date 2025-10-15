<?php
function db(){ static $pdo;
  if(!$pdo){ $pdo=new PDO('mysql:host=mysql;dbname=appdb;charset=utf8mb4','appuser','apppass',
    [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]); }
  return $pdo;
}
function h($s){return htmlspecialchars((string)$s, ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$pass = (string)($_POST['password'] ?? '');

// ここでは平文のまま保存（暗号化は次回授業）
$stmt = db()->prepare('INSERT INTO users(name,email,password) VALUES(?,?,?)');
$stmt->execute([$name,$email,$pass]);

echo "<h1>会員登録 完了</h1>";
echo "<p>ようこそ、".h($name)." さん</p>";
echo '<p><a href="login.php">ログインへ</a></p>';

