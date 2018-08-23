<!-- 会員登録フォーム  -->

<?php
header("Content-type: text/html; charset=utf-8");

session_start();

//クリックジャッキング対策
header('X-FRAME-OPTIONS: SAMEORIGIN');

//クロスサイトリクエストフォージェリ（CSRF）対策
$_SESSION['token'] = base64_encode(openssl_random_pseudo_bytes(32));
$token = $_SESSION['token'];

//データベース接続
require_once(__DIR__.'/../../../../db/db_intern.php');
$dbh = db_connect();

//エラーメッセージの初期化
$errors = array();

if(empty($_GET)) {
	header("Location: /registration_mail_form.php");
	exit();
}else{
	//GETデータを変数に入れる
	$urltoken = isset($_GET['urltoken']) ? $_GET['urltoken'] : NULL;
	//メール入力判定
	if ($urltoken == ''){
		$errors['urltoken'] = "もう一度登録をやりなおして下さい。";
	}else{
		try{
			//静的プレースホルダを用いるようにエミュレーションを無効化
			$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

			//例外処理を投げる（スロー）ようにする
			$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			//flagが0の未登録者・仮登録日から24時間以内
			// $statement = $dbh->prepare("SELECT mail FROM pre_member WHERE urltoken = (:urltoken) AND flag = 0 AND date > now() - interval 24 hour");

      //仮登録日から24時間以内
			$statement = $dbh->prepare("SELECT * FROM pre_member WHERE urltoken = (:urltoken) AND date > now() - interval 24 hour");
			$statement->bindValue(':urltoken', $urltoken, PDO::PARAM_STR);
			$statement->execute();

			//レコード件数取得
			$row_count = $statement->rowCount();

			//24時間以内に仮登録され、本登録されていないトークンの場合
			if($row_count == 0){
				$errors['urltoken_timeover'] = "このURLはご利用できません。有効期限が過ぎた等の問題があります。もう一度登録をやりなおして下さい。";
			}

			//データベース接続切断
			$dbh = null;

		}catch (PDOException $e){
			echo "エラーが発生しました。もう一度やりなおして下さい。";
			// echo 'Error:'.$e->getMessage();
			die();
		}
	}
}

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title>会員登録画面|学内フリマ「iCycle」</title>
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

				<h1>会員登録画面</h1>

				<form action="registration_check.php" method="post">

					<!-- <p>メールアドレス：<?=htmlspecialchars($mail, ENT_QUOTES, 'UTF-8')?></p> -->
					<p>※1.本登録には岩手大学のメールアドレスは利用できません。</p>
					<p>※2.アイアシスタントで使用している「ログイン名」「パスワード」と同一の文字列、またはそれらが推測される文字列の使用はご控え下さい。</p>
					<p>メールアドレス：<input type="email" name="mail" size="50"></p>
					<p>ユーザ名：<input type="text" name="username" size="50" maxlength="16" placeholder="半角英数、16文字以内"></p>
					<p>パスワード：<input type="password" name="password" size="50" maxlength="20" placeholder="半角英大文字/半角英小文字/数字を全て使用、10〜20文字"></p>

					<input type="hidden" name="token" value="<?=htmlspecialchars($token, ENT_QUOTES, 'UTF-8')?>">
					<input type="submit" value="利用規約・プライバシーポリシーに同意して登録">
					<p><font color = 'red'>※登録ボタンをクリックすることで、利用規約・プライバシーポリシーを読み、同意したものとします。</font></p>

				</form>

			<?php elseif(count($errors) > 0): ?>

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
contact
