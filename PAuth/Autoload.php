<?php
	spl_autoload_register('autholoader');
	function autholoader($p_sClass) {
		$aClass = explode('\\', $p_sClass);
		if($aClass[0] == 'PAuth' && is_file('PAuth/'.$aClass[count($aClass)-1].'.class.php'))
			require_once('PAuth/'.$aClass[count($aClass)-1].'.class.php');
	}
?>