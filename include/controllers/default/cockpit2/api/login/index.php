<?php

class Controller_api_login extends Crunchbutton_Controller_Rest {

	public function init() {
		$user = c::auth()->doAuthByLocalUser(['email' => $this->request()['username'], 'password' => $this->request()['password']]);
		if ($user) {
			if( $this->request()[ 'native' ] ){
				Cockpit_Driver_Log::nativeAppLogin();
			}
			$this->export();
		} else {
			echo json_encode(['error' => 'invalid login']);
		}
	}

	public function export(){
		$user = c::user()->exports();
		if( c::user()->id_admin ){
			$user[ 'invite_code' ] = c::user()->inviteCode();
			$user['working'] = c::user()->isWorking();
		}

		$reward = new Crunchbutton_Reward();
		$settings = $reward->loadSettings();

		$user['referral_customer_credit'] = ( c::user()->referral_customer_credit ? c::user()->referral_customer_credit : $settings[ Crunchbutton_Reward::CONFIG_KEY_GET_REFERRED_DISCOUNT_AMOUNT ] );
		$user['referral_admin_credit'] = ( c::user()->referral_admin_credit ? c::user()->referral_admin_credit : $settings[ Crunchbutton_Reward::CONFIG_KEY_ADMIN_REFER_USER_AMOUNT ] );
		$user['isMarketingRep'] = c::user()->isMarketingRep();
		$user['isCampusManager'] = c::user()->isCampusManager();

		$payment_type = c::user()->payment_type();
		if( $payment_type->using_pex ){
			$user[ 'using_pex' ] = true;
		}

		echo json_encode( $user );exit;

	}

}