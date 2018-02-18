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
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="description" content="">
<meta name="keywords" content="">
<title>Футбольный прогноз | Административный блок</title>
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
//если была нажата кнопка
if (isset($_POST['lock_button']) && $_POST['lock_button']=='ok'){
$turnir = $conn -> real_escape_string(htmlspecialchars(trim($_POST['lock_chemp'])));
$tour = $conn -> real_escape_string(htmlspecialchars(trim($_POST['lock_tour'])));
/*отбераем даты проведения реальных матчей и проверяем не осталось ли в каком-то
  времени меньше, чем 30 мин. до его начала*/
  $sql = 'SELECT id, r_date
		  FROM r_igra
		  WHERE turnir_id = ? AND tur_nom = ?';
  if (!$stmt = $conn -> prepare($sql)){
  	error_rez('Запрос на выборку даты проведения матчей не выполнен ! '.$conn -> error);
  }	else {
    $stmt -> bind_param('ii', $turnir, $tour);
    if (!$stmt -> execute()){
      error_rez('Запрос на выборку даты проведения матчей не выполнен ! '.$conn -> error);
    } else {
  	  $res = $stmt -> get_result();
  	  $stmt -> close();
  	  $date_now = time();
  	  while ($row = $res -> fetch_row()){
  	  //если мы нашли реальный матч, где осталось 30 мин. и меньше до его начала,
  	    if ($date_now >= strtotime($row[1]) - 1800){
		  //то ставим этому матчу активность '2'
		  $sql = 'UPDATE r_igra
				  SET r_active = "2"
				  WHERE id = ?';
		  if ($stmt = $conn -> prepare($sql)){
			$stmt -> bind_param('i', $row[0]);
			if ($stmt -> execute()){
			  $stmt -> close();
			} else error_rez('Ошибка изменения активности матча ! '.$conn -> error);
		  } else error_rez('Ошибка изменения активности матча ! '.$conn -> error);
		  //и каждому несделанному прогнозу на этот матч ставим '0'
		  $sql = 'UPDATE prognozi
				  SET prognoz = "0"
				  WHERE r_igra_id = ? AND prognoz IS NULL';
		  if ($stmt = $conn -> prepare($sql)){
			$stmt -> bind_param('i', $row[0]);
			if ($stmt -> execute()){
			  $stmt -> close();
			} else error_rez('Ошибка изменения несделанных прогнозов ! '.$conn -> error);
		  } else error_rez('Ошибка изменения несделанных прогнозов ! '.$conn -> error);
  	    }
  	  }
    }
  }
header('Location:programka.php?tur='.$tour.'&chemp='.$turnir.'');
} else error_rez('Ошибка передачи данных. Выполнение скрипта остановлено !');
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