<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<?php
$db = "fotbprog_soccer";
$host = "db20.freehost.com.ua";
$user = "fotbprog_soccer";
$psw = "Cbkmdfyf53";
$conn = new mysqli($host, $user, $psw, $db);

if ($conn -> connect_errno) {
    die("Ошибка соединения с базой данных: ". $conn -> connect_errno." - ".$conn -> connect_error);
}
$conn -> set_charset("utf8");
?>
