<?php

class SettlementTest extends PHPUnit_Framework_TestCase {

	public function setUp() {

		$this->restaurant_orders_formal_relationship = [];
		$this->restaurant_orders_no_formal_relationship = [];
		$this->driver_orders = [];

		// values of id_order: 24515
		$this->restaurant_orders_formal_relationship[] = ['subtotal' => 12.48, 'tax' => 0.79, 'tip' => 2.25, 'delivery_fee' => 0, 'service_fee' => 0, 'customer_fee' => 0, 'customer_fee_percent' => 0, 'restaurant_fee_percent' => 10, 'delivery_service_markup' => 0, 'delivery_service_markup_value' => 0, 'id_admin' => 0, 'gift_card_total' => 0, 'gift_card_paid_by_crunchbutton' => 0, 'gift_card_paid_by_restaurant' => 0, 'gift_card_paid_by_promotional' => 0, 'gift_card_paid_by_other_restaurant' => 0, 'total_charged' => 15.52, 'promotion_maximum' => 2, 'max_apology_credit' => 5, 'credit' => 1, 'cash' => 0, 'charge_credit_fee' => 1, 'pay_credit_charge' => 1, 'pay_promotion' => 1, 'just_fee_on_subtotal' => 0, 'delivery_service' => 0, 'formal_relationship' => 1, 'paid_with_cb_card' => 0, 'refunded' => 0, 'pay_if_refunded' => 0];
		// values of id_order: 24505
		$this->restaurant_orders_formal_relationship[] = ['subtotal' => 9.34, 'tax' => 0.59, 'tip' => 2, 'delivery_fee' => 0, 'service_fee' => 0, 'customer_fee' => 0, 'customer_fee_percent' => 0, 'restaurant_fee_percent' => 10, 'delivery_service_markup' => 0, 'delivery_service_markup_value' => 0, 'id_admin' => 0, 'gift_card_total' => 0, 'gift_card_paid_by_crunchbutton' => 0, 'gift_card_paid_by_restaurant' => 0, 'gift_card_paid_by_promotional' => 0, 'gift_card_paid_by_other_restaurant' => 0, 'total_charged' => 11.93, 'promotion_maximum' => 2, 'max_apology_credit' => 5, 'credit' => 1, 'cash' => 0, 'charge_credit_fee' => 1, 'pay_credit_charge' => 1, 'pay_promotion' => 1, 'just_fee_on_subtotal' => 0, 'delivery_service' => 0, 'formal_relationship' => 1, 'paid_with_cb_card' => 0, 'refunded' => 0, 'pay_if_refunded' => 0];
		// values of id_order: 24497
		$this->restaurant_orders_formal_relationship[] = ['subtotal' => 8.99, 'tax' => 0.57, 'tip' => 0, 'delivery_fee' => 0, 'service_fee' => 0, 'customer_fee' => 0, 'customer_fee_percent' => 0, 'restaurant_fee_percent' => 10, 'delivery_service_markup' => 0, 'delivery_service_markup_value' => 0, 'id_admin' => 0, 'gift_card_total' => 0, 'gift_card_paid_by_crunchbutton' => 0, 'gift_card_paid_by_restaurant' => 0, 'gift_card_paid_by_promotional' => 0, 'gift_card_paid_by_other_restaurant' => 0, 'total_charged' => 9.56, 'promotion_maximum' => 2, 'max_apology_credit' => 5, 'credit' => 0, 'cash' => 1, 'charge_credit_fee' => 1, 'pay_credit_charge' => 1, 'pay_promotion' => 1, 'just_fee_on_subtotal' => 0, 'delivery_service' => 0, 'formal_relationship' => 1, 'paid_with_cb_card' => 0, 'refunded' => 0, 'pay_if_refunded' => 0];
		// values of id_order: 24420
		$this->restaurant_orders_formal_relationship[] = ['subtotal' => 10.98, 'tax' => 0.7, 'tip' => 1.1, 'delivery_fee' => 0, 'service_fee' => 0, 'customer_fee' => 0, 'customer_fee_percent' => 0, 'restaurant_fee_percent' => 10, 'delivery_service_markup' => 0, 'delivery_service_markup_value' => 0, 'id_admin' => 0, 'gift_card_total' => 0, 'gift_card_paid_by_crunchbutton' => 0, 'gift_card_paid_by_restaurant' => 0, 'gift_card_paid_by_promotional' => 0, 'gift_card_paid_by_other_restaurant' => 0, 'total_charged' => 12.78, 'promotion_maximum' => 2, 'max_apology_credit' => 5, 'credit' => 1, 'cash' => 0, 'charge_credit_fee' => 1, 'pay_credit_charge' => 1, 'pay_promotion' => 1, 'just_fee_on_subtotal' => 0, 'delivery_service' => 0, 'formal_relationship' => 1, 'paid_with_cb_card' => 0, 'refunded' => 0, 'pay_if_refunded' => 0];
		// values of id_order: 24407
		$this->restaurant_orders_formal_relationship[] = ['subtotal' => 8.99, 'tax' => 0.57, 'tip' => 1.35, 'delivery_fee' => 0, 'service_fee' => 0, 'customer_fee' => 0, 'customer_fee_percent' => 0, 'restaurant_fee_percent' => 10, 'delivery_service_markup' => 0, 'delivery_service_markup_value' => 0, 'id_admin' => 0, 'gift_card_total' => 0, 'gift_card_paid_by_crunchbutton' => 0, 'gift_card_paid_by_restaurant' => 0, 'gift_card_paid_by_promotional' => 0, 'gift_card_paid_by_other_restaurant' => 0, 'total_charged' => 10.91, 'promotion_maximum' => 2, 'max_apology_credit' => 5, 'credit' => 1, 'cash' => 0, 'charge_credit_fee' => 1, 'pay_credit_charge' => 1, 'pay_promotion' => 1, 'just_fee_on_subtotal' => 0, 'delivery_service' => 0, 'formal_relationship' => 1, 'paid_with_cb_card' => 0, 'refunded' => 0, 'pay_if_refunded' => 0];

		// values of id_order: 24482
		$this->restaurant_orders_no_formal_relationship[] = ['subtotal' => 2.59, 'tax' => 0.23, 'tip' => 1.25, 'delivery_fee' => 3, 'service_fee' => 0, 'customer_fee' => 0, 'customer_fee_percent' => 0, 'restaurant_fee_percent' => 0, 'delivery_service_markup' => 20, 'delivery_service_markup_value' => 0.52, 'id_admin' => 205, 'gift_card_total' => 0, 'gift_card_paid_by_crunchbutton' => 0, 'gift_card_paid_by_restaurant' => 0, 'gift_card_paid_by_promotional' => 0, 'gift_card_paid_by_other_restaurant' => 0, 'total_charged' => 7.59, 'promotion_maximum' => 2, 'max_apology_credit' => 0, 'credit' => 1, 'cash' => 0, 'charge_credit_fee' => 1, 'pay_credit_charge' => 1, 'pay_promotion' => 1, 'just_fee_on_subtotal' => 0, 'delivery_service' => 1, 'formal_relationship' => 0, 'paid_with_cb_card' => 0, 'refunded' => 0, 'pay_if_refunded' => 0];
		// values of id_order: 24459
		$this->restaurant_orders_no_formal_relationship[] = ['subtotal' => 11.46, 'tax' => 1.03, 'tip' => 2.5, 'delivery_fee' => 3, 'service_fee' => 0, 'customer_fee' => 0, 'customer_fee_percent' => 0, 'restaurant_fee_percent' => 0, 'delivery_service_markup' => 20, 'delivery_service_markup_value' => 2.3, 'id_admin' => 72, 'gift_card_total' => 0, 'gift_card_paid_by_crunchbutton' => 0, 'gift_card_paid_by_restaurant' => 0, 'gift_card_paid_by_promotional' => 0, 'gift_card_paid_by_other_restaurant' => 0, 'total_charged' => 20.29, 'promotion_maximum' => 2, 'max_apology_credit' => 0, 'credit' => 1, 'cash' => 0, 'charge_credit_fee' => 1, 'pay_credit_charge' => 1, 'pay_promotion' => 1, 'just_fee_on_subtotal' => 0, 'delivery_service' => 1, 'formal_relationship' => 0, 'paid_with_cb_card' => 0, 'refunded' => 0, 'pay_if_refunded' => 0];

		// id_order: 24474
		$this->driver_orders[] = ["subtotal" => "9.96","tax" => "0.75","tip" => "2.25","delivery_fee" => "2","service_fee" => "0","customer_fee" => "0","customer_fee_percent" => "0","restaurant_fee_percent" => "0","delivery_service_markup" => "20","delivery_service_markup_value" => "1.99","id_admin" => "209","id_order" => "24474","gift_card_total" => "0","gift_card_paid_by_crunchbutton" => "0","gift_card_paid_by_restaurant" => "0","gift_card_paid_by_promotional" => "0","gift_card_paid_by_other_restaurant" => "0","total_charged" => "16.96","promotion_maximum" => "2","max_apology_credit" => "0","credit" => "1","cash" => "0","charge_credit_fee" => "1","pay_credit_charge" => "1","pay_promotion" => "1","just_fee_on_subtotal" => "0","delivery_service" => "1","formal_relationship" => "0","paid_with_cb_card" => "0","refunded" => "0","pay_if_refunded" => "0","driver" => "Steven Frasica"];
		// id_order: 24473
		$this->driver_orders[] = ["subtotal" => "6.65","tax" => "0.5","tip" => "1.2","delivery_fee" => "2","service_fee" => "0","customer_fee" => "0","customer_fee_percent" => "0","restaurant_fee_percent" => "0","delivery_service_markup" => "20","delivery_service_markup_value" => "1.33","id_admin" => "209","id_order" => "24473","gift_card_total" => "0","gift_card_paid_by_crunchbutton" => "0","gift_card_paid_by_restaurant" => "0","gift_card_paid_by_promotional" => "0","gift_card_paid_by_other_restaurant" => "0","total_charged" => "11.68","promotion_maximum" => "2","max_apology_credit" => "0","credit" => "1","cash" => "0","charge_credit_fee" => "1","pay_credit_charge" => "1","pay_promotion" => "1","just_fee_on_subtotal" => "0","delivery_service" => "1","formal_relationship" => "0","paid_with_cb_card" => "0","refunded" => "0","pay_if_refunded" => "0","driver" => "Steven Frasica"];
		// id_order: 24472
		$this->driver_orders[] = ["subtotal" => "6.65","tax" => "0.5","tip" => "1.75","delivery_fee" => "2","service_fee" => "0","customer_fee" => "0","customer_fee_percent" => "0","restaurant_fee_percent" => "0","delivery_service_markup" => "20","delivery_service_markup_value" => "1.33","id_admin" => "209","id_order" => "24472","gift_card_total" => "0","gift_card_paid_by_crunchbutton" => "0","gift_card_paid_by_restaurant" => "0","gift_card_paid_by_promotional" => "0","gift_card_paid_by_other_restaurant" => "0","total_charged" => "12.23","promotion_maximum" => "2","max_apology_credit" => "0","credit" => "1","cash" => "0","charge_credit_fee" => "1","pay_credit_charge" => "1","pay_promotion" => "1","just_fee_on_subtotal" => "0","delivery_service" => "1","formal_relationship" => "0","paid_with_cb_card" => "0","refunded" => "0","pay_if_refunded" => "0","driver" => "Steven Frasica"];
		// id_order: 24419
		$this->driver_orders[] = ["subtotal" => "11.1","tax" => "0.83","tip" => "0","delivery_fee" => "2","service_fee" => "0","customer_fee" => "0","customer_fee_percent" => "0","restaurant_fee_percent" => "0","delivery_service_markup" => "20","delivery_service_markup_value" => "2.22","id_admin" => "209","id_order" => "24419","gift_card_total" => "0","gift_card_paid_by_crunchbutton" => "0","gift_card_paid_by_restaurant" => "0","gift_card_paid_by_promotional" => "0","gift_card_paid_by_other_restaurant" => "0","total_charged" => "16.15","promotion_maximum" => "2","max_apology_credit" => "0","credit" => "0","cash" => "1","charge_credit_fee" => "1","pay_credit_charge" => "1","pay_promotion" => "1","just_fee_on_subtotal" => "0","delivery_service" => "1","formal_relationship" => "0","paid_with_cb_card" => "0","refunded" => "0","pay_if_refunded" => "0","driver" => "Steven Frasica"];
		// id_order: 24415
		$this->driver_orders[] = ["subtotal" => "6.65","tax" => "0.5","tip" => "0.8","delivery_fee" => "2","service_fee" => "0","customer_fee" => "0","customer_fee_percent" => "0","restaurant_fee_percent" => "0","delivery_service_markup" => "20","delivery_service_markup_value" => "1.33","id_admin" => "209","id_order" => "24415","gift_card_total" => "0","gift_card_paid_by_crunchbutton" => "0","gift_card_paid_by_restaurant" => "0","gift_card_paid_by_promotional" => "0","gift_card_paid_by_other_restaurant" => "0","total_charged" => "11.28","promotion_maximum" => "2","max_apology_credit" => "0","credit" => "1","cash" => "0","charge_credit_fee" => "1","pay_credit_charge" => "1","pay_promotion" => "1","just_fee_on_subtotal" => "0","delivery_service" => "1","formal_relationship" => "0","paid_with_cb_card" => "0","refunded" => "0","pay_if_refunded" => "0","driver" => "Steven Frasica"];
		// id_order: 24471
		$this->driver_orders[] = ["subtotal" => "12.5","tax" => "0.97","tip" => "2.25","delivery_fee" => "2","service_fee" => "0","customer_fee" => "0","customer_fee_percent" => "0","restaurant_fee_percent" => "15","delivery_service_markup" => "0","delivery_service_markup_value" => "0","id_admin" => "209","id_order" => "24471","gift_card_total" => "0","gift_card_paid_by_crunchbutton" => "0","gift_card_paid_by_restaurant" => "0","gift_card_paid_by_promotional" => "0","gift_card_paid_by_other_restaurant" => "0","total_charged" => "17.72","promotion_maximum" => "2","max_apology_credit" => "0","credit" => "1","cash" => "0","charge_credit_fee" => "1","pay_credit_charge" => "1","pay_promotion" => "1","just_fee_on_subtotal" => "0","delivery_service" => "1","formal_relationship" => "1","paid_with_cb_card" => "0","refunded" => "0","pay_if_refunded" => "0","driver" => "Steven Frasica"];
		// id_order: 24416
		$this->driver_orders[] = ["subtotal" => "9.45","tax" => "0.71","tip" => "1.7","delivery_fee" => "2","service_fee" => "0","customer_fee" => "0","customer_fee_percent" => "0","restaurant_fee_percent" => "15","delivery_service_markup" => "0","delivery_service_markup_value" => "0","id_admin" => "209","id_order" => "24416","gift_card_total" => "0","gift_card_paid_by_crunchbutton" => "0","gift_card_paid_by_restaurant" => "0","gift_card_paid_by_promotional" => "0","gift_card_paid_by_other_restaurant" => "0","total_charged" => "13.87","promotion_maximum" => "2","max_apology_credit" => "0","credit" => "1","cash" => "0","charge_credit_fee" => "1","pay_credit_charge" => "1","pay_promotion" => "1","just_fee_on_subtotal" => "0","delivery_service" => "1","formal_relationship" => "1","paid_with_cb_card" => "0","refunded" => "0","pay_if_refunded" => "0","driver" => "Steven Frasica"];
		// id_order: 24414
		$this->driver_orders[] = ["subtotal" => "6.69","tax" => "0.52","tip" => "1.34","delivery_fee" => "3","service_fee" => "0","customer_fee" => "0","customer_fee_percent" => "0","restaurant_fee_percent" => "10","delivery_service_markup" => "0","delivery_service_markup_value" => "0","id_admin" => "209","id_order" => "24414","gift_card_total" => "0","gift_card_paid_by_crunchbutton" => "0","gift_card_paid_by_restaurant" => "0","gift_card_paid_by_promotional" => "0","gift_card_paid_by_other_restaurant" => "0","total_charged" => "11.56","promotion_maximum" => "2","max_apology_credit" => "0","credit" => "1","cash" => "0","charge_credit_fee" => "1","pay_credit_charge" => "1","pay_promotion" => "1","just_fee_on_subtotal" => "0","delivery_service" => "1","formal_relationship" => "1","paid_with_cb_card" => "0","refunded" => "0","pay_if_refunded" => "0","driver" => "Steven Frasica"];
		// id_order: 24413
		$this->driver_orders[] = ["subtotal" => "14.53","tax" => "1.13","tip" => "2.5","delivery_fee" => "3","service_fee" => "0","customer_fee" => "0","customer_fee_percent" => "0","restaurant_fee_percent" => "10","delivery_service_markup" => "0","delivery_service_markup_value" => "0","id_admin" => "209","id_order" => "24413","gift_card_total" => "0","gift_card_paid_by_crunchbutton" => "0","gift_card_paid_by_restaurant" => "0","gift_card_paid_by_promotional" => "0","gift_card_paid_by_other_restaurant" => "0","total_charged" => "21.16","promotion_maximum" => "2","max_apology_credit" => "0","credit" => "1","cash" => "0","charge_credit_fee" => "1","pay_credit_charge" => "1","pay_promotion" => "1","just_fee_on_subtotal" => "0","delivery_service" => "1","formal_relationship" => "1","paid_with_cb_card" => "0","refunded" => "0","pay_if_refunded" => "0","driver" => "Steven Frasica"];
		// id_order: 24412
		$this->driver_orders[] = ["subtotal" => "9.75","tax" => "0.76","tip" => "1.07","delivery_fee" => "2","service_fee" => "0","customer_fee" => "0","customer_fee_percent" => "0","restaurant_fee_percent" => "10","delivery_service_markup" => "10","delivery_service_markup_value" => "0.98","id_admin" => "209","id_order" => "24412","gift_card_total" => "0","gift_card_paid_by_crunchbutton" => "0","gift_card_paid_by_restaurant" => "0","gift_card_paid_by_promotional" => "0","gift_card_paid_by_other_restaurant" => "0","total_charged" => "14.57","promotion_maximum" => "2","max_apology_credit" => "0","credit" => "1","cash" => "0","charge_credit_fee" => "1","pay_credit_charge" => "1","pay_promotion" => "1","just_fee_on_subtotal" => "0","delivery_service" => "1","formal_relationship" => "1","paid_with_cb_card" => "0","refunded" => "0","pay_if_refunded" => "0","driver" => "Steven Frasica"];

		$this->settlement = new Crunchbutton_Settlement;
	}

