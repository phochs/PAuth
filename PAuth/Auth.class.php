<?php
	namespace PAuth;
	class Auth {
		public function login($p_sUsername, $p_sPassword) {
			$oToken = new Token();
			$oToken->deleteOldTokens(); // As a "comminity service", we'll delete tokens that get left behind and are expired as often as possible
			
			$this->logout(); // If someone is logged in, let's log them out
			
			// First, check if the user actually exists
			$oUser = $this->_getUser($p_sUsername);
			if(!$oUser)
				return false;
			
			// Next, we will decrypt the salt and try to recreate the hash from the database
			$bCheck = $this->_checkPassword($oUser, $p_sPassword);
			if(!$bCheck)
				return false;
			
			// The user exists and has filled in the right password, yay! Now we generate a new hash for the password and store in in the database (optional, for extra security)
			if(Settings::get('general.rehashAtLogon') == true)
				$this->_regeneratePassword($oUser, $p_sPassword);
			unset($p_sPassword);
			
			// Now we are ready to generate the token and store it in a cookie
			$this->_createToken($oUser->userId);
			
			// That was it, we're done here!
			return true;
		}
		
		public function checkLogin() {
			$oToken = new Token();
			$oToken->deleteOldTokens();
			
			// This method will return a user object if all goes well, or false if not
			
			// First, check it both tokens are present
			$bResult = $this->_checkCookies();
			if(!$bResult)
				return false;
			
			// Now we will load the token from the database and check it
			$oUser = $this->_checkToken();
			return $oUser;
		}
		
		public function logout() {
			$oToken = new Token();
			$oToken->deleteOldTokens();
			
			// Firstly, check if the cookies are there. If not, we don't need to logout anyone
			$bCookies = $this->checkLogin();
			
			// Now delete the token
			if($bCookies) {
				$oToken = new Token();
				$oToken->loadToken($_COOKIE[Settings::get('cookies.DBCookieName')]);
				$oToken->deleteToken();
			}
			
			return true;
		}
		
		protected function _getUser($p_sUsername) {
			$oUser = new User();
			$bResult = $oUser->findUser($p_sUsername);
			
			if(!$bResult)
				return false;
			
			return $oUser;
		}
		
		protected function _checkPassword(&$p_oUser, $p_sPassword) {
			if(Settings::get('hashing.encryptSalt')) {
				$oAES = new AES();
				$sSalt = $oAES->decrypt($p_oUser->salt, $p_sPassword);
			} else {
				$sSalt = $p_oUser->salt;
			}
			
			$oBCrypt = new BCrypt();
			$sPassword = $oBCrypt->genPassword($p_sPassword, $sSalt);
			unset($p_sPassword);
			
			if($p_oUser->password == $sPassword)
				return true;
			
			return false;
		}
		
		protected function _regeneratePassword(&$p_oUser, $p_sPassword) {
			$oBCrypt = new BCrypt();
			$p_oUser->password = $oBCrypt->genPassword($p_sPassword);
			
			if(Settings::get('hashing.encryptSalt')) {
				$oAES = new AES();
				$p_oUser->salt = $oAES->encrypt($oBCrypt->salt, $p_sPassword);
			} else {
				$p_oUser->salt = $oBCrypt->salt;
			}
			unset($p_sPassword);
			
			$p_oUser->saveUser();
		}
		
		protected function _createToken($p_sUserId) {
			$oToken = new Token();
			$sUserToken = $oToken->genToken($p_sUserId);
			$oToken->saveToken();
			$sDBToken = $oToken->token;
			setcookie(Settings::get('cookies.userCookieName'), $sUserToken, 0, '/');
			setcookie(Settings::get('cookies.DBCookieName'), $sDBToken, 0, '/');
		}
		
		protected function _checkCookies() {
			if(!isset($_COOKIE[Settings::get('cookies.userCookieName')]) || !isset($_COOKIE[Settings::get('cookies.DBCookieName')]))
				return false;
			
			if(strlen($_COOKIE[Settings::get('cookies.userCookieName')]) != Settings::get('cookies.tokenLength') || strlen($_COOKIE[Settings::get('cookies.DBCookieName')]) != Settings::get('cookies.tokenLength'))
				return false;
			
			return true;
		}
		
		protected function _checkToken() {
			$oToken = new Token();
			$bSuccess = $oToken->loadToken($_COOKIE[Settings::get('cookies.DBCookieName')]);
			if(!$bSuccess)
				return false;
			
			$vReturn = $oToken->checkToken($_COOKIE[Settings::get('cookies.userCookieName')]);
			if($vReturn === false) {
				$oToken->deleteToken();
				setcookie(Settings::get('cookies.userCookieName'), '', time()-3600);
				setcookie(Settings::get('cookies.DBCookieName'), '', time()-3600);
			}
			
			//All is good, now update the time
			$oToken->expires = time() + Settings::get('login.expireTime');
			$oToken->saveToken();
			
			return $vReturn;
		}
	}
?>