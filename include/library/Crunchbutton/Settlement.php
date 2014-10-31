<?php

/**
 * Settlement
 *
 * settlement settles fund distribution. this can be CB, driver, or restaurant
 *
 */


class Crunchbutton_Settlement extends Cana_Model {

	const DEFAULT_NOTES = 'Crunchbutton Orders';

	const TEST_SUMMARY_FAX = '_PHONE_';
	const TEST_SUMMARY_EMAIL = 'daniel@_DOMAIN_';

	// id_orders equals or lower than that will be ignored
	const CONFIG_KEY_ID_ORDER_START = 'settlement-id_order-start';

	public function __construct( $filters = [] ) {
		$this->filters = $filters;
	}

	public function startRestaurant(){

		$this->restaurants = self::restaurants( $this->filters );

		foreach ( $this->restaurants as $restaurant ) {
			$restaurant->_payableOrders = $restaurant->payableOrders( $this->filters );
			$orders = [];
			foreach( $restaurant->_payableOrders as $order ){
				$orders[] = $this->orderExtractVariables( $order );
			}
			$restaurant->payment_data = $this->restaurantsProcessOrders( $orders );
		}
		return $this->restaurants;
	}

	public function startDriver(){
		$_orders = self::orders( $this->filters );
		$orders = [];
		foreach ( $_orders as $order ) {
			$orders[] = $this->orderExtractVariables( $order );
		}
		return $this->driversProcess( $orders );
	}

	// https://github.com/crunchbutton/crunchbutton/issues/3234
	public function driverWeeksSummaryShifts( $id_driver ){
		$query = 'SELECT cs.*, asa.id_admin_shift_assign FROM admin_shift_assign AS asa
							INNER JOIN community_shift cs ON cs.id_community_shift = asa.id_community_shift
							WHERE asa.id_admin = "' . $id_driver . '"
								AND DATE( cs.date_start ) >= "' . ( new DateTime( $this->filters[ 'start' ] ) )->format( 'Y-m-d' ) . '"
								AND DATE( cs.date_start ) <= "' . ( new DateTime( $this->filters[ 'end' ] ) )->format( 'Y-m-d' ) . '"';
		$shifts = Crunchbutton_Community_Shift::q( $query );
		$_shifts = [];
		foreach( $shifts as $shift ){
			$_shift[ 'week' ] = str_pad( $shift->dateStart()->format( 'W' ), 2, '0', STR_PAD_LEFT );
			$_shift[ 'year' ] = $shift->dateStart()->format( 'Y' );
			$_shift[ 'day' ] = $shift->dateStart()->format( 'Ymd' );
			$_shift[ 'day' ] = $shift->dateStart()->format( 'Ymd' );
			$_shift[ 'date_day' ] = $shift->dateStart()->format( 'M jS Y' );
			$_shift[ 'date_start' ] = $shift->dateStart()->format( 'M jS Y g:i:s A' );
			$_shift[ 'date_end' ] = $shift->dateEnd()->format( 'M jS Y g:i:s A' );
			$_shift[ 'hours' ] = $shift->duration();
			$_shift[ 'id_admin_shift_assign' ] = $shift->id_admin_shift_assign;
			$_shift[ 'id_community_shift' ] = $shift->id_community_shift;
			$_shift[ 'driver_paid' ] = $shift->duration();
			if( $_shift[ 'driver_paid' ] ){
				$schedule_info = Cockpit_Payment_Schedule_Shift::checkShiftWasPaidDriver( $_shift[ 'id_admin_shift_assign' ] );
				if( $schedule_info ){
					$payment_info = $schedule_info->payment()->get( 0 );
					if( $payment_info ){
						$date = $payment_info->date();
						$_shift[ 'paid_info' ] = [ 'id_payment' => $payment_info->id_payment, 'date' => $date->format( 'M jS Y g:i:s A' ) ];
					}
				}
			}
			$_shifts[] = $_shift;
		}
		return $_shifts;
	}

	public function driverWeeksSummaryOrders( $id_driver ){
		$query = 'SELECT DISTINCT(o.id_order) AS id, o.* FROM `order` o
							INNER JOIN order_action oa ON oa.id_order = o.id_order
							WHERE
									DATE( o.date ) >= "' . ( new DateTime( $this->filters[ 'start' ] ) )->format( 'Y-m-d' ) . '"
								AND DATE( o.date ) <= "' . ( new DateTime( $this->filters[ 'end' ] ) )->format( 'Y-m-d' ) . '"
								AND ( oa.type = "' . Crunchbutton_Order_Action::DELIVERY_DELIVERED . '"
											OR oa.type = "' . Crunchbutton_Order_Action::DELIVERY_PICKEDUP . '"
											OR oa.type = "' . Crunchbutton_Order_Action::DELIVERY_ACCEPTED . '"
											OR oa.type = "' . Crunchbutton_Order_Action::DELIVERY_TRANSFERED . '" )
								AND oa.id_admin = "' . $id_driver . '"
								AND o.name NOT LIKE "%test%"
							ORDER BY o.date DESC ';
		$_orders = [];
		$orders = Order::q( $query );
		foreach( $orders as $order ) {
			if( $order->getDeliveryDriver() && $order->getDeliveryDriver()->id_admin == $id_driver ){
				$_order = $this->orderExtractVariables( $order );
				$_order[ 'week' ] = str_pad( $order->date()->format( 'W' ), 2, '0', STR_PAD_LEFT );
				$_order[ 'year' ] = $order->date()->format( 'Y' );
				$_order[ 'day' ] = $order->date()->format( 'Ymd' );
				$_order[ 'date_day' ] = $order->date()->format( 'M jS Y' );
				if( $_order[ 'driver_paid' ] ){
					$payment_info = Crunchbutton_Order_Transaction::orderPaymentInfoDriver( $_order[ 'id_order' ] );
					if( $payment_info ){
						$_order[ 'payment_info' ] = [ 'id_payment' => $payment_info->id_payment, 'date' => $payment_info->date()->format( 'M jS Y g:i:s A' ) ];
					}
				}
				if( $_order[ 'driver_reimbursed' ] ){
					$payment_info = Crunchbutton_Order_Transaction::orderReimbursementInfoDriver( $_order[ 'id_order' ] );
					if( $payment_info ){
						$_order[ 'reimbursed_info' ] = [ 'id_payment' => $payment_info->id_payment, 'date' => $payment_info->date()->format( 'M jS Y g:i:s A' ) ];
					}
				}
				$_orders[] = $_order;
			}
		}
		return $this->driversProcess( $_orders, true );
	}

	// get orders we have to pay
	public static function orders( $filters ){
		$query = 'SELECT o.* FROM `order` o
									INNER JOIN restaurant r ON r.id_restaurant = o.id_restaurant
									WHERE DATE(o.date) >= "' . (new DateTime($filters['start']))->format('Y-m-d') . '"
										AND DATE(o.date) <= "' . (new DateTime($filters['end']))->format('Y-m-d') . '"
										AND o.name NOT LIKE "%test%"
										AND r.name NOT LIKE "%test%"
									ORDER BY o.date ASC ';
		// todo: do not commit with this line: for test only
		// $query = 'SELECT * FROM `order` WHERE id_order IN( 24462, 24463, 24464 ) order by id_order desc';
		// $query = 'SELECT * FROM `order` WHERE id_order IN( 24515,24505,24497,24420,24407,24484,24495,24457,24438,24429,24493,24460,24450,24427,24418,24455,24406,24409,24513,24476,24435,24501,24494,24456,24421,24423,24403,24408,24424,24449,24504,24436,24434,24417,24516,24485,24488,24437,24451,24512,24507,24500,24466,24422,24496,24432,24425,24487,24498,24433,24405,24411,24483,24474,24473,24472,24419,24415,24471,24443,24416,24503,24499,24492,24490,24448,24446,24414,24413,24491,24447,24412,24509,24506,24479,24478,24462,24461,24428,24508,24475,24463,24440,24489,24486,24514,24464,24431,24458,24430,24511,24404,24470,24482,24459,24467,24502,24480,24426 ) order by id_order desc';
		return Order::q( $query );
	}

	// shifts we have to pay hourly
	public function workedShiftsByPeriod( $id_admin, $filters ){
		$start = ( new DateTime( $filters[ 'start' ] ) )->format( 'Y-m-d' );
		$end = ( new DateTime( $filters[ 'end' ] ) )->format( 'Y-m-d' );
		return Crunchbutton_Admin_Shift_Assign::shiftsByAdminPeriod( $id_admin, $start, $end );
	}


	// get restaurants that we need to pay
	public static function restaurants($filters = []) {
		$q = 'SELECT restaurant.*, MAX(p.date) AS last_pay, p.id_restaurant AS p_id_rest
					FROM restaurant
						LEFT OUTER JOIN ( SELECT id_restaurant, `date` FROM `payment` ) AS p using(id_restaurant)
						INNER JOIN restaurant_payment_type rpt ON rpt.id_restaurant = restaurant.id_restaurant
					WHERE 1=1 ';
		if ($filters['payment_method']) {
			 $q .= ' AND rpt.payment_method = "'.$filters['payment_method']. '" ';
		} else {
			$q .= ' AND ( rpt.payment_method = "check" OR rpt.payment_method = "deposit" ) ';
		}
		if( $filters[ 'id_restaurant' ] ){
			$q .= ' AND restaurant.id_restaurant = "' . $filters[ 'id_restaurant' ] . '"';
		}
		$q .= ' AND restaurant.id_restaurant AND restaurant.name NOT LIKE "%test%"
						GROUP BY restaurant.id_restaurant
						 ORDER BY (CASE WHEN p_id_rest IS NULL THEN 1 ELSE 0 END) ASC, restaurant.name ASC';
		return Restaurant::q( $q );
	}


