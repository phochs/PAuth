<?php
	require_once('lib/function.lib.php');
	require_once('lib/DB.class.php');
	require_once('lib/Token.class.php');
	
	$oToken = new Token();
	$oToken->userId = 'test.userId';
	$oToken->IP = 'test.IP';
	$oToken->IPVia = 'test.IPVia';
	$oToken->IPForward = 'test.IPForward';
	$oToken->userAgent = 'test.userAgent';
	$oToken->userLanguage = 'test.userLanguage';
	$oToken->HTTPAccept = 'test.HTTPAccept';
	$oToken->expires = time();
	$oToken->saveToken();
?>