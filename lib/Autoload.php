<?php
	spl_autoload_register('autholoader');
	function autholoader($p_sClass) {
		require_once('lib/'.$p_sClass.'.class.php');
	}
?>