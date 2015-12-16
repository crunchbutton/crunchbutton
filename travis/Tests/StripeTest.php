<?php

class StripeTest extends PHPUnit_Framework_TestCase {
	protected static $restaurant;
	protected static $order;
	protected static $user;

	public static function setUpBeforeClass() {

		$name = get_called_class();

		self::$restaurant = new Restaurant([
			'name' => $name,
			'active' => 1
		]);
		self::$restaurant->save();

		self::$user = new User([
			'name' => $name,
			'active' => 1
		]);
		self::$user->save();
	}

	public static function tearDownAfterClass() {
		if (self::$restaurant)
			self::$restaurant->delete();
		if (self::$user)
			self::$user->delete();
		if (self::$order)
			self::$order->delete();
	}

	public function testChargeNewCard() {
		$name = get_called_class();

		if (!self::$restaurant) {
			$this->markTestSkipped('Restaurant was not created.');
			return;
		}

		$customer = \Stripe\Customer::create([
			'description' => $name,
			'email' => $params['email'],
			'source' => [
				'object' => 'card',
				'number' => '4111111111111111',
				'exp_month' => '12',
				'exp_year' => '2020'
			]
		]);

		self::$user->stripe_id = $customer->id;
		self::$user->save();

		$cards = $customer->sources->all(['object' => 'card'])->data;
		foreach ($cards as $card) {
			break;
		}

		$charge = new Charge_Stripe(['customer_id' => $customer->id, 'card_id' => $card->id]);

		$r = $charge->charge([
			'amount' => '1.25',
			'name' => self::$user->name,
			'address' => '123 UNIT TEST',
			'phone' => '234-567-8901',
			'restaurant' => self::$restaurant
		]);

		if ($r['status']) {
			(new User_Payment_Type([
				'id_user' => self::$user->id_user,
				'stripe_id' => $card['id'],
				'card' => '************1111',
				'card_type' => $card['card_type'],
				'card_exp_year' => $card['year'],
				'card_exp_month' => $card['month'],
				'date' => date('Y-m-d H:i:s'),
				'active' => 1
			]))->save();

			self::$order = new Order([
				'name' => self::$user->name,
				'date' => date('Y-m-d H:i:s'),
				'address' => '123 UNIT TEST',
				'phone' => '234-567-8901',
				'txn' => $r['txn'],
				'id_user' => self::$user->id_user,
				'restaurant' => self::$restaurant->id_restaurant
			]);
			self::$order->save();
		}

		$this->assertTrue($r['status'] ? true : $r['errors'][0]);
	}

	public function testChargeStoredCard() {
		if (! self::$user) {
			$this->markTestSkipped('User was not created.');
			return;
		}

		$paymentType = self::$user->payment_type();

		if (!self::$restaurant) {
			$this->markTestSkipped('Restaurant was not created.');
			return;
		}

		if (!$paymentType) {
			$this->markTestSkipped('User payment type required to refund.');
			return;
		}

		if (!$paymentType->stripe_id) {
			$this->assertTrue('Missing stripe id');
			return;
		}

		$charge = new Charge_Stripe([
			'card_id' => $paymentType->stripe_id,
			'customer_id' => self::$user->stripe_id
		]);

		$r = $charge->charge([
			'amount' => '1.26',
			'name' => self::$user->name,
			'address' => '123 UNIT TEST',
			'phone' => '234-567-8901',
			'restaurant' => self::$restaurant
		]);

		$this->assertTrue($r['status'] ? true : $r['errors'][0]);
	}


	public function testRefund() {
		if (!self::$order) {
			$this->markTestSkipped('No order to refund.');
			return;
		}

		$status = self::$order->refund(1.25);

		$this->assertTrue($status->status);
	}
}