	public function testDriversMath() {

		$calcs = $this->settlement->driversProcess( $this->driver_orders, true, false );

		$totals = [];

		$calc = $calcs[ 14 ];

		// foreach( $calcs as $calc ){
			$totals[ 'subtotal' ] += $calc[ 'subtotal' ];
			$totals[ 'tax' ] += $calc[ 'tax' ];
			$totals[ 'delivery_fee' ] += $calc[ 'delivery_fee' ];
			$totals[ 'tip' ] += $calc[ 'tip' ];
			$totals[ 'customer_fee' ] += $calc[ 'customer_fee' ];
			$totals[ 'markup' ] += $calc[ 'markup' ];
			$totals[ 'credit_charge' ] += $calc[ 'credit_charge' ];
			$totals[ 'restaurant_fee' ] += $calc[ 'restaurant_fee' ];
			$totals[ 'gift_card' ] += $calc[ 'gift_card' ];
			$totals[ 'total_reimburse' ] += $calc[ 'total_reimburse' ];
			$totals[ 'total_payment' ] += $calc[ 'total_payment' ];
			$totals[ 'orders' ] += count( $calc[ 'orders' ] );
		// }

		foreach( $totals as $key => $val ){
			$totals[ $key ] = floatval( number_format( $val, 2 ) );
		}

		$this->assertEquals( $totals[ 'subtotal' ], 29.91 );
		$this->assertEquals( $totals[ 'tax' ], 2.25 );
		$this->assertEquals( $totals[ 'delivery_fee' ], 20 );
		$this->assertEquals( $totals[ 'tip' ], 14.86 );
		$this->assertEquals( $totals[ 'customer_fee' ], 0 );
		$this->assertEquals( $totals[ 'markup' ], -2.22 );
		$this->assertEquals( $totals[ 'credit_charge' ], 0 );
		$this->assertEquals( $totals[ 'restaurant_fee' ], 0 );
		$this->assertEquals( $totals[ 'gift_card' ], 0 );
		$this->assertEquals( $totals[ 'orders' ], 10 );
		$this->assertEquals( $totals[ 'total_reimburse' ], 32.16 );
		$this->assertEquals( $totals[ 'total_payment' ], 32.64 );

	}

