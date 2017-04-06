<?php

class MailgunTest extends PHPUnit_Framework_TestCase {

	public static function setUpBeforeClass() {
		$name = get_called_class();

		$r = new Restaurant([
			'name' => $name,
			'active' => 1,
			'delivery' => 1,
			'credit' => 1,
			'delivery_fee' => '1.5',
			'confirmation' => 0,
			'community' => 'test',
			'timezone' => 'America/Los_Angeles'
		]);
		$r->save();

		$u = new User([
			'name' => $name,
			'phone' => '$_ENV['DEBUG_PHONE']',
			'address' => '123 main',
			'active' => 1
		]);
		$u->save();

		$d = new Dish([
			'name' => $name,
			'price' => '10',
			'id_restaurant' => $r->id_restaurant,
			'active' => 1
		]);
		$d->save();

		$o = new Order([
			'id_restaurant' => $r->id_restaurant,
			'id_user' => $u->id_user,
			'date' => date('Y-m-d H:i:s'),
			'name' => $u->name,
			'phone' => $u->phone,
//			'env' => 'live',
//			'processor' => 'balanced',
//			'type' => 'web',
			'tax' => '8',
			'tip' => '14.25',
			'final_price_plus_delivery_markup' => '148.71',
			'final_price' => '148.71',
			'price_plus_delivery_markup' => '123',
			'price' => '123'
		]);
		$o->save();

		$od = new Order_Dish([
			'id_order' => $o->id_order,
			'id_dish' => $d->id_dish
		]);
		$od->save();

	}

	public static function tearDownAfterClass() {
		$name = get_called_class();

		Restaurant::q('select * from restaurant where name=?', [$name])->delete();
		User::q('select * from `user` where name=?', [$name])->delete();
		Order_Dish::q('
			select order_dish.* from order_dish
			left join `order` using(id_order)
			where `order`.name=?', [$name])->delete();
		Order::q('select * from `order` where name=?', [$name])->delete();
		Dish::q('select * from dish where name=?', [$name])->delete();
	}

	public function setUp() {
		$name = get_called_class();
		$this->order = Order::q('select * from `order` where name=? limit 1', [$name])->get(0);
	}

	public function testMail() {
		if (!$this->order->id_order) {
			return $this->assertTrue('No id_order');
		}

		$mail = new Email_Order([
			'order' => $this->order,
			'email' => '_EMAIL_'
		]);
		$res = $mail->send();

		$this->assertTrue($res === true ? true : false);
	}
}