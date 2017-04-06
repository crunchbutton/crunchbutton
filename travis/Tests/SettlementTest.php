<?php

class SettlementTest extends PHPUnit_Framework_TestCase {

	// because settlement filters stuff with 'test' on its name
	// to do:
	// add invites
	// make the payment schedule
	const NAME = 'Settlement Travis';

	public static function setUpBeforeClass() {

		$name = self::NAME;

		// restaurant stuff
		$r1 = new Restaurant([ 'name' => $name . ' FORMAL', 'formal_relationship' => false, 'active' => true, 'delivery' => true, 'credit' => 1, 'delivery_fee' => 5, 'confirmation' => 0, 'community' => 'test', 'timezone' => 'America/Los_Angeles', 'open_for_business' => true, 'delivery_service' => true ]);
		$r1->save();

		$r2 = new Restaurant([ 'name' => $name . ' INFORMAL', 'formal_relationship' => true, 'active' => true, 'delivery' => true, 'credit' => 1, 'delivery_fee' => 5, 'confirmation' => 0, 'community' => 'test', 'timezone' => 'America/Los_Angeles', 'open_for_business' => true, 'delivery_service' => true ]);
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
		$u = new User([ 'name' => $name, 'phone' => '$_ENV['DEBUG_PHONE']', 'address' => '123 main', 'active' => 1 ]);
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

		// orders driver hour without tips
		$d3_o1 = new Order;
		$charge = $d3_o1->process( [ 'address' => $u->address, 'phone' => $u->phone, 'name' => $u->name, 'cart' => [['id' => $d1->id_dish]], 'pay_type' => 'cash', 'delivery_type' => 'delivery', 'restaurant' => $r1->id_restaurant, 'make_default' => true, 'notes' => 'TEST', 'lat' => '33.175101', 'lon' => '-96.677810', 'local_gid' => 'RAND', 'processor' => Crunchbutton_User_Payment_Type::processor() ] );

		$d3_o2 = new Order;
		$charge = $d3_o2->process( [ 'address' => $u->address, 'phone' => $u->phone, 'name' => $u->name, 'cart' => [['id' => $d1->id_dish]], 'pay_type' => 'cash', 'tip' => 20, 'delivery_type' => 'delivery', 'restaurant' => $r1->id_restaurant, 'make_default' => true, 'notes' => 'TEST', 'lat' => '33.175101', 'lon' => '-96.677810', 'local_gid' => 'RAND', 'processor' => Crunchbutton_User_Payment_Type::processor() ] );
		$d3_o2->pay_type = 'card'; // fake card just to make calcs
		$d3_o2->save();

		$d3_o3 = new Order;
		$charge = $d3_o3->process( [ 'address' => $u->address, 'phone' => $u->phone, 'name' => $u->name, 'cart' => [['id' => $d2->id_dish]], 'pay_type' => 'cash', 'delivery_type' => 'delivery', 'restaurant' => $r2->id_restaurant, 'make_default' => true, 'notes' => 'TEST', 'lat' => '33.175101', 'lon' => '-96.677810', 'local_gid' => 'RAND', 'processor' => Crunchbutton_User_Payment_Type::processor() ] );

		$d3_o4 = new Order;
		$charge = $d3_o4->process( [ 'address' => $u->address, 'phone' => $u->phone, 'name' => $u->name, 'cart' => [['id' => $d2->id_dish]], 'pay_type' => 'cash', 'tip' => 20, 'delivery_type' => 'delivery', 'restaurant' => $r2->id_restaurant, 'make_default' => true, 'notes' => 'TEST', 'lat' => '33.175101', 'lon' => '-96.677810', 'local_gid' => 'RAND', 'processor' => Crunchbutton_User_Payment_Type::processor() ] );
		$d3_o4->pay_type = 'card'; // fake card just to make calcs
		$d3_o4->save();

		// orders driver make whole
		$d4_o1 = new Order;
		$charge = $d4_o1->process( [ 'address' => $u->address, 'phone' => $u->phone, 'name' => $u->name, 'cart' => [['id' => $d1->id_dish]], 'pay_type' => 'cash', 'delivery_type' => 'delivery', 'restaurant' => $r1->id_restaurant, 'make_default' => true, 'notes' => 'TEST', 'lat' => '33.175101', 'lon' => '-96.677810', 'local_gid' => 'RAND', 'processor' => Crunchbutton_User_Payment_Type::processor() ] );

		$d4_o2 = new Order;
		$charge = $d4_o2->process( [ 'address' => $u->address, 'phone' => $u->phone, 'name' => $u->name, 'cart' => [['id' => $d1->id_dish]], 'pay_type' => 'cash', 'tip' => 20, 'delivery_type' => 'delivery', 'restaurant' => $r1->id_restaurant, 'make_default' => true, 'notes' => 'TEST', 'lat' => '33.175101', 'lon' => '-96.677810', 'local_gid' => 'RAND', 'processor' => Crunchbutton_User_Payment_Type::processor() ] );
		$d4_o2->pay_type = 'card'; // fake card just to make calcs
		$d4_o2->save();

		$d4_o3 = new Order;
		$charge = $d4_o3->process( [ 'address' => $u->address, 'phone' => $u->phone, 'name' => $u->name, 'cart' => [['id' => $d2->id_dish]], 'pay_type' => 'cash', 'delivery_type' => 'delivery', 'restaurant' => $r2->id_restaurant, 'make_default' => true, 'notes' => 'TEST', 'lat' => '33.175101', 'lon' => '-96.677810', 'local_gid' => 'RAND', 'processor' => Crunchbutton_User_Payment_Type::processor() ] );

		$d4_o4 = new Order;
		$charge = $d4_o4->process( [ 'address' => $u->address, 'phone' => $u->phone, 'name' => $u->name, 'cart' => [['id' => $d2->id_dish]], 'pay_type' => 'cash', 'tip' => 20, 'delivery_type' => 'delivery', 'restaurant' => $r2->id_restaurant, 'make_default' => true, 'notes' => 'TEST', 'lat' => '33.175101', 'lon' => '-96.677810', 'local_gid' => 'RAND', 'processor' => Crunchbutton_User_Payment_Type::processor() ] );
		$d4_o4->pay_type = 'card'; // fake card just to make calcs
		$d4_o4->save();

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

		// driver hourly without tips
		$d3 = new Admin( [ 'name' => $name . ' HOUR WITHOUT TIPS', 'login' => null, 'active' => 1 ] );
		$d3->save();
		$pt3 = $d3->payment_type();
		$pt3->payment_type = Crunchbutton_Admin_Payment_Type::PAYMENT_TYPE_HOURS_WITHOUT_TIPS;
		$pt3->hour_rate = 20;
		$pt3->using_pex = 1;
		$pt3->save();

		// driver make whole
		$d4 = new Admin( [ 'name' => $name . ' MAKE WHOLE', 'login' => null, 'active' => 1 ] );
		$d4->save();
		$pt4 = $d4->payment_type();
		$pt4->payment_type = Crunchbutton_Admin_Payment_Type::PAYMENT_TYPE_HOURS_WITHOUT_TIPS;
		$pt4->hour_rate = 10;
		$pt4->using_pex = 1;
		$pt4->save();

		// community
		$c = new Community([ 'name' => $name, 'active' => 1, 'timezone' => 'America/Los_Angeles', 'driver-group' => 'drivers-test-settlement', 'range' => 2, 'private' => 1, 'delivery_logistics' => true, 'amount_per_order' => 3 ]);
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

		$d3_asa1 = new Admin_Shift_Assign([ 'id_community_shift' => $cs1->id_community_shift, 'id_admin' => $d3->id_admin, 'date' => date('Y-m-d H:i:s'), 'warned' => 1 ]);
		$d3_asa1->save();

		$d3_asa2 = new Admin_Shift_Assign([ 'id_community_shift' => $cs2->id_community_shift, 'id_admin' => $d3->id_admin, 'date' => date('Y-m-d H:i:s'), 'warned' => 1 ]);
		$d3_asa2->save();

		$d4_asa1 = new Admin_Shift_Assign([ 'id_community_shift' => $cs1->id_community_shift, 'id_admin' => $d4->id_admin, 'date' => date('Y-m-d H:i:s'), 'warned' => 1 ]);
		$d4_asa1->save();

		$d4_asa2 = new Admin_Shift_Assign([ 'id_community_shift' => $cs2->id_community_shift, 'id_admin' => $d4->id_admin, 'date' => date('Y-m-d H:i:s'), 'warned' => 1 ]);
		$d4_asa2->save();

		// hourly driver accepts orders
		$d1_o1->setStatus( Crunchbutton_Order_Action::DELIVERY_DELIVERED, false, $d1);
		$d1_o2->setStatus( Crunchbutton_Order_Action::DELIVERY_DELIVERED, false, $d1);
		$d1_o3->setStatus( Crunchbutton_Order_Action::DELIVERY_DELIVERED, false, $d1);
		$d1_o4->setStatus( Crunchbutton_Order_Action::DELIVERY_DELIVERED, false, $d1);

		// commissioned driver accepts orders
		$d2_o1->setStatus( Crunchbutton_Order_Action::DELIVERY_DELIVERED, false, $d2);
		$d2_o2->setStatus( Crunchbutton_Order_Action::DELIVERY_DELIVERED, false, $d2);
		$d2_o3->setStatus( Crunchbutton_Order_Action::DELIVERY_DELIVERED, false, $d2);
		$d2_o4->setStatus( Crunchbutton_Order_Action::DELIVERY_DELIVERED, false, $d2);

		// hourly without tips driver accepts orders
		$d3_o1->setStatus( Crunchbutton_Order_Action::DELIVERY_DELIVERED, false, $d3);
		$d3_o2->setStatus( Crunchbutton_Order_Action::DELIVERY_DELIVERED, false, $d3);
		$d3_o3->setStatus( Crunchbutton_Order_Action::DELIVERY_DELIVERED, false, $d3);
		$d3_o4->setStatus( Crunchbutton_Order_Action::DELIVERY_DELIVERED, false, $d3);

		// make whole driver accepts orders
		$d4_o1->setStatus( Crunchbutton_Order_Action::DELIVERY_DELIVERED, false, $d4);
		$d4_o2->setStatus( Crunchbutton_Order_Action::DELIVERY_DELIVERED, false, $d4);
		$d4_o3->setStatus( Crunchbutton_Order_Action::DELIVERY_DELIVERED, false, $d4);
		$d4_o4->setStatus( Crunchbutton_Order_Action::DELIVERY_DELIVERED, false, $d4);

	}

