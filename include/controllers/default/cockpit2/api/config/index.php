<?php

class Controller_api_config extends Crunchbutton_Controller_Rest {
	public function init() {
		switch ($this->method()) {
			case 'get':

				$user = c::user()->exports();
				if( c::user()->id_admin ){
					$user[ 'invite_code' ] = c::user()->inviteCode();
					$user['working'] = c::user()->isWorking();
					$user[ 'hours_since_last_shift' ] = c::user()->getLastWorkedTimeHours();
				}

				$reward = new Crunchbutton_Reward();
				$settings = $reward->loadSettings();

				$user['referral_customer_credit'] = ( c::user()->referral_customer_credit ? c::user()->referral_customer_credit : $settings[ Crunchbutton_Reward::CONFIG_KEY_GET_REFERRED_DISCOUNT_AMOUNT ] );
				$user['referral_admin_credit'] = ( c::user()->referral_admin_credit ? c::user()->referral_admin_credit : $settings[ Crunchbutton_Reward::CONFIG_KEY_ADMIN_REFER_USER_AMOUNT ] );
				$user['isMarketingRep'] = c::user()->isMarketingRep();
				$user['isCampusManager'] = c::user()->isCampusManager();
				$user['isCommunityDirector'] = c::user()->isCommunityDirector();
				$user['isDriver'] = c::user()->isDriver();

				$payment_type = c::user()->payment_type();

				if( c::user()->isCampusManager() ){
					$user['profit_percent'] = $payment_type->profit_percent;
				}

				$user['has_resource'] = c::user()->hasResource();
				$user['side_resource'] = c::user()->hasResource( 'side' );
				$user['has_community_to_open'] = c::user()->hasCommunityToOpen();
				$user['has_community_to_close'] = c::user()->hasCommunityToClose();

				if( $payment_type->using_pex ){
					$user[ 'using_pex' ] = true;
					$pex = Cockpit_Admin_Pexcard::getByAdmin( c::user()->id_admin )->get( 0 );
					if( $pex->id_admin_pexcard ){
						$user['pexcard'] = [
							'card_serial' => $pex->card_serial,
							'last_four' => $pex->last_four,
							'active' => $pex->card_serial && $pex->card_serial ? true : false
						];
						$user['has_pexcard'] = true;
					} else {
						$user['has_pexcard'] = false;
					}
				}

				$config = [
					'user' => $user,
					'env' => c::env(),
					'version' => $_ENV['HEROKU_SLUG_COMMIT'],
					'site' => c::config()->site->exposedConfig()
				];

				if ($this->request()['init']) {
					$config['timezones'] = json_decode(file_get_contents(c::config()->dirs->www.'assets/cockpit/js/moment-timezone-db.json'));
				}
				/*
				a different way of doing permissions that we should remove
				if ($this->request()['permissions']) {
					$config['permissions'] = [];
					$perms = c::admin()->getAllPermissionsName();
					foreach ($perms as $perm) {
						$config['permissions'][] = $perm->permission;
					}
				}
				*/


				echo json_encode($config);
				break;

			case 'post':
				$key = strtolower($this->request()['key']);
				$value = $this->request()['value'];
				if (!$key) {
					echo json_encode(['error' => 'nothing to save']);
					exit;
				}
				switch ($key) {
					case 'push-ios':
					case 'push-android':
						c::admin()->setPush($value, $key == 'push-ios' ? 'ios' : 'android');
						break;
					default:
						c::admin()->setConfig($key, $value, 1);
						break;
				}
				break;
		}
	}
}
