<?php

class Controller_api_referral extends Crunchbutton_Controller_Rest {
	public function init() {

		switch (c::getPagePiece(2)) {

			case 'code':
				if( c::user()->id_user && c::user()->id_user != '' ){
					$invite_code = c::user()->inviteCode();
					$url = 'http://' . $_SERVER['HTTP_HOST'] . '/invite/' . $invite_code;
					echo json_encode(['invite_url' => $url ]);
				} else {
					echo json_encode(['error' => 'invalid request']);
				}
			break;

			case 'value':
				if( c::user()->id_user && c::user()->id_user != '' ){
					$value = Crunchbutton_Referral::getInviterCreditValue();
					echo json_encode(['value' => intval( $value ) ]);
				} else {
					echo json_encode(['error' => 'invalid request']);
				}
			break;

			default:
				echo json_encode(['error' => 'invalid request']);
		}
	}
}
