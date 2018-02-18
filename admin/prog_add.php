<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
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
/* переменные, которые пришли с POST
chemp$i - номер названия турнира
com$i.1 - номер названия первой комманды
com$i.2 - номер названия второй комманды
pdate$i - дата проведения матча
ptime$i - время проведения матча
rez1	- количество очков первой команды
rez2	- количество очков второй команды
prog_add2 - id турнира с которым работаем
r_igra_id$i - id матча, который нужно редактировать
status	- что нужно делать с данными
		  ('new' - новые записи, 'edit' - редактирование старых)
prog_ok (значение 'ok') - флаг, что информация занеслась в POST
tur		- номер нового тура, который нужно добавить (передаётся через POST)
---
$rez 		- результат вставки данных в БД
$error 		- если равен '1', значит была ошибка ввода данных в форму и запись
			  в базу данных невозможна
$schet1		- оличество очков первой команды
$schet2		- количество очков второй команды
$r_igra_id 	- id матча, который нужно редактировать
$tur		- номер нового тура
*/
//echo '<pre>';
//print_r($_POST);
//echo '</pre>';
if (isset($_POST['prog_ok']) && $_POST['prog_ok']=='ok'){
	if (isset($_POST['tur']) && $_POST['tur'] == ''){
	  	$error = 'Вы не ввели номер нового тура.
	  			  <br>&nbsp;&nbsp;Вернитесь назад и повторите ввод!';
	} elseif (isset($_POST['tur'])){
		$tur = $conn -> real_escape_string(htmlspecialchars(trim($_POST['tur'])));
	  }
	for ($i=1; $i<=15; $i++){
		$chemp{$i} = $conn -> real_escape_string(htmlspecialchars(trim($_POST["chemp$i"])));
		$com{$i.'1'} = $conn -> real_escape_string(htmlspecialchars(trim($_POST["com${i}_1"])));
		$com{$i.'2'} = $conn -> real_escape_string(htmlspecialchars(trim($_POST["com${i}_2"])));
		$pdate{$i} = $conn -> real_escape_string(htmlspecialchars(trim($_POST["pdate$i"])));
		$ptime{$i} = $conn -> real_escape_string(htmlspecialchars(trim($_POST["ptime$i"])));
		if (isset($_POST["rez1$i"]) && isset($_POST["rez2$i"])){
		$schet1{$i} = $conn -> real_escape_string(htmlspecialchars(trim($_POST["rez1$i"])));
		$schet2{$i} = $conn -> real_escape_string(htmlspecialchars(trim($_POST["rez2$i"])));
		}
		// проверка не пустые ли значения
		if ($chemp{$i}=='' || $com{$i.'1'}=='' || $com{$i.'2'}=='' ||
			$pdate{$i}=='' || $ptime{$i}==''){
			if (!isset($error)){
			$error = 'Вы ввели не всю информацию в '.$i.'-ой игре.
					  <br>&nbsp;&nbsp;Вернитесь назад и повторите ввод!';
			}
		} // проверка даты и времени
		if (!preg_match("~^0?(\d|[0-2]\d|3[0-2])\.0?(\d|1[0-2])\.(\d{4})$~", $pdate{$i}) ||
			!preg_match("/^([0-1][0-9]|[2][0-3]):([0-5][0-9])$/", $ptime{$i})){
			if (!isset($error)){
			$error = 'Ошибка при вводе даты или времени проведения '.$i.'-ой игры.
						<br>&nbsp;&nbsp;Вернитесь назад и повторите ввод!';
			}
		}
	} // проверка есть ли переменная $_POST['prog_add2']
	if (!isset($_POST['prog_add2']) || $_POST['prog_add2'] == ''){
		if (!isset($error)){
		$error = 'Не известно в какой турнир необходимо записать программу!';
		}
	}
	$status = $conn -> real_escape_string(htmlspecialchars(trim($_POST['status'])));
	if (!isset($error)){
		for ($i=1; $i<=15; $i++){
			$data = date('Y-m-d H:i', strtotime($pdate{$i}.' '.$ptime{$i}));
			$date_now = date('Y-m-d H:i:s');
			//проверка есть ли переменная $status и она не пустая
            if (isset($status) && $status <> ''){
				// вводим новую программу
				if ($status == 'new'){
					$sql = "INSERT INTO r_igra
							(r_date, r_turnir_id, r_com1_id, r_com2_id, r_active,
							 turnir_id, tur_nom, date_now)
							VALUES
							 (?, ?, ?, ?, '1', ?, ?, ?)";
					if ($stmt = $conn -> prepare($sql)){
					  $stmt -> bind_param('ssssiis',$data,$chemp{$i},$com{$i.'1'},$com{$i.'2'},
										  $_POST['prog_add2'], $tur, $date_now);
					  if (!$stmt -> execute()){
					 	error_rez('Информацию по '.$i.'-й игре внести не удалось!');
					  } else $stmt -> close();
					} else {
					  	error_rez('Информацию по '.$i.'-й игре внести не удалось!');
					  }
				} elseif ($status == 'edit'){ // редактируем существующую программу
					$r_igra_id = $_POST["r_igra_id$i"];
					$sql = "UPDATE r_igra
							  SET r_date=?, r_turnir_id=?, r_com1_id=?, r_com2_id=?,
							 	  date_now=?, r_rez1=?, r_rez2=?
							  WHERE id = ?";
					// если чсёт не был введёт, записываем в базу NULL
					$schet1{$i} == '' ? $schet1{$i} = NULL : false;
					$schet2{$i} == '' ? $schet2{$i} = NULL : false;
					if ($stmt = $conn -> prepare($sql)){
					  $stmt -> bind_param('sssssiii',$data,$chemp{$i},$com{$i.'1'},$com{$i.'2'},
										  $date_now,$schet1{$i},$schet2{$i},$r_igra_id);
					  if (!$stmt -> execute()){
						  error_rez('Информацию по '.$i.'-й игре внести не удалось!');
					  }
					} else error_rez('Информацию по '.$i.'-й игре внести не удалось!');
				  } else error_rez('\"Status\" is not \'new\' and not \'edit\'!');
			} else error_rez('Неизвестно что нужно делать с данными этой программы!');
		}
		// если добавляем новый тур и он не первый, то предыдущему туру ставим r_active=0
		if (isset($tur) && $tur <> 1 && isset($status) && $status == 'new'){
		$tur = $tur - 1;
		$sql = 'UPDATE r_igra
				SET r_active = "0"
				WHERE tur_nom = ?';
		  if ($stmt = $conn -> prepare($sql)){
			  $stmt -> bind_param('i', $tur);
			  if (!$stmt -> execute()){
			  	error_rez('Ошибка изменения активности предыдущего тура!');
			  }
		  } else error_rez('Ошибка изменения активности предыдущего тура!');
		}
		// ищем самую последнюю игру этого чемпионата и этого тура (в r_igra) и
		// записываем дату этой игры во все игры этого чемпионата и этого тура (в igra)
		$sql = 'SELECT MAX(r_date)
				FROM r_igra
				WHERE turnir_id = ? AND tur_nom = ?';
		if ($stmt = $conn -> prepare($sql)){
		  $stmt -> bind_param('ii', $_POST['prog_add2'], $tur);
		  if ($stmt -> execute()){
			$res = $stmt -> get_result();
			$date_last = $res -> fetch_row(); // последняя дата игр в программе
			$stmt -> close();
			$res -> close();
		  } else error_rez('Ошибка поиска даты проведения '.$tur.'-го тура турнира !!!');
		} else error_rez('Ошибка поиска даты проведения '.$tur.'-го тура турнира !!!');
		$sql = 'UPDATE igra
				SET date = ?
				WHERE turnir_id = ? AND tur_nom = ?';
		if ($stmt = $conn -> prepare($sql)){
		  $stmt -> bind_param('sii', $date_last[0], $_POST['prog_add2'], $tur);
		  if ($stmt -> execute()){
			$stmt -> close();
			header('Location:turnirs.php?message=true');
		  } else error_rez('Ошибка записи в базу даты проведения '.$tur.'-го тура турнира !!!');
		} else error_rez('Ошибка записи в базу даты проведения '.$tur.'-го тура турнира !!!');
	} else { // если была ошибка ввода данных формы в programka.php
	  	if ($status == 'new'){
	  echo '<img src="../pics/warning.png" style="vertical-align: middle" align="left">&nbsp;
			<b>'.$error.'</b>
			<form><br>
				<button type="button" onclick="window.location=\'programka.php\';">
					<img src="../pics/undo.png" style="vertical-align: middle">&nbsp;Вернуться назад
				</button>
			</form>';
		} elseif ($status == 'edit'){
			error_rez($error);
		  }
	  }
} else error_rez('Ошибка передачи данных. Информация не записана!');
?>