	// this method receives the restaurant orders and run the math
	public function restaurantsProcessOrders( $orders, $recalculatePaidOrders = false ){
		// start all with 0
		$restaurant = [];
		$pay = [ 'card_subtotal' => 0, 'cash_reimburse' => 0, 'tax' => 0, 'delivery_fee' => 0, 'tip' => 0, 'customer_fee' => 0, 'markup' => 0, 'credit_charge' => 0, 'restaurant_fee' => 0, 'promo_gift_card' => 0, 'apology_gift_card' => 0, 'order_payment' => 0, 'cash_subtotal' => 0 ];
		foreach ( $orders as $order ) {
			if( $order ){

				// Pay if Refunded
				if( ( $order[ 'refunded' ] == 1 && $order[ 'pay_if_refunded' ] == 0 ) ){
					continue;
				}
				if( $order[ 'do_not_pay_restaurant' ] == 1 ){
					continue;
				}
				if( $order[ 'restaurant_paid' ] && !$recalculatePaidOrders ){
					continue;
				}
				$restaurant['restaurant'] = $order[ 'restaurant' ];
				$pay[ 'card_subtotal' ] += $this->orderCardSubtotalPayment( $order );
				$pay[ 'tax' ] += $this->orderTaxPayment( $order );
				$pay[ 'delivery_fee' ] += $this->orderDeliveryFeePayment( $order );
				$pay[ 'tip' ] += $this->orderTipPayment( $order );
				$pay[ 'customer_fee' ] += $this->orderCustomerFeePayment( $order );
				$pay[ 'markup' ] += $this->orderMarkupPayment( $order );
				$pay[ 'credit_charge' ] += $this->orderCreditChargePayment( $order );
				$pay[ 'restaurant_fee' ] += $this->orderRestaurantFeePayment( $order );
				$pay[ 'promo_gift_card' ] += $this->orderPromoGiftCardPayment( $order );
				$pay[ 'apology_gift_card' ] += $this->orderApologyGiftCardPayment( $order );
				$pay[ 'order_payment' ] += $this->orderRestaurantOrderPayment( $order );
				$pay[ 'cash_subtotal' ] += $this->orderCashSubtotalPayment( $order );
				$pay[ 'cash_reimburse' ] += $this->orderReimburseCashOrder( $order );
				$pay[ 'formal_relationship' ] = $order[ 'formal_relationship' ];
			}
		}
		foreach ( $pay as $key => $val ) {
			$pay[ $key ] = round( $val, 2 );
		}
		// sum
		$pay[ 'total_due' ] = round( $this->orderCalculateTotalDue( $pay ), 2 );

		$this->log( 'restaurantsProcessOrders', array_merge( $restaurant, $pay ) );
		return $pay;
	}

	// this method receives the restaurant orders and run the math
	public function driversProcess( $orders, $recalculatePaidOrders = false, $include_invites = true ){
		$pay = [];

		// amount for each invited user
		foreach ( $orders as $order ) {

			if( $order && $order[ 'id_admin' ] ){

				// Refunded Driver Orders Should Show Up! #3568 -- !(Refunded orders are not paid)
 				// if( $order[ 'refunded' ] == 1 ){
					// continue;
				// }

				$driver = $order[ 'id_admin' ];
				if( !$pay[ $driver ] ){
					$pay[ $driver ] = [ 'subtotal' => 0, 'tax' => 0, 'delivery_fee' => 0, 'tip' => 0, 'customer_fee' => 0, 'markup' => 0, 'credit_charge' => 0, 'restaurant_fee' => 0, 'gift_card' => 0, 'total_spent' => 0, 'orders' => [] ];
					$pay[ $driver ][ 'id_admin' ] = $driver;
					$pay[ $driver ][ 'name' ] = $order[ 'driver' ];
					$pay_type = Admin::o( $driver )->payment_type();
					$pay[ $driver ][ 'using_pex' ] = $pay_type->using_pex;
					if( $pay_type->id_admin_payment_type ){
						$pay[ $driver ][ 'pay_type' ][ 'payment_type' ] = $pay_type->payment_type;
					} else {
						$pay[ $driver ][ 'pay_type' ][ 'payment_type' ] = Crunchbutton_Admin_Payment_Type::PAYMENT_TYPE_ORDERS;
					}
				}

				$order[ 'pay_info' ] = [];
				$order[ 'pay_info' ][ 'pay_by_order' ] = ( $pay[ $driver ][ 'pay_type' ][ 'payment_type' ] != Crunchbutton_Admin_Payment_Type::PAYMENT_TYPE_HOURS ? 1 : 0 );
				$order[ 'pay_info' ][ 'subtotal' ] = $this->orderSubtotalDriveryPay( $order );
				$order[ 'pay_info' ][ 'tax' ] = $this->orderTaxDriverPay( $order );
				$order[ 'pay_info' ][ 'delivery_fee' ] = $this->orderDeliveryFeeDriverPay( $order );
				$order[ 'pay_info' ][ 'tip' ] = $this->orderTipDriverPay( $order );
				$order[ 'pay_info' ][ 'customer_fee' ] = $this->orderCustomerFeeDriverPay( $order );
				$order[ 'pay_info' ][ 'markup' ] = $this->orderMarkupDriverPay( $order );
				$order[ 'pay_info' ][ 'credit_charge' ] = $this->orderCreditChargeDriverPay( $order );
				$order[ 'pay_info' ][ 'restaurant_fee' ] = $this->orderRestaurantFeeDriverPay( $order );
				$order[ 'pay_info' ][ 'gift_card' ] = $this->orderGiftCardDriverPay( $order );
				$order[ 'pay_info' ][ 'total_reimburse' ] = $this->orderReimburseDriver( $order );
				$order[ 'pay_info' ][ 'total_payment' ] = $this->orderCalculateTotalDueDriver( $order[ 'pay_info' ] );
				$order[ 'pay_info' ][ 'total_spent' ] = $this->orderCalculateTotalSpent( $order );

				// Do not reimburse drivers that are using pex card #3876
				if( ( $order[ 'driver_reimbursed' ] && !$recalculatePaidOrders ) || $pay[ $driver ][ 'using_pex' ] ){
					$order[ 'pay_info' ][ 'total_reimburse' ] = 0;
				}
				if( $order[ 'driver_paid' ] && !$recalculatePaidOrders ){
					$order[ 'pay_info' ][ 'total_payment' ] = 0;
				}

				$pay[ $driver ][ 'orders' ][] = $order;

				if( $order[ 'do_not_pay_driver' ] == 1 ){
					continue;
				}
				$pay[ $driver ][ 'subtotal' ] += $order[ 'pay_info' ][ 'subtotal' ];
				$pay[ $driver ][ 'tax' ] += $order[ 'pay_info' ][ 'tax' ];
				$pay[ $driver ][ 'delivery_fee' ] += $order[ 'pay_info' ][ 'delivery_fee' ];
				$pay[ $driver ][ 'tip' ] += $order[ 'pay_info' ][ 'tip' ];
				$pay[ $driver ][ 'customer_fee' ] += $order[ 'pay_info' ][ 'customer_fee' ];
				$pay[ $driver ][ 'markup' ] += $order[ 'pay_info' ][ 'markup' ];
				$pay[ $driver ][ 'credit_charge' ] += $order[ 'pay_info' ][ 'credit_charge' ];
				$pay[ $driver ][ 'restaurant_fee' ] += $order[ 'pay_info' ][ 'restaurant_fee' ];
				$pay[ $driver ][ 'gift_card' ] += $order[ 'pay_info' ][ 'gift_card' ];
				$pay[ $driver ][ 'total_reimburse' ] += $order[ 'pay_info' ][ 'total_reimburse' ];
				$pay[ $driver ][ 'total_payment' ] += $order[ 'pay_info' ][ 'total_payment' ];
				$pay[ $driver ][ 'total_spent' ] += $order[ 'pay_info' ][ 'total_spent' ];
			}
		}

		foreach( $pay as $id_driver => $driver ){
			$pay_type = Admin::o( $id_driver )->payment_type();
			if( $pay_type->payment_type == Crunchbutton_Admin_Payment_Type::PAYMENT_TYPE_HOURS ){
				$shifts = $this->workedShiftsByPeriod( $id_driver, $this->filters );
				$worked_shifts = [];
				$pay[ $id_driver ][ 'salary_type' ] = Crunchbutton_Admin_Payment_Type::PAYMENT_TYPE_HOURS;
				$pay[ $id_driver ][ 'shifts' ] = [ 'worked_total' => 0, 'amount' => 0, 'hour_rate' => $pay_type->hour_rate ];
				foreach( $shifts as $shift ){
					if( !Cockpit_Payment_Schedule_Shift::checkShiftWasPaidDriver( $shift->id_admin_shift_assign ) || $recalculatePaidOrders ){
						$_shift = [];
						$_shift[ 'id_community_shift' ] = $shift->id_community_shift;
						$_shift[ 'id_admin_shift_assign' ] = $shift->id_admin_shift_assign;
						$_shift[ 'period' ] = $shift->startEndToStringCommunityTz();;
						$_shift[ 'hours' ] = $shift->duration();
						$_shift[ 'amount' ] = round( $shift->duration() * $pay_type->hour_rate, 2 );
						$pay[ $id_driver ][ 'shifts' ][ 'worked_total' ]++;
						$pay[ $id_driver ][ 'shifts' ][ 'amount' ] += round( $_shift[ 'amount' ], 2 );
						$worked_shifts[] = $_shift;
					}
				}
				$tip = 0;

				foreach( $pay[ $id_driver ][ 'orders' ] as $id_order => $order ){
					if( !$order[ 'driver_paid' ] ){
						$tip += $order[ 'pay_info' ][ 'tip' ];
					}
				}

				$pay[ $id_driver ][ 'worked_hours' ] = $pay[ $id_driver ][ 'shifts' ][ 'amount' ];
				$pay[ $id_driver ][ 'total_payment' ] = ( $pay[ $id_driver ][ 'shifts' ][ 'amount' ] + $tip + $pay[ $id_driver ][ 'markup' ] );
				$pay[ $id_driver ][ 'shifts' ][ 'worked' ] = $worked_shifts;

			} else {
				$pay[ $id_driver ][ 'salary_type' ] = Crunchbutton_Admin_Payment_Type::PAYMENT_TYPE_ORDERS;
			}
		}

		// Add the invites data
		// https://github.com/crunchbutton/crunchbutton/issues/2561#issuecomment-49223406
		if( $include_invites ){
			$amount_per_invited_user = $this->amount_per_invited_user();
			$invites = $this->driverInvites();
			if( $invites ){
				foreach( $invites as $id_admin => $invites ){
					if( !$pay[ $id_admin ] ){
						$admin = Crunchbutton_Admin::o( $id_admin );
						$pay[ $id_admin ] = [ 'id_admin' => $id_admin, 'name' => $admin->name ];
					}
					$pay[ $id_admin ][ 'invites' ] = $invites;
					$pay[ $id_admin ][ 'invites_total' ] = count( $invites );
					$pay[ $id_admin ][ 'invites_total_payment' ] = ( $amount_per_invited_user * $pay[ $id_admin ][ 'invites_total' ] );
					$pay[ $id_admin ][ 'total_payment' ] += $pay[ $id_admin ][ 'invites_total_payment' ];
				}
			}
		}

		usort( $pay, function( $a, $b ) {
			return $a[ 'name'] > $b[ 'name' ];
		} );

		return $pay;
	}

