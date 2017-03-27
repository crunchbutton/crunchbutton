<?php

class OrderLogisticsTest extends PHPUnit_Framework_TestCase {

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
			'timezone' => 'America/Los_Angeles',
			'open_for_business' => true,
			'delivery_service' => true
		]);
		$r->save();

		$c = new Community([
			'name' => $name,
			'active' => 1,
			'timezone' => 'America/Los_Angeles',
			'driver-group' => 'drivers-testlogistics',
			'range' => 2,
			'delivery_logistics' => true
		]);
		$c->save();

		$rc = new Restaurant_Community([
			'id_restaurant' => $r->id_restaurant,
			'id_community' => $c->id_community
		]);
		$rc->save();

		$cs = new Community_Shift([
			'id_community' => $c->id_community,
			'date_start' => date('Y-m-d H:i:s'),
			'date_end' => date('Y-m-d 24:i:s'),
			'active' => 1
		]);
		$cs->save();

		$h = new Hour([
			'id_restaurant' => $r->id_restaurant,
			'day' => strtolower(date('D')),
			'time_open' => '0:01',
			'time_close' => '23:59',
		]);
		$h->save();

		$a1 = new Admin([
			'name' => $name.' - ONE',
			'login' => null,
			'active' => 1
		]);
		$a1->save();

		$an1 = new Admin_Notification([
			'id_admin' => $a1->id_admin,
			'type' => 'sms',
			'value' => '4155490115',
			'active' => true
		]);
		$an1->save();

		$a2 = new Admin([
			'name' => $name.' - TWO',
			'login' => null,
			'active' => 1
		]);
		$a2->save();

		$an2 = new Admin_Notification([
			'id_admin' => $a2->id_admin,
			'type' => 'sms',
			'value' => '4155490115',
			'active' => true
		]);
		$an2->save();


		$asa1 = new Admin_Shift_Assign([
			'id_community_shift' => $cs->id_community_shift,
			'id_admin' => $a1->id_admin
			'date' => date('Y-m-d H:i:s'),
			'warned' => 0
		]);
		$asa1->save();


		$asa2 = new Admin_Shift_Assign([
			'id_community_shift' => $cs->id_community_shift,
			'id_admin' => $a2->id_admin
			'date' => date('Y-m-d H:i:s'),
			'warned' => 0
		]);
		$asa2->save();

		$u = new User([
			'name' => $name,
			'phone' => '_PHONE_',
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

	}

	public static function tearDownAfterClass() {
		$name = get_called_class();

		Restaurant::q('select * from restaurant where name=?', [$name])->delete();
		User::q('select * from `user` where name=?', [$name])->delete();
		Order::q('select * from `order` where name=?', [$name])->delete();
		Admin::q('select * from admin where name=?', [$name. ' ONE'])->delete();
		Admin::q('select * from admin where name=?', [$name. ' ONE'])->delete();
		Dish::q('select * from dish where name=?', [$name])->delete();
	}

	public function setUp() {
		$name = get_called_class();

		$this->restaurant = Restaurant::q('select * from restaurant where name=? order by id_restaurant desc limit 1', [$name])->get(0);
		$this->driver1 = Admin::q('select * from admin where name=? order by id_admin desc limit 1', [$name. ' ONE'])->get(0);
		$this->driver2 = Admin::q('select * from admin where name=? order by id_admin desc limit 1', [$name. ' ONE'])->get(0);
		$this->user = User::q('select * from `user` where name=? order by id_user desc limit 1', [$name])->get(0);
		$this->dish = Dish::q('select * from `dish` where name=? order by id_dish desc limit 1', [$name])->get(0);
	}

	public function testOrderLogistics() {

		$_POST = [
			'address' => $this->user->address,
			'phone' => $this->user->phone,
			'name' => $this->user->name,
			'cart' => [['id' => $this->dish->id_dish]],
			'pay_type' => 'cash',
			'delivery_type' => 'delivery',
			'restaurant' => $this->restaurant->id_restaurant,
			'make_default' => true,
			'notes' => 'TEST',
			'lat' => '33.175101',
			'lon' => '-96.677810',
			'local_gid' => 'RAND'
		];

		$order = new Order;
		$charge = $order->process($_POST);

		// charge was good
		$this->assertTrue($charge === true);

		// synchronasly run everything in que
		//Crunchbutton_Queue::end();

		// check that both admins were notified
		$a1 = Crunchbutton_Queue::q('select * from queue where id_order=? and id_admin=?', [$order->id_order, $this->driver1->id_admin])->get(0);
		$a2 = Crunchbutton_Queue::q('select * from queue where id_order=? and id_admin=?', [$order->id_order, $this->driver1->id_admin])->get(0);

		$this->assertTrue($a1->id_queue ? true : false);
		$this->assertTrue($a2->id_queue ? true : false);

		$this->assertTrue(false);

	}
}