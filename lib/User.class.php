<?php
	namespace PAuth;
	/**
	 * User manages the the user table in the database
	 * 
	 * @author Phochs
	 * @copyright © 2014
	 */
	class User {
		/**
		 * @var object $m_oDB the database connection
		 */
		protected $m_oDB;
		
		/**
		 * @var string $m-sUserId the UUID of the user
		 */
		protected $m_sUserId;
		
		/**
		 * @var string $m_sPassword the user's password, it's stored double-hashed
		 */
		protected $m_sPassword;
		
		/**
		 * @var string $m_sSalt the salt or the user, it's AES encrypted with the user's password
		 */
		protected $m_sSalt;
		
		/**
		 * @var string $m_sUsername the username (they can login with either their username or email address)
		 */
		protected $m_sUsername;
		
		/**
		 * @var string $m_sEmail the user's email address
		 */
		protected $m_sEmail;
		
		/**
		 * @var string $m_sName
		 */
		protected $m_sName;
		
		/**
		 * @var integer $m_iCreationTime when the user was created
		 */
		protected $m_iCreationTime;
		
		public function __construct($p_sUserId=null) {
			$this->reset();
			$this->_getDBConnection();
			
			if($p_sUserId != null)
				$this->loadUser($p_sUserId);
		}
		
		public function __get($p_sVar) {
			$aWhitelist = array('userId' => 'm_sUserId', 'password' => 'm_sPassword', 'salt' => 'm_sSalt', 'username' => 'm_sUsername', 'email' => 'm_sEmail', 'name' => 'm_sName', 'creationTime' => 'm_iCreationTime');
			
			$sVar = htmlspecialchars($p_sVar);
			if(array_key_exists($sVar, $aWhitelist)) {
				return $this->$aWhitelist[$sVar];
			}
			
			throw new Exception ('The requested variable ('.$sVar.') is not in the whitelist!');
		}
		
		public function __set($p_sVar, $p_vValue) {
			$aWhitelist = array('password' => 'm_sPassword', 'salt' => 'm_sSalt', 'username' => 'm_sUsername', 'email' => 'm_sEmail', 'name' => 'm_sName');
			
			$sVar = htmlspecialchars($p_sVar);
			if(array_key_exists($sVar, $aWhitelist)) {
				$this->$aWhitelist[$sVar] = $p_vValue;
			} else {
				throw new Exception ('The requested variable ('.$sVar.') is not in the whitelist!');
			}
		}
		
		protected function _getDBConnection() {
			$this->m_oDB = new DB();
		}
		
		public function loadUser($p_sUserId) {
			$this->reset(); // Just to be sure...
			
			$sQuery = 'SELECT * FROM user WHERE userId = :userId LIMIT 1';
			$oStatement = $this->m_oDB->prepare($sQuery);
			$oStatement->bindParam(':userId', $p_sUserId);
			$oStatement->execute();
			
			if($oStatement->rowCount() != 1)
				return false;
			
			$aData = $oStatement->fetch();
			
			$this->m_sUserId = $aData['userId'];
			$this->m_sPassword = $aData['password'];
			$this->m_sSalt = $aData['salt'];
			$this->m_sUsername = $aData['username'];
			$this->m_sEmail = $aData['email'];
			$this->m_sName = $aData['name'];
			$this->m_iCreationTime = $aData['creationTime'];
			
			return true;
		}
		
		public function findUser($p_sUsername) {
			// The username can be either the real username of the email address (couldn't think of a better variable name :( )
			$sQuery = 'SELECT userId FROM user WHERE';
			
			$aWhere = array();
			if(Settings::get('login.allowUsernameLogin'))
				$aWhere[] = 'username = :username';
			if(Settings::get('login.allowEmailLogin'))
				$aWhere[] = 'email = :username';
			
			if(count($aWhere) == 0)
				return false;
			
			$sWhere = implode(' OR ', $aWhere);
			$sQuery = $sQuery.' '.$sWhere;
			
			$oStatement = $this->m_oDB->prepare($sQuery);
			$oStatement->bindParam(':username', $p_sUsername);
			$oStatement->execute();
			
			if($oStatement->rowCount() != 1)
				return false;
			
			$sUserId = $oStatement->fetch()[0];
			
			return $this->loadUser($sUserId);
		}
		
		public function saveUser() {
			if($this->m_sUserId == '') { // Then we need to create a new user
				$iTime = time(); // To insert into the database and into the object
				$this->m_iCreationTime = $iTime;
				$this->m_sUserId = guidv4();
				
				$sQuery = 'INSERT INTO user (userId, password, salt, username, email, name, creationTime) VALUES (:userId, :password, :salt, :username, :email, :name, :creationTime)';
			} else { // Otherwise we need to update an existing user
				$sQuery = 'UPDATE user SET password = :password, salt = :salt, username = :username, email = :email, name = :name, creationTime = :creationTime WHERE userId = :userId';
			}
			
			$oStatement = $this->m_oDB->prepare($sQuery);
			$oStatement->bindParam(':userId', $this->m_sUserId);
			$oStatement->bindParam(':password', $this->m_sPassword);
			$oStatement->bindParam(':salt', $this->m_sSalt);
			$oStatement->bindParam(':username', $this->m_sUsername);
			$oStatement->bindParam(':email', $this->m_sEmail);
			$oStatement->bindParam(':name', $this->m_sName);
			$oStatement->bindParam(':creationTime', $this->m_iCreationTime);
			$oStatement->execute();
		}
		
		public function reset() {
			$this->m_sUserId = '';
			$this->m_sPassword = '';
			$this->m_sSalt = '';
			$this->m_sUsername = '';
			$this->m_sEmail = '';
			$this->m_sName = '';
			$this->m_iCreationTime = '';
		}
	}
?>