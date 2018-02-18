<?php
if ($_POST['reg_button'] <> 'Регистрация'){
	header('Location:index.php');
	exit();
}
require_once('blocks/connect.php');
require_once('functions.php');
/*
Переменные, которые отправляются в register.php через POST
username 	- ник пользователя (имя тренера)
pass1 		- пароль 1
pass2 		- пароль 2
email 		- электронный адрес
fname 		- Имя
lname 		- Фамилия
gender 		- пол
day 		- день рождения
month 		- месяц рождения
year 		- год рождения
place 		- место жительства
kod 		- код безопасности введённый пользователем
reg_button 	- кнопка ввода данных регистрации (должна иметь значение "Регистрация")

$er[] 		- массив, куда заносится информация об ошибках ввода пользователем данных в форму
			  ($er[1] - логин, $er[2] - пароль 1, $er[3] - пароль 2,
			   $er[4] - e-mail, $er[5] - Имя, $er[6] - Фамилия, $er[7] - дата,
			   $er[8] - место жительства, $er[9] - код безопасности)
			  (значения: "1" - ошибка, "2" - без ошибки)
$flag		- "1" - были ошибки при вводе данных в форму, "2" - без ошибок
$username 	- передаётся с POST логин (имя тренера)
$pass1 		- пароль 1 переданный с POST
$pass2 		- пароль 2 переданный с POST
$kol 		- колличество записей в таблице kod_bezop
$pic 		- cлучайное число от 1 до количества записей в таблице kod_bezop
$myrow 		- массив записи результата выборки с таблицы kod_bezop с id=$pic
*/
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="description" content="">
<meta name="keywords" content="">
<title>Футбольный прогноз | Регистрация пользователя</title>
<link href="styles.css" rel="stylesheet" type="text/css">
<script>
function chk_reg(){
var flag = true;
	if (document.regform.username.value=="" ||
		document.regform.pass1.value=="" ||
		document.regform.pass2.value=="" ||
		document.regform.email.value=="" ||
		document.regform.fname.value=="" ||
		document.regform.kod.value==""){
			flag = false;
			alert("Не все обязательные поля заполнены!!!");
	}
	return flag;
}
</script>
</head>
<body>
<!-- Подключается Header -->
<?php require_once("blocks/header.php"); ?>
<!------------------------->
<table class="telo" cellpadding="0" cellspacing="0">
<tr>
	<!-- Подключается верхнее меню -->
	<?php require_once("blocks/top_menu.php"); ?>
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

		<form name='regform' method='post' action='register.php' onsubmit='return chk_reg();'>
		<table width=100% border='0' cellspacing='0' cellpadding='0'>
			<tr>
			<td width=25% align='right'><h3><b>Ник (Логин): &nbsp;</b></h3></td>
			<td width='10px'><font color='red'>*</font></td>
			<td><input type='text' size='25' maxlength='20' name='username'
