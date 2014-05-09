<?php

class BalancedTest extends PHPUnit_Framework_TestCase {

	public function setUp() {
		$this->restaurant = (new Restaurant([
			'name' => 'UNIT TEST RESTAURANT',
			'active' => 1
		]))->save();
	
		$this->user = (new User([
			'name' => 'UNIT TEST',
			'active' => 1
		]))->save();
	}

	public function tearDown() {
		Restaurant::q('select * from restaurant where name="UNIT TEST RESTAURANT"')->delete();
		User::q('select * from user where name="UNIT TEST"')->delete();
	}

	public function testCharge() {

		$charge = new Charge_Balanced;

		$card = c::balanced()->createCard(
			null, null, null, null, null,
			'4111111111111111',
			'123',
			'12',
			'2020'
		);

		$card = [
			'id' => $card->id,
			'lastfour' => '1111',
			'uri' => $card->uri,
			'card_type' => $card->card_type,
			'month' => $card->expiration_month,
			'year' => $card->expiration_year
		];

		$r = $charge->charge([
			'amount' => '1.25',
			'card' => $card,
			'name' => $this->user->name,
			'address' => '123 UNIT TEST',
			'phone' => '234-567-8901',
			'user' => $this->user,
			'restaurant' => $this->restaurant
		]);

		$this->assertTrue($r['status'] ? true : $r['errors'][0]);
	}
	
	public function testRefund() {
		
	}
	
	public function testDeposit() {
		
	}
	
	public function testChargeNewCard() {

	}
	
	public function testChargeOldCard() {
		$paymentType = $this->user->payment_type();
		$charge = new Charge_Balanced([
			'customer_id' => $this->user->balanced_id,
			'card_id' => $paymentType->balanced_id
		]);
		
		$r = $charge->charge([
			'amount' => '1.25',
			'card' => $card,
			'name' => $this->user->name,
			'address' => '123 UNIT TEST',
			'phone' => '234-567-8901',
			'user' => $this->user,
			'restaurant' => $this->restaurant
		]);

		$this->assertTrue($r['status'] ? true : $r['errors'][0]);
	}
}
