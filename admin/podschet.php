<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['login']) || !isset($_SESSION['rights']) ||
	($_SESSION['rights'] <> 2 && $_SESSION['rights'] <> 1) || (!isset($_POST['turnir']) &&
	(!isset($_GET['tur']) || !isset($_GET['turnir'])))){
	//header('Location:../index.php');
	echo '<script type="text/javascript">';
	echo 'window.location.href="../index.php";';
	echo '</script>';
	exit();
}
require_once('../blocks/connect.php');
require_once('functions.php');
/*
$turnir 	- id турнира с каким работаем (передаётся через POST или GET)
$turnir_max - максимальное значение id турнира, которое есть в базе
			 (используется только для проверки правильности значения турнира в GET)
$tur		- № тура текущего турнира (передаётся через GET)
$tur_max	- максимальное значение тура текущего турнира, которое есть в базе
			 (используется только для проверки правильности значения тура в GET)
$tur_prog	- массив со всеми турами и прогнозами к ним
			  нужного нам турнира и сезона (если они есть)
$max		- количество туров в базе в этом турнире
$turs_exec	- массив с перечислением туров, в которых посчитан результат
$pari_igr	- массив с парами игр текущего тура чемпионата
$igri		- массив с реальными играми и их результатами
$forecast	- массива с названием команд клуба и их прогнозами
$net_igri	- количество несыгранных игр из числа основных
$net_igri_dop - количество несыгранных игр из числа дополнительных
$r_ishod	- массив с исходами игры играков
			 (0 - матч не состоялся,
			  1 - выиграла первая команда,
			  X - ничья,
			  2 - выиграла вторая команда)
$home 		- текущая позиция для "домашнего матча"
$r			- массив с результатами текущего матча клуба по каждой команде:
   			 1. Название команды (0)
   			 2. Результат (1-15)
			  (-1 - матч не состоялся или доп.матч несыгранный,
			    0 - прогноз не совпал с реальным результатом,
			    1 - прогноз совпал,
			    2 - прогноз не совпал, игрок пропустил гол,
			    3 - прогноз совпал, игрок забил гол)
			 3. Количество очков (16)
			 4. id индекса исхода матча (17)
			 5. Игра "дома" или "в гостях" (18)
			   (1 - "дома"
			    0 - "в гостях")
			 6. Название команды соперника (19)
			 7. Разность голов (20)
			 8. Количество голов (21)
$pr			- массив c результатами текущего матча клуба по каждой команде, которые
			  будут записаны в базу (табл. turnirs):
   			 1. id команды (0)
   			 2. Результат (1-15)
			  (-1 - матч не состоялся или доп.матч несыгранный,
			    0 - прогноз не совпал с реальным результатом,
			    1 - прогноз совпал,
			    2 - прогноз не совпал, игрок пропустил гол,
			    3 - прогноз совпал, игрок забил гол)
			 3. Количество очков (16)
			 4. id индекса исхода матча (17)
			 5. Игра "дома" или "в гостях" (18)
			   (1 - "дома"
			    0 - "в гостях")
			 6. id команды соперника (19)
			 7. Разность голов (20)
			 8. Количество голов (21)
			 9. id тренера команды (22)
			 10.id тренера команды соперника (23)
$flag		- посчитаны или нет результаты туров
			  (1 - посчитан,
			   0 - непосчитан)
$doma		- признак того, что команда игра домашний матч (для расчёта рейтинга)
			  (1 - игра дома
			   0 - игра в гостях)
$rating_old	- массив со значениями id команды и её предыдущего рейтинга и
			  значения id соперника	и его предыдущего рейтинга
			  (0 - com_id, rating   id и предыдущий рейтинг текущей команды
			   1 - com_id, rating)  id и предыдущий рейтинг команды соперника
$rating_old2 - массив со значениями id тренеров текущей команды и коменды-соперника
			  и их предыдущими рейтингами
			  (0 - user_id, rating_user	  id тренера текущей команды и предыдущий его рейтинг
			   1 - user_id, rating_user)  id тренера команды-соперника и предыдущий его рейтинг
$ishod			- индекс исхода матча
$razn_gol		- индекс разницы голов
$ves_match_ind 	- значение индекса весомости матча текущего турнира
$dr				- игра на домашнем поле или нет
			  	 (100 - на домашнем поле
			   	  0 - в гостях)
$rating_com		- прошлый рейтинг текущей команды
$rating_soper 	- прошлый рейтинг команды соперника
$rating_user 	- предыдущий рейтинг тренера текущей команды
$rating_user_soper - предыдущий рейтинг тренера команды-соперника
$i_rez_ojid		- индекс ожидаемого результата матча
$i_rez_ojid2	- индекс ожидаемого результата матча (для подсчёта рейтинга тренера)
$rating_new		- рейтинг команды за текущий тур
$rating_new2	- рейтинг тренера за текущую игру
$rating			- новый рейтинг команды
$rating2		- новый рейтинг тренера
*/
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="description" content="">
<meta name="keywords" content="">
<title>Футбольный прогноз | Подсчёт результатов туров</title>
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
<?php
//если была нажата кнопка "Записать" в базу данных результатов данного тура текущего турнира
if (isset($_POST['save_ok']) && $_POST['save_ok'] == 'ok'){  if (isset($_POST['turnir']) && isset($_POST['tur']) && isset($_POST['amount_coms'])){  	$turnir = $conn -> real_escape_string(htmlspecialchars(trim($_POST['turnir'])));	$tur = $conn -> real_escape_string(htmlspecialchars(trim($_POST['tur'])));
	$amount = $conn -> real_escape_string(htmlspecialchars(trim($_POST['amount_coms'])));
  }//echo '<pre>';
//print_r($_POST);
//echo '</pre>';
  $sql = 'SELECT id, com_n, user_id
  		  FROM sl_com';
  if ($stmt = $conn -> prepare($sql)){
	if ($stmt -> execute()){
	  $res = $stmt -> get_result();
	  $stmt -> close();
  	} else error_rez('Ошибка при поиске id команды!!! '.$conn -> error);
  } else error_rez('Ошибка при поиске id команды!!! '.$conn -> error);
  while ($row = $res -> fetch_assoc()){
    $coms[] = $row; //массив с названиями всех команд с тренерами и их id
  }
  $res -> close();
  // защищаем данные взятые с POST и присваеваем их переменным
  for ($i=1; $i<=$amount; $i++){  	for($j=0; $j<22; $j++){  	  // если это название команды, то ищем её id, и id тренера
  	  if ($j == 0){  	  	$com_name = $conn -> real_escape_string(htmlspecialchars(trim($_POST['pr'.$i.'_'.$j.''])));
    	foreach ($coms as $pole){		  if ($pole['com_n'] == $com_name){			$pr{$i.'_'.$j} = $pole['id'];
			$pr{$i.'_22'} = $pole['user_id'];
		  }
    	}
  	  } elseif($j == 19){// если это название команды соперника, то ищем её id  	  	$com_name_sopernik = $conn -> real_escape_string(htmlspecialchars(trim($_POST['pr'.$i.'_'.$j.''])));
    	foreach ($coms as $pole){
		  if ($pole['com_n'] == $com_name_sopernik){
			$pr{$i.'_'.$j} = $pole['id'];
			$pr{$i.'_23'} = $pole['user_id'];
		  }
    	}
  	  }	else {
  	    $pr{$i.'_'.$j} = $conn -> real_escape_string(htmlspecialchars(trim($_POST['pr'.$i.'_'.$j.''])));
  	  }
  	}
  }
//---------------------------------------------------------------------------------
  // ищем команды, которые принимают участи в текущем туре данного турнира
  $sql = 'SELECT id, com1_id, com2_id
  		  FROM igra
  		  WHERE turnir_id = ? AND tur_nom = ?';
  if ($stmt = $conn -> prepare($sql)){  	$stmt -> bind_param('ii', $turnir, $tur);    if ($stmt -> execute()){      $res = $stmt -> get_result();      $stmt -> close();
    } else error_rez('Ошибка поиска команд из таб. "igri" !'.$conn -> error);
  } else error_rez('Ошибка поиска команд из таб. "igri" !'.$conn -> error);
  while ($row = $res -> fetch_assoc()){  	$coms_igra[] = $row; //массив с id пар команд текущего турнира, этого тура
  }
//---перебираем все команды для определения рейтинга и записи результатов в базу---
  $k = 0; //счётчик перебора элементов массива $coms_igra
  for ($i=1; $i<=$amount; $i++){  // определение рейтинга
  // -----узнаём предшествующий рейтинг своей команды и команды соперника-----
    $sql = 'SELECT max(id), com_id, rating
    		FROM turnir_table
    		WHERE (com_id = ? AND turnir_id = ?)
    		   OR (com_id = ? AND turnir_id = ?)
    		GROUP BY com_id, rating';
    if ($stmt = $conn -> prepare($sql)){      $stmt -> bind_param('iiii', $pr{$i.'_0'}, $turnir, $pr{$i.'_19'}, $turnir);
	  if ($stmt -> execute()){	    $res = $stmt -> get_result();
	    $stmt -> close();
	  } else error_rez('Ошибка поиска рейтинга текущей команды и её соперника !'.$conn -> error);
    } else error_rez('Ошибка поиска рейтинга текущей команды и её соперника !'.$conn -> error);
    if ($res -> num_rows <> 0){//если в данном турнире был прошлый тур, то находим рейтинги
      while ($row = $res -> fetch_assoc()){
        $rating_old[] = $row; //массив с id текущей команды и её соперника, и их рейтингами
      }
    } else { //если в данном турнире небыло прошлого тура (это первый)      $rating_old[0]['com_id'] = $pr{$i.'_0'};
      $rating_old[0]['rating'] = 0;
      $rating_old[1]['com_id'] = $pr{$i.'_19'};
      $rating_old[1]['rating'] = 0;
    }
  // -----узнаём предшествующий рейтинг тренера и тренера-соперника------
    $sql = 'SELECT max(id), user_id, rating_user
    		FROM turnir_table
    		WHERE user_id = ? OR user_id = ?
    		GROUP BY user_id, rating_user';
    if ($stmt = $conn -> prepare($sql)){
      $stmt -> bind_param('ii', $pr{$i.'_22'}, $pr{$i.'_23'});
	  if ($stmt -> execute()){
	    $res = $stmt -> get_result();
	    $stmt -> close();
	  } else error_rez('Ошибка поиска рейтинга текущего тренера и тренера-соперника !'.$conn -> error);
    } else error_rez('Ошибка поиска рейтинга текущего тренера и тренера-соперника !'.$conn -> error);
    //если данный тренер и тренер-соперник имеют предыдущий рейтинг
    if ($res -> num_rows <> 0){
      while ($row = $res -> fetch_assoc()){
      //массив с id тренера текущей команды и id тренера команды-соперника, и их рейтингами
        $rating_old2[] = $row;
      }
      //если данный тренер или тренер-соперник не имеют предыдущего рейтинга
      if ($res -> num_rows == 1){
        //если рейтинг имеет только тренер текущей команды
        if ($rating_old2[0]['user_id'] == $pr{$i.'_22'}){
      	  $rating_old2[1]['user_id'] = $pr{$i.'_23'};
      	  $rating_old2[1]['rating_user'] = 0;
      	//если рейтинг имеет только тренер команды-соперника
        } elseif ($rating_old2[0]['user_id'] == $pr{$i.'_23'}){          $temp = $rating_old2[0]['rating_user'];
          $rating_old2[0]['user_id'] = $pr{$i.'_22'};
      	  $rating_old2[0]['rating_user'] = 0;
      	  $rating_old2[1]['user_id'] = $pr{$i.'_23'};
      	  $rating_old2[1]['rating_user'] = $temp;
        }
      }
    } else {//если данный тренер и тренер-соперник не имеют предыдущих рейтингов
      $rating_old2[0]['user_id'] = $pr{$i.'_22'};
      $rating_old2[0]['rating_user'] = 0;
      $rating_old2[1]['user_id'] = $pr{$i.'_23'};
      $rating_old2[1]['rating_user'] = 0;
    }
   // узнаём индекс исхода матча
    $sql = "SELECT id, ind
    		FROM sl_ishod";
    if ($stmt = $conn -> prepare($sql)){
	  if ($stmt -> execute()){
		$res = $stmt -> get_result();
		$stmt -> close();
		while ($row = $res -> fetch_assoc()){
          $ishod_ind[] = $row;//массив с индексами возможного исхода матча
		}
	  } else error_rez('Запрос на выборку индекса исхода матча не выполнен! '.$conn -> error);
    } else error_rez('Запрос на выборку индекса исхода матча не выполнен! '.$conn -> error);
    foreach ($ishod_ind as $pole){
      if ($pole['id'] == $pr{$i.'_17'}){      	$ishod = $pole['ind']; //необходимый индекс исхода матча для данной команды
      }
    }
   // узнаём индекс разницы голов
    $sql = "SELECT raznica_golov, ind
    		FROM sl_raznica_golov";
    if ($stmt = $conn -> prepare($sql)){
	  if ($stmt -> execute()){
		$res = $stmt -> get_result();
		$stmt -> close();
		while ($row = $res -> fetch_assoc()){
          $razn_gol_ind[] = $row;//массив с индексами различных разниц голов
		}
	  } else error_rez('Запрос на выборку индекса разницы голов не выполнен! '.$conn -> error);
    } else error_rez('Запрос на выборку индекса разницы голов не выполнен! '.$conn -> error);
    foreach ($razn_gol_ind as $pole){
      if ($pole['raznica_golov'] == $pr{$i.'_20'}){
      	$razn_gol = $pole['ind']; //необходимый индекс разницы голов для данной команды
      }
    }
   // узнаём индекс весомости матча
    $sql = 'SELECT ves_match_id
    		FROM turnirs
    		WHERE id = ?';
    if (!$stmt = $conn -> prepare($sql)){      error_rez('Запрос на выборку индекса разницы голов из табл. "turnirs" не выполнен! '.$conn -> error);
    } else {
      $stmt -> bind_param('i', $turnir);
	  if (!$stmt -> execute()){
		 error_rez('Запрос на выборку индекса разницы голов из табл. "turnirs" не выполнен! '.$conn -> error);
	  }	else {
	 	$res = $stmt -> get_result();
		$stmt -> close();
		$ves_match_id = $res -> fetch_row(); //id весомости текущего турнира
        $res -> close();
        $sql = 'SELECT id, ind
    			FROM sl_ves_matcha';
        if ($stmt = $conn -> prepare($sql)){          if ($stmt -> execute()){          	$res = $stmt -> get_result();
          	$stmt -> close();
          	while ($row = $res -> fetch_assoc()){        	  $ves_match[] = $row; //массив со значениями весомости матча и их id
          	}
          	foreach ($ves_match as $pole){			  if ($pole['id'] == $ves_match_id[0]){			  	$ves_match_ind = $pole['ind']; //значение индекса весомости матча текущего турнира
			  }
          	}
          }	else error_rez('Запрос на выборку индекса разницы голов c базы не выполнен! '.$conn -> error);
        } else error_rez('Запрос на выборку индекса разницы голов c базы не выполнен! '.$conn -> error);
	  }
    }
   // непосредственно процесс поиск рейтинга
   // индекс ожидаемого результата матча
    if ($pr{$i.'_18'} == 1){
   	  $dr = 100; //игра на домашнем поле
    } else {   	  $dr = 0;   //игра в гостях
    }
    //поиск предыдущего рейтинга текущей команды и команды соперника
    foreach ($rating_old as $pole){
   	  if ($pole['com_id'] == $pr{$i.'_0'}){
   	 	$rating_com = $pole['rating'];
   	  }
   	  if ($pole['com_id'] == $pr{$i.'_19'}){   	 	$rating_soper = $pole['rating'];
   	  }
   	  if ($pole['com_id'] == $pr{$i.'_0'} && $pole['com_id'] == $pr{$i.'_19'}){   	  	error_rez('Ошибка в массиве с рейтингами текущей команды и её соперника! '.$conn -> error);
   	  }
    }
    //поиск предыдущего рейтинга текущего тренера и тренера команды соперника
    foreach ($rating_old2 as $pole){
   	  if ($pole['user_id'] == $pr{$i.'_22'}){
   	 	$rating_user = $pole['rating'];
   	  }
   	  if ($pole['user_id'] == $pr{$i.'_23'}){
   	 	$rating_user_soper = $pole['rating'];
   	  }
   	  if ($pole['user_id'] == $pr{$i.'_22'} && $pole['user_id'] == $pr{$i.'_23'}){
   	  	error_rez('Ошибка в массиве с рейтингами тренера текущей команды и команды-соперника! '.$conn -> error);
   	  }
    }
   $i_rez_ojid = 1/(pow(10, -($rating_com-$rating_soper+$dr)/400)+1);
   $i_rez_ojid2 = 1/(pow(10, -($rating_user-$rating_user_soper+$dr)/400)+1);
   $rating_new = round(($ishod - $i_rez_ojid) * $razn_gol * $ves_match_ind, 2);
   $rating_new2 = round(($ishod - $i_rez_ojid2) * $razn_gol * $ves_match_ind, 2);
   $rating = round($rating_new + $rating_com, 2);
   $rating2 = round($rating_new2 + $rating_user, 2);
  // -----------------------------------------------------------------------------------
  // записываем в базу результаты этого тура текущего турнира
	$sql = "INSERT INTO turnir_table
			(com_id, user_id, turnir_id, tur_nom, res1, res2, res3, res4, res5,
			res6, res7, res8, res9, res10, res11, res12, res13, res14, res15,
			ishod, doma, rating, rating_new, rating_user)
			VALUES  (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
					 ?, ?, ?, ?, ?)";
	if (!$stmt = $conn -> prepare($sql)){	  $message_er = '&nbsp;&nbsp;Информацию по командам в таб. "turnir_table" внести не удалось!!! '.$conn -> error;
	} else {
	  $stmt -> bind_param('iiiiiiiiiiiiiiiiiiiiiddd',$pr{$i.'_0'},$pr{$i.'_22'},
	  			$turnir,$tur,$pr{$i.'_1'},$pr{$i.'_2'},$pr{$i.'_3'},$pr{$i.'_4'},
	  			$pr{$i.'_5'},$pr{$i.'_6'},$pr{$i.'_7'},$pr{$i.'_8'},$pr{$i.'_9'},
	  			$pr{$i.'_10'},$pr{$i.'_11'},$pr{$i.'_12'},$pr{$i.'_13'},$pr{$i.'_14'},
				$pr{$i.'_15'},$pr{$i.'_16'},$pr{$i.'_18'}, $rating, $rating_new, $rating2);
	  if (!$stmt -> execute()){
		$message_er = '&nbsp;&nbsp;Информацию по командам в таб. "turnir_table" внести не удалось!!! '.$conn -> error;
	  } else {	  	$stmt -> close();
       //запись результата в базу данных в таб. igra
        if ($i%2){          if ($pr{$i.'_0'} == $coms_igra[$k]['com1_id'] && $pr{$i.'_19'} == $coms_igra[$k]['com2_id']){
            $sql = 'UPDATE igra
            		SET rez1 = ?, rez2 = ?, sigrano = "1"
            		WHERE id = ?';
		    if($stmt = $conn -> prepare($sql)){		      $l = $i + 1;
		      $stmt -> bind_param('iii', $pr{$i.'_21'}, $pr{$l.'_21'}, $coms_igra[$k]['id']);
		      if ($stmt -> execute()){		      	$stmt -> close();	  			$message_ok = '&nbsp;&nbsp;Информация успешно записана в базу !!!';
		      } else {		      	$message_er = '&nbsp;&nbsp;Информацию по командам в таб. "turnir_table" внести не удалось !!! '.$conn -> error;
		      }
		    } else {		      $message_er = '&nbsp;&nbsp;Информацию по командам в таб. "turnir_table" внести не удалось !!! '.$conn -> error;
		    }
	      } else {	      	$message_er = '&nbsp;&nbsp;Ошибка набора данных при сравнении id играющих между собой команд в таб. "igra" и массиве $pr !!!'.$conn -> error;;
	      }
	      $k++;
        }
	  }
    }
  }
  $sql = 'UPDATE r_igra
          SET r_active = "0"
          WHERE turnir_id = ? AND tur_nom = ?';
  if($stmt = $conn -> prepare($sql)){
	$stmt -> bind_param('ii', $turnir, $tur);
	if ($stmt -> execute()){
	  $stmt -> close();
	} else {
	  $message_er = '&nbsp;&nbsp;Активность в таб. "r_igra" изменить не удалось !!! '.$conn -> error;;
	  unset($message_ok);
	}
  } else {
	$message_er = '&nbsp;&nbsp;Активность в таб. "r_igra" изменить не удалось !!! '.$conn -> error;
	unset($message_ok);
  }
/* //проверка что находиться в переменных $pr
  for ($i=1; $i<=$amount; $i++){
  	for($j=0; $j<23; $j++){
  	  echo '$pr'.$i.$j.'- '.$pr{$i.'_'.$j}.'<br>';
  	}
  } */
}
//-----------------------------------------------
if (isset($_POST['turnir']) && !isset($_POST['save_ok'])){
  $turnir = $conn -> real_escape_string(htmlspecialchars(trim($_POST['turnir'])));
}
// проверяем правильность введёного через GET
if (isset($_GET['tur']) && isset($_GET['turnir'])){// проверка турнира
  $sql = 'SELECT MAX(id) FROM turnirs';
  if ($stmt = $conn -> prepare($sql)){
	if ($stmt -> execute()){
	  $res = $stmt -> get_result();
	  $stmt -> close();
	} else error_rez('Ошибка проверки имеющихся турниров и туров игр !');
  } else error_rez('Ошибка проверки имеющихся турниров и туров игр !');
  $turnir_max = $res -> fetch_row();
  $res -> close();
  if ($_GET['turnir'] < 1 || $_GET['turnir'] > $turnir_max[0] || !is_numeric($_GET['turnir'])){
	error_rez('Ошибочное значение турнира !');
  } else {
  	 $turnir = $conn -> real_escape_string(htmlspecialchars(trim($_GET['turnir'])));
  }
// проверка тура
  $sql = 'SELECT MAX(tur_nom)
  		  FROM igra
  		  WHERE turnir_id = ?';
  if ($stmt = $conn -> prepare($sql)){
    $stmt -> bind_param('i', $turnir);
	  if ($stmt -> execute()){
	    $res = $stmt -> get_result();
	    $stmt -> close();
  	  } else error_rez('Ошибка при проверке правильности тура!!! '.$conn -> error);
  } else error_rez('Ошибка при проверке правильности тура!!! '.$conn -> error);
  $tur_max = $res -> fetch_row();
  $res -> close();
  if ($_GET['tur'] < 1 || $_GET['tur'] > $tur_max[0] || !is_numeric($_GET['tur'])){
	error_rez('Ошибочное значение тура !');
  } else {
    $tur = $conn -> real_escape_string(htmlspecialchars(trim($_GET['tur'])));
  }
}
// выбираем все имеющиеся туры с базы
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
  }
  //ищем название турнира и № сезона по id турнира
  $sql = 'SELECT turnir_n, season_nom
  		  FROM turnirs
  		  WHERE id = ?';
  if ($stmt = $conn -> prepare($sql)){
	$stmt -> bind_param('i', $turnir);
	if ($stmt -> execute()){
	  $res = $stmt -> get_result();
	  $stmt -> close();
	  $i = 0;
	  $current_turnir = $res -> fetch_assoc();
	} else error_rez('Запрос на выборку турниров клуба не выполнен! '.$conn -> error);
  } else error_rez('Запрос на выборку турниров клуба не выполнен! '.$conn -> error);
