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
Приходит с turnirs.php через POST
jrebiy - кнопка жребия, =id турнира клуба с которым работаем
---
$turnir - id турнира с которым работаем
$row	- используется для отбора команд для жеребьёвки
$myrow 	- массив с названиями команд для жеребьёвки
$kol	- количество команд для жеребьёвки
$mas_maket - вспомагательный массив (макет)
$mas_rab   - рабочий массив с командами после жеребьёвки
$iter	   - количество половины иттераций
$i	- перебор вспомагательного массива (ось Х)
$j	- перебор вспомагательного массива (ось Х)
$k	- перебор рабочего массива (текущая иттерация, ось Х)
$t	- перебор рабочего массива (все предыдущие и текущая иттерации, ось Х)
$k	- перебор рабочего массива (все предыдущие и текущая иттерации, ось Y)
$r	- счётчик иттераций
$l	- значение начальной иттерации для проверки уже имеющихся пар
$rows1[] - массив с названиями команд
$tur_n 	 - название турнира
$com_nom - номер команды по-порядку в массиве $myrow
$err	 - =1 ошибка при записи результатов жеребьёвки в базу
$first_com - id первой команды в паре
*/
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="description" content="">
<meta name="keywords" content="">
<title>Футбольный прогноз | Жеребьёвка</title>
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

