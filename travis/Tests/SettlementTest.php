<?php

class SettlementTest extends PHPUnit_Framework_TestCase {

	public function setUp() {

		$this->orders_formal_relationship = [];
		$this->orders_no_formal_relationship = [];

		// values of id_order: 24515
		$this->orders_formal_relationship[] = ['subtotal' => 12.48, 'tax' => 0.79, 'tip' => 2.25, 'delivery_fee' => 0, 'service_fee' => 0, 'customer_fee' => 0, 'customer_fee_percent' => 0, 'restaurant_fee_percent' => 10, 'delivery_service_markup' => 0, 'delivery_service_markup_value' => 0, 'id_admin' => 0, 'gift_card_total' => 0, 'gift_card_paid_by_crunchbutton' => 0, 'gift_card_paid_by_restaurant' => 0, 'gift_card_paid_by_promotional' => 0, 'gift_card_paid_by_other_restaurant' => 0, 'total_charged' => 15.52, 'promotion_maximum' => 2, 'max_apology_credit' => 5, 'credit' => 1, 'cash' => 0, 'charge_credit_fee' => 1, 'pay_credit_charge' => 1, 'pay_promotion' => 1, 'just_fee_on_subtotal' => 0, 'delivery_service' => 0, 'formal_relationship' => 1, 'paid_with_cb_card' => 0, 'refunded' => 0, 'pay_if_refunded' => 0];
		// values of id_order: 24505
		$this->orders_formal_relationship[] = ['subtotal' => 9.34, 'tax' => 0.59, 'tip' => 2, 'delivery_fee' => 0, 'service_fee' => 0, 'customer_fee' => 0, 'customer_fee_percent' => 0, 'restaurant_fee_percent' => 10, 'delivery_service_markup' => 0, 'delivery_service_markup_value' => 0, 'id_admin' => 0, 'gift_card_total' => 0, 'gift_card_paid_by_crunchbutton' => 0, 'gift_card_paid_by_restaurant' => 0, 'gift_card_paid_by_promotional' => 0, 'gift_card_paid_by_other_restaurant' => 0, 'total_charged' => 11.93, 'promotion_maximum' => 2, 'max_apology_credit' => 5, 'credit' => 1, 'cash' => 0, 'charge_credit_fee' => 1, 'pay_credit_charge' => 1, 'pay_promotion' => 1, 'just_fee_on_subtotal' => 0, 'delivery_service' => 0, 'formal_relationship' => 1, 'paid_with_cb_card' => 0, 'refunded' => 0, 'pay_if_refunded' => 0];
		// values of id_order: 24497
		$this->orders_formal_relationship[] = ['subtotal' => 8.99, 'tax' => 0.57, 'tip' => 0, 'delivery_fee' => 0, 'service_fee' => 0, 'customer_fee' => 0, 'customer_fee_percent' => 0, 'restaurant_fee_percent' => 10, 'delivery_service_markup' => 0, 'delivery_service_markup_value' => 0, 'id_admin' => 0, 'gift_card_total' => 0, 'gift_card_paid_by_crunchbutton' => 0, 'gift_card_paid_by_restaurant' => 0, 'gift_card_paid_by_promotional' => 0, 'gift_card_paid_by_other_restaurant' => 0, 'total_charged' => 9.56, 'promotion_maximum' => 2, 'max_apology_credit' => 5, 'credit' => 0, 'cash' => 1, 'charge_credit_fee' => 1, 'pay_credit_charge' => 1, 'pay_promotion' => 1, 'just_fee_on_subtotal' => 0, 'delivery_service' => 0, 'formal_relationship' => 1, 'paid_with_cb_card' => 0, 'refunded' => 0, 'pay_if_refunded' => 0];
		// values of id_order: 24420
		$this->orders_formal_relationship[] = ['subtotal' => 10.98, 'tax' => 0.7, 'tip' => 1.1, 'delivery_fee' => 0, 'service_fee' => 0, 'customer_fee' => 0, 'customer_fee_percent' => 0, 'restaurant_fee_percent' => 10, 'delivery_service_markup' => 0, 'delivery_service_markup_value' => 0, 'id_admin' => 0, 'gift_card_total' => 0, 'gift_card_paid_by_crunchbutton' => 0, 'gift_card_paid_by_restaurant' => 0, 'gift_card_paid_by_promotional' => 0, 'gift_card_paid_by_other_restaurant' => 0, 'total_charged' => 12.78, 'promotion_maximum' => 2, 'max_apology_credit' => 5, 'credit' => 1, 'cash' => 0, 'charge_credit_fee' => 1, 'pay_credit_charge' => 1, 'pay_promotion' => 1, 'just_fee_on_subtotal' => 0, 'delivery_service' => 0, 'formal_relationship' => 1, 'paid_with_cb_card' => 0, 'refunded' => 0, 'pay_if_refunded' => 0];
		// values of id_order: 24407
		$this->orders_formal_relationship[] = ['subtotal' => 8.99, 'tax' => 0.57, 'tip' => 1.35, 'delivery_fee' => 0, 'service_fee' => 0, 'customer_fee' => 0, 'customer_fee_percent' => 0, 'restaurant_fee_percent' => 10, 'delivery_service_markup' => 0, 'delivery_service_markup_value' => 0, 'id_admin' => 0, 'gift_card_total' => 0, 'gift_card_paid_by_crunchbutton' => 0, 'gift_card_paid_by_restaurant' => 0, 'gift_card_paid_by_promotional' => 0, 'gift_card_paid_by_other_restaurant' => 0, 'total_charged' => 10.91, 'promotion_maximum' => 2, 'max_apology_credit' => 5, 'credit' => 1, 'cash' => 0, 'charge_credit_fee' => 1, 'pay_credit_charge' => 1, 'pay_promotion' => 1, 'just_fee_on_subtotal' => 0, 'delivery_service' => 0, 'formal_relationship' => 1, 'paid_with_cb_card' => 0, 'refunded' => 0, 'pay_if_refunded' => 0];

		// values of id_order: 24482
		$this->orders_no_formal_relationship[] = ['subtotal' => 2.59, 'tax' => 0.23, 'tip' => 1.25, 'delivery_fee' => 3, 'service_fee' => 0, 'customer_fee' => 0, 'customer_fee_percent' => 0, 'restaurant_fee_percent' => 0, 'delivery_service_markup' => 20, 'delivery_service_markup_value' => 0.52, 'id_admin' => 205, 'gift_card_total' => 0, 'gift_card_paid_by_crunchbutton' => 0, 'gift_card_paid_by_restaurant' => 0, 'gift_card_paid_by_promotional' => 0, 'gift_card_paid_by_other_restaurant' => 0, 'total_charged' => 7.59, 'promotion_maximum' => 2, 'max_apology_credit' => 0, 'credit' => 1, 'cash' => 0, 'charge_credit_fee' => 1, 'pay_credit_charge' => 1, 'pay_promotion' => 1, 'just_fee_on_subtotal' => 0, 'delivery_service' => 1, 'formal_relationship' => 0, 'paid_with_cb_card' => 0, 'refunded' => 0, 'pay_if_refunded' => 0];
		// values of id_order: 24459
		$this->orders_no_formal_relationship[] = ['subtotal' => 11.46, 'tax' => 1.03, 'tip' => 2.5, 'delivery_fee' => 3, 'service_fee' => 0, 'customer_fee' => 0, 'customer_fee_percent' => 0, 'restaurant_fee_percent' => 0, 'delivery_service_markup' => 20, 'delivery_service_markup_value' => 2.3, 'id_admin' => 72, 'gift_card_total' => 0, 'gift_card_paid_by_crunchbutton' => 0, 'gift_card_paid_by_restaurant' => 0, 'gift_card_paid_by_promotional' => 0, 'gift_card_paid_by_other_restaurant' => 0, 'total_charged' => 20.29, 'promotion_maximum' => 2, 'max_apology_credit' => 0, 'credit' => 1, 'cash' => 0, 'charge_credit_fee' => 1, 'pay_credit_charge' => 1, 'pay_promotion' => 1, 'just_fee_on_subtotal' => 0, 'delivery_service' => 1, 'formal_relationship' => 0, 'paid_with_cb_card' => 0, 'refunded' => 0, 'pay_if_refunded' => 0];

		$this->settlement = new Crunchbutton_Settlement;
	}