echo '<h3>Подсчёт результатов: &nbsp;&nbsp;<span class="theme">
	  "'.$current_turnir["turnir_n"].'" (Сезон '.$current_turnir["season_nom"].')</span></h3>
  	<table border="0" width="761" class="rez_tour">
  	<tr><td colspan="2"><h3 class="b">
  	<span style="float:left">Туры:&nbsp;</span></h3>';
  // если в базе есть какие-то туры в этом турнире - выводим их
 if ($max <> 0){//при этом условии выполняем весь скрипт до конца
    // смотрим в каких турах результаты посчитаны
    $sql = "SELECT DISTINCT tur_nom
    		FROM turnir_table
    		WHERE turnir_id = ?
    		ORDER BY tur_nom";
    if ($stmt = $conn -> prepare($sql)){	  $stmt -> bind_param('i', $turnir);
	  if ($stmt -> execute()){		$res = $stmt -> get_result();
		$stmt -> close();
		if ($res -> num_rows <> 0){//если в базе есть что-то по этому турниру
		  $i = 0;
		  while ($mas[] = $res -> fetch_row()){		    //записываем в массив все посчитанные туры            $turs_exec[$i] = $mas[$i][0];
            $i++;
		  }
		}
	  } else error_rez('Запрос на выборку просчитанных туров не выполнен! '.$conn -> error);
    } else error_rez('Запрос на выборку просчитанных туров не выполнен! '.$conn -> error);
//echo '<pre>';
//print_r($turs_exec);
//echo '</pre>';
    echo '<ul class="zakladki">';
    for ($i=1; $i<=$max; $i++){
echo '<li class="';
 	  // если тур ещё не выбирали, то активным будет последний
 	  if (isset($_POST['turnir']) && !isset($_POST['save_ok']) && $i == $max){
 	    echo 'current';
 	    $tur = $max; //последний тур - текущий, текущий турнир известен ($turnir)
 	  }
 	  // если тур выбрали, то он и будет активным
 	  if (isset($_GET['tur']) && $i == $tur){
 	    echo 'current';
 	  }
 	  // если была запись результатов тура турнира в базу, то он будет текущим
 	  if (isset($_POST['save_ok']) && $_POST['save_ok'] == 'ok' && $i == $tur){ 	    echo 'current';
 	  }
echo '"><a href="podschet.php?tur='.$i.'&turnir='.$turnir.'">'.$i.'</a></li>';
    }
echo '</ul></td></tr><tr class="gor_line"><td>
	  <table border="0" class="rez_tour">
	  <tr><td width="60"></td>';
	//ставим отметку посчитаны или нет туры
	for ($j=1; $j<=$max; $j++){	  $flag	= 0;	  if (isset($turs_exec)){
	    foreach ($turs_exec as $pole){	  	  if ($j == $pole){	  	    $flag = 1; //результаты тура посчитаны
	  	  }
	    }
	  }	  if ($flag == 1){	  echo '<td><img src="../pics/dot_green.bmp"></td>';
	  } elseif ($flag == 0){	  echo '<td><img src="../pics/dot_red.bmp"></td>';
	  }
	}
echo '</tr></table></td></tr>';
  /* если в текущем туре есть не все данные (например: не все результаты
	реальных матчей или не все игроки сделали прогнозы */
   $stop = 0;
   foreach ($tur_prog as $pole){
	  if (($pole[2] == NULL && ord($pole[2]) <> 48) ||
	  	  ($pole[3] == NULL && ord($pole[3]) <> 48) ||
	  	  $pole[5] == NULL || $pole[6] == NULL){
	  	$stop = 1; //признак того, что в текущем туре собрана не вся информация
	  }
   }
  if ($stop == 1){//--- если есть не все результаты, то заполняем тоблицы данными, которые есть ---  // создаём вспомагательные массивы
  $sql = 'SELECT igra.date, n1.com_n, n2.com_n
  		  FROM igra
  		    INNER JOIN sl_com AS n1
  		      ON igra.com1_id = n1.id
  		    INNER JOIN sl_com AS n2
  		      ON igra.com2_id = n2.id
  		  WHERE igra.turnir_id = ? AND igra.tur_nom = ?';
  if ($stmt = $conn -> prepare($sql)){
	$stmt -> bind_param('ii', $turnir, $tur);
	if ($stmt -> execute()){
	  $res = $stmt -> get_result();
	  $stmt -> close();
	} else error_rez('Запрос на выборку матчей турнира не выполнен! '.$conn -> error);
  } else error_rez('Запрос на выборку матчей турнира не выполнен! '.$conn -> error);
  while ($row = $res -> fetch_row()){
    $pari_igr[] = $row; /* массив с парами игр текущего тура чемпионата */
  }
  $i = 0;
  $flag = 0;
  $igri[] = 0; //массив с реальными играми и их результатами
  foreach($tur_prog as $pole){
  	if ($pole[4] == $tur){ //формируем массивы только с тех записей, в которых нужный тур
  	  // формирование массива с реальными играми и их результатами
  	  if ($igri[$i]['com1'] <> $pole[0] || $igri[$i]['com2'] <> $pole[1]){
  	    $i++;
  	    $igri[$i]['com1'] = $pole[0];
  	    $igri[$i]['com2'] = $pole[1];
  	    $igri[$i]['rez1'] = $pole[2];
  	    $igri[$i]['rez2'] = $pole[3];
  	  }
  	  if ($flag <> $i){ // проверка: когда нам нужно начать сначала $j
  	  	$j = 1;
  	  	$flag = $i;
  	  } else {
  	  	$j++;
  	  }
  	  // формирование массива с названием команд клуба и их прогнозами
  	  if (!isset($forecast[$j])){
  		$forecast[$j][0] = $pole[5];
  		$forecast[$j][$i] = $pole[6];
  	  } else {
  	  	$forecast[$j][$i] = $pole[6];
  	  }
  	}
  }
//echo '<pre>';
//print_r($igri);
//print_r($forecast);
//print_r ($pari_igr);
//echo '</pre>';
echo '<tr><td><br><br>
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
	 echo '<td><img src="../pics/cancel.png" style="vertical-align: middle"></td></tr>';
	 }
   }
