<?php
session_start();
require_once('blocks/connect.php');
require_once('functions.php');
/*
Передаётся самому себе через POST
result_ok - если ='ok', то информация удачно передана
turnir 		- значение выбраного турнира
season 		- значение выбраного сезона
---
$turnir		- id текущего турнира
$results	- массив с информацией о результатах всех играх в текущем турнире
$coms		- вспомагательный массив с готовыми данными для вывода на страницу
			 ($coms['id команды']['...']
			  'flag' 	- флаг команды
			  'com_n' 	- название команды
			  'games' 	- количество сыграных игр
			  'win' 	- количество побед
			  'nich' 	- количество ничьих
			  'lose' 	- количество поражений
			  'score' 	- очки
			  'forecasts' - количество сделанных прогнозов
			  'zab' 	- забитые голы
			  'prop' 	- пропущенные голы
			  'right_prog' - верные прогнозы
			  'rating'	- рейтинг команды
			  'rating_new - рейтинг команды в этом туре
			  'rating_user - рейтинг тренера текущей команды')
$mesta		- массив с id команд в порядке вывода их в турнирной таблице
			 ($mesta['порядковый номер'][...]
			  0			- 'id' id команды
			  1			- 'score' очки команды
			  2			- 'difference' разница между забитыми и пропущенными голами
			  3			- 'zab' забитые голы)
$msxi		- текущее количество элементов в мас.$mesta
$maxj		- текущее количество элементов в мас.$mesta при сдвиге элементов
			  в конец массива
$max		- промежуточное максимальное значение повторяющихся ключевых позиций
$max_eq		- окончательное максимальное значение повторяющихся ключевых позиций
			  (количество иттераций для перебора мас.$mesta по $x)
*/
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="description" content="">
<meta name="keywords" content="">
<title>Футбольный прогноз - турнирная таблица</title>
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
<center class="gor_line"><h2><b>Турнирная таблица</b></h2></center>
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
  <form method="post" action="turnir_table.php">
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
// конец динамических SELECT-ов ------------
if (isset($_POST['result_ok']) && $_POST['result_ok'] == 'ok'){  //выборка с базы и формирование данных по всем командам и играм в текущем турнире  $sql = 'SELECT sl_com.id, sl_com.com_n, sl_com.flag, turnir_table.res1, turnir_table.res2,
  				 turnir_table.res3, turnir_table.res4, turnir_table.res5, turnir_table.res6,
  				 turnir_table.res7, turnir_table.res8, turnir_table.res9, turnir_table.res10,
  				 turnir_table.res11, turnir_table.res12, turnir_table.res13, turnir_table.res14,
  				 turnir_table.res15, users.username, users.id AS trener_id, turnir_table.ishod,
  				 turnir_table.rating, turnir_table.rating_new, turnir_table.rating_user
  		  FROM turnir_table
  		    INNER JOIN sl_com
  		      ON turnir_table.com_id = sl_com.id
  		    INNER JOIN users
  		      ON turnir_table.user_id = users.id
  		  WHERE turnir_id = ?';
  if ($stmt = $conn -> prepare($sql)){
  	$stmt -> bind_param('i', $turnir);
  	if ($stmt -> execute()){
  	  $res = $stmt -> get_result();
  	  $stmt -> close();
  	} else error_rez('Запрос на выборку результатов матчей не выполнен! '.$conn -> error);
  } else error_rez('Запрос на выборку результатов матчей не выполнен! '.$conn -> error);
 if ($res -> num_rows <> 0){//если в базе есть данные по этому турниру
  while ($row = $res -> fetch_assoc()){
  	$results[] = $row; //массив с информацией о результатах всех играх в текущем турнире
  }
//echo '<pre>';
//print_r($results);
//echo '</pre>';
  for ($i=0; $i<count($results); $i++){   //если команда выбралась 1-й раз, то заносим её в массив $coms
	if (!isset($coms[$results[$i]['id']])){	  //первоначальные данные, которые сразу можно внести в массив $coms	  $coms[$results[$i]['id']]['id'] = $results[$i]['id']; //id команды
	  $coms[$results[$i]['id']]['flag'] = $results[$i]['flag']; //флаг команды
	  $coms[$results[$i]['id']]['name'] = $results[$i]['com_n']; //название команды
	  $coms[$results[$i]['id']]['games'] = 1; //количество сыграных игр
	  $coms[$results[$i]['id']]['trener'] = $results[$i]['username']; //тренер команды
	  $coms[$results[$i]['id']]['trener_id'] = $results[$i]['trener_id']; //id тренерa команды
	  $coms[$results[$i]['id']]['rating'] = $results[$i]['rating']; //рейтинг команды после последнего тура
	  $coms[$results[$i]['id']]['rating_new'] = $results[$i]['rating_new']; //рейтинг команды в последнем туре
	  $coms[$results[$i]['id']]['rating_user'] = $results[$i]['rating_user']; //рейтинг команды в последнем туре
	  if ($results[$i]['ishod'] == 3){ //количество побед	  	$coms[$results[$i]['id']]['win'] = 1;
	  } else {	  	$coms[$results[$i]['id']]['win'] = 0;
	  }
	  if ($results[$i]['ishod'] == 1){ //количество ничьих
	  	$coms[$results[$i]['id']]['nich'] = 1;
	  } else {	  	$coms[$results[$i]['id']]['nich'] = 0;
	  }
	  if ($results[$i]['ishod'] == 0){ //количество поражений
	  	$coms[$results[$i]['id']]['lose'] = 1;
	  } else {	  	$coms[$results[$i]['id']]['lose'] = 0;
	  }
	  $coms[$results[$i]['id']]['score'] = $results[$i]['ishod']; //очки
	  //забитые, пропущенные голы и количество сделанных и правильных прогнозов
	  $coms[$results[$i]['id']]['forecasts'] = 0;
	  $coms[$results[$i]['id']]['zab'] = 0;
	  $coms[$results[$i]['id']]['prop'] = 0;
	  $coms[$results[$i]['id']]['right_prog'] = 0;
	  for ($j=1; $j<=15; $j++){	  	if ($results[$i]["res$j"] <> -1){ //количество сделанных прогнозов
	  	  $coms[$results[$i]['id']]['forecasts']++;
	  	}	  	if ($results[$i]["res$j"] == 3){ //забитые голы
          $coms[$results[$i]['id']]['zab']++;
	  	}
	  	if ($results[$i]["res$j"] == 2){ //пропущенные голы	  	  $coms[$results[$i]['id']]['prop']++;
	  	}
	  	if ($results[$i]["res$j"] == 1 || $results[$i]["res$j"] == 3){ //правильные прогнозы
	  	  $coms[$results[$i]['id']]['right_prog']++;
	  	}
	  }
	} else {//если команда уже выбиралась, то добавляем результаты по этой команде в массив $coms
	  //прибавляем количество сыграных игр
	  $coms[$results[$i]['id']]['games']++;
	  //прибавляем количество побед
	  if ($results[$i]['ishod'] == 3){
	    $coms[$results[$i]['id']]['win']++;
	  }
	  //прибавляем количество ничьих
	  if ($results[$i]['ishod'] == 1){
	    $coms[$results[$i]['id']]['nich']++;
	  }
	  //прибавляем количество поражений
	  if ($results[$i]['ishod'] == 0){
	    $coms[$results[$i]['id']]['lose']++;
	  }
	  //прибавляем очки	  $coms[$results[$i]['id']]['score'] = $coms[$results[$i]['id']]['score'] + $results[$i]['ishod'];
	  //забитые, пропущенные голы и количество сделанных и правильных прогнозов
	  for ($j=1; $j<=15; $j++){
	  	if ($results[$i]["res$j"] <> -1){ //количество сделанных прогнозов
	  	  $coms[$results[$i]['id']]['forecasts']++;
	  	}
	  	if ($results[$i]["res$j"] == 3){ //забитые голы
          $coms[$results[$i]['id']]['zab']++;
	  	}
	  	if ($results[$i]["res$j"] == 2){ //пропущенные голы
	  	  $coms[$results[$i]['id']]['prop']++;
	  	}
	  	if ($results[$i]["res$j"] == 1 || $results[$i]["res$j"] == 3){ //правильные прогнозы
	  	  $coms[$results[$i]['id']]['right_prog']++;
	  	}
	  }
	}
  }
//echo '<pre>';
//print_r($coms);
//echo '</pre>';
  //===================================================================
  /*формируем массив $mesta с командами отсоритрованными в порядке,
  необходимом для вывода в турнирной таблице*/
  foreach ($coms as $com){
	if (!isset($mesta)){//елси в массив $mesta ничего ещё не занесено
	  $mesta[1][0] = $com['id']; //id     //присваеваем начальные данные
	  $mesta[1][1] = $com['score']; //score
	  $mesta[1][2] = $com['zab'] - $com['prop']; //difference
	  $mesta[1][3] = $com['zab'];  //zab
	} else {//елси в массив $mesta уже занесены какие-то данные
      //сравниваем значение кол.очков с мас.$coms($com) с каждым кол.очков с мас.$mesta
      $maxi = count($mesta);
      for ($i=1; $i<=$maxi; $i++){
      	//если кол.очков с мас.$coms($com) > кол.очков с мас.$mesta
	  	if ($com['score'] > $mesta[$i][1]){//вставляем на это место большее
          $maxj = count($mesta); //количество элементов в мас.$mesta
          for ($j=$maxj; $j>=$i; $j--){//переставляем элементы массива со сдвигом в конец
            $mesta[$j+1][0] = $mesta[$j][0];
            $mesta[$j+1][1] = $mesta[$j][1];
            $mesta[$j+1][2] = $mesta[$j][2];
            $mesta[$j+1][3] = $mesta[$j][3];
          }//вставляем больший элемент в освобождённую позицию
          $mesta[$i][0] = $com['id'];
          $mesta[$i][1] = $com['score'];
          $mesta[$i][2] = $com['zab'] - $com['prop'];
          $mesta[$i][3] = $com['zab'];
          break;
	  	} else {//если меньше или равно
	  	  //то пропускаем и проверяем следущую позицию кол.очков с мас.$mesta
          //кроме случая, когда уже пройден мас.$mesta до конца, тогда
          if ($i == count($mesta)){//присваиваем значения на последнюю позицию
          	$mesta[$i+1][0] = $com['id'];
            $mesta[$i+1][1] = $com['score'];
            $mesta[$i+1][2] = $com['zab'] - $com['prop'];
            $mesta[$i+1][3] = $com['zab'];
          }
	  	}
      }
	}
  }
//------------------------------------------------------------------------
 /*дополняем мас.$mesta дополнительными ключевыми позициями по результатам
  очных встреч команд с одинаковыми показателями на первичных ключевых позициях*/
  for ($k=1; $k<count($mesta); $k++){//если все три ключевые позиции равны, то  	if ($mesta[$k][1] == $mesta[$k+1][1] && $mesta[$k][2] == $mesta[$k+1][2] &&
  		$mesta[$k][3] == $mesta[$k+1][3]){//<><><><><><><><><><><><><><><><><><><><>
  	  for ($i=0; $i<count($results); $i++){
   	  //выбираем только нужные нам пары id команды для подсчёта
   	    if ($results[$i]['id'] == $mesta[$k][0]){   	   	  if (($i+1)%2 && $results[$i+1]['id'] == $mesta[$k+1][0]){   	  	   //находим индексы для первой и второй каманды (если первая нечётная)
   	  	   $index_com1 = $i;
   	       $index_com2 = $i+1;
   	       $flag2 = true;
   	      } elseif (!($i+1)%2 && $results[$i-1]['id'] == $mesta[$k+1][0]){   	   	   //находим индексы для первой и второй каманды (если первая чётная)
   	  	   $index_com1 = $i;
    	   $index_com2 = $i-1;
   	       $flag2 = true;
   	      }
   	    } else $flag2 = false;
   	    if ($flag2 == true){//если очная встреча, то делаем дальше
	  	//первоначальные данные по первой команде
	  	  if (!isset($coms2[$results[$index_com1]['id']])){
	  	    $coms2[$results[$index_com1]['id']]['id'] = $results[$index_com1]['id'];//id
	  	    $coms2[$results[$index_com1]['id']]['score'] = $results[$index_com1]['ishod'];//очки
	  	    $coms2[$results[$index_com1]['id']]['zab'] = 0;
	  	    $coms2[$results[$index_com1]['id']]['prop'] = 0;
	  	    for ($j=1; $j<=15; $j++){
	  		  if ($results[$index_com1]["res$j"] == 3){ //забитые голы
          	    $coms2[$results[$index_com1]['id']]['zab']++;
	  		  }
	  		  if ($results[$index_com1]["res$j"] == 2){ //пропущенные голы
	  	  	    $coms2[$results[$index_com1]['id']]['prop']++;
	  		  }
	  	    }
	  	    $flag3 = true;//признак того, что id команды в первый раз пишем в $coms2
		  }
	  	 //первоначальные данные по второй команде
	  	  if (!isset($coms2[$results[$index_com2]['id']])){	  	    $coms2[$results[$index_com2]['id']]['id'] = $results[$index_com2]['id'];//id
	  	    $coms2[$results[$index_com2]['id']]['score'] = $results[$index_com2]['ishod'];//очки
	  	    $coms2[$results[$index_com2]['id']]['zab'] = 0;
	  	    $coms2[$results[$index_com2]['id']]['prop'] = 0;
	  	    for ($j=1; $j<=15; $j++){
	  		  if ($results[$index_com2]["res$j"] == 3){ //забитые голы
          	    $coms2[$results[$index_com2]['id']]['zab']++;
	  		  }
	  		  if ($results[$index_com2]["res$j"] == 2){ //пропущенные голы
	  	  	    $coms2[$results[$index_com2]['id']]['prop']++;
	  		  }
	  	    }
	  	    $flag3 = true;//признак того, что id команды в первый раз пишем в $coms2
	  	  }
		//если команды уже выбирались, то добавляем результаты по ним команде в массив $coms2
		  if ($flag3 <> true){
	  	   //прибавляем очки
	  	   $coms2[$results[$index_com1]['id']]['score'] =+ $results[$index_com1]['ishod'];
	  	   $coms2[$results[$index_com2]['id']]['score'] =+ $results[$index_com2]['ishod'];
	  	   //забитые и пропущенные голы
	  	    for ($j=1; $j<=15; $j++){
	  		  if ($results[$index_com1]["res$j"] == 3){ //забитые голы
          	    $coms2[$results[$index_com1]['id']]['zab']++;
	  		  }
	  		  if ($results[$index_com2]["res$j"] == 3){ //забитые голы
          	    $coms2[$results[$index_com2]['id']]['zab']++;
	  		  }
	  		  if ($results[$index_com1]["res$j"] == 2){ //пропущенные голы
	  	  	    $coms2[$results[$index_com1]['id']]['prop']++;
	  		  }
	  		  if ($results[$index_com2]["res$j"] == 2){ //пропущенные голы
	  	  	    $coms2[$results[$index_com2]['id']]['prop']++;
	  		  }
	  	    }
		  }
		}
  	  } //конец отбора данных по очным встречам одной пары команд
//<><><><><><><><><><><><><><><><><><><><>    }
  } //конец отбора очных встреч
//echo '<pre>';
//print_r($coms2);
//echo '</pre>';
  //дописываем мас.$mesta новыми данными из мас.$coms2
  for ($k=1; $k<count($mesta); $k++){  	foreach ($coms2 as $com){
  	  if ($mesta[$k][0] == $com['id']){  		$mesta[$k][4] = $com['score'];//очки
  		$mesta[$k][5] = $com['zab'] - $com['prop'];//разница забитых и пропущенных
  		$mesta[$k][6] = $com['zab'];//забитые голы
  	  }
  	}
  }
 //-----------------------------------------------------------------------
 /*перебираем массив $mesta на предмет идентичности ключевых позиций
 для определения места в турнирной таблице */
  for ($x=1; $x<3; $x++){//перебор по ключевым позициям в мас.$mesta    $max_eq = 0;
    $y = 1;
    $flag = false;
    do {//иттерации в количестве максимального значения повторений ключевых позиций
  	  for ($i=1; $i<count($mesta); $i++){	    //если ключевые позиции ($x) двух команд равны
	    if ($mesta[$i][$x] == $mesta[$i+1][$x]){	      //ищем максимальное значение повторений ключевых позиций ($max_eq)
	   	  if ($flag <> true){//если равных не было в предыдущей иттерации, то
	   	    $max = 2; //присваеваем промежуточному максимуму '2'
	   	    if ($max > $max_eq){//если промежуточный максимум больше окончательного,
	   	   	  $max_eq = $max;//то окончательному присваеваем промежуточный
	   	    }
	   	  } else {//если равные были в предыдущей иттерации, то
	   	    $max++; //к промежуточному максимуму добавляем '1'
	   	    if ($max > $max_eq){
	   	   	  $max_eq = $max;
	   	    }
	   	  }
	   	  /*признак того, что в этой иттерации были равные ключевые позиции
	   	  двух команд */
	   	  $flag = true;
	   	  //и если значение ключевой позиции одной команды меньше другой,
          if ($mesta[$i][$x+1] < $mesta[$i+1][$x+1]){          	//то меняем местами все ключевые позиции этих двух команд
      	    $temp1 = $mesta[$i][0];
      	    $temp2 = $mesta[$i][1];
      	    $temp3 = $mesta[$i][2];
      	    $temp4 = $mesta[$i][3];
      	    $mesta[$i][0] = $mesta[$i+1][0];
      	    $mesta[$i][1] = $mesta[$i+1][1];
      	    $mesta[$i][2] = $mesta[$i+1][2];
      	    $mesta[$i][3] = $mesta[$i+1][3];
      	    $mesta[$i+1][0] = $temp1;
      	    $mesta[$i+1][1] = $temp2;
            $mesta[$i+1][2] = $temp3;
            $mesta[$i+1][3] = $temp4;
          }
	    } else {/*если ключевые позиции ($x) двух команд не равны, то
	    	обнуляем признак того, что в этой иттерации были равные
	    	ключевые позиции */	      $flag = false;
	    }
	  }
     $y++;
    } while ($y < $max_eq);
  }
//echo '<br><br><pre>';
//print_r($mesta);
//echo '</pre>';
//--------------------------------------------------------------------
// начало вывода турнирной таблицы (шапка)
echo'<br><table class="rez_tour"><tr style="background-color:#A8BF89;">
	<td title="Место" class="head">&nbsp;#&nbsp;</td>
	<td class="head" colspan="2">&nbsp;Команда&nbsp;</td>
	<td title="Игры" class="head" width="31">&nbsp;И&nbsp;</td>
	<td title="Выигрыши" class="head" width="31">&nbsp;В&nbsp;</td>
	<td title="Ничьи" class="head" width="31">&nbsp;Н&nbsp;</td>
	<td title="Поражения" class="head" width="31">&nbsp;П&nbsp;</td>
	<td title="Забито и пропущено голов" class="head">&nbsp;Голы&nbsp;</td>
	<td title="Очки" class="head" width="30">&nbsp;О&nbsp;</td>
	<td class="head">&nbsp;Тренер&nbsp;</td>
	<td title="Рейтинг тренера" class="head">&nbsp;РТ&nbsp;</td>
	<td title="Сделано прогнозов" class="head">&nbsp;СП&nbsp;</td>
	<td title="Верных прогнозов" class="head">&nbsp;ВП&nbsp;</td>
	<td title="% верных прогнозов" class="head">&nbsp;%&nbsp;</td>
	<td colspan="3" title="Рейтинг команды" class="head">&nbsp;&nbsp;Р&nbsp;&nbsp;</td>
	</tr>';
//вывод информации в турнирную таблицу из мас.$coms, согластно порядка мест из таб.$mesta
  for ($i=1; $i<=count($mesta); $i++){  	foreach($coms as $com){  	  if ($mesta[$i][0] == $com['id']){  echo '<tr ';
  	    if (!($i%2)){//если нечётная строка рисуем зебру  	  	  echo 'class="shadow"';
  	    } //-- место в турнирной таблице --
  	echo'><td class="ver_line"><span ';
  		if ($_SESSION['user_id'] == $com['trener_id']){  		  echo 'class="b"'; //делаем жирным, если это текущий тренер
  		}
  	echo'>'.$i.'</span></td>
  	  	<td align="left"><img src="'.substr($com['flag'], 1).'"
  	  		style="vertical-align:middle"></td>
  	  	<td align="left" class="ver_line" nowrap><span ';
  		if ($_SESSION['user_id'] == $com['trener_id']){
  		  echo 'class="b"'; //делаем жирным, если это текущий тренер
  		} //название команды
  	echo'>'.$com['name'].'</span>&nbsp;</td>
  	  	<td class="ver_line"><span ';
  		if ($_SESSION['user_id'] == $com['trener_id']){
  		  echo 'class="b"'; //делаем жирным, если это текущий тренер
  		} //количество сыгранных игр
  	echo'>'.$com['games'].'</span></td>
  	  	<td class="ver_line"><span ';
  		if ($_SESSION['user_id'] == $com['trener_id']){
  		  echo 'class="b"'; //делаем жирным, если это текущий тренер
  		} //количество выгрышей
  	echo'>'.$com['win'].'</span></td>
  	  	<td class="ver_line"><span ';
  		if ($_SESSION['user_id'] == $com['trener_id']){
  		  echo 'class="b"'; //делаем жирным, если это текущий тренер
  		} //количество ничьих
  	echo'>'.$com['nich'].'</span></td>
  	  	<td class="ver_line"><span ';
  		if ($_SESSION['user_id'] == $com['trener_id']){
  		  echo 'class="b"'; //делаем жирным, если это текущий тренер
  		} //количество проигрышей
  	echo'>'.$com['lose'].'</span></td>
  	  	<td class="ver_line"><span ';
  		if ($_SESSION['user_id'] == $com['trener_id']){
  		  echo 'class="b"'; //делаем жирным, если это текущий тренер
  		} //забитые и пропущенные голы
  	echo'>'.$com['zab'].'-'.$com['prop'].'</span></td>
  	  	<td class="ver_line"><span ';
  		if ($_SESSION['user_id'] == $com['trener_id']){
  		  echo 'class="b"'; //делаем жирным, если это текущий тренер
  		} //очки
  	echo'>'.$com['score'].'</span></td>
  	  	<td align="left" class="ver_line"><center><span ';
  		if ($_SESSION['user_id'] == $com['trener_id']){
  		  echo 'style="color:#CC6600;" class="b"'; //выделяем другим цветом, если это текущий тренер
  		} //тренер команды
  	echo'>&nbsp;'.$com['trener'].'&nbsp;</span></center></td>
  	  	<td class="ver_line" align="right"><span ';
  		if ($_SESSION['user_id'] == $com['trener_id']){
  		  echo 'class="b"'; //делаем жирным, если это текущий тренер
  		} //рейтинг тренера текущей команды
  	echo'>'.number_format($com['rating_user'],2,".","").'</span>&nbsp;</td>
  	  	<td class="ver_line"><span ';
  		if ($_SESSION['user_id'] == $com['trener_id']){
  		  echo 'class="b"'; //делаем жирным, если это текущий тренер
  		} //количество сделанных прогнозов
  	echo'>'.$com['forecasts'].'</span></td>
  	  	<td class="ver_line"><span ';
  		if ($_SESSION['user_id'] == $com['trener_id']){
  		  echo 'class="b"'; //делаем жирным, если это текущий тренер
  		} //количество верных прогнозов
  	echo'>'.$com['right_prog'].'</span></td>
  	  	<td class="ver_line"><span ';
  		if ($_SESSION['user_id'] == $com['trener_id']){
  		  echo 'class="b"'; //делаем жирным, если это текущий тренер
  		} //% верных прогнозов
  	echo'>'.round($com['right_prog']*100/$com['forecasts'],2).'</span></td>
  	  	<td style="text-align:right"><span ';
  		if ($_SESSION['user_id'] == $com['trener_id']){
  		  echo 'class="b"'; //делаем жирным, если это текущий тренер
  		} //рейтинг команды
  	echo'>'.number_format($com['rating'],2,".","").'</span>&nbsp;</td>
  		<td nowrap>';
  		// вывод рейтинга в последнем туре
  		if ($com['rating_new'] > 0){  	echo '<img src="pics/arrow_up.png" style="vertical-align: middle">';//рейтинг повысился
  		} elseif ($com['rating_new'] < 0) {  	echo '<img src="pics/arrow_down.png" style="vertical-align: middle">';//рейтинг снизился
  		} else {  	echo '<img src="pics/minus.png" style="vertical-align: middle">';//рейтинг не изменился
  		}
  	echo '</td><td><i ';
  		if ($_SESSION['user_id'] == $com['trener_id']){
  		  echo 'class="b" '; //делаем жирным, если это текущий тренер
  		}
  	echo 'style="font-size:12px">'.number_format($com['rating_new'],2,".","").'</i></td></tr>';
  	  }
  	}
  }
echo'</table><br><br><br><br><br>';
 } else {//если в базе нет данных по этому турниру	mes_er(' &nbsp;В этом турнире не сыграно не одной игры !!!');
 }
}
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