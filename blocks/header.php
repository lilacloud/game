<div id="header">
<a href="index.php"><img id="logo" src="
<?php
$str = strpos($_SERVER['PHP_SELF'], '/admin/');
if (!$str === false){	echo '../';
}
?>
pics/logo.png"></a>
</div>