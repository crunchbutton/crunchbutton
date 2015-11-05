<?php

class Controller_api_order extends Crunchbutton_Controller_RestAccount {

	public function init() {

		$restaurant = Admin::restaurantOrderPlacement();

		// list recent orders for restaurants
		// @todo: move this over to orders php
		if (c::getPagePiece(2) == 'restaurant-list-last') {
			if (is_numeric(c::getPagePiece(3)) && c::admin()->permission()->check(['global'])) {
				$restaurant = Restaurant::o(intval(c::getPagePiece(3)));
			}

			if ($restaurant->id_restaurant) {
				$out = [ 'id_restaurant' => intval( $restaurant->id_restaurant ), 'orders' => [] ];
				$orders = Order::q( 'SELECT * FROM `order` o WHERE id_restaurant = "' . $restaurant->id_restaurant . '" AND o.date BETWEEN NOW() - INTERVAL 7 DAY AND NOW() ORDER BY id_order DESC' );
				foreach( $orders as $order ) {
					$out[ 'orders' ][]	= array( 	'id_order' => $order->id_order,
																				'lastStatus' => $order->deliveryLastStatus(),
																				'name' => $order->name,
																				'phone' => $order->phone,
																				'date' => $order->date()->format( 'M jS Y g:i:s A' ),
																		);
				}
				echo json_encode( $out );
			} else {
				echo json_encode(['error' => 'invalid object']);
			}

			exit;
		}

		// post an order
		if (!c::getPagePiece(2) && $this->method() == 'post') {

			if (is_numeric($_POST['restaurant']) && c::admin()->permission()->check(['global'])){
				$restaurant = Restaurant::o(intval($_POST['restaurant']));
			}

			if ($restaurant && $restaurant->id_restaurant && $_POST[ 'restaurant' ] == $restaurant->id_restaurant) {
				$order = new Order;
				// card, subtotal, tip, name, phone, address
				$charge = $order->process( $_POST, 'restaurant' );
				if ($charge === true) {
					echo json_encode([
						'id_order' => $order->id_order,
						'id_user' => $order->user()->id_user,
						'final_price' => $order->final_price,
						'uuid' => (new Order($order->id_order))->uuid
					]);
				} else {
					echo json_encode(['status' => 'false', 'errors' => $charge]);
				}
			} else {
				echo json_encode(['status' => 'false', 'errors' => 'invalid request' ] );
			}
			exit;
		}

		$order = Order::uuid(c::getPagePiece(2));

		if (!$order->id_order) {
			$order = Order::o(c::getPagePiece(2));
		}

		if (get_class($order) != 'Cockpit_Order') {
			$order = $order->get(0);
		}

		if (!$order->id_order) {
			$this->error(404);
		}

		if (!c::admin()->permission()->check(['global','orders-all','orders-list-page']) && $restaurant->id_restaurant != $order->id_restaurant) {
			$this->error(401);
		}

		// update an order
		if ($this->method() == 'put') {

			$allowed = ['lat','lon','notes','address','phone','name'];
			$changed = false;
			foreach ($this->request() as $k => $v) {
				if (in_array($k, $allowed)) {
					$order->{$k} = $v;
					$changed = true;
				}
			}

			if ($changed) {
				$order->save();
			}
		}

		switch (c::getPagePiece(3)) {

			case 'refund-info':
				if (!c::admin()->permission()->check(['global', 'support-all', 'support-view', 'support-crud'])) {
					$this->error(401);
				}

				$out[ 'id_order' ] = $order->id_order;
				$out[ 'charged' ] = $order->charged();
				$out[ 'credit' ] = $order->chargedByCredit();
				$out[ 'status' ] = $order->status()->last();

				echo json_encode($out);

				break;

			case 'campus-cash':
				if (!c::admin()->permission()->check(['global', 'campus-cash'])) {
					$this->error(401);
				}
				if ($this->method() == 'post') {
					$sha1 = $this->request()[ 'sha1' ];
					echo json_encode( [ 'success' => $order->campus_cash_studentID( $sha1 ) ] );
				} else {
					$this->error(401);
				}
				break;
			case 'refund':
				if (!c::admin()->permission()->check(['global', 'support-all', 'support-view', 'support-crud'])) {
					$this->error(401);
				}

				$reason = $this->request()[ 'reason' ];
				$tell_driver = $this->request()[ 'tell_driver' ];
				$cancel_order = $this->request()[ 'cancel_order' ];
				$amount = floatval( $this->request()[ 'amount' ] );

				if( !floatval( $amount ) ){
					echo json_encode( [ 'error' => 'The refund amount should be at least $0.01.' ] );exit;
				}

				if( floatval( $amount ) > $order->charged() ){
					echo json_encode( [ 'error' => 'The refund amount should be equal or less than $' . $order->charged() . '.' ] );exit;
				}


				if( $this->request()[ 'reason_other' ] && $reason == 'Other' ){
					$reason = $this->request()[ 'reason_other' ];
				}

				$status = $order->refund( $amount, $reason, $tell_driver );

				if( $status ){

					if( $cancel_order ){
						$order->setStatus( Crunchbutton_Order_Action::DELIVERY_CANCELED, false );
					}
					echo json_encode( [ 'success' => true ] );
				} else {
					echo json_encode( [ 'error' => true ] );
				}
				break;

			case 'do_not_reimburse_driver':
				if (!c::admin()->permission()->check(['global', 'support-all'])) {
					$this->error(401);
				}
				$order->do_not_reimburse_driver = ( $order->do_not_reimburse_driver == 1 ? false : true );
				$order->save();
				echo json_encode( [ 'success' => true ] );
				break;

			case 'do_not_pay_driver':
				if (!c::admin()->permission()->check(['global', 'support-all'])) {
					$this->error(401);
				}
				$order->do_not_pay_driver = ( $order->do_not_pay_driver == 1 ? false : true );
				$order->do_not_reimburse_driver = $order->do_not_pay_driver;
				$order->save();
				echo json_encode( [ 'success' => true ] );
				break;

			case 'do_not_pay_restaurant':
				if (!c::admin()->permission()->check(['global', 'support-all'])) {
					$this->error(401);
				}
				$order->do_not_pay_restaurant = ( $order->do_not_pay_restaurant == 1 ? 0 : 1 );
				$order->save();
				echo json_encode( [ 'success' => true ] );
				break;

			case 'mark_cash_card_charged':
				if (!c::admin()->permission()->check(['global', 'support-all'])) {
					$this->error(401);
				}
				if( $order->mark_cash_card_charged() ){
					echo json_encode( [ 'success' => true ] );
				} else {
					echo json_encode( [ 'error' => true ] );
				}
				break;

			case 'resend_notification':
				if ( !c::admin()->permission()->check(['global','orders-all','orders-notification'])) {
					$this->error(401);
				}
				echo json_encode(['status' => $order->resend_notify() ? 'success' : 'error']);

				break;

			case 'resend_notification_drivers':
				if ( !c::admin()->permission()->check(['global','orders-all','orders-notification'])) {
					$this->error(401);
				}
				echo json_encode(['status' => $order->resend_notify_drivers() ? 'success' : 'error']);

				break;

			case 'eta':
				if (c::getPagePiece(4) == 'refresh') {
					$order->eta(true);
				}
				if ($this->method() == 'post') {
					$eta = new Order_Eta([
						'id_order' => $order->id_order,
						'method' => $this->request()['method'],
						'time' => $this->request()['time'],
						'distance' => $this->request()['distance'],
						'date' => date('Y-m-d H:i:s')
					]);
					$eta->save();
				}
				echo $order->eta()->json();
				break;

			case 'status':
				echo json_encode($order->status()->last());
				break;

			case 'text-5-min-away':
				$order->textCustomer( Cockpit_Order::I_AM_5_MINUTES_AWAY, true );
				echo json_encode(['success' => 'true']);
				break;

			case 'status-change':


				$new_status = $this->request()[ 'status' ];

				if( $new_status == 'canceled' ){
					$admin = Admin::o( c::user()->id_admin );
					$notify_customer = false;
				} else {
					$id_admin = $this->request()[ 'id_admin' ];
					$notify_customer = intval( $this->request()[ 'notify_customer' ] ) == 1 ? true : false;
					$admin = Admin::o( $id_admin );
				}

				if( !$admin->id_admin || trim( $new_status ) == '' ){
					$this->error( 404 );
				}

				$note = 'Status changed by ' . c::user()->name . ' (' . c::user()->login . ')';

				$change = false;

				$status = $order->status()->last();
				if( $status[ 'driver' ] ){
					if( $status[ 'driver' ][ 'id_admin' ] != $admin->id_admin ){
						// Set order as rejected
						$driver = Admin::o( $status[ 'driver' ][ 'id_admin' ] );
						$order->setStatus( Crunchbutton_Order_Action::DELIVERY_REJECTED, false, $driver, $note, false, false );
						$order->clearStatus();
						$change = true;
					}
				}

				if( $status[ 'status' ] != $new_status ){
					$change = true;
				}

				if( $change ){
					switch ( $new_status ) {
						case 'pickedup':
							$res = $order->setStatus( Crunchbutton_Order_Action::DELIVERY_PICKEDUP, $notify_customer, $admin, $note, true );
							break;

						case 'delivered':
							$res = $order->setStatus( Crunchbutton_Order_Action::DELIVERY_DELIVERED, $notify_customer, $admin, $note, true );
							break;

						case 'accepted':
							$res = $order->setStatus( Crunchbutton_Order_Action::DELIVERY_ACCEPTED, $notify_customer, $admin, $note, true );
							break;

						case 'rejected':
							$res = $order->setStatus( Crunchbutton_Order_Action::DELIVERY_REJECTED, $notify_customer, $admin, $note, true );
							break;

						case 'canceled':
							$res = $order->setStatus( Crunchbutton_Order_Action::DELIVERY_CANCELED, $notify_customer, $admin, $note, true );
							break;
					}
				}

				$order->clearStatus();
				echo json_encode( $order->status()->last() );

				break;

			case 'ticket':
				$ticket = $order->getSupport(true);
				if( $ticket ){
					echo json_encode( [ 'id_support' => $ticket->id_support ] );exit;
				}
				break;

			default:
				$out = $order->ordersExports();
				$out['user'] = $order->user()->id_user ? $order->user()->exports() : null;
				$out['restaurant'] = $order->restaurant()->id_restaurant ? $order->restaurant()->exports() : null;
				$out[ 'do_not_reimburse_driver' ] = ( intval( $out[ 'do_not_reimburse_driver' ] ) > 0 ) ? true : false;
				$out[ 'do_not_pay_driver' ] = ( intval( $out[ 'do_not_pay_driver' ] ) > 0 ) ? true : false;
				$out[ 'do_not_pay_restaurant' ] = ( intval( $out[ 'do_not_pay_restaurant' ] ) > 0 ) ? true : false;

				$campus_card_charged = $order->campus_cash_charged();

				if( $campus_card_charged ){
					$out[ 'campus_cash_charged' ] = true;
					$out[ 'campus_cash_charged_info' ] = $campus_card_charged;
				} else {
					$out[ 'campus_cash_charged' ] = false;
					$paymentType = $order->paymentType();
					$out[ 'campus_cash_sha1' ] = $paymentType->stripe_id;
				}

				echo json_encode($out);
				break;
		}

	}
}
