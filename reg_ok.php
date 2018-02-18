<?php
/*
Приходят с register.php	через GET
reg	 - была удачная регистрация и ='ok'
name - имя пользователя, который прошел удачно регистрацию
*/
require_once('blocks/connect.php');
require_once('functions.php');
if (isset($_GET['reg']) && $_GET['reg'] == 'ok'):
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="description" content="">
<meta name="keywords" content="">
<title>Футбольный прогноз | Удачная регистрация</title>
<link href="styles.css" rel="stylesheet" type="text/css">
</head>
<body>
<!-- Подключается Header -->
<?php require_once('blocks/header.php'); ?>
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
	  	<table align='center'
		style="width:50%; margin-top:40px; text-align:center; border:1px solid #75A54B;
		background-color:#f6f6f6; font-family:Verdana; font-size:14px; color:2f2f2f;
		padding:10px 0px">
		<tr>
			<td>
			<center><h1><p style="line-height:25px">Уважаемый(ая)<br>
			<font color='#E8C208'><?php echo $_GET['name'] ?></font><br>
			Вы успешно зарегистрированы!
			</p></h1></center><br>
			<p>На Ваш e-mail было направлено письмо с просьбой подтвердить регистрацию.</p>
			<p>Чтобы войти на сайт под своим логином, необходимо завершить
			   регистрацию и активировать учетную запись
			   пройдя по ссылке, указанной в письме.</p>
			</td>
		</tr>
		</table>
    	<!-------------------------->
	  </td>
	</tr>
	</table>
	</td>
</tr>
<tr>
<?php require_once("blocks/footer.php"); ?>
</tr>
</table>
</body>
</html>
<?php
else:
header('Location:index.php');
exit();
endif;
?>