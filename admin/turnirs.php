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
$_GET['message'] - сообщение об успешной записи программы переданое через GET c prog_add.php
prog_add   - id турнира с которым будем работать передаётся через POST в programka.php
Переменные передаваемые сами себе через POST
add_turnir - если равно 'ок', то добавляем в базу новый турнир
name_t     - название турнира
season_t   - номер сезона
country		- id реального турнира
drop_turnir - если существует и имеет значение, то нужно удалить турнир с этим значением
active_sw	- переключатель активности набора команд в чемпионат (=id чемпионата,
			  с которым будем работать)
---
$turnir_n   - название турнира
$season     - номер сезона
$country	- id реального турнира
$ves_match  - id весомости матча в этом ткрнире
$rez		- результат выборки всех полей с таблицы 'turnirs' для вывода на страницу
$row		- массив со всеми значениями таблицы 'turnirs' для вывода на страницу
$rez1       - результат выборки всех полей с таблицы 'turnirs' для проверки повторяющихся турниров
$row1	    - массив со значениями с таблицы 'turnirs' для проверки повторяющихся турниров
$rez2	    - результат вставки значений в таблицу 'turnirs'
$rez3		- результат удаления турнира с таблицы 'turnirs'
$res		- результат выборки названий реальных турниров 'sl_turnir_r'
$message_ok - сообщение об удачной операции
$message_er - сообщение c ошибкой
$active_sw	- id чемпионата, которому меняем активность набора команд
*/
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="description" content="">
<meta name="keywords" content="">
<title>Футбольный прогноз | Управление турнирами</title>
<link href="../styles.css" rel="stylesheet" type="text/css">
<script>
function del_tur(){
  var otvet = confirm('Вы действительно хотите удалить этот тур?');
  return otvet;
}
function chk_new(){
var flag = true;
  if (document.new.name_t.value=="" ||
	document.new.season_t.value=="" ||
	document.new.tur_t.value==""){
		flag = false;
		alert("Не все поля заполнены !!!");
  } else if (isNaN(parseFloat(document.new.season_t.value)) &&
  			!isFinite(document.new.season_t.value)) {			flag = false;
			alert("В поле № сезона указано неправильное число !!!");  	} else if (isNaN(parseFloat(document.new.tur_t.value)) &&
  			  !isFinite(document.new.tur_t.value)) {
			flag = false;
			alert("В поле № тура указано неправильное число !!!");
  	  }
return flag;
}
</script>
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
<h2><b>Список турниров клуба:</b></h2>
<table>
<tr><th>Название</th><th>№<br>сезона&nbsp;</th><th>&nbsp;Страна&nbsp;</th>
	<th>Набор<br>команд</th><th>Жеребьёвка</th><th>Прогр./Счёт</th>
	<th>Подсчёт<br>результатов</th></tr>
