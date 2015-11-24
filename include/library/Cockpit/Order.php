<?php

class Cockpit_Order extends Crunchbutton_Order {

	const I_AM_5_MINUTES_AWAY = 'i-am-5-minutes-away';

	public function exports( $params = [] ){

		// presets for performance reasons
		if( isset( $params[ 'profile' ] ) ){
			$_profile = $params[ 'profile' ];
		} else {
			$_profile = 'default';
		}

		$_ignore = [];
		if( isset( $params[ 'ignore' ] ) ){
			 foreach( $params[ 'ignore' ] as $key => $val ){
			 	$_ignore[ $val ] = true;
			 }
		}

		$out = $this->properties();

		$out['id'] = $this->uuid;

		$date = $this->date();

		$out['txn'] = $this->txn;
		$out['do_not_pay_restaurant'] = ( $out['do_not_pay_restaurant'] ? 1 : 0 );
		$out['do_not_pay_driver'] = ( $out['do_not_pay_driver'] ? 1 : 0 );
		$out['do_not_reimburse_driver'] = ( $out['do_not_reimburse_driver'] ? 1 : 0 );
		$out['date_formated'] = $date->format( 'M dS g:i a' );
		$out['time_formated' ] = $date->format( 'g:i' );
		$out['_restaurant_name'] = $this->restaurant()->name;
		$out['_restaurant_permalink'] = $this->restaurant()->permalink;
		$out['_restaurant_phone'] = $this->restaurant()->phone;
		$out['_restaurant_lat'] = $this->restaurant()->loc_lat;
		$out['_restaurant_lon'] = $this->restaurant()->loc_long;
		$out['_restaurant_address'] = $this->restaurant()->address;
		$out['_restaurant_delivery_estimated_time'] = $this->restaurant()->delivery_estimated_time;
		$out['_restaurant_pickup_estimated_time'] = $this->restaurant()->pickup_estimated_time;
		$out['_restaurant_delivery_estimated_time_formated'] = $this->restaurant()->calc_delivery_estimated_time( $this->date );
		$out['_restaurant_pickup_estimated_time_formated'] = $this->restaurant()->calc_pickup_estimated_time( $this->date );
		$out['user'] = $this->user()->uuid;

		//$out['timestamp'] = Crunchbutton_Util::dateToUnixTimestamp( $date );

		$out['timestamp'] = $this->date()->format('U');				// unix epoc
		$out['date'] = $this->date()->format('c');					// date in timezone that the order was placed in

		$out['_message'] = nl2br($this->orderMessage('web'));
		$out['charged'] = floatval( $this->charged() );
		$out['notes_to_driver'] = $this->restaurant()->notes_to_driver;

		$agent = $this->agent();
		$out['agent'] = $agent->os.' '.$agent->browser;

		// resources
		$resources = Crunchbutton_Resource::byCommunity( $this->id_community, 'order_page' );
		if( $resources ){
			$out['resources'] = [];
			foreach( $resources as $resource ){
				$out['resources'][] = [ 'name' => $resource->name, 'path' => $resource->download_url() ];
			}
		}

		$credit = $this->chargedByCredit();
		if( $credit > 0 ){
			$out['credit'] = $credit;
		} else {
			$out['credit'] = 0;
		}

		$out['orders_by_phone'] = self::totalOrdersByPhone( $this->phone );

		$paymentType = $this->paymentType();
		if( $paymentType->id_user_payment_type ){
			$out['card_ending'] = substr( $paymentType->card, -4, 4 );
		} else {
			$out['card_ending'] = false;
		}

		$date = new DateTime($this->date);
		$date->setTimeZone( new DateTimeZone($this->restaurant()->timezone) );

		$out['_date_tz'] = $date->format('Y-m-d H:i:s');
		$out['_date_formatted'] = $date->format( 'M jS Y g:i:s A' );
		$out['_tz'] = $date->format('T');

		$out['_tip'] = $this->tip();
		$out['_tax'] = $this->tax();

		// price without delivery service nor delivery fee #6838
		$out[ '_final_price' ] = $out[ 'price' ] + $this->tax();
		// $out[ '_final_price' ] = $out[ 'final_price' ] - ( $out[ 'delivery_fee' ] );
		$out[ '_tip_with_cash' ] = ( $order->pay_type == 'card' && $order->tip == 0 );

		$out['summary'] = $this->orderMessage('summary');

		if( $this->restaurant()->delivery_estimated_time ){
			$estimate = $this->date()->modify('+'.$this->restaurant()->delivery_estimated_time.' minutes');
			$out[ '_delivery_estimated_time' ] = $estimate->format('h:i A');
			$out[ '_delivery_estimated_time_timestamp' ] = Crunchbutton_Util::dateToUnixTimestamp( $estimate );
		} else {
			$out[ '_delivery_estimated_time' ] = false;
			$out[ '_delivery_estimated_time_timestamp' ] = false;
		}
		$out[ '_instructions_payment' ] = $this->driverInstructionsPaymentStatus();
		$out[ '_instructions_payment_bgcolor' ] = $this->driverInstructionsPaymentBGColor();
		$out[ '_instructions_food' ] = $this->driverInstructionsFoodStatus();
		$out[ '_stealth_notification' ] = $this->restaurant()->hasNotification( 'stealth' );

		// Add a line to bottom of Driver Order view #6358 - old #3879
		$out[ '_instructions_fax' ] = 'Remember: do NOT give the receipt to the customer';

		$out[ 'refunded' ] = intval( $out[ 'refunded' ] );

		if( $out[ 'refunded' ] ){
			 $transaction = $this->refundedReason();
			 if( $transaction ){
				$out[ 'refunded_reason' ] = $transaction->note;
			 }
		}

		$out[ '_dishes' ] = [];

		$delivery_service_markup = ( $this->delivery_service_markup ) ? $this->delivery_service_markup : 0;
		$out[ '_delivery_service_markup' ] = $delivery_service_markup;

		// Dishes
		foreach( $this->dishes() as $dish ){

			$food = $dish->dish()->name;
			$price = $dish->dish()->price;
			$regular_price = $dish->dish()->price;

			// add the delivery markup
			if( $delivery_service_markup > 0 && $price > 0 ){
				$price = $price + number_format( ( $dish->dish()->price * $delivery_service_markup / 100 ), 2 );
				$price = Crunchbutton_Restaurant::roundDeliveryMarkupPrice( $price);
			}
			$regular_price = number_format( $regular_price, 2 );

			$options = $dish->options();

			if (gettype($options) == 'array') {
				$options = i::o($options);
			}

			$withOptions = '';
			$selectOptions = '';

			if ($options->count()) {

				foreach ($dish->options() as $option) {

					if ($option->option()->type == 'select') {
						continue;
					}

					$regular_price += $option->option()->price;

					// add the delivery markup
					if( $delivery_service_markup > 0 && $price > 0 ){
						$option_price = Crunchbutton_Restaurant::roundDeliveryMarkupPrice( $option->option()->price + ( $option->option()->price * $delivery_service_markup / 100 ));
						$price = $price + $option_price;
					}

					if($option->option()->id_option_parent) {
						$optionGroup = Crunchbutton_Option::o($option->option()->id_option_parent);
						if( $selectOptions == '' ){
							$selectOptions .= ' ';
						}
						$selectOptions .= $optionGroup->name . ': ';
						$selectOptions .= $option->option()->name.', ';
					} else {
						$withOptions .= $option->option()->name.', ';
					}
					$regular_price = number_format( $regular_price, 2 );
				}
				if( $withOptions != '' ){
					$withOptions = substr($withOptions, 0, -2);
				}
				if( $selectOptions != '' ){
					$selectOptions = substr($selectOptions, 0, -2);
				}
			}

			$withoutDefaultOptions = '';
			if( $dish->id_order_dish && $dish->id_dish ){
				$optionsNotChoosen = $dish->optionsDefaultNotChoosen();
				$commas = '';
				if( $optionsNotChoosen->count() ){
					foreach( $optionsNotChoosen as $dish_option ){
						$withoutDefaultOptions .= $commas . 'No ' . $dish_option->option()->name;
						$commas = ', ';
					}
				}
			}

			if ( $withOptions == '' && $withoutDefaultOptions == '' && $selectOptions == '' ) {
				$food .= '.';
			} else {
				$food .= ': ';
			}

			if( $withOptions != '' ){
				$withOptions .= '.';
			}

			if( $withoutDefaultOptions != '' ){
				$withoutDefaultOptions .= '.';
			}

			if( $selectOptions != '' ){
				$selectOptions .= '.';
			}

			$regular_price = number_format( $regular_price, 2 );
			$price = number_format( $price, 2 );

			$out[ '_dishes' ][] = [ 'name' => $food, 'price' => [ 'regular' => $regular_price, 'marked_up' => $price ], 'options' => [ 'without_default_options' => $withoutDefaultOptions, 'with_option' => $withOptions, 'select_options' => $selectOptions ] ];
		}

		// driver suggestion: quantity column to make ordering easier #4779
		$_dishes = [];
		foreach( $out[ '_dishes' ] as $_dish ){
			$token = trim( str_replace( ' ' , '', strtolower( $_dish[ 'name' ] ) ) ) . md5( serialize( $_dish ) );
			if( !$_dishes[ $token ] ){
				$_dishes[ $token ] = [ 'dish' => $_dish, 'quantity' => 0 ];
			}
			$_dishes[ $token ][ 'quantity' ]++;
		}

		// sort
		ksort( $_dishes );

		// kept the _dishes for legacy reasons (native app and other places where it is used)
		$_dishes_qty = [];
		foreach( $_dishes as $_dish ){
			$dish = array_merge( $_dish[ 'dish' ], [ 'quantity' => $_dish[ 'quantity' ] ] );
			$dish[ 'price' ][ 'regular_unity' ] = floatval( $dish[ 'price' ][ 'regular' ] );
			$dish[ 'price' ][ 'marked_up_unity' ] = floatval( $dish[ 'price' ][ 'marked_up' ] );
			$dish[ 'price' ][ 'regular' ] = ( $dish[ 'price' ][ 'regular_unity' ] * $dish[ 'quantity' ] );
			$dish[ 'price' ][ 'marked_up' ] = ( $dish[ 'price' ][ 'marked_up_unity' ] * $dish[ 'quantity' ] );
			$_dishes_qty[] = $dish;
		}

		usort( $_dishes_qty, function( $a, $b ){
			$a_price = $a[ 'price' ][ 'regular_unity' ];
			$b_price = $b[ 'price' ][ 'regular_unity' ];
			return floatval( $a_price ) < floatval( $b_price );
		} );

		$out[ '_dishes_qty' ] = $_dishes_qty;

		$status = $this->status()->last();
		$status_date = new DateTime( $status[ 'date' ], new DateTimeZone( $this->restaurant()->timezone ) );
		$now = new DateTime( 'now', new DateTimeZone( $this->restaurant()->timezone ) );
		$status[ 'date_timestamp' ] = Crunchbutton_Util::dateToUnixTimestamp( $status_date );
		$status[ '_outside_of_24h' ] = Crunchbutton_Util::intervalMoreThan24Hours( $now->diff( $date ) );
		$status[ '_date_formatted' ] = $status_date->format( 'M jS Y g:i:s A' );

		$out['status'] = $status;
		$out['eta'] = $this->eta()->exports();
		$driver = $this->status()->driver();

		$actions = $this->actions();

		$out[ 'actions' ] = [];

		foreach ( $actions as $action ) {
			$_action = [];
			$_admin = $action->admin();
			$_action[ 'id_order_action' ] = $action->id_order_action;
			$_action[ 'type' ] = $action->type;
			$_action[ 'note' ] = $action->note;
			$_action[ 'date' ] = Crunchbutton_Util::dateToUnixTimestamp( $action->date() );
			$_action[ 'admin' ] = [ 'id_admin' => $_admin->id_admin, 'login' => $_admin->login, 'name' => $_admin->name, 'phone' => $_admin->phone ];
			$out[ 'actions' ][] = $_action;
		}

		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );

