<?php

class Controller_api_Credit extends Crunchbutton_Controller_Rest {
	public function init() {

		switch ( $this->method() ) {
			
			// Saves a credit
			case 'post':
				if (c::getPagePiece(2) == 'new') {
					if ($_SESSION['admin']) {
						$credit = new Crunchbutton_Credit();
						$credit->id_user = $this->request()['id_user'];
						$credit->type = Crunchbutton_Credit::TYPE_CREDIT;
						$credit->id_restaurant = $this->request()['id_restaurant'];
						$credit->date = date('Y-m-d H:i:s');
						$credit->value = $this->request()['value'];
						$credit->note = $this->request()['note'];
						$credit->save();
						if( $credit->id_credit ){
							echo $credit->json();
						} else {
							echo json_encode(['error' => 'credit not added']);
						}
					} else {
						echo json_encode(['error' => 'invalid object']);
					}
				}
			break;
			default:
				echo json_encode(['error' => 'invalid object']);
			break;
		}
	}
}