	public function testRestaurantIndividualMathsCashOrderFormalRelationship() {
		$order = $this->restaurant_orders_formal_relationship[ 2 ];
		$this->assertEquals( $this->settlement->orderCardSubtotalPayment( $order ), 0 );
		$this->assertEquals( $this->settlement->orderTaxPayment( $order ), 0 );
		$this->assertEquals( $this->settlement->orderDeliveryFeePayment( $order ), 0 );
		$this->assertEquals( $this->settlement->orderTipPayment( $order ), 0 );
		$this->assertEquals( $this->settlement->orderCustomerFeePayment( $order ), 0 );
		$this->assertEquals( $this->settlement->orderMarkupPayment( $order ), 0 );
		$this->assertEquals( $this->settlement->orderCreditChargePayment( $order ), 0 );
		$this->assertEquals( $this->settlement->orderRestaurantFeePayment( $order ), -0.899 );
		$this->assertEquals( $this->settlement->orderPromoGiftCardPayment( $order ), 0 );
		$this->assertEquals( $this->settlement->orderApologyGiftCardPayment( $order ), 0 );
		$this->assertEquals( $this->settlement->orderRestaurantOrderPayment( $order ), 0 );
		$this->assertEquals( $this->settlement->orderCashSubtotalPayment( $order ), 9.56 );
	}

