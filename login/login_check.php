<?php
session_start();

header("Content-type: text/html; charset=utf-8");

//クリックジャッキング対策
header('X-FRAME-OPTIONS: SAMEORIGIN');

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
require_once(__DIR__.'/../../../../db/db_intern.php');
$dbh = db_connect();

//エラーメッセージの初期化
$errors = array();

if(empty($_POST)) {
	header("Location: login_form.php");
	exit();
}else{
	//POSTされたデータを各変数に入れる
	$username = isset($_POST['username']) ? $_POST['username'] : NULL;
	$password = isset($_POST['password']) ? $_POST['password'] : NULL;

	//前後にある半角全角スペースを削除
	$username = spaceTrim($username);
	$password = spaceTrim($password);

	//ユーザ名入力判定
	if ($username == '') {
		$errors['username'] = "ユーザ名が入力されていません。";
	} elseif(!preg_match('/^[0-9a-zA-Z]{1,16}$/',$username)) {  //ここいらない？
		$errors['username_length'] = "ユーザ名は半角英数字の16文字以内で入力して下さい。";
	}


	//パスワード入力判定
	if ($password == '') {
		$errors['password'] = "パスワードが入力されていません。";
	}	elseif(!preg_match('/^[0-9a-zA-Z]{8,20}$/', $_POST["password"])) {    //ここを直す！！！！（いらない？）
		$errors['password_length'] = "パスワードは半角英数字の8文字以上20文字以下で入力して下さい。";
	} else {
		$password_hide = str_repeat('*', strlen($password));    //*をパスワードの文字数分反復
	}

}

//エラーが無ければ実行する
if(count($errors) === 0){
	try{
		//静的プレースホルダを用いるようにエミュレーションを無効化
		$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

		//例外処理を投げる（スロー）ようにする
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		//アカウントで検索
		$statement = $dbh->prepare("SELECT * FROM member WHERE username =  (:username)");
		// $statement = $dbh->prepare("SELECT * FROM member WHERE username =  (:username) AND flag =1");
		$statement->bindValue(':username', $username, PDO::PARAM_STR);
		$statement->execute();

		//ユーザー名が一致
		if($row = $statement->fetch()){

			$password_hash = $row['password'];

			//パスワードが一致
			if (password_verify($password, $password_hash)) {

				//セッションハイジャック対策
				session_regenerate_id(true);

				$_SESSION['username'] = $username;  //ログイン状態の判別に使う
				header("Location: http://ifive.sakura.ne.jp/intern/iCycle/index.php");
				exit();

			}else{
				$errors['password'] = "パスワードが一致しません。";
			}
		}else{
			$errors['username'] = "ユーザ名が一致しません。";
		}

		//データベース接続切断
		$dbh = null;

	}catch (PDOException $e){
		echo "もう一度やりなおして下さい。";
		echo 'Error:'.$e->getMessage();
		die();
	}
}

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title>ログイン確認画面|学内フリマ「iCycle」</title>
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
			<?php if(count($errors) > 0): ?>

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