echo '</table>
	<tr><td><br><br><table class="rez_tour" border="0">
	<tr class="gor_line"><th class="ver_line"></th>';
   for ($i=1; $i<count($igri); $i++){
  	echo '<th width="25" ';
  	echo '>'.$i.'</th>'; //ставим номерацию игр
   }
echo '</tr><tr class="gor_line" style="background-color:#E7EDDF;">';
  $n = 0;
  $net_igri = 0;
  $net_igri_dop = 0;
  foreach($igri as $pole){ // ставим реальный исход матчей
   if ($pole == 0){//если это первая ячейка, то пишем текст и ставим вер.полосу
echo '<td nowrap class="ver_line"><b>Реальный исход</b></td>';
   } else {
   	 if ($pole['rez1'] == 99 && $pole['rez2'] == 99 && $n < 11){
   	 	$net_igri++; //количество несостоящихся реальных матчей (из основных)
   	 } elseif ($pole['rez1'] == 99 && $pole['rez2'] == 99 &&
   	 		  ($n > 10 && $n <= 10 + $net_igri + $net_igri_dop)){
		$net_igri_dop++; //если не состоялся матч из дополнительных
   	 }
 echo '<td height="25"';
      if ($n == 10 + $net_igri + $net_igri_dop){//если это последняя из игр, что учитываются
      echo 'class="ver_line"';
      }
      if ($n == 15){ //если это самая последняя ячейка, то ставим вер.линию
      echo 'class="ver_line"';
      }
 echo '><b>';
     if ($pole['rez1'] == NULL && ord($pole['rez1']) <> 48 &&
     	 $pole['rez2'] == NULL && ord($pole['rez2']) <> 48){ 		echo '<img src="../pics/non.png" style="vertical-align: middle">';
 	    $r_ishod[$n] = '&nbsp;';
     } elseif ($pole['rez1'] == 99 && $pole['rez2'] == 99){//если матч не состоялся
		echo '<img src="../pics/cancel.png" style="vertical-align: middle">';
		$r_ishod[$n] = '0';
     } elseif ($pole['rez1'] > $pole['rez2']){
 		echo '1';
 		$r_ishod[$n] = '1';
     } elseif ($pole['rez1'] < $pole['rez2']){
 		echo '2';
 		$r_ishod[$n] = '2';
     } elseif ($pole['rez1'] == $pole['rez2']){
 		echo 'X';
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
    if ($r_ishod[$k] == '0'){ //если есть несостоявшийся матч
	  if ($k == $home){//если несостоявшийся матч это 10-я позиция или текущая позиция,
	    $home = $k - 1;  //то текущую позицию делаем на единицу меньше
	  }
    }
  }
  // пишем сами прогнозы команд
  $m = 0; //счётчик команд участвующих в этом туре чемпионата с результатами
  foreach($pari_igr as $pole2){ //листаем массив с парами игр текущего тура чемпионата
    for ($l=1; $l<=2; $l++){ //из пары берём сначала одну команду, потом вторую
      foreach($forecast as $polay){
        if ($pole2[$l] == $polay[0] || $polay[0] == NULL){          //если нужная команда найдена или никто из участников не делал прогнозов
  	      echo '<tr '; //то выводим данные, которые есть
  	      if ($l == 2){ // ставим гор.линию после пары команд
  	      echo 'class="gor_line"';
  	      }
  	      echo '>';
  	      for($i=0; $i<count($polay); $i++){
      echo '<td height="25" ';
	        if ($i == 0){//если это первая ячейка, текст сдвигаем влево и ставим вер.полосу
	      echo 'align="left" class="ver_line" ';
	        } elseif ($i == $home){ //если это "домашний матч", выделяем фон
			echo 'background="../pics/fon1.jpg"';
	        }
	        if ($i == 10 + $net_igri + $net_igri_dop){//если каких-то матчей не было, то
	      echo 'class="ver_line"'; //вер.линию ставим после доп.матчей или после 10-го матча
	        } elseif ($i == 15){     // если это последняя ячейка,
	      echo 'class="ver_line"';   // то ставим вер.линию
	        }
      echo '><span ';
			if (isset($r_ishod[$i]) && $r_ishod[$i] == '0'){
            //если матч не состоялся
              echo 'style="background:#bbb;"';
            } elseif (isset($r_ishod[$i]) && $r_ishod[$i] == $polay[$i]){
            //если это не первая ячейка и данный прогноз совпал с
            //реальным результатом, то делаем его "жирным"
			  echo 'class="b"';
            }
      echo '>';
            /*если это первая ячейка (где должно быть название команды игрока) и
            если она не существует или = NULL, то пишем название из $pari_igr*/
      		if ($i == 0 && (!isset($polya[0]) || $polya[0] == NULL)){
      		  echo $pole2[$l];
      		} else echo $polay[$i];//иначе пишем то, что есть
      echo '</span></td>';
  	      }
  	  echo '</tr>';
  	    }
      }
    }
  }
echo '</table>
	<table class="rez_tour" border="0">
	<tr><td>&nbsp;</td></tr>
	<tr class="gor_line"><td height="25"><b>Счёт</b></td></tr>';
  // поле голов
  for ($i=1; $i<=count($pari_igr); $i++){
    echo '<tr ';
    if (!($i % 2)){//если это вторая команда, заносим количество очков
    echo 'class="gor_line"';
    }
echo '><td height="25">&nbsp;</td></tr>';
  }
echo '</table></td></tr></table>';
echo '<br><table><tr><td>
	<form action="turnirs.php"><br><button>
	<img src="../pics/undo.png" style="vertical-align: middle">&nbsp;Назад
	</button></form></td></tr></table><br>';
  } elseif ($stop == 0){
  // ----------------- вывод результатов игр ------------------------
  // создаём вспомагательные массивы
  $sql = 'SELECT igra.date, n1.com_n, n2.com_n
  		  FROM igra
  		    INNER JOIN sl_com AS n1
  		      ON igra.com1_id = n1.id
  		    INNER JOIN sl_com AS n2
  		      ON igra.com2_id = n2.id
  		  WHERE igra.turnir_id = ? AND igra.tur_nom = ?';
  if ($stmt = $conn -> prepare($sql)){
	$stmt -> bind_param('ii', $turnir, $tur);
	if ($stmt -> execute()){
	  $res = $stmt -> get_result();
	  $stmt -> close();
	} else error_rez('Запрос на выборку матчей турнира не выполнен! '.$conn -> error);
  } else error_rez('Запрос на выборку матчей турнира не выполнен! '.$conn -> error);
  while ($row = $res -> fetch_row()){
    $pari_igr[] = $row; /* массив с парами игр текущего тура чемпионата */
  }
  $i = 0;
  $flag = 0;
  $igri[] = 0; //массив с реальными играми и их результатами
  foreach($tur_prog as $pole){
  	if ($pole[4] == $tur){ //формируем массивы только с тех записей, в которых нужный тур
  	  // формирование массива с реальными играми и их результатами
  	  if ($igri[$i]['com1'] <> $pole[0] || $igri[$i]['com2'] <> $pole[1]){
  	    $i++;
  	    $igri[$i]['com1'] = $pole[0];
  	    $igri[$i]['com2'] = $pole[1];
  	    $igri[$i]['rez1'] = $pole[2];
  	    $igri[$i]['rez2'] = $pole[3];
  	  }
  	  if ($flag <> $i){ // проверка: когда нам нужно начать сначала $j
  	  	$j = 1;
  	  	$flag = $i;
  	  } else {
  	  	$j++;
  	  }
  	  // формирование массива с названием команд клуба и их прогнозами
  	  if (!isset($forecast[$j])){
  		$forecast[$j][0] = $pole[5];
  		$forecast[$j][$i] = $pole[6];
  	  } else {
  	  	$forecast[$j][$i] = $pole[6];
  	  }
  	}
  }
//echo '<pre>';
//print_r($igri);
//print_r($forecast);
//print_r ($pari_igr);
//echo '</pre>';
 if (count($igri)-1 == 15){
echo '<tr><td><br><br>
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
	 echo '<td><img src="../pics/cancel.png" style="vertical-align: middle"></td></tr>';
	 }
   }
echo '</table></td></tr><tr><td><br><br><table class="rez_tour" border="0">
	<tr class="gor_line"><th class="ver_line"></th>';
   for ($i=1; $i<count($igri); $i++){
  	echo '<th width="25" ';
  	     /*  if ($i == 10){//если это десятая ячейка, то ставим вер.линию
           echo 'class="ver_line"';
  	       } */
  	echo '>'.$i.'</th>'; //ставим номерацию игр
   }
echo '</tr><tr class="gor_line" style="background-color:#E7EDDF;">';
  $n = 0;
  $net_igri = 0;
  $net_igri_dop = 0;
  foreach($igri as $pole){ // ставим реальный исход матчей
   if ($pole == 0){//если это первая ячейка, то пишем текст и ставим вер.полосу
echo '<td nowrap class="ver_line"><b>Реальный исход</b></td>';
   } else {
   	 if ($pole['rez1'] == 99 && $pole['rez2'] == 99 && $n < 11){
   	 	$net_igri++; //количество несостоящихся реальных матчей (из основных)
   	 } elseif ($pole['rez1'] == 99 && $pole['rez2'] == 99 &&
   	 		  ($n > 10 && $n <= 10 + $net_igri + $net_igri_dop)){
		$net_igri_dop++; //если не состоялся матч из дополнительных
   	 }
 echo '<td height="25"';
      if ($n == 10 + $net_igri + $net_igri_dop){//если это последняя из игр, что учитываются
      echo 'class="ver_line"';
      }
      if ($n == 15){ //если это самая последняя ячейка, то ставим вер.линию
      echo 'class="ver_line"';
      }
 echo '><b>';
     if ($pole['rez1'] == 99 && $pole['rez2'] == 99){//если матч не состоялся
		echo '<img src="../pics/cancel.png" style="vertical-align: middle">';
		$r_ishod[$n] = '0';
     } elseif ($pole['rez1'] > $pole['rez2']){
 		echo '1';
 		$r_ishod[$n] = '1';
     } elseif ($pole['rez1'] < $pole['rez2']){
 		echo '2';
 		$r_ishod[$n] = '2';
     } elseif ($pole['rez1'] == $pole['rez2']){
 		echo 'X';
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
    if ($r_ishod[$k] == '0'){ //если есть несостоявшийся матч
	  if ($k == $home){//если несостоявшийся матч это 10-я позиция или текущая позиция,
	    $home = $k - 1;  //то текущую позицию делаем на единицу меньше
	  }
    }
  }
  // пишем сами прогнозы команд
  $m = 0; //счётчик команд участвующих в этом туре чемпионата с результатами
  foreach($pari_igr as $pole2){ //листаем массив с парами игр текущего тура чемпионата
    for ($l=1; $l<=2; $l++){ //из пары берём сначала одну команду, потом вторую
      foreach($forecast as $polay){
        if ($pole2[$l] == $polay[0]){//если нужная команда найдена, то выводим её
          $m++;
  	      $r[$m][0] = $polay[0];//создаём массив с результатами матча клуба каждой команды
  	      echo '<tr ';
  	      if ($l == 2){ // ставим гор.линию после пары команд
  	      echo 'class="gor_line"';
  	      }
  	      echo '>';
  	      for($i=0; $i<count($polay); $i++){
      echo '<td height="25" ';
	        if ($i == 0){//если это первая ячейка, текст сдвигаем влево и ставим вер.полосу
	      echo 'align="left" class="ver_line" ';
	        } elseif ($i == $home){ //если это "домашний матч", выделяем фон
			echo 'background="../pics/fon1.jpg"';
	        }
	        if ($i == 10 + $net_igri + $net_igri_dop){//если каких-то матчей не было, то
	      echo 'class="ver_line"'; //вер.линию ставим после доп.матчей или после 10-го матча
	        } elseif ($i == 15){     // если это последняя ячейка,
	      echo 'class="ver_line"';   // то ставим вер.линию
	        }
      echo '><span ';
            if ($i == 0){//если это первая ячейка, то ещё проверяем данная команда
              //не текущего ли тренера, если так, то выделяем её "жирным"
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
	      	     echo 'class="b"';
	    	     }
	  		   }
              }
            } elseif (isset($r_ishod[$i]) && $r_ishod[$i] == '0'){
            //если матч не состоялся
              echo 'style="background:#bbb;"';
              $r[$m][$i] = -1;
            } elseif (isset($r_ishod[$i]) && $r_ishod[$i] == $polay[$i]){
            //если это не первая ячейка и данный прогноз совпал с
            //реальным результатом, то делаем его "жирным" и $r = 1
			  $r[$m][$i] = 1;
			  echo 'class="b"';
            } elseif (isset($r_ishod[$i]) && $r_ishod[$i] <> $polay[$i]){
              $r[$m][$i] = 0; //если прогноз не совпал, то $r = 0
            }
      echo '>'.$polay[$i].'</span></td>';
  	      }
  	  echo '</tr>';
  	    }
      }
    }
  }
 // поиск забитых мячей
  for ($i=1; $i<=count($r); $i+=2){
    for ($j=1; $j<=10+$net_igri+$net_igri_dop; $j++){ // $j < count($r[$i]) - это все 15 прогнозов
	  if ($j == $home && $r[$i][$j] == 1){//гол хозяйвов поля (только 10-я игра)
	    $r[$i][$j] = 3;   //забитый гол
	    $r[$i+1][$j] = 2; //пропущенный гол
	  } elseif ($r[$i][$j] > $r[$i+1][$j]){//гол забила первая команда
	    $r[$i][$j] = 3;   //забитый гол
	    $r[$i+1][$j] = 2; //пропущенный гол
	  } elseif ($r[$i][$j] < $r[$i+1][$j]){//гол забила вторая команда
	    $r[$i][$j] = 2;   //пропущенный гол
	    $r[$i+1][$j] = 3; //забитый гол
	  }
    }
    //всем несыгранным доп.матчам ставится "-1" и они не будут идти в статистику
    for ($j=15; $j>10+$net_igri+$net_igri_dop; $j--){
      $r[$i][$j] = -1;
      $r[$i+1][$j] = -1;
    }
  }