	public function orderCalculateTotalSpent( $arr ){
		return ( $arr[ 'subtotal' ] + $arr[ 'tax' ] ) * $arr[ 'delivery_service' ] * ( $arr[ 'formal_relationship' ] ? 0 : 1 );
	}

	public function orderCalculateTotalDueDriver( $pay ){
		$total_due = 	( ( $pay[ 'subtotal' ] +
										$pay[ 'tax' ] +
										$pay[ 'delivery_fee' ] +
										$pay[ 'customer_fee' ] +
										$pay[ 'markup' ] +
										$pay[ 'credit_charge' ] +
										$pay[ 'restaurant_fee' ] +
										$pay[ 'gift_card' ] -
										$pay[ 'total_reimburse' ] ) * $pay[ 'pay_by_order' ] ) + $pay[ 'tip' ];
		return $total_due;
	}

	public function orderCalculateTotalDue( $pay ){
		$total_due = 	$pay[ 'card_subtotal' ] +
									$pay[ 'tax' ] +
									$pay[ 'delivery_fee' ] +
									$pay[ 'tip' ] +
									$pay[ 'customer_fee' ] +
									$pay[ 'markup' ] +
									$pay[ 'credit_charge' ] +
									$pay[ 'restaurant_fee' ] +
									$pay[ 'promo_gift_card' ] +
									$pay[ 'apology_gift_card' ] +
									$pay[ 'cash_reimburse' ];
		return ( max( $total_due, 0 ) ) * $pay[ 'formal_relationship' ];
	}

	// This method calculates the "base" charge for credit orders using the formula
	// that Balanced and Stripe use.
	public function orderBaseCreditCharge( $arr ){
		return ( 0.3 + 0.029 * $arr[ 'total_charged' ] ) * $arr[ 'credit' ];
	}

	// This method calculates the base fee charged to the restaurant.
	// It applies the Restaurant Fee % to the subtotal.
	// It also applies the Fee % to the Delivery Fee and Tax, unless excplicitly excepted,
	// or unless the restaurant has 3rd-party delivery.
	public function orderBaseRestaurantFee( $arr ){
		return ( $arr[ 'restaurant_fee_percent' ] * ( $arr[ 'subtotal' ] + ( 1 - $arr[ 'delivery_service' ] ) * ( 1 - $arr[ 'just_fee_on_subtotal' ] ) * ( $arr[ 'tip' ] + $arr[ 'delivery_fee' ] ) ) ) / 100;
	}

	// For cash orders: reimburse just the cost of the food, so Subtotal + Tax
	public function orderReimburseCashOrder( $arr ){
		return ( $arr[ 'subtotal' ] + $arr[ 'tax' ] ) * $arr[ 'cash' ] * $arr[ 'reimburse_cash_order' ];
	}

	// For the subtotal, we pay this to the restaurant for credit orders.
	public function orderCardSubtotalPayment( $arr ){
		return $arr[ 'subtotal' ] * $arr[ 'credit' ];
	}

	// Tax is also paid to the restaurant for credit orders.
	public function orderTaxPayment( $arr ){
		return $arr[ 'tax' ] * $arr[ 'credit' ];
	}

	// Delivery fee is paid to the restaurant for credit orders,
	// unless we do delivery ourselves.
	public function orderDeliveryFeePayment( $arr ){
		return $arr[ 'delivery_fee' ] * $arr[ 'credit' ] * ( 1 - $arr[ 'delivery_service' ] );
	}

	// Tip  is paid to the restaurant for credit orders
	// unless we do delivery ourselves.
	public function orderTipPayment( $arr ){
		return $arr[ 'tip' ] * $arr[ 'credit' ] * ( 1 - $arr[ 'delivery_service' ] );
	}

	// We charge the restaurant when they collected our markup from cash orders,
	// except for 3rd-party delivery, when this must be collected from the driver.
	public function orderCustomerFeePayment( $arr ){
		return - ( $arr[ 'service_fee' ] * $arr[ 'cash' ] * ( 1 - $arr[ 'delivery_service' ] ) );
	}

	// We charge the restaurant when they collected our markup from cash orders,
	// except for 3rd-party delivery, when this must be collected from the driver.
	public function orderMarkupPayment( $arr ){
		return - ( $arr[ 'delivery_service_markup_value' ] * $arr[ 'cash' ] * ( 1 - $arr[ 'delivery_service' ] ) );
	}

	// We charge the restaurant our Credit Charge from Balanced except
	// when explicitly agreed otherwise.
	public function orderCreditChargePayment( $arr ){
		return - ( $this->orderBaseCreditCharge( $arr ) * $arr[ 'pay_credit_charge' ] );
	}

	// We charge the restaurant our contracted fee in all cases.
	public function orderRestaurantFeePayment( $arr ){
		return - ( $this->orderBaseRestaurantFee( $arr ) );
	}

	// We charge the restaurants for Promo gift cards up to their Promo maximum.
	// This is set to 0 at the moment because we told them it would only be for new users,
	// and lots of people are using gift cards again and again.
	// When this is fixed, I'll get rid of the "*0" in the formula.
	public function orderPromoGiftCardPayment( $arr ){
		return - ( min( [ $arr[ 'gift_card_paid_by_promotional' ], $arr[ 'promotion_maximum' ] ] ) * $arr[ 'credit' ] ) * 0;
	}

	// We charge restaurants for apology credits up to their apology credit maximum.
	public function orderApologyGiftCardPayment( $arr ){
		return - ( min( [ $arr[ 'gift_card_paid_by_restaurant' ], $arr[ 'max_apology_credit' ] ] ) * $arr[ 'credit' ] );
	}

	// Drivers are paid the subtotal for credit orders when we have no formal
	// relationship with the restaurant and they had to buy the food themselves.
	public function orderSubtotalDriveryPay( $arr ){
		return $arr[ 'subtotal' ] * $arr[ 'credit' ] * ( 1 - $arr[ 'formal_relationship' ] ) * ( 1 - $arr[ 'paid_with_cb_card' ] ) * $arr[ 'delivery_service' ];
	}

	// Drivers are paid the tax for credit orders when we have no formal
	// relationship with the restaurant and they had to buy the food themselves.
	public function orderTaxDriverPay( $arr ){
		return $arr[ 'tax' ] * $arr[ 'credit' ] * ( 1 - $arr[ 'formal_relationship' ] ) * ( 1 - $arr[ 'paid_with_cb_card' ] ) * $arr[ 'delivery_service' ];
	}

	// Drivers are paid the whole delivery fee from credit orders.
	public function orderDeliveryFeeDriverPay( $arr ){
		return $arr[ 'delivery_fee' ] * $arr[ 'credit' ] * $arr[ 'delivery_service' ];
	}

	// Reimburse: To quote above, drivers are only reimbursed (that is, reimbursed for subtotal + tax)
	// on orders where they front the money (i.e. on credit orders to non-formal-relationship stores)
	// https://github.com/crunchbutton/crunchbutton/issues/3232#issuecomment-47254475
	// https://github.com/crunchbutton/crunchbutton/issues/3232#issuecomment-47283481
	public function orderReimburseDriver( $arr ){
		return ( $arr[ 'subtotal' ] + $arr[ 'tax' ] ) * $arr[ 'credit' ] * $arr[ 'delivery_service' ] * ( 1 - $arr[ 'formal_relationship' ] );
	}

	// Drivers are paid the whole tip from credit orders.
	public function orderTipDriverPay( $arr ){
		return $arr[ 'tip' ] * $arr[ 'credit' ];
	}

	// Drivers must pay us back our markup they collected in cash.
	public function orderCustomerFeeDriverPay( $arr ){
		return - ( $arr[ 'service_fee' ] * $arr[ 'cash' ] );
	}

	// Drivers must pay us back our markup they collected in cash.
	public function orderMarkupDriverPay( $arr ){
		return - ( $arr[ 'delivery_service_markup_value' ] * $arr[ 'cash' ] );
	}

	// Drivers are not charged credit card fee, or gift card charge, for their orders.
	// We eat this cost, or assume it is taken into account when setting our markup.
	public function orderCreditChargeDriverPay( $arr ){
		return 0;
	}

	// Drivers are not charged credit card fee, or gift card charge, for their orders.
	// We eat this cost, or assume it is taken into account when setting our markup.
	public function orderRestaurantFeeDriverPay( $arr ){
		return 0;
	}

	// Drivers are not charged credit card fee, or gift card charge, for their orders.
	// We eat this cost, or assume it is taken into account when setting our markup.
	public function orderGiftCardDriverPay( $arr ){
		return 0;
	}

	// Sum the amount we have to pay
	public function orderRestaurantOrderPayment( $arr ){
		return 	$this->orderCardSubtotalPayment( $arr ) +
						$this->orderTaxPayment( $arr ) +
						$this->orderDeliveryFeePayment( $arr ) +
						$this->orderTipPayment( $arr ) +
						$this->orderCustomerFeePayment( $arr ) +
						$this->orderMarkupPayment( $arr );
	}

	public function orderCashSubtotalPayment( $arr ){
		return 	( $arr[ 'subtotal' ] +
							$arr[ 'tax' ] +
							( $arr[ 'tip' ] + $arr[ 'delivery_fee' ] ) *
							( 1 - $arr[ 'delivery_service' ] ) ) * $arr[ 'cash' ];
	}

	public function orderTotalFee( $arr ){
		return $arr[ 'customer_fee_percent' ] + $arr[ 'restaurant_fee_percent' ];
	}

