<?php
// предотвращение входа в дерево каталога
	$uri = 'http://'.$_SERVER['HTTP_HOST'];
	header('Location: '.$uri.'/index.php');
	exit;
?>