<!-- 出品完了 -->

<?php
header("Content-type: text/html; charset=utf-8");

//クリックジャッキング対策
header('X-FRAME-OPTIONS: SAMEORIGIN');

define('MAIL', 'yamajin-ips11@ezweb.ne.jp'); // 運営のメールアドレス
define('NAME', '学内カンパニー'); // 運営の名前
// define('IMAGES_DIR', __DIR__ . '/images');
// define('THUMBNAIL_DIR', __DIR__ . '/thumbs');

function h($s) {
  return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

session_start();

//クロスサイトリクエストフォージェリ（CSRF）対策のトークン判定
if ($_POST['token'] != $_SESSION['token']){
  echo "不正アクセスの可能性あり";
  exit();
}

//データベース接続
require_once(__DIR__ .'/../../../../db/db_intern.php');
$dbh = db_connect();

//エラーメッセージの初期化
$errors = array();

if(empty($_POST)) {
  header("Location: upload_form.php");
  exit();
}

$username = $_SESSION['username'];
$image0 = $_SESSION['image0'] ;
$image1 = $_SESSION['image1'] ;
$image2 = $_SESSION['image2'] ;
$image3 = $_SESSION['image3'] ;
$pname = $_SESSION['pname'];
$category = $_SESSION['category'];
$condition = $_SESSION['condition'];
// $place = $_SESSION['place'];        //消す可能性あり
$explanation = $_SESSION['explanation'];
$price = $_SESSION['price'];

try{
  //静的プレースホルダを用いるようにエミュレーションを無効化
  $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

  //例外処理を投げる（スロー）ようにする
  $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  //ユーザのメールアドレスを取って来る
  $statement = $dbh->prepare("SELECT mail FROM member WHERE username = :username");
  $statement->bindValue(':username', $username, PDO::PARAM_STR);
  $statement->execute();
  $mail_array = $statement->fetch();   //ここ参考に！！
  $mail = $mail_array['mail'];

  //トランザクション開始
  $dbh->beginTransaction();

  //productテーブルに本登録する
  $statement = $dbh->prepare("INSERT INTO product (username,image0,image1,image2,image3,pname,category,_condition,explanation,price,date)
  VALUES (:username,:image0,:image1,:image2,:image3,:pname,:category,:condition,:explanation,:price,now())");   //placeは消すかも
  //プレースホルダへ実際の値を設定する
  $statement->bindValue(':image0', $image0, PDO::PARAM_STR);
  $statement->bindValue(':image1', $image1, PDO::PARAM_STR);
  $statement->bindValue(':image2', $image2, PDO::PARAM_STR);
  $statement->bindValue(':image3', $image3, PDO::PARAM_STR);
  $statement->bindValue(':username', $username, PDO::PARAM_STR);
  $statement->bindValue(':pname', $pname, PDO::PARAM_STR);
  $statement->bindValue(':category', $category, PDO::PARAM_STR);
  $statement->bindValue(':condition', $condition, PDO::PARAM_STR);
  // $statement->bindValue(':place', $place, PDO::PARAM_STR);          //消すかも
  $statement->bindValue(':explanation', $explanation, PDO::PARAM_STR);
  $statement->bindValue(':price', $price, PDO::PARAM_STR);
  $statement->execute();

  // トランザクション完了（コミット）
  $dbh->commit();

  //データベース接続切断
  $dbh = null;

  //セッション変数を解除（いる？）      //ログイン状態は残したい
  unset($_SESSION['image0']);
  unset($_SESSION['image1']);
  unset($_SESSION['image2']);
  unset($_SESSION['image3']);
  unset($_SESSION['pname']);
  unset($_SESSION['category']);
  unset($_SESSION['condition']);
  unset($_SESSION['explanation']);
  unset($_SESSION['price']);
  unset($_SESSION['token']);

  //ここどうしよう
  //セッションクッキーの削除
  // if (isset($_COOKIE["PHPSESSID"])) {
  //   		setcookie("PHPSESSID", '', time() - 1800, '/');
  // }

  //セッションを破棄する（いらないかな？） //ログイン状態は残したい
  // session_destroy();

  //出品完了のメールを送信
  //メールの宛先
  $mailTo = $mail;

  //Return-Pathに指定するメールアドレス
  $returnMail = MAIL;   //エラーメールの送信先

  $subject = "【iCycle】出品完了のお知らせ";  //題名

  //メールの内容
  $body = <<< EOM
  出品が完了しました。
EOM;

  mb_language('ja');
  mb_internal_encoding('UTF-8');

  //Fromヘッダーを作成
  $header = 'From: ' . mb_encode_mimeheader(NAME). ' <' . MAIL. '>';

  mb_send_mail($mailTo, $subject, $body, $header, '-f'. $returnMail);

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
  <title>出品完了画面|学内フリマ「iCycle」</title>
  <link href="../css/style.css" rel="stylesheet">
</head>
<body id="allpage">
  <header>
    <div class="logo">
      <a href="../index.php"><img src="../images/logo1.png" alt="SNAPPERS"></a>
    </div>
    <nav>
      <ul class="global-nav">
        <li><a href = "../all_product.php">全商品を見る</a></li>
        <li><a href="upload_form.php">出品する</a></li>
        <li><a href="../user's_product_list.php">あなたの出品物</a></li>
        <li><a href="../contact.html">お問い合わせ</a></li>
      </ul>
    </nav>
  </header>

  <div class="content">
    <div class="main-center">

      <?php if (count($errors) === 0): ?>

        <h1>出品完了画面</h1>

        <p>出品完了のメールをお送りしました。ご確認下さい。</p>

        <p>出品が完了いたしました。</p>
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
    <small>(c)2017 iFive.inc</small>
  </footer>
</body>
</html>