<?php		// Если была ошибка - сохраняем значение поля
			if (isset($_POST["username"])){
				$username = $_POST["username"];
				print "value='$username'>";
			} else print ">";
			if (isset($_POST["username"])){
				$username = trim($username);
				// Проверяем, что-бы не меньше 3-х символов
				if (strlen($username)<3){
					print "&nbsp;&nbsp;<img src='pics/er.png'> &nbsp;Логин не может быть меньше 3-х символов!";
					$er[1] = 1;
				} else { // Проверяем, что-бы правильные символы вводились
				  if (preg_match("/[^(\w)|(\x7F-\xFF)|(\s)]/",$username)){
				   	print "&nbsp;&nbsp;<img src='pics/er.png'> &nbsp;В логине содержатся недопустимые символы!";
				 	$er[1] = 1;
				  } else { //проверяем наличие в БД уже такого пользователя
                    $username = $conn -> real_escape_string(trim($username));
                    if ($stmt = $conn -> prepare("SELECT id FROM users WHERE username=?")){
				 	  $stmt -> bind_param('s', $username);
				 	  $stmt -> execute();
				 	  $res = $stmt -> get_result();
				 	  if ($res -> num_rows > 0){
						$stmt -> close();
						$res -> close();
						print "&nbsp;&nbsp;<img src='pics/er.png'> &nbsp;Пользователь с таким логином уже зарегистрирован!";
						$er[1] = 1;
				      } else {print "&nbsp;&nbsp;<img src='pics/ok_sm.png'>";
						  $er[1] = 2;
					 	}
					} else error_rez('ОШИБКА при обращении к базе данных для проверки логина!');
				  }
				}
			}
		print "
				</td>
			</tr>
			<tr>
				<td width=25% align='right'><h3><b>Пароль: &nbsp;</b></h3></td>
				<td><font color='red'>*</font></td>
				<td><input type='password' size='25' maxlength='25' name='pass1'>
		";		// Проверяем, что-бы было не меньше 6-ти символов
				if (isset($_POST["pass1"])){
					$pass1 = $_POST["pass1"];
					if (strlen($pass1)<6){
						print "&nbsp;<img src='pics/er.png'> &nbsp;Пароль не может быть меньше 6-ти символов!";
						$er[2] = 1;
					} else { //print "&nbsp;<img src='pics/ok_sm.png'>";
							$er[2] = 2;
						}
				}
		print "
				</td>
			</tr>
			<tr>
				<td width=25% align='right'><h3><b>Повторите пароль: &nbsp;</b></h3></td>
				<td><font color='red'>*</font></td>
				<td><input type='password' size='25' maxlength='25' name='pass2'>
		";		// Проверяем совпадение двух паролей
				if (isset($_POST["pass1"]) && isset($_POST["pass2"])){
					$pass1 = $_POST["pass1"];
					$pass2 = $_POST["pass2"];
				  if ($er[2] == 2){ //если с первым паролем всё нормально, тогда проверяется второй
					if (strcmp($pass1, $pass2) != 0){
						print "&nbsp;<img src='pics/er.png'> &nbsp;Пароли не совпадают, повторите попытку!";
						$er[3] = 1;
					} else {//print "&nbsp;<img src='pics/ok_sm.png'>";
							$er[3] = 2;
					}
				  }
				}
		print "
				</td>
			</tr>
			<tr>
				<td width=25% align='right'><h3><b>Ваш E-Mail: &nbsp;</b></h3></td>
				<td><font color='red'>*</font></td>
				<td><input type='text' size='25' name='email'";
				// Если была ошибка - сохраняем значение поля
				if (isset($_POST["email"])){
					$email = $_POST["email"];
					print "value='$email'>";
				} else print '>';
				// Проверяем правильность ввода e-mail
				if (isset($_POST["email"])){
					if (!preg_match("/^[\._A-Za-z0-9-]+@[\.A-Za-z0-9-]+\.[a-z]{2,6}$/", $email)){
						print "&nbsp;&nbsp;<img src='pics/er.png'> &nbsp;Вы ввели неверный E-Mail адрес!";
						$er[4] = 1;
					} else { //проверяем наличие в БД уже такого e-mail
						$email = $conn -> real_escape_string(trim($email));
						if ($stmt = $conn -> prepare("SELECT id FROM users WHERE email=?")){
							$stmt -> bind_param('s', $email);
							$stmt -> execute();
							$res = $stmt->get_result();
							if ($res -> num_rows > 0){
								$stmt -> close();
								$res -> close();
								print "&nbsp;&nbsp;<img src='pics/er.png'> &nbsp;Такой E-Mail адрес уже зарегистрирован!";
								$er[4] = 1;
							} else {
								print "&nbsp;&nbsp;<img src='pics/ok_sm.png'>";
								$er[4] = 2;
						  	}
						 } else error_rez('ОШИБКА при обращении к базе данных для проверки E-Mail!');
					  }
				}
		print "
				</td>
			</tr>
			<tr>
			<td width=25% align='right'><h3><b>Имя: &nbsp;</b></h3></td>
				<td><font color='red'>*</font></td>
				<td><input type='text' size='25' maxlength='20' name='fname'";
				// Если была ошибка - сохраняем значение поля
				if (isset($_POST["fname"])){
					$fname = $_POST["fname"];
					print "value='$fname'>";
				} else print '>';
				// Проверяем правильность ввода имени
				if (isset($_POST["fname"])){
				  if (preg_match("/[^(\w)|(\x7F-\xFF)|(\s)]/",$fname)){
				  	  print "&nbsp;&nbsp;<img src='pics/er.png'> &nbsp;Имя не может содержать недопустимые символы!";
					  $er[5] = 1;
				  } else {print "&nbsp;&nbsp;<img src='pics/ok_sm.png'>";
							$er[5] = 2;
					}
				}
	print "
				</td>
		</tr>
			<tr>
			<td width=25% align='right'><h3><b>Фамилия: &nbsp;</b></h3></td>
				<td>&nbsp;</td>
			<td><input type='text' size='25' name='lname'
   ";       // Если была ошибка - сохраняем значение поля
			if (isset($_POST["lname"])){
				$lname = $_POST["lname"];
				print "value='$lname'>";
			} else print ">";
			// Проверяем правильность ввода фамилии
			if (isset($_POST["lname"]) && $_POST["lname"] != ""){
				if (preg_match("/[^(\w)|(\x7F-\xFF)|(\s)]/",$lname)){
					print "&nbsp;&nbsp;<img src='pics/er.png'> &nbsp;Фамилия не может содержать недопустимые символы!";
					$er[6] = 1;
				} else {print "&nbsp;&nbsp;<img src='pics/ok_sm.png'>";
						$er[6] = 2;
				}
			}
		print "
				</td>
			</tr>
			<tr>
				<td width=25% align='right'><h3><b>Пол: &nbsp;</b></h3></td>
				<td>&nbsp;</td>
				<td>
					<b><input type='radio' name='gender' value='M'
		";		//Если уже был ввод
				if (isset($_POST["gender"]) && $_POST["gender"]=="M"){
					$gender = $_POST["gender"];
					print "checked";
				}
		print "
					> Мужской</b> &nbsp;&nbsp;
					<b><input type='radio' name='gender' value='W'
		";
				//Если уже был ввод
				if (isset($_POST["gender"]) && $_POST["gender"]=="W"){
					$gender = $_POST["gender"];
					print "checked";
				}
		print "
					> Женский</b>
				</td>
			</tr>
			<tr>
				<td width=25% align='right'><h3><b>Дата рождения: &nbsp;</b></h3></td>
				<td>&nbsp;</td>
				<td><select name='day'>
		";
				if (isset($_POST["day"]) && $_POST["day"] != 0){ //Если уже был ввод (была ошибка),
					$day = $_POST["day"];
					for ($i=1;$i<=31;$i++){
						if ($i==$day) {
							print "<option value='$day' selected>$day</option>";
						} else
						echo "<option value=\"$i\">$i</option>";
					}
				} else {
					print "<option value='0' selected>День</option>";
					for ($i=1;$i<=31;$i++){
						echo "<option value=\"$i\">$i</option>";
					}
				  }
			print "</select>
					<select name='month'>";
				if (isset($_POST["month"]) && $_POST["month"] != 0){ //Если уже был ввод,
					$month = $_POST["month"];
					$monthes = array (0,"января","февраля","марта","апреля","мая","июня","июля","августа","сентября","октября","ноября","декабря");
					for ($i=1;$i<=12;$i++){
						if ($i==$month) {
							print "<option value='$month' selected>$monthes[$month]</option>";
						} else
						echo "<option value=\"$i\">$monthes[$i]</option>";
					}
				} else {
					print "<option value='0' selected>Месяц</option>";
					$monthes = array (0,"января","февраля","марта","апреля","мая","июня","июля","августа","сентября","октября","ноября","декабря");
					for ($i=1;$i<=12;$i++){
						echo "<option value=\"$i\">$monthes[$i]</option>";
				}
				}
				print "
				</select>
					<select name='year'>
				";
				if (isset($_POST["year"]) && $_POST["year"] != 0){ //Если уже был ввод
					$year = $_POST["year"];
					for ($i=1940;$i<=date("Y")-10;$i++){
						if ($i==$year) {
							print "<option value='$year' selected>$year</option>";
						} else
						echo "<option value=\"$i\">$i</option>";
					}
				} else {
					print "<option value='0' selected>Год</option>";
					for ($i=1940;$i<=date("Y")-10;$i++){
						echo "<option value=\"$i\">$i</option>";
					}
				}
				print "</select>"; //Проверяем правильность ввода даты
				if (isset($_POST["day"]) && $_POST["day"] != 0 &&
					isset($_POST["month"]) && $_POST["month"] != 0 &&
					isset($_POST["year"]) && $_POST["year"] != 0){
					if (!checkdate($month, $day, $year)){
						print "&nbsp;&nbsp;<img src='pics/er.png'> &nbsp;Вы выбрали неправильную дату!";
						$er[7] = 1;
					} else {print "&nbsp;&nbsp;<img src='pics/ok_sm.png'>";
							$er[7] = 2;
					}
				}
		print "
				</td>
			</tr>
			<tr>
				<td width=25% align='right'><h3><b>Место жительства: &nbsp;</b></h3></td>
				<td>&nbsp;</td>
				<td><input type='text' size='40' maxlength='50' name='place'
		";     	// Если была ошибка - сохраняем значение поля
				if (isset($_POST["place"])){
					$place = $_POST["place"];
					print "value='$place'>";
				} else print ">";
				// Проверяем правильность ввода
				if (isset($_POST["place"]) && $_POST["place"] != ""){
					if (!preg_match("/[\w\x7F-\xFF\d\s\,\.\-]{3,50}/",$place)){
						print "&nbsp;&nbsp;<img src='pics/er.png'> &nbsp;Содержыт недопустимые символы!";
						$er[8] = 1;
					} else {print "&nbsp;&nbsp;<img src='pics/ok_sm.png'>";
						$er[8] = 2;
					}
				}
		print "
				</td>
			</tr>
			<tr>
				<td width=25% align='right'><h3><b>Код безопасности: &nbsp;</b></h3></td>
				<td><font color='red'>*</font></td>
		";      //Получение количества строк в таблице
				$res = $conn -> query("SELECT COUNT(*) FROM kod_bezop");
				if (!$res) error_rez('ОШИБКА! SQL запрос не выполнен при загрузке изображения кода безопасности!');
 				if ($res -> num_rows == 0)
					error_rez('Информация по запросу кода безопасности не может быть извлечена,
								в таблице нет записей!');
				$kol = $res -> fetch_array();
				//Получение случайной картинки кода безопасности
				$pic = mt_rand(1, $kol[0]);
				$res = $conn -> query("SELECT img, sum FROM kod_bezop WHERE id= '$pic'");
				if (!$res) error_rez('ШИБКА! SQL запрос не выполнен при загрузке изображения кода безопасности!');
				if ($res -> num_rows == 0)
					error_rez('Информация по запросу кода безопасности не может быть извлечена,
								в таблице нет записей!');
				$myrow = $res -> fetch_assoc();
		print "
				<td>
				<input type='hidden' name='kod_b' value='{$myrow['sum']}'>
				<img align='absmiddle' src='{$myrow['img']}'>&nbsp;
				<input type='text' size='1' maxlength='2' name='kod'>
		";      // Проверяем правильность ввода кода
				if (isset($_POST["kod"])){
					$kod = $_POST["kod"];
					$sum = $_POST["kod_b"];
					if ($sum != $kod){
						print "&nbsp;&nbsp;<img src='pics/er.png'> &nbsp;Вы ввели неверный ответ!";
						$er[9] = 1;
					} else if ($sum == $kod) { $er[9] = 2; }
				}
		print "
			</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td colspan='2'><br><hr align='left' width='330px'><font color='red'>*</font>
				&nbsp;- поля, обязательные для заполнения</td>
			</tr>
		"; // проверка: были ли ошибки
			$flag = 2;
			for ($i=1;$i<=9;$i++){
				if ((isset($er[$i])) && ($er[$i] == 1)){
					$flag = 1;
				}
			}
   print "
			<tr>
				<td width=20%>&nbsp;</td><td>&nbsp;</td>
				<td><br><input type='submit' class='button' name='reg_button' value='Регистрация'>
				</td>
			</tr>
			</table>
			</form>
	";      // -- Конец ввода формы -->
			if ($flag == 2){ // если не было ошибок
				$username = $conn -> real_escape_string(htmlspecialchars(trim($_POST["username"])));
				$pass = $conn -> real_escape_string(htmlspecialchars($_POST["pass1"]));
				$pass = md5($pass);
				$email = $conn -> real_escape_string(htmlspecialchars(trim($_POST["email"])));
				$fname = $conn -> real_escape_string(htmlspecialchars(trim($_POST["fname"])));
			  if (isset($_POST["lname"]))
				$lname = $conn -> real_escape_string(htmlspecialchars(trim($_POST["lname"])));
			  if (isset($_POST["gender"]))
				$gender = $_POST["gender"];
			  if (isset($_POST["day"]) && isset($_POST["month"]) && isset($_POST["year"])){
				$day = $_POST["day"];
				$month = $_POST["month"];
				$year = $_POST["year"];
				$date_b = date("Y-m-d", mktime(0, 0, 0, $month, $day, $year));
			  }
			  if (isset($_POST["place"]))
				$place = $conn -> real_escape_string(htmlspecialchars(trim($_POST["place"])));
				$code = md5(uniqid( rand(), 1));
				$sql = "INSERT INTO
				users (username,pass,email,fname,lname,gender,date_b,date_r,
						place,last_visit,rights,activation)
				VALUES(?, ?, ?, ?, ?, ?, ?, NOW(), ?, '', 3, ?)";
				if($stmt = $conn -> prepare($sql)){                   $stmt -> bind_param('sssssssss',$username,$pass,$email,$fname,
                   						$lname,$gender,$date_b,$place,$code);
                   $stmt -> execute();
                   $stmt -> close();
                   /*
					// Посылаем письмо пользователю с просьбой активировать учетную запись
					$headers = "From: ".$_SERVER['SERVER_NAME']." <'stargates@mail.ru'>\n";
					$headers = $headers."Content-type: text/html; charset=\"windows-1251\"\n";
					$headers = $headers."Return-path: <'stargates@mail.ru'>\n";
					$message = '<p>Добро пожаловать на сайт '.$_SERVER['SERVER_NAME'].'!</p>'."\n";
					$message = $message.'<p>Пожалуйста, обязательно сохраните это письмо. Параметры вашей
						учётной записи: </p>'."\n";
					$message = $message.'<p>Логин: '.$username.'<br/>Пароль: '.$pass.'</p>'."\n";
					$message = $message.'<p>Для активации вашей учетной записи перейдите по ссылке:</p>'."\n";
					$link = 'http://'.$_SERVER['SERVER_NAME'].'/activation.php?code='.$code;
					$message = $message.'<p><a href="'.$link.'">Активировать учетную запись</a></p>'."\n";
					$message = $message.'<p>Не забывайте свой пароль, он хранится в нашей базе
						в зашифрованном виде, и мы не сможем вам его выслать. Если вы всё же забудете пароль,
						то нажмите на ссылку \"Забыли пароль?\" на главной странице, и выполнится процедура смены пароля.</p>'."\n";
					$message = $message.'<p>Спасибо за то, что зарегистрировались на нашем сайте.</p>'."\n";
					$message = $message.'<p>--<br>Данное письмо сгенерировано автоматически, отвечать на него не нужно.
						Если Вы не регистрировались на этом сайте, просто проигнорируйте это письмо и удалите его.</p>'."\n";
					$subject = 'Регистрация на сайте '.$_SERVER['SERVER_NAME'];
					$subject = '=?koi8-r?B?'.base64_encode(convert_cyr_string($subject, "w","k")).'?=';
					mail( $email, $subject, $message, $headers );
				  */
					$conn -> close();
					//header('Location:reg_ok.php');
					//exit();
					echo '<script type="text/javascript">
							window.location = "reg_ok.php?reg=ok&name='.$fname.'"
						  </script>';
				} else error_rez('При регистрации произошла ошибка.
									Вернитесь назад и повторите попытку!');
			}
?>
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