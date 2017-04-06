<?php

class DriverPriorityTest extends PHPUnit_Framework_TestCase
{

    public static function setUpBeforeClass()
    {
        $name = get_called_class();
        $hours = 2;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $hours . ' hours');
        $useDateEarly = $now->format('Y-m-d H:i:s');
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('+ ' . $hours . ' hours');
        $useDateLater = $now->format('Y-m-d H:i:s');

        // La Taquiza
        $r1 = new Restaurant([
            'name' => $name . ' - ONE',
            'active' => 1,
            'delivery' => 1,
            'credit' => 1,
            'delivery_fee' => '1.5',
            'confirmation' => 0,
            'community' => 'test',
            'timezone' => 'America/Los_Angeles',
            'open_for_business' => true,
            'delivery_service' => true,
            'loc_lat' => 34.0251,
            'loc_long' => -118.279
        ]);
        $r1->save();
        $restaurants[] = $r1;

        // Five Guys
        $r2 = new Restaurant([
            'name' => $name . ' - TWO',
            'active' => 1,
            'delivery' => 1,
            'credit' => 1,
            'delivery_fee' => '1.5',
            'confirmation' => 0,
            'community' => 'test',
            'timezone' => 'America/Los_Angeles',
            'open_for_business' => true,
            'delivery_service' => true,
            'loc_lat' => 34.0269,
            'loc_long' => -118.276
        ]);
        $r2->save();
        $restaurants[] = $r2;

        // Chipotle
        $r3 = new Restaurant([
            'name' => $name . ' - THREE',
            'active' => 1,
            'delivery' => 1,
            'credit' => 1,
            'delivery_fee' => '1.5',
            'confirmation' => 0,
            'community' => 'test',
            'timezone' => 'America/Los_Angeles',
            'open_for_business' => true,
            'delivery_service' => true,
            'loc_lat' => 34.017,
            'loc_long' => -118.282
        ]);
        $r3->save();
        $restaurants[] = $r3;

        // McDonalds
        $r4 = new Restaurant([
            'name' => $name . ' - FOUR',
            'active' => 1,
            'delivery' => 1,
            'credit' => 1,
            'delivery_fee' => '1.5',
            'confirmation' => 0,
            'community' => 'test',
            'timezone' => 'America/Los_Angeles',
            'open_for_business' => true,
            'delivery_service' => true,
            'loc_lat' => 34.0261,
            'loc_long' => -118.277
        ]);
        $r4->save();
        $restaurants[] = $r4;

        // Taco Bell
        $r5 = new Restaurant([
            'name' => $name . ' - FIVE',
            'active' => 1,
            'delivery' => 1,
            'credit' => 1,
            'delivery_fee' => '1.5',
            'confirmation' => 0,
            'community' => 'test',
            'timezone' => 'America/New_York',
            'open_for_business' => true,
            'delivery_service' => true,
            'loc_lat' => 34.0266,
            'loc_long' => -118.276
        ]);
        $r5->save();
        $restaurants[] = $r5;


        $c = new Community([
            'name' => $name . ' - ONE',
            'active' => 1,
            'timezone' => 'America/Los_Angeles',
            'driver-group' => 'drivers-testlogistics',
            'range' => 2,
            'private' => 1,
            'loc_lat' => 34.02481,
            'loc_lon' => -118.2881961,
            'delivery_logistics' => 2
        ]);
        $c->save();

        $c2 = new Community([
            'name' => $name . ' - TWO',
            'active' => 1,
            'timezone' => 'America/New_York',
            'driver-group' => 'drivers-testlogistics',
            'range' => 2,
            'private' => 1,
            'loc_lat' => 34.023281,
            'loc_lon' => -118.2881961,
            'delivery_logistics' => null
            ]);
        $c2->save();

        $r1c = new Restaurant_Community([
            'id_restaurant' => $r1->id_restaurant,
            'id_community' => $c->id_community
        ]);
        $r1c->save();

        $r2c = new Restaurant_Community([
            'id_restaurant' => $r2->id_restaurant,
            'id_community' => $c->id_community
        ]);
        $r2c->save();

        $r3c = new Restaurant_Community([
            'id_restaurant' => $r3->id_restaurant,
            'id_community' => $c->id_community
        ]);
        $r3c->save();

        $r4c = new Restaurant_Community([
            'id_restaurant' => $r4->id_restaurant,
            'id_community' => $c->id_community
        ]);
        $r4c->save();

        $r5c = new Restaurant_Community([
            'id_restaurant' => $r5->id_restaurant,
            'id_community' => $c2->id_community
        ]);
        $r5c->save();

        $cs = new Community_Shift([
            'id_community' => $c->id_community,
            'date_start' => $useDateEarly,
            'date_end' => $useDateLater,
            'active' => 1
        ]);
        $cs->save();

        $h1 = new Hour([
            'id_restaurant' => $r1->id_restaurant,
            'day' => strtolower(date('D')),
            'time_open' => '0:01',
            'time_close' => '23:59',
        ]);
        $h1->save();

        $h2 = new Hour([
            'id_restaurant' => $r2->id_restaurant,
            'day' => strtolower(date('D')),
            'time_open' => '0:01',
            'time_close' => '23:59',
        ]);
        $h2->save();

        $h3 = new Hour([
            'id_restaurant' => $r3->id_restaurant,
            'day' => strtolower(date('D')),
            'time_open' => '0:01',
            'time_close' => '23:59',
        ]);
        $h3->save();


        $a1 = new Admin([
            'name' => $name . ' - ONE',
            'login' => null,
            'active' => 1,
            'timezone' => 'America/Los_Angeles'
        ]);
        $a1->save();
        $drivers[] = $a1;

        $an1 = new Admin_Notification([
            'id_admin' => $a1->id_admin,
            'type' => 'sms',
            'value' => '$_ENV['DEBUG_PHONE']',
            'active' => true
        ]);
        $an1->save();

        $a2 = new Admin([
            'name' => $name . ' - TWO',
            'login' => null,
            'active' => 1,
            'timezone' => 'America/Los_Angeles'
        ]);
        $a2->save();
        $drivers[] = $a2;

        $an2 = new Admin_Notification([
            'id_admin' => $a2->id_admin,
            'type' => 'sms',
            'value' => '$_ENV['DEBUG_PHONE']',
            'active' => true
        ]);
        $an2->save();

        $a3 = new Admin([
            'name' => $name . ' - THREE',
            'login' => null,
            'active' => 1,
            'timezone' => 'America/Los_Angeles'
        ]);
        $a3->save();
        $drivers[] = $a3;

        $an3 = new Admin_Notification([
            'id_admin' => $a3->id_admin,
            'type' => 'sms',
            'value' => '$_ENV['DEBUG_PHONE']',
            'active' => true
        ]);
        $an3->save();


        $asa1 = new Admin_Shift_Assign([
            'id_community_shift' => $cs->id_community_shift,
            'id_admin' => $a1->id_admin,
            'date' => date('Y-m-d H:i:s'),
            'warned' => 0
        ]);
        $asa1->save();


        $asa2 = new Admin_Shift_Assign([
            'id_community_shift' => $cs->id_community_shift,
            'id_admin' => $a2->id_admin,
            'date' => date('Y-m-d H:i:s'),
            'warned' => 0
        ]);
        $asa2->save();

        $asa3 = new Admin_Shift_Assign([
            'id_community_shift' => $cs->id_community_shift,
            'id_admin' => $a3->id_admin,
            'date' => date('Y-m-d H:i:s'),
            'warned' => 0
        ]);
        $asa3->save();


        $u = new User([
            'name' => $name . ' - ONE',
            'phone' => '$_ENV['DEBUG_PHONE']',
            'address' => '123 main',
            'active' => 1
        ]);
        $u->save();

        $u2 = new User([
            'name' => $name . ' - TWO',
            'phone' => '$_ENV['DEBUG_PHONE']',
            'address' => '1157 W 27th St APT 2 - 90007',
            'active' => 1
        ]);
        $u2->save();

        $u3 = new User([
            'name' => $name . ' - THREE',
            'phone' => '$_ENV['DEBUG_PHONE']',
            'address' => '500 S Grand Ave Los Angeles CA 90014',
            'active' => 1
        ]);
        $u3->save();

        $d = new Dish([
            'name' => $name,
            'price' => '10',
            'id_restaurant' => $r1->id_restaurant,
            'active' => 1
        ]);
        $d->save();

