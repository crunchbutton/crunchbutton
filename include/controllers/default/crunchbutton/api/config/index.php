<?php

class Controller_api_config extends Crunchbutton_Controller_Rest {
	public function init() {
		switch ($this->method()) {
			case 'post':
				if ($this->request()['ab']) {
					echo 'saving';
					print_r($this->request()['ab']);
					c::auth()->set('ab', json_encode($this->request()['ab']));
				}
				break;
			case 'get':
				$config = c::appConfig();
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