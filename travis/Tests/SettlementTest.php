<?php

class SettlementTest extends PHPUnit_Framework_TestCase {

	// because settlement filters stuff with 'test' on its name
	const NAME = 'Settlement Travis';

	public static function setUpBeforeClass() {

		$name = self::NAME;

		// restaurant stuff
		$r1 = new Restaurant([ 'name' => $name . ' FORMAL', 'formal_relationship' => false, 'active' => true, 'delivery' => true, 'credit' => 1, 'delivery_fee' => '1.5', 'confirmation' => 0, 'community' => 'test', 'timezone' => 'America/Los_Angeles', 'open_for_business' => true, 'delivery_service' => true ]);
		$r1->save();

		$r2 = new Restaurant([ 'name' => $name . ' INFORMAL', 'formal_relationship' => true, 'active' => true, 'delivery' => true, 'credit' => 1, 'delivery_fee' => '1.5', 'confirmation' => 0, 'community' => 'test', 'timezone' => 'America/Los_Angeles', 'open_for_business' => true, 'delivery_service' => true ]);
		$r2->save();

		$h1 = new Hour([ 'id_restaurant' => $r1->id_restaurant, 'day' => strtolower(date('D')), 'time_open' => '0:01', 'time_close' => '23:59' ]);
		$h1->save();

		$h2 = new Hour([ 'id_restaurant' => $r2->id_restaurant, 'day' => strtolower(date('D')), 'time_open' => '0:01', 'time_close' => '23:59' ]);
		$h2->save();

		$d1 = new Dish([ 'name' => $name, 'price' => '10', 'id_restaurant' => $r1->id_restaurant, 'active' => 1 ]);
		$d1->save();

		$d2 = new Dish([ 'name' => $name, 'price' => '10', 'id_restaurant' => $r2->id_restaurant, 'active' => 1 ]);
		$d2->save();

		// user
		$u = new User([ 'name' => $name, 'phone' => '_PHONE_', 'address' => '123 main', 'active' => 1 ]);
		$u->save();

		// orders driver hourly
		$d1_o1 = new Order;
		$charge = $d1_o1->process( [ 'address' => $u->address, 'phone' => $u->phone, 'name' => $u->name, 'cart' => [['id' => $d1->id_dish]], 'pay_type' => 'cash', 'delivery_type' => 'delivery', 'restaurant' => $r1->id_restaurant, 'make_default' => true, 'notes' => 'TEST', 'lat' => '33.175101', 'lon' => '-96.677810', 'local_gid' => 'RAND', 'processor' => Crunchbutton_User_Payment_Type::processor() ] );

		$d1_o2 = new Order;
		$charge = $d1_o2->process( [ 'address' => $u->address, 'phone' => $u->phone, 'name' => $u->name, 'cart' => [['id' => $d1->id_dish]], 'pay_type' => 'cash', 'tip' => 20, 'delivery_type' => 'delivery', 'restaurant' => $r1->id_restaurant, 'make_default' => true, 'notes' => 'TEST', 'lat' => '33.175101', 'lon' => '-96.677810', 'local_gid' => 'RAND', 'processor' => Crunchbutton_User_Payment_Type::processor() ] );
		$d1_o2->pay_type = 'card'; // fake card just to make calcs
		$d1_o2->save();

		$d1_o3 = new Order;
		$charge = $d1_o3->process( [ 'address' => $u->address, 'phone' => $u->phone, 'name' => $u->name, 'cart' => [['id' => $d2->id_dish]], 'pay_type' => 'cash', 'delivery_type' => 'delivery', 'restaurant' => $r2->id_restaurant, 'make_default' => true, 'notes' => 'TEST', 'lat' => '33.175101', 'lon' => '-96.677810', 'local_gid' => 'RAND', 'processor' => Crunchbutton_User_Payment_Type::processor() ] );

		$d1_o4 = new Order;
		$charge = $d1_o4->process( [ 'address' => $u->address, 'phone' => $u->phone, 'name' => $u->name, 'cart' => [['id' => $d2->id_dish]], 'pay_type' => 'cash', 'tip' => 20, 'delivery_type' => 'delivery', 'restaurant' => $r2->id_restaurant, 'make_default' => true, 'notes' => 'TEST', 'lat' => '33.175101', 'lon' => '-96.677810', 'local_gid' => 'RAND', 'processor' => Crunchbutton_User_Payment_Type::processor() ] );
		$d1_o4->pay_type = 'card'; // fake card just to make calcs
		$d1_o4->save();

		// orders driver hourly
		$d2_o1 = new Order;
		$charge = $d2_o1->process( [ 'address' => $u->address, 'phone' => $u->phone, 'name' => $u->name, 'cart' => [['id' => $d2->id_dish]], 'pay_type' => 'cash', 'delivery_type' => 'delivery', 'restaurant' => $r1->id_restaurant, 'make_default' => true, 'notes' => 'TEST', 'lat' => '33.175101', 'lon' => '-96.677810', 'local_gid' => 'RAND', 'processor' => Crunchbutton_User_Payment_Type::processor() ] );

		$d2_o2 = new Order;
		$charge = $d2_o2->process( [ 'address' => $u->address, 'phone' => $u->phone, 'name' => $u->name, 'cart' => [['id' => $d2->id_dish]], 'pay_type' => 'cash', 'tip' => 20, 'delivery_type' => 'delivery', 'restaurant' => $r1->id_restaurant, 'make_default' => true, 'notes' => 'TEST', 'lat' => '33.175101', 'lon' => '-96.677810', 'local_gid' => 'RAND', 'processor' => Crunchbutton_User_Payment_Type::processor() ] );
		$d2_o2->pay_type = 'card'; // fake card just to make calcs
		$d2_o2->save();

		$d2_o3 = new Order;
		$charge = $d2_o3->process( [ 'address' => $u->address, 'phone' => $u->phone, 'name' => $u->name, 'cart' => [['id' => $d2->id_dish]], 'pay_type' => 'cash', 'delivery_type' => 'delivery', 'restaurant' => $r2->id_restaurant, 'make_default' => true, 'notes' => 'TEST', 'lat' => '33.175101', 'lon' => '-96.677810', 'local_gid' => 'RAND', 'processor' => Crunchbutton_User_Payment_Type::processor() ] );

		$d2_o4 = new Order;
		$charge = $d2_o4->process( [ 'address' => $u->address, 'phone' => $u->phone, 'name' => $u->name, 'cart' => [['id' => $d2->id_dish]], 'pay_type' => 'cash', 'tip' => 20, 'delivery_type' => 'delivery', 'restaurant' => $r2->id_restaurant, 'make_default' => true, 'notes' => 'TEST', 'lat' => '33.175101', 'lon' => '-96.677810', 'local_gid' => 'RAND', 'processor' => Crunchbutton_User_Payment_Type::processor() ] );
		$d2_o4->pay_type = 'card'; // fake card just to make calcs
		$d2_o4->save();

		// driver hourly
		$d1 = new Admin( [ 'name' => $name . ' HOUR', 'login' => null, 'active' => 1 ] );
		$d1->save();
		$pt1 = $d1->payment_type();
		$pt1->payment_type = Crunchbutton_Admin_Payment_Type::PAYMENT_TYPE_HOURS;
		$pt1->hour_rate = 10;
		$pt1->using_pex = 1;
		$pt1->save();

		// driver commissioned
		$d2 = new Admin( [ 'name' => $name . ' ORDER', 'login' => null, 'active' => 1 ] );
		$d2->save();
		$pt2 = $d2->payment_type();
		$pt2->payment_type = Crunchbutton_Admin_Payment_Type::PAYMENT_TYPE_ORDERS;
		$pt2->using_pex = 1;
		$pt2->save();

		// community
		$c = new Community([ 'name' => $name, 'active' => 1, 'timezone' => 'America/Los_Angeles', 'driver-group' => 'drivers-test-settlement', 'range' => 2, 'private' => 1, 'delivery_logistics' => true ]);
		$c->save();

		// shifts
		$now = new DateTime('now', new DateTimeZone(c::config()->timezone));
		$now->modify('- 1 day');
		$shift_start = $now->format( 'Y-m-d H:i:s' );
		$now->modify('+ 2 hours');
		$shift_end = $now->format( 'Y-m-d H:i:s' );

		$cs1 = new Community_Shift([ 'id_community' => $c->id_community, 'date_start' => $shift_start, 'date_end' => $shift_end, 'active' => 1 ]);
		$cs1->save();

		$now->modify('- 1 day');
		$shift_start = $now->format( 'Y-m-d H:i:s' );
		$now->modify('+ 2 hours');
		$shift_end = $now->format( 'Y-m-d H:i:s' );
		$cs2 = new Community_Shift([ 'id_community' => $c->id_community, 'date_start' => $shift_start, 'date_end' => $shift_end, 'active' => 1 ]);
		$cs2->save();

		// assign drivers
		$d1_asa1 = new Admin_Shift_Assign([ 'id_community_shift' => $cs1->id_community_shift, 'id_admin' => $d1->id_admin, 'date' => date('Y-m-d H:i:s'), 'warned' => 1 ]);
		$d1_asa1->save();

		$d1_asa2 = new Admin_Shift_Assign([ 'id_community_shift' => $cs2->id_community_shift, 'id_admin' => $d1->id_admin, 'date' => date('Y-m-d H:i:s'), 'warned' => 1 ]);
		$d1_asa2->save();

		$d2_asa1 = new Admin_Shift_Assign([ 'id_community_shift' => $cs1->id_community_shift, 'id_admin' => $d2->id_admin, 'date' => date('Y-m-d H:i:s'), 'warned' => 1 ]);
		$d2_asa1->save();

		$d2_asa2 = new Admin_Shift_Assign([ 'id_community_shift' => $cs2->id_community_shift, 'id_admin' => $d2->id_admin, 'date' => date('Y-m-d H:i:s'), 'warned' => 1 ]);
		$d2_asa2->save();

		// hourly driver accepts orders
		// $d1_o1->setStatus( Crunchbutton_Order_Action::DELIVERY_ACCEPTED, false, $d1);
		// $d1_o1->setStatus( Crunchbutton_Order_Action::DELIVERY_PICKEDUP, false, $d1);
		$d1_o1->setStatus( Crunchbutton_Order_Action::DELIVERY_DELIVERED, false, $d1);

		// $d1_o2->setStatus( Crunchbutton_Order_Action::DELIVERY_ACCEPTED, false, $d1);
		// $d1_o2->setStatus( Crunchbutton_Order_Action::DELIVERY_PICKEDUP, false, $d1);
		$d1_o2->setStatus( Crunchbutton_Order_Action::DELIVERY_DELIVERED, false, $d1);

		// $d1_o3->setStatus( Crunchbutton_Order_Action::DELIVERY_ACCEPTED, false, $d1);
		// $d1_o3->setStatus( Crunchbutton_Order_Action::DELIVERY_PICKEDUP, false, $d1);
		$d1_o3->setStatus( Crunchbutton_Order_Action::DELIVERY_DELIVERED, false, $d1);

		// $d1_o4->setStatus( Crunchbutton_Order_Action::DELIVERY_ACCEPTED, false, $d1);
		// $d1_o4->setStatus( Crunchbutton_Order_Action::DELIVERY_PICKEDUP, false, $d1);
		$d1_o4->setStatus( Crunchbutton_Order_Action::DELIVERY_DELIVERED, false, $d1);

		// commissioned driver accepts orders
		// $d2_o1->setStatus( Crunchbutton_Order_Action::DELIVERY_ACCEPTED, false, $d2);
		// $d2_o1->setStatus( Crunchbutton_Order_Action::DELIVERY_PICKEDUP, false, $d2);
		$d2_o1->setStatus( Crunchbutton_Order_Action::DELIVERY_DELIVERED, false, $d2);

		// $d2_o2->setStatus( Crunchbutton_Order_Action::DELIVERY_ACCEPTED, false, $d2);
		// $d2_o2->setStatus( Crunchbutton_Order_Action::DELIVERY_PICKEDUP, false, $d2);
		$d2_o2->setStatus( Crunchbutton_Order_Action::DELIVERY_DELIVERED, false, $d2);

		// $d2_o3->setStatus( Crunchbutton_Order_Action::DELIVERY_ACCEPTED, false, $d2);
		// $d2_o3->setStatus( Crunchbutton_Order_Action::DELIVERY_PICKEDUP, false, $d2);
		$d2_o3->setStatus( Crunchbutton_Order_Action::DELIVERY_DELIVERED, false, $d2);

		// $d2_o4->setStatus( Crunchbutton_Order_Action::DELIVERY_ACCEPTED, false, $d2);
		// $d2_o4->setStatus( Crunchbutton_Order_Action::DELIVERY_PICKEDUP, false, $d2);
		$d2_o4->setStatus( Crunchbutton_Order_Action::DELIVERY_DELIVERED, false, $d2);

	}

