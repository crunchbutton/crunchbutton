<?php

class Controller_api_user_orders extends Crunchbutton_Controller_Rest {
	public function init() {
		switch ($this->method()) {
			case 'get':
				$orders = c::user()->orders('compact');
				if ( method_exists( $orders, 'count' ) && $orders->count() > 0) {
					echo $orders->json();
				} else {
					//echo json_encode([]);
				}
				break;

		}
	}
}