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
	
	/*$oUser = new PAuth\User();
	$oUser->username = 'phochs';
	$oUser->email = 'email@example.com';
	$oUser->name = 'My Name';
	
	$oBCrypt = new PAuth\BCrypt();
	$oUser->password = $oBCrypt->genPassword('testPass');
	
	if(Settings::get('hashing.encryptSalt')) {
		$oAES = new PAuth\AES();
		$oUser->salt = $oAES->encrypt($oBCrypt->salt, 'testPass');
	} else {
		$oUser->salt = $oBCrypt->salt;
	}
	$oUser->saveUser();*/
	
	/*$oAuth = new PAuth\Auth();
	var_dump($oAuth->login('phochs', 'testPass'));*/
	
	/*$oAuth = new PAuth\Auth();
	var_dump($oAuth->checkLogin());*/
?>
	</body>
</html>