<?php
session_start();
require_once('blocks/connect.php');
require_once('functions.php');
/*
Переменные переданные с left_menu.php через POST
login_button - кнопка, которая передаёт дааные с left_menu.php через POST
				(должна иметь значение "Войти")
username	- логин
password	- пароль
*/
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="description" content="">
<meta name="keywords" content="">
<title>Футбольный прогноз - сайт знатоков футбола</title>
<link href="styles.css" rel="stylesheet" type="text/css">
</head>
<body>
<!-- Подключается Header -->
<?php require_once('blocks/header.php'); ?>
<!------------------------->
<table class="telo" cellpadding="0" cellspacing="0">
<tr>
<!-- Подключается верхнее меню -->
<?php require_once ("blocks/top_menu.php"); ?>
<!------------------------->
</tr>
<tr>
	<td>
	<table class="telo" cellpadding="0" cellspacing="3">
	<tr>
		<!-- Подключается левое меню -->
		<?php require_once("blocks/left_menu.php"); ?>
		<!------------------------->
		<td valign="top">
			<!-- Рабочая часть сайта -->
<?php
if (isset($_POST['login_button']) && $_POST['login_button'] == 'ВОЙТИ'){
	$username = $conn -> real_escape_string(htmlspecialchars(trim($_POST['username'])));
	if ($username == '') unset($username);
	$password = $conn -> real_escape_string(htmlspecialchars($_POST['password']));
	$password = md5($password);
	if ($password == '') unset($password);
    // проверяем логин и пароль в базе
	$sql = "SELECT * FROM users WHERE username=? AND pass=?";
	if ($stmt = $conn -> prepare($sql)){
		$stmt -> bind_param('ss',$username,$password);
		$stmt -> execute();
		$res = $stmt -> get_result();
		if ($res -> num_rows == 1){
			$row = $res -> fetch_assoc();
			$_SESSION['user_id'] =	$row['id'];
			$_SESSION['login'] = $row['username'];
			$_SESSION['rights'] = $row['rights'];
			//header('Location:index.php');
			echo '<script type="text/javascript">';
			echo 'window.location.href="index.php";';
			echo '</script>';
			exit();
		} else echo "
		<table align='center'
		style='width:60%; margin-top:30px; text-align:center; border:1px solid #75A54B;
		background-color:#f6f6f6; font-family:Verdana; font-size:14px; color:#FF0000;
		padding:10px 0px'>
		<tr>
			<td>
			<center><h3><p style='line-height:25px'>
			Не найдено такого логина или пароля!<br>
			Проверьте правильность набора, раскладку клавиатуры, \"Caps Lock\"  и др.<br>
			Повторите пожалуйста ввод или зарегистрируйтесь.
			</p></h3></center><br>
			</td>
		</tr>
		</table>";
	} else error_rez('Ошибка соединения с базой данных! Вернитесь назад и повторите попытку!');
}
?>
			<!-------------------------->
		</td>
	</tr>
	</table>
	</td>
</tr>
<tr>
<?php require_once("blocks/footer.php"); ?>
</tr>
</table>
</body>
</html>