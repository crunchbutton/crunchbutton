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

				echo json_encode($config);
				break;
		}
	}
}
