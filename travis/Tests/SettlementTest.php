<?php
// phpunit --configuration travis/phpunit.xml  travis/Tests/SettlementTest.php
class SettlementTest extends PHPUnit_Framework_TestCase {

	public function setUp() {

		$this->restaurant_orders_formal_relationship = [];
		$this->restaurant_orders_no_formal_relationship = [];

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

		$this->settlement = new Crunchbutton_Settlement;
	}

	public function testDriversOrdersMath() {

		// id_orders: 140548,140827,140893,141037,141046
		$orders = unserialize( 'a:5:{i:0;a:48:{s:8:"subtotal";d:8.4600000000000009;s:3:"tax";d:0.76000000000000001;s:3:"tip";d:2.5;s:32:"final_price_plus_delivery_markup";d:17.109999999999999;s:12:"delivery_fee";d:3;s:11:"service_fee";d:0;s:12:"customer_fee";d:0;s:20:"customer_fee_percent";d:0;s:22:"restaurant_fee_percent";d:0;s:23:"delivery_service_markup";d:20;s:29:"delivery_service_markup_value";d:2.3900000000000001;s:8:"id_admin";d:2073;s:13:"pay_type_hour";d:0;s:14:"pay_type_order";d:0;s:8:"id_order";d:140548;s:15:"gift_card_total";d:0;s:30:"gift_card_paid_by_crunchbutton";d:0;s:28:"gift_card_paid_by_restaurant";d:0;s:29:"gift_card_paid_by_promotional";d:0;s:34:"gift_card_paid_by_other_restaurant";d:0;s:13:"total_charged";d:17.109999999999999;s:17:"promotion_maximum";d:0;s:18:"max_apology_credit";d:0;s:6:"credit";d:1;s:4:"cash";d:0;s:17:"charge_credit_fee";d:1;s:17:"pay_credit_charge";d:1;s:13:"pay_promotion";d:0;s:20:"just_fee_on_subtotal";d:0;s:16:"delivery_service";d:1;s:19:"formal_relationship";d:0;s:17:"paid_with_cb_card";d:0;s:8:"refunded";d:0;s:21:"do_not_pay_restaurant";d:0;s:17:"do_not_pay_driver";d:0;s:23:"do_not_reimburse_driver";d:0;s:15:"pay_if_refunded";d:0;s:20:"reimburse_cash_order";d:0;s:15:"restaurant_paid";b:0;s:17:"driver_reimbursed";b:1;s:11:"driver_paid";b:0;s:6:"driver";s:12:"Andy Bearden";s:4:"name";s:14:"William Hughes";s:8:"pay_type";s:4:"card";s:10:"restaurant";s:9:"Taco Bell";s:4:"date";s:34:"Apr 29th 2015 (Wed) 8:03:58 PM CDT";s:14:"formatted_date";s:8:"20150429";s:10:"short_date";s:19:"Apr 29th 2015 (Wed)";}i:1;a:48:{s:8:"subtotal";d:8.9700000000000006;s:3:"tax";d:0.81000000000000005;s:3:"tip";d:0;s:32:"final_price_plus_delivery_markup";d:15.380000000000001;s:12:"delivery_fee";d:3;s:11:"service_fee";d:0;s:12:"customer_fee";d:0;s:20:"customer_fee_percent";d:0;s:22:"restaurant_fee_percent";d:0;s:23:"delivery_service_markup";d:20;s:29:"delivery_service_markup_value";d:2.6000000000000001;s:8:"id_admin";d:2073;s:13:"pay_type_hour";d:0;s:14:"pay_type_order";d:0;s:8:"id_order";d:140827;s:15:"gift_card_total";d:0;s:30:"gift_card_paid_by_crunchbutton";d:0;s:28:"gift_card_paid_by_restaurant";d:0;s:29:"gift_card_paid_by_promotional";d:0;s:34:"gift_card_paid_by_other_restaurant";d:0;s:13:"total_charged";d:15.380000000000001;s:17:"promotion_maximum";d:0;s:18:"max_apology_credit";d:0;s:6:"credit";d:1;s:4:"cash";d:0;s:17:"charge_credit_fee";d:1;s:17:"pay_credit_charge";d:1;s:13:"pay_promotion";d:0;s:20:"just_fee_on_subtotal";d:0;s:16:"delivery_service";d:1;s:19:"formal_relationship";d:0;s:17:"paid_with_cb_card";d:0;s:8:"refunded";d:0;s:21:"do_not_pay_restaurant";d:0;s:17:"do_not_pay_driver";d:0;s:23:"do_not_reimburse_driver";d:0;s:15:"pay_if_refunded";d:0;s:20:"reimburse_cash_order";d:0;s:15:"restaurant_paid";b:0;s:17:"driver_reimbursed";b:1;s:11:"driver_paid";b:0;s:6:"driver";s:12:"Andy Bearden";s:4:"name";s:15:"Brandon Luckham";s:8:"pay_type";s:4:"card";s:10:"restaurant";s:9:"Taco Bell";s:4:"date";s:34:"Apr 29th 2015 (Wed) 9:43:26 PM CDT";s:14:"formatted_date";s:8:"20150429";s:10:"short_date";s:19:"Apr 29th 2015 (Wed)";}i:2;a:48:{s:8:"subtotal";d:8.5700000000000003;s:3:"tax";d:0.77000000000000002;s:3:"tip";d:2.5;s:32:"final_price_plus_delivery_markup";d:17.43;s:12:"delivery_fee";d:3;s:11:"service_fee";d:0;s:12:"customer_fee";d:0;s:20:"customer_fee_percent";d:0;s:22:"restaurant_fee_percent";d:0;s:23:"delivery_service_markup";d:20;s:29:"delivery_service_markup_value";d:2.5800000000000001;s:8:"id_admin";d:2073;s:13:"pay_type_hour";d:0;s:14:"pay_type_order";d:0;s:8:"id_order";d:140893;s:15:"gift_card_total";d:0;s:30:"gift_card_paid_by_crunchbutton";d:0;s:28:"gift_card_paid_by_restaurant";d:0;s:29:"gift_card_paid_by_promotional";d:0;s:34:"gift_card_paid_by_other_restaurant";d:0;s:13:"total_charged";d:17.43;s:17:"promotion_maximum";d:0;s:18:"max_apology_credit";d:0;s:6:"credit";d:1;s:4:"cash";d:0;s:17:"charge_credit_fee";d:1;s:17:"pay_credit_charge";d:1;s:13:"pay_promotion";d:0;s:20:"just_fee_on_subtotal";d:0;s:16:"delivery_service";d:1;s:19:"formal_relationship";d:0;s:17:"paid_with_cb_card";d:0;s:8:"refunded";d:0;s:21:"do_not_pay_restaurant";d:0;s:17:"do_not_pay_driver";d:0;s:23:"do_not_reimburse_driver";d:0;s:15:"pay_if_refunded";d:0;s:20:"reimburse_cash_order";d:0;s:15:"restaurant_paid";b:0;s:17:"driver_reimbursed";b:1;s:11:"driver_paid";b:0;s:6:"driver";s:12:"Andy Bearden";s:4:"name";s:13:"robert duncan";s:8:"pay_type";s:4:"card";s:10:"restaurant";s:9:"McDonalds";s:4:"date";s:35:"Apr 29th 2015 (Wed) 10:08:24 PM CDT";s:14:"formatted_date";s:8:"20150429";s:10:"short_date";s:19:"Apr 29th 2015 (Wed)";}i:3;a:48:{s:8:"subtotal";d:10.470000000000001;s:3:"tax";d:0.93999999999999995;s:3:"tip";d:2.5;s:32:"final_price_plus_delivery_markup";d:20.18;s:12:"delivery_fee";d:3;s:11:"service_fee";d:0;s:12:"customer_fee";d:0;s:20:"customer_fee_percent";d:0;s:22:"restaurant_fee_percent";d:0;s:23:"delivery_service_markup";d:20;s:29:"delivery_service_markup_value";d:3.2599999999999998;s:8:"id_admin";d:2073;s:13:"pay_type_hour";d:0;s:14:"pay_type_order";d:0;s:8:"id_order";d:141037;s:15:"gift_card_total";d:3;s:30:"gift_card_paid_by_crunchbutton";d:3;s:28:"gift_card_paid_by_restaurant";d:0;s:29:"gift_card_paid_by_promotional";d:0;s:34:"gift_card_paid_by_other_restaurant";d:0;s:13:"total_charged";d:17.18;s:17:"promotion_maximum";d:0;s:18:"max_apology_credit";d:0;s:6:"credit";d:1;s:4:"cash";d:0;s:17:"charge_credit_fee";d:1;s:17:"pay_credit_charge";d:1;s:13:"pay_promotion";d:0;s:20:"just_fee_on_subtotal";d:0;s:16:"delivery_service";d:1;s:19:"formal_relationship";d:0;s:17:"paid_with_cb_card";d:0;s:8:"refunded";d:0;s:21:"do_not_pay_restaurant";d:0;s:17:"do_not_pay_driver";d:0;s:23:"do_not_reimburse_driver";d:0;s:15:"pay_if_refunded";d:0;s:20:"reimburse_cash_order";d:0;s:15:"restaurant_paid";b:0;s:17:"driver_reimbursed";b:1;s:11:"driver_paid";b:0;s:6:"driver";s:12:"Andy Bearden";s:4:"name";s:14:"Andrew Capecci";s:8:"pay_type";s:4:"card";s:10:"restaurant";s:9:"McDonalds";s:4:"date";s:35:"Apr 29th 2015 (Wed) 11:06:35 PM CDT";s:14:"formatted_date";s:8:"20150429";s:10:"short_date";s:19:"Apr 29th 2015 (Wed)";}i:4;a:48:{s:8:"subtotal";d:6.6699999999999999;s:3:"tax";d:0.59999999999999998;s:3:"tip";d:0;s:32:"final_price_plus_delivery_markup";d:12.17;s:12:"delivery_fee";d:3;s:11:"service_fee";d:0;s:12:"customer_fee";d:0;s:20:"customer_fee_percent";d:0;s:22:"restaurant_fee_percent";d:0;s:23:"delivery_service_markup";d:20;s:29:"delivery_service_markup_value";d:1.8999999999999999;s:8:"id_admin";d:2073;s:13:"pay_type_hour";d:0;s:14:"pay_type_order";d:0;s:8:"id_order";d:141046;s:15:"gift_card_total";d:0;s:30:"gift_card_paid_by_crunchbutton";d:0;s:28:"gift_card_paid_by_restaurant";d:0;s:29:"gift_card_paid_by_promotional";d:0;s:34:"gift_card_paid_by_other_restaurant";d:0;s:13:"total_charged";d:12.17;s:17:"promotion_maximum";d:0;s:18:"max_apology_credit";d:0;s:6:"credit";d:0;s:4:"cash";d:1;s:17:"charge_credit_fee";d:1;s:17:"pay_credit_charge";d:1;s:13:"pay_promotion";d:0;s:20:"just_fee_on_subtotal";d:0;s:16:"delivery_service";d:1;s:19:"formal_relationship";d:0;s:17:"paid_with_cb_card";d:0;s:8:"refunded";d:0;s:21:"do_not_pay_restaurant";d:0;s:17:"do_not_pay_driver";d:0;s:23:"do_not_reimburse_driver";d:0;s:15:"pay_if_refunded";d:0;s:20:"reimburse_cash_order";d:0;s:15:"restaurant_paid";b:0;s:17:"driver_reimbursed";b:1;s:11:"driver_paid";b:0;s:6:"driver";s:12:"Andy Bearden";s:4:"name";s:25:"Benjamin Alexander Lorick";s:8:"pay_type";s:4:"cash";s:10:"restaurant";s:9:"McDonalds";s:4:"date";s:35:"Apr 29th 2015 (Wed) 11:17:23 PM CDT";s:14:"formatted_date";s:8:"20150429";s:10:"short_date";s:19:"Apr 29th 2015 (Wed)";}}' );

		$calcs = $this->settlement->driversProcess( $orders, true, false, false );

		$totals = $calcs[ 0 ];

		$this->assertEquals( $totals[ 'subtotal' ], 36.47 );
		$this->assertEquals( $totals[ 'tax' ], 3.28 );
		$this->assertEquals( $totals[ 'delivery_fee' ], 12 );
		$this->assertEquals( $totals[ 'tip' ], 7.5 );
		$this->assertEquals( $totals[ 'customer_fee' ], 0 );
		$this->assertEquals( $totals[ 'markup' ], -1.9 );
		$this->assertEquals( $totals[ 'credit_charge' ], 0 );
		$this->assertEquals( $totals[ 'restaurant_fee' ], 0 );
		$this->assertEquals( $totals[ 'gift_card' ], 0 );
		$this->assertEquals( $totals[ 'delivery_fee_collected' ] , -3 );
		$this->assertEquals( $totals[ 'standard_reimburse' ] , 39.75 );
		$this->assertEquals( $totals[ 'total_reimburse' ], 39.75 );
		$this->assertEquals( $totals[ 'total_payment' ], 2.6 );
		$this->assertEquals( $totals[ 'total_payment_per_order' ], 14.6 );
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