	public function orderExtractVariables( $order ){
		// Get all variables
		$values = [];
		$values[ 'subtotal' ] = $order->subtotal();
		$values[ 'tax' ] = $order->tax();
		$values[ 'tip' ] = $order->tip();
		$values[ 'final_price_plus_delivery_markup' ] = $order->final_price_plus_delivery_markup;
		$values[ 'delivery_fee' ] = $order->deliveryFee();
		$values[ 'service_fee' ] = $order->serviceFee();
		$values[ 'customer_fee' ] = $order->customer_fee();
		$values[ 'customer_fee_percent' ] = $order->restaurant()->fee_customer;
		$values[ 'restaurant_fee_percent' ] = $order->restaurant_fee_percent();
		$values[ 'delivery_service_markup' ] = $order->delivery_service_markup;
		$values[ 'delivery_service_markup_value' ] = $order->delivery_service_markup_value;
		$values[ 'id_admin' ] = $order->getDeliveryDriver()->id_admin; // driver
		$values[ 'id_order' ] = $order->id_order; // driver

		$values[ 'gift_card_total' ] = $order->chargedByCredit();
		$values[ 'gift_card_paid_by_crunchbutton' ] = Crunchbutton_Credit::creditByOrderPaidBy( $order->id_order, Crunchbutton_Credit::PAID_BY_CRUNCHBUTTON );
		$values[ 'gift_card_paid_by_restaurant' ] = Crunchbutton_Credit::creditByOrderPaidBy( $order->id_order, Crunchbutton_Credit::PAID_BY_RESTAURANT );
		$values[ 'gift_card_paid_by_promotional' ] = Crunchbutton_Credit::creditByOrderPaidBy( $order->id_order, Crunchbutton_Credit::PAID_BY_PROMOTIONAL );
		$values[ 'gift_card_paid_by_other_restaurant' ] = Crunchbutton_Credit::creditByOrderPaidBy( $order->id_order, Crunchbutton_Credit::PAID_BY_OTHER_RESTAURANT );

		$values[ 'total_charged' ] = $order->charged();
		$values[ 'promotion_maximum' ] = $order->restaurant()->payment_type()->max_pay_promotion;
		$values[ 'max_apology_credit' ] = $order->restaurant()->payment_type()->max_apology_credit;

		$values[ 'credit' ] = ( $order->pay_type == Crunchbutton_Order::PAY_TYPE_CREDIT_CARD ) ? 1 : 0;
		$values[ 'cash' ] = ( $order->pay_type == Crunchbutton_Order::PAY_TYPE_CASH ) ? 1 : 0;
		$values[ 'charge_credit_fee' ] = ( $order->restaurant()->charge_credit_fee > 0 )  ? 1 : 0;
		$values[ 'pay_credit_charge' ] = ( $order->restaurant()->charge_credit_fee > 0 )  ? 1 : 0;
		$values[ 'pay_promotion' ] = ( $order->restaurant()->payment_type()->max_pay_promotion > 0 ) ? 1 : 0;
		$values[ 'just_fee_on_subtotal' ] = ( $order->restaurant()->fee_on_subtotal > 0 ) ? 1: 0;
		$values[ 'delivery_service' ] = ( $order->delivery_service > 0 ) ? 1: 0;
		$values[ 'formal_relationship' ] = ( $order->restaurant()->formal_relationship > 0 ) ? 1: 0;
		$values[ 'paid_with_cb_card' ] = ( $order->paid_with_cb_card > 0 ) ? 1: 0;
		$values[ 'refunded' ] = ( $order->refunded > 0 ) ? 1: 0;
		$values[ 'do_not_pay_restaurant' ] = ( $order->do_not_pay_restaurant > 0 ) ? 1: 0;
		$values[ 'do_not_pay_driver' ] = ( $order->do_not_pay_driver > 0 ) ? 1: 0;
		$values[ 'pay_if_refunded' ] = ( $order->pay_if_refunded > 0 ) ? 1: 0;
		$values[ 'reimburse_cash_order' ] = ( $order->reimburse_cash_order > 0 ) ? 1: 0;

		// convert all to float -> mysql returns some values as string
		foreach( $values as $key => $val ){
			$values[ $key ] = floatval( $val );
		}

		$values[ 'restaurant_paid' ] = Cockpit_Payment_Schedule_Order::checkOrderWasPaidRestaurant( $order->id_order );
		if( !$values[ 'restaurant_paid' ] ){
			$values[ 'restaurant_paid' ] = Crunchbutton_Order_Transaction::checkOrderWasPaidRestaurant( $order->id_order );
		}

		$values[ 'driver_reimbursed' ] = Cockpit_Payment_Schedule_Order::checkOrderWasReimbursedDriver( $order->id_order );
		if( !$values[ 'driver_reimbursed' ] ){
			$values[ 'driver_reimbursed' ] = Crunchbutton_Order_Transaction::checkOrderWasReimbursedDriver( $order->id_order );
		}

		$values[ 'driver_paid' ] = Cockpit_Payment_Schedule_Order::checkOrderWasPaidDriver( $order->id_order );
		if( !$values[ 'driver_paid' ] ){
			$values[ 'driver_paid' ] = Crunchbutton_Order_Transaction::checkOrderWasPaidDriver( $order->id_order );
		}

		// Assumes the order was already paid
		// Checklist for AFTER new settlement is deployed #3603 - item 2
		$id_order_start = $this->id_order_start();
		if( intval( $values[ 'id_order' ] ) <= $id_order_start ){
			$values[ 'restaurant_paid' ] = true;
			$values[ 'driver_reimbursed' ] = true;
			$values[ 'driver_paid' ] = true;
		}

		if( $values[ 'id_admin' ] ){
			$admin = Admin::o( $values[ 'id_admin' ] );
			$values[ 'driver' ] = $admin->name;
		} else {
			$values[ 'driver' ] = '';
		}

		$values[ 'name' ] = $order->name;
		$values[ 'pay_type' ] = $order->pay_type;
		$values[ 'restaurant' ] = $order->restaurant()->name;
		$values[ 'date' ] = $order->date()->format( 'M jS Y g:i:s A' );
		$values[ 'short_date' ] = $order->date()->format( 'M jS Y' );

		return $values;
	}

	public function scheduleRestaurantPayment( $id_restaurants ){
		$this->log( 'scheduleRestaurantPayment', $id_restaurants );
		$restaurants = $this->startRestaurant();
		foreach ( $restaurants as $_restaurant ) {
			if( !$id_restaurants[ $_restaurant->id_restaurant ] ){
				continue;
			}
			$notes = $id_restaurants[ $_restaurant->id_restaurant ][ 'notes' ];
			$adjustment = $id_restaurants[ $_restaurant->id_restaurant ][ 'adjustment' ];
			$id_restaurant = $_restaurant->id_restaurant;
			$payment_data = $_restaurant->payment_data;
			// the total dues should include the adjustment
			$payment_data[ 'total_due' ] = $payment_data[ 'total_due' ] + $adjustment;

			// check if it has any order to be paid
			$shouldSchedule = ( $payment_data[ 'total_due' ] > 0 ) ? true : false;

			foreach ( $_restaurant->_payableOrders as $order ) {
				$alreadyPaid = Cockpit_Payment_Schedule_Order::checkOrderWasPaidRestaurant( $order->id_order );
				if( !$alreadyPaid ){
					$alreadyPaid = Crunchbutton_Order_Transaction::checkOrderWasPaidRestaurant( $order->id_order );
				}
				if( !$alreadyPaid ){
					$shouldSchedule = true;
				}
			}
			if( $shouldSchedule ){
				// schedule it
				$schedule = new Cockpit_Payment_Schedule;
				$schedule->id_restaurant = $_restaurant->id_restaurant;
				$schedule->date = date( 'Y-m-d H:i:s' );
				$schedule->amount = max( $payment_data[ 'total_due' ], 0 );
				$schedule->adjustment = $adjustment;
				$schedule->pay_type = Cockpit_Payment_Schedule::PAY_TYPE_PAYMENT;
				$schedule->type = Cockpit_Payment_Schedule::TYPE_RESTAURANT;
				$schedule->status = Cockpit_Payment_Schedule::STATUS_SCHEDULED;
				$schedule->note = $notes;
				$schedule->id_admin = c::user()->id_admin;
				$schedule->save();
				$id_payment_schedule = $schedule->id_payment_schedule;
				// save the orders
				foreach ( $_restaurant->_payableOrders as $order ) {
					$total_due = $this->restaurantsProcessOrders( [ $this->orderExtractVariables( $order ) ] );
					$schedule_order = new Cockpit_Payment_Schedule_Order;
					$schedule_order->id_payment_schedule = $id_payment_schedule;
					$schedule_order->id_order = $order->id_order;
					$schedule_order->amount = max( $total_due[ 'total_due' ], 0 );
					$schedule_order->save();
				}
				$this->log( 'scheduleRestaurantPayment', $schedule->properties() );
			}
		}
		$settlement = new Crunchbutton_Settlement;
		Cana::timeout(function() use( $settlement ) {
			$settlement->doRestaurantPayments();
		} );
	}

	public function scheduleDriverArbitraryPayment( $id_driver, $amount, $pay_type, $notes ){

		$this->log( 'scheduleDriverArbitraryPayment: start', [ $id_driver, $amount, $type, $notes ] );

		$schedule = new Cockpit_Payment_Schedule;
		$schedule->id_driver = $id_driver;
		$schedule->date = date( 'Y-m-d H:i:s' );
		$schedule->pay_type = $pay_type;
		$schedule->amount = max( $amount, 0 );
		$schedule->adjustment = 0;
		$schedule->arbritary = 1;
		$schedule->note = $notes;
		$schedule->id_admin = c::user()->id_admin;
		$schedule->type = Cockpit_Payment_Schedule::TYPE_DRIVER;
		$schedule->status = Cockpit_Payment_Schedule::STATUS_SCHEDULED;
		$schedule->save();

		$id_payment_schedule = $schedule->id_payment_schedule;

		$this->log( 'scheduleDriverArbitraryPayment: end', $schedule->properties() );

		return $id_payment_schedule;

	}

	public function sendDriverArbitraryPayment( $id_payment_schedule ){
		if( $this->doDriverPayments( $id_payment_schedule ) ){
			return $id_payment_schedule;
		} else {
			return false;
		}
	}

