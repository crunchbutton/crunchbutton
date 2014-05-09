<?php

class BalancedTest extends PHPUnit_Framework_TestCase {

	public static function setUpBeforeClass() {
		(new Restaurant([
			'name' => 'UNIT TEST RESTAURANT',
			'active' => 1
		]))->save();
	
		(new User([
			'name' => 'UNIT TEST',
			'active' => 1
		]))->save();
	}

	public static function tearDownAfterClass() {
		//Restaurant::q('select * from restaurant where name="UNIT TEST RESTAURANT"')->delete();
		//User::q('select * from user where name="UNIT TEST"')->delete();
	}
	
	public function setUp() {
		$this->restaurant = Restaurant::q('select * from restaurant where name="UNIT TEST RESTAURANT" limit 1')->get(0);
		$this->user = User::q('select * from restaurant where name="UNIT TEST" limit 1')->get(0);
	}

	public function testChargeNewCard() {

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
			'uri' => $card->href,
			'card_type' => $card->brand,
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
		
		if ($r['status']) {
			$this->paymentType = (new User_Payment_Type([
				'id_user' => $this->user->id_user,
				'balanced_id' => $card->id,
				'card' => '************1111',
				'card_type' => $card->brand,
				'card_exp_year' => $card->expiration_month,
				'card_exp_month' => $card->expiration_year,
				'date' => date('Y-m-d H:i:s')
			]))->save();
		}
		
		$this->assertTrue($r['status'] ? true : $r['errors'][0]);
	}

	public function testChargeStoredCard() {
		$this->paymentType = $this->user->payment_type();
		
		if (!$this->paymentType) {
			$this->assertTrue('testChargeNewCard is required and has failed.');
			return;
		}

		$charge = new Charge_Balanced([
			'customer_id' => $this->user->balanced_id,
			'card_id' => $this->paymentType->balanced_id
		]);
		
		$r = $charge->charge([
			'amount' => '1.25',
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

}
