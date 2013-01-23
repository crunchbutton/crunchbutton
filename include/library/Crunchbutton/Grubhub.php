<?php

class Crunchbutton_Grubhub extends Cana_Model {
	public function _get($url) {
		if (Cana::app()->cache()->cached($url)) {
			$data = Cana::app()->cache()->read($url);
		} else {
			$data = @file_get_contents($url);
			Cana::app()->cache()->write($url, $data);
		}
		return $data;
	}
}