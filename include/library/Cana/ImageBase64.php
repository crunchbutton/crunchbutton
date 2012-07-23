<?php

class Cana_ImageBase64 {
	public function __construct($image = null) {
		if (is_null($image))
			return;

		$this->_file = $image;
		$fp = fopen($this->_file,'rb', 0);
		if (!$fp) {
			throw new Exception($image.' does not exist');
		}
		$picture = fread($fp,filesize($this->_file));
		fclose($fp);
		$this->_encoded = base64_encode($picture);
	}
	
	public function output() {
		return 'data:'.mime_content_type($this->_file).';base64,'.$this->_encoded;
	}
	
	public function __toString() {
		return $this->output();
	}
}