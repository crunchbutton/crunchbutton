<?php

class Controller_api_v1_user extends Crunchbutton_Controller_Rest {
	public function init() {
		switch ($this->method()) {
			case 'get':
				$user = User::o(c::getPagePiece(3));
				if (!$user->id_user) {
					echo json_encode(['error' => 'invalid resource']);
					exit;
				}
				echo $user->json();
				break;

		}
	}
}