	public function testIndividualMathsCashOrderFormalRelationship() {
		$order = $this->orders_formal_relationship[ 2 ];
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

	public function testIndividualMathsCreditOrderFormalRelationship() {
		$order = $this->orders_formal_relationship[ 0 ];
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

	public function testDueToPayFormalRelationship(){
		$pay = $this->settlement->restaurantsProcessOrders( $this->orders_formal_relationship );
		$this->assertEquals( $pay[ 'card_subtotal' ], 41.79 );
		$this->assertEquals( $pay[ 'tax' ], 2.65 );
		$this->assertEquals( $pay[ 'delivery_fee' ], 0 );
		$this->assertEquals( $pay[ 'tip' ], 6.7 );
		$this->assertEquals( $pay[ 'customer_fee' ], 0 );
		$this->assertEquals( $pay[ 'markup' ], 0 );
		$this->assertEquals( $pay[ 'credit_charge' ], -2.68306 );
		$this->assertEquals( $pay[ 'restaurant_fee' ], -5.748 );
		$this->assertEquals( $pay[ 'promo_gift_card' ], 0 );
		$this->assertEquals( $pay[ 'apology_gift_card' ], 0 );
		$this->assertEquals( $pay[ 'order_payment' ], 51.14 );
		$this->assertEquals( $pay[ 'cash_subtotal' ], 9.56 );
		$this->assertEquals( $pay[ 'formal_relationship' ], 1 );
		$this->assertEquals( $pay[ 'total_due' ], 42.70894 );
	}

	public function testIndividualMathsCashOrderNoFormalRelationship() {
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

	public function testIndividualMathsCreditOrderNoFormalRelationship() {
		$order = $this->orders_no_formal_relationship[ 0 ];
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

	public function testDueToPayNoFormalRelationship(){
		$pay = $this->settlement->restaurantsProcessOrders( $this->orders_no_formal_relationship );
		$this->assertEquals( $pay[ 'card_subtotal' ], 14.05 );
		$this->assertEquals( $pay[ 'tax' ], 1.26 );
		$this->assertEquals( $pay[ 'delivery_fee' ], 0 );
		$this->assertEquals( $pay[ 'tip' ], 0 );
		$this->assertEquals( $pay[ 'customer_fee' ], 0 );
		$this->assertEquals( $pay[ 'markup' ], 0 );
		$this->assertEquals( $pay[ 'credit_charge' ], -1.40852 );
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
