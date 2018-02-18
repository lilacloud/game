<?php
session_start();
require_once('blocks/connect.php');
require_once('functions.php');
/*
Передаётся самому себе через POST
kalendar_ok - если ='ok', то информация удачно передана
turnir - значение выбраного турнира
season - значение выбраного сезона
---
$row_turn 	- массив названий турниров выбранных с базы
$row_season - массив с названиями сезонов в соответствии с выбранным турниром
$turnir_old - значение выбраного турнира (после обработки POST)
$list 		- список для dataList для синхронизации второго select-а с первым
$turnir 	- id турнира с каким работаем (передаётся через POST)
$kalendar 	- массив со значениями для календаря с таб. 'igra'
$tur 		- счётчик тура
$month_rus 	- месяца на русском языке
$dt 		- массив с разделёнными датой и временем проведения турнира взятые с базы
$day 		- день проведения турнира
$month 		- месяц проведения турнира
$year 		- год проведения турнира
$time 		- время проведения турнира
$season_old - значение сезона для отображения на странице (после выбора пользователем)
$seas_pos 	- значение позиции select-а, если оно было уже выбрано
$coms_all 	- массив с названиями всех команд и id их тренеро
*/
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/xml; charset=UTF-8">
<meta name="description" content="">
<meta name="keywords" content="">
<title>Футбольный прогноз | Календарь игр</title>
<link href="styles.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="jscripts/linkedselect.js"></script>
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
<center class="gor_line"><h2><b>Календарь игр</b></h2></center>
<?php
$sql = 'SELECT DISTINCT turnir_n
		FROM turnirs';
if ($stmt = $conn -> prepare($sql)){
  if ($stmt -> execute()){
    $res = $stmt -> get_result();
    $stmt -> close();
    while ($row = $res -> fetch_assoc()){
      $row_turn[] = $row;
    }
    $res -> close();
  } else error_rez('Ошибка поиска турнира в базе данных !!!');
} else error_rez('Ошибка поиска турнира в базе данных !!!');

echo '
  <form method="post" action="kalendar.php">
  <center><table>
  <tr><td>
  <!-- первый select -->
  <label for="turnir" style="display:block;font-weight:bold;margin-bottom:5px;">
  Чемпионат:</label>
  <select name="turnir" id="turnir">';
  //если select уже выбирался
  if (isset($_POST['kalendar_ok']) && $_POST['kalendar_ok'] == 'ok'){  	$turnir_old = $conn -> real_escape_string(htmlspecialchars(trim($_POST['turnir'])));echo '<option value="'.$turnir_old.'" selected>'.$turnir_old.'</option>';
  } //заплняем select данными с базы
  foreach ($row_turn as $pole){  	if ($turnir_old <> $pole['turnir_n']){
      echo '<option value="'.$pole['turnir_n'].'">'.$pole['turnir_n'].'</option>';
    }
  }
echo '
  </select>&nbsp;&nbsp;
  </td>
  <!-- второй select -->
  <td>
  <label for="season" style="display:block;font-weight:bold;margin-bottom:5px;">
  Сезон:</label>
  <select name="season" id="season" class="select" style="width:60px">
  </select>&nbsp;&nbsp</td>
  <td>
  <table><tr><td>&nbsp;</td></tr><tr><td>
  <button type="submit" name="kalendar_ok" value="ok" class="button">Выбрать</button>
  </td></tr></table>
  </td></tr></table></center></form>';
  // ищем значения для второго select-а
$list = ''; // строка, которую вставим в dataList
for ($i=0; $i<count($row_turn); $i++){ // перебираются все турниры из первого select-а
  $sql = 'SELECT id, season_nom
		  FROM turnirs
		  WHERE turnir_n = ?';
  if ($stmt = $conn -> prepare($sql)){    $stmt -> bind_param('s', $row_turn[$i]['turnir_n']);
    if (!$stmt -> execute()){      error_rez('Ошибка поиска сезона в базе данных !!!');    }
    $res = $stmt -> get_result();
    $stmt -> close();
    if ($res -> num_rows <> 0){ // если данные для второго select-а выбрались удачно
      $turn = $row_turn[$i]['turnir_n']; // названия турниров
      while ($row = $res -> fetch_assoc()){
        $rows[] = $row;
      }
      $row_season[$turn] = $rows; //массив со значениями названий турниров и соответствующими им сезонами
      $rows = NULL;
      $res -> close();
    } else error_rez('Ошибка! Некоректные данные! Работа скрипта прекращена !!!');
  } else error_rez('Ошибка поиска сезона в базе данных !!!');
}
//echo '<pre>';
//print_r($row_season);
//echo '</pre>';
// заполняем dataList для второго select-а
while ($polay = current($row_season)){  $list .= '\''.key($row_season).'\':{';  while ($pole = current($polay)){  	if (key($polay) == count($polay) - 1){// если это последняя запись, то запятую не ставим
  	  $list .= '\''.$pole["id"].'\':\''.$pole["season_nom"].'\'';
  	} else {// если это не последняя запись, то ставим запятую  		$list .= '\''.$pole["id"].'\':\''.$pole["season_nom"].'\',';
  	}
  	next($polay);
  }
  if (next($row_season)){// если перенос указателя произошел, то ставим запятую
    $list .= '},';
  } else {// иначе просто закрываем скобку  	$list .= '}';
  }
}
//если второй select ранее был уже выбран, то ищем эту позицию для связанных select-ов
if (isset($_POST['kalendar_ok']) && $_POST['kalendar_ok'] == 'ok'){
  $turnir = $conn -> real_escape_string(htmlspecialchars(trim($_POST['season'])));
  reset($row_season);
  while ($polay2 = current($row_season)){
    if (key($row_season) == $turnir_old){
      while ($pole2 = current($polay2)){  	    if ($pole2['id'] == $turnir){  	      $seas_pos = key($polay2);
  	      break 2;
  	    }
  	    next($polay2);
      }
    }
    next ($row_season);
  }
} else {
  $seas_pos = 0;
}
// связываем select-ы
echo '
<script type="text/javascript">
  // Создаем новый объект связанных списков
  var syncList1 = new syncList;
  // Создаём обьект со вторым select-ом чтобы когда он будет выбран
  // после отправки данных на сервер, значение оставалось прежним
  var objSel2 = document.getElementById("season");
  // Определяем значения второго select-а
  syncList1.dataList = {'.$list.'};
  // Включаем синхронизацию связанных списков
  syncList1.sync("turnir","season");
  // Устанавливаем уже выбранное значение, если уже был выбор
  //objSel2.options[objSel2.options.length] = new Option("текст", "значение", true, true);
  objSel2.selectedIndex = '.$seas_pos.';
