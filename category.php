<!-- カテゴリ別のページ -->
<!-- <div id="item-list"> -->
<!-- <div class="box">  -->
<!-- p class="last" -->


<?php
header("Content-type: text/html; charset=utf-8");

//クリックジャッキング対策
header('X-FRAME-OPTIONS: SAMEORIGIN');

function h($s) {
  return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

define('UPLOAD_DIR', __DIR__ . '/upload');
define('IMAGES_DIR', __DIR__ . '/images');

session_start();

//データベース接続
require_once(__DIR__ .'/../../../db/db_intern.php');
$dbh = db_connect();

if(empty($_POST)) {
  header("Location: index.php");
  // echo '1234';
  exit();
}

//POSTされたcategoryをセット
$category = isset($_POST['category']) ? $_POST['category'] : NULL;

try{
  //静的プレースホルダを用いるようにエミュレーションを無効化
  $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

  //例外処理を投げる（スロー）ようにする
  $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $statement = $dbh->prepare("SELECT * FROM product WHERE category = (:category)");
  $statement->bindValue(':category', $category, PDO::PARAM_STR);
  $statement->execute();
  $product_array = $statement->fetchAll();

  //データベース接続切断
  $dbh = null;
}catch (PDOException $e){
  echo "エラーが発生しました。";
  // echo 'Error:'.$e->getMessage();
  die();
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>カテゴリ別ページ|学内フリマ「iCycle」</title>
  <link href="css/style.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css?family=Bitter:400,700" rel="stylesheet">
  <link href="favicon.ico" rel="shortcut icon">
</head>

<body id="allpage">
  <header>
    <div class="logo">
      <a href="./index.php"><img src="images/logo1.png" alt="SNAPPERS"></a>
    </div>
    <nav>
      <ul class="global-nav">
        <li><a href="./all_product.php">全商品を見る</a></li>
        <li><a href="./upload/upload_form.php">出品する</a></li>
        <li><a href="./user's_product_list.php">あなたの出品物</a></li>
        <li><a href="./contact.html">お問い合わせ</a></li>
      </ul>
    </nav>
  </header>

  <div class="content">
    <div class="main">
      <!-- <p><a href="index.php">ホームへ戻る</a></p> -->
      <h1><?=h($category)?>の商品一覧</h1>
      <!-- <p>これまでに出品されている商品</p> -->
      <div id="item-list">
        <ul>
          <?php for($i = 0; $i < $statement->rowCount(); $i++): ?>
            <li>
              <!-- <a href="<?php echo h(basename(UPLOAD_DIR)) . '/' . h(basename(IMAGES_DIR)). '/' . h(basename($product_array[$i]['image0'])); ?>"> -->
              <form name="product<?= h($i);?>" action="product.php" method="post">
                <input type="hidden" name="username" value="<?= h($product_array[$i]['username'])?>">
                <input type="hidden" name="pname" value="<?= h($product_array[$i]['pname'])?>">
                <!-- <?php var_dump($product_array[$i]['pname']); ?> -->
              </form>
              <a href="product.php" onclick="javascript:document.product<?= h($i);?>.submit();return false;">
                <p>＜商品名＞</p>
                <p><?=h($product_array[$i]['pname']);?></p>
                <div class="box">
                  <img src="<?php echo h(basename(UPLOAD_DIR)) . '/' . h($product_array[$i]['image0']); ?>">
                </div>
                <p>出品者：<?=h($product_array[$i]['username']);?></p>
                <p class="last">価格：<?=h($product_array[$i]['price']);?>円</p>
              </a>
            </li>
          <?php endfor; ?>
        </ul></div>
      </div>

      <aside class="sidebar">
        <section>
          <h2>カテゴリー</h2>
          <ul>
            <!--  ここから書いてあるのは、aタグを用いてcategory.php（カテゴリ別のページ）にPOSTデータを送信するコード。
            aタグでのPOST送信により、送信ボタン（submit）を作らずにPOSTできる-->
            <!-- 以下のように、JavaScriptを使う -->

            <li>
              <form name="botton1" action="category.php" method="post">
                <input type="hidden" name="category" value="書籍（教科書）">
              </form>
              <a href="category.php" onclick="javascript:document.botton1.submit();return false;">書籍（教科書）</a>
            </li>
            <li>
              <form name="botton2" action="category.php" method="post">
                <input type="hidden" name="category" value="書籍（参考書/その他）">
              </form>
              <a href="category.php" onclick="javascript:document.botton2.submit();return false;">書籍（参考書/その他）</a>
            </li>
            <!-- <li>
            <form name="botton3" action="category.php" method="post">
            <input type="hidden" name="category" value="書籍（その他）">
          </form>
          <a href="category.php" onclick="javascript:document.botton3.submit();return false;">書籍（その他）</a>
        </li> -->
        <li>
          <form name="botton4" action="category.php" method="post">
            <input type="hidden" name="category" value="学内カンパニー">
          </form>
          <a href="category.php" onclick="javascript:document.botton4.submit();return false;">学内カンパニー</a>
        </li>
        <li>
          <form name="botton5" action="category.php" method="post">
            <input type="hidden" name="category" value="サークル等団体">
          </form>
          <a href="category.php" onclick="javascript:document.botton5.submit();return false;">サークル等団体</a>
        </li>
        <!-- <li>
        <form name="botton6" action="category.php" method="post">
        <input type="hidden" name="category" value="教職員">
      </form>
      <a href="category.php" onclick="javascript:document.botton6.submit();return false;">教職員</a>
    </li> -->
    <li>
      <form name="botton7" action="category.php" method="post">
        <input type="hidden" name="category" value="その他">
      </form>
      <a href="category.php" onclick="javascript:document.botton7.submit();return false;">その他</a>
    </li>

  </ul>
</section>
</aside>

</div>
<footer>
  <small>利用規約　</small>
  <small>プライバシーポリシー　</small>
  <small>会社概要<br></small>
  <small>(c) 2017 iFive.inc</small>
</footer>
</body>
</html>
