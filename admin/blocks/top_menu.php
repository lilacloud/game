<td class="top_menu">
	<ul>
	<li>&nbsp;</li>
	<li><a href="../index.php">Главная</a></li>
<?php
	if (isset($_SESSION['user_id']) && isset($_SESSION['rights']) &&
		($_SESSION['rights'] == 2 || $_SESSION['rights'] == 1))
	echo '
	<li class="current"><a href="admin.php">Администратор</a></li>';
?>
	<li><a href="../ruls.php">Правила</a></li>
	<li><a href="">Европейские турниры</a></li>
	</ul>
</td>