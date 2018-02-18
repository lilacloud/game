<?php
session_start();
require_once('functions.php');
if (isset($_GET['action']) && $_GET['action'] == 'logout' &&
	isset($_SESSION['user_id'])){	require_once('blocks/connect.php');	// внесение даты последнего визита
	$id = $conn -> real_escape_string(htmlspecialchars($_SESSION['user_id']));
	$sql = 'UPDATE users
			SET last_visit=NOW() WHERE id=?';
	$stmt = $conn -> prepare($sql);
	$stmt -> bind_param('i', $id);
	if ($stmt -> execute()){
	  session_unset();
	  session_destroy();
	  //header('Location:index.php');
	  echo '<script type="text/javascript">';
	  echo 'window.location.href="index.php";';
	  echo '</script>';
	  exit();
	} else error_rez('Ошибка при обращении к базе данных!');
}

?>