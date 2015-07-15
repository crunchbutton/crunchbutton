<?php

class DriverAcceptTest extends PHPUnit_Framework_TestCase {

	public static function setUpBeforeClass() {
		$name = get_called_class();

		$r = new Restaurant([
			'name' => $name,
			'active' => true,
			'delivery' => true,
			'credit' => true,
			'delivery_fee' => '1.5',
			'confirmation' => false,
			'community' => 'test',
			'timezone' => 'America/Los_Angeles',
			'open_for_business' => true
		]);
		$r->save();

		$h = new Hour([
			'id_restaurant' => $r->id_restaurant,
			'day' => strtolower(date('D')),
			'time_open' => '0:01',
			'time_close' => '23:59',
		]);
		$h->save();

		$a = new Admin([
			'name' => $name,
			'login' => null,
			'active' => true
		]);
		$a->save();

		$u = new User([
			'name' => $name,
			'phone' => '_PHONE_',
			'address' => '123 main',
			'active' => true
		]);
		$u->save();

		$d = new Dish([
			'name' => $name,
			'price' => '10',
			'id_restaurant' => $r->id_restaurant,
			'active' => true
		]);
		$d->save();

		$_POST = [
			'address' => $u->address,
			'phone' => $u->phone,
			'name' => $u->name,
			'cart' => [['id' => $d->id_dish]],
			'pay_type' => 'cash',
			'delivery_type' => 'delivery',
			'restaurant' => $r->id_restaurant,
			'make_default' => true,
			'notes' => 'TEST',
			'lat' => '33.175101',
			'lon' => '-96.677810',
			'local_gid' => 'RAND',
			'processor' => Crunchbutton_User_Payment_Type::processor()
		];

		$order = new Order;
		$charge = $order->process($_POST);
		if (!$charge) {
			print_r($charge);
		}
	}

	public static function tearDownAfterClass() {
		$name = get_called_class();

		Restaurant::q('select * from restaurant where name=?', [$name])->delete();
		User::q('select * from `user` where name=?', [$name])->delete();
		Order::q('select * from `order` where name=?', [$name])->delete();
		Admin::q('select * from admin where name=?', [$name])->delete();
		Dish::q('select * from dish where name=?', [$name])->delete();
	}

	public function setUp() {
		$name = get_called_class();

		$this->restaurant = Restaurant::q('select * from restaurant where name=? order by id_restaurant desc limit 1', [$name])->get(0);
		$this->driver = Admin::q('select * from admin where name=? order by id_admin desc limit 1', [$name])->get(0);
		$this->user = User::q('select * from `user` where name=? order by id_user desc limit 1', [$name])->get(0);
		$this->dish = Dish::q('select * from `dish` where name=? order by id_dish desc limit 1', [$name])->get(0);
		$this->order = Order::q('select * from `order` where name=? order by id_order desc limit 1', [$name])->get(0);
	}

	public function testDriverAccept() {
		if (!$this->order) {
			$this->assertTrue('Could not find order');
			return;
		}
		$status = $this->order->setStatus(Crunchbutton_Order_Action::DELIVERY_ACCEPTED, true, $this->driver);
		$this->assertTrue($status === true);
	}
}