if (isset($_POST['jrebiy']) && $_POST['jrebiy'] <> ''){$turnir = $conn -> real_escape_string(htmlspecialchars(trim($_POST['jrebiy'])));
// если уже была жеребьёвка по этому чемпионату, то удаляем её
  if (isset($_POST['status']) && $_POST['status'] == 1){	$sql = 'DELETE FROM igra
			WHERE turnir_id = ?';
	if ($stmt = $conn -> prepare($sql)){	  $stmt -> bind_param('i', $turnir);
	  if ($stmt -> execute()){	  	$stmt -> close();
	  }	else error_rez('Ошибка при удалении имеющейся жеребьёвки !!!');
	} else error_rez('Ошибка при удалении имеющейся жеребьёвки !!!');
  }// отбираем команды для жеребьёвки
$sql = 'SELECT id, com_n, flag
		FROM sl_com
		WHERE turnirs_id = ?
		AND user_id IS NOT NULL';
  if ($stmt = $conn -> prepare($sql)){	$stmt -> bind_param('i', $turnir);
	if ($stmt -> execute()){	  $res = $stmt -> get_result();
	  $stmt -> close();
	  while ($row = $res -> fetch_assoc()){		$myrow[] = $row; // массив с командами для жеребьёвки
	  }
	  $res -> close();
	} else error_rez('Не удалось отобрать команды для жеребьёвки !!!');
  } else error_rez('Не удалось отобрать команды для жеребьёвки !!!');
// формируем вспомагательный массив для жеребьёвки ($mas_maket)
  $kol = count($myrow); // количество команд для жеребьёвки
  for ($i=1; $i<=$kol; $i++){	for ($j=1; $j<=$kol; $j++){	  if ($i == $j){
	    $mas_maket[$i][$j] = 0;
	  } else {		  $mas_maket[$i][$j] = 1;
	  	}
	}
  }
//echo '<pre>';
//print_r($myrow);
//echo '</pre>';
// алгоритм жеребьёвки
if ($kol % 2 == 0){
	$iter = ($kol - 1); // количество половины иттераций
} else {	$iter = $kol;
}
for ($r=1; $r<=$iter*2; $r++){  $mas_rab[$r][0] = 0; // начинаем рабочий массив
  if ($r > $iter){ // розделяем два круга чемпионата  	$l = $iter + 1; // сдвигаем первую позицию для поиска в предыдущих рядых пар значений
  } else {    $l = 1;
  }
  if ($r % 2 <> 0){ //если текущая иттерация - нечётная, то перебираем с первой позиции по последнюю
    for ($i=1; $i<=$kol; $i++){      for ($j=1; $j<=$kol; $j++){	    if ($mas_maket[$i][$j] == 1){		  $flag = 0;
		  // проверяем чтобы в текущем ряду не было таких значений
		  for ($k=1; $k<count($mas_rab[$r]); $k++){		    if ((isset($mas_rab[$r][$k]) && $mas_rab[$r][$k] == $i) ||
		  	    (isset($mas_rab[$r][$k]) && $mas_rab[$r][$k] == $j)){			  $flag = 1; // флаг, что такое значение уже было в этом ряду
			  break;
		    }
 		  }
 		  // проверяем чтобы в предыдущих рядах не было таких пар значений
 		  for ($t=$l; $t<$r; $t++){ 		    for ($k=1; $k<count($mas_rab[$l]); $k=$k+2){			  if (((isset($mas_rab[$t][$k]) && $mas_rab[$t][$k] == $i) ||
				  (isset($mas_rab[$t][$k]) && $mas_rab[$t][$k] == $j)) &&
				  ((isset($mas_rab[$t][$k+1]) && $mas_rab[$t][$k+1] == $i) ||
				  (isset($mas_rab[$t][$k+1]) && $mas_rab[$t][$k+1] == $j))){			    $flag = 1;//флаг, что такая пара уже где-то была среди всех предыдущих рядов
			    break(2);
			  }
 		    } 		  }
 		  if ($flag == 0){ // если таких значений раньше не было, то заносим их в рабочий массив
		    $mas_rab[$r][] = $i;
		    $mas_rab[$r][] = $j;
		    $mas_maket[$i][$j] = 0; //во вспомагательном массиве, в выбранной позиции ставим '0'
		  }
	    }
      }
    }
  } else {//если текущая иттерация - чётная, то перебираем с последней позиции по первую    for ($i=$kol; $i>=1; $i--){
      for ($j=1; $j<=$kol; $j++){
	    if ($mas_maket[$i][$j] == 1){
		  $flag = 0;
		  // проверяем чтобы в текущем ряду не было таких значений
		  for ($k=1; $k<count($mas_rab[$r]); $k++){
		    if ((isset($mas_rab[$r][$k]) && $mas_rab[$r][$k] == $i) ||
		  	    (isset($mas_rab[$r][$k]) && $mas_rab[$r][$k] == $j)){
			  $flag = 1; // флаг, что такое значение уже было в этом ряду
			  break;
		    }
 		  }
 		  // проверяем чтобы в предыдущих рядах не было таких пар значений
 		  for ($t=$l; $t<=$r; $t++){
 		    for ($k=1; $k<count($mas_rab[$l]); $k=$k+2){
			  if (((isset($mas_rab[$t][$k]) && $mas_rab[$t][$k] == $i) ||
				  (isset($mas_rab[$t][$k]) && $mas_rab[$t][$k] == $j)) &&
				  ((isset($mas_rab[$t][$k+1]) && $mas_rab[$t][$k+1] == $i) ||
				  (isset($mas_rab[$t][$k+1]) && $mas_rab[$t][$k+1] == $j))){
			    $flag = 1;//флаг, что такая пара уже где-то была среди всех предыдущих рядов
			    break(2);
			  }
 		    }
 		  }
 		  if ($flag == 0){ // если таких значений раньше не было, то заносим их в рабочий массив
		    $mas_rab[$r][] = $i;
		    $mas_rab[$r][] = $j;
		    $mas_maket[$i][$j] = 0; //во вспомагательном массиве, в выбранной позиции ставим '0'
		  }
	    }
      }
    }
  } // конец проверки чётности иттерации
}
/*
echo '<pre>';
print_r($mas_maket);
echo '</pre>';
echo '<pre>';
print_r($mas_rab);
echo '</pre>';*/
// вывод результатов жеребьёвки на страницу и запись их в базу
echo '<center><h2><b>Жеребьёвка:</b></h2></center>';
$res1 = $conn -> query('SELECT id, com_n
					   FROM sl_com');
if (!$res1) error_rez('Не удалось отобрать названия команд после жеребьёвки !!!');
while ($row1 = $res1 -> fetch_assoc()){	$rows1[] = $row1; // массив с названиями команд
} // отбираем название турнира
$sql = 'SELECT turnir_n
		FROM turnirs
		WHERE id = ?';
if ($stmt = $conn -> prepare($sql)){	$stmt -> bind_param('i', $turnir);
	if ($stmt -> execute()){		$res = $stmt -> get_result();
		$tur_n = $res -> fetch_assoc(); // название турнира
		$stmt -> close();
		$res -> close();
	} else error_rez('Не удалось выбрать название турнира !!!');
} else error_rez('Не удалось выбрать название турнира !!!');
// отображаем туры чемпионата после жеребьёвки и записываем в базу
echo '<center><table>';
for ($m=1; $m<=count($mas_rab); $m++){echo '<tr><td colspan="5"><center><h3><b>'.$tur_n['turnir_n'].', '.$m.'-й тур</b></h3></center></td></tr>';
  for ($n=1; $n<count($mas_rab[$m]); $n++){  	$com_nom = $mas_rab[$m][$n] - 1; // номер команды в массиве $myrow
  	if ($n % 2 <> 0){ // первая командаecho '<tr><td align="right">'.$myrow[$com_nom]['com_n'].'</td>
	  <td><img src="..'.$myrow[$com_nom]['flag'].'" style="vertical-align:middle"></td>
	  <td>-</td>';
	  $sql = 'INSERT INTO igra
			  (turnir_id, tur_nom, com1_id)
			  VALUES (?, ?, ?)';
	  if ($stmt = $conn -> prepare($sql)){		$stmt -> bind_param('iii', $turnir,$m,$myrow[$com_nom]['id']);
		if ($stmt -> execute()){		  $stmt -> close();
		  $first_com = $myrow[$com_nom]['id'];
		} else {			error_rez('Ошибка при записи результатов жеребьёвки в базу !!!');
			$err = 1;
		}
	  }	else {	  	  error_rez('Ошибка при записи результатов жеребьёвки в базу !!!');
	  	  $err = 1;
	  }
  	} elseif ($n % 2 == 0){ // вторая команда  echo '<td><img src="..'.$myrow[$com_nom]['flag'].'" style="vertical-align:middle"></td>
  		<td>'.$myrow[$com_nom]['com_n'].'</td></tr>';
  		$sql = 'UPDATE igra
			  	SET com2_id = ?, sigrano = "0"
			  	WHERE com1_id = ? AND turnir_id = ? AND tur_nom = ?';
	    if ($stmt = $conn -> prepare($sql)){
		  $stmt -> bind_param('iiii', $myrow[$com_nom]['id'],$first_com,$turnir,$m);
		  if ($stmt -> execute()){
		    $stmt -> close();
		  } else {		  	error_rez('Ошибка при записи результатов жеребьёвки в базу !!!');
		  	$err = 1;
		  }
	    } else {	    	error_rez('Ошибка при записи результатов жеребьёвки в базу !!!');
	    	$err = 1;
	    }
  	}
  }
}
echo '<tr><td colspan="5" class="gor_line">&nbsp;</td></tr></table></center>';
  if (!isset($err)){
	echo '<center><h3><b>Результаты жеребьёвки удачно записаны в базу !!!</b></h3></center>
		<center><form action="turnirs.php"><button>
		<img src="../pics/undo.png" style="vertical-align: middle">&nbsp;Назад
		</button></form></center>';
  }
} else error_rez('Ошибка жеребьёвки, дальнейшая работа невозможна !!!');
?>
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