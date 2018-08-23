<!-- 出品物削除確認画面  -->

<?php
header("Content-type: text/html; charset=utf-8");

//クリックジャッキング対策
header('X-FRAME-OPTIONS: SAMEORIGIN');

ini_set('display_errors', 1); //最後にコメント化

session_start();

function h($s) {
  return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

//クロスサイトリクエストフォージェリ（CSRF）対策のトークン判定
if ($_POST['token'] != $_SESSION['token']){
  echo "不正アクセスの可能性あり";
  exit();
}

//データベース接続
require_once(__DIR__ .'/../../../../db/db_intern.php');
$dbh = db_connect();

$errors = array();

if(empty($_POST)) {
  header("Location: product.php");
  exit();
}

$username = isset($_POST['username']) ? $_POST['username'] : NULL;  //出品者の名前hiddenで送る
$pname = isset($_POST['pname']) ? $_POST['pname'] : NULL;  //出品物の商品名hiddenで送る

try{
  //静的プレースホルダを用いるようにエミュレーションを無効化
  $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

  //例外処理を投げる（スロー）ようにする
  $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $statement = $dbh->prepare("SELECT * FROM product WHERE username = (:username) AND pname = (:pname)");
  $statement->bindValue(':username', $username, PDO::PARAM_STR);
  $statement->bindValue(':pname', $pname, PDO::PARAM_STR);
  $statement->execute();
  if($statement->rowCount() == 0) {
    $errors['delete'] = "この商品は既に購入または削除されています。";
  }

  //データベース接続切断
  $dbh = null;

}catch (PDOException $e){
  echo "エラーが発生しました。もう一度やりなおして下さい。";
  // echo 'Error:'.$e->getMessage();
  die();
}

?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>削除確認画面|学内フリマ「iCycle」</title>
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

      <?php if (count($errors) === 0): ?>

        <h1>削除確認画面</h1>

        <p><?=h($username);?>さんの商品「<?=h($pname);?>」を出品物から削除しますか？</p>
        <div class="next">
          <form  action="../product.php" method="post">
            <input type="hidden" name="username" value="<?= h($username)?>">
            <input type="hidden" name="pname" value="<?= h($pname)?>">
            <input type="submit" value="戻る">
            <!-- <button type="submit">戻る</button> -->
          </form>
          <form action="delete_finish.php" method="post">
            <input type="hidden" name="username" value="<?=h($username)?>">
            <input type="hidden" name="pname" value="<?=h($pname)?>">
            <input type="hidden" name="token" value="<?=h($_POST['token'])?>">
            <input class="ok_btn" type="submit" value="確定">
            <!-- <input type="button" value="戻る" onClick="history.back()"> -->
          </form>
        </div>
      <?php elseif(count($errors) > 0): ?>

        <h1>エラーメッセージ</h1>

        <?php
        foreach($errors as $value){
          echo "<p>".$value."</p>";
        }
        ?>

        <!-- <input type="button" value="戻る" onClick="history.back()"> -->
        <form  action="../product.php" method="post">
          <input type="hidden" name="username" value="<?= h($username)?>">
          <input type="hidden" name="pname" value="<?= h($pname)?>">
          <button type="submit">戻る</button>
        </form>

      <?php endif; ?>
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
