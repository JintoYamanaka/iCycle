<!-- メール確認・送信 -->

<?php
header("Content-type: text/html; charset=utf-8");

define('MAIL', 'yamajin-ips11@ezweb.ne.jp'); // 運営のメールアドレス
define('NAME', '学内カンパニー'); // 運営の名前

//クリックジャッキング対策
header('X-FRAME-OPTIONS: SAMEORIGIN');

//メールアドレスの形式チェック
function is_valid_email($email, $check_dns = false)
{
	switch (true) {
		case false === filter_var($email, FILTER_VALIDATE_EMAIL, FILTER_FLAG_EMAIL_UNICODE):
		case !preg_match('/@([^@\[]++)\z/', $email, $m):
		return false;

		case !$check_dns:
		case checkdnsrr($m[1], 'MX'):
		case checkdnsrr($m[1], 'A'):
		case checkdnsrr($m[1], 'AAAA'):
		return true;

		default:
		return false;
	}
}

session_start();

//クロスサイトリクエストフォージェリ（CSRF）対策のトークン判定
if ($_POST['token'] != $_SESSION['token']) {
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
} else {
	//POSTされたデータを変数に入れる
	$mail = isset($_POST['mail']) ? $_POST['mail'] : NULL;

	//メール入力判定

	// 入力判定
	if ($mail == '') {
		$errors['mail'] = "メールアドレスが入力されていません。";
	} else {

		if(!is_valid_email($mail, true)) {
			$errors['mail_check'] = "メールアドレスの形式が正しくありません。";
		}

		//岩大メールアドレスの判定
		$mailcheck = explode("@", $mail);  //@でメールアドレスを分割

		if(!($mailcheck[1] == "iwate-u.ac.jp" || $mailcheck[1] == "cis.iwate-u.ac.jp" || $mailcheck[1] == "mips.cis.iwate-u.ac.jp" || $mailcheck[1] == "kono.cis.iwate-u.ac.jp")) {
			$errors['mail_check_iwate'] = "岩大のメールアドレスではありません。";
		}

		try{
			//静的プレースホルダを用いるようにエミュレーションを無効化
			$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

			//例外処理を投げる（スロー）ようにする
			$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			//本登録用のmemberテーブルにすでに登録されているmailかどうかをチェックする。
			$statement = $dbh->prepare("SELECT * FROM member WHERE mail = (:mail)");
			$statement->bindValue(':mail', $mail, PDO::PARAM_STR);
			$statement->execute();
			// var_dump($statement->rowCount());
			if($statement->rowCount() != 0) {
				$errors['member_check'] = "このメールアドレスはすでに利用されております。";
			}

		}catch (PDOException $e){
			echo "エラーが発生しました。もう一度やりなおして下さい。";
			die();
		}

	}
}

if (count($errors) === 0){

	$urltoken = hash('sha256',uniqid(rand(),1));
	$url = SITE_URL."/intern/iCycle/registration/registration_form.php"."?urltoken=".$urltoken;     //GETメソッドによりトークンを取得できるようにする

	//ここでデータベースに登録する
	try{
		//静的プレースホルダを用いるようにエミュレーションを無効化
		$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

		//例外処理を投げる（スロー）ようにする
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		// $statement = $dbh->prepare("INSERT INTO pre_member (urltoken,mail,date) VALUES (:urltoken,:mail,now() )");
		$statement = $dbh->prepare("INSERT INTO pre_member (urltoken,date) VALUES (:urltoken,now())");

		//プレースホルダへ実際の値を設定する
		$statement->bindValue(':urltoken', $urltoken, PDO::PARAM_STR);
		// $statement->bindValue(':mail', $mail, PDO::PARAM_STR);
		$statement->execute();

		//データベース接続切断
		$dbh = null;

	}catch (PDOException $e){
		echo "エラーが発生しました。もう一度やりなおして下さい。";
		// echo 'Error:'.$e->getMessage();
		die();
	}

	//メールの宛先
	$mailTo = $mail;

	//Return-Pathに指定するメールアドレス
	$returnMail = MAIL;   //エラーメールの送信先

	$subject = "【iCycle】会員登録用URLのお知らせ";  //題名

	$body = <<< EOM
	24時間以内に下記のURLからご登録下さい。
	{$url}
EOM;

	mb_language('ja');
	mb_internal_encoding('UTF-8');

	//Fromヘッダーを作成
	$header = 'From: ' . mb_encode_mimeheader(NAME). ' <' . MAIL. '>';

	if (mb_send_mail($mailTo, $subject, $body, $header, '-f'. $returnMail)) {

		//セッション変数を全て解除
		$_SESSION = array();

		//クッキーの削除
		if (isset($_COOKIE["PHPSESSID"])) {
			setcookie("PHPSESSID", '', time() - 1800, '/');
		}

		//セッションを破棄する
		session_destroy();

		$message = "メールをお送りしました。24時間以内にメールに記載されたURLからご登録下さい。";

	} else {
		$errors['mail_error'] = "メールの送信に失敗しました。";
	}
}

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title>メール確認画面|学内フリマ「iCycle」</title>
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

				<h1>メール確認画面</h1>

				<p><?=htmlspecialchars($message, ENT_QUOTES, 'UTF-8')?></p>

				<p>↓このURLが記載されたメールが届きます。</p>
				<a href="<?=htmlspecialchars($url, ENT_QUOTES, 'UTF-8')?>"><?=htmlspecialchars($url, ENT_QUOTES, 'UTF-8')?></a>

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
