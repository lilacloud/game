<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['login']) || !isset($_SESSION['rights']) ||
	$_SESSION['rights'] <> 1){
	//header('Location:../index.php');
	echo '<script type="text/javascript">';
	echo 'window.location.href="../index.php";';
	echo '</script>';
	exit();
}
require_once('../blocks/connect.php');
require_once('functions.php');
/*
Переданное через POST
chemp		- название нового чемпионата
img_file	- путь к картинке
add_chemp	- кнопка добавления нового чемпионата (если = 'ok', то данные переданы)
drop_chemp	- кнопка удаление чемпионата (= id чемпионата, который нужно удалить)
edit_chemp	- кнопка редактирования чемпионата (= id чемпионата, который нужно редактировать)
save_chemp	- кнопка сохранения изменений чемпионата (= id чемпионата,
			  изменённые данные которого нужно сохранить)
ed_chemp	- изменённое название чемптоната
ed_img_file - изменённая картинка чемпионата
---
$message_ok - сообщение об успешном выполнении операции
$message_er - сообщение об ошибке
$chemp		- название нового чемпионата
$imageinfo	- информация о типе киртинки
$arr		- массив с "белым списком" типов картинок
$picsdir	- папка куда будут записываться картинки
$fullpath	- полный путь к картинке
*/
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="description" content="">
<meta name="keywords" content="">
<title>Футбольный прогноз | Административный блок</title>
<!--<base href="<?php echo $_SERVER['SERVER_NAME'].'/soccer';?>-->

