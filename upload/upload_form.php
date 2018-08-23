<!-- 出品登録フォーム  -->

<?php
ini_set('display_errors', 1); //最後にコメント化

define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('THUMBNAIL_WIDTH', 150);     //サムネサイズ
define('IMAGES_DIR', __DIR__ . '/images');
define('THUMBNAIL_DIR', __DIR__ . '/thumbs');
define('FILE_NUMBER', 4);       //最大アップロードファイル数

header("Content-type: text/html; charset=utf-8");

//クリックジャッキング対策
header('X-FRAME-OPTIONS: SAMEORIGIN');

session_start();

function h($s) {
  return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

//クロスサイトリクエストフォージェリ（CSRF）対策
$_SESSION['token'] = base64_encode(openssl_random_pseudo_bytes(32));
$token = $_SESSION['token'];

//データベース接続
require_once(__DIR__ .'/../../../../db/db_intern.php');
$dbh = db_connect();

$errors = array();
$images = array();  //配列の初期化

//ログイン状態のチェック
if(!isset($_SESSION['username'])) {
  $errors['login']  = 'ログインして下さい。';
}else{

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    //まず不必要な画像ファイルを削除
    try{
      //例外処理を投げる（スロー）ようにする
      $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      //トランザクション開始
      $dbh->beginTransaction();

      $statement = $dbh->query("SELECT * FROM product");
      // $statement->execute();
      $product_array = $statement->fetchAll();

      foreach(glob('images/*') as $file){  //globでパターンにマッチするパス名を探す
        // var_dump(basename($file));
        for($i = 0; $i < $statement->rowCount(); $i++){
          if(basename($file) == basename($product_array[$i]['image0'])) break;
          if(basename($file) == basename($product_array[$i]['image1'])) break;
          if(basename($file) == basename($product_array[$i]['image2'])) break;
          if(basename($file) == basename($product_array[$i]['image3'])) break;
        }
        if($i == $statement->rowCount()){
          unlink($file);                   //商品としてアップされていない画像を削除
        }
      }

      foreach(glob('thumbs/*') as $file){
        // var_dump(basename($file));
        for($i = 0; $i < $statement->rowCount(); $i++){
          if(basename($file) == basename($product_array[$i]['image0'])) break;
          if(basename($file) == basename($product_array[$i]['image1'])) break;
          if(basename($file) == basename($product_array[$i]['image2'])) break;
          if(basename($file) == basename($product_array[$i]['image3'])) break;
        }
        if($i == $statement->rowCount()){
          unlink($file);                 //商品としてアップされていない画像を削除
        }
      }

      // トランザクション完了（コミット）
      $dbh->commit();

      //データベース接続切断
      $dbh = null;

    }catch (PDOException $e){
      //トランザクション取り消し（ロールバック）
      $dbh->rollBack();
      echo "エラーが発生しました。もう一度やりなおして下さい。";
      // echo 'Error:'.$e->getMessage();
      die();
    }

    if(count($_FILES['image']['tmp_name']) > FILE_NUMBER){
      $errors['image'] = 'アップロードできるのは4枚までです。';    //multipleのとき
    } else {
      // for($i = 0; $i < FILE_NUMBER - 1; $i++){     //multipleじゃないとき
      for($i = 0; $i < count($_FILES['image']['tmp_name']); $i++){  //multipleの時
        // if($_FILES['image']['error'][$i] == UPLOAD_ERR_NO_FILE){          //multipleじゃないとき
        //   continue;
        // }

        // エラーチェック
        if (!isset($_FILES['image']) || !isset($_FILES['image']['error'][$i])) {
          $errors['error'] = 'アップロードエラー';
          // echo 'アップロードエラー';
          // var_dump($i);
          // exit;
        }

        if(count($errors) === 0){

          switch($_FILES['image']['error'][$i]) {
            case UPLOAD_ERR_OK:
            break;
            case UPLOAD_ERR_INI_SIZE:
            $errors['error'] = 'ファイルの容量が超過しています。1枚5MBまでです。';  //ここ決める
            break;
            // exit;
            case UPLOAD_ERR_FORM_SIZE:
            // var_dump($i);
            // $errors['error'] = 'ファイルの容量が超過しています。1枚?????MBまでです。';  //ここ決める
            $errors['error'] = 'ファイルの容量が超過しています。1枚5MBまでです。';  //ここ決める
            break;
            // exit;
            default:
            // var_dump($i);
            // var_dump($_FILES['image']['tmp_name']);
            // var_dump($_FILES['image'][$i]);
            // $errors['error'] = 'Error: ' . $_FILES['image']['error'][$i];
            $errors['error'] = 'Error: 画像ファイルを選択していない可能性があります。' ;
            // exit;
          }

        }

        if(count($errors) === 0){
          // タイプチェック
          $imageType = exif_imagetype($_FILES['image']['tmp_name'][$i]);
          // var_dump(exif_imagetype($_FILES['image']['tmp_name'][$i]));
          switch($imageType) {
            case IMAGETYPE_GIF:
            $ext = 'gif';
            break;
            case IMAGETYPE_JPEG:
            $ext = 'jpg';
            break;
            case IMAGETYPE_PNG:
            $ext = 'png';
            break;
            default:
            $errors['error'] =  'PNG/JPEG/GIF の画像のみ登録できます。';
            // exit;
          }
        }

        if(count($errors) === 0){
          // 保存
          $imageFileName = sprintf(
            '%s_%s.%s',
            time(),          //現在までの経過ミリ秒
            sha1(uniqid(mt_rand(), true)),     //重複しないランダムな文字列
            $ext                   //拡張子
          );
          $savePath = IMAGES_DIR . '/' . $imageFileName;
          $res = move_uploaded_file($_FILES['image']['tmp_name'][$i], $savePath);
          if ($res === false) {
            $errors['error'] = 'アップロードできませんでした。';
            // var_dump($savePath);
            // var_dump($_FILES['image']['tmp_name'][$i]);
            // exit;
          }
        }

        if(count($errors) === 0){
          //サムネイル作成
          $imageSize = getimagesize($savePath);
          $width = $imageSize[0];
          $height = $imageSize[1];
          if ($width > THUMBNAIL_WIDTH) {

            switch($imageType) {
              case IMAGETYPE_GIF:
              $srcImage = imagecreatefromgif($savePath);
              break;
              case IMAGETYPE_JPEG:
              $srcImage = imagecreatefromjpeg($savePath);
              break;
              case IMAGETYPE_PNG:
              $srcImage = imagecreatefrompng($savePath);
              break;
            }

            //幅を指定の大きさにし、縦横比を保持して高さを設定
            $thumbHeight = round($height * THUMBNAIL_WIDTH / $width);

            //比率を計算
            $proportion = THUMBNAIL_WIDTH / $thumbHeight;

            //高さが幅より大きい場合は、高さを幅に合わせ、横幅を縮小
            if($proportion < 1){
              $thumbHeight = THUMBNAIL_WIDTH;
              $width_new = THUMBNAIL_WIDTH * $proportion;
              $thumbImage = imagecreatetruecolor($width_new, $thumbHeight);
              imagecopyresampled($thumbImage, $srcImage, 0, 0, 0, 0, $width_new, $thumbHeight, $width, $height);
            }else{
              $thumbImage = imagecreatetruecolor(THUMBNAIL_WIDTH, $thumbHeight);
              imagecopyresampled($thumbImage, $srcImage, 0, 0, 0, 0, THUMBNAIL_WIDTH, $thumbHeight, $width, $height);
            }

            switch($imageType) {
              case IMAGETYPE_GIF:
              imagegif($thumbImage, THUMBNAIL_DIR . '/' . $imageFileName);
              break;
              case IMAGETYPE_JPEG:
              imagejpeg($thumbImage, THUMBNAIL_DIR . '/' . $imageFileName);
              break;
              case IMAGETYPE_PNG:
              imagepng($thumbImage, THUMBNAIL_DIR . '/' . $imageFileName);
              break;
            }
          }

          //変数に画像ファイル名を登録
          if(file_exists(THUMBNAIL_DIR . '/' . $imageFileName)) {
            $image = basename(THUMBNAIL_DIR) . '/' . $imageFileName;
          } else {
            $image = basename(IMAGES_DIR) . '/' . $imageFileName;
          }
          //  $image[$i]= $uploader->upload();
          $images[$i] = $image;
        }
      }
    }
  }
}
// var_dump($images[0]);
// var_dump(h(basename(IMAGES_DIR)) . '/' . basename($images[0]));

