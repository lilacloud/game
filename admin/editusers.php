<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['login']) || !isset($_SESSION['rights']) ||
	($_SESSION['rights'] <> 2 && $_SESSION['rights'] <> 1)){
	//header('Location:../index.php');
	echo '<script type="text/javascript">';
	echo 'window.location.href="../index.php";';
	echo '</script>';
	exit();
}
require_once('../blocks/connect.php');
require_once('functions.php');
/*
$active_button 	- указывает на то, что какая-то кнопка была нажата
				  (true - активна
				   false - неактивна)
$text_id		- id пользователя, которому нужно сделать изменения
$text			- текст того, на что нужно изменить данные
$fild			- наименование поля таблицы, которое нужно редактировать
$day			- значение дня, на которое будем менять
$month			- значение месяца, на которое будем менять
$year			- значение года, на которое будем менять
$i				- порядковый номер пользователя
$users			- массив со всеми данными всех пользователей
				  (id 		- id пользователя
				   username - имя пользователя
				   email	- e-mail пользователя
				   fname	- имя
				   lname	- фамилия
				   gender	- пол
				   date_b	- дата рождения
				   place	- место жительства
				   rights	- права пользователя
				   date_r	- дата регистрации
				   last_visit - дата последнего посещения)
$lname			- фамилия пользователя (если пустое значение, то = 'НЕТ')
$gender 		- пол пользователя (если пустое значение, то = 'НЕ ОПРЕДЕЛЕНО')
$data			- дата рождения пользователя (если пустое значение, то = 'НЕТ')
$place			- место рождения пользователя (если пустое значение, то = 'НЕТ')
$data_r			- дата регистрации пользователя
$last_visit		- дата последнего посещения
*/
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="description" content="">
<meta name="keywords" content="">
<title>Футбольный прогноз | Управление пользователями</title>
<link href="../styles.css" rel="stylesheet" type="text/css">
</head>
<body>
<!-- Подключается Header -->
<?php require_once('../blocks/header.php'); ?>
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
<h2><b>Редактирование учётных записей пользователей:</b></h2>
<h3><b>Список пользователей:</b></h3>
<table cellspacing="0" border="1" bordercolor="#75A54B">
<tr style="background-color:#A8BF89;">
<th class="ver_line">#</th>
<th class="ver_line">Пользователь</th>
<th class="ver_line">Пароль</th>
<th class="ver_line">Права</th>
<th class="ver_line">E-Mail</th>
<th class="ver_line">Имя</th>
<th class="ver_line">Фамилия</th>
<th class="ver_line">Пол</th>
<th class="ver_line">Дата<br>рождения</th>
<th class="ver_line">Место<br>жительства</th>
<th class="ver_line">Дата<br>регистрации</th>
<th class="ver_line">Последний<br>визит</th>
</tr>
<?php
$active_button = false;
//проверака нажатия различных кнопок
if (isset($_POST['pass_but']) && $_POST['pass_but'] == 'ok'){//изменение пароля$text_id = $conn -> real_escape_string(htmlspecialchars(trim($_POST['pass_id'])));
$text = $conn -> real_escape_string(htmlspecialchars($_POST['pass']));
$text = md5($text);
$fild = 'pass';
$active_button = true;
} elseif (isset($_POST['rights_but']) && $_POST['rights_but'] == 'ok'){//изменение прав пользователя$text_id = $conn -> real_escape_string(htmlspecialchars(trim($_POST['rights_id'])));
$text = $conn -> real_escape_string(htmlspecialchars($_POST['rights']));
$fild = 'rights';
$active_button = true;
} elseif (isset($_POST['email_but']) && $_POST['email_but'] == 'ok'){//изменение e-mail
$text_id = $conn -> real_escape_string(htmlspecialchars(trim($_POST['email_id'])));
$text = $conn -> real_escape_string(htmlspecialchars($_POST['email']));
$fild = 'email';
$active_button = true;
} elseif (isset($_POST['fname_but']) && $_POST['fname_but'] == 'ok'){//изменение Имени
$text_id = $conn -> real_escape_string(htmlspecialchars(trim($_POST['fname_id'])));
$text = $conn -> real_escape_string(htmlspecialchars($_POST['fname']));
$fild = 'fname';
$active_button = true;
} elseif (isset($_POST['lname_but']) && $_POST['lname_but'] == 'ok'){
//изменение Фамилия
$text_id = $conn -> real_escape_string(htmlspecialchars(trim($_POST['lname_id'])));
$text = $conn -> real_escape_string(htmlspecialchars($_POST['lname']));
$fild = 'lname';
$active_button = true;
} elseif (isset($_POST['gender_but']) && $_POST['gender_but'] == 'ok'){
//изменение пола
$text_id = $conn -> real_escape_string(htmlspecialchars(trim($_POST['gender_id'])));
$text = $conn -> real_escape_string(htmlspecialchars($_POST['gender']));
$fild = 'gender';
$active_button = true;
} elseif (isset($_POST['date_b_but']) && $_POST['date_b_but'] == 'ok'){
//изменение даты рождения
$text_id = $conn -> real_escape_string(htmlspecialchars(trim($_POST['date_b_id'])));
$day = $conn -> real_escape_string(htmlspecialchars($_POST['day']));
if ($day == NULL || $day == ''){//если пользователь отправил пустое значение  $day = '00';
}
$month = $conn -> real_escape_string(htmlspecialchars($_POST['month']));
if ($month == NULL || $month == ''){//если пользователь отправил пустое значение
  $month = '00';
}
$year = $conn -> real_escape_string(htmlspecialchars($_POST['year']));
if ($year == NULL || $year == ''){//если пользователь отправил пустое значение
  $year = '0000';
}
$text = date("Y-m-d", mktime(0, 0, 0, $month, $day, $year));
$fild = 'date_b';
$active_button = true;
} elseif (isset($_POST['place_but']) && $_POST['place_but'] == 'ok'){
//изменение места жительства
$text_id = $conn -> real_escape_string(htmlspecialchars(trim($_POST['place_id'])));
$text = $conn -> real_escape_string(htmlspecialchars($_POST['place']));
$fild = 'place';
$active_button = true;
}
//если была нажата какая-то кнопка
if (isset($active_button) && $active_button == true){
  $sql = 'UPDATE users
		  SET '.$fild.'=?
		  WHERE id=?';
  if ($stmt = $conn -> prepare($sql)){    $stmt -> bind_param('si', $text, $text_id);
    if ($stmt -> execute()){      $stmt -> close();
    } else error_rez('Ошибка при изменении данных пользователя в базе данных! '.$conn -> error);
  } else error_rez('Ошибка при изменении данных пользователя в базе данных! '.$conn -> error);
}
//выводим на экран всех пользователей клуба
$sql = 'SELECT id, username, email, fname, lname, gender, date_b,
			   place, rights, date_r, last_visit
		FROM users
		ORDER BY id';