	public function setUp() {

		$now = new DateTime('now', new DateTimeZone(c::config()->timezone));
		$this->end_date = $now->format( 'Y-m-d' );

		$now->modify('- 2 day');
		$this->start_date = $now->format( 'Y-m-d' );

		$name = self::NAME;
		$this->driver_hourly = Admin::q('select * from admin where name=? order by id_admin desc limit 1', [$name.' HOUR'])->get(0);
		$this->driver_hourly_without_tips = Admin::q('select * from admin where name=? order by id_admin desc limit 1', [$name.' HOUR WITHOUT TIPS'])->get(0);
		$this->driver_commissioned = Admin::q('select * from admin where name=? order by id_admin desc limit 1', [$name.' ORDER'])->get(0);
		$this->driver_make_whole = Admin::q('select * from admin where name=? order by id_admin desc limit 1', [$name.' MAKE WHOLE'])->get(0);
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

		// gets the commissioned driver info
		$this->hourly_without_tips_payment_info = null;
		foreach ( $orders as $key => $val ) {
			if( $val[ 'id_admin' ] == $this->driver_hourly_without_tips->id_admin ){
				$this->hourly_without_tips_payment_info = $val;
			}
		}

		// gets the make whole driver info
		$this->make_whole_payment_info = null;
		foreach ( $orders as $key => $val ) {
			if( $val[ 'id_admin' ] == $this->driver_make_whole->id_admin ){
				$this->make_whole_payment_info = $val;
			}
		}

	}

