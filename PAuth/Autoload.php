<?php
	spl_autoload_register('autholoader');
	function autholoader($p_sClass) {
		$aClass = explode('\\', $p_sClass);
		if($aClass[0] == 'PAuth' && is_file('lib/'.$aClass[count($aClass)-1].'.class.php'))
			require_once('lib/'.$aClass[count($aClass)-1].'.class.php');
	}
?>