	public function testRestaurantIndividualMathsCreditOrderFormalRelationship() {
		$order = $this->restaurant_orders_formal_relationship[ 0 ];
		$this->assertEquals( $this->settlement->orderCardSubtotalPayment( $order ), 12.48 );
		$this->assertEquals( $this->settlement->orderTaxPayment( $order ), 0.79 );
		$this->assertEquals( $this->settlement->orderDeliveryFeePayment( $order ), 0 );
		$this->assertEquals( $this->settlement->orderTipPayment( $order ), 2.25 );
		$this->assertEquals( $this->settlement->orderCustomerFeePayment( $order ), 0 );
		$this->assertEquals( $this->settlement->orderMarkupPayment( $order ), 0 );
		$this->assertEquals( $this->settlement->orderCreditChargePayment( $order ), -0.75008 );
		$this->assertEquals( $this->settlement->orderRestaurantFeePayment( $order ), -1.473 );
		$this->assertEquals( $this->settlement->orderPromoGiftCardPayment( $order ), 0 );
		$this->assertEquals( $this->settlement->orderApologyGiftCardPayment( $order ), 0 );
		$this->assertEquals( $this->settlement->orderRestaurantOrderPayment( $order ), 15.52 );
		$this->assertEquals( $this->settlement->orderCashSubtotalPayment( $order ), 0 );
	}

