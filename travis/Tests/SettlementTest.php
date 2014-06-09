<?php

class SettlementTest extends PHPUnit_Framework_TestCase {

	public function setUp() {
		$this->settlement = new Crunchbutton_Settlement;

		// at first test the calcs using one order
		$this->first_order = Order::o( 22763 );
		$this->first_order_variables = $this->settlement->processOrder( $this->order );
	}

	public function testFirstOrder() {

		// $this->first_order
		// $this->order_variables

		// orderSubtotalPayment

		// $this->order_variables


		$this->assertTrue(true);
	}

	public function tearDown() {
		// neet to implement
	}

}
