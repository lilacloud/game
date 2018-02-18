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
$_POST['prog_add'] - id выбранного турнира для создания новой программы
					или редактирования уже существующей
$rez 	- результат выборки таблиц 'turnirs' и 'r_igra'
$myrow 	- массив с выборкой турнира и программой к нему (от $rez)
$rez1 	- результат выборки названий реальных турниров из словаря
$rez2 	- результат выборки названий реальных команд из словаря
$myrow1 - массив названий реальных турниров
$myrow2 - массив названий реальных команд
$dt		- массив с датой и временем выбранные с базы данных для отображения в форме
$data	- дата в нужном формате для отображения в форме
$time	- время в нужном формате для отображения в форме
$chem	- id чемпионата, с которым работаем
$tur	- номер текущего тура
$r_active	- признак активности игры в програме
$lock_yes	- признак того, есть ли матчи, времени до начала которых осталось
			  30 мин. и меньше
---
chemp$i 	- название реального турнира (передаётся через POST в prog_add.php)
com$i.1 	- название первой реальной команды (передаётся через POST в prog_add.php)
com$i.2 	- название второй реальной команды (передаётся через POST в prog_add.php)
pdate$i 	- дата проведения реальной игры (передаётся через POST в prog_add.php)
ptime$i 	- время проведения реальной игры (передаётся через POST в prog_add.php)
prog_add 	- id турнира, которому нужно добавить или изменить программу
		   (принимается с turnirs.php через POST)
prog_add2 	- id турнира, которому нужно добавить программу
			(передаётся в prog_add.php через POST)
r_igra_id$i - id матча, который нужно редактировать
status	- передаёт информацию в prog_add.php что нужно делать с данными
		  	('new' - вставить в базу новые значения,
		   	'edit' - редактировать уже существующие значения)
