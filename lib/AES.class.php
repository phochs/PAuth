<?php
	namespace PAuth;
	/**
	 * Source: https://stackoverflow.com/questions/3422759/php-aes-encrypt-decrypt
	 */
	class AES {
		protected $m_sPasswordFiller = ';1+ZRwZwkorSGZQu+FgPAFvPFbX-azkY';
		
		public function encrypt($p_sValue, $p_sSecretKey) {
			$p_sSecretKey = $this->parseSecret($p_sSecretKey);
			return rtrim(
				base64_encode(
					mcrypt_encrypt(
						MCRYPT_RIJNDAEL_256,
						$p_sSecretKey, $p_sValue, 
						MCRYPT_MODE_ECB, 
						mcrypt_create_iv(
							mcrypt_get_iv_size(
								MCRYPT_RIJNDAEL_256, 
								MCRYPT_MODE_ECB
							), 
							MCRYPT_RAND)
						)
					), "\0"
				);
		}
		
		public function decrypt($p_sValue, $p_sSecretKey) {
			$p_sSecretKey = $this->parseSecret($p_sSecretKey);
			return rtrim(
				mcrypt_decrypt(
					MCRYPT_RIJNDAEL_256, 
					$p_sSecretKey, 
					base64_decode($p_sValue), 
					MCRYPT_MODE_ECB,
					mcrypt_create_iv(
						mcrypt_get_iv_size(
							MCRYPT_RIJNDAEL_256,
							MCRYPT_MODE_ECB
						), 
						MCRYPT_RAND
					)
				), "\0"
			);
		}
		
		protected function parseSecret($p_sSecretKey) {
			if(in_array(strlen($p_sSecretKey), array(16, 24, 32)))
				return $p_sSecretKey;
			
			if(strlen($p_sSecretKey) < 16) {
				$iLength = 16;
			} elseif(strlen($p_sSecretKey) < 24) {
				$iLength = 24;
			} else {
				$iLength = 32;
			}
			
			return substr($p_sSecretKey.$this->m_sPasswordFiller, 0, $iLength);
		}
	}
?>