<?php
// изменяем активность набора команд в чемпионат
if (isset($_POST['active_sw']) && $_POST['active_sw'] <> ''){	$active_sw = $conn -> real_escape_string(htmlspecialchars(trim($_POST['active_sw'])));
	$sql = 'SELECT active_t FROM turnirs WHERE id=?';
	if ($stmt = $conn -> prepare($sql)){		$stmt -> bind_param('i', $_POST['active_sw']);
		if ($stmt -> execute()){			$res = $stmt -> get_result();
			$act = $res -> fetch_assoc();
			if ($act['active_t'] == 1){				$ac = 0;
			} elseif ($act['active_t'] == 0){				$ac = 1;			}
			$stmt -> close();
			$res -> close();
			$sql = 'UPDATE turnirs SET active_t=? WHERE id=?';
			if ($stmt = $conn -> prepare($sql)){
				$stmt -> bind_param('ii', $ac, $_POST['active_sw']);
				if ($stmt -> execute()){					$stmt -> close();
				} else error_rez('Невозможно изменить активность набора команд!');
			} else error_rez('Невозможно изменить активность набора команд!');
		} else error_rez('Невозможно изменить активность набора команд!');
	} else error_rez('Невозможно изменить активность набора команд!');
}
// вставляем в базу турнир
if (isset($_POST['add_turnir']) && $_POST['add_turnir']=='ok'){
	$turnir_n = $conn -> real_escape_string(htmlspecialchars(trim($_POST['name_t'])));
	$season = $conn -> real_escape_string(htmlspecialchars(trim($_POST['season_t'])));
	$country = $conn -> real_escape_string(htmlspecialchars(trim($_POST['country'])));
	$ves_match = $conn -> real_escape_string(htmlspecialchars(trim($_POST['ind_ves_match'])));
	$rez1 = $conn -> query('SELECT * FROM turnirs');
	while ($row1 = $rez1 -> fetch_assoc()){
		if ($row1['turnir_n'] == $turnir_n && $row1['season_nom'] == $season)
			$message_er = 'Такой турнир уже есть, его добавить невозможно!';
	}
	if (!isset($message_er)){ // если турнира такого ещё не было, то можно вносить в базу
	  $sql = "INSERT INTO turnirs (turnir_n, season_nom, active_t, turnir_r_id, ves_match_id)
						   VALUES (?, ?, '1', ?, ?)";
	  if ($stmt = $conn -> prepare($sql)){	  	$stmt -> bind_param('siii', $turnir_n, $season, $country, $ves_match);
	    if ($stmt -> execute()){
	      $message_ok = 'Новый турнир клуба добавлен успешно!';
	      $stmt -> close();
	    }	else $message_er = 'Внести новый турнир клуба не удалось!';
	  } else $message_er = 'Не удалось подготовить данные для внесения нового турнира!';
	}
}
// удаляем выбраный турнир с базы
if (isset($_POST['drop_turnir']) && $_POST['drop_turnir']<>''){
	$rez3 = $conn -> query("DELETE turnirs, r_igra FROM turnirs
								   LEFT JOIN r_igra
								   ON turnirs.id = r_igra.turnir_id
								   WHERE turnirs.id = {$_POST['drop_turnir']}");
	if (!$rez3){
		$message_er = 'Не удалось удалить турнир!';
	} else $message_ok = 'Турнир удалён успешно!';
}
// выводим турниры с базы на страницу
$rez = $conn -> query('SELECT * FROM turnirs
								LEFT JOIN sl_turnir_r
								ON turnirs.turnir_r_id = sl_turnir_r.id
								ORDER BY turnirs.turnir_n, turnirs.season_nom');
if ($rez -> num_rows > 0){
   while ($row = $rez -> fetch_array()){
// название чемпионата и № сезона
print'<tr>
	  <td>'.$row[1].'</td><td><center>'.$row['season_nom'].'</center></td>
	  <td><center>
	  <img src="..'.$row['flag'].'" style="vertical-align:middle">
	  </center>';
// доступен ли набор команд
echo "<td><form method='post' action='turnirs.php'>
		<button type='submit' name='active_sw' value='{$row[0]}'>
		   	<img src='../pics/";
		if ($row['active_t'] == 1){
			echo "light_on.png' style='vertical-align: middle'>&nbsp;ДА";
		} else {			echo "light_off.png' style='vertical-align: middle'>&nbsp;НЕТ";
		  }
echo "	</button>
		  </form></td><td>";
// жеребьёвка
	  if ($row['active_t'] == 1){	  	echo '<center><img src="../pics/cancel.png" alt="Запрещена"
	  		title="Запрещена" style="vertical-align: middle"></center></td>';
	  } elseif ($row['active_t'] == 0){	  	$sql = 'SELECT id
	  			FROM igra
	  			WHERE turnir_id = ?';
	  	if ($stmt = $conn -> prepare($sql)){	  	  $stmt -> bind_param('i', $row[0]);
	  	  if ($stmt -> execute()){			$res = $stmt -> get_result();
			$stmt -> close();
	  	  } else error_rez('Ошибка проверки жеребьёвки !!!');
	  	} else error_rez('Ошибка проверки жеребьёвки !!!');
echo "<form method='post' action='jrebiy.php'";
	    if ($res -> num_rows <> 0){
	  echo "onsubmit='return confirm(\"В этом турнире жеребьёвка уже проведена !!! \\nВы уверены, что хотите сделать её заново ?\")?true:false;'";
	    }
echo '>'; // отправляем информацию что уже была жеребьёвка, если была
		if ($res -> num_rows <> 0){		  echo '<input type="hidden" name="status" value="1">';
		}
echo "	<button type='submit' name='jrebiy' value='{$row[0]}'>
		<img src='../pics/rols.png' style='vertical-align: middle'>&nbsp;";
		if ($res -> num_rows == 0){		  echo 'НЕТ';
		  $no_jrebiy = 1; //жребий не проводился
		} else {		  echo 'ЕСТЬ';
		}
echo "	</button>
		  </form></td>";
	  } else {
	  	$message_er = 'Ошибка в турнире: <i>'.$row[1].'</i> !!!';
	  }
// программы турнира
echo "<td><form method='post' action='programka.php'";
	  if (isset($no_jrebiy) && $no_jrebiy == 1){
	    echo "onsubmit='return alert(\"В этом турнире жеребьёвка ещё не проведена !!! \\nСоставить программу невозможно !!!\"),false;'";
	  }
echo ">
		<button type='submit' name='prog_add' value='{$row[0]}'>
			<img src='../pics/edit_pr.png' style='vertical-align: middle'>&nbsp;Программа
		</button>
		  </form></td>";
// подсчёт результатов тура
	$sql = 'SELECT id
			FROM r_igra
			WHERE turnir_id = ?';
	if ($stmt = $conn -> prepare($sql)){
	  $stmt -> bind_param('i', $row[0]);
	  if ($stmt -> execute()){
		$ress = $stmt -> get_result();
		$stmt -> close();
	  } else error_rez('Ошибка проверки наличия программ !!!');
	} else error_rez('Ошибка проверки наличия программ !!!');
echo '<td><form method="post" action="podschet.php"';
	if ($ress -> num_rows == 0){
	  echo "onsubmit='return alert(\"В этом турнире не составлено не одной программы !!! \\nПодсчитать результаты туров невозможно !!!\"),false;'";
	}
echo '>
	<button type="submit" name="turnir" value="'.$row[0].'">
	  <img src="../pics/calc.gif" style="vertical-align: middle">&nbsp;Подсчёт
	</button>
	</form></td>';
// удаление турнира
echo "	<td><form method='post' action='turnirs.php' onsubmit='return del_tur();'>
		<button type='submit' name='drop_turnir' value='{$row[0]}'>
			<img src='../pics/drop.png' style='vertical-align: middle'>&nbsp;Удалить
		</button>
		  </form></td>";
echo '</tr>';
   }
} else {echo '<tr><td colspan="3">';
	mes_inf('База данных наименований турниров пуста!');
}
?>
	 </td></tr>
<tr><td>&nbsp;</td></tr>
</table>
<table>
<tr>
<td colspan="5"><b>Ввести новый турнир клуба:</b></td>
</tr>
<tr><th>Название</th>
<th>№<br>&nbsp;&nbsp;сезона&nbsp;&nbsp;</th>
<th>&nbsp;Страна&nbsp;</th>
<th>Индекс<br>весомости матча<br>(для рейтинга)</th>
<th>&nbsp;</th></tr>
<form method="post" action="turnirs.php" name="new" onsubmit="return chk_new();">
<tr>
	<td><center><input name='name_t' type='text' size='30'></center></td>
	<td><center><input name='season_t' type='text' size='3' maxlenth='2'></center></td>
	<td><center>
	<select name="country">
<?php
// отбираем названия турниров
$res = $conn -> query("SELECT * FROM sl_turnir_r ORDER BY turnir_n");
if (!$res)	error_rez('Запрос на выборку названий турниров не выполнен!');
while ($row4 = $res -> fetch_assoc()){
	$myrow4[] = $row4;	// массив с названиями турниров
}
foreach ($myrow4 as $pole){
echo "<option value='{$pole['id']}'>{$pole['turnir_n']}</option>";
	}
$res -> close();
?>
	</select>
	</center></td>
	<td><center>
	<select name="ind_ves_match">
<?php
// отбираем все возможные индексы весомости матча
$sql = 'SELECT *
		FROM sl_ves_matcha
		  ORDER BY ind DESC';
if ($stmt = $conn -> prepare($sql)){
  if ($stmt -> execute()){
	$ress = $stmt -> get_result();
	$stmt -> close();
	while ($row = $ress -> fetch_assoc()){
      $ves_match_ind[] = $row;//массив со всеми индексами весомости матча
	}
  } else error_rez('Запрос на выборку индекса весомости матча не выполнен! '.$conn -> error);
} else error_rez('Запрос на выборку индекса весомости матча не выполнен! '.$conn -> error);
foreach ($ves_match_ind as $pole){echo '<option value="'.$pole['id'].'">'.$pole['type'].' - '.$pole['ind'].'</option>';
}
?>
	</select></center></td>
	<td>&nbsp;<button type="submit" name="add_turnir" value="ok">
	  <img src="../pics/add.png" style="vertical-align: middle">&nbsp;Добавить
	</button></td>
</tr>
</form>
</table><br><br>
<?php
isset($message_ok) ? mes_ok($message_ok) : false;
isset($message_er) ? mes_er($message_er) : false;
isset($_GET['message']) && $_GET['message'] == 'true' ? mes_ok('Программа записана успеншно!') : false;
?>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<!---конец рабочей састи сайта--->
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