	public function scheduleDriverPayment( $id_drivers, $type ){

		$this->log( 'scheduleDriversPayment', $id_drivers );

		$drivers = $this->startDriver();

		foreach ( $drivers as $_driver ) {

			if( !$id_drivers[ $_driver[ 'id_admin' ] ] ){
				continue;
			}

			$notes = $id_drivers[ $_driver[ 'id_admin' ] ][ 'notes' ];
			$adjustment = $id_drivers[ $_driver[ 'id_admin' ] ][ 'adjustment' ];
			$adjustment_notes = $id_drivers[ $_driver[ 'id_admin' ] ][ 'adjustment_notes' ];
			$id_driver = $_driver[ 'id_admin' ];

			$shouldSchedule = false;
			if( $type == Cockpit_Payment_Schedule::PAY_TYPE_REIMBURSEMENT ){
				$shouldSchedule = ( $_driver[ 'total_reimburse' ] != 0 ) ? true : false;
			} else {
				$shouldSchedule = ( $_driver[ 'total_payment' ] != 0 ) ? true : false;
			}

			if( $_driver[ 'orders' ] ){
				foreach ( $_driver[ 'orders' ] as $order ) {
					if( $type == Cockpit_Payment_Schedule::PAY_TYPE_REIMBURSEMENT ){
						if( !$order[ 'driver_reimbursed' ] ){
							$shouldSchedule = true;
						}
					} else {
						if( !$order[ 'driver_paid' ] ){
							$shouldSchedule = true;
						}
					}
				}
			}

			$invites = $_driver[ 'invites' ];
			if( $type == Cockpit_Payment_Schedule::PAY_TYPE_REIMBURSEMENT ){
				if( count( $invites ) > 0 ){
					$shouldSchedule = true;
				}
			}

			if( $shouldSchedule ){

				// schedule it
				$schedule = new Cockpit_Payment_Schedule;
				$schedule->id_driver = $id_driver;
				$schedule->date = date( 'Y-m-d H:i:s' );

				if( $type == Cockpit_Payment_Schedule::PAY_TYPE_REIMBURSEMENT ){
					$amount = $_driver[ 'total_reimburse' ] + $adjustment;
				} else {
					$amount = $_driver[ 'total_payment' ] + $adjustment;
					$pay_type = Admin::o( $id_driver )->payment_type();
					if( $pay_type->id_admin_payment_type && $pay_type->payment_type == Crunchbutton_Admin_Payment_Type::PAYMENT_TYPE_HOURS ){
						$schedule->driver_payment_hours = 1;
					} else {
						$schedule->driver_payment_hours = 0;
					}
				}

				// Range
				$range = ( new DateTime( $this->filters[ 'start' ] ) )->format( 'm/d/Y' );
				$range .= ' => ';
				$range .= ( new DateTime( $this->filters[ 'end' ] ) )->format( 'm/d/Y' );

				$schedule->amount = max( $amount, 0 );
				$schedule->adjustment = $adjustment;
				$schedule->range_date = $range;
				$schedule->pay_type = $type;
				$schedule->type = Cockpit_Payment_Schedule::TYPE_DRIVER;
				$schedule->status = Cockpit_Payment_Schedule::STATUS_SCHEDULED;
				$schedule->log = 'Schedule created';
				$schedule->note = $notes;
				$schedule->adjustment_note = $adjustment_notes;
				$schedule->id_admin = c::user()->id_admin;
				$schedule->save();
				$id_payment_schedule = $schedule->id_payment_schedule;

				if( $type == Cockpit_Payment_Schedule::PAY_TYPE_PAYMENT ){
					if( $_driver[ 'shifts' ] && $_driver[ 'shifts' ][ 'worked' ] ){
						foreach ( $_driver[ 'shifts' ][ 'worked' ] as $shift ) {
							$schedule_shift = new Cockpit_Payment_Schedule_Shift;
							$schedule_shift->id_payment_schedule = $id_payment_schedule;
							$schedule_shift->id_admin_shift_assign = $shift[ 'id_admin_shift_assign' ];
							$schedule_shift->hours = $shift[ 'hours' ];
							$schedule_shift->amount = $shift[ 'amount' ];
							$schedule_shift->save();
							$this->log( 'scheduleDriverPayment', $schedule->properties() );
						}
					}
					if( $_driver[ 'invites' ] ){
						foreach( $_driver[ 'invites' ] as $invite ){
							$schedule_referral = new Cockpit_Payment_Schedule_Referral;
							$schedule_referral->id_payment_schedule = $id_payment_schedule;
							$schedule_referral->id_referral = $invite[ 'id_referral' ];
							$schedule_referral->amount = $this->amount_per_invited_user();
							$schedule_referral->save();
							$this->log( 'scheduleReferralPayment', $schedule_referral->properties() );
						}
					}
				}

				if( $_driver[ 'orders' ] ){
					foreach ( $_driver[ 'orders' ] as $order ) {
						if( $type == Cockpit_Payment_Schedule::PAY_TYPE_REIMBURSEMENT ){
							$order_amount = $order[ 'pay_info' ][ 'total_reimburse' ];
						} else {
							$order_amount = $order[ 'pay_info' ][ 'total_payment' ];
						}
						$schedule_order = new Cockpit_Payment_Schedule_Order;
						$schedule_order->id_payment_schedule = $id_payment_schedule;
						$schedule_order->id_order = $order[ 'id_order' ];
						$schedule_order->amount = $order_amount;
						$schedule_order->save();
					}
				}

				$this->log( 'scheduleDriverPayment', $schedule->properties() );
			}
		}
		$this->doDriverPayments();
		return true;
	}

	public function doDriverErrPayments(){
		$schedules = Cockpit_Payment_Schedule::q( 'SELECT * FROM payment_schedule WHERE status = "' . Cockpit_Payment_Schedule::STATUS_ERROR . '"' );
		foreach( $schedules as $_schedule ){
			$id_payment_schedule = $_schedule->id_payment_schedule;
			$settlement = new Crunchbutton_Settlement;
			Cana::timeout( function() use( $settlement, $id_payment_schedule ) {
				$settlement->payDriver( $id_payment_schedule );
			} );
		}
	}

	public function doDriverPayments( $id_payment_schedule = false ){
		if( $id_payment_schedule ){
			return $this->payDriver( $id_payment_schedule );
		} else {
			$schedule = new Cockpit_Payment_Schedule;
			$lastDate = $schedule->lastDriverStatusDate();
			$schedules = $schedule->driversSchedulesFromDate( $lastDate );
			foreach( $schedules as $_schedule ){
				$id_payment_schedule = $_schedule->id_payment_schedule;
				$settlement = new Crunchbutton_Settlement;
				Cana::timeout( function() use( $settlement, $id_payment_schedule ) {
					$settlement->payDriver( $id_payment_schedule );
				} );
			}
			return;
		}
	}

	public function doRestaurantPayments( $id_payment_schedule = false ){
		if( $id_payment_schedule ){
			return $this->payRestaurant( $id_payment_schedule );
		} else {
			$schedule = new Cockpit_Payment_Schedule;
			$lastDate = $schedule->lastRestaurantStatusDate();
			$schedules = $schedule->restaurantSchedulesFromDate( $lastDate );
			foreach( $schedules as $_schedule ){
				$this->payRestaurant( $_schedule->id_payment_schedule );
			}
		}
	}

	public function restaurantSummaryByIdPayment( $id_payment ){
		$schedule = Cockpit_Payment_Schedule::q( 'SELECT * FROM payment_schedule WHERE id_payment = "' . $id_payment . '"' );
		if( $schedule->id_payment_schedule ){
			return $this->restaurantSummary( $schedule->id_payment_schedule );
		} else {
			return false;
		}
	}

	public function restaurantSummary( $id_payment_schedule ){
		$schedule = Cockpit_Payment_Schedule::o( $id_payment_schedule );
		if( $schedule->id_payment_schedule && $schedule->type == Cockpit_Payment_Schedule::TYPE_RESTAURANT ){
			$settlement = new Settlement;
			$summary = $schedule->exports();
			$summary[ 'restaurant' ] = $schedule->restaurant()->name;
			$summary[ 'summary_method' ] = $schedule->restaurant()->payment_type()->summary_method;
			$summary[ 'summary_email' ] = $schedule->restaurant()->payment_type()->summary_email;
			$summary[ 'summary_fax' ] = $schedule->restaurant()->payment_type()->summary_fax;
			$summary[ 'payment_method' ] = $schedule->restaurant()->payment_type()->payment_method;
			$summary[ 'type' ] = Cockpit_Payment_Schedule::TYPE_RESTAURANT;
			$payment = $schedule->payment();
			if( $payment->id_payment ){
				$summary[ 'balanced_id' ] = $payment->balanced_id;
				$summary[ 'stripe_id' ] = $payment->stripe_id;
				$summary[ 'check_id' ] = $payment->check_id;
				$summary[ 'summary_sent_date' ] = $payment->summary_sent_date()->format( 'M jS Y g:i:s A T' );
				$summary[ 'payment_date' ] = $payment->date()->format( 'M jS Y g:i:s A T' );
			}
			if( $schedule->status_date ){
				$summary[ 'status_date' ] = $schedule->status_date()->format( 'M jS Y g:i:s A T' );
			}
			$orders = $schedule->orders();
			$_orders = [];
			$summary[ 'orders_cash' ] = 0;
			$summary[ 'orders_card' ] = 0;
			$summary[ 'orders_not_included' ] = 0;
			$summary[ 'orders' ] = [ 'card' => [], 'cash' => [], 'not_included' => [] ];
			foreach( $orders as $order ){
				$_order = $order->order();
				if( $_order->id_order ){
					$variables = $settlement->orderExtractVariables( $_order );
					$type = $variables[ 'cash' ] ? 'cash' : 'card';
					if( $type == 'cash' || ( $type == 'card' && $order->amount > 0 ) ){
						if( $type == 'card' ){
							$summary[ 'orders_card' ]++;
						} else {
							$summary[ 'orders_cash' ]++;
						}
						$summary[ 'orders' ][ $type ][] = [ 'id_order' => $variables[ 'id_order' ], 'name' => $variables[ 'name' ], 'total' => $variables[ 'final_price_plus_delivery_markup' ], 'date' => $variables[ 'short_date' ], 'tip' => $variables[ 'tip' ] ];
						$_orders[] = $variables;
					} else if ( !$order->amount ){
						$summary[ 'orders_not_included' ]++;
						$summary[ 'orders' ][ 'not_included' ][] = [ 'id_order' => $variables[ 'id_order' ], 'name' => $variables[ 'name' ], 'total' => $variables[ 'final_price_plus_delivery_markup' ], 'date' => $variables[ 'short_date' ], 'tip' => $variables[ 'tip' ] ];
					}
				}
			}
			$summary[ 'calcs' ] = $settlement->restaurantsProcessOrders( $_orders, true );
			$summary[ 'calcs' ][ 'total_due' ] = $summary[ 'calcs' ][ 'total_due' ] + $summary[ 'adjustment' ];
			$summary[ 'admin' ] = [ 'id_admin' => $schedule->id_admin, 'name' => $schedule->admin()->name ];
			return $summary;
		} else {
			return false;
		}
	}