	public function testDriverPaidByOrder(){

		$this->assertNotNull( $this->commissioned_payment_info );
		$this->assertEquals( count( $this->commissioned_payment_info[ 'orders' ] ), 2 );

		$this->assertEquals( $this->commissioned_payment_info[ 'invites_total' ], 0 );
		$this->assertEquals( $this->commissioned_payment_info[ 'invites_total_payment' ], 0 );
		$this->assertEquals( $this->commissioned_payment_info[ 'salary_type' ], 'orders' );
		$this->assertEquals( $this->commissioned_payment_info[ 'tip' ], 2 );
		$this->assertEquals( $this->commissioned_payment_info[ 'delivery_fee' ], 10 );
		$this->assertEquals( $this->commissioned_payment_info[ 'total_spent' ], 0 );
		$this->assertEquals( $this->commissioned_payment_info[ 'delivery_fee_collected' ], -5 );
		$this->assertEquals( $this->commissioned_payment_info[ 'standard_reimburse' ], 0 );
		$this->assertEquals( $this->commissioned_payment_info[ 'total_payment_per_order' ], 7 );
		$this->assertEquals( $this->commissioned_payment_info[ 'customer_fee_collected' ], 0 );
		$this->assertEquals( $this->commissioned_payment_info[ 'amount_per_order' ], 10 );
		$this->assertEquals( $this->commissioned_payment_info[ 'total_payment' ], 7 );
	}
	public function testDriverPaidByHour(){

		$this->assertNotNull( $this->hourly_payment_info );
		$this->assertEquals( count( $this->hourly_payment_info[ 'orders' ] ), 4 );

		$this->assertEquals( $this->hourly_payment_info[ 'invites_total' ], 0 );
		$this->assertEquals( $this->hourly_payment_info[ 'invites_total_payment' ], 0 );

		$this->assertEquals( count( $this->hourly_payment_info[ 'shifts' ][ 'worked' ] ) , 2 );
		$this->assertEquals( $this->hourly_payment_info[ 'salary_type' ], 'hours' );
		$this->assertEquals( $this->hourly_payment_info[ 'shifts' ][ 'worked_total' ], 2 );
		$this->assertEquals( $this->hourly_payment_info[ 'shifts' ][ 'amount' ], 40 );
		$this->assertEquals( $this->hourly_payment_info[ 'tip' ], 4 );
		$this->assertEquals( $this->hourly_payment_info[ 'delivery_fee' ], 10 );
		$this->assertEquals( $this->hourly_payment_info[ 'total_spent' ], 20 );
		$this->assertEquals( $this->hourly_payment_info[ 'delivery_fee_collected' ], -10 );
		$this->assertEquals( $this->hourly_payment_info[ 'amount_per_order' ], 20 );
		$this->assertEquals( $this->hourly_payment_info[ 'standard_reimburse' ], 10 );
		$this->assertEquals( $this->hourly_payment_info[ 'total_payment_per_order' ], 14 );
		$this->assertEquals( $this->hourly_payment_info[ 'total_payment' ], 34 );
	}


