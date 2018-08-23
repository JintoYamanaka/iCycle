<!-- 会員登録完了 -->

<?php
header("Content-type: text/html; charset=utf-8");

define('MAIL', 'yamajin-ips11@ezweb.ne.jp'); // 運営のメールアドレス
define('NAME', '学内カンパニー'); // 運営の名前

//クリックジャッキング対策
header('X-FRAME-OPTIONS: SAMEORIGIN');

session_start();

//クロスサイトリクエストフォージェリ（CSRF）対策のトークン判定
if ($_POST['token'] != $_SESSION['token']){
	echo "不正アクセスの可能性あり";
	exit();
}

//データベース接続
require_once(__DIR__.'/../../../../db/db_intern.php');
$dbh = db_connect();

//エラーメッセージの初期化
$errors = array();

if(empty($_POST)) {
	header("Location: registration_mail_form.php");
	exit();
}

$mail = $_SESSION['mail'];
$username = $_SESSION['username_pre'];

//パスワードのハッシュ化
$password_hash =  password_hash($_SESSION['password'], PASSWORD_DEFAULT);

//ここでデータベースに登録する
try{
	//静的プレースホルダを用いるようにエミュレーションを無効化
	$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

	//例外処理を投げる（スロー）ようにする
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	//トランザクション開始
	$dbh->beginTransaction();

	//memberテーブルに本登録する
	$statement = $dbh->prepare("INSERT INTO member (username,mail,password,date) VALUES (:username,:mail,:password_hash,now())");
	//プレースホルダへ実際の値を設定する
	$statement->bindValue(':username', $username, PDO::PARAM_STR);
	$statement->bindValue(':mail', $mail, PDO::PARAM_STR);
	$statement->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);
	$statement->execute();

	//pre_memberのflagを1にする
	// $statement = $dbh->prepare("UPDATE pre_member SET flag = 1 WHERE mail = (:mail)");

	//プレースホルダへ実際の値を設定する
	// $statement->bindValue(':mail', $mail, PDO::PARAM_STR);
	// $statement->execute();

	// トランザクション完了（コミット）
	$dbh->commit();

	//データベース接続切断
	$dbh = null;

	//セッション変数を全て解除
	$_SESSION = array();

	//セッションクッキーの削除・sessionidとの関係を探れ。つまりはじめのsesssionidを名前でやる
	if (isset($_COOKIE["PHPSESSID"])) {
		setcookie("PHPSESSID", '', time() - 1800, '/');
	}

	//セッションを破棄する
	session_destroy();

	//登録完了のメールを送信
	//メールの宛先
	$mailTo = $mail;

	//Return-Pathに指定するメールアドレス
	$returnMail = MAIL;   //エラーメールの送信先

	$subject = "【iCycle】会員登録完了のお知らせ";  //題名

	$body = <<< EOM
	登録が完了しました。
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
	<title>会員登録完了画面|学内フリマ「iCycle」</title>
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
				<h1>会員登録完了画面</h1>

				<p>登録完了のメールをお送りしました。ご確認下さい。</p>

				<p>登録完了いたしました。ログイン画面からどうぞ。</p>
				<p><a href="/../intern/iCycle/login/login_form.php">ログイン画面</a></p>

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
