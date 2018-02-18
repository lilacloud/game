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
$file		- путь к файлу с картинкой во временном каталоге (после передачи через POST)
$otvet		- ответ для кода безопасности для записи в базу
$picsdir	- имя папки с картинками
$file_name	- имя файла с картинкой
$full_path	- полный путь к файлу с картинкой
$path_for_save - путь к файлу с картинкой для записи б базу (без ../)
$del_file	- путь к картинке во временном каталоге для удаления
$del_name	- имя удаляемого файла с папки с картинками
$pictmp		- путь к временной папке
$edit_id	- id картинки кода безопасности для редактирования
$edit_otvet	- ответ набранный пользователем для редактирования
$save_img	- путь к картинке во временном каталоге, если при редактировании
			  была изменена старая картинка и её нужно записать в базу
$message_er	- сообщение об ошибке
$message_ok	- сообщение об успешном завершении опарации
$code_pics	- массив со всеми картинками безопасности и данными к ним
$tmp_img_file - путь к файлу с картинкой во временном каталоге (до передачи через POST)
$id			- id картинки с базы
$img		- путь к картинке с базы
$sum		- ответ к коду безопасности с базы
*/
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="description" content="">
<meta name="keywords" content="">
<title>Футбольный прогноз | Административный блок</title>
<link href="../styles.css" rel="stylesheet" type="text/css">
<script>
function del_kod(){
  var otvet = confirm("Вы действительно хотите удалить этот код безопасности\n\(не должно быть пропусков номеров картинок кода безопасности\)?");
  return otvet;
}
function chk_save_edit(){
var flag = true;
  if (document.prew.save_otvet.value==""){
		flag = false;
		alert("Не заполнено поле \'Ответ\' !!!");
  }
return flag;
}
function chk_edit(){
var flag = true;
  if (document.save.prew_img_file.value==""){
		flag = false;
		alert("Не заполнено поле загрузки файла !!!");
  }
return flag;
}
function chk_save(){
var flag = true;
  if (document.save.save_otvet.value==""){
		flag = false;
		alert("Не заполнено поле \'Ответ\' !!!");
  }
return flag;
}
function chk_new(){
var flag = true;
  if (document.new.img_file.value==""){
		flag = false;
		alert("Не заполнено поле загрузки файла !!!");
  }
return flag;
}
function chk_new2(){
var flag = true;
  if (document.new2.otvet.value==""){
		flag = false;
		alert("Не заполнено поле ответа !!!");
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
<h2><b>Настройки сайта</b></h2>
<h3><b>Работа с картинками кода безопасности:</b></h3>
<table cellspacing="0" border="1" bordercolor="#75A54B">
<tr style="background-color:#A8BF89;">
<th class="ver_line">№</th>
<th class="ver_line">Картинка</th>
<th class="ver_line">Ответ</th>
<th class="ver_line">Путь</th>
<th class="ver_line" colspan="2">Действия</th>
</tr>
<?php
//echo '<pre>';
//print_r($_POST);
//echo '</pre>';
//сохранение картинки кода безопасности в базе после редактирования
if (isset($_POST['save_code']) && $_POST['save_code'] == 'ok'){  $edit_id = $conn -> real_escape_string(htmlspecialchars(trim($_POST['save_id'])));
  if (isset($_POST['save_otvet']) && $_POST['save_otvet'] <> ''){  	//ответ набранный пользователем
  	$edit_otvet = $conn -> real_escape_string(htmlspecialchars(trim($_POST['save_otvet'])));
  	if (isset($_POST['save_img'])){//была ли выбрана другая картинка для записи
  	  $file = $conn -> real_escape_string(htmlspecialchars(trim($_POST['save_img'])));      $picsdir = '../pics/kod/'; //имя папки с картинками
      $file_name = end(explode('/', $file));//имя нашего файла с картинкой
      $full_path = $picsdir.$file_name;//полный путь к файлу с картинкой
      $path_for_save = substr($full_path, 3);//путь к файлу с картинкой (без ../)
	//проверяем нет ли уже такой картинки в базе
	  $sql = 'SELECT img FROM kod_bezop';
	  if ($stmt = $conn -> prepare($sql)){
	  	if ($stmt -> execute()){
	  	  $res = $stmt -> get_result();
	  	  $stmt -> close();
		  while ($row = $res -> fetch_assoc()){
			if ($row['img'] == $path_for_save){
			  $message_er = 'Такая картинка уже есть в базе, её добавить невозможно !';
			}
		  }
		} else error_rez('Запрос на выборку имеющихся уже в базе картинок кода безопасности не выполнен ! ('.$conn -> error.')');
	  } else error_rez('Запрос на выборку имеющихся уже в базе картинок кода безопасности не выполнен ! '.$conn -> error.')');
  	  //перемещаем файл с временного каталога в каталог на сервере
  	  if (!rename($file, $full_path)){
		$message_er = 'Не удалось загрузить картинку на сервер !';
      }
	}
	if (!isset($message_er) || !isset($_POST['save_img'])){	//если нет ошибок или не была выбрана другая картинка для записи в базу, то идём дальше
 	  $first = 'UPDATE kod_bezop SET ';
      if (isset($_POST['save_img'])){      	$second = 'img=?, ';
      } else {      	$second = '';//если картинка не выбрана, то её не пишем в базу
      }
      $third = 'sum=? WHERE id=?';
 	  $sql = $first.$second.$third;
      if ($stmt = $conn -> prepare($sql)){
        if (isset($_POST['save_img'])){          $stmt -> bind_param('sii', $path_for_save, $edit_otvet, $edit_id);
        } else {
          $stmt -> bind_param('ii', $edit_otvet, $edit_id);
        }
        if ($stmt -> execute()){
          $message_ok = 'Редактирование картинки кода безопасности выполнено успешно !';
	      $stmt -> close();
        } else	$message_er = 'Не удалось отредактировать картинку безопасности ! ('.$conn->error.')';
      } else $message_er = 'Не удалось отредактировать картинку безопасности ! ('.$conn->error.')';
    }
  } else {
  	$message_er = 'Вы не ввели ответ! Начните редактирование заново!';
    if (isset($_POST['save_img'])){
      $del_file = $conn -> real_escape_string(htmlspecialchars(trim($_POST['save_img'])));
      unlink($del_file); //удаляем файл с картинкой c временного каталога
    }
  }
}
// удаление картинки кода безопасности с базы
if (isset($_POST['drop_code']) && $_POST['drop_code'] == 'ok'){
  $del_id = $conn -> real_escape_string(htmlspecialchars(trim($_POST['drop_id'])));
  $del_name = $conn -> real_escape_string(htmlspecialchars(trim($_POST['drop_name'])));
  $sql = 'UPDATE kod_bezop
		  SET img=NULL, sum=NULL
		  WHERE id=?';
  if ($stmt = $conn -> prepare($sql)){
	$stmt -> bind_param('i', $del_id);
	if ($stmt -> execute()){
    	$message_ok = 'Картинка кода безопасности удалена успешно !';
 	} else $message_er = 'Не удалось удалить картинку кода безопасности ! ('.$conn -> error.')';
  } else $message_er = 'Не удалось удалить картинку кода безопасности ! ('.$conn -> error.')';
  $del_name = '../'.$del_name;//имя удаляемого файла с папки с картинками
  unlink($del_name); //удаляем файл с картинкой c каталога c картинками
}
//удалить картинку с временного каталога при добавлении новой картинки
if (isset($_POST['del_img']) && $_POST['del_img'] == 'ok'){  $del_file = $conn -> real_escape_string(htmlspecialchars(trim($_POST['del_file'])));
  unlink($del_file); //удаляем файл с картинкой c временного каталога
}
//добавить новую картинку кода безопасности
if (isset($_POST['add_code']) && $_POST['add_code'] == 'ok'){  //путь к файлу с картинкой во временном каталоге
  $file = $conn -> real_escape_string(htmlspecialchars(trim($_POST['file'])));
  if (isset($_POST['otvet']) && $_POST['otvet'] <> ''){  	$otvet = $conn -> real_escape_string(htmlspecialchars(trim($_POST['otvet'])));
    $picsdir = '../pics/kod/'; //имя папки с картинками
    $file_name = end(explode('/', $file));//имя нашего файла с картинкой
    $full_path = $picsdir.$file_name;//полный путь к файлу с картинкой
    $path_for_save = substr($full_path, 3);//путь к файлу с картинкой (без ../)
  // копирование файла в дирекорию на сервере прошло успешно?
	if (!rename($file, $full_path)){//перемещаем файл с временного каталога в каталог на сервере
		$message_er = 'Не удалось загрузить картинку на сервер!';
	} else { //когда всё хорошо с картинкой, можно идти дальше
	  $sql = 'SELECT img FROM kod_bezop';
	  //проверяем нет ли уже такой картинки в базе
	  if ($stmt = $conn -> prepare($sql)){	  	if ($stmt -> execute()){	  	  $res = $stmt -> get_result();
	  	  $stmt -> close();
		  while ($row = $res -> fetch_assoc()){
			if ($row['img'] == $path_for_save){
			  $message_er = 'Такая картинка уже есть в базе, её добавить невозможно!';
			  unlink($full_path);//удаляем файл с картинкой c каталога на сервере
			}
		  }
		  if (!isset($message_er)){//если нет ошибок, то записываем в базу
           	$sql = 'INSERT INTO kod_bezop (img, sum)
						   VALUES (?, ?)';
          	if ($stmt = $conn -> prepare($sql)){
          	    $stmt -> bind_param('si', $path_for_save, $otvet);
          	  if ($stmt -> execute()){
          	    $message_ok = 'Новая картинка кода безопасности добавлена успешно !';
	    		$stmt -> close();
          	  } else	$message_er = 'Не удалось добавить новую картинку безопасности ! ('.$conn->error.')';
          	} else $message_er = 'Не удалось добавить новую картинку безопасности ! ('.$conn->error.')';
          }	  	} else error_rez('Запрос на выборку имеющихся уже в базе картинок кода безопасности не выполнен ! ('.$conn -> error.')');
	  }	else error_rez('Запрос на выборку имеющихся уже в базе картинок кода безопасности не выполнен ! '.$conn -> error.')');
	}
  } else {  	$message_er = 'Вы не ввели ответ!';
  }
}
// вывод списка картинок кода безопасности
$sql = 'SELECT id, img, sum
		FROM kod_bezop
		ORDER BY id';
if ($stmt = $conn -> prepare($sql)){
  if ($stmt -> execute()){
  	$res = $stmt -> get_result();
  	$stmt -> close();
  }	else error_rez('Запрос на выборку картинок кодов безопасности не выполнен! '.$conn -> error);
} else error_rez('Запрос на выборку картинок кодов безопасности не выполнен! '.$conn -> error);
//если в базе есть картинки кода безопасности
if ($res -> num_rows > 0){  while ($row = $res -> fetch_assoc()){  	$code_pics[] = $row;//массив со всеми картинками безопасности и данными к ним
  }  foreach($code_pics as $pole){  	  $id = $pole['id'];
  	  $img = $pole['img'];
  	  $sum = $pole['sum'];
echo'<tr><td>&nbsp;'.$id.'&nbsp;</td>';
    //если нажата кнопка редактирования
    if (isset($_POST['edit_code']) && $_POST['edit_code'] == 'ok' &&
    	$id == $_POST['edit_id']){      //если была нажата кнопка "Назад", то удаляем файл во временном каталоге
      if (isset($_POST['del_tmp'])){      	$del_file = $conn -> real_escape_string(htmlspecialchars(trim($_POST['del_tmp'])));      	unlink($del_file); //удаляем файл с картинкой c временного каталога
      } echo'<td><center><img src="../';
	  if ($img == ''){//если картинки нет, то рисуем иконку
	  	echo'pics/pic_no.png';
	  } else {
	  	echo $img;
	  }
 echo'"></center></td>
   	  <form method="post" action="settings.php" name="prew" enctype="multipart/form-data">
	  <td><input type="text" name="save_otvet" size="5" placeholder="Ответ" ';
	  if ($img <> ''){//если картинка есть, то пишем и ответ на неё
   	  echo 'value="'.$sum.'"';
	  } else {	  echo 'disabled';
	  }
    echo '></td>';
echo'<td colspan="2">';
	  if ($img <> ''){//если что-то есть, то пишем это
	  	echo'&nbsp;'.$img.'<br>';
	  }
echo '<input type="file" name="prew_img_file"
	 	accept="image/jpeg,image/jpg,image/gif,image/png,image/ico"></td>
	  <td nowrap>
	  <input type="hidden" name="prew_id" value="'.$id.'">
	  <button type="submit" name="prew_code" value="ok">
		<img src="../pics/1294.png" style="vertical-align: middle">Загрузить
	  </button>';
	  if ($img <> '' && $sum <>''){      //если есть и картинка и ответ, то рисуем кнопку сохранить
  echo '<br><input type="hidden" name="save_id" value="'.$id.'">
  		<button type="submit" name="save_code" value="ok" onclick="return chk_save_edit();">
		<img src="../pics/save.png" style="vertical-align: middle">
		  &nbsp;Сохранить
	  	</button>';
	  }
echo'</form></td>';
    //если нажата кнопка предварительного просмотра картинки
    } elseif (isset($_POST['prew_code']) && $_POST['prew_code'] == 'ok' &&
    	$id == $_POST['prew_id']){      if(empty($_FILES['prew_img_file']['size'])){
		$message_er = 'Вы не выбрали картинку !';
  	  } elseif($_FILES['prew_img_file']['size'] > (512 * 512)){
		$message_er = 'Размер файла не может превышать 256Кб !';
  	  } else {
    	$imageinfo = getimagesize($_FILES['prew_img_file']['tmp_name']);
		$arr = array('image/jpeg','image/ico','image/gif','image/png');
		if(!in_array($imageinfo['mime'], $arr)){
	  	  $message_er = 'Картинка должна быть формата JPG, ICO, GIF или PNG !';
		} else {
	  	  $pictmp = '../tmp/'; //путь к временной папке
	  	  // копирование файла во временную дирекорию прошло успешно?
	  	  if (!is_uploaded_file($_FILES['prew_img_file']['tmp_name'])){
			$message_er = 'Не удалось загрузить картинку !';
	  	  } elseif (!move_uploaded_file($_FILES['prew_img_file']['tmp_name'],
				$pictmp.$_FILES['prew_img_file']['name'])){
			$message_er = 'Не удалось загрузить картинку для просмотра !';
	  	  }
		}
      }
	  //если была нажата кнопка загрузки просмотра картинки и картинка выбрана,
	  if (isset($pictmp) && isset($_FILES['prew_img_file']['name'])){	  	$file = $pictmp.$_FILES['prew_img_file']['name'];//то выводим её
	  } elseif ($img == ''){//если была нажата кнопка и нет картинки	  	$file = '../pics/pic_no.png';
	  } else {//иначе, если картинка не была выбрана, а перед этим она была	  	$file = '../'.$img;//то выводим предыдущую картинку
	  }
	echo'<td><center><img src="'.$file.'"></center></td>
   	  	 <form method="post" action="settings.php" name="save" enctype="multipart/form-data">
   	  	 <td><input type="text" name="save_otvet" size="5" placeholder="Ответ" ';
	  if ($sum <> ''){//если что-то есть, то пишем это
	  	echo 'value="'.$sum.'"';
	  } elseif (isset($message_er) && $message_er == 'Вы не выбрали картинку !'){	  	echo 'disabled';
	  }
 	echo'></td>
   	  	 <td colspan="0">';
	  if ($img <> ''){//если что-то есть, то пишем это
	//если не была нажата кнопка загрузки просмотра картинки и когда картинка была выбрана,
	    if (!isset($pictmp) && !isset($_FILES['prew_img_file']['name'])){
	  	echo'&nbsp;'.$img;//то мы пишем предыдущий путь
	  	}//иначе не пишем
	  }
	  if (isset($message_er) && $message_er == 'Вы не выбрали картинку !'){	  	//если не был выбран файл картинки	  echo'<br><input type="file" name="prew_img_file"
	 	accept="image/jpeg,image/jpg,image/gif,image/png,image/ico">
	 	<input type="hidden" name="prew_id" value="'.$id.'">';
	  } else {/*если картинка была выбрана, но, возможно, её нужно изменить,
	  			то ставим кнопку Назад*/
	echo'</td><td><form action="settings.php">
	  	<input type="hidden" name="edit_id" value="'.$id.'">
	  	<input type="hidden" name="del_tmp" value="'.$file.'"><center>
	  	<button type=submit name="edit_code" value="ok">
	  	<img src="../pics/undo.png" style="vertical-align: middle">
	  	  &nbsp;Назад
		</button></center>';
	  }
	echo'</td><td nowrap>
	  	 <input type="hidden" name="save_id" value="'.$id.'">';
	  if (isset($message_er) && $message_er == 'Вы не выбрали картинку !'){	  	//если не был выбран файл картинки	echo'<button type="submit" name="prew_code" value="ok" onclick="return chk_edit();">
		 <img src="../pics/1294.png" style="vertical-align: middle">Загрузить
	  	 </button>';
	  } else {
	echo'<input type="hidden" name="save_img" value="'.$file.'">
	 	<button type="submit" name="save_code" value="ok" onclick="return chk_save();">
		<img src="../pics/save.png" style="vertical-align: middle">
			&nbsp;Сохранить
	  	</button>';
      }
     echo '</td></form>';
    } else {//если кнопка редактирования не нажата
echo '<td><center><img src="../';
	  if ($img == ''){//если картинки нет, то рисуем иконку	  	echo'pics/pic_no.png';
	  } else {	  	echo $img;
	  }
echo '"></center></td>
   	  <td><center>'.$sum.'</center></td>
   	  <td>&nbsp;'.$img.'&nbsp;</td>
	  <td nowrap><form method="post" action="settings.php">
	  <input type="hidden" name="edit_id" value="'.$id.'">
	  <button type="submit" name="edit_code" value="ok">
		<img src="../pics/edit.png" style="vertical-align: middle">
		&nbsp;Изменить
	  </button>
	  </form></td>
	  <td><form method="post" action="settings.php" onsubmit="return del_kod();">
	  <input type="hidden" name="drop_id" value="'.$id.'">
	  <input type="hidden" name="drop_name" value="'.$img.'">
	  <button ';
	  if ($img == '' && $sum == ''){//если это пропуск картинки
	  echo 'disabled ';//то блокируем кнопку
	  }
  echo 'type="submit" name="drop_code" value="ok">
		<img src="../pics/drop.png" style="vertical-align: middle">
		&nbsp;Удалить
	  </button></form></td>';
	}
  echo'</tr>';
  }
} else mes_inf('База данных картинок кода безопасности пуста !');
echo'</table><br><small><b>Нельзя допускать пропусков номеров картинок кода безопасности !
	</b></small><br><br><br>
<!-- ------- Добавление новой картинки кода безопасности ------- -->
<table><tr><td><b>Добавить новую картинку:</b></td></tr>
<form method="post" action="settings.php" name="new" enctype="multipart/form-data"
	  onsubmit="return chk_new();">
<tr><td><input type="file" name="img_file"
	 accept="image/jpeg,image/jpg,image/gif,image/png,image/ico"></td>
<td><button type="submit" name="load_code" value="ok">
	<img src="../pics/1294.png" style="vertical-align: middle">&nbsp;Загрузить
</button></td></form></tr>';
//если была нажата кнопка загрузки новой картинки
if(isset($_POST['load_code']) && $_POST['load_code'] == 'ok'){  if(empty($_FILES['img_file']['size'])){
	$message_er = 'Вы не выбрали картинку !';
  } elseif($_FILES['img_file']['size'] > (512 * 512)){
	$message_er = 'Размер файла не может превышать 256Кб !';
  } else {
    $imageinfo = getimagesize($_FILES['img_file']['tmp_name']);
	$arr = array('image/jpeg','image/ico','image/gif','image/png');
	if(!in_array($imageinfo['mime'],$arr)){
	  $message_er = 'Картинка должна быть формата JPG, ICO, GIF или PNG !';
	} else {
	  $pictmp = '../tmp/'; //путь к временной папке
	  // копирование файла во временную дирекорию прошло успешно?
	  if (!is_uploaded_file($_FILES['img_file']['tmp_name'])){
		$message_er = 'Не удалось загрузить картинку!';
	  } elseif (!move_uploaded_file($_FILES['img_file']['tmp_name'],
				$pictmp.$_FILES['img_file']['name'])){
		$message_er = 'Не удалось загрузить картинку для просмотра!';
	  }
	}
  }
}
 /*если нажата кнопка предварительного просмотра картинки и не было ошибки
    или была нажата кнопка загрузки картинки на сервер и была ошибка*/
if ((isset($_POST['load_code']) && $_POST['load_code'] == 'ok' && !isset($message_er)) ||
  	(isset($_POST['add_code']) && $_POST['add_code'] == 'ok' && isset($message_er))){  	//на случай, когда была нажата кнопка предварительного просмотра картинки
  if (isset($pictmp) && isset($_FILES['img_file']['name'])){
  	  $file = $pictmp.$_FILES['img_file']['name'];
  }echo'<form method="post" action="settings.php" name="new2" onsubmit="return chk_new2();">
  <tr><td colspan="3"><br><b>Укажите ответ для кода безопасности:</b></td></tr>
  <tr><td><img src="'.$file.'" style="vertical-align: middle">
  <input type="hidden" name="file" value="'.$file.'">
  <input type="text" name="otvet" size="5" placeholder="Ответ"></td>
  <td><button type="submit" name="add_code" value="ok">
	<img src="../pics/add.png" style="vertical-align: middle">&nbsp;Добавить
	</button></td>
  </form>';
/* если нажата кнопка предварительного просмотра картинки и не было ошибки
    или была нажата кнопка загрузки картинки на сервер и была ошибка,
    то ставим кнопку "Удалить" */
if (isset($pictmp)){//если была нажата кнопка предварительного просмотра ("Загрузить")
  $tmp_img_file = $pictmp.$_FILES['img_file']['name'];
} elseif (isset($file)){//если была нажата кнопка загрузки картинки на сервер ("Добавить")
  $tmp_img_file = $file;
}
echo'<form method="post" action="settings.php"><td>
	  <input type="hidden" name="del_file"
		 value="'.$tmp_img_file.'">
	  <button type="submit" name="del_img" value="ok">
	    <img src="../pics/drop.png" style="vertical-align: middle">&nbsp;Удалить
	  </button></td></form></tr>';
}

?>
</table><br>
<?php
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
<?php require_once("../blocks/footer.php"); ?>
</tr>
</table>
</body>
</html>