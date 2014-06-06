<?php

class SettlementRestaurantTest extends PHPUnit_Framework_TestCase {

	public function setUp() {
		// neet to implement
	}

	// -------------------------------------------------------------------------------

	/***
	 * BEGIN RESTAURANT STUFF
	 */

	// We pay them most of what we collected via credit card, MINUS our fees that they collected by cash.
	public function testRestaurantPaymentAmoutPerOrder(){
		// neet to implement
		// includes gift card logic
		// includes refund logic
		$this->assertTrue(true);
	}

	public function testRestaurantPaymentAmoutPerRestaurant(){
		// neet to implement
		// includes gift card logic
		// includes refund logic
		$this->assertTrue(true);
	}

	// Refunded orders: We do not pay the restaurant for these at all, nor do we charge them if it's a cash order.
	// 		It's like they don't exist; they are not included on the order summary.
	// Note 1: We have the OPTION to pay or charge a restaurant anyway for a refunded order.
	// 		This is currently allowed in autopay. In this case, the order DOES appear on the summary.
	// Note 2: The variable "Pay if refunded?" for credit orders makes it the
	// 		DEFAULT to pay the restaurant anyway for refunded orders.
	public function testRestaurantRefund(){

	}

	// Restaurant Fee is currently charged just on Subtotal + Delivery Fee
	// UNLESS "Fee on Subtotal?" is checked, in which case it's just on the subtotal.
	// We may want the option to charge our fee on the tip as well, though never on the tax
	// 		Tax % is also applied to Subtotal + Delivery fee at the moment.
	// 		For delivery system, Tax % should ONLY be applied to Subtotal.
	public function testRestaurantFee(){
		// neet to implement
		$this->assertTrue(true);
	}

	// Summaries are obviously not sent to places we don't have a formal relationship with
	public function testRestaurantSummary(){
		// neet to implement
		$this->assertTrue(true);
	}

	public function testRestaurantTax(){
		// neet to implement
		$this->assertTrue(true);
	}

	public function testRestaurantPlayableOrders(){
		// neet to implement
		$this->assertTrue(true);
	}

	// Credit Card Charges: We currently charge these to the restaurant by deducting from their payment.
	// 	For non-formal-relationship places, this should just be ignored.
	// 	UNLESS "Charge Credit Fee?" = 0, in which case we just eat this cost ourselves.
	// 	We never "bill" a restaurant at the moment; so if fees owed to us exceed payment owed to the restaurant,
	// 		we just don't send them anything (except a summary)
	public function testRestaurantCreditCardCharges(){
		// neet to implement
		$this->assertTrue(true);
	}


	// Gift cards: There are 4 types of gift card credit, and different things happens depending on which type is applied to the order:
	// Crunchbutton Pays: We pay the restaurant for the full price of the order
	// Restaurant Pays: This gift card credit is subtracted from the money owed to the restaurant,
	// 		and the order does not appear on the order summary, just like a refund.
	// Promotional: The gift card credit is subtracted from the money owed to the restaurant,
	// 		but the order DOES appear on the summary.
	// Paid by Another Restaurant: This is not currently implemented. However, probably an amount
	// 		should be charged to the offending restaurant on THEIR order summary for the mistake.
	public function testRestaurantGiftCards(){
		// neet to implement
		$this->assertTrue(true);
	}

	/*
	 * END RESTAURANT STUFF
	 ***/

	// -------------------------------------------------------------------------------

	/***
	 * BEGIN DELIVERY SERVICE STUFF
	 */

	public function testDeliverySystemFee(){
		// neet to implement
		$this->assertTrue(true);
	}

	/*
	 * END DELIVERY SERVICE STUFF
	 ***/

	// -------------------------------------------------------------------------------

	/***
	 * BEGIN CUSTOMER STUFF
	 */

	// Customer Fee is always just a percent on top of the Subtotal
	public function testCustomerFee(){
		// neet to implement
		$this->assertTrue(true);
	}

	/*
	 * END CUSTOMER STUFF
	 ***/

	// -------------------------------------------------------------------------------

	public function tearDown() {
		// neet to implement
	}

}
