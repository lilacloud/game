<?php
function error_rez($text){
	<b>'.$text.'</b>
	<form><br>
		<button type="button" onclick="window.history.back();">
			<img src="../pics/undo.png" style="vertical-align: middle">&nbsp;Вернуться назад
		</button>
	</form>');
}
function mes_ok($text){
}
function mes_er($text){
	print "<img src='../pics/er.png' style='vertical-align: middle'>&nbsp;<b>$text</b>";
}
function mes_inf($text){
	print "<img src='../pics/inf.png' style='vertical-align: middle'>&nbsp;<b>$text</b>";
}
?>