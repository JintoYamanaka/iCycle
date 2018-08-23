<!-- 出品登録確認 -->

<?php
header("Content-type: text/html; charset=utf-8");

//クリックジャッキング対策
header('X-FRAME-OPTIONS: SAMEORIGIN');

session_start();

function h($s) {
  return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

//前後にある半角全角スペースを削除する関数
function spaceTrim ($str) {
  // 行頭
  $str = preg_replace('/^[ 　]+/u', '', $str);
  // 末尾
  $str = preg_replace('/[ 　]+$/u', '', $str);
  return $str;
}

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
}else{
  //POSTされたデータを各変数に入れる
  $image0 = isset($_POST['image0']) ? $_POST['image0'] : NULL;  //メイン画像
  $image1 = isset($_POST['image1']) ? $_POST['image1'] : NULL;  //サブ画像
  $image2 = isset($_POST['image2']) ? $_POST['image2'] : NULL;  //サブ画像
  $image3 = isset($_POST['image3']) ? $_POST['image3'] : NULL;  //サブ画像
  $pname = isset($_POST['pname']) ? $_POST['pname'] : NULL;  //商品名
  $category = isset($_POST['category']) ? $_POST['category'] : NULL; //カテゴリ
  $condition = isset($_POST['condition']) ? $_POST['condition'] : NULL;  //商品の状態
  // $place = isset($_POST['place']) ? $_POST['place'] : NULL;   //受け渡し場所だが、不必要の可能性あり
  $explanation = isset($_POST['explanation']) ? $_POST['explanation'] : NULL;  //商品説明
  $price = isset($_POST['price']) ? $_POST['price'] : NULL;  //価格

  //前後にある半角全角スペースを削除
  $pname = spaceTrim($pname);
  // $place = spaceTrim($place);
  $price = spaceTrim($price);
  $explanation = spaceTrim($explanation);

  //商品画像登録判定
  if($image0 == '') {
    $errors['image'] = "メイン画像を登録して下さい。";
  }

  //商品名入力判定
  if ($pname == '') {
    $errors['pname'] = "商品名を入力して下さい。";
  } else {


    try{
      //静的プレースホルダを用いるようにエミュレーションを無効化
      $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

      //例外処理を投げる（スロー）ようにする
      $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      //同一ユーザーが同じ商品名を利用していないかをチェック
      $username = $_SESSION['username'];
      $statement = $dbh->prepare("SELECT * FROM product WHERE username = (:username) AND pname = (:pname)");
      $statement->bindValue(':username', $username, PDO::PARAM_STR);
      $statement->bindValue(':pname', $pname, PDO::PARAM_STR);
      $statement->execute();
      // var_dump($statement->rowCount());
      if($statement->rowCount() != 0) {
        $errors['pname'] = "あなたは既に同じ商品名の出品をしています。異なる商品名にして下さい。";
      }

      //データベース接続切断
      $dbh = null;

    }catch (PDOException $e){
      echo "エラーが発生しました。もう一度やりなおして下さい。";
      // echo 'Error:'.$e->getMessage();
      die();
    }

  }

  //カテゴリ入力判定
  if ($category == '') {
    $errors['category'] = "カテゴリを選択して下さい。";
  }

  //商品の状態入力判定
  if ($condition == '選択して下さい') {
    $errors['condition'] = "商品の状態を選択して下さい。";
  }

  // //受け渡し場所入力判定
  // if ($place == '') {                     //受け渡し場所の入力欄は無くす可能性あり
  // 	$errors['place'] = "受け渡し場所を入力して下さい。";
  // }

  //商品説明入力判定
  if ($explanation == '') {
    $errors['explanation'] = "商品説明を入力して下さい。";
  }

  //価格入力判定
  if ($price== '') {               
    $errors['price'] = "価格を設定して下さい。";
  }elseif(!preg_match('/^[0-9]+$/',$price)) {
    $errors['price'] = "価格は半角数字で入力して下さい。";
  }

}

//エラーが無ければセッションに登録
if(count($errors) === 0){
  $_SESSION['image0'] = $image0;
  $_SESSION['image1'] = $image1;
  $_SESSION['image2'] = $image2;
  $_SESSION['image3'] = $image3;
  $_SESSION['pname'] = $pname;
  $_SESSION['category'] = $category;
  $_SESSION['condition'] = $condition;
  $_SESSION['place'] = $place;
  $_SESSION['explanation'] = $explanation;
  $_SESSION['price'] = $price;
}

?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>出品登録確認画面|学内フリマ「iCycle」</title>
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
  <div id="wrap">
    <div class="content">
      <div class="main-center">

        <?php if (count($errors) === 0): ?>

          <h1>出品登録確認画面</h1>

          <p>以下の内容で出品を確定します。</p>

          <form action="upload_insert.php" method="post">

            <p>商品名：<?=h($pname);?></p>
            <p>カテゴリー：<?=h($category);?></p>
            <p>商品の状態：<?=h($condition);?></p>
            <p>商品説明：<?=h($explanation);?></p>
            <p>価格：<?=h($price);?>円</p>

            <input type="button" value="戻る" onClick="history.back()">
            <input type="hidden" name="token" value="<?=h($_POST['token'])?>">  <!--h使うべき？-->
            <input class="ok_btn" type="submit" value="確定">

          </form>

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
  </div>
  <footer>
    <small>利用規約　</small>
    <small>プライバシーポリシー　</small>
    <small>会社概要<br></small>
    <small>(c) 2017 iFive.inc</small>
  </footer>
</body>
</html>