	public function testDriverPaidByHourWithoutTips(){

		$this->assertNotNull( $this->hourly_without_tips_payment_info );
		$this->assertEquals( count( $this->hourly_without_tips_payment_info[ 'orders' ] ), 4 );

		$this->assertEquals( $this->hourly_without_tips_payment_info[ 'invites_total' ], 0 );
		$this->assertEquals( $this->hourly_without_tips_payment_info[ 'invites_total_payment' ], 0 );

		$this->assertEquals( count( $this->hourly_without_tips_payment_info[ 'shifts' ][ 'worked' ] ) , 2 );
		$this->assertEquals( $this->hourly_without_tips_payment_info[ 'salary_type' ], 'hours' );
		$this->assertEquals( $this->hourly_without_tips_payment_info[ 'shifts' ][ 'worked_total' ], 2 );
		$this->assertEquals( $this->hourly_without_tips_payment_info[ 'shifts' ][ 'amount' ], 80 );
		$this->assertEquals( $this->hourly_without_tips_payment_info[ 'tip' ], 4 );
		$this->assertEquals( $this->hourly_without_tips_payment_info[ 'delivery_fee' ], 10 );
		$this->assertEquals( $this->hourly_without_tips_payment_info[ 'total_spent' ], 20 );
		$this->assertEquals( $this->hourly_without_tips_payment_info[ 'delivery_fee_collected' ], -10 );
		$this->assertEquals( $this->hourly_without_tips_payment_info[ 'amount_per_order' ], 20 );
		$this->assertEquals( $this->hourly_without_tips_payment_info[ 'standard_reimburse' ], 10 );
		$this->assertEquals( $this->hourly_without_tips_payment_info[ 'total_payment_per_order' ], 14 );
		$this->assertEquals( $this->hourly_without_tips_payment_info[ 'total_payment' ], 70 );
	}

