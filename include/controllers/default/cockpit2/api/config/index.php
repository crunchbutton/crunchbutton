<?php

class Controller_api_config extends Crunchbutton_Controller_Rest {
	public function init() {
		switch ($this->method()) {
			case 'get':

				$user = c::user()->exports();
				if( c::user()->id_admin ){
					$user[ 'invite_code' ] = c::user()->inviteCode();
					$user['working'] = c::user()->isWorking();
					$user['isMarketingRep'] = c::user()->isMarketingRep();
				}

				$payment_type = c::user()->payment_type();
				if( $payment_type->using_pex ){
					$user[ 'using_pex' ] = true;
				}

				// $user[ 'using_pex' ] = true;


				$config = [
					'user' => $user,
					'env' => c::env(),
					'version' => Deploy_Server::currentVersion(),
					'site' => c::config()->site->exposedConfig()
				];

				if ($this->request()['init']) {
					$config['timezones'] = json_decode(file_get_contents(c::config()->dirs->www.'assets/cockpit/js/moment-timezone-db.json'));
				}

				echo json_encode($config);
				break;

			case 'post':
				$key = strtolower($_REQUEST['key']);
				switch ($key) {
					case 'push-ios':
					case 'push-android':
						c::admin()->setPush($_REQUEST['value'], $key == 'push-ios' ? 'ios' : 'android');
						break;
					default:
						c::admin()->setConfig($key, $_REQUEST['value'], 1);
						break;
				}
				break;
		}
	}
}
