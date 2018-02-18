<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['login']) || !isset($_SESSION['rights'])){
	header('Location:index.php');
	exit();
}
require_once('blocks/connect.php');
require_once('functions.php');
/*
Передаётся самому себе через POST
result_ok - если ='ok', то информация удачно передана
turnir 		- значение выбраного турнира
season 		- значение выбраного сезона
---
$row_turn 	- массив названий турниров выбранных с базы
$row_season - массив с названиями сезонов в соответствии с выбранным турниром
$turnir_old - значение выбраного турнира (после обработки POST)
$season_old - значение сезона для отображения на странице (после выбора пользователем)
$turnir 	- id турнира с каким работаем (передаётся через POST)
$tur		- № выбранного тура (если существует $_GET['tur'])
$list 		- список для dataList для синхронизации второго select-а с первым
$tur_prog	- массив со всеми турами и прогнозами к ним
			  выбранного турнира и сезона (если они есть)
$pari_igr	- массив с парами игр текущего тура чемпионата
$igri		- массив с реальными играми и их результатами
$forecast	- массив с названием команд клуба и их прогнозами
$r_ishod	- массив с реальными исходами прогнозов матчей
$r			- массив с результатами матча клуба каждой команды
$net_igri	- количество несостоящихся реальных матчей (из основных)
$net_igri_dop - количество несостоящихся реальных матчей (из дополнительных)
$home		- текущая позиция для "домашнего матча"
$gol		- массив с количеством голов каждой команды турнира в этом туре
*/
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="description" content="">
<meta name="keywords" content="">
<title>Футбольный прогноз - результаты матчей</title>
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
<center class="gor_line"><h2><b>Результаты матчей</b></h2></center>
<?php
// динамические select-ы -------------
$sql = 'SELECT DISTINCT turnir_n
		FROM turnirs';
if ($stmt = $conn -> prepare($sql)){
  if ($stmt -> execute()){
    $res = $stmt -> get_result();
    $stmt -> close();
    while ($row = $res -> fetch_assoc()){
      $row_turn[] = $row; //массив с названиями турниров
    }
    $res -> close();
  } else error_rez('Ошибка поиска турнира в базе данных !!!');
} else error_rez('Ошибка поиска турнира в базе данных !!!');

