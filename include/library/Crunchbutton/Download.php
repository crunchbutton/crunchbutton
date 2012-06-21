<?php

class Crunchbutton_Download extends Cana_File {

	public function __construct($params) {
		parent::__construct($params);

	}
	
	public function get($attachment = false, $resume = true, $log = false) {
		parent::get($attachment, $resume);
	}
}