<?php

class Controller_api_v1_server extends Crunchbutton_Controller_Rest {
	public function init() {
		switch ($this->method()) {
			case 'get':
				$server = Server::o(c::getPagePiece(3));
				if (!$server->id_server) {
					echo json_encode(['error' => 'invalid resource']);
					exit;
				}
				echo $server->json();
				break;

		}
	}
}