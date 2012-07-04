<?php

class Controller_api_user_orders extends Crunchbutton_Controller_Rest {
	public function init() {
		switch ($this->method()) {
			case 'get':
				$orders = Order::q('select * from `order` where id_user="'.c::user()->id_user.'" and id_user is not null');
				echo $orders->json();
				
				break;

		}
	}
}