echo '</tr></table>
	<table class="rez_tour" border="0">
	<tr><td>&nbsp;</td></tr>
	<tr class="gor_line"><td height="25"><b>Счёт</b></td></tr>';
  // подсчёт голов
  for ($i=1; $i<=count($r); $i++){
  	$gol[$i] = 0;
    for ($j=1; $j<count($r[$i]); $j++){
      if ($r[$i][$j] == 3){
		$gol[$i]++;
      }
    }
    echo '<tr ';
    if (!($i % 2)){//если это вторая команда, заносим количество очков
    echo 'class="gor_line"';
      //если первая команда (из этой пары команд) имеет больше голов
      if ($gol[$i-1] > $gol[$i]){
		$r[$i-1][] = 3; //то первая команда выграла (получает 3 очка)
		$r[$i][] = 0; //а вторая проиграла (получает 0 очков)
		$r[$i-1][] = 1; //присваивается id индексa исхода матча в случае победы
		$r[$i][] = 3; //присваивается id индексa исхода матча в случае поражения
      } elseif ($gol[$i-1] < $gol[$i]){//если вторая команда имеет больше голов
      	$r[$i][] = 3; //то вторая команда выграла (получает 3 очка)
      	$r[$i-1][] = 0; //а первая проиграла (получает 0 очков)
		$r[$i-1][] = 3; //присваивается id индексa исхода матча в случае поражения
		$r[$i][] = 1; //присваивается id индексa исхода матча в случае победы
      } else { //если две команды имеют одинаковое количество голов
		$r[$i-1][] = 1; //то первая команда сыграла в ничью (получает 1 очко)
		$r[$i][] = 1; //и вторая команда сыграла в ничью (получает 1 очко)
		$r[$i-1][] = 2; //присваивается id индексa исхода матча в случае ничьи
		$r[$i][] = 2; //присваивается id индексa исхода матча в случае ничьи
      }
      $r[$i-1][] = 1; //заносим информацию, что первая команда играет дома
      $r[$i][] = 0; //а вторая команда играет в гостях
      $r[$i-1][] = $r[$i][0]; //первой команде заносим id соперника
      $r[$i][] = $r[$i-1][0]; //второй команде заносим id соперника
      // вносим разность голов
      $r[$i-1][] = abs($gol[$i-1] - $gol[$i]); //модуль разницы      $r[$i][] = abs($gol[$i-1] - $gol[$i]);
      $r[$i-1][] = $gol[$i-1]; //количество голов первой команды
      $r[$i][] = $gol[$i]; //количество голов второй команды
    }
echo '><td height="25"><b style="color:red;">'.$gol[$i].'</b></td></tr>';
  }
