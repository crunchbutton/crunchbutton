<?php

class BalancedTest extends PHPUnit_Framework_TestCase {

	public static function setUpBeforeClass() {
		echo "\n\n";
		echo 'Balanced env: '.c::env()."\n";
		echo 'Balanced getenv: '.c::getEnv()."\n";
		echo 'Balanced config: '.c::config()->balanced->{c::getEnv()}->secret."\n\n\n";

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
		$this->restaurant = Restaurant::q('select * from restaurant where name="UNIT TEST RESTAURANT" order by id_restaurant desc limit 1')->get(0);
		$this->user = User::q('select * from `user` where name="UNIT TEST" order by id_user desc limit 1')->get(0);
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
			(new User_Payment_Type([
				'id_user' => $this->user->id_user,
				'balanced_id' => $card['id'],
				'card' => '************1111',
				'card_type' => $card['card_type'],
				'card_exp_year' => $card['year'],
				'card_exp_month' => $card['month'],
				'date' => date('Y-m-d H:i:s'),
				'active' => 1
			]))->save();
			
			(new Order([
				'name' => $this->user->name,
				'address' => '123 UNIT TEST',
				'phone' => '234-567-8901',
				'txn' => $r['txn'],
				'id_user' => $this->user->id_user,
				'restaurant' => $this->restaurant->id_restaurant
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
		$order = Order::q('select * from `order` where id_user="'.$this->user->id_user.'" order by date desc limit 1')->get(0);

		if (!$order->id_order) {
			$this->assertTrue('testChargeNewCard is required and has failed most likly.');
			return;
		}
		
		$res = $order->refund();
		$this->assertTrue($res->status);
	}
	
	
	public function testCreateMerchant() {
		$merchant = c::balanced()->createMerchant(
			'restaurant-'.$this->id_restaurant.'@_DOMAIN_',
			$p,
			null,
			null,
			$this->name
		);
		$this->assertTrue($merchant->id ? true : false);
	}
	
	public function testCreatebankAccount() {
		$bank = c::balanced()->createBankAccount('UNIT TEST RESTAURANT', '9900000002', '021000021', 'checking');
		$this->assertTrue($bank->id ? true : false);
	}
	
	public function testCredit() {
		$account = c::balanced()->createBankAccount('UNIT TEST RESTAURANT', '9900000002', '021000021', 'checking');
		$res = $account->credits->create([
			'amount' => 5555
		]);
		$this->assertTrue($res->id ? true : false);
	}


}
