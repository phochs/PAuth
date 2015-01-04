<?php
	abstract class Settings {
		protected static $m_aSettings = array();
		
		const INI_FILE = 'authConfig.ini';
		
		public static function get($sConfigValue) {
			if(empty(self::$m_aSetting)) {
				self::$m_aSettings = parse_ini_file(self::INI_FILE, true);
			}
			$aSetting = explode('.', $sConfigValue);
			$vReturn = self::$m_aSettings;
			foreach($aSetting as $sSetting) {
				$vReturn = $vReturn[$sSetting];
			}
			
			if(strtolower($vReturn) == 'on')
				$vReturn = true;
			if(strtolower($vReturn) == 'off')
				$vReturn = false;
			
			return $vReturn;
		}
	}
?>