<link href="../styles.css" rel="stylesheet" type="text/css">
<script>
function del_chemp(){
  var otvet = confirm('Вы действительно хотите удалить этот чемпионат?');
  return otvet;
}
function chk_new(){
var flag = true;
  if (document.new.chemp.value=="" ||
	document.new.img_file.value==""){
		flag = false;
		alert("Не все поля заполнены !!!");
  }
return flag;
}
function chk_edit(){
var flag = true;
  if (document.edit.ed_chemp.value=="" ||
	document.edit.ed_img_file.value==""){
		flag = false;
		alert("Не все поля заполнены !!!");
  }
return flag;
}
</script>
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
<h2><b>Редактирование чемпионатов и команд:</b></h2>
<h3><b>Список чемпионатов:</b></h3>
<table>
<?php
// добавление чемпионата
if (isset($_POST['add_chemp']) && $_POST['add_chemp'] == 'ok'){
    $chemp = $conn -> real_escape_string(htmlspecialchars(trim($_POST['chemp'])));
	if(empty($_FILES['img_file']['size']))
	   $message_er = 'Вы не выбрали флаг!';
	elseif($_FILES['img_file']['size'] > (512 * 1024))
	   $message_er = 'Размер файла не должен превышать 500Кб!';
    else {
       $imageinfo = getimagesize($_FILES['img_file']['tmp_name']);
	   $arr = array('image/jpeg','image/ico','image/gif','image/png');
	   if(!in_array($imageinfo['mime'],$arr))
		  $message_er = 'Картинка должна быть формата JPG, ICO, GIF или PNG';
	   else {
		  $picsdir = '../pics/flags/'; //имя папки с картинками
		  // копирование файла во временную дирекорию прошло успешно?
		  if (!is_uploaded_file($_FILES['img_file']['tmp_name']))
			$message_er = 'Не удалось загрузить картинку!';
		  elseif (!move_uploaded_file($_FILES['img_file']['tmp_name'],
				$picsdir.$_FILES['img_file']['name']))
			$message_er = 'Не удалось загрузить картинку на сервер!';
		  else { // когда всё хорошо с картинкой, можно идти дальше
		    $res = $conn -> query('SELECT turnir_n FROM sl_turnir_r');
			while ($row = $res -> fetch_assoc()){
			  if ($row['turnir_n'] == $chemp)
				$message_er = 'Такой чемпионат уже есть, его добавить невозможно!';
			}
          	if (!isset($message_er)){ // если нет ошибок, то записываем в базу
          	   $fullpath = substr($picsdir, 2).$_FILES['img_file']['name'];
           	   $sql = 'INSERT INTO sl_turnir_r (turnir_n, flag)
						   	  VALUES (?, ?)';
          	   if ($stmt = $conn -> prepare($sql)){
          	     $stmt -> bind_param('ss', $chemp, $fullpath);
          	     if ($stmt -> execute()){
          	    	$message_ok = 'Новый чемпионат добавлен успешно!';
	    			$stmt -> close();
          	     } else	$message_er = 'Не удалось добавить новый чемпионат!';
          	   } else $message_er = 'Не удалось добавить новый чемпионат! ('.$conn->error.')';
          	}
		  }
	   }
    }
}
// редактирование чемпионата
if (isset($_POST['save_chemp']) && !empty($_POST['save_chemp'])){    $chemp = $conn -> real_escape_string(htmlspecialchars(trim($_POST['ed_chemp'])));
	if(empty($_FILES['ed_img_file']['size']))
	   $message_er = 'Вы не выбрали флаг!';
	elseif($_FILES['ed_img_file']['size'] > (512 * 1024))
	   $message_er = 'Размер файла не должен превышать 500Кб!';
    else {
       $imageinfo = getimagesize($_FILES['ed_img_file']['tmp_name']);
	   $arr = array('image/jpeg','image/ico','image/gif','image/png');
	   if(!in_array($imageinfo['mime'],$arr))
		  $message_er = 'Картинка должна быть формата JPG, ICO, GIF или PNG';
	   else {
		  $picsdir = '../pics/flags/'; //имя папки с картинками
		  // копирование файла во временную дирекорию прошло успешно?
		  if (!is_uploaded_file($_FILES['ed_img_file']['tmp_name']))
			$message_er = 'Не удалось загрузить картинку!';
		  elseif (!move_uploaded_file($_FILES['ed_img_file']['tmp_name'],
				$picsdir.$_FILES['ed_img_file']['name']))
			$message_er = 'Не удалось загрузить картинку на сервер!';
		  else { // когда всё хорошо с картинкой, можно идти дальше
          	if (!isset($message_er)){ // если нет ошибок, то записываем в базу
          	   $fullpath = substr($picsdir, 2).$_FILES['ed_img_file']['name'];
          	   $sql = 'UPDATE sl_turnir_r
          	   		   SET turnir_n=?, flag=?
					   WHERE id=?';
          	   if ($stmt = $conn -> prepare($sql)){
          	     $stmt -> bind_param('ssi', $chemp, $fullpath, $_POST['save_chemp']);
          	     if ($stmt -> execute()){
          	    	$message_ok = 'Чемпионат изменён успешно!';
	    			$stmt -> close();
          	     } else	$message_er = 'Не удалось изменить чемпионат! ('.$conn->error.')';
          	   } else $message_er = 'Не удалось изменить чемпионат! ('.$conn->error.')';
          	}
		  }
	   }
    }
}
// удаление чемпионата
if (isset($_POST['drop_chemp']) && !empty($_POST['drop_chemp'])){	$sql = 'DELETE sl_turnir_r, sl_com
			FROM sl_turnir_r
			LEFT JOIN sl_com
			ON sl_turnir_r.id = sl_com.id_turnir_n
			WHERE sl_turnir_r.id = ?';
	if ($stmt = $conn -> prepare($sql)){
	  $stmt -> bind_param('i', $_POST['drop_chemp']);
	  if ($stmt -> execute()){
    	  $message_ok = 'Чемпионат удалён успешно!';
      } else $message_er = 'Не удалось удалить чемпионат!';
    } else $message_er = 'Не удалось удалить чемпионат!';
}
// вывод списка чемпионатов на страницу
$res = $conn -> query('SELECT * FROM sl_turnir_r ORDER BY turnir_n');
if ($res -> num_rows > 0){
   while ($row = $res -> fetch_assoc()){echo '<tr>';
	  if (isset($_POST['edit_chemp']) && $row['id'] == $_POST['edit_chemp']){	  echo '<form method="post" action="chemps.php" name="edit"
	  			enctype="multipart/form-data" onsubmit="return chk_edit();">
	  		<td colspan="5">
	  		<input name="ed_chemp" type="text" size="25" maxlenth="50"
	  			value="'.$row['turnir_n'].'">
	  		<img src="'.'..'.$row['flag'].'">
	  		<input type="file" name="ed_img_file"
	 			accept="image/jpeg,image/jpg,image/gif,image/png,image/ico">
	  		<button type="submit" name="save_chemp" value="'.$row['id'].'">
				<img src="../pics/save.png" style="vertical-align: middle">&nbsp;Сохранить
			</button>
			</td></form>';
	  } else {echo '<td>'.$row['turnir_n'].'</td><td><center><img src="'.'..'.$row['flag'].'"></center></td>
   	  <td><form method="post" action="komands.php">&nbsp;
		<button type="submit" name="edit_kom" value="'.$row['id'].'">
		<img src="../pics/edit_pr.png" style="vertical-align: middle">
			&nbsp;Команды
		</button>
	  </form></td>
	  <td><form method="post" action="chemps.php">
		<button type="submit" name="edit_chemp" value="'.$row['id'].'">
			<img src="../pics/edit.png" style="vertical-align: middle">
			&nbsp;Изменить
		</button>
	   </form></td>
	  <td><form method="post" action="chemps.php" onsubmit="return del_chemp();">
		<button type="submit" name="drop_chemp" value="'.$row['id'].'">
			<img src="../pics/drop.png" style="vertical-align: middle">
			&nbsp;Удалить
		</button>
	   </form></td>
	  </tr>';
	  }
   }
} else mes_inf('База данных чемпионатов пуста!');
?>
</table><br>
<table>
<tr>
<td><b>Добавить новый чемпионат:</b></td>
</tr>
<tr>
<th>Название</th><th>Флаг</th>
</tr>
<tr>
<form method="post" action="chemps.php" name="new" enctype="multipart/form-data"
	  onsubmit="return chk_new();">
<td><input name="chemp" type="text" size="30" maxlenth="50"
<?php
if (isset($_POST['add_chemp']) && $_POST['add_chemp'] == 'ok' && isset($message_er))
	echo 'value="'.$_POST['chemp'].'"';
?>
></td>
<td><input type="file" name="img_file"
	 accept="image/jpeg,image/jpg,image/gif,image/png,image/ico"></td>
<td><button type="submit" name="add_chemp" value="ok">
	<img src="../pics/add.png" style="vertical-align: middle">&nbsp;Добавить
	</button></td>
</form>
</tr>
</table>
<?php
isset($message_ok) ? mes_ok($message_ok) : false;
isset($message_er) ? mes_er($message_er) : false;
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