	public function setUp() {

		$now = new DateTime('now', new DateTimeZone(c::config()->timezone));
		$this->end_date = $now->format( 'Y-m-d' );

		$now->modify('- 2 day');
		$this->start_date = $now->format( 'Y-m-d' );

		$name = self::NAME;
		$this->driver_hourly = Admin::q('select * from admin where name=? order by id_admin desc limit 1', [$name.' HOUR'])->get(0);
		$this->driver_commissioned = Admin::q('select * from admin where name=? order by id_admin desc limit 1', [$name.' ORDER'])->get(0);
		$this->community = Community::q('select * from community where name=? order by id_community desc limit 1', [$name])->get(0);
		$this->order = Order::q('select * from `order` where name=? order by id_order desc limit 1', [$name])->get(0);

		$settlement = new Settlement( [ 'start' => $this->start_date, 'end' => $this->end_date ] );
		$orders = $settlement->startDriver();

		// gets the hourly driver info
		$this->hourly_payment_info = null;
		foreach ( $orders as $key => $val ) {
			if( $val[ 'id_admin' ] == $this->driver_hourly->id_admin ){
				$this->hourly_payment_info = $val;
			}
		}

		// gets the commissioned driver info
		$this->commissioned_payment_info = null;
		foreach ( $orders as $key => $val ) {
			if( $val[ 'id_admin' ] == $this->driver_commissioned->id_admin ){
				$this->commissioned_payment_info = $val;
			}
		}
	}

