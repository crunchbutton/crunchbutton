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
		return $this->driversProcessOrders( $orders );
	}

	// get orders we have to pay
	public static function orders( $filters ){
		$query = 'SELECT * FROM `order`
									WHERE DATE(`date`) >= "' . (new DateTime($filters['start']))->format('Y-m-d') . '"
										AND DATE(`date`) <= "' . (new DateTime($filters['end']))->format('Y-m-d') . '"
										AND NAME NOT LIKE "%test%"
									ORDER BY `date` ASC ';
		// todo: do not commit with this line
		// $query = 'SELECT * FROM `order` WHERE id_order IN( 24462, 24463, 24464 ) order by id_order desc';
		// $query = 'SELECT * FROM `order` WHERE id_order IN( 24515,24505,24497,24420,24407,24484,24495,24457,24438,24429,24493,24460,24450,24427,24418,24455,24406,24409,24513,24476,24435,24501,24494,24456,24421,24423,24403,24408,24424,24449,24504,24436,24434,24417,24516,24485,24488,24437,24451,24512,24507,24500,24466,24422,24496,24432,24425,24487,24498,24433,24405,24411,24483,24474,24473,24472,24419,24415,24471,24443,24416,24503,24499,24492,24490,24448,24446,24414,24413,24491,24447,24412,24509,24506,24479,24478,24462,24461,24428,24508,24475,24463,24440,24489,24486,24514,24464,24431,24458,24430,24511,24404,24470,24482,24459,24467,24502,24480,24426 ) order by id_order desc';
		// $query = 'SELECT * FROM `order` WHERE id_order IN( 13809,13808,13806,13803,13802,13800,13794,13793,13791,13789,13784,13782,13781,13780,13778,13774,13772,13771,13770,13768,13762,13761,13748,13747,13731,13728,13727,13726,13708,13707,13705,13704,13703,13699,13698,13690,13689,13688,13687,13685,13684,13683,13681,13679,13678,13675,13673,13672,13671,13667,13662,13660,13658,13657,13656,13653,13652,13651,13637,13632,13619,13618,13617,13613,13612,13611,13608,13607,13606,13604,13603,13601,13599,13598,13596,13594,13592,13588,13585,13584,13583,13582,13581,13579,13578,13577,13576,13572,13569,13562,13557,13548,13546,13538,13534,13532,13530,13527,13522,13521,13519,13517,13515,13511,13510,13508,13504,13493,13491,13483,13477,13468,13467,13465,13464,13463,13462,13461,13459,13457,13456,13455,13454,13443,13442,13440,13439,13438,13436,13435,13424,13419,13407,13404,13396,13395,13385,13384,13383,13381,13380,13377,13374,13370,13369,13367,13360,13357,13356,13355,13353,13345,13344,13328,13325,13324,13323,13322,13321,13320,13316,13315,13314,13311,13306,13304,13301,13299,13296,13293,13278,13262,13717,13674,13641,13610,13609,13597,13529,13526,13525,13466,13362,13655,13744,13494,13414,13409,13408,13399,13388,13359,13350,13297,13289,13732,13670,13458,13452,13718,13600,13595,13512,13444,13441,13400,13317,13308,13280,13276,13779,13752,13722,13713,13697,13605,13482,13413,13264,13661,13633,13496,13365,13757,13733,13724,13723,13715,13627,13621,13574,13567,13566,13555,13553,13543,13535,13503,13498,13427,13393,13624,13623,13550,13263,13734,13725,13415,13351,13268,13736,13634,13475,13417,13544,13397,13285,13284,13735,13341,13799,13785,13766,13730,13702,13696,13692,13686,13682,13677,13665,13590,13589,13568,13565,13536,13518,13516,13513,13433,13411,13405,13368,13319,13291,13265,13797,13788,13783,13769,13746,13745,13738,13729,13720,13719,13711,13691,13676,13669,13663,13650,13645,13644,13642,13636,13626,13602,13586,13552,13545,13542,13540,13514,13500,13499,13481,13476,13449,13448,13447,13445,13437,13434,13432,13430,13428,13426,13423,13416,13392,13387,13376,13373,13366,13364,13361,13336,13335,13318,13313,13312,13303,13302,13290,13269,13710,13292,13750,13286,13382,13267,13739,13716,13469,13271,13334,13547,13497,13480,13394,13391,13277,13807,13764,13554,13531,13528,13472,13406,13330,13307,13638,13635,13741,13495,13283,13714,13375,13629,13622,13420,13616,13506,13777,13759,13668,13580,13573,13502,13484,13473,13421,13352,13343,13282,13709,13706,13620,13470,13332,13509,13649,13765,13763,13666,13628,13570,13563,13541,13489,13487,13471,13425,13422,13418,13333,13273,13614,13533,13524,13460,13776,13694,13680,13615,13520,13453,13331,13281,13275,13558,13485,13310,13551,13753,13295,13266,13279,13805,13804,13801,13796,13756,13754,13751,13701,13700,13695,13693,13664,13654,13593,13587,13575,13571,13564,13556,13523,13507,13505,13501,13492,13450,13446,13431,13429,13372,13363,13358,13309,13305,13379,13272,13591,13486,13340,13537,13560,13410,13354,13755,13749,13740,13647,13561,13559,13288,13287,13625,13398,13339,13338,13294,13742,13640,13630,13349,13347,13346,13737,13643,13639,13490,13479,13478,13386,13270,13760,13659,13648 ) order by id_order desc';
		return Order::q( $query );
	}

	// get restaurants that we need to pay
	public static function restaurants($filters = []) {
		$q = 'SELECT restaurant.*, MAX(p.date) AS last_pay, p.id_restaurant AS p_id_rest
					FROM restaurant
						LEFT OUTER JOIN ( SELECT id_restaurant, `date` FROM `payment` ) AS p using(id_restaurant)
						INNER JOIN restaurant_payment_type rpt ON rpt.id_restaurant = restaurant.id_restaurant
					WHERE active=1 ';
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
	public function driversProcessOrders( $orders ){
		$pay = [];
		foreach ( $orders as $order ) {

			if( $order && $order[ 'id_admin' ] ){
				// Refunded orders are not paid
 				if( $order[ 'refunded' ] == 1 ){
					continue;
				}

				$driver = $order[ 'id_admin' ];
				if( !$pay[ $driver ] ){
					$pay[ $driver ] = [ 'subtotal' => 0, 'tax' => 0, 'delivery_fee' => 0, 'tip' => 0, 'customer_fee' => 0, 'markup' => 0, 'credit_charge' => 0, 'restaurant_fee' => 0, 'gift_card' => 0, 'orders' => [] ];
					$pay[ $driver ][ 'id_admin' ] = $driver;
					$pay[ $driver ][ 'name' ] = $order[ 'driver' ];
				}


				if( $order[ 'do_not_pay_driver' ] == 1 ){
					continue;
				}
				$order[ 'pay_info' ] = [];
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

				$pay[ $driver ][ 'orders' ][] = $order;
				// echo json_encode( $order_info );exit;
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

			}
		}

		foreach( $pay as $key => $val ){
			$pay[ $key ][ 'total_payment' ] = $this->orderCalculateTotalDueDriver( $pay[ $key ] );
		}

		usort( $pay, function( $a, $b ) {
			return $a[ 'name'] > $b[ 'name' ];
		});

		return $pay;
	}

	public function orderCalculateTotalDueDriver( $pay ){
		$total_due = 	$pay[ 'subtotal' ] +
									$pay[ 'tax' ] +
									$pay[ 'delivery_fee' ] +
									$pay[ 'tip' ] +
									$pay[ 'customer_fee' ] +
									$pay[ 'markup' ] +
									$pay[ 'credit_charge' ] +
									$pay[ 'restaurant_fee' ] +
									$pay[ 'gift_card' ] -
									$pay[ 'total_reimburse' ];
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
		return ( $arr[ 'subtotal' ] + $arr[ 'tax' ] ) * $arr[ 'credit' ] * $arr[ 'delivery_service' ];
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

		$values[ 'restaurant_paid' ] = Cockpit_Payment_Schedule_Order::checkOrderWasPaidRestaurant( $order->id_order );
		if( !$values[ 'restaurant_paid' ] ){
			$values[ 'restaurant_paid' ] = Crunchbutton_Order_Transaction::checkOrderWasPaidRestaurant( $order->id_order );
		}

		// convert all to float -> mysql returns some values as string
		foreach( $values as $key => $val ){
			$values[ $key ] = floatval( $val );
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
			// todo: build a better way to filter - this way is very ugly
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
		// Cana::timeout(function() use( $settlement ) {
			$settlement->doRestaurantPayments();
		// } );
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
			$summary[ 'restaurant' ] = $schedule->restaurant()->name;
			$summary[ 'payment_method' ] = $schedule->restaurant()->payment_type()->payment_method;
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
						} catch( Exception $e ) {
							$schedule->log = $e->getMessage();
							$schedule->status = Cockpit_Payment_Schedule::STATUS_ERROR;
							$schedule->status_date = date( 'Y-m-d H:i:s' );
							$schedule->save();
							return false;
						}

						if( $c && $c->id ){

							$payment = new Crunchbutton_Payment;
							$payment->check_id = $c->id;
							$payment->date = date( 'Y-m-d H:i:s' );
							$payment->id_restaurant = $schedule->id_restaurant;
							$payment->note = $schedule->note;
							$payment->env = c::getEnv();
							$payment->id_admin = $schedule->id_admin;
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

	private function log( $method, $message ){
		Log::debug( [ 'method' => $method, 'id_admin' => c::user()->id_admin, 'message' => $message, 'env' => c::getEnv(), 'type' => 'settlement' ] );
	}
}
