<!-- 全商品のページ -->

<!-- div   item-list -->
<!-- <div class="box">  -->
<!-- <p class="last"> -->


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
require_once(dirname(__FILE__).'/../../../db/db_intern.php');
$dbh = db_connect();

try{
  //静的プレースホルダを用いるようにエミュレーションを無効化
  $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

  //例外処理を投げる（スロー）ようにする
  $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $statement_0 = $dbh->query("SELECT * FROM product WHERE category = '書籍（教科書）' OR category = '書籍（参考書/その他）'");
  $product_array_0 = $statement_0->fetchAll();

  $statement_1 = $dbh->query("SELECT * FROM product WHERE category = '学内カンパニー'");
  $product_array_1 = $statement_1->fetchAll();

  $statement_2 = $dbh->query("SELECT * FROM product WHERE category = 'サークル等団体'");
  $product_array_2 = $statement_2->fetchAll();

  $statement_3 = $dbh->query("SELECT * FROM product WHERE category = 'その他'");
  $product_array_3 = $statement_3->fetchAll();

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
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>全ての商品一覧|学内フリマ「iCycle」</title>
  <link href="css/style.css" rel="stylesheet">
</head>
<body id="allpage">
  <header>
    <div class="logo">
      <a href="./index.php"><img src="images/logo1.png" alt="SNAPPERS" width=150></a>
    </div>
    <!--ナビゲーション-->
    <nav>
      <ul class="global-nav">
        <li><a href="all_product.php">全商品を見る</a></li>
        <li><a href="./upload/upload_form.php">出品する</a></li>
        <li><a href="./user's_product_list.php">あなたの出品物</a></li>
        <li><a href="./contact.html">お問い合わせ</a></li>
      </ul>
    </nav>
  </header>
  <div class="content">
    <div class="main">
      <h1>全ての商品一覧</h1>

      <section>

        <h2 class="icon">書籍（教科書/参考書/その他）</h2>
        <div id="item-list">
          <ul>
            <?php for($i = 0; $i < $statement_0->rowCount(); $i++): ?>
              <li>
                <form name="product0<?= h($i);?>" action="product.php" method="post">
                  <input type="hidden" name="username" value="<?= h($product_array_0[$i]['username'])?>">
                  <input type="hidden" name="pname" value="<?= h($product_array_0[$i]['pname'])?>">
                  <!-- <?php var_dump($product_array_0[$i]['spname']); ?> -->

                </form>
                <a href="product.php" onclick="javascript:document.product0<?= h($i);?>.submit();return false;">
                  <p>＜商品名＞</p>
                  <p><?=h($product_array_0[$i]['pname']);?></p>
                  <div class="box">
                    <img src="<?php echo h(basename(UPLOAD_DIR)) . '/' . h($product_array_0[$i]['image0']); ?>">
                  </div>
                  <p>出品者：<?=h($product_array_0[$i]['username']);?></p>
                  <p class="last">価格：<?=h($product_array_0[$i]['price']);?>円</p>
                </a>
              </li>
            <?php endfor; ?>
          </ul></div>
        </section>

        <section>
          <h2 class="icon">学内カンパニー</h2>
          <div id="item-list">
            <ul>
              <?php for($i = 0; $i < $statement_1->rowCount(); $i++): ?>
                <li>
                  <form name="product1<?= h($i);?>" action="product.php" method="post">
                    <input type="hidden" name="username" value="<?= h($product_array_1[$i]['username'])?>">
                    <input type="hidden" name="pname" value="<?= h($product_array_1[$i]['pname'])?>">
                  </form>
                  <a href="product.php" onclick="javascript:document.product1<?= h($i);?>.submit();return false;">
                    <p>＜商品名＞</p>
                    <p><?=h($product_array_1[$i]['pname']);?></p>
                    <div class="box">
                      <img src="<?php echo h(basename(UPLOAD_DIR)) . '/' . h($product_array_1[$i]['image0']); ?>">
                    </div>
                    <p>出品者：<?=h($product_array_1[$i]['username']);?></p>
                    <p class="last">価格：<?=h($product_array_1[$i]['price']);?>円</p>
                  </a>
                </li>
              <?php endfor; ?>
            </ul></div>
          </section>

          <section>
            <h2 class="icon">サークル等団体の出品</h2>
            <div id="item-list">
              <ul>
                <?php for($i = 0; $i < $statement_2->rowCount(); $i++): ?>
                  <li>
                    <form name="product2<?= h($i);?>" action="product.php" method="post">
                      <input type="hidden" name="username" value="<?= h($product_array_2[$i]['username'])?>">
                      <input type="hidden" name="pname" value="<?= h($product_array_2[$i]['pname'])?>">
                    </form>
                    <a href="product.php" onclick="javascript:document.product2<?= h($i);?>.submit();return false;">
                      <p>＜商品名＞</p>
                      <p><?=h($product_array_2[$i]['pname']);?></p>
                      <div class="box">
                        <img src="<?php echo h(basename(UPLOAD_DIR)) . '/' . h($product_array_2[$i]['image0']); ?>">
                      </div>
                      <p>出品者：<?=h($product_array_2[$i]['username']);?></p>
                      <p class="last">価格：<?=h($product_array_2[$i]['price']);?>円</p>
                    </a>
                  </li>
                <?php endfor; ?>
              </ul></div>
            </section>

            <section>
              <h2 class="icon">その他</h2>
              <div id="item-list">
                <ul>
                  <?php for($i = 0; $i < $statement_3->rowCount(); $i++): ?>
                    <li>
                      <form name="product3<?= h($i);?>" action="product.php" method="post">
                        <input type="hidden" name="username" value="<?= h($product_array_3[$i]['username'])?>">
                        <input type="hidden" name="pname" value="<?= h($product_array_3[$i]['pname'])?>">
                      </form>
                      <a href="product.php" onclick="javascript:document.product3<?= h($i);?>.submit();return false;">
                        <p>＜商品名＞</p>
                        <p><?=h($product_array_3[$i]['pname']);?></p>
                        <div class="box">
                          <img src="<?php echo h(basename(UPLOAD_DIR)) . '/' . h($product_array_3[$i]['image0']); ?>">
                        </div>
                        <p>出品者：<?=h($product_array_3[$i]['username']);?></p>
                        <p class="last">価格：<?=h($product_array_3[$i]['price']);?>円</p>
                      </a>
                    </li>
                  <?php endfor; ?>
                </ul></div>
              </section>
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
