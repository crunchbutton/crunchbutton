<?php

/**
 * an mcrypt wrapper class
 *
 * @author		Devin Smith (devin@cana.la)
 * @date		2009.12.09
 *
 * A basic mcrypt wrapper
 *
 */


class Cana_Crypt extends Cana_Model {
	const CYPHER_METHOD = MCRYPT_3DES;
	const CYPHER_MODE = MCRYPT_MODE_ECB;

	public function __construct($key) {
		$this->iv_size = mcrypt_get_iv_size(self::CYPHER_METHOD, self::CYPHER_MODE);
		$this->iv = mcrypt_create_iv($this->iv_size, MCRYPT_RAND);
		$this->key = $key;
	}

	public function encrypt($text) {
		if (!$text) return null;
		return base64_encode(mcrypt_encrypt(self::CYPHER_METHOD, $this->key, $text, self::CYPHER_MODE, $this->iv));
	}

	public function decrypt($text) {
		if (!$text) return null;
		return trim(mcrypt_decrypt(self::CYPHER_METHOD, $this->key, base64_decode($text), self::CYPHER_MODE, $this->iv));
	}
}
