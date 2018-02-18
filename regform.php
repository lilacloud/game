<?php
require_once('blocks/connect.php');
require_once('functions.php');
/*
Переменные, которые отправляются в register.php через POST
username 	- ник пользователя (имя тренера)
pass1 		- пароль 1
pass2 		- пароль 2
email 		- электронный адрес
fname 		- Имя
lname 		- Фамилия
gender 		- пол
day 		- день рождения
month 		- месяц рождения
year 		- год рождения
place 		- место жительства
kod 		- код безопасности введённый пользователем
reg_button 	- кнопка ввода данных регистрации (должна иметь значение "Регистрация")

Текущие переменные
$res 	- результат выборки количества записей в таблице kod_bezop
$kol 	- колличество записей в таблице kod_bezop
$pic 	- cлучайное число от 1 до количества записей в таблице kod_bezop
$res(2) - результат выборки записи с таблицы kod_bezop с id=$pic
$myrow 	- массив записи результата выборки с таблицы kod_bezop с id=$pic
*/
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="description" content="">
<meta name="keywords" content="">
<title>Футбольный прогноз | Регистрация пользователя</title>
<link href="styles.css" rel="stylesheet" type="text/css">
<script>
function chk_reg(){
var flag = true;
	if (document.regform.username.value=="" ||
		document.regform.pass1.value=="" ||
		document.regform.pass2.value=="" ||
		document.regform.email.value=="" ||
		document.regform.fname.value=="" ||
		document.regform.kod.value==""){
			flag = false;
			alert("Не все обязательные поля заполнены!!!");
	}
	return flag;
}
</script>
</head>
<body>
<!-- Подключается Header -->
<?php require_once("blocks/header.php"); ?>
<!------------------------->
<table class="telo" cellpadding="0" cellspacing="0">
<tr>
	<!-- Подключается верхнее меню -->
	<?php require_once("blocks/top_menu.php"); ?>
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
		<form name='regform' method='post' action='register.php' onsubmit='return chk_reg();'>
		<table width=100% border='0' cellspacing='0' cellpadding='0'>
			<tr>
				<td width=25% align='right'><h3><b>Ник (Логин): &nbsp;</b></h3></td>
				<td width='10px'><font color='red'>*</font></td>
				<td><input type='text' size='25' maxlength='20' name='username'></td>
			</tr>
			<tr>
				<td width=25% align='right'><h3><b>Пароль: &nbsp;</b></h3></td>
				<td><font color='red'>*</font></td>
				<td><input type='password' size='25' maxlength='25' name='pass1'></td>
			</tr>
			<tr>
				<td width=25% align='right'><h3><b>Повторите пароль: &nbsp;</b></h3></td>
				<td><font color='red'>*</font></td>
				<td><input type='password' size='25' maxlength='25' name='pass2'></td>
			</tr>
			<tr>
				<td width=25% align='right'><h3><b>Ваш E-Mail: &nbsp;</b></h3></td>
				<td><font color='red'>*</font></td>
				<td><input type='text' size='25' name='email'></td>
			</tr>
			<tr>
				<td width=25% align='right'><h3><b>Имя: &nbsp;</b></h3></td>
				<td><font color='red'>*</font></td>
				<td><input type='text' size='25' maxlength='20' name='fname'></td>
			</tr>
			<tr>
				<td width=25% align='right'><h3><b>Фамилия: &nbsp;</b></h3></td>
				<td>&nbsp;</td>
				<td><input type='text' size='25' name='lname'></td>
			</tr>
			<tr>
				<td width=25% align='right'><h3><b>Пол: &nbsp;</b></h3></td>
				<td>&nbsp;</td>
				<td>
					<b><input type='radio' name='gender' value='M'> Мужской</b>
					&nbsp;&nbsp;
					<b><input type='radio' name='gender' value='W'> Женский</b>
				</td>
			</tr>
			<tr>
				<td width=25% align='right'><h3><b>Дата рождения: &nbsp;</b></h3></td>
				<td>&nbsp;</td>
				<td>
				<select name='day'>
				<option value='0' selected>День</option>
<?php
				for ($i=1;$i<=31;$i++){
					echo "<option value=\"$i\">$i</option>";
				}
?>
				</select>
				<select name='month'>
				<option value='0' selected>Месяц</option>
<?php
				$monthes = array (0,"января","февраля","марта","апреля","мая","июня","июля","августа","сентября","октября","ноября","декабря");
				for ($i=1;$i<=12;$i++){
					echo "<option value=\"$i\">$monthes[$i]</option>";
				}
?>
 				</select>
				<select name='year'>
					<option value='0' selected>Год</option>
<?php
				for ($i=1940;$i<=date("Y")-10;$i++){
					echo "<option value=\"$i\">$i</option>";
				}
?>
				</select>
				</td>
			</tr>
			<tr>
				<td width=25% align='right'><h3><b>Место жительства: &nbsp;</b></h3></td>
				<td>&nbsp;</td>
				<td><input type='text' size='40' maxlength='50' name='place'></td>
			</tr>
			<tr>
				<td width=25% align='right'><h3><b>Код безопасности: &nbsp;</b></h3></td>
				<td><font color='red'>*</font></td>
<?php
				//Получение количества строк в таблице
				$res = $conn -> query("SELECT COUNT(*) FROM kod_bezop");
				if (!$res) error_rez('ОШИБКА! SQL запрос не выполнен при загрузке изображения кода безопасности!');
 				if ($res -> num_rows == 0)
					error_rez('Информация по запросу кода безопасности не может быть извлечена,
								в таблице нет записей!');
				$kol = $res -> fetch_array();
				//Получение случайной картинки кода безопасности
				$pic = mt_rand(1, $kol[0]);
				$res = $conn -> query("SELECT img, sum FROM kod_bezop WHERE id='$pic'");
				if (!$res) error_rez('ШИБКА! SQL запрос не выполнен при загрузке изображения кода безопасности!');
				if ($res -> num_rows == 0)
					error_rez('Информация по запросу кода безопасности не может быть извлечена,
								в таблице нет записей!');
				$myrow = $res -> fetch_assoc();
print "
				<td>
				<input type='hidden' name='kod_b' value='{$myrow['sum']}'>
				<img align='absmiddle' src='{$myrow['img']}'>&nbsp;
				<input type='text' size='1' maxlength='2' name='kod'>";
?>
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td colspan='2'><br><hr align='left' width='330px'><font color='red'>*</font>
				&nbsp;- поля, обязательные для заполнения</td>
			</tr>
			<tr>
				<td width=25%>&nbsp;</td><td>&nbsp;</td>
				<td><br><input type="submit" class="button" name="reg_button" value="Регистрация">
				</td>
			</tr>
		</table>
		</form>
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