		if( $status_date ){
			$out[ 'actions_today' ] = $status_date->format( 'Ymd' ) == $now->format( 'Ymd' );
		}

		if( $driver ){
			$out['driver'] = $driver->exports();
		}
		$out['hasCustomerBeenTexted5Minutes'] = $this->hasCustomerBeenTexted5Minutes();

		// remove
		$out[ '_restaurant_address' ] = Crunchbutton_Util::removeNewLine( $out[ '_restaurant_address' ] );
		$out[ 'address' ] = Crunchbutton_Util::removeNewLine( $out[ 'address' ] );

		// informed eta
		$out[ 'informed_eta' ] = Crunchbutton_Order_Eta::informedEtaByOrder( $this->id_order );

		if( $out[ 'campus_cash' ] ){
			$out[ 'campus_cash_name' ] = $this->campusCashName();
			$out[ 'require_signature' ] = $this->requireSignature();

			if( $this->signature_data ){
				$out[ 'last_digits' ] = $this->campusCashLastDigits();
				$out[ 'has_signature' ] = ( $this->signature() ? true : false );
				$out[ 'receipt_info' ] = nl2br( $this->campusCashReceiptInfo() );
				if( $this->signature() ){
					$out[ 'signature' ] = $this->signature();
				}
			}

		}

