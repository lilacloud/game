<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['login']) || !isset($_SESSION['rights'])){
	header('Location:index.php');
	exit();
}
require_once('blocks/connect.php');
require_once('functions.php');
/*
Передаются через POST
pr{$i} 		- сделанный прогноз на $i-й матч
igra_id{$i} - id текущего матча
turnir_id   - id турнира текущего матча
count_prog  - количество активных матчей, которые выводятся для прогноза
pr_ok 		- если равно 'ok', то данные в POST передались успешно
---
$rez 		- результат выборки программы
$myrow 		- массив с результатами выборки программы построчно
$dt			- массив с датой и временем выбранные с базы данных для отображения в форме
$data		- дата в нужном формате для отображения в форме
$time		- время в нужном формате для отображения в форме
$message_ok - сообщение об удачном выполнении операции
$messsage_er - сообщение об ошибке
$rez1 		- результат вставки записей в таблицу 'prognozi'
$post_pr 	- прогноз на i-й матч сделанный ранее
$count_prog - количество активных матчей, прогнозы на которые вводятся в базу
$igra_id    - id текущего матча, которій заносится в базу
$pr         - прогноз на текущий матч
$user       - id пользователя, который делает прогноз
$turnir		- id турнира клуба, в котором учавствует текущий игрок
$status		- ='est' - игрок участвует хотя бы в одном из активных туров чемпионатов клуба
			  ='net' - игрок не участвует не в одном из активных туров чемпионатов клуба
$status_save - ='new' - прогноза на эту игру этого игрока в таб. prognozi ещё нет
			   ='edit' - прогноз на эту игру этого игрока в таб. prognozi уже есть
$pr_id		- id прогноза на эту игру, который был сделан ранее
*/
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="description" content="">
<meta name="keywords" content="">
<title>Футбольный прогноз | Сделать прогноз</title>
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
<?php
// --- запись прогнозов в базу данных ---
if (isset($_POST['pr_ok']) && $_POST['pr_ok'] == 'ok'){//echo '<pre>';
//print_r($_POST);
//echo '</pre>';
$user = $conn -> real_escape_string(htmlspecialchars(trim($_SESSION['user_id'])));
$sql = 'SELECT user_id, turnirs_id, id
        FROM sl_com
        WHERE user_id = ?';
   if ($stmt = $conn -> prepare($sql)){
      $stmt -> bind_param('i', $user);
      if ($stmt -> execute()){
        $res = $stmt -> get_result();
        $stmt -> close();
        while ($row = $res -> fetch_assoc()){
            $rows[] = $row;
        }
        $res -> close();
      } else error_rez('Ошибка поиска команды турнира клуба, для которой делается прогноз !!!');
   } else error_rez('Ошибка поиска команды турнира клуба, для которой делается прогноз !!!');
$count_prog = $conn -> real_escape_string(htmlspecialchars(trim($_POST['count_prog'])));
	for ($i=1; $i<=$count_prog; $i++){
      // ищем нужную команду
      $turnir = $conn -> real_escape_string(htmlspecialchars(trim($_POST["turnir_id$i"])));
      for ($j=0; $j<=count($rows); $j++){
        if ($rows[$j]['user_id'] == $user && $rows[$j]['turnirs_id'] == $turnir){
            $com = $rows[$j]['id'];
            break 1;
        }
      }
      !isset($com) ? error_rez('Неизвестно к какой команде турнира клуба относится прогноз !!!') : false;
      $igra_id = $conn -> real_escape_string(htmlspecialchars(trim($_POST["igra_id$i"])));
       // проверяем есть ли запись прогноза этой команды ($com) на эту игру (igra_id$i) в таб. prognozi
       $sql ='SELECT id
       		  FROM prognozi
       		  WHERE r_igra_id = ?
       		    AND com_id = ?';
       if ($stmt = $conn -> prepare($sql)){  		 $stmt -> bind_param('ii', $igra_id, $com);
  		 if ($stmt -> execute()){		   $res = $stmt -> get_result();
		   $stmt -> close();
		   if ($res -> num_rows <> 0){		   	 $status_save = 'edit';
		   	 $pr_id = $res -> fetch_row();
		   	 $res -> close();
		   } else {		     $status_save = 'new';
		     $res -> close();
		   }
  		 } else error_rez('Ошибка при поиске ранее сделанных прогнозов для записи новых !!!');
       } else error_rez('Ошибка при поиске ранее сделанных прогнозов для записи новых !!!');
       $pr = $conn -> real_escape_string(htmlspecialchars(trim($_POST["pr$i"])));
       $pr == '' ? $pr = NULL : false;
       // если такого прогноза ещё не было
       if (isset($status_save) && $status_save == 'new'){
         $sql = 'INSERT INTO prognozi (r_igra_id, prognoz, com_id)
				 VALUES (?, ?, ?)';
     	 if ($stmt = $conn -> prepare($sql)) {
           $stmt -> bind_param('isi', $igra_id, $pr, $com);
           if ($stmt -> execute()){
             $message_ok = 'Прогнозы внесены успешно!';
             $stmt -> close();
           } else error_rez('Не удалось записать прогнозы в базу данных!');
         } else error_rez('Не удалось записать прогнозы в базу данных!');
       } elseif (isset($status_save) && $status_save == 'edit'){         $sql = 'UPDATE prognozi
         		 SET prognoz = ?
         		 WHERE id = ?';
         if ($stmt = $conn -> prepare($sql)){		   $stmt -> bind_param('si', $pr, $pr_id[0]);
		   if ($stmt -> execute()){		     $message_ok = 'Прогнозы внесены успешно!';
		     $stmt -> close();
		   } else error_rez('Не удалось изменить прогнозы в базе данных!');
         } else error_rez('Не удалось изменить прогнозы в базе данных! (prepare)');
       } else $message_er = 'Неизвестно: нужно добавлять новые или изменять старые прогнозы !!!';
	}
}
//--- Вывод на страницу программ для прогноза ---
// выбираем матчи только с активными программами и только тех турниров, в которых учавствует данный пользователь
$user = $conn -> real_escape_string(htmlspecialchars(trim($_SESSION['user_id'])));
$sql = 'SELECT r_igra.id, r_date, turnirs.turnir_n, season_nom, tur_nom, sl_turnir_r.turnir_n,
			   c1.com_n, c2.com_n, r_igra.turnir_id, prognoz, sl_com.flag, sl_com.com_n, sl_com.id
        FROM r_igra
			INNER JOIN turnirs
				ON r_igra.turnir_id = turnirs.id
			INNER JOIN sl_turnir_r
			    ON r_igra.r_turnir_id = sl_turnir_r.id
			INNER JOIN sl_com AS c1
				ON r_igra.r_com1_id = c1.id
			INNER JOIN sl_com AS c2
				ON r_igra.r_com2_id = c2.id
			RIGHT JOIN sl_com
				ON r_igra.turnir_id = sl_com.turnirs_id
			LEFT JOIN (SELECT prognozi.prognoz, prognozi.r_igra_id
					   FROM prognozi
					   	  LEFT JOIN sl_com
					   	    ON prognozi.com_id = sl_com.id
					   WHERE sl_com.user_id = ?) AS prog
				ON r_igra.id = prog.r_igra_id
        WHERE r_igra.r_active = "1"
        AND sl_com.user_id = ?
        ORDER BY r_igra.id';
