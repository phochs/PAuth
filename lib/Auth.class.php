<?php
	class Auth {
		public function login($p_sUsername, $p_sPassword) {
			// First, check if the user actually exists
			$oUser = $this->getUser($p_sUsername);
			if(!$oUser)
				return false;
			
			// Next, we will decrypt the salt and try to recreate the hash from the database
			$bCheck = $this->checkPassword($oUser, $p_sPassword);
			if(!$bCheck)
				return false;
			
			// The user exists and has filled in the right password, yay! Now we generate a new hash for the password and store in in the database (for extra security)
			$this->regeneratePassword($oUser, $p_sPassword);
			unset($p_sPassword);
			
			// Now we are ready to generate the token and store it in a cookie
			$this->createToken($oUser->userId);
			
			// That was it, we're done here!
			return true;
		}
		
		public function checkLogin() {
			// This method will return a user object if all goes well, or false if not
			
			// First, check it both tokens are present
			$bResult = $this->checkCookies();
			if(!$bResult)
				return false;
			
			// Now we will load the token from the database and check it
			$oUser = $this->checkToken();
			return $oUser;
		}
		
		protected function getUser($p_sUsername) {
			$oUser = new User();
			$bResult = $oUser->findUser($p_sUsername);
			
			if(!$bResult)
				return false;
			
			return $oUser;
		}
		
		protected function checkPassword(&$p_oUser, $p_sPassword) {
			$oAES = new AES();
			$sSalt = $oAES->decrypt($p_oUser->salt, $p_sPassword);
			
			$oBCrypt = new BCrypt();
			$sPassword = $oBCrypt->genPassword($p_sPassword, $sSalt);
			unset($p_sPassword);
			
			if($p_oUser->password == $sPassword)
				return true;
			
			return false;
		}
		
		protected function regeneratePassword(&$p_oUser, $p_sPassword) {
			$oBCrypt = new BCrypt();
			$p_oUser->password = $oBCrypt->genPassword($p_sPassword);
			
			$oAES = new AES();
			$p_oUser->salt = $oAES->encrypt($oBCrypt->salt, $p_sPassword);
			unset($p_sPassword);
			
			$p_oUser->saveUser();
		}
		
		protected function createToken($p_sUserId) {
			$oToken = new Token();
			$sUserToken = $oToken->genToken($p_sUserId);
			$oToken->saveToken();
			$sDBToken = $oToken->token;
			setcookie('sha_token_ID1', $sUserToken, 0, '/'); // Why would we give them usefull names?
			setcookie('sha_token_ID2', $sDBToken, 0, '/');
		}
		
		protected function checkCookies() {
			if(!isset($_COOKIE['sha_token_ID1']) || !isset($_COOKIE['sha_token_ID2']))
				return false;
			
			if(strlen($_COOKIE['sha_token_ID1']) != 255 || strlen($_COOKIE['sha_token_ID2']) != 255)
				return false;
			
			return true;
		}
		
		protected function checkToken() {
			$oToken = new Token();
			$bSuccess = $oToken->loadToken($_COOKIE['sha_token_ID2']);
			if(!$bSuccess)
				return false;
			
			$vReturn = $oToken->checkToken($_COOKIE['sha_token_ID1']);
			if($vReturn === false)
				$oToken->deleteToken();
			
			//All is good, now update the time
			$oToken->expires = time() + 1800;
			$oToken->saveToken();
			
			return $vReturn;
		}
	}
?>