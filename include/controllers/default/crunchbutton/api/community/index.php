<?php

class Controller_api_community extends Crunchbutton_Controller_Rest {
	public function init() {
		switch ($this->method()) {
			case 'get':
				$out = Community::o(1)->exports();
				print_r($out); exit;
				echo json_encode($out);
				
				break;

		}
	}
}