if ($stmt = $conn -> prepare($sql)) {
  $stmt -> bind_param('ii',$user, $user);
  if (!$stmt -> execute()){
    error_rez('Запрос на выборку матчей с программы не выполнен!');
  } else {
    $rez = $stmt -> get_result();
    $stmt -> close();
    if ($rez -> num_rows <> 0){
      while ($row = $rez -> fetch_row()) {
          $myrow[] = $row;
      }
      $rez -> close();
      //делаем запрос к базе, таб.'igra' чтобы проверит учавствует ли
      //текущая команда именно в этом туре
      $sql = 'SELECT turnir_id, tur_nom, com1_id, com2_id
      		  FROM igra
      		  WHERE date IS NOT NULL
      		  	AND sigrano = "0"';
      if ($stmt = $conn -> prepare($sql)){		if ($stmt -> execute()){		  $res = $stmt -> get_result();
		  $stmt -> close();
		  while ($row = $res -> fetch_assoc()){		  	 $games[] = $row;
		  }
		  $res -> close();
		} else error_rez('Ошибка базы данных при работе с таб."igra"!');
      } else error_rez('Ошибка базы данных при работе с таб."igra"!');
echo '<pre>';
print_r($games);
//print_r($myrow);
echo '</pre>';
      //если совпадает и турнир и тур и первая или вторая команда,
      //то увеличиваем флаг и эта команда учавствует в прогнозе
      $kol_myrow = count($myrow);
      for ($i=0; $i<$kol_myrow; $i++){		$flag = 0;
		foreach($games as $pole){		  if ($myrow[$i][8] == $pole['turnir_id'] && $myrow[$i][4] == $pole['tur_nom'] &&
		  	  ($myrow[$i][12] == $pole['com1_id'] || $myrow[$i][12] == $pole['com2_id'])){			$flag = 1;
		  }
		}
        if ($flag == 0){//если команда не участвует в этом туре этого турнира, то
    	  unset($myrow[$i]); //удаляем её из списка прогнозов
        }
      }
      if (count($myrow) == 0){
        $status = 'net'; //игрок не участвует не в одном из активных туров чемпионатов клуба
      } else {      	$status = 'est'; //игрок участвует хотя бы в одном из активных туров чемпионатов клуба
      }
    } else {      $status = 'net'; //игрок не участвует не в одном из активных туров чемпионатов клуба
    }
  }
} else error_rez('Запрос на выборку матчей с программы не выполнен!');
//echo '<pre>';
//print_r($myrow);
//echo '</pre>';
//сдвигаем удалённые записи в массиве $games к началу (если были удаления)
end($myrow);
$kol_myrow = key($myrow) + 1;
$key_mas = 0;
for ($i=0; $i<$kol_myrow; $i++){  if (isset($myrow[$i])){    if ($i > $key_mas){
      $myrow[$key_mas] = $myrow[$i];
      unset($myrow[$i]);
    }
  	$key_mas++;
  }
}
if (isset($status) && $status == 'net')://игрок не участвует не в одном из активных туров чемпионатов  echo '<br /><h3><center><img src="pics/edit_no.png" style="vertical-align: middle">&nbsp;
  		Сейчас для Вас нет активных программ для прогнозов !!!</center></h3>';
