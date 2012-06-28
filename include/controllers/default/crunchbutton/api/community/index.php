<?php

class Controller_api_community extends Crunchbutton_Controller_Rest {
	public function init() {
		switch ($this->method()) {
			case 'get':
				$out = Community::o(c::getPagePiece(2));
				if (!$out->id_community) {
					$out = Community::permalink(c::getPagePiece(2));
				}
				
				if ($out->id_community) {
					echo $out->json();
				} else {
					echo json_encode(['error' => 'invalid object']);
				}
									
				break;
		}
	}
}