	public function testDriverPaidMakeWhole(){

		$this->assertNotNull( $this->make_whole_payment_info );
		$this->assertEquals( count( $this->make_whole_payment_info[ 'orders' ] ), 4 );

		$this->assertEquals( $this->make_whole_payment_info[ 'invites_total' ], 0 );
		$this->assertEquals( $this->make_whole_payment_info[ 'invites_total_payment' ], 0 );

		$this->assertEquals( count( $this->make_whole_payment_info[ 'shifts' ][ 'worked' ] ) , 2 );
		$this->assertEquals( $this->make_whole_payment_info[ 'salary_type' ], 'hours' );
		$this->assertEquals( $this->make_whole_payment_info[ 'shifts' ][ 'worked_total' ], 2 );
		$this->assertEquals( $this->make_whole_payment_info[ 'shifts' ][ 'amount' ], 40 );
		$this->assertEquals( $this->make_whole_payment_info[ 'tip' ], 4 );
		$this->assertEquals( $this->make_whole_payment_info[ 'delivery_fee' ], 10 );
		$this->assertEquals( $this->make_whole_payment_info[ 'total_spent' ], 20 );
		$this->assertEquals( $this->make_whole_payment_info[ 'delivery_fee_collected' ], -10 );
		$this->assertEquals( $this->make_whole_payment_info[ 'amount_per_order' ], 20 );
		$this->assertEquals( $this->make_whole_payment_info[ 'standard_reimburse' ], 10 );
		$this->assertEquals( $this->make_whole_payment_info[ 'total_payment_per_order' ], 14 );
		$this->assertEquals( $this->make_whole_payment_info[ 'total_payment' ], 30 );
	}

	public static function tearDownAfterClass() {
		$name = self::NAME;
		Restaurant::q('select * from restaurant where name=?', [$name.' FORMAL'])->delete();
		Restaurant::q('select * from restaurant where name=?', [$name.' INFORMAL'])->delete();
		Order::q('select * from `order` where name=?', [$name])->delete();
		Admin::q('select * from admin where name=?', [$name.' ORDER'])->delete();
		Admin::q('select * from admin where name=?', [$name.' HOUR'])->delete();
		Admin::q('select * from admin where name=?', [$name.' HOUR WITHOUT TIPS'])->delete();
		Community::q('select * from dish where name=?', [$name])->delete();
	}

}
