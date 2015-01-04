<?php
	function guidv4($data=null) {
		// Source: https://stackoverflow.com/questions/2040240/php-function-to-generate-v4-uuid
		if($data == null)
			$data = openssl_random_pseudo_bytes(16);
		assert(strlen($data) == 16);

		$data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
		$data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

		return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
	}
	
	function genRandStr($p_iLength=10) {
		$sChars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_+=;:,';
		$aChars = str_split($sChars);
		$sReturn = '';
		for($i=0;$i<$p_iLength;$i++) {
			$sReturn .= $aChars[mt_rand(0, count($aChars)-1)];
		}
		
		return $sReturn;
	}
?>