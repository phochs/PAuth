<?php
	class BCrypt {
		protected $m_iRounds;
		
		protected $m_sSalt;
		
		public function __construct($p_iRounds=14) {
			if(CRYPT_BLOWFISH != 1) {
				throw new Exception("Bcrypt is not supported on this server, please see the following to learn more: http://php.net/crypt");
			}
			
			$this->m_iRounds = intval($p_iRounds);
		}
		
		public function __get($p_sVar) {
			$aWhitelist = array('rounds' => 'm_iRounds', 'salt' => 'm_sSalt');
			
			$sVar = htmlspecialchars($p_sVar);
			if(array_key_exists($sVar, $aWhitelist)) {
				return $this->$aWhitelist[$sVar];
			}
			
			throw new Exception ('The requested variable ('.$sVar.') is not in the whitelist!');
		}
		
		public function __set($p_sVar, $p_vValue) {
			$aWhitelist = array('rounds' => 'm_iRounds', 'salt' => 'm_sSalt');
			
			$sVar = htmlspecialchars($p_sVar);
			if(array_key_exists($sVar, $aWhitelist)) {
				$this->$aWhitelist[$sVar] = $p_vValue;
			} else {
				throw new Exception ('The requested variable ('.$sVar.') is not in the whitelist!');
			}
		}
		
		public function genSalt() {
			/* openssl_random_pseudo_bytes(16) Fallback */
			$sSeed = '';
			for($i = 0; $i < 16; $i++) {
				$sSeed .= chr(mt_rand(0, 255));
			}
			/* GenSalt */
			$sSalt = substr(strtr(base64_encode($sSeed), '+', '.'), 0, 22);
			
			$this->m_sSalt = $sSalt;
		}
		
		public function genHash($p_sPassword) {
			if(empty($this->m_sSalt))
				$this->gensalt();
			
			$sHash = crypt($p_sPassword, '$2y$'.$this->m_iRounds.'$'.$this->m_sSalt);
			unset($p_sPassword);
			return $sHash;
		}
		
		public function genPassword($p_sPassword, $p_sSalt=null) {
			// This function can be used to generate and to check a password.
			
			// TODO: generate a hash for every time this code is installed
			$sPepper = 'cvkSqfq7Z+lndnYe7kj+_Zn;IrW5vnBs';
			
			$this->m_sSalt = $p_sSalt;
			
			$sPassword = $this->genHash($p_sPassword);
			unset($p_sPassword);
			
			$sPassword = hash('sha512', $sPepper.$sPassword);
			return $sPassword;
		}
	}
?>