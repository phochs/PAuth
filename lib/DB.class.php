<?php
	namespace PAuth;
	class DB extends \PDO {
		public function __construct() {
			$sLoginStr = Settings::get('database.driver').':host='.Settings::get('database.host').';dbname='.Settings::get('database.db');
			parent::__construct($sLoginStr, Settings::get('database.user'), Settings::get('database.pass'));
		}
	}
?>