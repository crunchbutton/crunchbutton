<?php

class Controller_api_config extends Crunchbutton_Controller_Rest {
	public function init() {
		
		if (getenv('HEROKU')) {
			exit;
			error_log('>> DISPLAYING CONFIG...');
		}
		
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

				echo json_encode($config);
				break;
		}
	}
}
