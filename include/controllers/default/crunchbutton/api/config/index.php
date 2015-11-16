<?php

class Controller_api_config extends Crunchbutton_Controller_Rest {
	public function init() {

		switch ($this->method()) {
			case 'post':
				if ($this->request()['ab']) {
					c::auth()->set('ab', json_encode($this->request()['ab']));
				} else {
					$key = strtolower($this->request()['key']);
					$value = $this->request()['value'];
					switch ($key) {
						case 'user-push-ios':
						case 'user-push-android':
							c::user()->setPush($value, $key == 'user-push-ios' ? 'ios' : 'android');
							break;
					}
				}
				break;

			case 'get':
				$p = ['base'];
				if (c::getPagePiece(2) == 'extended') {
					$p[] = 'extended';
				}
				$config = c::appConfig($p);

				if ($_REQUEST['lat'] && $_REQUEST['lon']) {
					$restaurants = Restaurant::byRange([
						'lat' => $_REQUEST['lat'],
						'lon' => $_REQUEST['lon']
					]);
					foreach ($restaurants as $restaurant) {
						$config['restaurants'][] = $restaurant->exports();
					}
				}
				$config['session'] = c::auth()->session()->id;


				if( c::user()->id_user && c::user()->id_user != '' ){

					if( c::user()->id_user ){
						$users = Crunchbutton_Referral::newReferredUsersByUser( c::user()->id_user );
						$_users = [];
						if( $users ){
							foreach( $users as $user ){
								$_users[] = $user->name;
							}
							$config[ 'new_referral_users' ] = join( $_users, ', ' );
						}
					}

					$invite_code = c::user()->inviteCode();
					$invites = Crunchbutton_Referral::getInvitesPerCode( $invite_code );
					$limit = Crunchbutton_Referral::getInvitesLimit();
					$enabled = Crunchbutton_Referral::isReferralEnable();
					// $url = 'http://' . $_SERVER['HTTP_HOST'] . '?invite=' . $invite_code;
					$url = 'http://' . $_SERVER['HTTP_HOST'] . '/invite/' . $invite_code;
					$value = Crunchbutton_Referral::getInviterCreditValue();
					$config[ 'referral' ] = ['invites' => intval( $invites ), 'limit' => intval( $limit ), 'invite_url' => $url, 'value' => intval( $value ), 'enabled' => $enabled, 'invite_code' => $invite_code ];
				}

				echo json_encode($config);
				break;
		}
	}
}
