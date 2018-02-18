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
com_id 		  - id выбраной команды
enterprof_com - если = 'ok', то удачное нажатие кнопки
turnir_id	  - id турнира клуба, для которого выбираем команду
kol_turnirs	  - количество турниров клуба которым выбираем команды
---
$res, $res1 - результаты всех выборок с базы данных
$row, $row1 - массив с одной записью выборкой с базы данных
$rows 		- массив со всеми записями выборками с базы данны
$message_ok - добавление команды выполнено успешно
$kol		- количество турниров клуба которым выбираем команды
$turnir		- id турнира клуба, для которого выбираем команду
$com		- id выбраной команды
$net		- флаг было ли сообщение о том, нет активных чемпионатов
			  для выбора команды
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
	<li class='current'><a href='profile.php'>Выбор команды</a></li>
	<li><a href='profile1.php'>Пароль/E-Mail</a></li>
	<li><a href='profile2.php'>Личные данные</a></li>
	</ul>
</td></tr>
</table>
<table>
<form name='editprof_com' method='post' action='profile.php'>
<tr> <!-- Выбор команды -->
<th colspan='4' align='left' class='gor_line'>
	<h4><b>Можно выбрать только одну команду в чемпионате</b></h4></th>
</tr>
<?php
// вносим в базу выбранную команду
if (isset($_POST['enterprof_com']) && $_POST['enterprof_com'] == 'ok'){  $kol = $conn -> real_escape_string(htmlspecialchars(trim($_POST['kol_turnirs'])));
  for ($i=1; $i<=$kol; $i++){  	$turnir{$i} = $conn -> real_escape_string(htmlspecialchars(trim($_POST["turnir_id{$i}"])));
  	$com{$i} = $conn -> real_escape_string(htmlspecialchars(trim($_POST["com_id{$i}"])));	$sql = 'UPDATE sl_com
			SET user_id=?, turnirs_id=?
			WHERE id=?';
	$stmt = $conn -> prepare($sql);
	$stmt -> bind_param('iii',$_SESSION['user_id'], $turnir{$i}, $com{$i});
	if ($stmt -> execute())
		$message_ok = 'Операция выполнена успешно.';
	else error_rez('Ошибка записи команды в базу данных!');
  }
}
// отбираем все турниры клуба
$res1 = $conn -> query('SELECT turnirs.id, turnirs.turnir_n, turnirs.active_t,
							   turnirs.turnir_r_id, sl_turnir_r.flag
						FROM turnirs
						LEFT JOIN sl_turnir_r
						ON turnirs.turnir_r_id = sl_turnir_r.id');
if (!$res1) error_rez('Запрос на выборку названий турниров клуба не выполнен!');
$i = 0;
$turnir_all = $res1 -> num_rows;
$net = 0;
while ($row1 = $res1 -> fetch_assoc()){ // массив с активными турнирами клуба
// проверяем есть ли уже выбранная команда для этого турнира клуба этим тренером
$res = $conn -> query("SELECT sl_com.com_n, sl_com.flag,
							  flag_t.turnir_n, flag_t.flag AS flag_tu
					   FROM sl_com
					   LEFT JOIN
					   	(SELECT turnirs.id, turnirs.turnir_n, turnirs.turnir_r_id,
					   	 sl_turnir_r.id AS sl_id, sl_turnir_r.flag
					   	 FROM turnirs
					   	 LEFT JOIN sl_turnir_r
					   	 ON turnirs.turnir_r_id = sl_turnir_r.id)
					   AS flag_t
					   ON sl_com.turnirs_id = flag_t.id
					   WHERE sl_com.user_id = {$_SESSION['user_id']}
					   AND flag_t.id = {$row1['id']}");
if (!$res) error_rez('Запрос на поиск выбранной игроком команды не выполнен!');
// если команда уже выбрана, выводим чемпионат клуба, флаг страны и команду
  if ($res -> num_rows <> 0){
	$row = $res -> fetch_assoc();
//echo '<pre>';
//print_r($row);
//echo '</pre>';
echo'<tr><td width="30"><img src="'.substr($row['flag_tu'], 1).'"
						style="vertical-align: middle"></td>
	<td><h4><b>'.$row['turnir_n'].' -</b></h4></td>
	<td width="30"><img src="'.substr($row['flag'], 1).'"></td>
	<td><i><b>'.$row['com_n'].'</b></i></td></tr>';
	$res -> close();
  } elseif($row1['active_t'] == '1'){//если выбранной команды ещё нет и турнир активный
	$i++;
	$res = $conn -> query('SELECT *
						   	FROM sl_com
						   	WHERE id_turnir_n
						   	IN (SELECT turnir_r_id FROM turnirs
							   WHERE turnir_r_id = "'.$row1['turnir_r_id'].'")
						   	AND user_id IS NULL
						   	ORDER BY com_n');
	if (!$res) error_rez('Запрос на выборку названий команд не выполнен!');
	while ($row = $res -> fetch_assoc()){
	  	$rows[] = $row;  // массив с названиями команд
	}
echo'<tr><td width="30"><img src="'.substr($row1['flag'], 1).'"
						style="vertical-align: middle"></td>
	 <td><h4>'.$row1['turnir_n'].' -</h4>
</td>
<td colspan="2">
	<select name="com_id'.$i.'">';
	foreach ($rows as $pole){
	echo '<option  value="'.$pole['id'].'">'.$pole['com_n'].'</option>';
	}
echo '
	</select>
	<input type="hidden" name="turnir_id'.$i.'" value="'.$row1['id'].'">
</td>
</tr>';
  } else { //если выбранной команды ещё нет и турнир неактивный  	$net++;
  }
}
/* если нет неодного турнира в котором учавствует тренер и нет
   активных в которых он мог бы принять участие */
if ($turnir_all == $net){  	echo '<br /><h3><img src="pics/edit_no.png" style="vertical-align: middle">&nbsp;
  		Сейчас для Вас нет активных чемпионатов для выбора команды !!!</h3>';
}
if ($i <> 0){
echo'<tr><td><input type="hidden" name="kol_turnirs" value="'.$i.'"></td>
  	<td>&nbsp;</td>
  	<td colspan="2">
  	<button type="submit" name="enterprof_com" value="ok" class="button">Выбрать</button>
  	</td></tr>';
}
?>
</form>
</table>
<?php isset($message_ok)? mes_ok($message_ok) : false; ?>
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