<?php

class OrderTest extends PHPUnit_Framework_TestCase {

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

		$d1 = new Dish([
			'name' => $name . '1',
			'price' => '10',
			'id_restaurant' => $r->id_restaurant,
			'active' => 1
		]);
		$d1->save();

		$d2 = new Dish([
			'name' => $name . '2',
			'price' => '20',
			'id_restaurant' => $r->id_restaurant,
			'active' => 1
		]);
		$d2->save();

		$d3 = new Dish([
			'name' => $name . '3',
			'price' => '30',
			'id_restaurant' => $r->id_restaurant,
			'active' => 1
		]);
		$d3->save();

		$d4 = new Dish([
			'name' => $name . '4',
			'price' => '10',
			'id_restaurant' => $r->id_restaurant,
			'active' => 1
		]);
		$d4->save();

	}

	public static function tearDownAfterClass() {
		$name = get_called_class();

		Restaurant::q('select * from restaurant where name=?', [$name])->delete();
		User::q('select * from `user` where name=?', [$name])->delete();
		$order = Order::q('select * from `order` where name=?', [$name])->get( 0 );
		$id_order = $order->id_order;
		Admin::q('select * from admin where name=?', [$name])->delete();
		Dish::q('select * from dish where name=?', [$name.'1'])->delete();
		Dish::q('select * from dish where name=?', [$name.'2'])->delete();
		Dish::q('select * from dish where name=?', [$name.'3'])->delete();
		Dish::q('select * from dish where name=?', [$name.'4'])->delete();
		Order_Dish::q('select * from order_dish where id_order=?', [$id_order])->delete();
		Order::q('select * from `order` where id_order=?', [$id_order])->delete();
	}

	public function setUp() {
		$name = get_called_class();
		$this->restaurant = Restaurant::q('select * from restaurant where name=? order by id_restaurant desc limit 1', [$name])->get(0);
		$this->driver = Admin::q('select * from admin where name=? order by id_admin desc limit 1', [$name])->get(0);
		$this->user = User::q('select * from `user` where name=? order by id_user desc limit 1', [$name])->get(0);
		$this->dish1 = Dish::q('select * from `dish` where name=? order by id_dish desc limit 1', [$name.'1'])->get(0);
		$this->dish2 = Dish::q('select * from `dish` where name=? order by id_dish desc limit 1', [$name.'2'])->get(0);
		$this->dish3 = Dish::q('select * from `dish` where name=? order by id_dish desc limit 1', [$name.'3'])->get(0);
		$this->dish4 = Dish::q('select * from `dish` where name=? order by id_dish desc limit 1', [$name.'4'])->get(0);
	}

	public function testOrder() {

		$_POST = [
			'address' => $this->user->address,
			'phone' => $this->user->phone,
			'name' => $this->user->name,
			'cart' => [	[ 'id' => $this->id_dish1->id_dish ],
						[ 'id' => $this->id_dish2->id_dish ],
						[ 'id' => $this->id_dish3->id_dish ],
						[ 'id' => $this->id_dish4->id_dish ] ],
			'pay_type' => 'cash',
			'delivery_type' => 'delivery',
			'restaurant' => $this->restaurant->id_restaurant,
			'make_default' => true,
			'notes' => 'TEST',
			'lat' => '33.175101',
			'lon' => '-96.677810',
			'local_gid' => 'RAND',
			'processor' => Crunchbutton_User_Payment_Type::processor()
		];

		$order = new Order;
		$charge = $order->process($_POST);

		$this->id_order = $order->id_order;
		echo '<pre>';var_dump( $_POST, $charge );
		$this->assertTrue($charge === true);

		return $order;
	}

    /**
     * @depends testOrder
     */
	public function testTotalDishes( $order ){
		$total = 0;
		foreach( $order->dishes() as $dish ){
			$total++;
		}
		$this->assertEquals( $total, 4 );
		return $order;
	}

    /**
     * @depends testOrder
     */
	public function testDishes( $order ){
		$dishes = [];
		foreach( $order->dishes() as $dish ){
			$dishes[] = $dish->id_dish;
		}
		$this->assertTrue(in_array( $this->id_dish1->id_dish, $dishes));
		$this->assertTrue(in_array( $this->id_dish2->id_dish, $dishes));
		$this->assertTrue(in_array( $this->id_dish3->id_dish, $dishes));
		$this->assertTrue(in_array( $this->id_dish4->id_dish, $dishes));
	}

}