	public function testRestaurantDueToPayFormalRelationship(){
		$pay = $this->settlement->restaurantsProcessOrders( $this->restaurant_orders_formal_relationship );
		$this->assertEquals( $pay[ 'card_subtotal' ], 41.79 );
		$this->assertEquals( $pay[ 'tax' ], 2.65 );
		$this->assertEquals( $pay[ 'delivery_fee' ], 0 );
		$this->assertEquals( $pay[ 'tip' ], 6.7 );
		$this->assertEquals( $pay[ 'customer_fee' ], 0 );
		$this->assertEquals( $pay[ 'markup' ], 0 );
		$this->assertEquals( $pay[ 'credit_charge' ], -2.68 );
		$this->assertEquals( $pay[ 'restaurant_fee' ], -5.75 );
		$this->assertEquals( $pay[ 'promo_gift_card' ], 0 );
		$this->assertEquals( $pay[ 'apology_gift_card' ], 0 );
		$this->assertEquals( $pay[ 'order_payment' ], 51.14 );
		$this->assertEquals( $pay[ 'cash_subtotal' ], 9.56 );
		$this->assertEquals( $pay[ 'formal_relationship' ], 1 );
		$this->assertEquals( $pay[ 'total_due' ], 42.71 );
	}

	public function testRestaurantIndividualMathsCashOrderNoFormalRelationship() {
		// values of id_order: 24419
		$order = ['subtotal' => 11.1, 'tax' => 0.83, 'tip' => 0, 'delivery_fee' => 2, 'service_fee' => 0, 'customer_fee' => 0, 'customer_fee_percent' => 0, 'restaurant_fee_percent' => 0, 'delivery_service_markup' => 20, 'delivery_service_markup_value' => 2.22, 'id_admin' => 209, 'gift_card_total' => 0, 'gift_card_paid_by_crunchbutton' => 0, 'gift_card_paid_by_restaurant' => 0, 'gift_card_paid_by_promotional' => 0, 'gift_card_paid_by_other_restaurant' => 0, 'total_charged' => 16.15, 'promotion_maximum' => 2, 'max_apology_credit' => 0, 'credit' => 0, 'cash' => 1, 'charge_credit_fee' => 1, 'pay_credit_charge' => 1, 'pay_promotion' => 1, 'just_fee_on_subtotal' => 0, 'delivery_service' => 1, 'formal_relationship' => 0, 'paid_with_cb_card' => 0, 'refunded' => 0, 'pay_if_refunded' => 0];
		$this->assertEquals( $this->settlement->orderCardSubtotalPayment( $order ), 0 );
		$this->assertEquals( $this->settlement->orderTaxPayment( $order ), 0 );
		$this->assertEquals( $this->settlement->orderDeliveryFeePayment( $order ), 0 );
		$this->assertEquals( $this->settlement->orderTipPayment( $order ), 0 );
		$this->assertEquals( $this->settlement->orderCustomerFeePayment( $order ), 0 );
		$this->assertEquals( $this->settlement->orderMarkupPayment( $order ), 0 );
		$this->assertEquals( $this->settlement->orderCreditChargePayment( $order ), 0 );
		$this->assertEquals( $this->settlement->orderRestaurantFeePayment( $order ), 0 );
		$this->assertEquals( $this->settlement->orderPromoGiftCardPayment( $order ), 0 );
		$this->assertEquals( $this->settlement->orderApologyGiftCardPayment( $order ), 0 );
		$this->assertEquals( $this->settlement->orderRestaurantOrderPayment( $order ), 0 );
		$this->assertEquals( $this->settlement->orderCashSubtotalPayment( $order ), 11.93 );
	}

