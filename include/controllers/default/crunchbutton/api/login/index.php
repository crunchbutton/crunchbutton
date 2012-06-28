<?php

class Controller_api_v1_login extends Crunchbutton_Controller_Rest {
	public function init() {
		switch ($this->method()) {
			case 'get':
				$user = Crunchbutton_User::o(c::getPagePiece(2));
				if (!$file->id_file) {
					echo json_encode(['error' => 'invalid resource']);
					exit;
				}
				break;

		}
	}
}