echo '
  <form method="post" action="results.php">
  <center><table>
  <tr><td>
  <!-- первый select -->
  <label for="turnir" style="display:block;font-weight:bold;margin-bottom:5px;">
  Чемпионат:</label>
  <select name="turnir" id="turnir">';
  //если select уже выбирался
  if (isset($_POST['result_ok']) && $_POST['result_ok'] == 'ok'){
  	$turnir_old = $conn -> real_escape_string(htmlspecialchars(trim($_POST['turnir'])));
echo '<option value="'.$turnir_old.'" selected>'.$turnir_old.'</option>';
  } //запoлняем select данными с базы
  foreach ($row_turn as $pole){
  	if ($turnir_old <> $pole['turnir_n']){
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
  <button type="submit" name="result_ok" value="ok" class="button">Выбрать</button>
  </td></tr></table>
  </td></tr></table></center></form>';
  // ищем значения для второго select-а
$list = ''; // строка, которую вставим в dataList
for ($i=0; $i<count($row_turn); $i++){ // перебираются все турниры из первого select-а
  $sql = 'SELECT id, season_nom
		  FROM turnirs
		  WHERE turnir_n = ?';
  if ($stmt = $conn -> prepare($sql)){
    $stmt -> bind_param('s', $row_turn[$i]['turnir_n']);
    if (!$stmt -> execute()){
      error_rez('Ошибка поиска сезона в базе данных !!!');
    }
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
while ($polay = current($row_season)){
  $list .= '\''.key($row_season).'\':{';
  while ($pole = current($polay)){
  	if (key($polay) == count($polay) - 1){// если это последняя запись, то запятую не ставим
  	  $list .= '\''.$pole["id"].'\':\''.$pole["season_nom"].'\'';
  	} else {// если это не последняя запись, то ставим запятую
  		$list .= '\''.$pole["id"].'\':\''.$pole["season_nom"].'\',';
  	}
  	next($polay);
  }
  if (next($row_season)){// если перенос указателя произошел, то ставим запятую
    $list .= '},';
  } else {// иначе просто закрываем скобку
  	$list .= '}';
  }
}
//если второй select ранее был уже выбран, то ищем эту позицию для связанных select-ов
if (isset($_POST['result_ok']) && $_POST['result_ok'] == 'ok'){
  $turnir = $conn -> real_escape_string(htmlspecialchars(trim($_POST['season'])));
  reset($row_season);
  while ($polay2 = current($row_season)){
    if (key($row_season) == $turnir_old){
      while ($pole2 = current($polay2)){
  	    if ($pole2['id'] == $turnir){
  	      $seas_pos = key($polay2);
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
  objSel2.selectedIndex = '.$seas_pos.'; // почему-то не работает
</script>';
//---------- конец динамических SELECT-ов ------------
if ((isset($_POST['result_ok']) && $_POST['result_ok'] == 'ok') ||
	(isset($_GET['tur']) && isset($_GET['turnir']))){  // делаем если информация была послана через POST или через GET  if (isset($_POST['result_ok']) && $_POST['result_ok'] == 'ok'){
    // если существует POST
    $turnir = $conn -> real_escape_string(htmlspecialchars(trim($_POST['season'])));
  } elseif (isset($_GET['tur']) && isset($_GET['turnir'])){  	// если существует GET
  	// ----- проверяем данные переданные через GET -----
    $sql = 'SELECT MAX(id) FROM turnirs';
    if ($stmt = $conn -> prepare($sql)){
	  if ($stmt -> execute()){
		$res = $stmt -> get_result();
		$stmt -> close();
	  } else error_rez('Ошибка проверки имеющихся турниров и туров игр !');
    } else error_rez('Ошибка проверки имеющихся турниров и туров игр !');
    $turnir_max = $res -> fetch_row();
    if ($_GET['turnir'] < 1 || $_GET['turnir'] > $turnir_max[0] || !is_numeric($_GET['turnir'])){
	  error_rez('Ошибочное значение турнира !');
    } else {
  	 $turnir = $conn -> real_escape_string(htmlspecialchars(trim($_GET['turnir'])));
    }
  }
  //узнаём номер выбранного сезона
  $sql = 'SELECT turnir_n, season_nom
  		FROM turnirs
  		WHERE id = ?';
  if ($stmt = $conn -> prepare($sql)){
	$stmt -> bind_param('i', $turnir);
	if ($stmt -> execute()){
	  $res = $stmt -> get_result();
	  $stmt -> close();
	  $name_tur_seas = $res -> fetch_row();
	  //$season_old = $res -> fetch_row();
	  $res -> close();
	} else error_rez('Ошибка! Невозможно определить старое значение сезона !!!');
  } else error_rez('Ошибка! Невозможно определить старое значение сезона !!!');
  if (isset($_GET['tur']) && isset($_GET['turnir'])){
    $turnir_old = $name_tur_seas[0];
  }
  $season_old = $name_tur_seas[1];
  // выбираем все имеющиеся туры
$sql = 'SELECT n1.com_n, n2.com_n, r_igra.r_rez1, r_igra.r_rez2,
			   r_igra.tur_nom, pr.com_n, pr.prognoz
		FROM r_igra
          LEFT JOIN (SELECT prognozi.r_igra_id, sl_com.com_n, prognozi.prognoz
          			 FROM prognozi
          			   LEFT JOIN sl_com
          			     ON prognozi.com_id = sl_com.id) AS pr
            ON r_igra.id = pr.r_igra_id
          INNER JOIN sl_com AS n1
            ON r_igra.r_com1_id = n1.id
          INNER JOIN sl_com AS n2
            ON r_igra.r_com2_id = n2.id
		WHERE r_igra.turnir_id = ?';
  if ($stmt = $conn -> prepare($sql)){
    $stmt -> bind_param('i', $turnir);
	  if ($stmt -> execute()){
	    $res = $stmt -> get_result();
	    $stmt -> close();
  	  } else error_rez('Запрос на выборку туров указанного чемпионата не выполнен! '.$conn -> error);
  } else error_rez('Запрос на выборку туров указанного чемпионата не выполнен! '.$conn -> error);
  while ($row = $res -> fetch_row()){
    $tur_prog[] = $row; /* массив со всеми турами и прогнозами к ним
    					  выбранного турнира и сезона (если они есть) */
  }
//echo '<pre>';
//print_r($tur_prog);
//echo '</pre>';
  $max = 0; // количество туров в базе в этом турнире
  if (isset($tur_prog)){//если в этот массив что-то выбралось с базы, то делаем дальше
    foreach ($tur_prog as $pole){
	  if ($pole[4] > $max){
		$max = $pole[4];
	  }
    }
  } //иначе в базе нет информации по даному турниру ($max == 0)
  echo '<table border="0" width="761"><tr><td colspan="2"><h3 class="b">
  		<span style="float:left">Туры:&nbsp;</span></h3>';
  // проверяем правильность введёного через GET тура
  if (isset($_GET['tur']) && isset($_GET['turnir'])){
    if ($_GET['tur'] < 1 || $_GET['tur'] > $max || !is_numeric($_GET['tur'])){  	  error_rez('Ошибочное значение тура !');
    } else {  	  $tur = $conn -> real_escape_string(htmlspecialchars(trim($_GET['tur'])));
    }
  }
  // если в базе есть какие-то туры в этом турнире - выводим их
 if ($max > 0){//при этом условии выполняем весь скрипт до конца
    echo '<ul class="zakladki">';
    for ($i=1; $i<=$max; $i++){
echo '<li class="';
 	// если тур ещё не выбирали, то активным будет последний
 	if (isset($_POST['result_ok']) && $_POST['result_ok'] == 'ok' && $i == $max){ 	  echo 'current';
 	  $tur = $max; //последний тур - текущий, текущий турнир известен ($turnir)
 	}
 	// если тур выбрали, то он и будет активным
 	if (isset($_GET['tur']) && $i == $tur){ 	  echo 'current';
 	}
echo '"><a href="results.php?tur='.$i.'&turnir='.$turnir.'">'.$i.'</a></li>';
    }
echo '</ul></td></tr>';
  /* если в текущем туре есть не все данные (например: не все результаты
	реальных матчей или не все игроки сделали прогнозы */
	$stop = 0;
	foreach ($tur_prog as $pole){	  if (($pole[2] == NULL && ord($pole[2]) <> 48) ||
	  	  ($pole[3] == NULL && ord($pole[3]) <> 48) ||
	  	  $pole[5] == NULL || $pole[6] == NULL){	  	$stop = 1; //признак того, что в текущем туре собрана не вся информация
	  }
	}
  if ($stop == 0){
  // ----------------- вывод результатов игр ------------------------
  // создаём вспомагательные массивы
  $sql = 'SELECT igra.date, n1.com_n, n2.com_n
  		  FROM igra
  		    INNER JOIN sl_com AS n1
  		      ON igra.com1_id = n1.id
  		    INNER JOIN sl_com AS n2
  		      ON igra.com2_id = n2.id
  		  WHERE igra.turnir_id = ? AND igra.tur_nom = ?';
  if ($stmt = $conn -> prepare($sql)){	$stmt -> bind_param('ii', $turnir, $tur);
	if ($stmt -> execute()){	  $res = $stmt -> get_result();
	  $stmt -> close();
	} else error_rez('Запрос на выборку матчей турнира не выполнен! ').$conn -> error;
  } else error_rez('Запрос на выборку матчей турнира не выполнен! ').$conn -> error;
  while ($row = $res -> fetch_row()){
    $pari_igr[] = $row; /* массив с парами игр текущего тура чемпионата */
  }
  $i = 0;
  $flag = 0;
  $igri[] = 0; //массив с реальными играми и их результатами
  foreach($tur_prog as $pole){  	if ($pole[4] == $tur){ //формируем массивы только с тех записей, в которых нужный тур
  	  // формирование массива с реальными играми и их результатами
  	  if ($igri[$i]['com1'] <> $pole[0] || $igri[$i]['com2'] <> $pole[1]){  	    $i++;
  	    $igri[$i]['com1'] = $pole[0];
  	    $igri[$i]['com2'] = $pole[1];
  	    $igri[$i]['rez1'] = $pole[2];
  	    $igri[$i]['rez2'] = $pole[3];
  	  }
  	  if ($flag <> $i){ // проверка: когда нам нужно начать сначала $j  	  	$j = 1;
  	  	$flag = $i;
  	  } else {  	  	$j++;
  	  }
  	  // формирование массива с названием команд клуба и их прогнозов
  	  if (!isset($forecast[$j])){  		$forecast[$j][0] = $pole[5];
  		$forecast[$j][$i] = $pole[6];
  	  } else {  	  	$forecast[$j][$i] = $pole[6];
  	  }  	}
  }
//echo '<pre>';
//print_r($igri);
//print_r($forecast);
//print_r ($pari_igr);
//echo '</pre>';
 if (count($igri)-1 == 15){echo '<tr><td><br><br>
	<!-- таблица результатов реальных матчей -->
	<table class="rez_tour" border="0"><tr class="gor_line">
	<td class="ver_line"><b>№</b></td><td class="ver_line"><b>Матчи</b></td>
	<td><b>Счёт</b></td></tr>';
  for ($i=1; $i<count($igri); $i++){
echo '<tr ';
	if ($igri[$i]['rez1'] == 99 && $igri[$i]['rez2'] == 99){
	echo 'style="background:#bbb;"';
	}
echo '><td class="ver_line">'.$i.'&nbsp;</td><td align="left" class="ver_line">&nbsp;
		'.$igri[$i]['com1'].' - '.$igri[$i]['com2'].'&nbsp;</td>';
	if ($igri[$i]['rez1'] <> 99 && $igri[$i]['rez2'] <> 99){
echo '<td><span style="color:green;">'.$igri[$i]['rez1'].':'.$igri[$i]['rez2'].'</span></td></tr>';
	} else {
	echo '<td><img src="pics/cancel.png" style="vertical-align: middle"></td></tr>';
	}
  }
echo '</table></td></tr><tr><td><br><br><table class="rez_tour" border="0">
	<tr class="gor_line"><th class="ver_line"></th>';
  for ($i=1; $i<count($igri); $i++){  	echo '<th width="25" ';
  	 if ($i == 15){//если это последняя ячейка, то ставим вер.линию     echo 'class="ver_line"';
  	 }
  	echo '>'.$i.'</th>'; //ставим нумерацию игр
  }
echo '</tr><tr class="gor_line" style="background-color:#E7EDDF;">';
  $n = 0;
  $net_igri = 0;
  $net_igri_dop = 0;
  foreach($igri as $pole){ // ставим реальный исход матчей
   if ($pole == 0){//если это первая ячейка, то пишем текст и ставим вер.полосуecho '<td nowrap class="ver_line"><b>Реальный исход</b></td>';
   } else {   	 if ($pole['rez1'] == 99 && $pole['rez2'] == 99 && $n < 11){   	 	$net_igri++; //количество несостоящихся реальных матчей (основных)
   	 } elseif ($pole['rez1'] == 99 && $pole['rez2'] == 99 &&
   	 		  ($n > 10 && $n <= 10 + $net_igri + $net_igri_dop)){		$net_igri_dop++; //если не состоялся матч из дополнительных
   	 }
 echo '<td height="25"';
      if ($n == 10 + $net_igri + $net_igri_dop){//если это последняя из игр, что учитываются      echo 'class="ver_line"';
      }
      if ($n == 15){ //если это самая последняя ячейка, то ставим вер.линию      echo 'class="ver_line"';
      }
 echo '><b>';     if ($pole['rez1'] == 99 && $pole['rez2'] == 99){//если матч не состоялся		echo '<img src="pics/cancel.png" style="vertical-align: middle">';
		$r_ishod[$n] = '0';
     } elseif ($pole['rez1'] > $pole['rez2']){ 		echo '1';
 		$r_ishod[$n] = '1';
     } elseif ($pole['rez1'] < $pole['rez2']){ 		echo '2';
 		$r_ishod[$n] = '2';
     } elseif ($pole['rez1'] == $pole['rez2']){ 		echo 'X';
 	    $r_ishod[$n] = 'X';
     }
 echo '</b></td>';
   }
   $n++;
  }
echo '</tr>';
  // ищем позицию для "домашнего матча", если какие-то матчи не состоялись
  $home = 10; //текущая позиция для "домашнего матча"
  for ($k=10; $k>=1; $k--){
    if ($r_ishod[$k] == '0'){ //если есть несостоявшейся матч
	  if ($k == $home){//если несостоявшейся матч это 10-я позиция или текущая позиция,
	    $home = $k - 1;  //то текущую позицию делаем на единицу меньше
	  }
    }
  }
  // пишем сами прогнозы команд
  $m = 0; //счётчик команд участвующих в этом туре чемпионата с результатами
  foreach($pari_igr as $pole2){ //листаем массив с парами игр текущего тура чемпионата
    for ($l=1; $l<=2; $l++){ //из пары берём сначала одну команду, потом вторую
      foreach($forecast as $polay){        if ($pole2[$l] == $polay[0]){//если нужная команда найдена, то виводим её
          $m++;  	      $r[$m][0] = $polay[0];//создаём массив с результатами матча клуба каждой команды
  	      echo '<tr ';
  	      if ($l == 2){ // ставим гор.линию после пары команд  	      echo 'class="gor_line"';
  	      }
  	      echo '>';  	      for($i=0; $i<count($polay); $i++){      echo '<td height="25" ';
	        if ($i == 0){//если это первая ячейка, текст сдвигаем влево и ставим вер.полосу	      echo 'align="left" class="ver_line" ';
	        } elseif ($i == $home){ //если это "домашний матч", выделяем фон			echo 'background="pics/fon1.jpg"';
	        }
	        if ($i == 10 + $net_igri + $net_igri_dop){//если каких-то матчей не было, то	      echo 'class="ver_line"'; //вер.линию ставим после доп.матчей или после 10-го матча
	        } elseif ($i == 15){     // если это последняя ячейка,	      echo 'class="ver_line"';   // то ставим вер.линию
	        }
      echo '><span ';
            if ($i == 0){//если это первая ячейка, то ещё проверяем данная команда
              //не текущего ли тренера, если так, то выделяем её другим цветом
	          //отбираем перечень всех команд и id их тренеров
	  		  //чтобы выделить команды текущего тренера
	  		  $sql = "SELECT com_n, user_id
	  		  		  FROM sl_com";
	  		  if ($stmt = $conn -> prepare($sql)){
	  		    if ($stmt -> execute()){
	  	  		  $res = $stmt -> get_result();
	  	  		  $stmt -> close();
          		  while ($row = $res -> fetch_assoc()){
          		    $coms_all[] = $row; //массив с названиями всех команд и id их тренеров
        		  }
          		  $res -> close();
	    	    }
	  		  }
			 //выделяем команду текущего тренера
			  foreach ($coms_all as $pole3){
	  		   if ($_SESSION['user_id'] == $pole3['user_id']){
	    	     if ($pole3['com_n'] == $r[$m][0]){
	      	     echo 'style="color: #CC6600;"';
	    	     }
	  		   }
              }
            } elseif (isset($r_ishod[$i]) && $r_ishod[$i] == '0'){            //если матч не состоялся
              echo 'style="background:#bbb;"';
              $r[$m][$i] = -1;
            } elseif (isset($r_ishod[$i]) && $r_ishod[$i] == $polay[$i]){
            //если это не первая ячейка и данный прогноз совпал с
            //реальным результатом, то делаем его "жирным" и $r = 1
			  $r[$m][$i] = 1;
			  echo 'class="b"';
            } elseif (isset($r_ishod[$i]) && $r_ishod[$i] <> $polay[$i]){              $r[$m][$i] = 0; //если прогноз не совпал, то $r = 0
            }
      echo '>'.$polay[$i].'</span></td>';
  	      }
  	  echo '</tr>';
  	    }
      }
    }
  }
 // поиск забитых мячей
  for ($i=1; $i<=count($r); $i+=2){    for ($j=1; $j<=10+$net_igri+$net_igri_dop; $j++){ // $j < count($r[$i]) - это все 15 прогнозов	  if ($j == $home && $r[$i][$j] == 1){//гол хозяйвов поля (только 10-я игра)	    $r[$i][$j] = 2;
	  } elseif ($r[$i][$j] > $r[$i+1][$j]){//гол забила первая команда	    $r[$i][$j] = 2;
	  } elseif ($r[$i][$j] < $r[$i+1][$j]){//гол забила вторая команда	    $r[$i+1][$j] = 2;
	  }
    }
    //всем несыгранным доп.матчам ставится "-1" и они не будут идти в статистику
    for ($j=15; $j>10+$net_igri+$net_igri_dop; $j--){      $r[$i][$j] = -1;
      $r[$i+1][$j] = -1;
    }
  }
echo '</tr></table>
	<table class="rez_tour" border="0">
	<tr><td>&nbsp;</td></tr>
	<tr class="gor_line"><td height="25"><b>Счёт</b></td></tr>';
  // подсчёт голов
  for ($i=1; $i<=count($r); $i++){  	$gol[$i] = 0;    for ($j=1; $j<count($r[$i]); $j++){      if ($r[$i][$j] == 2){		$gol[$i]++;
      }
    }
    echo '<tr ';
    if (!($i % 2)){
    echo 'class="gor_line"';
      //если первая команда (из этой пары команд) имеет больше голов
      if ($gol[$i-1] > $gol[$i]){		$r[$i-1][] = 2; //то первая команда выграла
		$r[$i][] = 0; //а вторая проиграла
      } elseif ($gol[$i-1] < $gol[$i]){//если вторая команда имеет больше голов      	$r[$i][] = 2; //то вторая команда выграла
      	$r[$i-1][] = 0; //а первая проиграла
      } else { //если две команды имеют одинаковое количество голов		$r[$i-1][] = 1; //то первая команда сыграли в нечью
		$r[$i][] = 1; //и вторая команда сыграли в нечью
      }
    }
echo '><td height="25"><b style="color:red;">'.$gol[$i].'</b></td></tr>';
  }
echo '
	</table></td></tr></table>';
 } else {//если в массиве $igri не 15 игр  echo '<tr><td>&nbsp;<img src="pics/edit_no.png" style="vertical-align: middle">&nbsp;
  	<b style="font-size:15px;">Некоректный набор данных в массиве: "$igri" !</b>
  	</td></tr></table>';
 }  // если в базе не вся информация о результатах реальных матчей или не все
 } else {//игроки сделали прогнозы на эти матчи в текущем туре  echo '<tr><td><br><br>&nbsp;<img src="pics/inf.png" style="vertical-align: middle">&nbsp;
  	<b style="font-size:15px;">
  	На данный момент за текущий тур собрана не вся информация необходимая для вывода его результатов !
  	</b></td></tr></table>';
 }
 } else {// если в базе нет информации об этом турнире
  echo '&nbsp;<img src="pics/edit_no.png" style="vertical-align: middle">&nbsp;
  	<b style="font-size:15px;">В этом турнире не сыграно не одного матча !</b>
  	</td></tr></table>';
 }
}
//echo '<pre>';
//print_r($igri);
//print_r($r_ishod);
//print_r($r);
//echo '</pre>';
?>
<br><br><br><br><br>
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