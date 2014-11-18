<?php

class Controller_api_config extends Crunchbutton_Controller_Rest {
	public function init() {
		switch ($this->method()) {
			case 'get':

				$user = c::user()->exports();
				if( c::user()->id_admin ){
					$user[ 'invite_code' ] = c::user()->inviteCode();
				}

				$config = [
					'user' => $user,
					'env' => c::env(),
					'version' => Cana_Util::gitVersion(),
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
