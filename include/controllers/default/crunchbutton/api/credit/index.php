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
						
						// id_restaurant == * means any restaurant
						if( $this->request()['id_restaurant'] == '*' ){
							$credit->note = 'This credit is valid to any restaurant!' . "\n" . $this->request()['note'];
						} else {
							$credit->id_restaurant = $this->request()['id_restaurant'];
							$credit->note = $this->request()['note'];
						}
						$credit->date = date('Y-m-d H:i:s');
						$credit->value = $this->request()['value'];
						
						$credit->id_order_reference = $this->request()['id_order_reference'];
						$credit->paid_by = $this->request()['paid_by'];
						if( $this->request()['paid_by'] == 'other_restaurant' ){
							$credit->id_restaurant_paid_by = $this->request()['id_restaurant_paid_by'];
						}
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