<!doctype html>
<html>
	<head>
		<title>Auth</title>
		
		<link rel="stylesheet" href="css/bootstrap.min.css" type="text/css" />
		<link rel="stylesheet" href="css/font-awesome.min.css" type="text/css" />
		<link rel="stylesheet" href="css/style.css" type="text/css" />
		
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		
		<script type="text/javascript" src="js/jquery-2.1.1.min.js"></script>
	</head>
	<body>
<?php
	require_once('lib/Autoload.php');
	require_once('lib/function.lib.php');
	
	// The first block of commented code registers a user. The second block performs a login. The third block checks a login
	
	/*$oUser = new User();
	$oUser->username = 'phochs';
	$oUser->email = 'email@example.com';
	$oUser->name = 'My Name';
	$oUser->gender = 'm';
	
	$oBCrypt = new BCrypt();
	$oUser->password = $oBCrypt->genPassword('testPass');
	
	$oAES = new AES();
	$oUser->salt = $oAES->encrypt($oBCrypt->salt, 'testPass');
	$oUser->saveUser();*/
	
	/*$oAuth = new Auth();
	var_dump($oAuth->login('phochs', 'testPass'));*/
	
	/*$oAuth = new Auth();
	var_dump($oAuth->checkLogin());*/
?>
	</body>
</html>