if ($stmt = $conn -> prepare($sql)){  if ($stmt -> execute()){  	$res = $stmt -> get_result();
  	$stmt -> close();  }	else error_rez('Запрос на выборку пользователей не выполнен! '.$conn -> error);
} else error_rez('Запрос на выборку пользователей не выполнен! '.$conn -> error);
//если в базе есть пользователи
if ($res -> num_rows <> 0){  $i = 0;  while ($row = $res -> fetch_assoc()){  	$users[] = $row;
  	$i++;
echo '<tr ';
  	if (!($i%2)){//если чётная строка рисуем зебру
  	echo 'class="shadow"';
  	}
echo '><td>&nbsp;'.$i.'&nbsp;</td>
	<!-- Имя пользователя -->
	<td>&nbsp;<span class="username">'.$row['username'].'</span>&nbsp;</td>
  	<!-- Изменение пароля -->
  	<td nowrap><form method="post" action="editusers.php">
  	  <input type="password" name="pass" size="20" placeholder="Новый пароль"><br>
  	  <input type="hidden" name="pass_id" value="'.$row['id'].'"><center>
  	  <button type="submit" name="pass_but" value="ok">
	  <img src="../pics/key.png" style="vertical-align: middle">&nbsp;Изменить
	  </button></center></form>
	</td>
  	<!-- Изменение прав пользователя -->
  	<td><center ';
  	if ($row['rights'] == 1){  	echo 'style="color:#CC6600;">
  		<span style="background-image:url(../pics/fon2.jpg)">СУПЕРПОЛЬЗОВАТЕЛЬ</span>';
  	} elseif ($row['rights'] == 2){  	echo 'style="color:red">
  		<span style="background-image:url(../pics/fon2.jpg)">АДМИНИСТРАТОР</span>';
  	} elseif ($row['rights'] == 3){
  	echo 'style="color:green">
  		<span style="background-image:url(../pics/fon2.jpg)">ПОЛЬЗОВАТЕЛЬ</span>';
  	} else {//неизвестные праваecho'<img src="../pics/interrogatory.png" style="vertical-align: middle">&nbsp;Неизвестно';
  	}
echo'<br><form method="post" action="editusers.php">
	  <select name="rights">
	    <option value="1">суперпользователь</option>
	    <option value="2">администратор</option>
	    <option value="3">пользователь</option>
	  </select><br>
	  <input type="hidden" name="rights_id" value="'.$row['id'].'">
  	  <button type="submit" name="rights_but" value="ok">
	  <img src="../pics/rights.png" style="vertical-align:top">&nbsp;Изменить
	  </button>
	</form>';
echo'</center></td>
  	<!-- Изменение E-Mail-a -->
  	<td><center>&nbsp;
  	<span style="background-image:url(../pics/fon2.jpg)">'.$row['email'].'</span>
  	&nbsp;<br><form method="post" action="editusers.php">
  	  <input type="text" name="email" size="20" placeholder="Новый E-Mail"><br>
  	  <input type="hidden" name="email_id" value="'.$row['id'].'">
  	  <button type="submit" name="email_but" value="ok">
	  <img src="../pics/1463.png" style="vertical-align: middle">&nbsp;Изменить
	  </button></center></form>
  	</td>
  	<!-- Изменение Имени -->
  	<td><center>&nbsp;
  	<span style="background-image:url(../pics/fon2.jpg)">'.$row['fname'].'</span>
  	&nbsp;<br><form method="post" action="editusers.php">
  	  <input type="text" name="fname" size="20" placeholder="Новое Имя"><br>
  	  <input type="hidden" name="fname_id" value="'.$row['id'].'">
  	  <button type="submit" name="fname_but" value="ok">
	  <img src="../pics/1463.png" style="vertical-align: middle">&nbsp;Изменить
	  </button></center></form>
  	</td>
  	<!-- Изменение Фамилия -->';
  	//если в поле с фамилией пустое значение
  	if ($row['lname'] == ''){  	  $lname = 'НЕТ';
  	} else {  	  $lname = $row['lname'];
  	}
echo'<td><center>&nbsp;
  	<span style="background-image:url(../pics/fon2.jpg)">'.$lname.'</span>
  	&nbsp;<br><form method="post" action="editusers.php">
  	  <input type="text" name="lname" size="20" placeholder="Новое Фамилия"><br>
  	  <input type="hidden" name="lname_id" value="'.$row['id'].'">
  	  <button type="submit" name="lname_but" value="ok">
	  <img src="../pics/1463.png" style="vertical-align: middle">&nbsp;Изменить
	  </button></center></form>
  	</td>
  	<!-- Изменение пола -->';
  	//если поле не был указан пользователем
  	if ($row['gender'] == ''){
  	  $gender = 'НЕ ОПРЕДЕЛЕНО';
  	} else {
  	  $gender = $row['gender'];
  	}
echo'<td nowrap><center>
  	<span style="background-image:url(../pics/fon2.jpg)">'.$gender.'</span>
	<form method="post" action="editusers.php">
	  <select name="gender">
	    <option value="M">мужской</option>
	    <option value="W">женский</option>
	  </select><br>
	  <input type="hidden" name="gender_id" value="'.$row['id'].'">
  	  <button type="submit" name="gender_but" value="ok">
	  <img src="../pics/1463.png" style="vertical-align:top">&nbsp;Изменить
	  </button>
	</form></center></td>
  	<!-- Изменение даты рождения -->';
  	if (strtotime($row['date_b']) == 943916400){  	  $data = 'НЕТ';
  	} else {
  	  $data = date('d.m.Y', strtotime($row['date_b']));
  	}
echo'<td nowrap><center>&nbsp;
	<span style="background-image:url(../pics/fon2.jpg)">'.$data.'</span>&nbsp;
 	<form method="post" action="editusers.php">
  	<select name="day">
  	<option value="" selected>День</option>';
  	for ($l=1; $l<=31; $l++){  	echo '<option value="'.$l.'">'.$l.'</option>';
  	}
echo'</select>
    <select name="month">
	<option value="" selected>Месяц</option>';
	$monthes = array(0,"января","февраля","марта","апреля","мая","июня","июля","августа","сентября","октября","ноября","декабря");
	for ($j=1; $j<=12; $j++){	echo '<option value="'.$j.'">'.$monthes[$j].'</option>';
	}
echo'</select>
	<select name="year">
	<option value="" selected>Год</option>';
	for ($k=1940;$k<=date("Y")-10;$k++){
	echo '<option value="'.$k.'">'.$k.'</option>';
	}
echo'</select><br>
	<input type="hidden" name="date_b_id" value="'.$row['id'].'">
  	<button type="submit" name="date_b_but" value="ok">
	<img src="../pics/1463.png" style="vertical-align:top">&nbsp;Изменить
	</button></form></center></td>
  	<!-- Изменение места жительства -->';
  	//если поле не был указан пользователем
  	if ($row['place'] == ''){
  	  $place = 'НЕТ';
  	} else {
  	  $place = $row['place'];
  	}
echo'<td><center><span style="background-image:url(../pics/fon2.jpg)">
  	'.$place.'</span>
	<form method="post" action="editusers.php">
  	<input type="text" name="place" size="20" placeholder="Новое место жит-ва"><br>
  	<input type="hidden" name="place_id" value="'.$row['id'].'">
  	<button type="submit" name="place_but" value="ok">
	<img src="../pics/1463.png" style="vertical-align: middle">&nbsp;Изменить
	</button></form></center>
  	</td>';
  	$data_r = date('d.m.Y H:i', strtotime($row['date_r']));
  	$last_visit = date('d.m.Y H:i', strtotime($row['last_visit']));
echo'<td><center style="background-image:url(../pics/fon2.jpg)">
	'.$data_r.'</center></td>
  	<td><center style="background-image:url(../pics/fon2.jpg)">
  	'.$last_visit.'</center></td></tr>';
  }
} else mes_inf('В базе данных не найдено не одного пользователя !');
?>
</table><br><br><br><br><br>
			<!-------------------------->
		</td>
	</tr>
	</table>
	</td>
</tr>
<tr>
<?php require_once("../blocks/footer.php"); ?>
</tr>
</table>
</body>
</html>