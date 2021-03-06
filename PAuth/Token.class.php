<?php
	namespace PAuth;
	class Token {
		/**
		 * @var object $m_oDB the database connection
		 */
		protected $m_oDB;
		
		/**
		 * @var string $m_sToken the token is used to primary identify the user
		 */
		protected $m_sToken;
		
		/**
		 * @var string $m_sUserId the encrypted ID of the logged in user
		 */
		protected $m_sUserId;
		
		/**
		 * @var string $m_sIP the IP address of the user
		 */
		protected $m_sIP;
		
		/**
		 * @var string $m_sIPVia an optional via address
		 */
		protected $m_sIPVia;
		
		/**
		 * @var string $m_sIPForward an optional forward address
		 */
		protected $m_sIPForward;
		
		/**
		 * @var string $m_sUserAgent the user agent the browser uses
		 */
		protected $m_sUserAgent;
		
		/**
		 * @var string $m_sUserLanguage the languages the user preferes
		 */
		protected $m_sUserLanguage;
		
		/**
		 * @var string $m_sHTTPAccept the mimetypes the browser accepts
		 */
		protected $m_sHTTPAccept;
		
		/**
		 * @var integer $m_iExpires the timestamp when the token expires
		 */
		protected $m_iExpires;
		
		public function __construct($p_sTokenId=null) {
			$this->reset();
			$this->_getDBConnection();
			
			if($p_sTokenId != null)
				$this->loadToken($p_sTokenId);
		}
		
		public function __get($p_sVar) {
			$aWhitelist = array('token' => 'm_sToken', 'userId' => 'm_sUserId', 'IP' => 'm_sIP', 'IPVia' => 'm_sIPVia', 'IPForward' => 'm_sIPForward', 'userAgent' => 'm_sUserAgent', 'userLanguage' => 'm_sUserLanguage', 'HTTPAccept' => 'm_sHTTPAccept', 'expires' => 'm_iExpires');
			
			$sVar = htmlspecialchars($p_sVar);
			if(array_key_exists($sVar, $aWhitelist)) {
				return $this->$aWhitelist[$sVar];
			}
			
			throw new Exception ('The requested variable ('.$sVar.') is not in the whitelist!');
		}
		
		public function __set($p_sVar, $p_vValue) {
			$aWhitelist = array('userId' => 'm_sUserId', 'IP' => 'm_sIP', 'IPVia' => 'm_sIPVia', 'IPForward' => 'm_sIPForward', 'userAgent' => 'm_sUserAgent', 'userLanguage' => 'm_sUserLanguage', 'HTTPAccept' => 'm_sHTTPAccept', 'expires' => 'm_iExpires');
			
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
		
		public function loadToken($p_sTokenId) {
			$this->reset(); // Just to be sure...
			
			$sQuery = 'SELECT * FROM '.Settings::get('database.tblPrefix').'authToken WHERE token = :token LIMIT 1';
			$oStatement = $this->m_oDB->prepare($sQuery);
			$oStatement->bindParam(':token', $p_sTokenId);
			$oStatement->execute();
			
			if($oStatement->rowCount() != 1)
				return false;
			
			$aData = $oStatement->fetch();
			
			$this->m_sToken = $aData['token'];
			$this->m_sUserId = $aData['userId'];
			$this->m_sIP = $aData['IP'];
			$this->m_sIPVia = $aData['IPVia'];
			$this->m_sIPForward = $aData['IPForward'];
			$this->m_sUserAgent = $aData['userAgent'];
			$this->m_sUserLanguage = $aData['userLanguage'];
			$this->m_sHTTPAccept = $aData['HTTPAccept'];
			$this->m_iExpires = $aData['expires'];
			
			return true;
		}
		
		public function saveToken() {
			if($this->m_sToken == '') { // Then we need to create a new user
				$this->m_sToken = genRandStr(Settings::get('cookies.tokenLength'));
				
				$sQuery = 'INSERT INTO '.Settings::get('database.tblPrefix').'authToken (token, userId, IP, IPVia, IPForward, userAgent, userLanguage, HTTPAccept, expires) VALUES (:token, :userId, :IP, :IPVia, :IPForward, :userAgent, :userLanguage, :HTTPAccept, :expires)';
			} else { // Otherwise we need to update an existing user
				$sQuery = 'UPDATE '.Settings::get('database.tblPrefix').'authToken SET userId = :userId, IP = :IP, IPVia = :IPVia, IPForward = :IPForward, userAgent = :userAgent, userLanguage = :userLanguage, HTTPAccept = :HTTPAccept, expires = :expires WHERE token = :token';
			}
			
			$oStatement = $this->m_oDB->prepare($sQuery);
			$oStatement->bindParam(':token', $this->m_sToken);
			$oStatement->bindParam(':userId', $this->m_sUserId);
			$oStatement->bindParam(':IP', $this->m_sIP);
			$oStatement->bindParam(':IPVia', $this->m_sIPVia);
			$oStatement->bindParam(':IPForward', $this->m_sIPForward);
			$oStatement->bindParam(':userAgent', $this->m_sUserAgent);
			$oStatement->bindParam(':userLanguage', $this->m_sUserLanguage);
			$oStatement->bindParam(':HTTPAccept', $this->m_sHTTPAccept);
			$oStatement->bindParam(':expires', $this->m_iExpires);
			$oStatement->execute();
		}
		
		public function genToken($p_sUserId) {
			$sUserToken = genRandStr(Settings::get('cookies.tokenLength'));
			$oAES = new AES();
			$this->m_sUserId = $oAES->encrypt($p_sUserId, $sUserToken);
			if(Settings::get('general.IPHashing'))
				$this->m_sIP = hash(Settings::get('hashing.tokenHashAlg'), $_SERVER['REMOTE_ADDR']);
			else
				$this->m_sIP = $_SERVER['REMOTE_ADDR'];
			
			if(isset($_SERVER['HTTP_VIA'])) {
				if(Settings::get('general.IPHashing'))
					$this->m_sIPVia = hash(Settings::get('hashing.tokenHashAlg'), $_SERVER['HTTP_VIA']);
				else
					$this->m_sIPVia = $_SERVER['HTTP_VIA'];
			}
			
			if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				if(Settings::get('general.IPHashing'))
					$this->m_sIPForward = hash(Settings::get('hashing.tokenHashAlg'), $_SERVER['HTTP_X_FORWARDED_FOR']);
				else
					$this->m_sIPForward = $_SERVER['HTTP_X_FORWARDED_FOR'];
			}
			
			if(isset($_SERVER['HTTP_USER_AGENT']))
				$this->m_sUserAgent = hash(Settings::get('hashing.tokenHashAlg'), $_SERVER['HTTP_USER_AGENT']);
			
			if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
				$this->m_sUserLanguage = hash(Settings::get('hashing.tokenHashAlg'), $_SERVER['HTTP_ACCEPT_LANGUAGE']);
			
			$this->m_sHTTPAccept = hash(Settings::get('hashing.tokenHashAlg'), $_SERVER['HTTP_ACCEPT']);
			$this->m_iExpires = time() + Settings::get('login.expireTime');
			
			return $sUserToken;
		}
		
		public function checkToken($p_sUserToken) {
			if(Settings::get('general.IPHashing'))
				$sIP = @hash(Settings::get('hashing.tokenHashAlg'), $_SERVER['REMOTE_ADDR']);
			else
				$sIP = @$_SERVER['REMOTE_ADDR'];
			if($this->m_sIP != $sIP)
				return false;
			
			if(Settings::get('general.IPHashing'))
				$sIPVia = @hash(Settings::get('hashing.tokenHashAlg'), $_SERVER['HTTP_VIA']);
			else
				$sIPVia = @$_SERVER['HTTP_VIA'];
			if(!empty($this->m_sIPVia) || isset($_SERVER['HTTP_VIA']))
				if($this->m_sIPVia != $sIPVia)
					return false;
			
			if(Settings::get('general.IPHashing'))
				$sIPForward = @hash(Settings::get('hashing.tokenHashAlg'), $_SERVER['HTTP_X_FORWARDED_FOR']);
			else
				$sIPForward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
			if(!empty($this->m_sIPForward) || isset($_SERVER['HTTP_X_FORWARDED_FOR']))
				if($this->m_sIPForward != $sIPForward)
					return false;
			
			if(!empty($this->m_sUserAgent) || isset($_SERVER['HTTP_USER_AGENT']))
				if($this->m_sUserAgent != hash(Settings::get('hashing.tokenHashAlg'), $_SERVER['HTTP_USER_AGENT']))
					return false;
			
			if(!empty($this->m_sUserLanguage) || isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
				if($this->m_sUserLanguage != hash(Settings::get('hashing.tokenHashAlg'), $_SERVER['HTTP_ACCEPT_LANGUAGE']))
					return false;
			
			if($this->m_sHTTPAccept != hash(Settings::get('hashing.tokenHashAlg'), $_SERVER['HTTP_ACCEPT']))
				return false;
			
			if($this->m_iExpires < time())
				return false;
			
			$oAES = new AES();
			$sUserId = $oAES->decrypt($this->m_sUserId, $p_sUserToken);
			$oUser = new User();
			$bSuccess = $oUser->loadUser($sUserId);
			if($bSuccess)
				return $oUser;
			
			return false;
		}
		
		public function deleteToken() {
			$sQuery = 'DELETE FROM '.Settings::get('database.tblPrefix').'authToken WHERE token = :token';
			$oStatement = $this->m_oDB->prepare($sQuery);
			$oStatement->bindParam(':token', $this->m_sToken);
			return $oStatement->execute();
		}
		
		public function deleteOldTokens() {
			$iTime = time();
			$sQuery = 'DELETE FROM '.Settings::get('database.tblPrefix').'authToken WHERE expires <= :time';
			$oStatement = $this->m_oDB->prepare($sQuery);
			$oStatement->bindParam(':time', $iTime);
			return $oStatement->execute();
		}
		
		public function reset() {
			$this->m_sToken = '';
			$this->m_sUserId = '';
			$this->m_sIP = '';
			$this->m_sIPVia = '';
			$this->m_sIPForward = '';
			$this->m_sUserAgent = '';
			$this->m_sUserLanguage = '';
			$this->m_sHTTPAccept = '';
			$this->m_iExpires = 0;
		}
	}
?>