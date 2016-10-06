<?php

class Controller_api_customer extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if (!c::admin()->permission()->check(['global', 'support-all', 'support-view', 'support-crud', 'community-cs','community-director'])) {
			$this->error(401, true);
		}

		$customer = User::uuid(c::getPagePiece(2));

		if (!$customer->id_user) {
			$customer = User::o(c::getPagePiece(2));
		}

		if (!$customer->id_user) {
			$customer = User::byPhone(c::getPagePiece(2), false);
		}
/*
		if (get_class($customer) != 'Crunchbutton_User') {
			$customer = $customer->get(0);
		}
*/

		if (!$customer->id_user) {
			$this->error(404, true);
		}

		$this->customer = $customer;

		switch (c::getPagePiece(3)) {
			default:
				$this->_customer();
				break;
		}

	}

	private function _customer() {
		switch ($this->method()) {
			case 'get':

				switch ( c::getPagePiece( 3 ) ) {
					case 'active-orders':
						return $this->_active_orders();
						break;

					default:
						echo $this->customer->json();
						break;
				}

				break;
			case 'post':
				$this->_save();
				break;
		}
	}

	private function _save(){

		$this->customer->name = $this->request()[ 'name' ];
		$this->customer->phone = $this->request()[ 'phone' ];
		$this->customer->address = $this->request()[ 'address' ];
		$this->customer->email = $this->request()[ 'email' ];
		$this->customer->active = $this->request()[ 'active' ];

		$phone = Phone::byPhone( $this->request()[ 'phone' ] );

		$this->customer->id_phone = $phone->id;

		if( $this->request()[ 'remove_credit_card' ] ){
			$paymentType = $this->customer->payment_type();
			$paymentType->active = 0;
			$paymentType->save();
		}

		$this->customer->save();

		$orders = $this->_active_orders( false );
		foreach( $orders as $order ){

			$old_name = $order->name;
			$old_phone = $order->phone;
			$old_address = $order->address;

			$order->name = $this->customer->name;
			$order->address = $this->customer->address;
			$order->phone = $this->customer->phone;
			$order->id_phone = $phone->id;
			$order->save();
			if( $this->request()[ 'notify_driver' ] ){
				$notify = [];
				if( $old_phone != $order->phone ){
					$notify[ 'phone' ] = $order->phone;
				}
				if( $old_address != $order->address ){
					$notify[ 'address' ] = $order->address;
				}
				if( $old_name != $order->name ){
					$notify[ 'name' ] = $order->name;
				}
				$order->notifyDriverAboutCustomerChanges( $notify );
			}
		}
		echo json_encode( [ 'success' => true ] );exit;
	}

	private function _active_orders( $json = true ){
		$orders = Order::q( 'SELECT DISTINCT(o.id_order), o.* FROM `order` o
													INNER JOIN order_action oa ON o.id_order = oa.id_order AND ( oa.type = ? || oa.type = ? )
													WHERE o.id_user = ? AND o.refunded = 0
													ORDER BY o.id_order ASC',
													[
														Crunchbutton_Order_Action::DELIVERY_PICKEDUP,
														Crunchbutton_Order_Action::DELIVERY_ACCEPTED,
														$this->customer->id_user ] );
		if( $json ){
			$out = [];
			foreach( $orders as $order ){
				$status = $order->status()->last();
				$out[] = [	'id_order' => $order->id_order,
										'final_price_plus_delivery_markup' => $order->final_price_plus_delivery_markup,
										'delivery_fee' => $order->delivery_fee,
										'delivery_service_markup_value' => $order->delivery_service_markup_value,
										'price' => $order->price,
										'service_fee' => $order->service_fee,
										'price' => $order->price,
										'delivery_service' => $order->delivery_service,
										'date' => $order->date,
										'id_restaurant' => $order->id_restaurant,
										'_restaurant_permalink' => $order->restaurant()->permalink,
										'_restaurant_name' => $order->restaurant()->name,
										'pay_type' => $order->pay_type,
										'status' => $status,
										'name' => $order->name,
										'id_user' => $order->id_user
									];
			}
			echo json_encode( $out );exit;
		}
		return $orders;
	}

}