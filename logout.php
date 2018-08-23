<?php
ini_set('display_errors', 1); //最後にコメント化

session_start();

header("Content-type: text/html; charset=utf-8");

// ログイン状態のチェック
if (!isset($_SESSION["username"])) {
	header("Location: index.php");
	exit();
}

//セッション変数を全て解除
$_SESSION = array();

//セッションクッキーの削除
if (isset($_COOKIE["PHPSESSID"])) {
	setcookie("PHPSESSID", '', time() - 1800, '/');
}

//セッションを破棄する
session_destroy();

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title>ログアウト画面|学内フリマ「iCycle」</title>
	<link href="css/style.css" rel="stylesheet">
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
		<div class="main-center">

			<h1>ログアウト完了画面</h1>

			<p>ログアウトしました。</p>

			<!-- <p>	<a href= '/login/login_form.php'>ログイン画面へ</a></p> -->
			<p><a href= 'index.php'>ホームへ戻る</a></p>
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
