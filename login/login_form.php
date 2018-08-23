<?php
ini_set('display_errors', 1); //最後にコメント化

session_start();

header("Content-type: text/html; charset=utf-8");

//クリックジャッキング対策
header('X-FRAME-OPTIONS: SAMEORIGIN');

//クロスサイトリクエストフォージェリ（CSRF）対策
$_SESSION['token'] = base64_encode(openssl_random_pseudo_bytes(32));
$token = $_SESSION['token'];

?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>ログイン画面|学内フリマ「iCycle」</title>
  <link href="../css/style.css" rel="stylesheet">
</head>
<body id="allpage">
  <header>
    <div class="logo">
      <a href="../index.php"><img src="../images/logo1.png" alt="SNAPPERS"></a>
    </div>
    <nav>
      <ul class="global-nav">
        <li><a href="../all_product.php">全商品を見る</a></li>
        <li><a href="../upload/upload_form.php">出品する</a></li>
        <li><a href="../user's_product_list.php">あなたの出品物</a></li>
        <li><a href="../contact.html">お問い合わせ</a></li>
      </ul>
    </nav>
  </header>
  <div class="content">
    <div class="main-center">

      <h1>ログイン画面</h1>

      <form action="login_check.php" method="post">

        <p>ユーザ名：<input type="text" name="username" size="50"></p>
        <p>パスワード：<input type="password" name="password" size="50"></p>

        <input type="hidden" name="token" value="<?=htmlspecialchars($token, ENT_QUOTES, 'UTF-8')?>">
        <input type="submit" value="ログイン">

      </form>
    </div>
  </div>
  <footer>
    <small>利用規約　</small>
    <small>プライバシーポリシー　</small>
    <small>会社概要<br></small>
    <small>(c) 2017 iFive.inc</small>
  </footer>
</body>
</html>
