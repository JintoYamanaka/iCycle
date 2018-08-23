<?php session_start();?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>iCycle -岩手大学公式フリマサイト-</title>
  <link href="css/style.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css?family=Bitter:400,700" rel="stylesheet">
  <link href="favicon.ico" rel="shortcut icon">
</head>
<body id="index">
  <header>
    <div class="logo">
      <!-- <a href="./index.php"><img src="images/logo1.png" alt="SNAPPERS" width=150></a> -->
    </div>

    <!--ナビゲーション-->
    <nav>
      <ul class="global-nav">
        <li><a href="all_product.php">全商品を見る</a></li>
        <li><a href="./upload/upload_form.php">出品する</a></li>
        <li><a href="user's_product_list.php">あなたの出品物</a></li>
        <li><a href="./contact.html">お問い合わせ</a></li>
        <!-- <li><a href="contact.html">お問い合わせ</a></li> -->
      </ul>
    </nav>
  </header>
  <div id="wrap">
    <div class="content">
      <h1>Make Cycle by iCycle</h1>
      <p>「iCycle」は、岩手大学公式フリーマーケットWEBサービスです。<br>岩手大学関係者は様々なジャンルのアイテムを出品・購入できます。</p>

      <!--ログイン画面へ誘導-->
      <?php if (!isset($_SESSION['username'])):?>
        <p class="btn"><a href="./registration/registration_mail_form.php">新規登録</a></p>
        <p class="btn"><a href="./login/login_form.php">ログイン</a></p>
        <!-- <?php var_dump($_SESSION['username']);?> -->
      <?php else:?>
        <p class="btn"><a href="logout.php">ログアウト</a></p>
      <?php endif;?>
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
