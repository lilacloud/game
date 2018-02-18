<td width="200px" valign="top">
	<!-- Поле авторизации, регистрации пользователя -->
<?php
if (isset($_SESSION['user_id']) && isset($_SESSION['rights']) &&
	($_SESSION['rights'] == 1 || $_SESSION['rights'] == 2)):?>
	<table>
	<tr>
		<td class="top_block">
		<div style="color:#CC6600; margin:0; background:#F1D29C;
		font:bolder 17px Comic Sans MS,Verdana">
		<?php echo $_SESSION["login"]; ?></div>
		</td>
	</tr>
	<tr>
		<td class="top_block">
		<div style="color:#158014; font:bolder 15px Verdana,sans-serif;">
		<?php
		if ($_SESSION['rights'] == 1) echo 'СУПЕРПОЛЬЗОВАТЕЛЬ';
		if ($_SESSION['rights'] == 2) echo 'АДМИНИСТРАТОР';
		?>
		</div>
		</td>
	</tr>
	</table>
<?php endif;
if (isset($_SESSION['user_id']) && isset($_SESSION['rights'])):
?>  <br><br>
	<table>
	<tr>
		<td class="top_block"><h1>Управление:</h1></td>
	</tr>
	<tr>
		<td class="body_block">
		<ul>
		<?php
		if ($_SESSION['rights'] == 1)
			echo '<li><a href="editusers.php">Пользователями</a></li>';
		if ($_SESSION['rights'] == 1)
			echo '<li><a href="chemps.php">Командами</a></li>';
		if ($_SESSION['rights'] == 1 || $_SESSION['rights'] == 2)
		echo '<li><a href="turnirs.php">Турнирами</a></li>';
		if ($_SESSION['rights'] == 1)
			echo '<li><a href="settings.php">Настройками</a></li>';
		?>
		</ul>
		</td>
	</tr>
	</table>
<?php endif; ?>
<br><br><br><br><br><br><br>
</td>