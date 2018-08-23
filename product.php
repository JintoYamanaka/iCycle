<!-- 商品別のページ -->

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

//クロスサイトリクエストフォージェリ（CSRF）対策
$_SESSION['token'] = base64_encode(openssl_random_pseudo_bytes(32));
$token = $_SESSION['token'];

//データベース接続
require_once(__DIR__ .'/../../../db/db_intern.php');
$dbh = db_connect();

$errors = array();

if(empty($_POST)) {
  header("Location: index.php");
  // echo '1234';
  exit();
}

//POSTされたusernameとpnameをセット
$username = isset($_POST['username']) ? $_POST['username'] : NULL;
$pname = isset($_POST['pname']) ? $_POST['pname'] : NULL;

try{
  //静的プレースホルダを用いるようにエミュレーションを無効化
  $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

  //例外処理を投げる（スロー）ようにする
  $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $statement = $dbh->prepare("SELECT * FROM product WHERE username = (:username) AND pname = (:pname)");
  $statement->bindValue(':username', $username, PDO::PARAM_STR);
  $statement->bindValue(':pname', $pname, PDO::PARAM_STR);
  $statement->execute();
  $array = $statement->fetch();
  if($statement->rowCount() == 0) {
    $errors['delete'] = "この商品は既に購入または削除されています。";
  }

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
  <title>商品別ページ|学内フリマ「iCycle」</title>
  <link href="css/style.css" rel="stylesheet">
</head>

<body id="allpage">
  <!-- <body id=contact> -->
  <header>
    <div class="logo">
      <a href="index.php"><img src="images/logo1.png" alt="SNAPPERS"></a>
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
    <?php if (count($errors) === 0): ?>

      <div class="main">

        <!-- <center> -->
        <!-- <img src="https://〇〇〇.com/img.jpg" width="600"><br /> -->
        <div class='product-table'>

          <table cellpadding="10" >
            <tbody>
              <tr>
                <td bgcolor="#2678b7"><font color='white'><h1><?=h($pname)?></h1></font></td>
              </tr>

              <tr>
                <td bgcolor="WhiteSmoke">
                  <a href="<?php echo h(basename(UPLOAD_DIR)) . '/'. h(basename(IMAGES_DIR)). '/' . h(basename($array['image0'])); ?>">
                    <img class="top" src="<?php echo h(basename(UPLOAD_DIR)) . '/'. h(basename(IMAGES_DIR)). '/' . h(basename($array['image0'])); ?>">
                  </a>
                </td>
              </tr>

              <?php if($array['image1'] != NULL):?>
                <tr>
                  <td bgcolor="WhiteSmoke">
                    <a href="<?php echo h(basename(UPLOAD_DIR)) . '/'. h(basename(IMAGES_DIR)). '/' . h(basename($array['image1'])); ?>">
                      <img src="<?php echo 'upload/'. h($array['image1']); ?>">
                    </a>


                    <?php if($array['image2'] != NULL):?>

                      <a href="<?php echo h(basename(UPLOAD_DIR)) . '/'. h(basename(IMAGES_DIR)). '/' . h(basename($array['image2'])); ?>">
                        <img src="<?php echo 'upload/'. h($array['image2']); ?>">
                      </a>


                      <?php if($array['image3'] != NULL):?>

                        <a href="<?php echo h(basename(UPLOAD_DIR)) . '/'. h(basename(IMAGES_DIR)). '/' . h(basename($array['image3'])); ?>">
                          <img src="<?php echo 'upload/'. h($array['image3']); ?>">
                        </a>
                      </td>
                    </tr>

                  <?php endif;?>
                </td>
              </tr>
            <?php endif;?>
          </td>
        </tr>
      <?php endif;?>

      <tr>
        <th>出品者</th>
      </tr>
      <tr>
        <td bgcolor="#F8F8FF"><?=h($username);?></td>
      </tr>


      <tr>
        <th>カテゴリ</th>
      </tr>
      <tr>
        <td bgcolor="#F8F8FF"><?=h($array['category']);?></td>
      </tr>

      <tr>
        <th>商品の状態 </th>
      </tr>
      <tr>
        <td bgcolor="#F8F8FF"><?=h($array['_condition']);?></td>
      </tr>

      <tr>
        <th> 商品説明 </th>
      </tr>
      <tr>
        <td bgcolor="#F8F8FF"><?=h($array['explanation']);?></td>
      </tr>

      <tr>
        <th>価格</></th>
      </tr>
      <tr>
        <td bgcolor="#F8F8FF"><?=h($array['price']);?>円</td>
      </tr>

    </tbody>
  </table>

</div>


<?php if($username != $_SESSION['username']):?>
  <form action="./buy/buy_check.php" method="post">
    <input type="hidden" name="username" value="<?= h($username)?>">
    <input type="hidden" name="pname" value="<?= h($pname)?>">
    <input type="hidden" name="price" value="<?= h($array['price'])?>">
    <input type="hidden" name="token" value="<?= h($token)?>">
    <button type="submit" id="buy">購入</button>
  </form>

<?php elseif($username == $_SESSION['username']): ?>
  <form  action="./delete/delete_check.php" method="post">
    <input type="hidden" name="username" value="<?= h($username)?>">
    <input type="hidden" name="pname" value="<?= h($pname)?>">
    <input type="hidden" name="token" value="<?= h($token)?>">
    <button type="submit" id="delete">削除</button>
  </form>
<?php endif;?>

</div>
<!-- </center> -->

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

<?php elseif(count($errors) > 0): ?>
  <div class="main-center">

    <h1>エラーメッセージ</h1>

    <?php
    foreach($errors as $value){
      echo "<p>".$value."</p>";
    }
    ?>

    <input type="button" value="戻る" onClick="history.back()">
  </div>

<?php endif; ?>

</div>

<footer>
  <small>利用規約　</small>
  <small>プライバシーポリシー　</small>
  <small>会社概要<br></small>
  <small>(c) 2017 iFive.inc</small>
</footer>
</body>
</html>