	public function testRestaurantIndividualMathsCreditOrderNoFormalRelationship() {
		$order = $this->restaurant_orders_no_formal_relationship[ 0 ];
		$this->assertEquals( $this->settlement->orderCardSubtotalPayment( $order ), 2.59 );
		$this->assertEquals( $this->settlement->orderTaxPayment( $order ), 0.23 );
		$this->assertEquals( $this->settlement->orderDeliveryFeePayment( $order ), 0 );
		$this->assertEquals( $this->settlement->orderTipPayment( $order ), 0 );
		$this->assertEquals( $this->settlement->orderCustomerFeePayment( $order ), 0 );
		$this->assertEquals( $this->settlement->orderMarkupPayment( $order ), 0 );
		$this->assertEquals( $this->settlement->orderCreditChargePayment( $order ), -0.52011 );
		$this->assertEquals( $this->settlement->orderRestaurantFeePayment( $order ), 0 );
		$this->assertEquals( $this->settlement->orderPromoGiftCardPayment( $order ), 0 );
		$this->assertEquals( $this->settlement->orderApologyGiftCardPayment( $order ), 0 );
		$this->assertEquals( $this->settlement->orderRestaurantOrderPayment( $order ), 2.82 );
		$this->assertEquals( $this->settlement->orderCashSubtotalPayment( $order ), 0 );
	}

