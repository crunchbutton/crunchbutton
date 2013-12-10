<?php

class Controller_api_user_orders extends Crunchbutton_Controller_Rest {
	public function init() {
		switch ($this->method()) {
			case 'get':


				$orders = c::user()->orders('compact');
				if ( method_exists( $orders, 'count' ) && $orders->count() > 0) {
					echo $orders->json();
					Log::debug( [ 'id_user' => c::user()->id_user, 'orders' => $orders->count(), 'type' => 'api orders' ]);
				} else {
					echo json_encode( [] );
					Log::debug( [ 'id_user' => c::user()->id_user, 'orders' => 0, 'type' => 'api orders' ]);
				}
				break;

		}
	}
}