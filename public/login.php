<?php
function h($s){return htmlspecialchars((string)$s, ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
?>
<h1>ログイン</h1>
<form method="post" action="login_finish.php">
  <div>メール: <input name="email" type="email" required></div>
  <div>パスワード: <input name="password" type="password" required></div>
  <button>ログイン</button>
</form>

