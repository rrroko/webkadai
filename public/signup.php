<?php
// フォーム表示（POST先は signup_finish.php）
function h($s){return htmlspecialchars((string)$s, ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
?>
<h1>会員登録</h1>
<form method="post" action="signup_finish.php">
  <div>名前: <input name="name" required></div>
  <div>メール: <input name="email" type="email" required></div>
  <div>パスワード: <input name="password" type="password" required></div>
  <button>登録</button>
</form>