echo '</table></td></tr></table>
	 <br><table><tr>';
  // проверяем есть ли результаты тура текущего турнира в базе
  $sql = 'SELECT id
  		  FROM turnir_table
  		  WHERE turnir_id = ? AND tur_nom = ?';
  if ($stmt = $conn -> prepare($sql)){	$stmt -> bind_param('ii', $turnir, $tur);
	if ($stmt -> execute()){	  $res = $stmt -> get_result();
	  $stmt -> close();
	} else error_rez('Запрос данных из таб. \'turnir_table\' не выполнен! '.$conn -> error);
  } else error_rez('Запрос данных из таб. \'turnir_table\' не выполнен! '.$conn -> error);
  // если результатов тура текущего турнира в базе нет, то ставим кнопку "Записать"
  // и передаём данные через POST в этот же скрипт для записи в базу
  if ($res -> num_rows == 0){
echo'<td><form action="podschet.php" method="post">';
	for ($i=1; $i<=count($r); $i++){	  for ($j=0; $j<count($r[$i]); $j++){	  echo '<input type="hidden" name="pr'.$i.'_'.$j.'" value="'.$r[$i][$j].'">';
	  }
	}
echo'<input type="hidden" name="turnir" value="'.$turnir.'">
	<input type="hidden" name="tur" value="'.$tur.'">
	<input type="hidden" name="amount_coms" value="'.count($r).'">
	<br><button type="submit" name="save_ok" value="ok">
	<img src="../pics/save.png" style="vertical-align: middle">&nbsp;
	 Записать</button>&nbsp;&nbsp;&nbsp;</form></td>';
  }
echo'<td><form action="turnirs.php"><br><button>
	<img src="../pics/undo.png" style="vertical-align: middle">&nbsp;Назад
	</button></form></td></tr></table><br>';
 } else {//если в массиве $igri не 15 игр
  echo '<tr><td>&nbsp;<img src="../pics/edit_no.png" style="vertical-align: middle">&nbsp;
  	<b style="font-size:15px;">Некоректный набор данных в массиве: "$igri"!</b>
  	</td></tr></table><br>';
 }
 }
 } else {// если в базе нет информации об этом турнире
  echo '&nbsp;<img src="../pics/edit_no.png" style="vertical-align: middle">&nbsp;
  	<b style="font-size:15px;">В этом турнире не сыграно не одного матча!</b>
  	</td></tr></table><br>';
 }
isset($message_ok) ? mes_ok($message_ok) : false;
isset($message_er) ? mes_er($message_er) : false;
//echo '<pre>';
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
<?php require_once("../blocks/footer.php"); ?>
</tr>
</table>
</body>
</html>