</script>';
//--------- Блок после динамических SELECT-ов ------------
// вывод календаря уже с выбранным турниром и сезоном
if (isset($_POST['kalendar_ok']) && $_POST['kalendar_ok'] == 'ok'){  $sql = 'SELECT igra.tur_nom, igra.date, c1.com_n, c2.com_n,
				 c1.flag, c2.flag, igra.rez1, igra.rez2, igra.sigrano
		  FROM igra
		    LEFT JOIN sl_com AS c1
		      ON igra.com1_id = c1.id
		    LEFT JOIN sl_com AS c2
		      ON igra.com2_id = c2.id
		  WHERE igra.turnir_id = ?';
  if ($stmt = $conn -> prepare($sql)){
    $stmt -> bind_param('i', $turnir);
    if ($stmt -> execute()){      $res = $stmt -> get_result();
      $stmt -> close();
      while ($row = $res -> fetch_row()){
		$kalendar[] = $row; // массив с командами для вывода
	  }
	  $res -> close();
    } else error_rez('Ошибка формирования календаря турнира !!!');
  } else error_rez('Ошибка формирования календаря турнира !!!');
//узнаём номер выбранного сезона
  $sql = 'SELECT season_nom
  		FROM turnirs
  		WHERE id = ?';
  if ($stmt = $conn -> prepare($sql)){
	$stmt -> bind_param('i', $turnir);
	if ($stmt -> execute()){
	  $res = $stmt -> get_result();
	  $stmt -> close();
	  $season_old = $res -> fetch_row();
	  $res -> close();
	} else error_rez('Ошибка! Невозможно определить старое значение сезона !!!');
  } else error_rez('Ошибка! Невозможно определить старое значение сезона !!!');
//  echo '<pre>';
//  print_r($kalendar);
//  echo '</pre>';
echo '<center><table>
	  <tr><td colspan="9"><center style="background:#FFB080;"><h2>'.$turnir_old.',
	  (сезон '.$season_old[0].')</h2></center></td></tr>';
  $tur = '0'; // счётчик тура
  $month_rus = array ("января","февраля","марта","апреля","мая","июня","июля","августа","сентября","октября","ноября","декабря");
  for ($i=0; $i<count($kalendar); $i++){  	if ($kalendar[$i][1] <> ''){ //если есть дата проведения матча
  	  $dt = explode(" ", $kalendar[$i][1]);
	  $day = date('d', strtotime($dt[0]));
	  $month = date('n', strtotime($dt[0]));
	  $year = date('Y', strtotime($dt[0]));
	  $time = date('H:i', strtotime($dt[1]));
	}  	if ($tur <> $kalendar[$i][0]){  	  $tur = $kalendar[$i][0];
  	  //пишем № тура
echo '<tr><td>&nbsp;</td><td colspan="8"><center><h3><b>'.$tur.'-й тур</b></h3></center></td></tr>
  	  <tr><td>&nbsp;</td><td colspan="8" class="shadow"><center class="b">';
  	  //пишем дату проведеня игры
  	  if ($kalendar[$i][1] <> ''){ //если есть дата проведения матча
	    echo $day.'&nbsp;'; //пишем число проведения игры
	    for ($j=0; $j<count($month_rus); $j++){	  	  if ($month == $j+1){	  	    echo $month_rus[$j].'&nbsp;'; //пишем месяц проведения игры
	  	  }
	    }
	    echo $year; //пишем год проведения игры
	  } else { //если нет даты	    echo 'Дата ещё не определена';
	  }
echo '</center></td></tr><tr><td width="100"><i>';
	  if ($kalendar[$i][1] <> ''){ //если есть дата проведения матча        echo $time; //пишем время проведения игры
	  } else {	  	echo '--:--';
	  }
	  //пишем название и флаг первой команды
echo '</i></td><td align="right" ';
     //если эта команда - победитель, то подчёркиваем
      if ($kalendar[$i][6] > $kalendar[$i][7]){      	echo 'class="u"';
      }
echo '><span ';
	  //отбираем перечень всех команд и id их тренеров
	  //чтобы выделить команды текущего тренера
	  $sql = "SELECT com_n, user_id
	  		  FROM sl_com";
	  if ($stmt = $conn -> prepare($sql)){	  	if ($stmt -> execute()){
	  	  $res = $stmt -> get_result();
	  	  $stmt -> close();
          while ($row = $res -> fetch_assoc()){          	$coms_all[] = $row; //массив с названиями всех команд и id их тренеров
          }
          $res -> close();
	    }
	  }
	//выделяем команду текущего тренера
	  foreach ($coms_all as $pole3){	    if ($_SESSION['user_id'] == $pole3['user_id']){
	      if ($pole3['com_n'] == $kalendar[$i][2]){
	      echo 'class="b"';
	      }
	    }
	  }
echo '>'.$kalendar[$i][2].'</span></td>
	  <td><img src="'.substr($kalendar[$i][4], 1).'" style="vertical-align:middle"></td>
	  <td><b>';
	  //пишем счёт игры
	  if ($kalendar[$i][6] == NULL && $kalendar[$i][8] == 0){
	    echo '-'; //если результата матча ещё нет, то пишем '-'
	  } else {
	    echo $kalendar[$i][6];
	  }
echo '</b></td><td class="b">:</td><td><b>';
	  if ($kalendar[$i][7] == NULL && $kalendar[$i][8] == 0){
	  	echo '-'; //если результата матча ещё нет, то пишем '-'
	  }	else {
	  	echo $kalendar[$i][7];
	  }
	  //пишем флаг и название второй команды
echo '</b></td><td ';
     //если эта команда - победитель, то подчёркиваем
      if ($kalendar[$i][7] > $kalendar[$i][6]){
      	echo 'class="u"';
      }
echo '><img src="'.substr($kalendar[$i][5], 1).'" style="vertical-align:middle"></td>
  	  <td><span ';
  	//выделяем команду текущего тренера
	foreach ($coms_all as $pole3){
	  if ($_SESSION['user_id'] == $pole3['user_id']){
	    if ($pole3['com_n'] == $kalendar[$i][3]){
	      echo 'class="b"';
	    }
	  }
	}
echo '>'.$kalendar[$i][3].'</span></td></tr>';
  	} else { //если это не первая игра
// ---------------------------------------
echo '<tr><td width="100"><i>';
	  if ($kalendar[$i][1] <> ''){ //если есть дата проведения матча
        echo $time; //пишем время проведения игры
	  } else {
	  	echo '--:--';
	  }
  	  //пишем назвиние и флаг первой команды
echo '</i></td><td align="right" ';
     //если эта команда - победитель, то подчёркиваем
      if ($kalendar[$i][6] > $kalendar[$i][7]){
      	echo 'class="u"';
      }
echo '><span ';
    //выделяем команду текущего тренера
	foreach ($coms_all as $pole3){
	  if ($_SESSION['user_id'] == $pole3['user_id']){
	    if ($pole3['com_n'] == $kalendar[$i][2]){
	      echo 'class="b"';
	    }
	  }
	}
echo '>'.$kalendar[$i][2].'</span></td>
	  <td><img src="'.substr($kalendar[$i][4], 1).'" style="vertical-align:middle"></td>
	  <td><b>';
	  //пишем счёт игры
	  if ($kalendar[$i][6] == NULL && $kalendar[$i][8] == 0){	    echo '-'; //если результата матча ещё нет, то пишем '-'
	  } else {	    echo $kalendar[$i][6];
	  }
echo '</b></td><td class="b">:</td><td><b>';
	  if ($kalendar[$i][7] == NULL && $kalendar[$i][8] == 0){	  	echo '-'; //если результата матча ещё нет, то пишем '-'
	  }	else {	  	echo $kalendar[$i][7];
	  }
	  //пишем флаг и название второй команды
echo '</b></td><td ';
     //если эта команда - победитель, то подчёркиваем
      if ($kalendar[$i][7] > $kalendar[$i][6]){
      	echo 'class="u"';
      }
echo '><img src="'.substr($kalendar[$i][5], 1).'" style="vertical-align:middle"></td>
  	  <td><span ';
    //выделяем команду текущего тренера
	foreach ($coms_all as $pole3){
	  if ($_SESSION['user_id'] == $pole3['user_id']){
	    if ($pole3['com_n'] == $kalendar[$i][3]){
	      echo 'class="b"';
	    }
	  }
	}
echo '>'.$kalendar[$i][3].'</span></td></tr>';
  	}  }
echo '</table></center><br><br><br><br><br>';
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