	public function testRestaurantDueToPayNoFormalRelationship(){
		$pay = $this->settlement->restaurantsProcessOrders( $this->restaurant_orders_no_formal_relationship );
		$this->assertEquals( $pay[ 'card_subtotal' ], 14.05 );
		$this->assertEquals( $pay[ 'tax' ], 1.26 );
		$this->assertEquals( $pay[ 'delivery_fee' ], 0 );
		$this->assertEquals( $pay[ 'tip' ], 0 );
		$this->assertEquals( $pay[ 'customer_fee' ], 0 );
		$this->assertEquals( $pay[ 'markup' ], 0 );
		$this->assertEquals( $pay[ 'credit_charge' ], -1.41 );
		$this->assertEquals( $pay[ 'restaurant_fee' ], 0 );
		$this->assertEquals( $pay[ 'promo_gift_card' ], 0 );
		$this->assertEquals( $pay[ 'apology_gift_card' ], 0 );
		$this->assertEquals( $pay[ 'order_payment' ], 15.31 );
		$this->assertEquals( $pay[ 'cash_subtotal' ], 0 );
		$this->assertEquals( $pay[ 'formal_relationship' ], 0 );
		$this->assertEquals( $pay[ 'total_due' ], 0 );
	}


	public function tearDown() {}

}
