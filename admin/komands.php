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
/* переменные приходящие через POST
edit_kom - id чемпионата, с командами которого необходимо работать
			(приходит с chemps.php)
add_kom	 - кнопка добавления команды (= 'ok', если всё нормально)
edit_kom - id чемпионата с которым работаем
kom		 - название новой команды
img_file - имя файла с флагом новой команды
drop_kom - кнопка удаления команды (= id команды, которую нужно удалить)
ed_kom	 - кнопка редактирования команды (= id команды, которую нужно изменить)
save_kom - кнопка сохранения изменений команды (= id команды,
			изменённые данные которой нужно сохранить)
ed_name_kom - изменённое название команды
ed_img_file - изменённое имя файла с флагом команды
---
$kom	 - название команды, которую добавляем
$picsdir - имя папки, куда записывается картинка
$fullpath - путь к картинке, который записывается в базу
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
function del_kom(){
  var otvet = confirm('Вы действительно хотите удалить эту команду?');
  return otvet;
}
function chk_new(){
var flag = true;
  if (document.new.kom.value=="" ||
	document.new.img_file.value==""){
		flag = false;
		alert("Не все поля заполнены !!!");
  }
return flag;
}
function chk_edit(){
var flag = true;
  if (document.edit.ed_name_kom.value=="" ||
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
<?php // вывод названия чемпионата
$sql = 'SELECT * FROM sl_turnir_r WHERE id = ?';
if ($stmt = $conn -> prepare($sql)){
	$stmt -> bind_param('i', $_POST['edit_kom']);
 	if ($stmt -> execute()){
		$res = $stmt -> get_result();
		$row = $res -> fetch_assoc();
		echo '<h3><p>'.$row['turnir_n'].'&nbsp;
			  <img src="'.'../'.$row['flag'].'" style="vertical-align:middle;"></p></h3>';
		$stmt -> close();
	} else mes_er('Не удалось связаться с базой данных, неизвестно какой чемпионат!');
} else mes_er('Не удалось связаться с базой данных! ('.$conn -> error.')');
?>
<h3><b>Список команд:</b></h3>
<table>
<?php
//echo '<pre>';
//print_r($_POST);
//echo '</pre>';
// редактирование команды
if (isset($_POST['save_kom']) && !empty($_POST['save_kom'])){
    $kom = $conn -> real_escape_string(htmlspecialchars(trim($_POST['ed_name_kom'])));
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
          	   $sql = 'UPDATE sl_com
          	   		   SET com_n=?, flag=?
					   WHERE id=?';
          	   if ($stmt = $conn -> prepare($sql)){
          	     $stmt -> bind_param('ssi', $kom, $fullpath, $_POST['save_kom']);
          	     if ($stmt -> execute()){
          	    	$message_ok = 'Команда изменена успешно!';
	    			$stmt -> close();
          	     } else	$message_er = 'Не удалось изменить команду! ('.$conn->error.')';
          	   } else $message_er = 'Не удалось изменить команду! ('.$conn->error.')';
          	}
		  }
	   }
    }
}
// удаление команды
if (isset($_POST['drop_kom']) && !empty($_POST['drop_kom'])){
	$sql = 'DELETE FROM sl_com
			WHERE id = ?';
	if ($stmt = $conn -> prepare($sql)){
	  $stmt -> bind_param('i', $_POST['drop_kom']);
	  if ($stmt -> execute()){
    	  $message_ok = 'Команда удалена успешно!';
    	  $stmt -> close();
      } else $message_er = 'Не удалось удалить команду!';
    } else $message_er = 'Не удалось удалить команду!';
}
// добавление команды
if (isset($_POST['add_kom']) && $_POST['add_kom'] == 'ok'){
    $kom = $conn -> real_escape_string(htmlspecialchars(trim($_POST['kom'])));
	if(empty($_FILES['img_file']['size'])){
	   $message_er = 'Вы не выбрали флаг!';
	} elseif($_FILES['img_file']['size'] > (512 * 1024)){
	   $message_er = 'Размер файла не должен превышать 500Кб!';
    } else {
       $imageinfo = getimagesize($_FILES['img_file']['tmp_name']);
	   $arr = array('image/jpeg','image/ico','image/gif','image/png');
	   if(!in_array($imageinfo['mime'],$arr))
		  $message_er = 'Картинка должна быть формата JPG, ICO, GIF или PNG';
	   else {
		  $picsdir = '../pics/flags/'; //имя папки с картинками
		  // копирование файла во временную дирекорию прошло успешно?
		  if (!is_uploaded_file($_FILES['img_file']['tmp_name'])){
			$message_er = 'Не удалось загрузить картинку!';
		  } elseif (!move_uploaded_file($_FILES['img_file']['tmp_name'],
				$picsdir.$_FILES['img_file']['name'])){
			$message_er = 'Не удалось загрузить картинку на сервер!';
		  } else { // когда всё хорошо с картинкой, можно идти дальше
		    $res = $conn -> query('SELECT com_n FROM sl_com');
			while ($row = $res -> fetch_assoc()){
			  if ($row['com_n'] == $kom)
				$message_er = 'Такая команда уже есть, добавление невозможно!';
			}
          	if (!isset($message_er)){ // если нет ошибок, то записываем в базу
          	   $fullpath = substr($picsdir, 2).$_FILES['img_file']['name'];
          	   $sql = 'INSERT INTO sl_com (com_n, id_turnir_n, flag)
						   	  VALUES (?, ?, ?)';
          	   if ($stmt = $conn -> prepare($sql)){
          	     $stmt -> bind_param('sis', $kom, $_POST['edit_kom'], $fullpath);
          	     if ($stmt -> execute()){
          	    	$message_ok = 'Новая команда добавлена успешно!';
	    			$stmt -> close();
          	     } else	$message_er = 'Не удалось добавить новую команду!';
          	   } else $message_er = 'Не удалось добавить новую команду! ('.$conn->error.')';
          	}
		  }
	   }
    }
}
// вывод списка команд на страницу
$sql = 'SELECT * FROM sl_com
		WHERE id_turnir_n = ?
		ORDER BY com_n';
if ($stmt = $conn -> prepare($sql)){  $stmt -> bind_param('i', $_POST['edit_kom']);
  $stmt -> execute();
  $res = $stmt -> get_result();
  if ($res -> num_rows > 0){
     while ($row = $res -> fetch_assoc()){
echo '<tr>';
	   if (isset($_POST['ed_kom']) && $row['id'] == $_POST['ed_kom']){
	 echo'<form method="post" action="komands.php" name="edit"
	  		enctype="multipart/form-data" onsubmit="return chk_edit();">
	  	  <td colspan="4">
	  	  <input name="ed_name_kom" type="text" size="20" maxlenth="50"
	  		value="'.$row['com_n'].'">
	  	  <img src="'.'..'.$row['flag'].'">
	  	  <input type="file" name="ed_img_file"
	 		accept="image/jpeg,image/jpg,image/gif,image/png,image/ico">
	 	  <input type="hidden" name="edit_kom" value="'.$_POST['edit_kom'].'">
	  	  <button type="submit" name="save_kom" value="'.$row['id'].'">
			<img src="../pics/save.png" style="vertical-align: middle">&nbsp;Сохранить
		  </button>
		  </td></form>';
	   } else {
echo   '<td width="147">'.$row['com_n'].'</td><td><center><img src="'.'..'.$row['flag'].'"></center></td>
   	    <td><form method="post" action="komands.php">&nbsp;
   	    <input type="hidden" name="edit_kom" value="'.$_POST['edit_kom'].'">
		<button type="submit" name="ed_kom" value="'.$row['id'].'">
			<img src="../pics/edit.png" style="vertical-align: middle">
			&nbsp;Изменить
		</button>
	    </form></td>
	    <td width="210"><form method="post" action="komands.php" onsubmit="return del_kom();">
	    <input type="hidden" name="edit_kom" value="'.$_POST['edit_kom'].'">
		<button type="submit" name="drop_kom" value="'.$row['id'].'">
			<img src="../pics/drop.png" style="vertical-align: middle">
			&nbsp;Удалить
		</button>
	    </form></td>
	    </tr>';
       }
     }
  } else mes_inf('База данных команд пуста!');
} else mes_er('Ошибка вывода списка команд!');
?>
</table><br>
<table>
<tr>
<td><b>Добавить новую команду:</b></td>
</tr>
<tr>
<th>Название</th><th>Флаг</th>
</tr>
<tr>
<form method="post" action="komands.php" name="new" enctype="multipart/form-data"
	  onsubmit="return chk_new();">
<td><input name="kom" type="text" size="30" maxlenth="50"
<?php
if (isset($_POST['add_kom']) && $_POST['add_kom'] == 'ok' && isset($message_er))
	echo 'value="'.$_POST['kom'].'"';
?>
></td>
<td><input type="file" name="img_file"
	 accept="image/jpeg,image/jpg,image/gif,image/png,image/ico">
	 <input type="hidden" name="edit_kom" value="<?php echo $_POST['edit_kom']; ?>"></td>
<td><button type="submit" name="add_kom" value="ok">
	<img src="../pics/add.png" style="vertical-align: middle">&nbsp;Добавить
	</button></td>
</form>
</tr>
</table>
<?php
isset($message_ok) ? mes_ok($message_ok) : false;
isset($message_er) ? mes_er($message_er) : false;
?>
<p><button type="button" onclick="window.location='chemps.php'">
<img src='../pics/undo.png' style='vertical-align: middle'>&nbsp;Назад
</button></p>
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