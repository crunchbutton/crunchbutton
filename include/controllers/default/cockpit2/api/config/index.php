<?php

class Controller_api_config extends Crunchbutton_Controller_Rest {
	public function init() {
		switch ($this->method()) {
			case 'get':

				$config = [
					'user' => c::user()->exports(),
					'env' => c::env(),
					'version' => Cana_Util::gitVersion(),
					'site' => c::config()->site->exposedConfig()
				];

				echo json_encode($config);
				break;
		}
	}
}
