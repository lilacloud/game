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

---

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
	<li ><a href='profile1.php'>Пароль/E-Mail</a></li>
	<li class='current'><a href='profile2.php'>Личные данные</a></li>
	</ul>
</td></tr>
</table>
<table>
<form name='editprof_else' method='post' action='profile2.php'>
<tr> <!-- Изменить Имя -->
<th colspan='2' align='left' class='gor_line'>
	<h3><b>Изменить Имя</b></h3></th>
</tr>
<tr>
<td><h4>&nbsp;</h4></td>
<td><input type='text' size='20' maxlength='20' name='fname'></td>
</tr>
<tr> <!-- Изменить Фамилию -->
<th colspan='2' align='left' class='gor_line'>
	<h3><b>Изменить Фамилию</b></h3></th>
</tr>
<tr>
<td><h4>&nbsp;</h4></td>
<td><input type='text' size='20' maxlength='20' name='lname'></td>
</tr>
<tr> <!-- Изменить пол -->
<th colspan='2' align='left' class='gor_line'>
	<h3><b>Изменить пол</b></h3></th>
</tr>
<tr>
<td><h4>&nbsp;</h4></td>
<td><input type='text' size='20' maxlength='20' name='gender'></td>
</tr>
<tr> <!-- Изменить дату рождения -->
<th colspan='2' align='left' class='gor_line'>
	<h3><b>Изменить дату рождения</b></h3></th>
</tr>
<tr>
<td><h4>&nbsp;</h4></td>
<td><input type='text' size='20' maxlength='20' name='date_b'></td>
</tr>
<tr> <!-- Изменить место жительства -->
<th colspan='2' align='left' class='gor_line'>
	<h3><b>Изменить место жительства</b></h3></th>
</tr>
<tr>
<td><h4>&nbsp;</h4></td>
<td><input type='text' size='20' maxlength='20' name='place'></td>
</tr>
<tr><td>&nbsp;</td>
  <td>
  <button type='submit' name='enterprof_else' value='ok' class='button'>Изменить</button>
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