<?php

class Controller_api_user_orders extends Crunchbutton_Controller_Rest {
	public function init() {
		switch ($this->method()) {
			case 'get':
				$orders = c::user()->orders();
				echo $orders->json();
				
				break;

		}
	}
}