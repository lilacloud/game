<td width="200px" valign="top">
	<!-- Поле авторизации, регистрации пользователя -->
<?php
if (!isset($_SESSION['user_id']) || !isset($_SESSION['login'])){
  print '
	<table>
	<tr>
		<td class="top_block"> <h1>Вход</h1> </td>
	</tr>
	<tr>
	  <td class="body_block">
		<form action="login.php" method="post">
		<table border="0"><tr><td>
		  <p class="log_pass">Логин:</p> <input type="text" name="username" class="login_txt">
		</td></tr>
		<tr><td>
		  <p class="log_pass">Пароль:</p> <input type="password" name="password" class="login_txt">
		</td></tr>
		<tr><td>
		  <input type="submit" name="login_button" value="ВОЙТИ" class="button">
		  <a style="font:12px Arial, Verdana; float:right; margin:10 0;" href="">Забыли пароль?</a>
		  <br><a style="font:12.5px Verdana, Arial; float:right;" href="regform.php">
		  <img src="pics/1463.png" style="vertical-align: middle">Регистрация</a>
		</td></tr></table>
		</form>
	  </td>
	</tr>
	</table><br>';
}
if (isset($_SESSION['user_id']) && isset($_SESSION['login'])){  print '
	<!-- Поле авторизировавшегося пользователя -->
	<table>
	<tr>
		<td class="top_block">
		<div style="color:#CC6600; margin:0; background:#F1D29C;
		font:bolder 17px Comic Sans MS,Verdana">'.
		$_SESSION["login"].'</div>
		</td>
	</tr>
	<tr>
		<td class="body_block">
		<ul>
			<li><a href="profile.php">Мой профиль</a></li>
			<center>
			<p class="logout"><a href="logout.php?action=logout">Выйти
			</a></p></center>
		</ul>
		</td>
	</tr>
	</table>
	<br>';
}
?>
	<!-- Поле бокового меню -->
	<table>
	<tr>
		<td class="top_block"> <h1>Навигация</h1> </td>
	</tr>
	<tr>
		<td class="body_block">
		<ul>
<?php   if (isset($_SESSION['user_id']) && isset($_SESSION['login']))
  		echo '
		<li><a href="kalendar.php">Календарь игр</a></li>
		<li><a href="prognoz.php">Сделать прогнозы</a></li>
		<li><a href="results.php">Результаты матчей</a></li>';
		if ((isset($_SESSION['user_id']) && isset($_SESSION['login']))||
			(!isset($_SESSION['user_id'])))
		echo '
		<li><a href="turnir_table.php">Турнирная таблица</a></li>
		<li><a href="">Архив новостей</a></li>
		<li><a href="">Болталка</a></li>';
?>
		</ul>
		</td>
	</tr>
	</table>
	<br><br><br><br><br><br><br><br><br><br>
</td>