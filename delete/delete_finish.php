<!-- 削除完了画面  -->

<?php
header("Content-type: text/html; charset=utf-8");

//クリックジャッキング対策
header('X-FRAME-OPTIONS: SAMEORIGIN');

define('MAIL', 'yamajin-ips11@ezweb.ne.jp'); // 運営のメールアドレス
define('NAME', '学内カンパニー'); // 運営の名前
define('UPLOAD_DIR', __DIR__ . '/upload');
define('IMAGES_DIR', __DIR__ . '/images');

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

$username = isset($_POST['username']) ? $_POST['username'] : NULL;  //出品者の名前、これでメールアドレスを持ってくる
$pname = isset($_POST['pname']) ? $_POST['pname'] : NULL;  //出品物の商品名、メールで使用


try{
  //静的プレースホルダを用いるようにエミュレーションを無効化
  $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

  //例外処理を投げる（スロー）ようにする
  $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  //存在確認
  $statement = $dbh->prepare("SELECT * FROM product WHERE username = (:username) AND pname = (:pname)");
  $statement->bindValue(':username', $username, PDO::PARAM_STR);
  $statement->bindValue(':pname', $pname, PDO::PARAM_STR);
  $statement->execute();
  if($statement->rowCount() == 0) {
    $errors['delete'] = "この商品は既に購入または削除されています。";
  }

  if(count($errors) === 0){

    //トランザクション開始
    $dbh->beginTransaction();

    //商品情報をデータベースから削除
    $statement = $dbh->prepare("DELETE FROM product WHERE username = (:username) AND pname = (:pname)");
    $statement->bindValue(':username', $username, PDO::PARAM_STR);
    $statement->bindValue(':pname', $pname, PDO::PARAM_STR);
    $statement->execute();

    // トランザクション完了（コミット）
    $dbh->commit();

    //出品者のメールアドレスを取って来る
    $statement = $dbh->prepare("SELECT mail FROM member WHERE username = :username");
    $statement->bindValue(':username', $username, PDO::PARAM_STR);
    $statement->execute();
    $mail_array = $statement->fetch();
    $mailTo = $mail_array['mail'];

    //削除完了のお知らせを出品者へ送信

    //Return-Pathに指定するメールアドレス
    $returnMail = MAIL;   //エラーメールの送信先（運営）

    $subject = "【iCycle】出品物削除完了のお知らせ";  //題名
    //メールの内容
    $body = <<< EOM
    商品名「{$pname}」の出品を削除しました。
EOM;

    mb_language('ja');
    mb_internal_encoding('UTF-8');

    //Fromヘッダーを作成
    $header = 'From: ' . mb_encode_mimeheader(NAME). ' <' . MAIL. '>';

    mb_send_mail($mailTo, $subject, $body, $header, '-f'. $returnMail);

    //データベース接続切断
    $dbh = null;

    unset($_SESSION['token']);
  }

}catch (PDOException $e){
  //トランザクション取り消し（ロールバック）
  $dbh->rollBack();
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
  <title>削除完了画面|学内フリマ「iCycle」</title>
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

        <h1>削除完了画面</h1>

        <p>削除完了のメールをお送りしました。ご確認下さい。</p>

        <p>出品物の削除が完了しました。</p>
        <p><a href="/../intern/iCycle/index.php">ホームへ戻る</a></p>

      <?php elseif(count($errors) > 0): ?>

        <h1>エラーメッセージ</h1>

        <?php
        foreach($errors as $value){
          echo "<p>".$value."</p>";
        }
        ?>

        <input type="button" value="戻る" onClick="history.back()">

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