        foreach ($restaurants as $res) {
            foreach ($drivers as $dri) {
                $n = new Crunchbutton_Notification([
                    'type' => Crunchbutton_Notification::TYPE_ADMIN,
                    'active' => true,
                    'id_restaurant' => $res->id_restaurant,
                    'id_admin' => $dri->id_admin
                ]);
                $n->save();
            }
        }
    }

    public static function tearDownAfterClass()
    {
        // Restaurant_Community doesn't need to be deleted because of cascade
        // Admin_Notification doesn't need to be deleted because of cascade
        // Notification doesn't need to be deleted because of cascade

        // Community_Shift records need to be deleted because it is set null instead of cascade
        // Admin_Shift_Assign records need to be deleted because it is set null instead of cascade
        $name = get_called_class();

        $community = Community::q('select * from community where name =?', [$name . ' - ONE'])->get(0);
        $communityId = $community->id_community;
        $cs = Crunchbutton_Community_Shift::q('select * from community_shift where id_community=?', [$communityId])->get(0);
        $csId = $cs->id_community_shift;

        $community2 = Community::q('select * from community where name =?', [$name . ' - TWO'])->get(0);
        $communityId2 = $community2->id_community;

        Crunchbutton_Admin_Shift_Assign::q('select * from admin_shift_assign where id_community_shift=?', [$csId])->delete();
        if( $cs && $cs->id_community ){ $cs->delete(); }
        if( $community && $community->id_community ){ $community->delete(); }
        if( $community2 && $community2->id_community ){ $community2->delete(); }

        Restaurant::q('select * from restaurant where name = ?', [$name . ' - ONE'])->delete();
        Restaurant::q('select * from restaurant where name = ?', [$name . ' - TWO'])->delete();
        Restaurant::q('select * from restaurant where name = ?', [$name . ' - THREE'])->delete();
        Restaurant::q('select * from restaurant where name = ?', [$name . ' - FOUR'])->delete();
		Restaurant::q('select * from restaurant where name = ?', [$name . ' - FIVE'])->delete();
        Admin::q('select * from admin where name=?', [$name . ' - ONE'])->delete();
        Admin::q('select * from admin where name=?', [$name . ' - TWO'])->delete();
        Admin::q('select * from admin where name=?', [$name . ' - THREE'])->delete();
        User::q('select * from `user` where name=?', [$name . ' - ONE'])->delete();
		User::q('select * from `user` where name=?', [$name . ' - TWO'])->delete();
        User::q('select * from `user` where name=?', [$name . ' - THREE'])->delete();
        Dish::q('select * from dish where name=?', [$name])->delete();

    }

    public function setUp()
    {
        $name = get_called_class();
        $this->restaurant1 = Restaurant::q('select * from restaurant where name=? order by id_restaurant desc limit 1', [$name . ' - ONE'])->get(0);
        $this->restaurant2 = Restaurant::q('select * from restaurant where name=? order by id_restaurant desc limit 1', [$name . ' - TWO'])->get(0);
        $this->restaurant3 = Restaurant::q('select * from restaurant where name=? order by id_restaurant desc limit 1', [$name . ' - THREE'])->get(0);
        $this->restaurant4 = Restaurant::q('select * from restaurant where name=? order by id_restaurant desc limit 1', [$name . ' - FOUR'])->get(0);
		$this->restaurant5 = Restaurant::q('select * from restaurant where name=? order by id_restaurant desc limit 1', [$name . ' - FIVE'])->get(0);
        $this->driver1 = Admin::q('select * from admin where name=? order by id_admin desc limit 1', [$name . ' - ONE'])->get(0);
        $this->driver2 = Admin::q('select * from admin where name=? order by id_admin desc limit 1', [$name . ' - TWO'])->get(0);
        $this->driver3 = Admin::q('select * from admin where name=? order by id_admin desc limit 1', [$name . ' - THREE'])->get(0);
        $this->user = User::q('select * from `user` where name=? order by id_user desc limit 1', [$name . ' - ONE'])->get(0);
		$this->user2 = User::q('select * from `user` where name=? order by id_user desc limit 1', [$name . ' - TWO'])->get(0);
        $this->user3 = User::q('select * from `user` where name=? order by id_user desc limit 1', [$name . ' - THREE'])->get(0);
        $this->community = Community::q('select * from community where name=? order by id_community desc limit 1', [$name . ' - ONE'])->get(0);
		$this->community2 = Community::q('select * from community where name=? order by id_community desc limit 1', [$name . ' - TWO'])->get(0);
    }

	public function tearDown()
    {
        $name = get_called_class();
        $this->restaurant1 = Restaurant::q('select * from restaurant where name=? order by id_restaurant desc limit 1', [$name . ' - ONE'])->get(0);
        $this->restaurant2 = Restaurant::q('select * from restaurant where name=? order by id_restaurant desc limit 1', [$name . ' - TWO'])->get(0);
        $this->restaurant3 = Restaurant::q('select * from restaurant where name=? order by id_restaurant desc limit 1', [$name . ' - THREE'])->get(0);
        $this->restaurant4 = Restaurant::q('select * from restaurant where name=? order by id_restaurant desc limit 1', [$name . ' - FOUR'])->get(0);
		$this->restaurant5 = Restaurant::q('select * from restaurant where name=? order by id_restaurant desc limit 1', [$name . ' - FIVE'])->get(0);

        Crunchbutton_Order_Priority::q('select * from order_priority where id_restaurant = ?', [$this->restaurant1->id_restaurant])->delete();
        Crunchbutton_Order_Priority::q('select * from order_priority where id_restaurant = ?', [$this->restaurant2->id_restaurant])->delete();
        Crunchbutton_Order_Priority::q('select * from order_priority where id_restaurant = ?', [$this->restaurant3->id_restaurant])->delete();
        Crunchbutton_Order_Priority::q('select * from order_priority where id_restaurant = ?', [$this->restaurant4->id_restaurant])->delete();
		Crunchbutton_Order_Priority::q('select * from order_priority where id_restaurant = ?', [$this->restaurant5->id_restaurant])->delete();
    }


    // All drivers should see the order if an order action has been taken
    //  and delivery logistics = 2 and order was placed more than a minute ago
    public function testDriverOrdersWithOrderActionOlderDL2()
    {
        $orders = [];
        $allops = [];
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $chipotle_lat = 34.0284;
        $chipotle_lon = -118.287;

        $ds = [$this->driver1, $this->driver2, $this->driver3];

        // Chipotle
        $og = $this->createOrderGroupAndSave($this->user, $this->restaurant3, $now, 70,
            $this->community, $chipotle_lat, $chipotle_lon, $ds,
            [Crunchbutton_Order_Priority::PRIORITY_HIGH, Crunchbutton_Order_Priority::PRIORITY_LOW,
                Crunchbutton_Order_Priority::PRIORITY_LOW], 30, $this->driver1->id_admin,
            "delivery-accepted");
        $orders[] = $og['o'];
        $allops = array_merge($allops, $og['ops']);
        $oa = $og['oa'];

        $dos = [];
        foreach ($ds as $driver) {
            $os = Crunchbutton_Order::deliveryOrdersForAdminOnly(1, $driver);
            $dos[$driver->id_admin] = $os;
        }

        foreach ($allops as $op) {
            $op->delete();
        }
        foreach ($orders as $o) {
            $o->delete();
        }


        $oa->delete();

        foreach ($ds as $driver) {
            $this->assertEquals($dos[$driver->id_admin]->count(), 1);
            $this->assertEquals($dos[$driver->id_admin]->get(0)->id_order, $orders[0]->id_order);
        }
    }


    // All drivers should see the order if an order action has been taken
    //  and delivery logistics = null and order was placed more than a minute ago
    public function testDriverOrdersWithOrderActionOlderNoDL()
    {
        $orders = [];
        $allops = [];
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));

        $chipotle_lat = 34.0284;
        $chipotle_lon = -118.287;

        $ds = [$this->driver1, $this->driver2, $this->driver3];

        // Chipotle
        $og = $this->createOrderGroupAndSave($this->user, $this->restaurant3, $now, 70,
            $this->community2, $chipotle_lat, $chipotle_lon, $ds,
            [Crunchbutton_Order_Priority::PRIORITY_HIGH, Crunchbutton_Order_Priority::PRIORITY_LOW,
                Crunchbutton_Order_Priority::PRIORITY_LOW], 30, $this->driver1->id_admin,
            "delivery-accepted");
        $orders[] = $og['o'];
        $allops = array_merge($allops, $og['ops']);
        $oa = $og['oa'];

        $dos = [];
        foreach ($ds as $driver) {
            $os = Crunchbutton_Order::deliveryOrdersForAdminOnly(1, $driver);
            $dos[$driver->id_admin] = $os;
        }

        foreach ($allops as $op) {
            $op->delete();
        }
        foreach ($orders as $o) {
            $o->delete();
        }


        $oa->delete();

        foreach ($ds as $driver) {
            $this->assertEquals($dos[$driver->id_admin]->count(), 1);
            $this->assertEquals($dos[$driver->id_admin]->get(0)->id_order, $orders[0]->id_order);
        }
    }

    // All drivers should see the order if an order action has been taken
    //  and delivery logistics = 2 and order was placed less than a minute ago
    public function testDriverOrdersWithOrderActionNewerDL2()
    {
        $orders = [];
        $allops = [];
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));

        $chipotle_lat = 34.0284;
        $chipotle_lon = -118.287;

        $ds = [$this->driver1, $this->driver2, $this->driver3];

        // Chipotle
        $og = $this->createOrderGroupAndSave($this->user, $this->restaurant3, $now, 30,
            $this->community, $chipotle_lat, $chipotle_lon, $ds,
            [Crunchbutton_Order_Priority::PRIORITY_HIGH, Crunchbutton_Order_Priority::PRIORITY_LOW,
                Crunchbutton_Order_Priority::PRIORITY_LOW], 30, $this->driver1->id_admin,
            "delivery-accepted");
        $orders[] = $og['o'];
        $allops = array_merge($allops, $og['ops']);
        $oa = $og['oa'];

        $dos = [];
        foreach ($ds as $driver) {
            $os = Crunchbutton_Order::deliveryOrdersForAdminOnly(1, $driver);
            $dos[$driver->id_admin] = $os;
        }

        foreach ($allops as $op) {
            $op->delete();
        }
        foreach ($orders as $o) {
            $o->delete();
        }


        $oa->delete();

        foreach ($ds as $driver) {
            $this->assertEquals($dos[$driver->id_admin]->count(), 1);
            $this->assertEquals($dos[$driver->id_admin]->get(0)->id_order, $orders[0]->id_order);
        }
    }


    // All drivers should see the order if an order action has been taken
    //  and delivery logistics = null and order was placed less than 3 minutes ago
    public function testDriverOrdersWithOrderActionNewerNoDL()
    {
        $orders = [];
        $allops = [];
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));

        $chipotle_lat = 34.0284;
        $chipotle_lon = -118.287;

        $ds = [$this->driver1, $this->driver2, $this->driver3];

        // Chipotle
        $og = $this->createOrderGroupAndSave($this->user, $this->restaurant3, $now, 170,
            $this->community2, $chipotle_lat, $chipotle_lon, $ds,
            [Crunchbutton_Order_Priority::PRIORITY_HIGH, Crunchbutton_Order_Priority::PRIORITY_LOW,
                Crunchbutton_Order_Priority::PRIORITY_LOW], 30, $this->driver1->id_admin,
            "delivery-accepted");
        $orders[] = $og['o'];
        $allops = array_merge($allops, $og['ops']);
        $oa = $og['oa'];

        $dos = [];
        foreach ($ds as $driver) {
            $os = Crunchbutton_Order::deliveryOrdersForAdminOnly(1, $driver);
            $dos[$driver->id_admin] = $os;
        }

        foreach ($allops as $op) {
            $op->delete();
        }
        foreach ($orders as $o) {
            $o->delete();
        }


        $oa->delete();

        foreach ($ds as $driver) {
            $this->assertEquals($dos[$driver->id_admin]->count(), 1);
            $this->assertEquals($dos[$driver->id_admin]->get(0)->id_order, $orders[0]->id_order);
        }
    }



    // All drivers should see the order if the order has no associated priorities or order actions
    //  and there is delivery logistics, when order was placed more than 3 minutes ago.
    public function testDriverOrdersWithNoPrioritiesOrOrderActionsOlderDL2()
    {
        $orders = [];
        $allops = [];
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));

        $chipotle_lat = 34.0284;
        $chipotle_lon = -118.287;

        $ds = [$this->driver1, $this->driver2, $this->driver3];

        // Chipotle
        $og = $this->createOrderGroupAndSave($this->user, $this->restaurant3, $now, 190,
            $this->community, $chipotle_lat, $chipotle_lon, $ds,
            [], 30);
        $orders[] = $og['o'];
        $allops = array_merge($allops, $og['ops']);

        $dos = [];
        foreach ($ds as $driver) {
            $os = Crunchbutton_Order::deliveryOrdersForAdminOnly(1, $driver);
            $dos[$driver->id_admin] = $os;
        }

        foreach ($allops as $op) {
            $op->delete();
        }
        foreach ($orders as $o) {
            $o->delete();
        }


        foreach ($ds as $driver) {
            $this->assertEquals($dos[$driver->id_admin]->count(), 1);
            $this->assertEquals($dos[$driver->id_admin]->get(0)->id_order, $orders[0]->id_order);
        }

    }

    // All drivers should see the order if the order has no associated priorities or order actions
    //  and there is no delivery logistics, when order was placed more than a minute ago.
    public function testDriverOrdersWithNoPrioritiesOrOrderActionsOlderNoDL()
    {
        $orders = [];
        $allops = [];
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));

        $chipotle_lat = 34.0284;
        $chipotle_lon = -118.287;

        $ds = [$this->driver1, $this->driver2, $this->driver3];

        // Chipotle
        $og = $this->createOrderGroupAndSave($this->user, $this->restaurant3, $now, 70,
            $this->community2, $chipotle_lat, $chipotle_lon, $ds,
            [], 30);
        $orders[] = $og['o'];
        $allops = array_merge($allops, $og['ops']);

        $dos = [];
        foreach ($ds as $driver) {
            $os = Crunchbutton_Order::deliveryOrdersForAdminOnly(1, $driver);
            $dos[$driver->id_admin] = $os;
        }

        foreach ($allops as $op) {
            $op->delete();
        }
        foreach ($orders as $o) {
            $o->delete();
        }


        foreach ($ds as $driver) {
            $this->assertEquals($dos[$driver->id_admin]->count(), 1);
            $this->assertEquals($dos[$driver->id_admin]->get(0)->id_order, $orders[0]->id_order);
        }

    }

    // All drivers should not see the order if the order has no associated priorities or order actions
    //  and there is delivery logistics, when order was placed less than a minute ago.
    public function testDriverOrdersWithNoPrioritiesOrOrderActionsNewerDL2()
    {
        $orders = [];
        $allops = [];
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));

        $chipotle_lat = 34.0284;
        $chipotle_lon = -118.287;

        $ds = [$this->driver1, $this->driver2, $this->driver3];

        // Chipotle
        $og = $this->createOrderGroupAndSave($this->user, $this->restaurant3, $now, 30,
            $this->community, $chipotle_lat, $chipotle_lon, $ds,
            [], 30);
        $orders[] = $og['o'];
        $allops = array_merge($allops, $og['ops']);

        $dos = [];
        foreach ($ds as $driver) {
            $os = Crunchbutton_Order::deliveryOrdersForAdminOnly(1, $driver);
            $dos[$driver->id_admin] = $os;
        }

        foreach ($allops as $op) {
            $op->delete();
        }
        foreach ($orders as $o) {
            $o->delete();
        }


        foreach ($ds as $driver) {
            $this->assertEquals($dos[$driver->id_admin]->count(), 0);
        }

    }

    // All drivers should see the order if the order has no associated priorities or order actions
    //  and there is no delivery logistics, when order was placed less than a minute ago.
    public function testDriverOrdersWithNoPrioritiesOrOrderActionsNewerNoDL()
    {
        $orders = [];
        $allops = [];
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));

        $chipotle_lat = 34.0284;
        $chipotle_lon = -118.287;

        $ds = [$this->driver1, $this->driver2, $this->driver3];

        // Chipotle
        $og = $this->createOrderGroupAndSave($this->user, $this->restaurant3, $now, 30,
            $this->community2, $chipotle_lat, $chipotle_lon, $ds,
            [], 30);
        $orders[] = $og['o'];
        $allops = array_merge($allops, $og['ops']);

        $dos = [];
        foreach ($ds as $driver) {
            $os = Crunchbutton_Order::deliveryOrdersForAdminOnly(1, $driver);
            $dos[$driver->id_admin] = $os;
        }

        foreach ($allops as $op) {
            $op->delete();
        }
        foreach ($orders as $o) {
            $o->delete();
        }


        foreach ($ds as $driver) {
            $this->assertEquals($dos[$driver->id_admin]->count(), 1);
            $this->assertEquals($dos[$driver->id_admin]->get(0)->id_order, $orders[0]->id_order);
        }

    }


    // All drivers assigned PRIORITY_NO_ONE.
    // All drivers should see the order.
    public function testDriverOrdersWithPriorityNoOneOlderDL2()
    {
        $orders = [];
        $allops = [];
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));

        $chipotle_lat = 34.0284;
        $chipotle_lon = -118.287;

        $ds = [$this->driver1, $this->driver2, $this->driver3];

        // Chipotle
        $og = $this->createOrderGroupAndSave($this->user, $this->restaurant3, $now, 70,
            $this->community, $chipotle_lat, $chipotle_lon, $ds,
            [Crunchbutton_Order_Priority::PRIORITY_NO_ONE, Crunchbutton_Order_Priority::PRIORITY_NO_ONE,
                Crunchbutton_Order_Priority::PRIORITY_NO_ONE]);
        $orders[] = $og['o'];
        $allops = array_merge($allops, $og['ops']);

        $dos = [];
        foreach ($ds as $driver) {
            $os = Crunchbutton_Order::deliveryOrdersForAdminOnly(1, $driver);
            $dos[$driver->id_admin] = $os;
        }

        foreach ($allops as $op) {
//            print "Order priority: $op->id_order_priority\n";
            $op->delete();
        }
        foreach ($orders as $o) {
//            print "Order: $op->id_order\n";
            $o->delete();
        }

        foreach ($ds as $driver) {
            $this->assertEquals($dos[$driver->id_admin]->count(), 1);
            $this->assertEquals($dos[$driver->id_admin]->get(0)->id_order, $orders[0]->id_order);
        }

    }

    // All drivers assigned PRIORITY_NO_ONE.
    // All drivers should see the order.
    public function testDriverOrdersWithPriorityNoOneOlderNoDL()
    {
        $orders = [];
        $allops = [];
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));

        $chipotle_lat = 34.0284;
        $chipotle_lon = -118.287;

        $ds = [$this->driver1, $this->driver2, $this->driver3];

        // Chipotle
        $og = $this->createOrderGroupAndSave($this->user, $this->restaurant3, $now, 70,
            $this->community2, $chipotle_lat, $chipotle_lon, $ds,
            [Crunchbutton_Order_Priority::PRIORITY_NO_ONE, Crunchbutton_Order_Priority::PRIORITY_NO_ONE,
                Crunchbutton_Order_Priority::PRIORITY_NO_ONE]);
        $orders[] = $og['o'];
        $allops = array_merge($allops, $og['ops']);

        $dos = [];
        foreach ($ds as $driver) {
            $os = Crunchbutton_Order::deliveryOrdersForAdminOnly(1, $driver);
            $dos[$driver->id_admin] = $os;
        }

        foreach ($allops as $op) {
//            print "Order priority: $op->id_order_priority\n";
            $op->delete();
        }
        foreach ($orders as $o) {
//            print "Order: $op->id_order\n";
            $o->delete();
        }

        foreach ($ds as $driver) {
            $this->assertEquals($dos[$driver->id_admin]->count(), 1);
            $this->assertEquals($dos[$driver->id_admin]->get(0)->id_order, $orders[0]->id_order);
        }

    }


    // All drivers assigned PRIORITY_NO_ONE.
    // All drivers should see the order.
    public function testDriverOrdersWithPriorityNoOneNewerDL2()
    {
        $orders = [];
        $allops = [];
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));

        $chipotle_lat = 34.0284;
        $chipotle_lon = -118.287;

        $ds = [$this->driver1, $this->driver2, $this->driver3];

        // Chipotle
        $og = $this->createOrderGroupAndSave($this->user, $this->restaurant3, $now, 30,
            $this->community, $chipotle_lat, $chipotle_lon, $ds,
            [Crunchbutton_Order_Priority::PRIORITY_NO_ONE, Crunchbutton_Order_Priority::PRIORITY_NO_ONE,
                Crunchbutton_Order_Priority::PRIORITY_NO_ONE]);
        $orders[] = $og['o'];
        $allops = array_merge($allops, $og['ops']);

        $dos = [];
        foreach ($ds as $driver) {
            $os = Crunchbutton_Order::deliveryOrdersForAdminOnly(1, $driver);
            $dos[$driver->id_admin] = $os;
        }

        foreach ($allops as $op) {
//            print "Order priority: $op->id_order_priority\n";
            $op->delete();
        }
        foreach ($orders as $o) {
//            print "Order: $op->id_order\n";
            $o->delete();
        }

        foreach ($ds as $driver) {
            $this->assertEquals($dos[$driver->id_admin]->count(), 1);
            $this->assertEquals($dos[$driver->id_admin]->get(0)->id_order, $orders[0]->id_order);
        }

    }

    // All drivers assigned PRIORITY_NO_ONE.
    // All drivers should see the order.
    public function testDriverOrdersWithPriorityNoOneNewerNoDL()
    {
        $orders = [];
        $allops = [];
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));

        $chipotle_lat = 34.0284;
        $chipotle_lon = -118.287;

        $ds = [$this->driver1, $this->driver2, $this->driver3];

        // Chipotle
        $og = $this->createOrderGroupAndSave($this->user, $this->restaurant3, $now, 30,
            $this->community2, $chipotle_lat, $chipotle_lon, $ds,
            [Crunchbutton_Order_Priority::PRIORITY_NO_ONE, Crunchbutton_Order_Priority::PRIORITY_NO_ONE,
                Crunchbutton_Order_Priority::PRIORITY_NO_ONE]);
        $orders[] = $og['o'];
        $allops = array_merge($allops, $og['ops']);

        $dos = [];
        foreach ($ds as $driver) {
            $os = Crunchbutton_Order::deliveryOrdersForAdminOnly(1, $driver);
            $dos[$driver->id_admin] = $os;
        }

        foreach ($allops as $op) {
//            print "Order priority: $op->id_order_priority\n";
            $op->delete();
        }
        foreach ($orders as $o) {
//            print "Order: $op->id_order\n";
            $o->delete();
        }

        foreach ($ds as $driver) {
            $this->assertEquals($dos[$driver->id_admin]->count(), 1);
            $this->assertEquals($dos[$driver->id_admin]->get(0)->id_order, $orders[0]->id_order);
        }

    }


    // Only driver with high priority should see the order if the order has an unexpired high priority.
    //  In this test, driver 1 has high priority, and drivers 2 and 3 have low priority.
    //  Only driver 1 should see the order.
    public function testDriverOrdersWithUnexpiredPriorityOlderDL2()
    {
        $orders = [];
        $allops = [];
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));

        $chipotle_lat = 34.0284;
        $chipotle_lon = -118.287;

        $ds = [$this->driver1, $this->driver2, $this->driver3];

        // Chipotle
        $og = $this->createOrderGroupAndSave($this->user, $this->restaurant3, $now, 70,
            $this->community, $chipotle_lat, $chipotle_lon, $ds,
            [Crunchbutton_Order_Priority::PRIORITY_HIGH, Crunchbutton_Order_Priority::PRIORITY_LOW,
                Crunchbutton_Order_Priority::PRIORITY_LOW]);
        $orders[] = $og['o'];
        $allops = array_merge($allops, $og['ops']);

        $dos = [];
        foreach ($ds as $driver) {
            $os = Crunchbutton_Order::deliveryOrdersForAdminOnly(1, $driver);
            $dos[$driver->id_admin] = $os;
        }

        foreach ($allops as $op) {
//            print "Order priority: $op->id_order_priority\n";
            $op->delete();
        }
        foreach ($orders as $o) {
//            print "Order: $op->id_order\n";
            $o->delete();
        }

        foreach ($ds as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($dos[$driver->id_admin]->count(), 1);
                $this->assertEquals($dos[$driver->id_admin]->get(0)->id_order, $orders[0]->id_order);
            } else {
                $this->assertEquals($dos[$driver->id_admin]->count(), 0);
            }
        }

    }

    // Only driver with high priority should see the order if the order has an unexpired high priority.
    //  In this test, driver 1 has high priority, and drivers 2 and 3 have low priority.
    //  Only driver 1 should see the order.
    public function testDriverOrdersWithUnexpiredPriorityOlderNoDL()
    {
        $orders = [];
        $allops = [];
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));

        $chipotle_lat = 34.0284;
        $chipotle_lon = -118.287;

        $ds = [$this->driver1, $this->driver2, $this->driver3];

        // Chipotle
        $og = $this->createOrderGroupAndSave($this->user, $this->restaurant3, $now, 70,
            $this->community2, $chipotle_lat, $chipotle_lon, $ds,
            [Crunchbutton_Order_Priority::PRIORITY_HIGH, Crunchbutton_Order_Priority::PRIORITY_LOW,
                Crunchbutton_Order_Priority::PRIORITY_LOW]);
        $orders[] = $og['o'];
        $allops = array_merge($allops, $og['ops']);

        $dos = [];
        foreach ($ds as $driver) {
            $os = Crunchbutton_Order::deliveryOrdersForAdminOnly(1, $driver);
            $dos[$driver->id_admin] = $os;
        }

        foreach ($allops as $op) {
//            print "Order priority: $op->id_order_priority\n";
            $op->delete();
        }
        foreach ($orders as $o) {
//            print "Order: $op->id_order\n";
            $o->delete();
        }

        foreach ($ds as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($dos[$driver->id_admin]->count(), 1);
                $this->assertEquals($dos[$driver->id_admin]->get(0)->id_order, $orders[0]->id_order);
            } else {
                $this->assertEquals($dos[$driver->id_admin]->count(), 0);
            }
        }

    }

    // Only driver with high priority should see the order if the order has an unexpired high priority.
    //  In this test, driver 1 has high priority, and drivers 2 and 3 have low priority.
    //  Only driver 1 should see the order.
    public function testDriverOrdersWithUnexpiredPriorityNewerDL2()
    {
        $orders = [];
        $allops = [];
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));

        $chipotle_lat = 34.0284;
        $chipotle_lon = -118.287;

        $ds = [$this->driver1, $this->driver2, $this->driver3];

        // Chipotle
        $og = $this->createOrderGroupAndSave($this->user, $this->restaurant3, $now, 30,
            $this->community, $chipotle_lat, $chipotle_lon, $ds,
            [Crunchbutton_Order_Priority::PRIORITY_HIGH, Crunchbutton_Order_Priority::PRIORITY_LOW,
                Crunchbutton_Order_Priority::PRIORITY_LOW]);
        $orders[] = $og['o'];
        $allops = array_merge($allops, $og['ops']);

        $dos = [];
        foreach ($ds as $driver) {
            $os = Crunchbutton_Order::deliveryOrdersForAdminOnly(1, $driver);
            $dos[$driver->id_admin] = $os;
        }

        foreach ($allops as $op) {
//            print "Order priority: $op->id_order_priority\n";
            $op->delete();
        }
        foreach ($orders as $o) {
//            print "Order: $op->id_order\n";
            $o->delete();
        }

        foreach ($ds as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($dos[$driver->id_admin]->count(), 1);
                $this->assertEquals($dos[$driver->id_admin]->get(0)->id_order, $orders[0]->id_order);
            } else {
                $this->assertEquals($dos[$driver->id_admin]->count(), 0);
            }
        }

    }

    // Only driver with high priority should see the order if the order has an unexpired high priority.
    //  In this test, driver 1 has high priority, and drivers 2 and 3 have low priority.
    //  Only driver 1 should see the order.
    public function testDriverOrdersWithUnexpiredPriorityNewerNoDL()
    {
        $orders = [];
        $allops = [];
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));

        $chipotle_lat = 34.0284;
        $chipotle_lon = -118.287;

        $ds = [$this->driver1, $this->driver2, $this->driver3];

        // Chipotle
        $og = $this->createOrderGroupAndSave($this->user, $this->restaurant3, $now, 30,
            $this->community2, $chipotle_lat, $chipotle_lon, $ds,
            [Crunchbutton_Order_Priority::PRIORITY_HIGH, Crunchbutton_Order_Priority::PRIORITY_LOW,
                Crunchbutton_Order_Priority::PRIORITY_LOW]);
        $orders[] = $og['o'];
        $allops = array_merge($allops, $og['ops']);

        $dos = [];
        foreach ($ds as $driver) {
            $os = Crunchbutton_Order::deliveryOrdersForAdminOnly(1, $driver);
            $dos[$driver->id_admin] = $os;
        }

        foreach ($allops as $op) {
//            print "Order priority: $op->id_order_priority\n";
            $op->delete();
        }
        foreach ($orders as $o) {
//            print "Order: $op->id_order\n";
            $o->delete();
        }

        foreach ($ds as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($dos[$driver->id_admin]->count(), 1);
                $this->assertEquals($dos[$driver->id_admin]->get(0)->id_order, $orders[0]->id_order);
            } else {
                $this->assertEquals($dos[$driver->id_admin]->count(), 0);
            }
        }

    }


    // All drivers should see the order if the order has an unexpired high priority and an order action.
    //  In this test, driver 1 has high priority, and drivers 2 and 3 have low priority.
    //  Driver 1 accepted.  Therefore, all drivers should see the order.
    public function testDriverOrdersWithOrderActionAndUnexpiredPriorityOlderDL2()
    {
        $orders = [];
        $allops = [];
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));

        $chipotle_lat = 34.0284;
        $chipotle_lon = -118.287;

        $ds = [$this->driver1, $this->driver2, $this->driver3];

        // Chipotle
        $og = $this->createOrderGroupAndSave($this->user, $this->restaurant3, $now, 70,
            $this->community, $chipotle_lat, $chipotle_lon, $ds,
            [Crunchbutton_Order_Priority::PRIORITY_HIGH, Crunchbutton_Order_Priority::PRIORITY_LOW,
                Crunchbutton_Order_Priority::PRIORITY_LOW], 30, $this->driver1->id_admin,
            "delivery-accepted");
        $orders[] = $og['o'];
        $allops = array_merge($allops, $og['ops']);

        $dos = [];
        foreach ($ds as $driver) {
            $os = Crunchbutton_Order::deliveryOrdersForAdminOnly(1, $driver);
            $dos[$driver->id_admin] = $os;
        }

        foreach ($allops as $op) {
//            print "Order priority: $op->id_order_priority\n";
            $op->delete();
        }
        foreach ($orders as $o) {
//            print "Order: $op->id_order\n";
            $o->delete();
        }

        foreach ($ds as $driver) {
            $this->assertEquals($dos[$driver->id_admin]->count(), 1);
            $this->assertEquals($dos[$driver->id_admin]->get(0)->id_order, $orders[0]->id_order);
        }

    }

    // All drivers should see the order if the order has an unexpired high priority and an order action.
    //  In this test, driver 1 has high priority, and drivers 2 and 3 have low priority.
    //  Driver 1 accepted.  Therefore, all drivers should see the order.
    public function testDriverOrdersWithOrderActionAndUnexpiredPriorityOlderNoDL()
    {
        $orders = [];
        $allops = [];
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));

        $chipotle_lat = 34.0284;
        $chipotle_lon = -118.287;

        $ds = [$this->driver1, $this->driver2, $this->driver3];

        // Chipotle
        $og = $this->createOrderGroupAndSave($this->user, $this->restaurant3, $now, 70,
            $this->community2, $chipotle_lat, $chipotle_lon, $ds,
            [Crunchbutton_Order_Priority::PRIORITY_HIGH, Crunchbutton_Order_Priority::PRIORITY_LOW,
                Crunchbutton_Order_Priority::PRIORITY_LOW], 30, $this->driver1->id_admin,
            "delivery-accepted");
        $orders[] = $og['o'];
        $allops = array_merge($allops, $og['ops']);

        $dos = [];
        foreach ($ds as $driver) {
            $os = Crunchbutton_Order::deliveryOrdersForAdminOnly(1, $driver);
            $dos[$driver->id_admin] = $os;
        }

        foreach ($allops as $op) {
//            print "Order priority: $op->id_order_priority\n";
            $op->delete();
        }
        foreach ($orders as $o) {
//            print "Order: $op->id_order\n";
            $o->delete();
        }

        foreach ($ds as $driver) {
            $this->assertEquals($dos[$driver->id_admin]->count(), 1);
            $this->assertEquals($dos[$driver->id_admin]->get(0)->id_order, $orders[0]->id_order);
        }

    }


    // All drivers should see the order if the order has an unexpired high priority and an order action.
    //  In this test, driver 1 has high priority, and drivers 2 and 3 have low priority.
    //  Driver 1 accepted.  Therefore, all drivers should see the order.
    public function testDriverOrdersWithOrderActionAndUnexpiredPriorityNewerDL2()
    {
        $orders = [];
        $allops = [];
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));

        $chipotle_lat = 34.0284;
        $chipotle_lon = -118.287;

        $ds = [$this->driver1, $this->driver2, $this->driver3];

        // Chipotle
        $og = $this->createOrderGroupAndSave($this->user, $this->restaurant3, $now, 30,
            $this->community, $chipotle_lat, $chipotle_lon, $ds,
            [Crunchbutton_Order_Priority::PRIORITY_HIGH, Crunchbutton_Order_Priority::PRIORITY_LOW,
                Crunchbutton_Order_Priority::PRIORITY_LOW], 30, $this->driver1->id_admin,
            "delivery-accepted");
        $orders[] = $og['o'];
        $allops = array_merge($allops, $og['ops']);

        $dos = [];
        foreach ($ds as $driver) {
            $os = Crunchbutton_Order::deliveryOrdersForAdminOnly(1, $driver);
            $dos[$driver->id_admin] = $os;
        }

        foreach ($allops as $op) {
//            print "Order priority: $op->id_order_priority\n";
            $op->delete();
        }
        foreach ($orders as $o) {
//            print "Order: $op->id_order\n";
            $o->delete();
        }

        foreach ($ds as $driver) {
            $this->assertEquals($dos[$driver->id_admin]->count(), 1);
            $this->assertEquals($dos[$driver->id_admin]->get(0)->id_order, $orders[0]->id_order);
        }

    }

    // All drivers should see the order if the order has an unexpired high priority and an order action.
    //  In this test, driver 1 has high priority, and drivers 2 and 3 have low priority.
    //  Driver 1 accepted.  Therefore, all drivers should see the order.
    public function testDriverOrdersWithOrderActionAndUnexpiredPriorityNewerNoDL()
    {
        $orders = [];
        $allops = [];
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));

        $chipotle_lat = 34.0284;
        $chipotle_lon = -118.287;

        $ds = [$this->driver1, $this->driver2, $this->driver3];

        // Chipotle
        $og = $this->createOrderGroupAndSave($this->user, $this->restaurant3, $now, 30,
            $this->community2, $chipotle_lat, $chipotle_lon, $ds,
            [Crunchbutton_Order_Priority::PRIORITY_HIGH, Crunchbutton_Order_Priority::PRIORITY_LOW,
                Crunchbutton_Order_Priority::PRIORITY_LOW], 30, $this->driver1->id_admin,
            "delivery-accepted");
        $orders[] = $og['o'];
        $allops = array_merge($allops, $og['ops']);

        $dos = [];
        foreach ($ds as $driver) {
            $os = Crunchbutton_Order::deliveryOrdersForAdminOnly(1, $driver);
            $dos[$driver->id_admin] = $os;
        }

        foreach ($allops as $op) {
//            print "Order priority: $op->id_order_priority\n";
            $op->delete();
        }
        foreach ($orders as $o) {
//            print "Order: $op->id_order\n";
            $o->delete();
        }

        foreach ($ds as $driver) {
            $this->assertEquals($dos[$driver->id_admin]->count(), 1);
            $this->assertEquals($dos[$driver->id_admin]->get(0)->id_order, $orders[0]->id_order);
        }

    }

    // All drivers should see the order if the order has an unexpired high priority and an order action.
    //  In this test, driver 1 has high priority, and drivers 2 and 3 have low priority.
    //  Driver 2 accepted.  Therefore, all drivers should see the order.
    public function testDriverOrdersWithOrderActionAndUnexpiredPriority2OlderDL2()
    {
        $orders = [];
        $allops = [];
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));

        $chipotle_lat = 34.0284;
        $chipotle_lon = -118.287;

        $ds = [$this->driver1, $this->driver2, $this->driver3];

        // Chipotle
        $og = $this->createOrderGroupAndSave($this->user, $this->restaurant3, $now, 70,
            $this->community, $chipotle_lat, $chipotle_lon, $ds,
            [Crunchbutton_Order_Priority::PRIORITY_HIGH, Crunchbutton_Order_Priority::PRIORITY_LOW,
                Crunchbutton_Order_Priority::PRIORITY_LOW], 30, $this->driver2->id_admin,
            "delivery-accepted");
        $orders[] = $og['o'];
        $allops = array_merge($allops, $og['ops']);

        $dos = [];
        foreach ($ds as $driver) {
            $os = Crunchbutton_Order::deliveryOrdersForAdminOnly(1, $driver);
            $dos[$driver->id_admin] = $os;
        }

        foreach ($allops as $op) {
//            print "Order priority: $op->id_order_priority\n";
            $op->delete();
        }
        foreach ($orders as $o) {
//            print "Order: $op->id_order\n";
            $o->delete();
        }

        foreach ($ds as $driver) {
            $this->assertEquals($dos[$driver->id_admin]->count(), 1);
            $this->assertEquals($dos[$driver->id_admin]->get(0)->id_order, $orders[0]->id_order);
        }

    }

    // All drivers should see the order if the order has an unexpired high priority and an order action.
    //  In this test, driver 1 has high priority, and drivers 2 and 3 have low priority.
    //  Driver 2 accepted.  Therefore, all drivers should see the order.
    public function testDriverOrdersWithOrderActionAndUnexpiredPriority2OlderNoDL()
    {
        $orders = [];
        $allops = [];
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));

        $chipotle_lat = 34.0284;
        $chipotle_lon = -118.287;

        $ds = [$this->driver1, $this->driver2, $this->driver3];

        // Chipotle
        $og = $this->createOrderGroupAndSave($this->user, $this->restaurant3, $now, 70,
            $this->community, $chipotle_lat, $chipotle_lon, $ds,
            [Crunchbutton_Order_Priority::PRIORITY_HIGH, Crunchbutton_Order_Priority::PRIORITY_LOW,
                Crunchbutton_Order_Priority::PRIORITY_LOW], 30, $this->driver2->id_admin,
            "delivery-accepted");
        $orders[] = $og['o'];
        $allops = array_merge($allops, $og['ops']);

        $dos = [];
        foreach ($ds as $driver) {
            $os = Crunchbutton_Order::deliveryOrdersForAdminOnly(1, $driver);
            $dos[$driver->id_admin] = $os;
        }

        foreach ($allops as $op) {
//            print "Order priority: $op->id_order_priority\n";
            $op->delete();
        }
        foreach ($orders as $o) {
//            print "Order: $op->id_order\n";
            $o->delete();
        }

        foreach ($ds as $driver) {
            $this->assertEquals($dos[$driver->id_admin]->count(), 1);
            $this->assertEquals($dos[$driver->id_admin]->get(0)->id_order, $orders[0]->id_order);
        }

    }

    // All drivers should see the order if the order has an unexpired high priority and an order action.
    //  In this test, driver 1 has high priority, and drivers 2 and 3 have low priority.
    //  Driver 2 accepted.  Therefore, all drivers should see the order.
    public function testDriverOrdersWithOrderActionAndUnexpiredPriority2NewerDL2()
    {
        $orders = [];
        $allops = [];
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));

        $chipotle_lat = 34.0284;
        $chipotle_lon = -118.287;

        $ds = [$this->driver1, $this->driver2, $this->driver3];

        // Chipotle
        $og = $this->createOrderGroupAndSave($this->user, $this->restaurant3, $now, 30,
            $this->community, $chipotle_lat, $chipotle_lon, $ds,
            [Crunchbutton_Order_Priority::PRIORITY_HIGH, Crunchbutton_Order_Priority::PRIORITY_LOW,
                Crunchbutton_Order_Priority::PRIORITY_LOW], 30, $this->driver2->id_admin,
            "delivery-accepted");
        $orders[] = $og['o'];
        $allops = array_merge($allops, $og['ops']);

        $dos = [];
        foreach ($ds as $driver) {
            $os = Crunchbutton_Order::deliveryOrdersForAdminOnly(1, $driver);
            $dos[$driver->id_admin] = $os;
        }

        foreach ($allops as $op) {
//            print "Order priority: $op->id_order_priority\n";
            $op->delete();
        }
        foreach ($orders as $o) {
//            print "Order: $op->id_order\n";
            $o->delete();
        }

        foreach ($ds as $driver) {
            $this->assertEquals($dos[$driver->id_admin]->count(), 1);
            $this->assertEquals($dos[$driver->id_admin]->get(0)->id_order, $orders[0]->id_order);
        }

    }

    // All drivers should see the order if the order has an unexpired high priority and an order action.
    //  In this test, driver 1 has high priority, and drivers 2 and 3 have low priority.
    //  Driver 2 accepted.  Therefore, all drivers should see the order.
    public function testDriverOrdersWithOrderActionAndUnexpiredPriority2NewerNoDL()
    {
        $orders = [];
        $allops = [];
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));

        $chipotle_lat = 34.0284;
        $chipotle_lon = -118.287;

        $ds = [$this->driver1, $this->driver2, $this->driver3];

        // Chipotle
        $og = $this->createOrderGroupAndSave($this->user, $this->restaurant3, $now, 30,
            $this->community, $chipotle_lat, $chipotle_lon, $ds,
            [Crunchbutton_Order_Priority::PRIORITY_HIGH, Crunchbutton_Order_Priority::PRIORITY_LOW,
                Crunchbutton_Order_Priority::PRIORITY_LOW], 30, $this->driver2->id_admin,
            "delivery-accepted");
        $orders[] = $og['o'];
        $allops = array_merge($allops, $og['ops']);

        $dos = [];
        foreach ($ds as $driver) {
            $os = Crunchbutton_Order::deliveryOrdersForAdminOnly(1, $driver);
            $dos[$driver->id_admin] = $os;
        }

        foreach ($allops as $op) {
//            print "Order priority: $op->id_order_priority\n";
            $op->delete();
        }
        foreach ($orders as $o) {
//            print "Order: $op->id_order\n";
            $o->delete();
        }

        foreach ($ds as $driver) {
            $this->assertEquals($dos[$driver->id_admin]->count(), 1);
            $this->assertEquals($dos[$driver->id_admin]->get(0)->id_order, $orders[0]->id_order);
        }

    }


    // All drivers should see the order if the order has an expired high priority.
    public function testDriverOrdersWithExpiredPriorityOlderDL2()
    {
        $orders = [];
        $allops = [];
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));

        $seconds = 120;
        $earlier120 = clone $now;
        $earlier120->modify('- ' . $seconds . ' seconds');

        $chipotle_lat = 34.0284;
        $chipotle_lon = -118.287;

        $ds = [$this->driver1, $this->driver2, $this->driver3];

        // Chipotle
        $og = $this->createOrderGroupAndSave($this->user, $this->restaurant3, $earlier120, 70,
            $this->community, $chipotle_lat, $chipotle_lon, $ds,
            [Crunchbutton_Order_Priority::PRIORITY_HIGH, Crunchbutton_Order_Priority::PRIORITY_LOW,
                Crunchbutton_Order_Priority::PRIORITY_LOW]);
        $orders[] = $og['o'];
        $allops = array_merge($allops, $og['ops']);

        $dos = [];
        foreach ($ds as $driver) {
            $os = Crunchbutton_Order::deliveryOrdersForAdminOnly(1, $driver);
            $dos[$driver->id_admin] = $os;
        }

        foreach ($allops as $op) {
//            print "Order priority: $op->id_order_priority\n";
            $op->delete();
        }
        foreach ($orders as $o) {
//            print "Order: $op->id_order\n";
            $o->delete();
        }

        foreach ($ds as $driver) {
            $this->assertEquals($dos[$driver->id_admin]->count(), 1);
            $this->assertEquals($dos[$driver->id_admin]->get(0)->id_order, $orders[0]->id_order);
        }

    }

    // All drivers should see the order if the order has an expired high priority.
    public function testDriverOrdersWithExpiredPriorityOlderNoDL()
    {
        $orders = [];
        $allops = [];
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));

        $seconds = 120;
        $earlier120 = clone $now;
        $earlier120->modify('- ' . $seconds . ' seconds');

        $chipotle_lat = 34.0284;
        $chipotle_lon = -118.287;

        $ds = [$this->driver1, $this->driver2, $this->driver3];

        // Chipotle
        $og = $this->createOrderGroupAndSave($this->user, $this->restaurant3, $earlier120, 70,
            $this->community, $chipotle_lat, $chipotle_lon, $ds,
            [Crunchbutton_Order_Priority::PRIORITY_HIGH, Crunchbutton_Order_Priority::PRIORITY_LOW,
                Crunchbutton_Order_Priority::PRIORITY_LOW]);
        $orders[] = $og['o'];
        $allops = array_merge($allops, $og['ops']);

        $dos = [];
        foreach ($ds as $driver) {
            $os = Crunchbutton_Order::deliveryOrdersForAdminOnly(1, $driver);
            $dos[$driver->id_admin] = $os;
        }

        foreach ($allops as $op) {
//            print "Order priority: $op->id_order_priority\n";
            $op->delete();
        }
        foreach ($orders as $o) {
//            print "Order: $op->id_order\n";
            $o->delete();
        }

        foreach ($ds as $driver) {
            $this->assertEquals($dos[$driver->id_admin]->count(), 1);
            $this->assertEquals($dos[$driver->id_admin]->get(0)->id_order, $orders[0]->id_order);
        }

    }

    // All drivers should see the order if the order has an expired high priority.
    public function testDriverOrdersWithExpiredPriorityNewerDL2()
    {
        $orders = [];
        $allops = [];
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));

        $seconds = 120;
        $earlier120 = clone $now;
        $earlier120->modify('- ' . $seconds . ' seconds');

        $chipotle_lat = 34.0284;
        $chipotle_lon = -118.287;

        $ds = [$this->driver1, $this->driver2, $this->driver3];

        // Chipotle
        $og = $this->createOrderGroupAndSave($this->user, $this->restaurant3, $earlier120, 30,
            $this->community, $chipotle_lat, $chipotle_lon, $ds,
            [Crunchbutton_Order_Priority::PRIORITY_HIGH, Crunchbutton_Order_Priority::PRIORITY_LOW,
                Crunchbutton_Order_Priority::PRIORITY_LOW]);
        $orders[] = $og['o'];
        $allops = array_merge($allops, $og['ops']);

        $dos = [];
        foreach ($ds as $driver) {
            $os = Crunchbutton_Order::deliveryOrdersForAdminOnly(1, $driver);
            $dos[$driver->id_admin] = $os;
        }

        foreach ($allops as $op) {
//            print "Order priority: $op->id_order_priority\n";
            $op->delete();
        }
        foreach ($orders as $o) {
//            print "Order: $op->id_order\n";
            $o->delete();
        }

        foreach ($ds as $driver) {
            $this->assertEquals($dos[$driver->id_admin]->count(), 1);
            $this->assertEquals($dos[$driver->id_admin]->get(0)->id_order, $orders[0]->id_order);
        }

    }

    // All drivers should see the order if the order has an expired high priority.
    public function testDriverOrdersWithExpiredPriorityNewerNoDL()
    {
        $orders = [];
        $allops = [];
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));

        $seconds = 120;
        $earlier120 = clone $now;
        $earlier120->modify('- ' . $seconds . ' seconds');

        $chipotle_lat = 34.0284;
        $chipotle_lon = -118.287;

        $ds = [$this->driver1, $this->driver2, $this->driver3];

        // Chipotle
        $og = $this->createOrderGroupAndSave($this->user, $this->restaurant3, $earlier120, 30,
            $this->community, $chipotle_lat, $chipotle_lon, $ds,
            [Crunchbutton_Order_Priority::PRIORITY_HIGH, Crunchbutton_Order_Priority::PRIORITY_LOW,
                Crunchbutton_Order_Priority::PRIORITY_LOW]);
        $orders[] = $og['o'];
        $allops = array_merge($allops, $og['ops']);

        $dos = [];
        foreach ($ds as $driver) {
            $os = Crunchbutton_Order::deliveryOrdersForAdminOnly(1, $driver);
            $dos[$driver->id_admin] = $os;
        }

        foreach ($allops as $op) {
//            print "Order priority: $op->id_order_priority\n";
            $op->delete();
        }
        foreach ($orders as $o) {
//            print "Order: $op->id_order\n";
            $o->delete();
        }

        foreach ($ds as $driver) {
            $this->assertEquals($dos[$driver->id_admin]->count(), 1);
            $this->assertEquals($dos[$driver->id_admin]->get(0)->id_order, $orders[0]->id_order);
        }

    }

    public function defaultOrder($user, $restaurantId, $date, $community) {
       return new Order([
            'name' => $user->name,
            'address' => $user->address,
            'phone' => $user->phone,
            'price' => '10',
            'price_plus_delivery_markup' => '10',
            'final_price' => '12.8',
            'final_price_plus_delivery_markup' => '12.8',
            'pay_type' => 'cash',
            'delivery_type' => 'delivery',
            'delivery_service' => true,
            'id_user' => $user->id_user,
            'date' => $date,
            'id_community' => $community->id_community,
            'id_restaurant' => $restaurantId,
            'active' => 1
        ]);

    }

    public function defaultOrderWithLoc($user, $restaurantId, $date, $community, $lat, $lon) {
        return new Order([
            'name' => $user->name,
            'address' => $user->address,
            'phone' => $user->phone,
            'price' => '10',
            'price_plus_delivery_markup' => '10',
            'final_price' => '12.8',
            'final_price_plus_delivery_markup' => '12.8',
            'pay_type' => 'cash',
            'delivery_type' => 'delivery',
            'delivery_service' => true,
            'id_user' => $user->id_user,
            'date' => $date,
            'id_community' => $community->id_community,
            'id_restaurant' => $restaurantId,
            'active' => 1,
            'lat' => $lat,
            'lon' => $lon
        ]);

    }


    public function defaultOrderPriority($order, $restaurant, $driver,
                                         $priorityTime, $priority, $delay, $expiration) {
        return new Crunchbutton_Order_Priority([
            'id_order' => $order->id_order,
            'id_restaurant' => $restaurant->id_restaurant,
            'id_admin' => $driver->id_admin,
            'priority_time' => $priorityTime,
            'priority_algo_version' => Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX_ALGO_VERSION,
            'priority_given' => $priority,
            'seconds_delay' => $delay,
            'priority_expiration' => $expiration
        ]);

    }

    public function createOrderGroupAndSave($user, $restaurant, $nowdt, $earlierSeconds, $community, $lat, $lon, $drivers,
                                            $priorities, $lastActionEarlierSeconds=null,
                                            $actionDriverId = null, $actionString=null) {
        $og = [];
        $ops = [];
        $oa = null;

        $usedt = clone $nowdt;
        $usedt->modify('- ' . $earlierSeconds . ' seconds');
        $useDateString = $usedt->format('Y-m-d H:i:s');

        $laterdt = clone $usedt;
        $laterdt->modify('+ ' . Crunchbutton_Order_Logistics::TIME_MAX_DELAY . ' seconds');
        $laterDateString = $laterdt->format('Y-m-d H:i:s');

        if (!is_null($lastActionEarlierSeconds)) {
            $actiondt = clone $nowdt;
            $actiondt->modify('- ' . $lastActionEarlierSeconds . ' seconds');
            $actionTimeString = $actiondt->format('Y-m-d H:i:s');
        }
        $o = $this->defaultOrderWithLoc($user, $restaurant->id_restaurant, $useDateString, $community, $lat, $lon);
        $o->save();
        $numDrivers = count($drivers);
        for ($i = 0; $i < $numDrivers; $i++) {
            $driver = $drivers[$i];
            $priority = $priorities[$i];
            if (!is_null($priority)){
                if ($priority == Crunchbutton_Order_Priority::PRIORITY_HIGH) {
                    $op = $this->defaultOrderPriority($o, $restaurant, $driver,
                        $useDateString, $priority, 0, $laterDateString);
                } else if ($priority == Crunchbutton_Order_Priority::PRIORITY_LOW) {
                    $op = $this->defaultOrderPriority($o, $restaurant, $driver,
                        $useDateString, $priority, Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDateString);
                } else if ($priority == Crunchbutton_Order_Priority::PRIORITY_NO_ONE) {
                    $op = $this->defaultOrderPriority($o, $restaurant, $driver,
                        $useDateString, $priority, 0, $useDateString);
                } else {
                    $op = $this->defaultOrderPriority($o, $restaurant, $driver,
                        $useDateString, $priority, 0, $useDateString);
                }
                $op->save();
                $ops[] = $op;
            }
            if (!is_null($actionDriverId) && $actionDriverId == $driver->id_admin && !is_null($actionString)) {
                $oa = new Order_Action([
                    'id_order' => $o->id_order,
                    'id_admin' => $driver->id_admin,
                    'timestamp' => $actionTimeString,
                    'type' => $actionString,
                    'note' => ''
                ]);
                $oa->save();
                $o->delivery_status = $oa->id_order_action;
                $o->save();
            }
        }
        $og['o'] = $o;
        $og['ops'] = $ops;
        $og['oa'] = $oa;
        return $og;
    }


    public function defaultOLP($restaurant, $start, $end, $duration=5, $dow=0) {
        return new Crunchbutton_Order_Logistics_Parking([
            'id_restaurant' => $restaurant->id_restaurant,
            'time_start_community' => $start,
            'time_end_community' => $end,
            'day_of_week' => $dow,
            'parking_duration' => $duration
        ]);
    }

    public function defaultOLOT($restaurant, $start, $end, $otime=15, $factor=1, $dow=0) {
        return new Crunchbutton_Order_Logistics_Ordertime([
            'id_restaurant' => $restaurant->id_restaurant,
            'time_start_community' => $start,
            'time_end_community' => $end,
            'day_of_week' => $dow,
            'order_time' => $otime,
            'scale_factor' => $factor
        ]);
    }

    public function defaultOLCS($community, $start, $end, $mph=10, $dow=0) {
        return new Crunchbutton_Order_Logistics_Communityspeed([
            'id_community' => $community->id_community,
            'time_start_community' => $start,
            'time_end_community' => $end,
            'day_of_week' => $dow,
            'mph' => $mph
        ]);
    }

    public function defaultOLBA($community, $address, $lat=34.023281, $lon=-118.2881961) {
        return new Crunchbutton_Order_Logistics_Badaddress([
            'id_community' => $community->id_community,
            'address_lc' => $address,
            'lat' => $lat,
            'lon' => $lon
        ]);
    }

    public function defaultScore($admin, $score=Cockpit_Admin_Score::DEFAULT_SCORE) {
        return new Cockpit_Admin_Score([
            'id_admin' => $admin->id_admin,
            'score' => $score
        ]);
    }

    public function defaultOLC($restaurant, $dow, $start="00:00:00", $end="24:00:00", $clusterid=null) {
        if (is_null($clusterid)) {
            $clusterid = $restaurant->id_restaurant;
        }
        return new Crunchbutton_Order_Logistics_Cluster([
            'id_restaurant_cluster' => $clusterid,
            'id_restaurant' => $restaurant->id_restaurant,
            'time_start_community' => $start,
            'time_end_community' => $end,
            'day_of_week' => $dow
        ]);
    }

    public function defaultDriverDestination($id){
        return new Crunchbutton_Order_Logistics_Destination([
            'id' => $id,
            'type' => Crunchbutton_Order_Logistics_Destination::TYPE_DRIVER
        ]);
    }

    public function defaultRestaurantDestination($id, $cluster = null){
        return new Crunchbutton_Order_Logistics_Destination([
            'id' => $id,
            'cluster' => $cluster,
            'type' => Crunchbutton_Order_Logistics_Destination::TYPE_RESTAURANT
        ]);
    }

    public function defaultCustomerDestination($id){
        return new Crunchbutton_Order_Logistics_Destination([
            'id' => $id,
            'type' => Crunchbutton_Order_Logistics_Destination::TYPE_CUSTOMER
        ]);
    }

    public function createAdminLocation($id_admin, $lat, $lon, $date) {
        return new Cockpit_Admin_Location([
            'id_admin' => $id_admin,
            'lat' => $lat,
            'lon' => $lon,
            'accuracy' => 50,
            'date' => $date
        ]);
    }

    public function createFakecustomer($community, $lat, $lon) {
        return new Crunchbutton_Order_Logistics_Fakecustomer([
            'id_community' => $community->id_community,
            'lat' => $lat,
            'lon' => $lon
        ]);
    }


}