?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>出品する|学内フリマ「iCycle」</title>
  <link href="../css/style.css" rel="stylesheet">
</head>

<!-- <body id=photograph> -->
<body id="allpage">
  <header>
    <div class="logo">
      <a href="../index.php"><img src="../images/logo1.png" alt="SNAPPERS"></a>
    </div>
    <nav>
      <ul class="global-nav">
        <li><a href="../all_product.php">全商品を見る</a></li>
        <li><a href="">出品する</a></li>
        <li><a href="../user's_product_list.php">あなたの出品物</a></li>
        <li><a href="../contact.html">お問い合わせ</a></li>
      </ul>
    </nav>
  </header>
  <div class="content">
    <div class="main-center">

      <?php if (count($errors) === 0): ?>

        <h1>出品する</h1>
        <p>以下の入力フォームから出品手続きを行ってください。</p>
        <!--   入力フォーム-->
        <section>
          <!-- <section id=photograph class=clearfix> -->
          <div class="form">
            <form action="" method="post" enctype="multipart/form-data">
              <span class="required">商品画像</span><br>
              <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo h(MAX_FILE_SIZE); ?>">
              <input type="file" name="image[]" list="hobbys" multiple accept="image/*">
              <!-- <input type="file" name="image[]">
              <input type="file" name="image[]">
              <input type="file" name="image[]">
              <input type="file" name="image[]"> -->
              <input type="submit" value="upload">
              <datalist id="product_images">
                <!-- <option value="image0">
                <option value="image1">
                <option value="image2">
                <option value="image3"> -->
              </datalist>　　　※４枚まで複数選択可（Macユーザーはcommandキー＋クリック）<br>
            </form>

            <!-- <p>メイン画像</p> -->
            <div id="upload">
              <ul>
                <?php if(isset($images[0])):?>
                  <p>メイン画像</p>
                  <li><span>
                    <a href="<?php echo h(basename(IMAGES_DIR)) . '/' . h(basename($images[0])); ?>">
                      <div class="main-box">           <img src="<?php echo h($images[0]); ?>">     </div>
                    </a>
                  </span></li>
                  <!-- </ul> -->
                <?php endif;?>

                <?php if(isset($images[1])):?>
                  <li><span>
                    <a href="<?php echo h(basename(IMAGES_DIR)) . '/' . h(basename($images[1])); ?>">
                      <div class="box">           <img src="<?php echo h($images[1]); ?>">       </div>
                    </a>
                  </span></li>
                <?php endif;?>

                <?php if(isset($images[2])):?>
                  <li><span>
                    <a href="<?php echo h(basename(IMAGES_DIR)) . '/' . h(basename($images[2])); ?>">
                      <div class="box">         <img src="<?php echo h($images[2]); ?>">        </div>
                    </a>
                  </span></li>
                <?php endif;?>

                <?php if(isset($images[3])):?>
                  <li><span>
                    <a href="<?php echo h(basename(IMAGES_DIR)) . '/' . h(basename($images[3])); ?>">
                      <div class="box">           <img src="<?php echo h($images[3]); ?>">      </div>
                    </a>
                  </span></li>
                  <!-- </ul> -->
                <?php endif;?>
              </ul>
            </div>

            <br><br>

            <form action="upload_check.php" method="post">
              <!-- <dl> -->
              <input type="hidden" name="token" value="<?=h($token)?>">

              <input type="hidden" name="image0" value="<?php echo h($images[0]); ?>">
              <input type="hidden" name="image1" value="<?php echo h($images[1]); ?>">
              <input type="hidden" name="image2" value="<?php echo h($images[2]); ?>">
              <input type="hidden" name="image3" value="<?php echo h($images[3]); ?>">

              <dt><span class="required">商品名(20文字以内)</span>　　　<input type="text" name="pname" required maxlength="20"></dt><br>
              <!-- <dd><input type="text" name="pname" required maxlength="20"></dd><br> -->

              <dt><span class="required">カテゴリー</span></dt>

              <dd>
                <label><input type="radio" name="category" value="書籍（教科書）" checked>　書籍（教科書）</label>
                <label><input type="radio" name="category" value="書籍（参考書/その他）">　書籍（参考書/その他）</label>
                <!-- <input type="radio" name="category" value="書籍（その他）">書籍(その他)<br> -->
                <label><input type="radio" name="category" value="学内カンパニー">　学内カンパニー</label>
                <label><input type="radio" name="category" value="サークル等団体">　サークル等団体</label>
                <!-- <input type="radio" name="category" value="教職員">教職員 -->
                <label><input type="radio" name="category" value="その他">　その他</label>
              </dd><br>

              <dt><span class="required">商品の状態</span></dt>

              <dd>
                <select name="condition" class="type">
                  <option value="選択して下さい">選択して下さい</option>
                  <option value="新品">新品</option>
                  <option value="ほぼ新品">ほぼ新品</option>
                  <option value="使用感あり">使用感あり</option>
                </select>
              </dd><br>

              <dt><span class="required">商品説明(100文字以内)</span></dt>
              <dd><textarea name="explanation" class="message" maxlength="100"></textarea></dd><br>
              <dt><span class="required">価格</span>　　　<input type="text" name="price" required maxlength="7" placeholder="半角数字のみ">　円</dt><br>
              <!-- <dd><input type="text" name="price" required maxlength="7">円</dd><br> -->
            </dl>
            <button type="submit" class="btn">出品</button>
          </form>
          <div class="attention">
            <p>※「<span class="required"></span>」の付いている項目は必須項目です。<br>
          </div>
        </div>
      </section>

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