elseif (isset($status) && $status == 'est'):
echo '<h2><b>Сделайте Ваши прогнозы:</b></h2>
<form method="post" action="prognoz.php">
<table style="border-collapse:collapse;">';
//echo '<pre>';
//print_r($myrow);
//echo '</pre>';
// выводим все активные турниры с участием данного игрока
  for ($j=0; $j<=count($myrow)-1; $j+=15){
  echo'<tr><td colspan="7">
  	<h2><img src="'.substr($myrow[$j][10], 1).'" style="vertical-align: middle">
	<span class="u"><i>'.$myrow[$j][11].'</i></span></h2></td></tr>
  	<tr><td colspan="7"><h3>
  	<b style="background:#FFB080;">&nbsp;'.$myrow[$j][2].' (сезон '.$myrow[$j][3].'): '.$myrow[$j][4].' тур.
	</b></h3></td></tr>
  <tr class="gor_line">
  <th>&nbsp;</th><th class="ver_line">Турнир</th><th>Дата и время</th>
  <th>Команда 1</th><th>&nbsp;</th><th>Команда 2</th><th>Прогноз</th>
  </tr>';
	for ($i=1; $i<=15; $i++){
echo '
	<tr>
		<td>'.$i.'</td>
		<td class="ver_line">'.$myrow[$j+$i-1][5].'&nbsp;</td>';
		$dt = explode(" ", $myrow[$j+$i-1][1]);
		$data = date('d.m.Y', strtotime($dt[0]));
		$time = date('H:i', strtotime($dt[1]));
echo '  <td>&nbsp;'.$data.'&nbsp;'.$time.'&nbsp;</td>
		<td align="right">'.$myrow[$j+$i-1][6].'</td>
		<td><b>&nbsp;VS&nbsp;</b></td>
		<td>'.$myrow[$j+$i-1][7].'</td>
		<td align="center">';
		$m = $j + $i; // порядковый номер прогноза
echo '	<select name="pr'.$m.'">
		   <option selected value="'.$myrow[$j+$i-1][9].'">'.$myrow[$j+$i-1][9].'</option>
	       <option value="1">1</option>
		   <option value="X">X</option>
		   <option value="2">2</option>
		</select>
		<input type="hidden" name="igra_id'.$m.'" value="'.$myrow[$j+$i-1][0].'">
		<input type="hidden" name="turnir_id'.$m.'" value="'.$myrow[$j+$i-1][8].'">
		</td>
	</tr>';
	}
echo '<tr><td colspan="7" class="gor_line_top">&nbsp;</td></tr>';
  }
?>
<tr><td>&nbsp;</td></tr>
<tr>
	<td>&nbsp;</td>
	<td>
	<input type="hidden" name="count_prog" value="<?php echo count($myrow); ?>">
	<button type="submit" name="pr_ok" value="ok">
		<img src="pics/save.png" style="vertical-align: middle">&nbsp;Сохранить
	</button>
	</td>
</tr>
</table>
</form><br>
<?php
 else:  echo 'Ошибка при проверке участия команд игрока в турнирах !!!';
endif;
isset($message_ok) ? mes_ok($message_ok) : false;
isset($message_er) ? mes_er($message_er) : false;
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