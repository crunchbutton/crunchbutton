<?php

class Controller_api_community extends Crunchbutton_Controller_RestAccount {
	public function init() {
		switch ($this->method()) {
			case 'get':
				
				echo json_encode($config);
				break;
		}
	}
}
