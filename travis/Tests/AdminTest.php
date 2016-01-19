<?php

class AdminTest extends PHPUnit_Framework_TestCase
{

    // TODO: Test that this works correctly for different time zones
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

        $p1 = new Crunchbutton_Phone(['phone' => '6265550100']);
        $p1->save();

        $p2 = new Crunchbutton_Phone(['phone' => '6265550101']);
        $p2->save();

        $p3 = new Crunchbutton_Phone(['phone' => '6265550102']);
        $p3->save();

        $p1id= $p1->id_phone;
        $p2id= $p2->id_phone;
        $p3id= $p3->id_phone;

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

        // Chick-Fil-A
        $r6 = new Restaurant([
            'name' => $name . ' - SIX',
            'active' => 1,
            'delivery' => 1,
            'credit' => 1,
            'delivery_fee' => '1.5',
            'confirmation' => 0,
            'community' => 'test',
            'timezone' => 'America/New_York',
            'open_for_business' => true,
            'delivery_service' => true,
            'loc_lat' => 34.0175,
            'loc_long' => -118.283
        ]);
        $r6->save();
        $restaurants[] = $r6;


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
            'driver_group' => 'drivers-testlogistics',
            'range' => 2,
            'private' => 1,
            'loc_lat' => 34.023281,
            'loc_lon' => -118.2881961,
            'delivery_logistics' => 2
        ]);
        $c2->save();

        $c3 = new Community([
            'name' => $name . ' - THREE',
            'active' => 1,
            'timezone' => 'America/New_York',
            'driver_group' => 'drivers-testlogistics',
            'range' => 2,
            'private' => 1,
            'loc_lat' => 34.023281,
            'loc_lon' => -118.2881961,
            'delivery_logistics' => null
        ]);
        $c3->save();

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

        $r6c = new Restaurant_Community([
            'id_restaurant' => $r6->id_restaurant,
            'id_community' => $c->id_community
        ]);
        $r6c->save();

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

        $h4 = new Hour([
            'id_restaurant' => $r4->id_restaurant,
            'day' => strtolower(date('D')),
            'time_open' => '0:01',
            'time_close' => '23:59',
        ]);
        $h4->save();

        $h5 = new Hour([
            'id_restaurant' => $r5->id_restaurant,
            'day' => strtolower(date('D')),
            'time_open' => '0:01',
            'time_close' => '23:59',
        ]);
        $h5->save();

        $h6 = new Hour([
            'id_restaurant' => $r6->id_restaurant,
            'day' => strtolower(date('D')),
            'time_open' => '0:01',
            'time_close' => '23:59',
        ]);
        $h6->save();

        $a1 = new Admin([
            'name' => $name . ' - ONE',
            'login' => null,
            'active' => 1,
            'timezone' => 'America/Los_Angeles',
            'id_phone' => intval($p1id),
            'phone' => $p1->phone
        ]);
        $a1->save();
        $drivers[] = $a1;

        $an1 = new Admin_Notification([
            'id_admin' => $a1->id_admin,
            'type' => 'sms',
            'value' => '_PHONE_',
            'active' => true
        ]);
        $an1->save();

        $a2 = new Admin([
            'name' => $name . ' - TWO',
            'login' => null,
            'active' => 1,
            'timezone' => 'America/Los_Angeles',
            'id_phone' => $p2id,
            'phone' => $p2->phone
        ]);
        $a2->save();
        $drivers[] = $a2;

        $an2 = new Admin_Notification([
            'id_admin' => $a2->id_admin,
            'type' => 'phone',
            'value' => '_PHONE_',
            'active' => true
        ]);
        $an2->save();

        $a3 = new Admin([
            'name' => $name . ' - THREE',
            'login' => null,
            'active' => 0,
            'timezone' => 'America/Los_Angeles'
        ]);
        $a3->save();
        $drivers[] = $a3;

        $an3 = new Admin_Notification([
            'id_admin' => $a3->id_admin,
            'type' => 'sms',
            'value' => '_PHONE_',
            'active' => true
        ]);
        $an3->save();

        $a4 = new Admin([
            'name' => $name . ' - FOUR',
            'login' => null,
            'active' => 0,
            'timezone' => 'America/Los_Angeles'
        ]);
        $a4->save();
        $drivers[] = $a4;

        $an4 = new Admin_Notification([
            'id_admin' => $a4->id_admin,
            'type' => 'phone',
            'value' => '_PHONE_',
            'active' => true
        ]);
        $an4->save();

        $a5 = new Admin([
            'name' => $name . ' - FIVE',
            'login' => null,
            'active' => 0,
            'timezone' => 'America/Los_Angeles'
        ]);
        $a5->save();
        $drivers[] = $a5;

        $an5 = new Admin_Notification([
            'id_admin' => $a5->id_admin,
            'type' => 'phone',
            'value' => '_PHONE_',
            'active' => true
        ]);
        $an5->save();

        $an5b = new Admin_Notification([
            'id_admin' => $a5->id_admin,
            'type' => 'sms',
            'value' => '_PHONE_',
            'active' => true
        ]);
        $an5b->save();

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

        $asa4 = new Admin_Shift_Assign([
            'id_community_shift' => $cs->id_community_shift,
            'id_admin' => $a4->id_admin,
            'date' => date('Y-m-d H:i:s'),
            'warned' => 0
        ]);
        $asa4->save();

        $asa5 = new Admin_Shift_Assign([
            'id_community_shift' => $cs->id_community_shift,
            'id_admin' => $a5->id_admin,
            'date' => date('Y-m-d H:i:s'),
            'warned' => 0
        ]);
        $asa5->save();


        $u = new User([
            'name' => $name . ' - ONE',
            'phone' => '_PHONE_',
            'address' => '123 main',
            'active' => 1
        ]);
        $u->save();

        $u2 = new User([
            'name' => $name . ' - TWO',
            'phone' => '_PHONE_',
            'address' => '1157 W 27th St APT 2 - 90007',
            'active' => 1
        ]);
        $u2->save();

        $u3 = new User([
            'name' => $name . ' - THREE',
            'phone' => '_PHONE_',
            'address' => '500 S Grand Ave Los Angeles CA 90014',
            'active' => 1
        ]);
        $u3->save();

        $u4 = new User([
            'name' => $name . ' - FOUR',
            'phone' => '_PHONE_',
            'address' => '517 Pier Ave, Hermosa Beach, CA 90254',
            'active' => 1
        ]);
        $u4->save();

        $d = new Dish([
            'name' => $name,
            'price' => '10',
            'id_restaurant' => $r1->id_restaurant,
            'active' => 1
        ]);
        $d->save();

        foreach ($restaurants as $res) {
            foreach ($drivers as $dri) {
                if ($dri->id_admin != $a5->id_admin) {
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


        $community3 = Community::q('select * from community where name =?', [$name . ' - THREE'])->get(0);
        $communityId3 = $community3->id_community;

        Crunchbutton_Admin_Shift_Assign::q('select * from admin_shift_assign where id_community_shift=?', [$csId])->delete();
        $cs->delete();
        $community->delete();
        $community2->delete();
        $community3->delete();

        Restaurant::q('select * from restaurant where name = ?', [$name . ' - ONE'])->delete();
        Restaurant::q('select * from restaurant where name = ?', [$name . ' - TWO'])->delete();
        Restaurant::q('select * from restaurant where name = ?', [$name . ' - THREE'])->delete();
        Restaurant::q('select * from restaurant where name = ?', [$name . ' - FOUR'])->delete();
		Restaurant::q('select * from restaurant where name = ?', [$name . ' - FIVE'])->delete();
        Restaurant::q('select * from restaurant where name = ?', [$name . ' - SIX'])->delete();
        Admin::q('select * from admin where name=?', [$name . ' - ONE'])->delete();
        Admin::q('select * from admin where name=?', [$name . ' - TWO'])->delete();
        Admin::q('select * from admin where name=?', [$name . ' - THREE'])->delete();
        Admin::q('select * from admin where name=?', [$name . ' - FOUR'])->delete();
        Admin::q('select * from admin where name=?', [$name . ' - FIVE'])->delete();
        User::q('select * from `user` where name=?', [$name . ' - ONE'])->delete();
		User::q('select * from `user` where name=?', [$name . ' - TWO'])->delete();
        User::q('select * from `user` where name=?', [$name . ' - THREE'])->delete();
        User::q('select * from `user` where name=?', [$name . ' - FOUR'])->delete();
        Dish::q('select * from dish where name=?', [$name])->delete();
        Phone::q('select * from phone where phone=?', ['6265550100'])->delete();
        Phone::q('select * from phone where phone=?', ['6265550101'])->delete();
        Phone::q('select * from phone where phone=?', ['6265550102'])->delete();

    }
	
    public function setUp()
    {
        $name = get_called_class();
        $this->restaurant1 = Restaurant::q('select * from restaurant where name=? order by id_restaurant desc limit 1', [$name . ' - ONE'])->get(0);
        $this->restaurant2 = Restaurant::q('select * from restaurant where name=? order by id_restaurant desc limit 1', [$name . ' - TWO'])->get(0);
        $this->restaurant3 = Restaurant::q('select * from restaurant where name=? order by id_restaurant desc limit 1', [$name . ' - THREE'])->get(0);
        $this->restaurant4 = Restaurant::q('select * from restaurant where name=? order by id_restaurant desc limit 1', [$name . ' - FOUR'])->get(0);
		$this->restaurant5 = Restaurant::q('select * from restaurant where name=? order by id_restaurant desc limit 1', [$name . ' - FIVE'])->get(0);
        $this->restaurant6 = Restaurant::q('select * from restaurant where name=? order by id_restaurant desc limit 1', [$name . ' - SIX'])->get(0);
        $this->driver1 = Admin::q('select * from admin where name=? order by id_admin desc limit 1', [$name . ' - ONE'])->get(0);
        $this->driver2 = Admin::q('select * from admin where name=? order by id_admin desc limit 1', [$name . ' - TWO'])->get(0);
        $this->driver3 = Admin::q('select * from admin where name=? order by id_admin desc limit 1', [$name . ' - THREE'])->get(0);
        $this->driver4 = Admin::q('select * from admin where name=? order by id_admin desc limit 1', [$name . ' - FOUR'])->get(0);
        $this->driver5 = Admin::q('select * from admin where name=? order by id_admin desc limit 1', [$name . ' - FIVE'])->get(0);
        $this->user = User::q('select * from `user` where name=? order by id_user desc limit 1', [$name . ' - ONE'])->get(0);
		$this->user2 = User::q('select * from `user` where name=? order by id_user desc limit 1', [$name . ' - TWO'])->get(0);
        $this->user3 = User::q('select * from `user` where name=? order by id_user desc limit 1', [$name . ' - THREE'])->get(0);
        $this->user4 = User::q('select * from `user` where name=? order by id_user desc limit 1', [$name . ' - FOUR'])->get(0);
        $this->community = Community::q('select * from community where name=? order by id_community desc limit 1', [$name . ' - ONE'])->get(0);
		$this->community2 = Community::q('select * from community where name=? order by id_community desc limit 1', [$name . ' - TWO'])->get(0);
        $this->community3 = Community::q('select * from community where name=? order by id_community desc limit 1', [$name . ' - THREE'])->get(0);
    }
	
	public function tearDown()
    {
        $name = get_called_class();

        Crunchbutton_Order_Priority::q('select * from order_priority where id_restaurant = ?', [$this->restaurant1->id_restaurant])->delete();
        Crunchbutton_Order_Priority::q('select * from order_priority where id_restaurant = ?', [$this->restaurant2->id_restaurant])->delete();
        Crunchbutton_Order_Priority::q('select * from order_priority where id_restaurant = ?', [$this->restaurant3->id_restaurant])->delete();
        Crunchbutton_Order_Priority::q('select * from order_priority where id_restaurant = ?', [$this->restaurant4->id_restaurant])->delete();
		Crunchbutton_Order_Priority::q('select * from order_priority where id_restaurant = ?', [$this->restaurant5->id_restaurant])->delete();
        Crunchbutton_Order_Priority::q('select * from order_priority where id_restaurant = ?', [$this->restaurant6->id_restaurant])->delete();

        Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_admin = ?', [$this->driver1->id_admin])->delete();
        Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_admin = ?', [$this->driver2->id_admin])->delete();
        Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_admin = ?', [$this->driver3->id_admin])->delete();
        Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_admin = ?', [$this->driver4->id_admin])->delete();
        Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_admin = ?', [$this->driver5->id_admin])->delete();

    }


    public function testGetByPhone1()
    {
        $p = '6265550100';
        $u = $this->driver1->getByPhone($p);
        $this->assertNotNull($u);

    }

    public function testGetByPhone2()
    {
        $p = '6265550900';
        $u = $this->driver1->getByPhone($p);
        $this->assertNull($u);

    }

    public function testGetByPhone3()
    {
        $p = '6265550102';
        $u = $this->driver3->getByPhone($p, true);
        $this->assertNull($u);

    }

    public function testGetTextNumber1()
    {
        $p = '_PHONE_';
        $u = $this->driver1->getTxtNumber();
        $this->assertNotNull($u);

    }

    public function testGetTextNumber2()
    {
        $u = $this->driver2->getTxtNumber();
        $this->assertEquals($u, false);
    }

    public function testGetPhoneNumber1()
    {
        $p = '_PHONE_';
        $u = $this->driver2->getPhoneNumber();
        $this->assertNotNull($u);

    }

    public function testGetPhoneNumber2()
    {
        $u = $this->driver3->getPhoneNumber();
        $this->assertEquals($u, false);
    }

    public function testActiveNotifications1()
    {
        $u = $this->driver3->activeNotifications()->count();
        $this->assertEquals($u, 1);
    }

    public function testActiveNotifications2()
    {
        $u = $this->driver5->activeNotifications()->count();
        $this->assertEquals($u, 2);
    }

    public function testRestaurantsHeDeliveryFor1()
    {
        $u = $this->driver1->restaurantsHeDeliveryFor()->count();
        $this->assertEquals($u, 6);
    }

    public function testRestaurantsHeDeliveryFor2()
    {
        $u = $this->driver5->restaurantsHeDeliveryFor()->count();
        $this->assertEquals($u, 0);
    }

//    public function testMisc()
//    {
//        $useDate2 = '2015-07-01 05:00:00';
//        $o1 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $useDate2, $this->community2);
//        $o1->save();
//        $useDT = new DateTime($o1->date, new DateTimeZone(c::config()->timezone)); // Should be PST
//        $useDT->modify('+ 1 minutes');
//        $useDate = $useDT->format('Y-m-d H:i:s');
//        var_dump($useDate);
//        $o1->delete();
//            }


//    public function testMisc2()
//    {
//
//        $useDate = '2015-07-01 05:00:00';
//        $o1 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $useDate, $this->community2);
//        $o1->save();
//        $o2 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $useDate, $this->community3);
//        $o2->save();
//        $qs = [];
//        $curCommunity = $o2->community();
//        var_dump($curCommunity);
//        $dl1 = $community1->delivery_logistics;
//        $dl2 = $community2->delivery_logistics;
//        $dl3 = $community3->delivery_logistics;
//
//        if (!is_null($curCommunity) && !is_null($curCommunity->delivery_logistics)) {
//           print "Check here \n";
//        } else {
//            print "Check here 2\n";
//
//        }
//
//            foreach ($qs as $q) {
//                $q->delete();
//            }
//        $o1->delete();
//        $o2->delete();
//
////        $this->assertEquals($dl1, 2);
////        $this->assertEquals($dl2, 2);
//        $this->assertEquals($dl3, 2);
//    }



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

    public function defaultOLS($restaurant, $start, $end, $duration=5, $dow=0) {
        return new Crunchbutton_Order_Logistics_Service([
            'id_restaurant' => $restaurant->id_restaurant,
            'time_start_community' => $start,
            'time_end_community' => $end,
            'day_of_week' => $dow,
            'service_duration' => $duration
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

    public function defaultOLR($order, $node_id_order, $driver, $seq, $node_type, $leaving_time, $lat, $lon, $isFake=false) {
        return new Crunchbutton_Order_Logistics_Route([
            'id_order' => $order->id_order,
            'node_id_order' => $node_id_order,
            'id_admin' => $driver->id_admin,
            'seq' => $seq,
            'node_type' => $node_type,
            'lat' => $lat,
            'lon' => $lon,
            'leaving_time' => $leaving_time,
            'is_fake' => $isFake
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

    // Locations are created every second from 0 to $window
    public function createAndSaveAdminLocations($id_admin, $lat, $lon, $dt, $window) {

        $locs = [];
        for ($i = 0; $i < $window; $i++) {
            $dt->modify('- ' . 1 . ' seconds');
            $date = $dt->format('Y-m-d H:i:s');

            $loc =  new Cockpit_Admin_Location([
                'id_admin' => $id_admin,
                'lat' => $lat,
                'lon' => $lon,
                'accuracy' => 50,
                'date' => $date
            ]);
            $loc->save();
            $locs[] = $loc;
        }
        return $locs;
    }


    public function createFakecustomer($community, $lat, $lon) {
        return new Crunchbutton_Order_Logistics_Fakecustomer([
            'id_community' => $community->id_community,
            'lat' => $lat,
            'lon' => $lon
        ]);
    }

    public function createDefaultQ($id_order, $id_admin, $type, $date_run, $date_start, $date_end, $status, $id_queue_type) {
        return new Crunchbutton_Queue(
            [
                'id_order' => $id_order,
                'id_admin' => $id_admin,
                'type' => $type,
                'date_run' => $date_run,
                'date_start' => $date_start,
                'date_end' => $date_end,
                'status' => $status,
                'id_queue_type' => $id_queue_type

            ]
        );
    }

    public function createDefaultAdminNotification($id_admin, $type, $value, $active) {
        return new Crunchbutton_Admin_Notification(
            [
                'id_admin' => $id_admin,
                'type' => $type,
                'value' => $value,
                'active' => $active
            ]
        );
    }

    public function createDefaultAdminNotificationLog($id_order, $description, $date) {
        return new Crunchbutton_Admin_Notification_Log(
            [
                'id_order' => $id_order,
                'description' => $description,
                'date' => $date
            ]
        );
    }


}
