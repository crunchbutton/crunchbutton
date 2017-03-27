<?php

class NotificationTest extends PHPUnit_Framework_TestCase {

	public static function setUpBeforeClass() {
		$name = get_called_class();

		$r = new Restaurant([
			'name' => $name,
			'active' => 1
		]);
		$r->save();

		$a = new Admin([
			'name' => $name,
			'login' => null,
			'active' => 1
		]);
		$a->save();

		$u = new User([
			'name' => $name,
			'phone' => '_PHONE_',
			'address' => '123 main',
			'active' => 1
		]);
		$u->save();

		$o = new Order([
			'name' => $name,
			'address' => $u->address,
			'phone' => $u->phone,
			'price' => '10',
			'price_plus_delivery_markup' => '10',
			'final_price' => '12.8',
			'final_price_plus_delivery_markup' => '12.8',
			'pay_type' => 'cash',
			'delivery_type' => 'delivery',
			'id_user' => $u->id_user,
			'date' => date('Y-m-d H:i:s'),
			'id_community' => '',
			'id_restaurant' => $r->id_restaurant,
			'active' => 1
		]);
		$o->save();

	}

	public static function tearDownAfterClass() {
		$name = get_called_class();

		Restaurant::q('select * from restaurant where name=?', [$name])->delete();
		User::q('select * from `user` where name=?', [$name])->delete();
		Order::q('select * from `order` where name=?', [$name])->delete();
		Admin::q('select * from admin where name=?', [$name])->delete();
	}

	public function setUp() {
		$name = get_called_class();

		$this->restaurant = Restaurant::q('select * from restaurant where name=? order by id_restaurant desc limit 1', [$name])->get(0);
		$this->driver = Admin::q('select * from admin where name=? order by id_admin desc limit 1', [$name])->get(0);
		$this->user = User::q('select * from `user` where name=? order by id_user desc limit 1', [$name])->get(0);
		$this->order = Order::q('select * from `order` where name=? order by id_order desc limit 1', [$name])->get(0);
	}

	/*
	// this doesnt even test anything
	public function testCustomerReceipt() {
		$this->order->receipt();
	}
	*/

	public function testNotifyDriverSms() {

		$n = (new Crunchbutton_Admin_Notification([
			'id_admin' => $this->driver->id_admin,
			'type' =>  Crunchbutton_Admin_Notification::TYPE_SMS,
			'value' => '4155490115',
			'active' => 1
		]))->save();

		$ress = $n->send($this->order);

		$status = true;
		foreach ($ress as $res) {
			if (!$res->sid) {
				$status = false;
				break;
			}
		}

		$this->assertTrue($status);
	}
/*
	public function testNotifyDriverPushIos() {

		$n = (new Crunchbutton_Admin_Notification([
			'id_admin' => $this->driver->id_admin,
			'type' => 'push-ios',
			'value' => 'bda4c763f2e2f2ec8b123a960fd2e9ecba591cf4a310253708156eed658a4bb2',
			'active' => 1
		]))->save();

		$status = $n->sendPushIos($this->order);
		if (!$status) {
			var_dump($status);
		}

		$this->assertTrue($status);
	}
*/



}
