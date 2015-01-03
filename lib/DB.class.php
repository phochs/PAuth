<?php
	class DB extends PDO {
		public function __construct() {
			parent::__construct('mysql:host=localhost;dbname=auth', 'root', '');
		}
	}
?>