<?php
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
			
			$sQuery = 'SELECT * FROM authToken WHERE token = :token LIMIT 1';
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
				$this->m_sToken = genRandStr(255);
				
				$sQuery = 'INSERT INTO authToken (token, userId, IP, IPVia, IPForward, userAgent, userLanguage, HTTPAccept, expires) VALUES (:token, :userId, :IP, :IPVia, :IPForward, :userAgent, :userLanguage, :HTTPAccept, :expires)';
			} else { // Otherwise we need to update an existing user
				$sQuery = 'UPDATE authToken SET userId = :userId, IP = :IP, IPVia = :IPVia, IPForward = :IPForward, userAgent = :userAgent, userLanguage = :userLanguage, HTTPAccept = :HTTPAccept, expires = :expires WHERE token = :token';
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