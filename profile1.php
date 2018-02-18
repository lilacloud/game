<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['login']) || !isset($_SESSION['rights'])){
	header('Location:index.php');
	exit();
}
require_once('blocks/connect.php');
require_once('functions.php');
/*
Переменные, которые приходят с POST
oldpass			- старый пароль
newpass 		- новый пароль
newpass2		- новый пароль ещё раз
enterprof_pass 	- если = 'ok', то удачное нажатие кнопки изменения пароля
email			- новый email
enterprof_email - если = 'ok', то удачное нажатие кнопки изменения email-а
new				- новый e-mail после записи в базу данных
---
$res  - результаты всех выборок с базы данных
$row  - массив с одной записью выборкой с базы данных
$rows - массив со всеми записями выборками с базы данны
$pass - старый пароль введённый пользователем
$pass1 - новый пароль введённый пользователем
$pass2 - новый пароль ещё раз
$er[1] - ошибка в старом пароле (если = 1, ошибки нет)
$er[2] - ошибка в новом пароле (если = 1, ошибки нет)
$er[3] - ошибка несовпадения новых паролей
$email - новый e-mail
$er[4] - ошибка e-mail
*/
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="description" content="">
<meta name="keywords" content="">
<title>Футбольный прогноз | Мой профиль</title>
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
		<td valign="top" class="rabchast">
		<!-- Рабочая часть сайта -->
<table>
<tr><td>
<h2><b>Профиль: &nbsp;</b>
	<b class='username'><?php echo $_SESSION['login']; ?></b></h2>
 	<ul class='zakladki'>
	<li><a href='profile.php'>Выбор команды</a></li>
	<li class='current'><a href='profile1.php'>Пароль/E-Mail</a></li>
	<li><a href='profile2.php'>Личные данные</a></li>
	</ul>
</td></tr>
</table>
<table>
<form name='editprof_pass' method='post' action='profile1.php'>
<tr> <!-- Изменить пароль -->
<th colspan='2' align='left' class='gor_line'>
	<h3><b>Изменить пароль</b></h3></th>
