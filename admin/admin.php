<?php
session_start();
require_once('../blocks/connect.php');
require_once('functions.php');
if (!isset($_SESSION['user_id']) || !isset($_SESSION['login']) || !isset($_SESSION['rights']) ||
	($_SESSION['rights'] <> 2 && $_SESSION['rights'] <> 1)){
	//header('Location:../index.php');
	echo '<script type="text/javascript">';
	echo 'window.location.href="../index.php";';
	echo '</script>';
	exit();
}
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