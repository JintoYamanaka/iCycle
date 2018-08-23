<!-- 会員登録確認 -->

<?php
header("Content-type: text/html; charset=utf-8");

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

//前後にある半角全角スペースを削除する関数
function spaceTrim ($str) {
	// 行頭
	$str = preg_replace('/^[ 　]+/u', '', $str);
	// 末尾
	$str = preg_replace('/[ 　]+$/u', '', $str);
	return $str;
}

session_start();

//クロスサイトリクエストフォージェリ（CSRF）対策のトークン判定
if ($_POST['token'] != $_SESSION['token']){
	echo "不正アクセスの可能性あり";
	exit();
}

//データベース接続
require_once(__DIR__.'/../../../../db/db_intern.php');
$dbh = db_connect(__DIR__);

//エラーメッセージの初期化
$errors = array();

if(empty($_POST)) {
	header("Location: registration_mail_form.php");
	exit();
}else{
	//POSTされたデータを各変数に入れる
	$mail = isset($_POST['mail']) ? $_POST['mail'] : NULL;
	$username = isset($_POST['username']) ? $_POST['username'] : NULL;
	$password = isset($_POST['password']) ? $_POST['password'] : NULL;

	//前後にある半角全角スペースを削除
	$mail = spaceTrim($mail);
	$username = spaceTrim($username);
	$password = spaceTrim($password);

	try{
		//静的プレースホルダを用いるようにエミュレーションを無効化
		$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

		//例外処理を投げる（スロー）ようにする
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		//メールアドレス入力判定
		$statement = $dbh->prepare("SELECT * FROM member WHERE mail = (:mail)");
		$statement->bindValue(':mail', $mail, PDO::PARAM_STR);
		$statement->execute();
		//var_dump($statement->rowCount());
		if ($mail == '') {
			$errors['mail'] = "メールアドレスが入力されていません。";
		} else if($statement->rowCount() != 0) {
			$errors['mail_duplicate'] = "このメールアドレスはすでに利用されています。";
		} else {

			if(!is_valid_email($mail, true)) {
				$errors['mail_check'] = "メールアドレスの形式が正しくありません。";
			}

			if(strpos($mail, 'iwate-u.ac.jp') !== false) {          //strposで指定文字列を含むかチェック
				$errors['mail_check_iwate'] = "岩大のメールアドレスは本登録には利用できません。";
			}

		}

		//ユーザ名入力判定
		$statement = $dbh->prepare("SELECT * FROM member WHERE username = (:username)");
		$statement->bindValue(':username', $username, PDO::PARAM_STR);
		$statement->execute();
		//var_dump($statement->rowCount());
		if ($username == ''){
			$errors['username'] = "ユーザ名が入力されていません。";
		}elseif(!preg_match('/^[0-9a-zA-Z]{1,16}$/',$username)){  /*(mb_strlen($username)>16):*/
			$errors['username_length'] = "ユーザ名は半角英数字の16文字以内で入力して下さい。";
		}elseif($statement->rowCount() != 0){
			$errors['username_duplicate'] = "このユーザ名はすでに利用されています。";    /*同姓同名の場合？？？*/
		}

		//データベース接続切断
		$dbh = null;

	}catch (PDOException $e){
		echo "エラーが発生しました。もう一度やりなおして下さい。";
		// echo 'Error:'.$e->getMessage();
		die();
	}

	//パスワード入力判定
	if ($password == ''){
		$errors['password'] = "パスワードが入力されていません。";
	}elseif(!preg_match('/^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])[0-9a-zA-Z]{10,20}$/', $_POST["password"])){
	// }elseif(!preg_match('/^[0-9a-zA-Z]{10,30}$/', $_POST["password"])){
		$errors['password_length'] = "パスワードは半角英大文字、半角英小文字、数字の全てを組み合わせた10文字以上20文字以下で入力して下さい。";
	}else{
		$password_hide = str_repeat('*', strlen($password));   //*をパスワードの文字数分反復
	}


	//エラーが無ければセッションに登録
	if(count($errors) === 0){
		$_SESSION['mail'] = $mail;
		$_SESSION['username_pre'] = $username;
		$_SESSION['password'] = $password;
	}
}

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title>会員登録確認画面|学内フリマ「iCycle」</title>
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

				<h1>会員登録確認画面</h1>

				<p>以下の内容で登録を確定します。</p>

				<form action="registration_insert.php" method="post">

					<p>メールアドレス：<?=htmlspecialchars($mail, ENT_QUOTES, 'UTF-8')?></p>
					<p>ユーザ名：<?=htmlspecialchars($username, ENT_QUOTES, 'UTF-8')?></p>
					<p>パスワード：<?=htmlspecialchars($password_hide, ENT_QUOTES, 'UTF-8')?></p>

					<input type="button" value="戻る" onClick="history.back()">
					<input type="hidden" name="token" value="<?=htmlspecialchars($_POST['token'], ENT_QUOTES, 'UTF-8')?>">
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
	<footer>
		<small>利用規約　</small>
		<small>プライバシーポリシー　</small>
		<small>会社概要<br></small>
		<small>(c) 2017 iFive.inc</small>
	</footer>
</body>
</html>
