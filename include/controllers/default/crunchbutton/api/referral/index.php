<?php

class Controller_api_referral extends Crunchbutton_Controller_Rest {
	public function init() {

		switch (c::getPagePiece(2)) {

			case 'save':
				if ( $_SESSION['admin'] && $this->method() == 'post' ) {

					Crunchbutton_Config::store( Crunchbutton_Referral::KEY_IS_REFERRAL_ENABLE, $this->request()['enabled'] );
					Crunchbutton_Config::store( Crunchbutton_Referral::KEY_INVITER_CREDIT_VALUE, $this->request()['inviter_credit'] );
					Crunchbutton_Config::store( Crunchbutton_Referral::KEY_INVITED_CREDIT_VALUE, $this->request()['invited_credit'] );
					Crunchbutton_Config::store( Crunchbutton_Referral::KEY_ADD_CREDIT_INVITED, $this->request()['add_credit_invited'] );
					Crunchbutton_Config::store( Crunchbutton_Referral::KEY_INVITES_LIMIT_PER_CODE, $this->request()['limit'] );

					echo json_encode(['success' => 'success']);
				}
			break;

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

			case 'status':
				if( c::user()->id_user && c::user()->id_user != '' ){
					$invite_code = c::user()->inviteCode();
					$invites = Crunchbutton_Referral::getInvitesPerCode( $invite_code );
					$limit = Crunchbutton_Referral::getInvitesLimit();
					$enabled = Crunchbutton_Referral::isReferralEnable();
					$url = 'http://' . $_SERVER['HTTP_HOST'] . '/invite/' . $invite_code;
					$value = Crunchbutton_Referral::getInviterCreditValue();
					echo json_encode(['invites' => intval( $invites ), 'limit' => intval( $limit ), 'invite_url' => $url, 'value' => intval( $value ), 'enabled' => $enabled ]);
				} else {
					echo json_encode(['error' => 'invalid request']);
				}
			break;

			default:
				echo json_encode(['error' => 'invalid request']);
		}
	}
}