		if( $out[ 'refunded' ] ){
			$out[ 'refunded_status' ] = $this->refundedStatus();
			if( $out[ 'refunded_status' ] == self::STATUS_REFUNDED_PARTIALLY ){
				$out[ 'refunded_partial' ] = true;
			}
			$out[ 'refunded_amount' ] = $this->refundedTotal();
		}

		foreach( $out as $key => $val ){
			if( is_numeric( $val ) ){
				$out[ $key ] = floatval( $val );
			}
		}

		switch ( $_profile ) {
			case 'driver':
				$out = $this->_driverExports( $out );
				break;
		}
		return $out;
	}

	private function _driverExports( $out ){
		$remove = [ 'do_not_pay_restaurant', 'do_not_pay_driver', 'do_not_reimburse_driver', 'id_user', 'env', 'service_fee', 'processor', 'id_community', 'pay_if_refunded', 'tip_type', 'id_agent', 'id_session', 'fee_restaurant', 'do_not_reimburse_driver', 'id_user_payment_type', 'local_gid', 'type', 'reimburse_cash_order', 'do_not_pay_restaurant', 'do_not_pay_driver', 'lon', 'lat', 'reward_delivery_free', 'likely_test', 'geomatched', 'id_phone', '_restaurant_lat', '_restaurant_lon', 'agent', 'credit', 'id_community', '_delivery_service_markup', '_restaurant_permalink', 'actions' ];
		foreach( $out as $key => $val ){
			if( in_array( $key, $remove ) ){
				unset( $out[ $key ] );
			}
		}
		foreach( $out as $key => $val ){
			if( is_null( $val ) || $val === false ){
				unset( $out[ $key ] );
			}
		}
		if( $out[ 'driver' ] ){
			$keep = [ 'name', 'phone' ];
			foreach( $out[ 'driver' ] as $key => $val ){
				if( !in_array( $key, $keep ) ){
					unset( $out[ 'driver' ][ $key ] );
				}
			}
		}
		return $out;
	}

	public function textCustomer( $text, $force = false ){

		switch ( $text ) {

			case Cockpit_Order::I_AM_5_MINUTES_AWAY:

				if( !$force ){
					$action = Crunchbutton_Order_Action::q( 'SELECT * FROM order_action WHERE id_order = ? AND type = ? LIMIT 1', [$this->id_order, Crunchbutton_Order_Action::DELIVERY_ORDER_TEXT_5_MIN])->get(0);
					if( $action->id_order_action ){
						return true;
					}
				}

				$pattern = "Your driver, %s, is about 5 minutes away and will contact you soon!";
				$driver = $this->driver();

				if( $driver->id_admin ){

					$message = sprintf( $pattern, $driver->firstName() );

					Crunchbutton_Message_Sms::send( [
						'to' => $this->phone,
						'message' => $message,
						'reason' => Crunchbutton_Message_Sms::REASON_DRIVER_NOTIFIES_CUSTOMER
					] );

					(new Order_Action([
						'id_order' => $this->id_order,
						'id_admin' => c::user()->id_admin,
						'timestamp' => date('Y-m-d H:i:s'),
						'note' => $note,
						'type' => Crunchbutton_Order_Action::DELIVERY_ORDER_TEXT_5_MIN
					]))->save();

				}
				break;

			default:
				// other messages here!
				break;
		}

	}

	public function campus_cash_studentID( $sha1 ){

		if( !$sha1 ){
			return Crunchbutton_Stripe_Campus_Cash::ERROR_NOT_FOUND;
		}

		$charged = $this->campus_cash_charged();
		if( $charged ){
			return 'Order already charged. ' . $charged[ 'name' ] . ' at ' . $charged[ 'date' ] . '.';
		}
		if( $this->refunded ){
			return 'Order refunded!';
		}

		if( $this->campus_cash_times_viewed() >= Cockpit_Campus_Cash_Log::VIEW_LIMIT ){
			return 'Sorry, you have reached the view limit of this information!';
		}

		$paymentType = $this->paymentType();
		$stripe_customer = $paymentType->stripe_customer;
		if( !$stripe_customer || $paymentType->stripe_id != $sha1 ){
			return Crunchbutton_Stripe_Campus_Cash::ERROR_NOT_FOUND;
		}
		$student_ID = Crunchbutton_Stripe_Campus_Cash::retrieve( $stripe_customer, $paymentType->id_user_payment_type );
		if( $student_ID ){
			return $student_ID;
		}
	}

	public function campus_cash_times_viewed(){
		$paymentType = $this->paymentType();
		$id_user_payment_type = $paymentType->id_user_payment_type;
		return Cockpit_Campus_Cash_Log::timesViewed( $id_user_payment_type );
	}

	public function campus_cash_charged(){
		$transaction = Crunchbutton_Order_Transaction::q( 'SELECT * FROM order_transaction WHERE id_order = ? AND type = ? ORDER BY id_order_transaction DESC LIMIT 1', [ $this->id_order, Crunchbutton_Order_Transaction::TYPE_CAMPUS_CASH_CHARGED ] )->get( 0 );
		if( $transaction->id_order_transaction ){
			return [ 'name' => $transaction->admin()->name, 'date' => $transaction->date()->format( 'M dS g:i a' ) ];
		}
		return false;
	}

	public function mark_cash_card_charged(){
		$transaction = new Crunchbutton_Order_Transaction;
		$transaction->id_order = $this->id_order;
		$transaction->id_admin = c::user()->id_admin;
		$transaction->type = Crunchbutton_Order_Transaction::TYPE_CAMPUS_CASH_CHARGED;
		$transaction->date = date( 'Y-m-d H:i:s' );
		$transaction->save();
		if( $transaction->id_order_transaction ){
			return true;
		}
		return false;
	}

	public function hasCustomerBeenTexted5Minutes(){
		$texts = Order::q( 'SELECT * FROM order_action WHERE `type`=\'delivery-text-5min\' AND id_order=? limit 1',[$this->id_order])->get(0);
		if ($texts->id_order) {
			return DATE_FORMAT(new DateTime($texts->timestamp), 'g:i A');
			//return $texts->timestamp->date()->format('h:i A');
		} else return false;
	}

    public function getGeo(){
		if (!isset($this->_geo)){
			$this->_geo = null;

	        if (!isset($this->lat) || !isset($this->lon)) {
				if (!isset($this->address)) {
					$this->geo = null;
				} else {
					$loc = $this->findGeoMatchFromDb();
					if (is_null($loc)) {
						$loc = $this->findGeoMatchFromBadAddresses();
						if (is_null($loc)) {
							$loc = Crunchbutton_GoogleGeocode::geocode($this->address);
							$community = $this->community();
							$community_lat = $community->loc_lat;
							$community_lon = $community->loc_lon;
							if (!is_null($loc)){
								if (!is_null($community_lat) && !is_null($community_lon)){
									$distance = Crunchbutton_GoogleGeocode::latlonDistanceInMiles($community_lat, $community_lon, $loc->lat, $loc->lon);
									if ($distance > 10){
										$loc = new Crunchbutton_Order_Location($community_lat, $community_lon);
									} else{
										$loc = new Crunchbutton_Order_Location($loc->lat, $loc->lon);
									}
								} else{
									$this->_geo = null;
									return $this->_geo;
								}
							}
							else{
								// Use community

								if (!is_null($community_lat) && !is_null($community_lon)) {
									$loc = new Crunchbutton_Order_Location($community_lat, $community_lon);
								} else {
									$this->_geo = null;
									return $this->_geo;
								}
							}
						}
					}

					// Save the geocode info
					$this->lat = $loc->lat;
					$this->lon = $loc->lon;
					$this->save();
					$this->_geo = $loc;
				}
			} else{
				$this->_geo = new Crunchbutton_Order_Location($this->lat, $this->lon);
			}
        }
		return $this->_geo;
    }

    /* Find same address in the database that is already geocoded */
    public function findGeoMatchFromDb() {
        $qString = "SELECT * FROM `order` WHERE id_community= ? and "
            ."address = ? and lat is not null and lon is not null limit 1";
        $order = Order::q($qString, [$this->id_community, $this->address]);
        if (is_null($order) || $order->count()==0){
            return null;
        } else{
            $o = $order->get(0);
            return new Crunchbutton_Order_Location($o->lat, $o->lon);
        }
    }

    /* Find same address in the database that is already geocoded */
    public function findGeoMatchFromBadAddresses() {
        $address_lc = preg_replace('/\s+/', ' ', trim(strtolower($this->address)));
        $qString = "SELECT * FROM order_logistics_badaddress WHERE id_community= ? and "
            ."address_lc = ? limit 1";
        $ba = Crunchbutton_Order_Logistics_Badaddress::q($qString, [$this->id_community, $address_lc]);
        if (is_null($ba) || $ba->count()==0){
            return null;
        } else{
            $o = $ba->get(0);
            return new Crunchbutton_Order_Location($o->lat, $o->lon);
        }
    }

	public function ip(){
		$ip = c::db()->get( 'SELECT * FROM session WHERE id_session=? AND ip IS NOT NULL ORDER BY session.date_activity DESC LIMIT 1', [ $this->id_session ] )->get( 0 )->ip;
		return $ip;
	}

	public function minutesToDelivery(){
			$ordered_at = $this->date();
			$ordered_at->setTimeZone( new DateTimeZone( c::config()->timezone ) );

			$status = $this->status()->last();
			if( $status[ 'status' ] == 'delivered' ){
				$delivered_at = new DateTime( $status[ 'date' ], new DateTimeZone( $this->restaurant()->timezone ) );
				$delivered_at->setTimeZone( new DateTimeZone( c::config()->timezone ) );
				return ceil( Crunchbutton_Util::intervalToSeconds( $delivered_at->diff( $ordered_at ) ) / 60 );
			}
			return null;
	}

}