	public function testDriverPaidByOrder(){
		$this->assertNotNull( $this->commissioned_payment_info );
		$this->assertEquals( count( $this->commissioned_payment_info[ 'orders' ] ), 4 );
		$this->assertEquals( $this->commissioned_payment_info[ 'salary_type' ], 'orders' );
		$this->assertEquals( $this->commissioned_payment_info[ 'tip' ], 4 );
		$this->assertEquals( $this->commissioned_payment_info[ 'delivery_fee' ], 6 );
		$this->assertEquals( $this->commissioned_payment_info[ 'total_spent' ], 20 );
		$this->assertEquals( $this->commissioned_payment_info[ 'delivery_fee_collected' ], -3 );
		$this->assertEquals( $this->commissioned_payment_info[ 'standard_reimburse' ], 10 );
		$this->assertEquals( $this->commissioned_payment_info[ 'total_payment_per_order' ], 7 );
		$this->assertEquals( $this->commissioned_payment_info[ 'total_payment' ], 7 );
	}

	public function testDriverPaidByHour(){

		$this->assertNotNull( $this->hourly_payment_info );
		$this->assertEquals( count( $this->hourly_payment_info[ 'orders' ] ), 4 );
		$this->assertEquals( count( $this->hourly_payment_info[ 'shifts' ][ 'worked' ] ) , 2 );
		$this->assertEquals( $this->hourly_payment_info[ 'salary_type' ], 'hours' );
		$this->assertEquals( $this->hourly_payment_info[ 'shifts' ][ 'worked_total' ], 2 );
		$this->assertEquals( $this->hourly_payment_info[ 'shifts' ][ 'amount' ], 40 );
		$this->assertEquals( $this->hourly_payment_info[ 'tip' ], 4 );
		$this->assertEquals( $this->hourly_payment_info[ 'delivery_fee' ], 3 );
		$this->assertEquals( $this->hourly_payment_info[ 'total_spent' ], 20 );
		$this->assertEquals( $this->hourly_payment_info[ 'delivery_fee_collected' ], -3 );
		$this->assertEquals( $this->hourly_payment_info[ 'standard_reimburse' ], 10 );
		$this->assertEquals( $this->hourly_payment_info[ 'total_payment_per_order' ], 7 );
		$this->assertEquals( $this->hourly_payment_info[ 'total_payment' ], 41 );
	}

	public static function tearDownAfterClass() {
		$name = self::NAME;
		Restaurant::q('select * from restaurant where name=?', [$name.' FORMAL'])->delete();
		Restaurant::q('select * from restaurant where name=?', [$name.' INFORMAL'])->delete();
		Order::q('select * from `order` where name=?', [$name])->delete();
		Admin::q('select * from admin where name=?', [$name.' ORDER'])->delete();
		Admin::q('select * from admin where name=?', [$name.' HOUR'])->delete();
		Community::q('select * from dish where name=?', [$name])->delete();
	}

}
