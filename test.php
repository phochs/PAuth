<?php
	require_once('lib/function.lib.php');
	require_once('lib/Autoload.php');
	
	$oBCrypt = new BCrypt();
	var_dump($oBCrypt->genPassword('testPass', 'N5ucOPAX9kNAK.htC7znjg'));
?>