	public function payRestaurant( $id_payment_schedule ){

		$env = c::getEnv();

		$schedule = Cockpit_Payment_Schedule::o( $id_payment_schedule );
		$this->log( 'payRestaurant', $schedule->properties() );
		if( $schedule->id_payment_schedule ){

			if( $schedule->status == Cockpit_Payment_Schedule::STATUS_SCHEDULED ||
					$schedule->status == Cockpit_Payment_Schedule::STATUS_ERROR ){

				// Save the processing date
				$schedule->status = Cockpit_Payment_Schedule::STATUS_PROCESSING;
				$schedule->status_date = date( 'Y-m-d H:i:s' );
				$schedule->save();

				$amount = floatval( $schedule->amount );

				$payment_method = $schedule->restaurant()->payment_type()->payment_method;

				// Deposit payment method
				if( $payment_method == Crunchbutton_Restaurant_Payment_Type::PAYMENT_METHOD_DEPOSIT ){

					$payment_type = $schedule->restaurant()->payment_type();

					if( !$payment_type->balanced_id || !$payment_type->balanced_bank ){
						$schedule->log = 'There is no account info for this restaurant.';
						$message = 'Restaurant Payment error! Restaurant: ' . $schedule->restaurant()->name;
						$message .= "\n". 'id_payment_schedule: ' . $schedule->id_payment_schedule;
						$message .= "\n". 'amount: ' . $schedule->amount;
						$message .= "\n". $schedule->log;
						$schedule->status = Cockpit_Payment_Schedule::STATUS_ERROR;
						$schedule->status_date = date( 'Y-m-d H:i:s' );
						$schedule->save();
						Crunchbutton_Support::createNewWarning(  [ 'body' => $message ] );
						return false;
					}

					if( $amount > 0 ){
						try {
							$p = Payment::credit( [ 'id_restaurant' => $schedule->id_restaurant,
																			'amount' => $amount,
																			'note' => $schedule->note,
																			'type' => 'balanced' ] );
						} catch ( Exception $e ) {
							$schedule->log = $e->getMessage();
							$schedule->status = Cockpit_Payment_Schedule::STATUS_ERROR;
							$schedule->status_date = date( 'Y-m-d H:i:s' );
							$schedule->save();
							$this->log( 'payRestaurant: Error', $schedule->properties() );
							return false;
						}
					} else {
						$payment = new Crunchbutton_Payment;
						$payment->date = date( 'Y-m-d H:i:s' );
						$payment->id_restaurant = $schedule->id_restaurant;
						$payment->note = $schedule->note;
						$payment->env = c::getEnv();
						$payment->id_admin = c::user()->id_admin;
						$payment->amount = 0;
						$payment->adjustment = $schedule->adjustment;
						$payment->save();

						$p = $payment->id_payment;
					}

					if( $p ){

						$payment = Crunchbutton_Payment::o( $p );
						// save the adjustment
						if( floatval( $schedule->adjustment ) != 0  ){
							$payment->adjustment = $schedule->adjustment;
							$payment->save();
						}

						$schedule->id_payment = $payment->id_payment;
						$schedule->status = Cockpit_Payment_Schedule::STATUS_DONE;
						$schedule->log = 'Payment finished';
						$schedule->status_date = date( 'Y-m-d H:i:s' );
						$schedule->save();
						$this->log( 'payRestaurant: Success', $schedule->properties() );
						$orders = $schedule->orders();

						foreach (  $orders as $order ) {

							$order_transaction = new Crunchbutton_Order_Transaction;
							$order_transaction->id_order = $order->id_order;
							$order_transaction->amt = $order->amount;
							$order_transaction->date = date( 'Y-m-d H:i:s' );
							$order_transaction->type = Crunchbutton_Order_Transaction::TYPE_PAID_TO_RESTAURANT;
							$order_transaction->source = Crunchbutton_Order_Transaction::SOURCE_CRUNCHBUTTON;
							$order_transaction->id_admin = $payment->id_admin;
							$order_transaction->save();

							$payment_order_transaction = new Cockpit_Payment_Order_Transaction;
							$payment_order_transaction->id_payment = $payment->id_payment;
							$payment_order_transaction->id_order_transaction = $order_transaction->id_order_transaction;
							$payment_order_transaction->save();
						}

						$this->sendRestaurantPaymentNotification( $payment->id_payment );
						return true;
					} else {
						$message = 'Restaurant Payment error! Restaurant: ' . $schedule->restaurant()->name;
						$message .= "\n". 'id_payment_schedule: ' . $schedule->id_payment_schedule;
						$message .= "\n". 'amount: ' . $schedule->amount;
						$message .= "\n". $schedule->log;
						$schedule->status = Cockpit_Payment_Schedule::STATUS_ERROR;
						$schedule->status_date = date( 'Y-m-d H:i:s' );
						$schedule->save();
						Crunchbutton_Support::createNewWarning(  [ 'body' => $message ] );
						return false;
					}
				}
				// Check payment method
				else if( $payment_method == Crunchbutton_Restaurant_Payment_Type::PAYMENT_METHOD_CHECK ){

					$payment_type = $schedule->restaurant()->payment_type();

					$check_address = $payment_type->check_address;
					$check_address_city = $payment_type->check_address_city;
					$check_address_state = $payment_type->check_address_state;
					$check_address_zip = $payment_type->check_address_zip;
					$check_address_country = $payment_type->check_address_country;

					$contact_name = $payment_type->contact_name;

					$error = false;
					$schedule->log = '';
					if( !$check_address || !$check_address_city || !$check_address_state || !$check_address_zip || !$check_address_country ){
						$schedule->log = 'Check address is incomplete. ';
						$error = true;
					}
					if( !$contact_name ){
						$schedule->log .= 'Contact name is missing.';
						$error = true;
					}
					if( $error ){
						$message = 'Restaurant Payment error! Restaurant: ' . $schedule->restaurant()->name;
						$message .= "\n". 'id_payment_schedule: ' . $schedule->id_payment_schedule;
						$message .= "\n". 'amount: ' . $schedule->amount;
						$message .= "\n". $schedule->log;
						$schedule->status = Cockpit_Payment_Schedule::STATUS_ERROR;
						$schedule->status_date = date( 'Y-m-d H:i:s' );
						$schedule->save();
						$this->log( 'payRestaurant: Error', $schedule->properties() );
						Crunchbutton_Support::createNewWarning(  [ 'body' => $message ] );
					} else {

						$check_name = $schedule->restaurant()->name;

						if( $amount > 0 ){
							try{
								$c = c::lob()->checks()->create( [ 'name' => $check_name,
																										'to' => [ 'name' => $contact_name,
																															'address_line1' => $check_address,
																															'address_city' => $check_address_city,
																															'address_state' => $check_address_state,
																															'address_zip' => $check_address_zip,
																															'address_country' => $check_address_country ],
																										'bank_account' => c::lob()->defaultAccount(),
																										'amount' => $amount,
																										'memo' => $schedule->note,
																										'message' => $schedule->note ] );
								$check_id = $c->id;
							} catch( Exception $e ) {
								$schedule->log = $e->getMessage();
								$schedule->status = Cockpit_Payment_Schedule::STATUS_ERROR;
								$schedule->status_date = date( 'Y-m-d H:i:s' );
								$schedule->save();
								return false;
							}
						}

						if( $check_id ||  $amount == 0 ){
							$success = true;
						}

						if( $success ){

							$payment = new Crunchbutton_Payment;
							$payment->check_id = $c->id;
							$payment->date = date( 'Y-m-d H:i:s' );
							$payment->id_restaurant = $schedule->id_restaurant;
							$payment->note = $schedule->note;
							$payment->env = c::getEnv();
							$payment->id_admin = c::user()->id_admin;
							$payment->amount = $schedule->amount;
							$payment->adjustment = $schedule->adjustment;
							$payment->save();

							$schedule->id_payment = $payment->id_payment;
							$schedule->status = Cockpit_Payment_Schedule::STATUS_DONE;
							$schedule->log = 'Payment finished';
							$schedule->status_date = date( 'Y-m-d H:i:s' );
							$schedule->save();

							$this->log( 'payRestaurant: Success', $schedule->properties() );

							$orders = $schedule->orders();

							foreach (  $orders as $order ) {

								$order_transaction = new Crunchbutton_Order_Transaction;
								$order_transaction->id_order = $order->id_order;
								$order_transaction->amt = $order->amount;
								$order_transaction->date = date( 'Y-m-d H:i:s' );
								$order_transaction->type = Crunchbutton_Order_Transaction::TYPE_PAID_TO_RESTAURANT;
								$order_transaction->source = Crunchbutton_Order_Transaction::SOURCE_CRUNCHBUTTON;
								$order_transaction->id_admin = $payment->id_admin;
								$order_transaction->save();

								$payment_order_transaction = new Cockpit_Payment_Order_Transaction;
								$payment_order_transaction->id_payment = $payment->id_payment;
								$payment_order_transaction->id_order_transaction = $order_transaction->id_order_transaction;
								$payment_order_transaction->save();
							}

							$this->sendRestaurantPaymentNotification( $payment->id_payment );
							return true;

						} else {
							$message = 'Restaurant Payment error! Restaurant: ' . $schedule->restaurant()->name;
							$message .= "\n". 'id_payment_schedule: ' . $schedule->id_payment_schedule;
							$message .= "\n". 'amount: ' . $schedule->amount;
							$message .= "\n". $schedule->log;
							$schedule->status = Cockpit_Payment_Schedule::STATUS_ERROR;
							$schedule->status_date = date( 'Y-m-d H:i:s' );
							$schedule->save();
							$this->log( 'payRestaurant: Error', $schedule->properties() );
							Crunchbutton_Support::createNewWarning(  [ 'body' => $message ] );
							return false;
						}
					}
				} else {
					$schedule->log = 'Restaurant doesn\'t have a payment method.';
					$message = 'Restaurant Payment error! Restaurant: ' . $schedule->restaurant()->name;
					$message .= "\n". 'id_payment_schedule: ' . $schedule->id_payment_schedule;
					$message .= "\n". 'amount: ' . $schedule->amount;
					$message .= "\n". $schedule->log;
					$schedule->status = Cockpit_Payment_Schedule::STATUS_ERROR;
					$schedule->status_date = date( 'Y-m-d H:i:s' );
					$schedule->save();
					$this->log( 'payRestaurant: Error', $schedule->properties() );
					Crunchbutton_Support::createNewWarning(  [ 'body' => $message ] );
				}

			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function payDriver( $id_payment_schedule ){

		$env = c::getEnv();

		$schedule = Cockpit_Payment_Schedule::o( $id_payment_schedule );
		$this->log( 'payDriver', $schedule->properties() );
		if( $schedule->id_payment_schedule ){

			if( $schedule->status == Cockpit_Payment_Schedule::STATUS_SCHEDULED ||
					$schedule->status == Cockpit_Payment_Schedule::STATUS_ERROR ){

				// Save the processing date
				$schedule->status = Cockpit_Payment_Schedule::STATUS_PROCESSING;
				$schedule->status_date = date( 'Y-m-d H:i:s' );
				$schedule->save();

				$amount = floatval( $schedule->amount );

				$payment_method = $schedule->driver()->payment_type()->payment_method;

				// Deposit payment method
				if( $payment_method == Crunchbutton_Admin_Payment_Type::PAYMENT_METHOD_DEPOSIT ){
					if( $amount > 0 ){
						try {
							$id_payment = ( $schedule->id_payment ) ? $schedule->id_payment : null;
							$p = Payment::credit_driver( [ 'id_driver' => $schedule->id_driver,
																			'id_payment' => $id_payment,
																			'amount' => $amount,
																			'note' => $schedule->note,
																			'pay_type' => $schedule->pay_type,
																			'type' => 'balanced' ] );
						} catch ( Exception $e ) {
							$schedule->log = $e->getMessage();
							$schedule->status = Cockpit_Payment_Schedule::STATUS_ERROR;
							$schedule->status_date = date( 'Y-m-d H:i:s' );
							$schedule->save();
							$this->log( 'payDriver: Error', $schedule->properties() );
							return false;
						}

 					} else {

						// If the payment is 0 just create a payment register and send to the driver the summary
						$payment = new Crunchbutton_Payment;
						$payment->date = date( 'Y-m-d H:i:s' );
						$payment->note = $schedule->note;
						$payment->adjustment_note = $schedule->adjustment_note;
						$payment->env = c::getEnv();
						$payment->id_driver = $schedule->id_driver;
						$payment->id_admin = c::user()->id_admin;
						$payment->amount = 0;
						$payment->pay_type = $schedule->pay_type;
						$payment->adjustment = $schedule->adjustment;
						$payment->save();

						$schedule->id_payment = $payment->id_payment;
						$schedule->status = Cockpit_Payment_Schedule::STATUS_DONE;
						$schedule->log = 'Payment finished';
						$schedule->status_date = date( 'Y-m-d H:i:s' );
						$schedule->save();
						$this->log( 'payDriver: Success', $schedule->properties() );
						$orders = $schedule->orders();

						$p = $payment->id_payment;

 					}

					if( $p ){

						$payment = Crunchbutton_Payment::o( $p );

						// save the adjustment
						if( floatval( $schedule->adjustment ) != 0  ){
							$payment->adjustment = $schedule->adjustment;
							$payment->adjustment_note = $schedule->adjustment_note;
							$payment->save();
						}

						$schedule->id_payment = $payment->id_payment;
						$schedule->status = Cockpit_Payment_Schedule::STATUS_DONE;
						$schedule->log = 'Payment finished';
						$schedule->status_date = date( 'Y-m-d H:i:s' );
						$schedule->save();
						$this->log( 'payDriver: Success', $schedule->properties() );
						$orders = $schedule->orders();

						if( $schedule->pay_type == Cockpit_Payment_Schedule::PAY_TYPE_PAYMENT ){
							$order_transaction_type = Crunchbutton_Order_Transaction::TYPE_PAID_TO_DRIVER;
						} else {
							$order_transaction_type = Crunchbutton_Order_Transaction::TYPE_REIMBURSED_TO_DRIVER;
						}

						foreach (  $orders as $order ) {

							$order_transaction = new Crunchbutton_Order_Transaction;
							$order_transaction->id_order = $order->id_order;
							$order_transaction->amt = $order->amount;
							$order_transaction->date = date( 'Y-m-d H:i:s' );
							$order_transaction->type = $order_transaction_type;
							$order_transaction->source = Crunchbutton_Order_Transaction::SOURCE_CRUNCHBUTTON;
							$order_transaction->id_admin = $payment->id_admin;
							$order_transaction->save();

							$payment_order_transaction = new Cockpit_Payment_Order_Transaction;
							$payment_order_transaction->id_payment = $payment->id_payment;
							$payment_order_transaction->id_order_transaction = $order_transaction->id_order_transaction;
							$payment_order_transaction->save();
						}

						$this->sendDriverPaymentNotification( $payment->id_payment );
						return true;
					} else {
						$message = 'Driver Payment error! Driver: ' . $schedule->driver()->name;
						$message .= "\n". 'id_payment_schedule: ' . $schedule->id_payment_schedule;
						$message .= "\n". 'amount: ' . $schedule->amount;
						$message .= "\n". $schedule->log;
						$schedule->status = Cockpit_Payment_Schedule::STATUS_ERROR;
						$schedule->status_date = date( 'Y-m-d H:i:s' );
						$schedule->save();
						Crunchbutton_Support::createNewWarning(  [ 'body' => $message ] );
						return false;
					}
				} else {
					$schedule->log = 'Driver doesn\'t have a payment method.';
					$message = 'Driver Payment error! Driver: ' . $schedule->driver()->name;
					$message .= "\n". 'id_payment_schedule: ' . $schedule->id_payment_schedule;
					$message .= "\n". 'amount: ' . $schedule->amount;
					$message .= "\n". $schedule->log;
					$schedule->status = Cockpit_Payment_Schedule::STATUS_ERROR;
					$schedule->status_date = date( 'Y-m-d H:i:s' );
					$schedule->save();
					$this->log( 'payDriver: Error', $schedule->properties() );
					Crunchbutton_Support::createNewWarning(  [ 'body' => $message ] );
				}
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function driverSummaryByIdPayment( $id_payment ){
		$schedule = Cockpit_Payment_Schedule::q( 'SELECT * FROM payment_schedule WHERE id_payment = "' . $id_payment . '"' );
		if( $schedule->id_payment_schedule ){
			return $this->driverSummary( $schedule->id_payment_schedule );
		} else {
			return false;
		}
	}

	public function driverSummary( $id_payment_schedule ){
		$schedule = Cockpit_Payment_Schedule::o( $id_payment_schedule );
		if( $schedule->id_payment_schedule && $schedule->type == Cockpit_Payment_Schedule::TYPE_DRIVER ){
			$settlement = new Settlement;
			$summary = $schedule->exports();
			$summary[ 'adjustment' ] = floatval( $summary[ 'adjustment' ] );
			$summary[ 'driver' ] = $schedule->driver()->name;
			$summary[ 'summary_email' ] = $schedule->driver()->payment_type()->summary_email;
			$summary[ 'driver' ] = $schedule->driver()->name;
			$summary[ 'payment_method' ] = $schedule->driver()->payment_type()->payment_method;
			$summary[ 'salary_type' ] = ( $schedule->driver_payment_hours ) ? Crunchbutton_Admin_Payment_Type::PAYMENT_TYPE_HOURS : Crunchbutton_Admin_Payment_Type::PAYMENT_TYPE_ORDERS;
			$summary[ 'type' ] = Cockpit_Payment_Schedule::TYPE_DRIVER;
			$payment = $schedule->payment();
			if( $payment->id_payment ){
				$summary[ 'balanced_status' ] = $payment->balanced_status;
				$summary[ 'balanced_failure_reason' ] = $payment->balanced_failure_reason;
				$balanced_date_checked = $payment->balanced_date_checked();
				if( $balanced_date_checked ){
					$summary[ 'balanced_date_checked' ] = $balanced_date_checked->format( 'M jS Y g:i:s A T' );
				}
				$summary[ 'balanced_id' ] = $payment->balanced_id;
				$summary[ 'stripe_id' ] = $payment->stripe_id;
				$summary[ 'check_id' ] = $payment->check_id;
				$summary[ 'summary_sent_date' ] = $payment->summary_sent_date()->format( 'M jS Y g:i:s A T' );
				$summary[ 'payment_date' ] = $payment->date()->format( 'M jS Y g:i:s A T' );
				$status = Cockpit_Payment_Schedule::statusToDriver( $schedule );
				$summary[ 'expected_date' ] = $status[ 'paid_date' ];
			}
			if( $schedule->status_date ){
				$summary[ 'status_date' ] = $schedule->status_date()->format( 'M jS Y g:i:s A T' );
			}

			$invites = $schedule->invites();
			if( $invites ){
				$summary[ 'invites_count' ] = 0;
				$summary[ 'invites_amount' ] = 0;
				$summary[ 'invites' ] = [];
				foreach( $invites as $invite ){
					$_invite = $invite->referral()->settlementExport();
					$_invite[ 'amount' ] = $invite->amount;
					$summary[ 'invites' ][] = $_invite;
					$summary[ 'invites_count' ]++;
					$summary[ 'invites_amount' ] += $invite->amount;
				}
			}

			$shifts = $schedule->shifts();
			if( $shifts ){
				$summary[ 'shifts_count' ] = 0;
				$summary[ 'shifts_hours' ] = 0;
				$summary[ 'shifts_hours_amount' ] = 0;
				$summary[ 'shifts' ] = [];
				foreach( $shifts as $shift ){
					$_shift = [];
					$_shift[ 'amount' ] = $shift->amount;
					$_shift[ 'hours' ] = $shift->hours;
					$_shift[ 'period' ] = $shift->shift()->startEndToStringCommunityTz();
					$summary[ 'shifts' ][] = $_shift;
					$summary[ 'shifts_count' ]++;
					$summary[ 'shifts_hours' ] += $shift->hours;
					$summary[ 'shifts_hours_amount' ] += $shift->amount;
				}
			}

			$summary[ '_total_reimburse_' ] = 0;
			$summary[ '_total_payment_' ] = 0;
			$summary[ '_total_received_cash_' ] = 0;
			$summary[ '_total_cash_orders_' ] = 0;

			$orders = $schedule->orders();
			$_orders = [];
			$summary[ 'orders_count' ] = 0;
			$summary[ 'orders_not_included' ] = 0;
			$summary[ 'orders' ] = [ 'included' => [], 'not_included' => [] ];
			foreach( $orders as $order ){
				$_order = $order->order();
				if( $_order->id_order ){
					$variables = $settlement->orderExtractVariables( $_order );
					$pay_info = $settlement->driversProcess( [ $variables ], true );
					$type = $variables[ 'cash' ] ? 'Cash' : 'Card';
					$summary[ 'orders_count' ]++;
					$summary[ 'orders' ][ 'included' ][] = [ 	'id_order' => $variables[ 'id_order' ],
																										'name' => $variables[ 'name' ],
																										'total' => $variables[ 'final_price_plus_delivery_markup' ],
																										'date' => $variables[ 'short_date' ],
																										'tip' => $variables[ 'tip' ],
																										'restaurant' => $variables[ 'restaurant' ],
																										'delivery_fee' => $variables[ 'delivery_fee' ],
																										'pay_type' => $type,
																										'total_reimburse' => $pay_info[ 0 ][ 'total_reimburse' ],
																										'total_payment' => $pay_info[ 0 ][ 'total_payment' ]
																									];
					if( $type == 'Cash' ){
						$summary[ '_total_received_cash_' ] = $variables[ 'final_price_plus_delivery_markup' ] + $variables[ 'delivery_fee' ];
						$summary[ '_total_cash_orders_' ]++;
					}


					$_orders[] = $variables;
					$summary[ '_total_reimburse_' ] += $pay_info[ 0 ][ 'total_reimburse' ];
					$summary[ '_total_payment_' ] += $pay_info[ 0 ][ 'total_payment' ];
				}
			}
			$calcs = $settlement->driversProcess( $_orders, true );

			$total_reimburse = $calcs[ 0 ][ 'total_reimburse' ];
			$total_payment = $calcs[ 0 ][ 'total_payment' ];

			if( $summary[ 'pay_type' ] == Cockpit_Payment_Schedule::PAY_TYPE_PAYMENT ){
				if( $schedule->driver_payment_hours ){
					$total_payment = floatval( $summary[ 'amount' ] );
					$summary[ 'hourly' ] = true;
				} else {
					$total_payment = floatval( $summary[ 'amount' ] );
				}

			} else {
				$total_reimburse = floatval( $summary[ 'amount' ] );
			}

			$summary[ 'calcs' ] = [ 'total_reimburse' => $total_reimburse,
															'total_payment' => $total_payment,
															'tax' => $calcs[ 0 ][ 'tax' ],
															'delivery_fee' => $calcs[ 0 ][ 'delivery_fee' ],
															'tip' => $calcs[ 0 ][ 'tip' ],
															'customer_fee' => $calcs[ 0 ][ 'customer_fee' ],
															'markup' => $calcs[ 0 ][ 'markup' ],
															'credit_charge' => $calcs[ 0 ][ 'credit_charge' ],
															'restaurant_fee' => $calcs[ 0 ][ 'restaurant_fee' ],
															'gift_card' => $calcs[ 0 ][ 'gift_card' ],
															'subtotal' => $calcs[ 0 ][ 'subtotal' ],
														];

			$summary[ 'admin' ] = [ 'id_admin' => $schedule->id_admin, 'name' => $schedule->admin()->name ];
			$summary[ 'total_payment' ] = max( $summary[ 'total_payment' ], 0 );
			$summary[ 'total_reimburse' ] = max( $summary[ 'total_reimburse' ], 0 );
			$summary[ 'calcs' ][ 'total_payment' ] = max( $summary[ 'calcs' ][ 'total_payment' ], 0 );
			$summary[ 'calcs' ][ 'total_reimburse' ] = max( $summary[ 'calcs' ][ 'total_reimburse' ], 0 );

			return $summary;
		} else {
			return false;
		}
	}

 	public function sendRestaurantPaymentNotification( $id_payment ){

		$summary = $this->restaurantSummaryByIdPayment( $id_payment );

		if( !$summary ){
			return false;
		}

		$this->log( 'sendRestaurantPaymentNotification', $summary );

		$env = c::getEnv();

		$mail = ( $env == 'live' ? $summary[ 'summary_email' ] : Crunchbutton_Settlement::TEST_SUMMARY_EMAIL );
		$fax = ( $env == 'live' ? $summary[ 'summary_fax' ] : Crunchbutton_Settlement::TEST_SUMMARY_FAX );

		$mail = new Crunchbutton_Email_Payment_Summary( [ 'summary' => $summary ] );

		$error = false;

		switch ( $summary[ 'summary_method' ] ) {
			case 'email':
					if( !$summary[ 'summary_email' ] ){
						$error = 'email';
					}
				break;
			case 'fax':
					if( !$summary[ 'summary_fax' ] ){
						$error = 'fax';
					}
				break;
		}

		if( $error ){
			$message = 'Payment Summary send error! Restaurant: ' . $summary[ 'restaurant' ];
			$message .= "\n". 'id_payment_schedule: ' . $summary[ 'id_payment_schedule' ];
			$message .= "\n". 'amount: ' . $summary[ 'amount' ];
			$message .= "\n";
			if( $error == 'email' ){
				$message .= 'Summary email missing.';
			} else if( $error == 'fax' ){
				$message .= 'Summary fax missing.';
			}
			Crunchbutton_Support::createNewWarning(  [ 'body' => $message ] );
			return;
		}

		switch ( $summary[ 'summary_method' ] ) {
			case 'email':

				if ( $mail->send() ) {
					$payment = Crunchbutton_Payment::o( $id_payment );
					$payment->summary_sent_date = date('Y-m-d H:i:s');
					$payment->save();
					return true;
				}
				return false;

				break;

			case 'fax':

				$temp = tempnam( '/tmp','fax' );
				file_put_contents( $temp, $mail->message() );
				rename($temp, $temp.'.html');

				$fax = new Phaxio( [ 'to' => $fax, 'file' => $temp.'.html' ] );

				unlink( $temp.'.html' );

				if ( $fax->success ) {
					$payment = Crunchbutton_Payment::o( $id_payment );
					$payment->summary_sent_date = date('Y-m-d H:i:s');
					$payment->save();
					return true;
				}
				return false;
				break;
		}
		return false;
	}

	public function driverInvites( $id_admin = false ){
		$out = [];
		if( $id_admin ){
			$where = ' AND a.id_admin = ' . $id_admin;
		} else {
			$where = '';
		}
		$invites = Crunchbutton_Referral::q( 'SELECT r.* FROM referral r
																					INNER JOIN admin a ON r.id_admin_inviter = a.id_admin AND r.new_user = 1 ' . $where . '
																					WHERE r.id_referral NOT IN( SELECT psr.id_referral FROM payment_schedule_referral psr )' );
		foreach( $invites as $invite ){
			$_invite = $invite->settlementExport();
			if( !$out[ $invite->id_admin_inviter ] ){
				$out[ $invite->id_admin_inviter ] = [];
			}
			$out[ $invite->id_admin_inviter ][] = $_invite;
		}
		return $out;
	}

	public function sendDriverPaymentNotification( $id_payment ){

		$summary = $this->driverSummaryByIdPayment( $id_payment );

		if( !$summary ){
			return false;
		}

		$this->log( 'sendDriverPaymentNotification', $summary );

		$env = c::getEnv();

		$mail = ( $env == 'live' ? $summary[ 'summary_email' ] : Crunchbutton_Settlement::TEST_SUMMARY_EMAIL );
		$fax = ( $env == 'live' ? $summary[ 'summary_fax' ] : Crunchbutton_Settlement::TEST_SUMMARY_FAX );

		$mail = new Crunchbutton_Email_Payment_Summary( [ 'summary' => $summary ] );

		if ( $mail->send() ) {
			$payment = Crunchbutton_Payment::o( $id_payment );
			$payment->summary_sent_date = date('Y-m-d H:i:s');
			$payment->save();
			return true;
		}

		return false;
	}

	public function id_order_start(){
		if( !$this->_id_order_start ){
			$id_order = Crunchbutton_Config::getVal( Crunchbutton_Settlement::CONFIG_KEY_ID_ORDER_START );
			$this->_id_order_start = intval( $id_order );
		}
		return $this->_id_order_start;
	}

	public function checkPaymentStatus( $type = 'driver' ){
		// get all payments with pending status - drivers
		$payments = Crunchbutton_Payment::q( "SELECT p.* FROM payment_schedule ps INNER JOIN payment p ON ps.id_payment = p.id_payment WHERE ps.status = '" . Cockpit_Payment_Schedule::STATUS_DONE . "' AND ps.type = '{$type}' AND p.balanced_status != '" . Crunchbutton_Payment::BALANCED_STATUS_SUCCEEDED . "'" );
		foreach( $payments as $payment ){
			$id_payment = $payment->id_payment;
			Cana::timeout(function() use( $id_payment ) {
				$payment = Crunchbutton_Payment::o( $id_payment );
				$status = $payment->checkBalancedStatus();
			} );
			$message = 'id_payment : ' . $payment->id_payment;
			$this->log( 'checkPaymentStatus', $message );
		}
	}

	public function amount_per_invited_user(){
		if( !$this->_amount_per_invited_user ){
			$reward = new Crunchbutton_Reward;
			$this->amount_per_invited_user = $reward->adminRefersNewUserCreditAmount();
		}
		return $this->amount_per_invited_user;
	}

	private function log( $method, $message ){
		Log::debug( [ 'method' => $method, 'id_admin' => c::user()->id_admin, 'message' => $message, 'env' => c::getEnv(), 'type' => 'settlement' ] );
	}
}