</tr>
<tr>
<td width='140'><h4>Старый пароль:</h4></td>
<td><input type='password' size='20' maxlength='20' name='oldpass'></td>
<?php
if (isset($_POST['oldpass'])){	if ($_POST['oldpass'] == '')
		$er[1] = 'Вы не ввели старый пароль!';
	else {
	  $res = $conn -> query("SELECT pass FROM users
	  	 					 WHERE id={$_SESSION['user_id']}");
	  if (!$res) error_rez('Запрос к базе данных для проверки пароля не выполнен!');
	  if ($res -> num_rows <> 0)
		  $row = $res -> fetch_row();
	  $pass = $conn -> real_escape_string(htmlspecialchars($_POST['oldpass']));
	  if ($row[0] <> md5($pass))
		  $er[1] = 'Вы ввели неверный пароль!';
	  else $er[1] = 1;
	}
}
?>
</tr>
<tr>
<td><h4>Новый пароль:</h4></td>
<td><input type='password' size='20' maxlength='20' name='newpass'></td>
<?php
if (isset($er[1]) && $er[1] == 1){ // если нет ошибки в старом пароле, то
	// Проверяем, что-бы было не меньше 6-ти символов в новом пароле
	if (isset($_POST['newpass'])){
		$pass1 = $conn -> real_escape_string(htmlspecialchars($_POST['newpass']));
		if (strlen($pass1) < 6)
			$er[2] = 'Пароль не может быть меньше 6-ти символов!';
		else $er[2] = 1;
	}
}
?>
</tr>
<tr>
<td><h4>Повторите пароль:</h4></td>
<td><input type='password' size='20' maxlength='20' name='newpass2'></td>
<?php
if (isset($_POST['newpass']) && isset($_POST['newpass2'])){
	$pass1 = $_POST['newpass'];
	$pass2 = $_POST['newpass2'];
  	if (isset($er[2]) && $er[2] == 1){ //если с первым паролем всё нормально, тогда
	  // Проверяем совпадение двух паролей
	  if (strcmp($pass1, $pass2) <> 0)
		$er[3] = 'Пароли не совпадают, повторите попытку!';
	  else $er[3] = 1;
  	}
}
?>
</tr>
<tr>
<td colspan='2' class='gor_line'>
<?php // если была ошибка - пишем её
for ($i=1; $i<=3; $i++)
	isset($er[$i]) && $er[$i]<>1 ? mes_er($er[$i]) : false;
// если не было ошибок - меняем пароль
if (isset($_POST['enterprof_pass']) && $_POST['enterprof_pass'] == 'ok' &&
	isset($er) && $er[1] == 1 && $er[2] == 1 && $er[3] == 1){	$pass1 = md5($pass1);
	$sql = 'UPDATE users
			SET pass=?
			WHERE id=?';
	if ($stmt = $conn -> prepare($sql)){
		$stmt -> bind_param('si', $pass1, $_SESSION['user_id']);
	  if ($stmt -> execute()){
		mes_ok('Операция выполнена успешно.');
		$stmt -> close();
	  } else error_rez('Ошибка смены пароля в базе данных!');
	} else error_rez('Ошибка смены пароля в базе данных!');
}
?>
</td>
</tr>
<tr><td>&nbsp;</td>
  <td>
  <button type='submit' name='enterprof_pass' value='ok' class='button'>Изменить</button>
  </td>
</tr>
</form>
<form name='editprof_email' method='post' action='profile1.php'>
<tr> <!-- Изменить e-mail -->
<th colspan='2' align='left' class='gor_line'>
	<h3><b>Изменить E-Mail</b></h3></th>
</tr>
<tr>
  <td>
  <h4>Ваш E-Mail:</h4>
  </td>
  <td>
<?php
// проверка e-mail
if (isset($_POST['enterprof_email']) && $_POST['enterprof_email'] == 'ok' &&
	isset($_POST['email'])){
	if ($_POST['email'] == '') // не пустое ли значение
		$er[4] = 'Вы не ввели новый E-Mail!';
	elseif (!preg_match("/^[\._A-Za-z0-9-]+@[\.A-Za-z0-9-]+\.[a-z]{2,6}$/", $_POST['email']))
		$er[4] = 'Вы ввели неверный E-Mail адрес!'; // правильный ли e-mail
	else { //проверяем наличие в БД уже такого e-mail
		$email = $conn -> real_escape_string(trim($_POST['email']));
	   if ($stmt = $conn -> prepare('SELECT id FROM users WHERE email=?')){
		  $stmt -> bind_param('s', $email);
		  if ($stmt -> execute()){
			$res = $stmt -> get_result();
			if ($res -> num_rows > 0){ // если e-mail уже есть
				$stmt -> close();
				$res -> close();
				$er[4] = 'Такой E-Mail адрес уже зарегистрирован!';
			} else $er[4] = 1; // если всё нормально
		  } else error_rez('ОШИБКА при обращении к базе данных для проверки E-Mail !');
	   } else error_rez('ОШИБКА при обращении к базе данных для проверки E-Mail !');
	}
// изменение пароля в базе
	if (isset($er[4]) && $er[4] == 1){
		$sql = 'UPDATE users
				SET email=?
				WHERE id=?';
	  if ($stmt = $conn -> prepare($sql)){
		$stmt -> bind_param('si', $email, $_SESSION['user_id']);
		if ($stmt -> execute())
			$message_ok = 'E-mail успешно изменён.';
		else error_rez('Ошибка смены пароля в базе данных!');
	  }	else error_rez('Ошибка смены пароля в базе данных!');
	}
}
// вывод старого e-mail на страницу
$res = $conn -> query('SELECT email FROM users
					   WHERE id ="'.$_SESSION['user_id'].'" ');
!$res ? error_rez('Запрос к базе данных для проверки e-mail не выполнен!') : false;
$row = $res -> fetch_row();
echo '<b><i>'.$row[0].'</i></b>';
?>
  </td>
</tr>
<tr>
<td><h4>Новый E-Mail:</h4></td>
<td><input type='text' size='20' maxlength='20' name='email'
<?php // если нажата кнопка и была ошибка, то оставляем предыдущий e-mail
isset($er[4]) && $er[4] <> 1 ? print 'value="'.$_POST['email'].'"' : false;
?>
></td>
</tr>
<tr>
<td colspan='2' class='gor_line'>
<?php
isset($er[4]) && $er[4] <> 1 ? mes_er($er[4]) : false;
isset($message_ok) ? mes_ok($message_ok) : false;
?>
</td>
</tr>
<tr><td>&nbsp;</td>
  <td>
  <button type='submit' name='enterprof_email' value='ok' class='button'>Изменить</button>
  </td>
</tr>
</form>
</table>
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