prog_ok 	- флаг, если равен 'ok', то переменные удачно занеслись в POST
tur			- номер нового тура
*/
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="description" content="">
<meta name="keywords" content="">
<title>Футбольный прогноз | Составление программы на тур</title>
<link href="../styles.css" rel="stylesheet" type="text/css">
<script>
function chk_prog(){
var flag = true;
  if (document.prog_form.tur.value==""){  	flag = false;
    alert("Не заполнен номер тура !!!");
  }
  if (document.prog_form.pdate1.value=="" || document.prog_form.pdate2.value=="" ||
  	  document.prog_form.pdate3.value=="" || document.prog_form.pdate4.value=="" ||
  	  document.prog_form.pdate5.value=="" || document.prog_form.pdate6.value=="" ||
  	  document.prog_form.pdate7.value=="" || document.prog_form.pdate8.value=="" ||
  	  document.prog_form.pdate9.value=="" || document.prog_form.pdate10.value=="" ||
  	  document.prog_form.pdate11.value=="" || document.prog_form.pdate12.value=="" ||
  	  document.prog_form.ptime1.value=="" || document.prog_form.ptime2.value=="" ||
  	  document.prog_form.ptime3.value=="" || document.prog_form.ptime4.value=="" ||
  	  document.prog_form.ptime5.value=="" || document.prog_form.ptime6.value=="" ||
  	  document.prog_form.ptime7.value=="" || document.prog_form.ptime8.value=="" ||
  	  document.prog_form.ptime9.value=="" || document.prog_form.ptime10.value=="" ||
  	  document.prog_form.ptime11.value=="" || document.prog_form.ptime12.value==""){
    flag = false;
    alert("Не все обязательные поля заполнены !!!");
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
<?php
// если нет POST и GET, то прекращаем работу
if (!isset($_POST['prog_add']) && (!isset($_GET['tur']) || !isset($_GET['chemp']))){
	error_rez('Ошибка при работе с програмой на тур!');
} elseif (isset($_GET['chemp'])){ //проверяем данные переданные через GET  $sql = 'SELECT id FROM turnirs';
  if ($stmt = $conn -> prepare($sql)){	if ($stmt -> execute()){
		$res = $stmt -> get_result();
		$stmt -> close();
	} else error_rez('Ошибка проверки имеющихся турниров и туров игр!');
  } else error_rez('Ошибка проверки имеющихся турниров и туров игр!');
  while ($row = $res -> fetch_row()){
	$rows[] = $row;
  }
  // находим максимальные значения турниров и туров
  $turnir_max = 0; //максимальное id турниров в базе
  foreach ($rows as $pole){
	if ($pole[0] > $turnir_max){
		$turnir_max = $pole[0];
	}
  } /* проверяем что-бы id турнира был не меньше 0, не больше максимального
   	  значения id турнира и было числом */
  if ($_GET['chemp'] < 1 || $_GET['chemp'] > $turnir_max || !is_numeric($_GET['chemp'])){  	error_rez('Ошибка при работе с програмой на тур!');
  }
  unset($row, $rows);
}
// выбираем турнир с программами (если они есть)
$sql = 'SELECT * FROM turnirs
        LEFT JOIN r_igra
        ON turnirs.id = r_igra.turnir_id
		WHERE turnirs.id = ?';
if ($stmt = $conn -> prepare($sql)){	isset($_POST['prog_add']) ? $chem = $_POST['prog_add'] : false;
	isset($_GET['tur']) && isset($_GET['chemp']) ? $chem = $_GET['chemp'] : false;
	$stmt -> bind_param('i', $chem);
	if ($stmt -> execute()){
		$rez = $stmt -> get_result();
		$stmt -> close();
	} else error_rez('Запрос на выборку туров текущего чемпионата не выполнен!');
} else error_rez('Запрос на выборку туров текущего чемпионата не выполнен!');
while ($row = $rez -> fetch_assoc())
$myrow[] = $row; // массив с нужным турниром и программами к нему (если они есть)
//echo '<pre>';
//print_r($myrow);
//echo '</pre>';
echo '<h3>Программы на: &nbsp;&nbsp;<span class="theme">
	  "'.$myrow[0]["turnir_n"].'" (Сезон '.$myrow[0]["season_nom"].')</span></h3>';
// формирование туров
$max = 0; // количество туров в базе в этом турнире
foreach ($myrow as $pole){	if ($pole['tur_nom'] > $max){		$max = $pole['tur_nom'];
	}
}
/* проверяем что-бы № тура был не меньше 0, не больше максимального
   значения № тура в этом турнире, и было числом */
  if (isset($_GET['tur']) && ($_GET['tur'] < 1 || $_GET['tur'] > $max || !is_numeric($_GET['tur']))){
  	error_rez('Ошибка при работе с програмой на тур!');
  }
// если в базе есть какие-то туры в этом турнире - выводим их
if ($max <> 0){
echo '<h3><b>Пройденные туры: </b></h3><ul class="zakladki">';
  for ($i=1; $i<=$max; $i++){
?><li class="<?php isset($_GET['tur']) && $i==$_GET['tur']? print 'current': false;?>">
<a href="programka.php?tur=<?php echo $i.'&chemp='.$chem; ?>"><?php echo $i; ?></a></li>
<?php
  }
echo '</ul><br><br>';
} else echo '<b>В этом турнире не сыграно не одного матча!</b>';
//------- конец формирования туров ---------
if (isset($_GET['tur']) && isset($_GET['chemp'])){$tur = $conn -> real_escape_string(htmlspecialchars(trim($_GET['tur'])));$sql = 'SELECT * FROM turnirs
        LEFT JOIN r_igra
        ON turnirs.id = r_igra.turnir_id
		WHERE turnirs.id = ? AND r_igra.tur_nom = ?';
  if ($stmt = $conn -> prepare($sql)){  	$stmt -> bind_param('ii', $chem, $tur);
  	if ($stmt -> execute()){
  		$res = $stmt -> get_result();
  		$stmt -> close();
  	} else error_rez('Запрос на выборку тура и его программы не выполнен!');
  }	else error_rez('Запрос на выборку тура и его программы не выполнен!');
  unset($row, $myrow);
  while ($row = $res -> fetch_assoc()){	$myrow[] = $row;  // массив с программой в выбранном туре в текущем турнире
  }
  for ($i=0; $i<count($myrow); $i++){  	$r_active[$i+1] = $myrow[$i]['r_active'];
  }
}
?>
<form method='post' action='prog_add.php' name='prog_form' onsubmit='return chk_prog();'>
<h3><b style='float:left;'>
<?php
if (isset($_POST['prog_add']) && !isset($_GET['tur'])){ // если новый тур	echo 'Добавить новый тур:';
} elseif (isset($_GET['tur'])){ // если редактируем имеющийся тур    echo 'Редактировать тур:';
  }
?>
&nbsp;&nbsp;</b></h3>
<input name="tur" type="text" size="2" maxlength="3" style='text-align:center;'
<?php // если новый тур, то делаем его следующим за имеющимся
$newtur = $max + 1;
isset($_POST['prog_add'])&&!isset($_GET['tur'])? print 'value="'.$newtur.'" readonly' : false;
// если есть номер тура, то мы его пишем в поле формы
isset($_GET['tur']) ? print 'value="'.$_GET['tur'].'" readonly' : false; ?>
><br><br>
<table cellspacing="0">
<tr>
<th>&nbsp;</th><th>Турнир</th><th>Команда 1</th><th>&nbsp;</th><th>Команда 2</th><th>Дата и время</th>
<?php // если нужно ввести новую программу
isset($_GET['tur']) ? print '<th>Счёт</th>' : false; ?>
</tr>
<?php
// отбираем название турниров
$rez1 = $conn -> query("SELECT * FROM sl_turnir_r ORDER BY turnir_n");
if (!$rez1)	error_rez('Запрос на выборку названий турниров не выполнен!');
// отбираем название комманд
$rez2 = $conn -> query("SELECT * FROM sl_com ORDER BY id_turnir_n, com_n");
if (!$rez2) error_rez('Запрос на выборку названий команд не выполнен!');
while ($row1 = $rez1 -> fetch_assoc()){
	$myrow1[] = $row1;	// массив с названиями турниров
}
while ($row2 = $rez2 -> fetch_assoc()){
	$myrow2[] = $row2;  // массив с названиями команд
}
for($i=1; $i<=15; $i++){
echo "
<tr>
	<td width='25'>$i.</td>
	<td width='200'>
	<select "; /*если выводим существующий тур и он не последний, или активность
	данной игры в этом туре = 0 (тур сыгран и закрыт) или = 2 (за 30 мин. до
	начала реальной игры редактировать можно только счёт), то редактировать нельзя */
	if ((isset($_GET['tur']) && isset($_GET['chemp']) && $_GET['tur'] <> $max) ||
	(isset($r_active) && $r_active[$i] == 0) || (isset($r_active) && $r_active[$i] == 2)){	  echo 'disabled ';
	}
echo "name='chemp$i'>";
	if (isset($_GET['tur']) && isset($_GET['chemp'])){
	  foreach ($myrow1 as $pole){
		if ($pole['id'] == $myrow[$i-1]['r_turnir_id']){
		print "<option selected value='{$pole['id']}'>{$pole['turnir_n']}</option>";
		}
	  }
	}
	foreach ($myrow1 as $pole){		print "<option value='{$pole['id']}'>{$pole['turnir_n']}</option>";
	}
echo "
	</select>
	</td>
	<td align='right' width='170'>
	<select ";
	if((isset($_GET['tur']) && isset($_GET['chemp']) && $_GET['tur'] <> $max) ||
	(isset($r_active) && $r_active[$i] == 0) || (isset($r_active) && $r_active[$i] == 2)){
	  echo 'disabled ';
	}
echo "name='com$i.1'>";
	if (isset($_GET['tur']) && isset($_GET['chemp'])){
		foreach ($myrow2 as $pole){
		if ($pole['id'] == $myrow[$i-1]['r_com1_id'])
			echo "<option selected value='{$myrow[$i-1]['r_com1_id']}'>{$pole['com_n']}</option>";
		}
	}
	foreach ($myrow2 as $pole){
		print "<option value='{$pole['id']}'>{$pole['com_n']}</option>";
	}
echo "
	</select>
	</td>
	<td width='20'><center><b>VS</b></center></td>
	<td width='170'>
	<select ";
	if((isset($_GET['tur']) && isset($_GET['chemp']) && $_GET['tur'] <> $max) ||
	(isset($r_active) && $r_active[$i] == 0) || (isset($r_active) && $r_active[$i] == 2)){
	  echo 'disabled ';
	}
echo "name='com$i.2'>";
	if (isset($_GET['tur']) && isset($_GET['chemp'])){
		foreach ($myrow2 as $pole){
		if ($pole['id'] == $myrow[$i-1]['r_com2_id'])
			echo "<option selected value='{$myrow[$i-1]['r_com2_id']}'>{$pole['com_n']}</option>";
		}
	}
	foreach ($myrow2 as $pole){
		print "<option value='{$pole['id']}'>{$pole['com_n']}</option>";
	}
echo "
	</select>
	</td>
	<td width='145' nowrap>";
	    if (isset($_GET['tur']) && isset($_GET['chemp'])){
			$dt = explode(" ",$myrow[$i-1]['r_date']);
			$data = date('d.m.Y', strtotime($dt[0]));
			$time = date('H:i', strtotime($dt[1]));
		}
echo "	<input ";
        if((isset($_GET['tur']) && isset($_GET['chemp']) && $_GET['tur'] <> $max) ||
        (isset($r_active) && $r_active[$i] == 0) || (isset($r_active) && $r_active[$i] == 2)){
		  echo 'disabled ';
		}
echo "	name='pdate$i' type='text' size='11'";
		if (isset($_GET['tur']) && isset($_GET['chemp']))
			echo "value='$data'";
echo "	>
        <input ";
        if((isset($_GET['tur']) && isset($_GET['chemp']) && $_GET['tur'] <> $max) ||
        (isset($r_active) && $r_active[$i] == 0) || (isset($r_active) && $r_active[$i] == 2)){
		  echo 'disabled ';
		}
echo "  name='ptime$i' type='text' size='5'";
        if (isset($_GET['tur']) && isset($_GET['chemp']))
        	echo "value='$time'";
echo "  >
	</td>";
	if (isset($_GET['tur']) && isset($_GET['chemp'])){
echo "<td width='80' nowrap>&nbsp;
	  <input ";
	  if((isset($_GET['tur']) && isset($_GET['chemp']) && $_GET['tur'] <> $max) ||
	  	(isset($r_active) && $r_active[$i] == 0)){
		echo 'disabled ';
	  }
echo " name='rez1$i' value='{$myrow[$i-1]['r_rez1']}' type='text'
	   size='1' maxlength='2' style='text-align:center;'>
	  <input ";
	  if((isset($_GET['tur']) && isset($_GET['chemp']) && $_GET['tur'] <> $max) ||
	  	(isset($r_active) && $r_active[$i] == 0)){
		echo 'disabled ';
	  }
echo " name='rez2$i' value='{$myrow[$i-1]['r_rez2']}' type='text'
	   size='1' maxlength='2' style='text-align:center;'>
	  <input type='hidden' name='r_igra_id$i' value='{$myrow[$i-1]['id']}'>
	  </td>";
	}
echo "
</tr>";
}
echo '<tr><td colspan="9">';
// если в базе было не 15 матчей в этом туре
if (isset($_GET['tur']) && isset($_GET['chemp']) && $res -> num_rows <> 15){
	echo '<img src="../pics/inf.png">&nbsp;<b>Внимание! В этой программе записаны не все матчи.</b>';
}
echo '<input type="hidden" name="prog_add2" value="'.$chem.'">
	  <input type="hidden" name="status" value="';
if (!isset($_GET['tur']) && !isset($_GET['chemp']))
	echo 'new';
else echo 'edit';
echo '">
</td></tr>
<tr><td></td><td colspan="3">';
//пишем эту надпись только, если можно заносить результаты игр
if (isset($_GET['tur']) && isset($_GET['chemp'])){
  echo '<small>99 - счёт, если матч не состоялся</small>';
} else echo '&nbsp;';
echo '</td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td>&nbsp;</td>
<td>';
// редактировать можно только последний тур и активный
if (isset($r_active)){$act = 0;
  for ($y=1; $y<=count($r_active); $y++){    if ($r_active[$y] == 0){  	  $act++;
    }
  }
} /* если информация передавалась по $_GET и это последний тур или
	не все 15 игр имеют активность 0 */
if ((isset($_GET['tur']) && isset($_GET['chemp']) && $_GET['tur'] == $max) &&
	(isset($r_active) && $act <> 15)){
echo'<button type="submit" name="prog_ok" value="ok">
	<img src="../pics/add.png" style="vertical-align: middle">&nbsp;
	 Изменить </button>';
} elseif (!isset($_GET['tur']) && !isset($_GET['chemp'])){
echo'<button type="submit" name="prog_ok" value="ok">
	 <img src="../pics/add.png" style="vertical-align: middle">&nbsp;
	 Добавить </button>';
  }
?>
</td>
</form>
<td><form action="turnirs.php"><button>
	<img src='../pics/undo.png' style='vertical-align: middle'>&nbsp;Назад
	</button></form>
</td><td></td>
<?php
if (isset($_GET['tur']) && isset($_GET['chemp'])){
echo '<td><form method="post" action="programka.php">
	  <button type="submit" name="prog_add" value="'.$chem.'">
	  	<img src="../pics/new.png" style="vertical-align: middle">&nbsp;Новый
	  </button>
	  </form></td>';
/*отбераем даты проведения реальных матчей и проверяем не осталось ли в каком-то
  времени меньше, чем 30 мин. до его начала*/
$sql = 'SELECT r_date, r_active
		FROM r_igra
		WHERE turnir_id = ? AND tur_nom = ?';
if ($stmt = $conn -> prepare($sql)){  $stmt -> bind_param('ii', $chem, $tur);
  if ($stmt -> execute()){  	$res = $stmt -> get_result();  	$stmt -> close();
  	$i = 0;
  	$date_now = time();
  	$lock_yes = 0;
  	$active_yes = 0;
  	while ($row = $res -> fetch_row()){  	  $dates[] = $row;
  	  if ($date_now >= strtotime($dates[$i][0]) - 1800){  	  	$lock_yes++;
  	  }
  	  if ($dates[$i][1] <> 1){  	  	$active_yes++;
  	  }
  	  $i++;
  	}
  } else error_rez('Запрос на выборку даты проведения матчей не выполнен! '.$conn -> error);
} else error_rez('Запрос на выборку даты проведения матчей не выполнен! '.$conn -> error);
echo '<td><form method="post" action="locktour.php">
	  <input type="hidden" name="lock_chemp" value="'.$chem.'">
	  <input type="hidden" name="lock_tour" value="'.$tur.'">
	  <button type="submit" name="lock_button" value="ok" ';
  /*если ещё нет 30 мин. не до какого матча или все матчи уже неактивны,
    то кнопка не нажимается */
  if ($lock_yes == 0 || $active_yes == 15){  	echo 'disabled';
  }
echo '><img src="../pics/lock.png" style="vertical-align: top">&nbsp;Закрыть
	  </button>
	  </form></td>';
}
echo '
</tr>
</table>';
?>
<br><br><br><br><br>
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