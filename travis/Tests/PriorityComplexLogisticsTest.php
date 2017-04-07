<?php

class PriorityComplexLogisticsTest extends PHPUnit_Framework_TestCase
{

    // TODO: Test that this works correctly for different time zones
    public static function setUpBeforeClass()
    {
        $community_tz1 = 'America/Los_Angeles';
        $community_tz2 = 'America/New_York';
        $community_tz3 = 'America/New_York';
        $name = get_called_class();
        $hours = 6;
        $now = new DateTime('now', new DateTimeZone($community_tz1));
        $now->modify('- ' . $hours . ' hours');
        $useDateEarly = $now->format('Y-m-d H:i:s');
        $now = new DateTime('now', new DateTimeZone($community_tz1));
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
            'timezone' => $community_tz1,
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
            'timezone' => $community_tz1,
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
            'timezone' => $community_tz1,
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
            'timezone' => $community_tz1,
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
            'timezone' => $community_tz2,
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
            'timezone' => $community_tz2,
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
            'timezone' => $community_tz1,
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
            'timezone' => $community_tz2,
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
            'timezone' => $community_tz3,
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
            'timezone' => $community_tz1
        ]);
        $a1->save();
        $drivers[] = $a1;

        $an1 = new Admin_Notification([
            'id_admin' => $a1->id_admin,
            'type' => 'sms',
            'value' => $_ENV['DEBUG_PHONE'],
            'active' => true
        ]);
        $an1->save();

        $a2 = new Admin([
            'name' => $name . ' - TWO',
            'login' => null,
            'active' => 1,
            'timezone' => $community_tz1
        ]);
        $a2->save();
        $drivers[] = $a2;

        $an2 = new Admin_Notification([
            'id_admin' => $a2->id_admin,
            'type' => 'sms',
            'value' => $_ENV['DEBUG_PHONE'],
            'active' => true
        ]);
        $an2->save();

        $a3 = new Admin([
            'name' => $name . ' - THREE',
            'login' => null,
            'active' => 1,
            'timezone' => $community_tz1
        ]);
        $a3->save();
        $drivers[] = $a3;

        $an3 = new Admin_Notification([
            'id_admin' => $a3->id_admin,
            'type' => 'sms',
            'value' => $_ENV['DEBUG_PHONE'],
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
            'phone' => $_ENV['DEBUG_PHONE'],
            'address' => '123 main',
            'active' => 1
        ]);
        $u->save();

        $u2 = new User([
            'name' => $name . ' - TWO',
            'phone' => $_ENV['DEBUG_PHONE'],
            'address' => '1157 W 27th St APT 2 - 90007',
            'active' => 1
        ]);
        $u2->save();

        $u3 = new User([
            'name' => $name . ' - THREE',
            'phone' => $_ENV['DEBUG_PHONE'],
            'address' => '500 S Grand Ave Los Angeles CA 90014',
            'active' => 1
        ]);
        $u3->save();

        $u4 = new User([
            'name' => $name . ' - FOUR',
            'phone' => $_ENV['DEBUG_PHONE'],
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
                $n = new Crunchbutton_Notification([
                    'type' => Crunchbutton_Notification::TYPE_ADMIN,
                    'active' => true,
                    'id_restaurant' => $res->id_restaurant,
                    'id_admin' => $dri->id_admin
                ]);
                $n->save();
            }
        }

        $bp = self::defaultOLBP($c->id_community, 7, 0.5, 10, 1, 10);
        $bp->save();
        $bp = self::defaultOLBP($c->id_community, 15, 0.5, 10, 2, 10);
        $bp->save();
//        $bp = self::defaultOLBP($c->id_community, 0, 0, 10, 1, 10);
//        $bp->save();
//        $bp = self::defaultOLBP($c->id_community, 0, 0, 10, 2, 10);
//        $bp->save();
        $bp = self::defaultOLBP($c2->id_community, 0, 1, 5, 1, 15);
        $bp->save();
        $bp = self::defaultOLBP($c2->id_community, 1, 1, 10, 2, 15);
        $bp->save();
//        $param1 = self::defaultOLParam($c->id_community, Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX_ALGO_VERSION,
//            120, 600, 3,
//            10, 4, 3, 5, 10,
//            10, 30);
//        $param1->save();

        $param2 = self::defaultOLParam($c2->id_community, Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX_ALGO_VERSION,
            50, 500, 2,
            8, 3, 2, 10, 5, 7,
            6, 29);
        $param2->save();
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
        User::q('select * from `user` where name=?', [$name . ' - ONE'])->delete();
        User::q('select * from `user` where name=?', [$name . ' - TWO'])->delete();
        User::q('select * from `user` where name=?', [$name . ' - THREE'])->delete();
        User::q('select * from `user` where name=?', [$name . ' - FOUR'])->delete();
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
        $this->restaurant6 = Restaurant::q('select * from restaurant where name=? order by id_restaurant desc limit 1', [$name . ' - SIX'])->get(0);
        $this->driver1 = Admin::q('select * from admin where name=? order by id_admin desc limit 1', [$name . ' - ONE'])->get(0);
        $this->driver2 = Admin::q('select * from admin where name=? order by id_admin desc limit 1', [$name . ' - TWO'])->get(0);
        $this->driver3 = Admin::q('select * from admin where name=? order by id_admin desc limit 1', [$name . ' - THREE'])->get(0);
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

    }


//    public function testMisc()
//    {
//        $cur_community_tz = $this->restaurant5->community()->timezone;
//        $now = new DateTime('now', new DateTimeZone($cur_community_tz));
//        var_dump($now);
//        $test = $now->format("w");
//        print "Day of week $test\n";
//        $useDate1 = $now->format('Y-m-d H:i:s');
//
//        $r1Id = $this->restaurant1->id_restaurant;
//
//        $o1 = $this->defaultOrder($this->user2, $r1Id, $useDate1, $this->community);
//        var_dump($o1->date);
//        $now2 = new DateTime($o1->date, new DateTimeZone($cur_community_tz));
//        var_dump($now2);
//        var_dump($now2->getTimestamp());
//    }
//
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


    public function testOLPTZConversionLosAngeles()
    {
        $refTZ = 'America/Los_Angeles';

        $useDate = '2015-07-01 05:00:00';
        $useDT = new DateTime($useDate, new DateTimeZone($refTZ)); // Should be PST
        $dow = $useDT->format('w');

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = date("H:i:s", strtotime('2015-01-01 06:00:00'));
        $end2 = date("H:i:s", strtotime('2015-01-01 12:00:00'));

        $olp1 = $this->defaultOLP($this->restaurant1, $start, $end, 7, 6, 5, $dow);
        $olp2 = $this->defaultOLP($this->restaurant1, $end, $end2, 10, 10, 10, $dow);
        $olp1->save();
        $olp2->save();
        $newTZ = $this->restaurant1->community()->timezone;
        $useDT->setTimezone(new DateTimeZone($newTZ));
        $parking = $this->restaurant1->parking($useDT->format('H:i:s'), $dow);
        $olp1->delete();
        $olp2->delete();
        $this->assertEquals($parking->parking_duration0, 7);
        $this->assertEquals($parking->parking_duration1, 6);
        $this->assertEquals($parking->parking_duration2, 5);
    }

    public function testOLPTZConversionNewYork()
    {
        $refTZ = 'America/Los_Angeles';
        $useDate = '2015-07-01 05:00:00';
        $useDT = new DateTime($useDate, new DateTimeZone($refTZ)); // Should be PST
//        var_dump($useDT);
        $dow = $useDT->format('w');

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = date("H:i:s", strtotime('2015-01-01 06:00:00'));
        $end2 = date("H:i:s", strtotime('2015-01-01 12:00:00'));

        $olp1 = $this->defaultOLP($this->restaurant5, $start, $end, 15, 15, 15, $dow);
        $olp2 = $this->defaultOLP($this->restaurant5, $end, $end2, 22, 21, 20, $dow);
        $olp1->save();
        $olp2->save();
        $newTZ = $this->restaurant5->community()->timezone;
        $useDT->setTimezone(new DateTimeZone($newTZ));
        $parking = $this->restaurant5->parking($useDT->format('H:i:s'), $dow);
//        var_dump($useDT);
//        print $useDT->format('H:i:s')."\n";
        $olp1->delete();
        $olp2->delete();
        $this->assertEquals($parking->parking_duration0, 22);
        $this->assertEquals($parking->parking_duration1, 21);
        $this->assertEquals($parking->parking_duration2, 20);
    }

    public function testOLSTZConversionLosAngeles()
    {
        $refTZ = 'America/Los_Angeles';
        $useDate = '2015-07-01 05:00:00';
        $useDT = new DateTime($useDate, new DateTimeZone($refTZ)); // Should be PST
        $dow = $useDT->format('w');

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = date("H:i:s", strtotime('2015-01-01 06:00:00'));
        $end2 = date("H:i:s", strtotime('2015-01-01 12:00:00'));

        $ols1 = $this->defaultOLS($this->restaurant1, $start, $end, 7, 6, 5, $dow);
        $ols2 = $this->defaultOLS($this->restaurant1, $end, $end2, 10, 10, 10, $dow);
        $ols1->save();
        $ols2->save();
        $newTZ = $this->restaurant1->community()->timezone;
        $useDT->setTimezone(new DateTimeZone($newTZ));
        $service = $this->restaurant1->service($useDT->format('H:i:s'), $dow);
        $ols1->delete();
        $ols2->delete();
        $this->assertEquals($service->service_duration0, 7);
        $this->assertEquals($service->service_duration1, 6);
        $this->assertEquals($service->service_duration2, 5);
    }

    public function testOLSTZConversionNewYork()
    {
        $refTZ = 'America/Los_Angeles';
        $useDate = '2015-07-01 05:00:00';
        $useDT = new DateTime($useDate, new DateTimeZone($refTZ)); // Should be PST
//        var_dump($useDT);
        $dow = $useDT->format('w');

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = date("H:i:s", strtotime('2015-01-01 06:00:00'));
        $end2 = date("H:i:s", strtotime('2015-01-01 12:00:00'));

        $ols1 = $this->defaultOLS($this->restaurant5, $start, $end, 15, 15, 15, $dow);
        $ols2 = $this->defaultOLS($this->restaurant5, $end, $end2, 22, 21, 20, $dow);
        $ols1->save();
        $ols2->save();
        $newTZ = $this->restaurant5->community()->timezone;
        $useDT->setTimezone(new DateTimeZone($newTZ));
        $service = $this->restaurant5->service($useDT->format('H:i:s'), $dow);
//        var_dump($useDT);
//        print $useDT->format('H:i:s')."\n";
        $ols1->delete();
        $ols2->delete();
        $this->assertEquals($service->service_duration0, 22);
        $this->assertEquals($service->service_duration1, 21);
        $this->assertEquals($service->service_duration2, 20);
    }


    public function testOLOTTZConversionLosAngeles()
    {
        $refTZ = 'America/Los_Angeles';
        $useDate = '2015-07-01 05:00:00';
        $useDT = new DateTime($useDate, new DateTimeZone($refTZ)); // Should be PST
        $dow = $useDT->format('w');

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = date("H:i:s", strtotime('2015-01-01 06:00:00'));
        $end2 = date("H:i:s", strtotime('2015-01-01 12:00:00'));
        $olot1 = $this->defaultOLOT($this->restaurant1, $start, $end, 5, 1, $dow);
        $olot2 = $this->defaultOLOT($this->restaurant1, $end, $end2, 10, 1, $dow);
        $olot1->save();
        $olot2->save();
        $newTZ = $this->restaurant1->community()->timezone;
        $useDT->setTimezone(new DateTimeZone($newTZ));
        $ot = $this->restaurant1->ordertime($useDT->format('H:i:s'), $dow);
        $olot1->delete();
        $olot2->delete();
        $this->assertEquals($ot->order_time, 5);
        $this->assertEquals($ot->scale_factor, 1);
    }

    public function testOLOTTZConversionNewYork()
    {
        $refTZ = 'America/Los_Angeles';
        $useDate = '2015-07-01 05:00:00';
        $useDT = new DateTime($useDate, new DateTimeZone($refTZ)); // Should be PST
        $dow = $useDT->format('w');

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = date("H:i:s", strtotime('2015-01-01 06:00:00'));
        $end2 = date("H:i:s", strtotime('2015-01-01 12:00:00'));
        $olot1 = $this->defaultOLOT($this->restaurant5, $start, $end, 15, 1, $dow);
        $olot2 = $this->defaultOLOT($this->restaurant5, $end, $end2, 20, 1, $dow);
        $olot1->save();
        $olot2->save();
        $newTZ = $this->restaurant5->community()->timezone;
        $useDT->setTimezone(new DateTimeZone($newTZ));
        $ot = $this->restaurant5->ordertime($useDT->format('H:i:s'), $dow);
//        var_dump($useDT);
//        print $useDT->format('H:i:s')."\n";
        $olot1->delete();
        $olot2->delete();
        $this->assertEquals($ot->order_time, 20);
        $this->assertEquals($ot->scale_factor, 1);
    }

    public function testOLCSTZConversionLosAngeles()
    {
        $refTZ = 'America/Los_Angeles';
        $useDate = '2015-07-01 05:00:00';
        $useDT = new DateTime($useDate, new DateTimeZone($refTZ)); // Should be PST
        $dow = $useDT->format('w');

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = date("H:i:s", strtotime('2015-01-01 06:00:00'));
        $end2 = date("H:i:s", strtotime('2015-01-01 12:00:00'));
        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 5, $dow);
        $olcs2 = $this->defaultOLCS($this->community, $end, $end2, 10, $dow);
        $olcs1->save();
        $olcs2->save();
        $newTZ = $this->community->timezone;
        $useDT->setTimezone(new DateTimeZone($newTZ));
        $cs = $this->community->communityspeed($useDT->format('H:i:s'), $dow);
        $olcs1->delete();
        $olcs2->delete();
        $this->assertEquals($cs->mph, 5);
    }

    public function testOLCSTZConversionNewYork()
    {
        $refTZ = 'America/Los_Angeles';
        $useDate = '2015-07-01 05:00:00';
        $useDT = new DateTime($useDate, new DateTimeZone($refTZ)); // Should be PST
        $dow = $useDT->format('w');

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = date("H:i:s", strtotime('2015-01-01 06:00:00'));
        $end2 = date("H:i:s", strtotime('2015-01-01 12:00:00'));
        $olcs1 = $this->defaultOLCS($this->community2, $start, $end, 15, $dow);
        $olcs2 = $this->defaultOLCS($this->community2, $end, $end2, 20, $dow);
        $olcs1->save();
        $olcs2->save();
        $newTZ = $this->community2->timezone;
        $useDT->setTimezone(new DateTimeZone($newTZ));
        $cs = $this->community2->communityspeed($useDT->format('H:i:s'), $dow);
        $olcs1->delete();
        $olcs2->delete();
        $this->assertEquals($cs->mph, 20);
    }


    public function testBadRoute()
    {

        $useDate = '2015-07-01 05:00:00';

        $lat = 34.0303;
        $lon = -118.286;

        $o1 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $useDate, $this->community);
        $o1->save();
        $id_order = $o1->id_order;

        $o2 = $this->defaultOrder($this->user1, $this->restaurant1->id_restaurant, $useDate, $this->community);
        $o2->save();

        $node_id_order = $o2->id_order;

        $olr1 = $this->defaultOLR($o1, $node_id_order, $this->driver1, -99, 0, $useDate, $lat, $lon);
        $olr1->save();
        $route1 = Crunchbutton_Order_Logistics_Route::routesByOrder($o1->id_order);
        $count = $route1->count();

        $olr1->delete();
        $o1->delete();
        $o2->delete();
        $this->assertEquals($count, 1);
        $this->assertEquals($route1->id_admin, $this->driver1->id_admin);
        $this->assertEquals($route1->id_order, $id_order);
        $this->assertEquals($route1->seq, -99);
        $this->assertEquals($route1->node_type, 0);
        $this->assertEquals($route1->leaving_time, $useDate);
        $this->assertEquals($route1->lat, $lat);
        $this->assertEquals($route1->lon, $lon);
        // Technically, for seq 0, there is no node_id_order, but it doesn't matter for this test.
        $this->assertEquals($route1->node_id_order, $node_id_order);
    }

    public function testRoutesByOrder()
    {

        $useDate = '2015-07-01 05:00:00';

        $lat = 34.0303;
        $lon = -118.286;

        $o1 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $useDate, $this->community);
        $o1->save();
        $id_order = $o1->id_order;

        $o2 = $this->defaultOrder($this->user1, $this->restaurant1->id_restaurant, $useDate, $this->community);
        $o2->save();

        $node_id_order = $o2->id_order;

        $olr1 = $this->defaultOLR($o1, $node_id_order, $this->driver1, 0, 0, $useDate, $lat, $lon);
        $olr1->save();
        $route1 = Crunchbutton_Order_Logistics_Route::routesByOrder($o1->id_order);
        $count = $route1->count();

        $olr1->delete();
        $o1->delete();
        $o2->delete();
        $this->assertEquals($count, 1);
        $this->assertEquals($route1->id_admin, $this->driver1->id_admin);
        $this->assertEquals($route1->id_order, $id_order);
        $this->assertEquals($route1->seq, 0);
        $this->assertEquals($route1->node_type, 0);
        $this->assertEquals($route1->leaving_time, $useDate);
        $this->assertEquals($route1->lat, $lat);
        $this->assertEquals($route1->lon, $lon);
        // Technically, for seq 0, there is no node_id_order, but it doesn't matter for this test.
        $this->assertEquals($route1->node_id_order, $node_id_order);
    }

    public function testOLRTZConversionNewYork()
    {
        $refTZ = 'America/Los_Angeles';
        $useDate = '2015-07-01 05:00:00';
        $useDT = new DateTime($useDate, new DateTimeZone($refTZ)); // Should be PST
        $dow = $useDT->format('w');

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = date("H:i:s", strtotime('2015-01-01 06:00:00'));
        $end2 = date("H:i:s", strtotime('2015-01-01 12:00:00'));
        $olcs1 = $this->defaultOLCS($this->community2, $start, $end, 15, $dow);
        $olcs2 = $this->defaultOLCS($this->community2, $end, $end2, 20, $dow);
        $olcs1->save();
        $olcs2->save();
        $newTZ = $this->community2->timezone;
        $useDT->setTimezone(new DateTimeZone($newTZ));
        $cs = $this->community2->communityspeed($useDT->format('H:i:s'), $dow);
        $olcs1->delete();
        $olcs2->delete();
        $this->assertEquals($cs->mph, 20);
    }


    public function testDistance1()
    {
        $lat1 = 33.9873;
        $lon1 = -118.446;
        $lat2 = 33.175101;
        $lon2 = -96.67781;
        $distance = Crunchbutton_GoogleGeocode::latlonDistanceInMiles($lat1, $lon1, $lat2, $lon2);
        $this->assertEquals(round($distance, 3), 1251.923);
    }

    public function testDistance2()
    {
        $lat1 = 34.0986;
        $lon1 = -118.35;
        $lat2 = 33.966309;
        $lon2 = -118.4229655;
        $distance = Crunchbutton_GoogleGeocode::latlonDistanceInMiles($lat1, $lon1, $lat2, $lon2);
        $this->assertEquals(round($distance, 3), 10.050);
    }

    public function testGoogleGeocode1()
    {
        $address = "311 Highland Lake Circle Decatur, GA, 30033";
        $location = Crunchbutton_GoogleGeocode::geocode($address);
        $lat = round($location->lat, 2);
        $lon = round($location->lon, 2);
        $this->assertEquals($lat, 33.80);
        $this->assertEquals($lon, -84.31);
    }

    public function testGoogleGeocode2()
    {
        $address = "1157 W 27th St APT 2 - 90007";
        $location = Crunchbutton_GoogleGeocode::geocode($address);
        $lat = round($location->lat, 2);
        $lon = round($location->lon, 2);
        $this->assertEquals($lat, 34.03);
        $this->assertEquals($lon, -118.29);
    }

    public function testAdminLocationMissing()
    {
        $d1 = $this->driver1;
        $loc = $d1->location();
        $this->assertNull($loc);

    }

    public function testPreorderNotProcessed()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $useDate1 = $now->format('Y-m-d H:i:s');
        $minutes = 240;
        $now->modify('+ ' . $minutes . ' minutes');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $r1Id = $this->restaurant1->id_restaurant;

        $o1 = $this->defaultOrder($this->user2, $r1Id, $useDate1, $this->community);
        $lat = 34.0303;
        $lon = -118.286;
        $o1->lat = $lat;
        $o1->lon = $lon;
        $o1->date = NULL;
        $o1->preordered = 1;
        $o1->date_delivery = $useDate2;
        $o1->preordered_date = $useDate1;
        $o1->save();
        $o2 = $this->defaultOrder($this->user2, $r1Id, $useDate1, $this->community);
        $o2->save();
        $ordersUnfiltered = Order::deliveryOrdersByCommunity(2, $this->community->id_community);
        $numOrders = $ordersUnfiltered->count();
        $this->assertEquals($numOrders, 1);
        $o1->delete();
        $o2->delete();
    }

    public function testPreorderProcessed()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $useDate1 = $now->format('Y-m-d H:i:s');
        $minutes = 240;
        $now->modify('+ ' . $minutes . ' minutes');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $r1Id = $this->restaurant1->id_restaurant;

        $o1 = $this->defaultOrder($this->user2, $r1Id, $useDate1, $this->community);
        $lat = 34.0303;
        $lon = -118.286;
        $o1->lat = $lat;
        $o1->lon = $lon;
        $o1->preordered = 1;
        $o1->date_delivery = $useDate2;
        $o1->preordered_date = $useDate1;
        $o1->save();
        $o2 = $this->defaultOrder($this->user2, $r1Id, $useDate1, $this->community);
        $o2->save();
        $ordersUnfiltered = Order::deliveryOrdersByCommunity(2, $this->community->id_community);
        $numOrders = $ordersUnfiltered->count();
        $this->assertEquals($numOrders, 2);
        $o1->delete();
        $o2->delete();
    }

    public function testOrderGeoMatchAvailable()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $useDate1 = $now->format('Y-m-d H:i:s');

        $r1Id = $this->restaurant1->id_restaurant;

        $o1 = $this->defaultOrder($this->user2, $r1Id, $useDate1, $this->community);
        $lat = 34.0303;
        $lon = -118.286;
        $o1->lat = $lat;
        $o1->lon = $lon;
        $o1->save();
        $o2 = $this->defaultOrder($this->user2, $r1Id, $useDate1, $this->community);
        $o2->save();
        $geo = $o2->findGeoMatchFromDb();
        $o1->delete();
        $o2->delete();
        $this->assertEquals($geo->lat, $lat);
        $this->assertEquals($geo->lon, $lon);
    }

    public function testOrderGeoMatchNotAvailable()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $useDate1 = $now->format('Y-m-d H:i:s');

        $r1Id = $this->restaurant1->id_restaurant;

        $o1 = $this->defaultOrder($this->user2, $r1Id, $useDate1, $this->community2);
        $lat = 34.0303;
        $lon = -118.286;
        $o1->lat = $lat;
        $o1->lon = $lon;
        $o1->save();
        $o2 = $this->defaultOrder($this->user2, $r1Id, $useDate1, $this->community);
        $o2->save();
        $geo = $o2->findGeoMatchFromDb();
        $o1->delete();
        $o2->delete();
        $this->assertNull($geo);
    }

    public function testOrderGeoMatchBadAddressAvailable()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $useDate1 = $now->format('Y-m-d H:i:s');

        $r1Id = $this->restaurant1->id_restaurant;

        $lat = 34.0303;
        $lon = -118.286;
        $o1 = $this->defaultOrder($this->user2, $r1Id, $useDate1, $this->community2);
        $o1->save();

        $use_address = preg_replace('/\s+/', ' ', trim(strtolower($this->user2->address)));
        $ba = $this->defaultOLBA($this->community2, strtolower($use_address), $lat, $lon);
        $ba->save();
        $geo = $o1->findGeoMatchFromBadAddresses();
        $o1->delete();
        $ba->delete();
        $this->assertEquals($geo->lat, $lat);
        $this->assertEquals($geo->lon, $lon);
    }

    public function testOrderGeoMatchBadAddressNotAvailable()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $useDate1 = $now->format('Y-m-d H:i:s');

        $r1Id = $this->restaurant1->id_restaurant;

        $lat = 34.0303;
        $lon = -118.286;
        $o1 = $this->defaultOrder($this->user2, $r1Id, $useDate1, $this->community2);
        $o1->save();

        $use_address = preg_replace('/\s+/', ' ', trim(strtolower($this->user2->address)));
        $ba = $this->defaultOLBA($this->community, strtolower($use_address), $lat, $lon);
        $ba->save();
        $geo = $o1->findGeoMatchFromBadAddresses();
        $o1->delete();
        $ba->delete();
        $this->assertNull($geo);
    }

    public function testOrderGeoSelfAvailable()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $useDate1 = $now->format('Y-m-d H:i:s');

        $r1Id = $this->restaurant1->id_restaurant;

        $o1 = $this->defaultOrder($this->user2, $r1Id, $useDate1, $this->community);
        $lat = 34.0303;
        $lon = -118.286;
        $o1->lat = $lat;
        $o1->lon = $lon;
        $o1->save();
        $geo = $o1->getGeo();
        $o1->delete();
        $this->assertEquals($geo->lat, $lat);
        $this->assertEquals($geo->lon, $lon);
    }

    public function testOrderGeoSameAddressAvailable()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $useDate1 = $now->format('Y-m-d H:i:s');

        $r1Id = $this->restaurant1->id_restaurant;

        $o1 = $this->defaultOrder($this->user2, $r1Id, $useDate1, $this->community);
        $lat = 34.0303;
        $lon = -118.286;
        $o1->lat = $lat;
        $o1->lon = $lon;
        $o1->save();
        $o2 = $this->defaultOrder($this->user2, $r1Id, $useDate1, $this->community);
        $o2->save();
        $geo = $o2->getGeo();
        $o1->delete();
        $o2->delete();
        $this->assertEquals($geo->lat, $lat);
        $this->assertEquals($geo->lon, $lon);
    }

    public function testOrderGeoBadAddressAvailable()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $useDate1 = $now->format('Y-m-d H:i:s');

        $r1Id = $this->restaurant1->id_restaurant;

        $o1 = $this->defaultOrder($this->user2, $r1Id, $useDate1, $this->community);
        $lat = 34.0303;
        $lon = -118.286;
        $lat2 = 35.0;
        $lon2 = -119.5;
        $o1->lat = $lat;
        $o1->lon = $lon;
        $o1->save();
        $o2 = $this->defaultOrder($this->user, $r1Id, $useDate1, $this->community);
        $o2->save();
        $use_address = preg_replace('/\s+/', ' ', trim(strtolower($this->user->address)));
        $ba = $this->defaultOLBA($this->community, strtolower($use_address), $lat2, $lon2);
        $ba->save();
        $geo = $o2->getGeo();
        $o1->delete();
        $o2->delete();
        $ba->delete();
        $this->assertEquals($geo->lat, $lat2);
        $this->assertEquals($geo->lon, $lon2);
    }

    public function testOrderGeoNotTooFar()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $useDate1 = $now->format('Y-m-d H:i:s');

        $r1Id = $this->restaurant1->id_restaurant;

        $o1 = $this->defaultOrder($this->user, $r1Id, $useDate1, $this->community);
        $lat = 35.0;
        $lon = -119.5;
        $lat2 = 34.03;
        $lon2 = -118.29;
        $o1->lat = $lat;
        $o1->lon = $lon;
        $o1->save();
        $o2 = $this->defaultOrder($this->user2, $r1Id, $useDate1, $this->community);
        $o2->save();
        $geo = $o2->getGeo();
        $o1->delete();
        $o2->delete();
        $this->assertEquals(round($geo->lat, 2), $lat2);
        $this->assertEquals(round($geo->lon, 2), $lon2);
    }

    public function testOrderGeoTooFar()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $useDate1 = $now->format('Y-m-d H:i:s');

        $r1Id = $this->restaurant1->id_restaurant;

        $o1 = $this->defaultOrder($this->user, $r1Id, $useDate1, $this->community);
        $lat = 35.0;
        $lon = -119.5;
        $o1->lat = $lat;
        $o1->lon = $lon;
        $o1->save();
        $o2 = $this->defaultOrder($this->user4, $r1Id, $useDate1, $this->community);
        $o2->save();
        $geo = $o2->getGeo();
        $o1->delete();
        $o2->delete();
        $this->assertEquals($geo->lat, $this->community->loc_lat);
        $this->assertEquals($geo->lon, $this->community->loc_lon);
    }


    public function testOrderGoogleGeoAvailable()
    {

        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $useDate1 = $now->format('Y-m-d H:i:s');

        $r1Id = $this->restaurant1->id_restaurant;

        $o1 = $this->defaultOrder($this->user, $r1Id, $useDate1, $this->community2);
        $lat = 34.050;
        $lon = -118.254;
        $lat2 = 35.0;
        $lon2 = -119.5;
        $o1->lat = $lat2;
        $o1->lon = $lon2;
        $o1->save();
        $o2 = $this->defaultOrder($this->user3, $r1Id, $useDate1, $this->community);
        $o2->save();
        $use_address = preg_replace('/\s+/', ' ', trim(strtolower($this->user2->address)));
        $ba = $this->defaultOLBA($this->community2, strtolower($use_address), $lat2, $lon2);
        $ba->save();
        $geo = $o2->getGeo();
        $o1->delete();
        $o2->delete();
        $ba->delete();

        $this->assertEquals(round($geo->lat, 3), $lat);
        $this->assertEquals(round($geo->lon, 3), $lon);
    }


    public function testOrderGeoCommunityGeoAvailable()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $useDate1 = $now->format('Y-m-d H:i:s');

        $r1Id = $this->restaurant1->id_restaurant;

        $o1 = $this->defaultOrder($this->user2, $r1Id, $useDate1, $this->community);
        $lat = 34.0303;
        $lon = -118.286;
        $lat2 = 35.0;
        $lon2 = -119.5;
        $lat3 = $this->community2->loc_lat;
        $lon3 = $this->community2->loc_lon;
        $o1->lat = $lat;
        $o1->lon = $lon;
        $o1->save();
        $o2 = $this->defaultOrder($this->user, $r1Id, $useDate1, $this->community2);
        $o2->save();
        $use_address = preg_replace('/\s+/', ' ', trim(strtolower($this->user->address)));
        $ba = $this->defaultOLBA($this->community, strtolower($use_address), $lat2, $lon2);
        $ba->save();
        $geo = $o2->getGeo();
        $o1->delete();
        $o2->delete();
        $ba->delete();

        $this->assertEquals($geo->lat, $lat3);
        $this->assertEquals($geo->lon, $lon3);
    }

    public function testGetTravelTime1()
    {

        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $useDate1 = $now->format('Y-m-d H:i:s');

        $r1Id = $this->restaurant1->id_restaurant;

        $o1 = $this->defaultOrder($this->user, $r1Id, $useDate1, $this->community2);
        // Downtown LA
        $lat = 34.050;
        $lon = -118.254;
        // Maricopa
        $lat2 = 35.0;
        $lon2 = -119.5;
        $o1->lat = $lat;
        $o1->lon = $lon;
        $o1->save();
        $o2 = $this->defaultOrder($this->user3, $r1Id, $useDate1, $this->community2);
        $o2->lat = $lat2;
        $o2->lon = $lon2;
        $o2->save();
        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o1);
        $tt = $ol->getTravelTime($o2, 10);
        $o1->delete();
        $o2->delete();

        $this->assertEquals(round($tt, 3), 579.827);
    }

    public function testGetTravelTime2()
    {

        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $useDate1 = $now->format('Y-m-d H:i:s');

        $r1Id = $this->restaurant1->id_restaurant;

        $o1 = $this->defaultOrder($this->user, $r1Id, $useDate1, $this->community2);
        // Downtown LA
        $lat = 34.050;
        $lon = -118.254;
        // Maricopa
        $lat2 = 35.0;
        $lon2 = -119.5;
        $o1->lat = $lat;
        $o1->lon = $lon;
        $o1->save();
        $o2 = $this->defaultOrder($this->user3, $r1Id, $useDate1, $this->community2);
        $o2->lat = $lat2;
        $o2->lon = $lon2;
        $o2->save();
        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o1);
        $tt = $ol->getTravelTime($o2, 60);
        $o1->delete();
        $o2->delete();

        $this->assertEquals(round($tt, 3), 96.638);
    }


    public function testGetBundleParams1()
    {

        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $useDate1 = $now->format('Y-m-d H:i:s');

        $r1Id = $this->restaurant1->id_restaurant;

        $o1 = $this->defaultOrder($this->user, $r1Id, $useDate1, $this->community1);
        $o1->save();
        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o1);
        $p0 = $ol->getBundleParams($this->community, 0);
        $p1 = $ol->getBundleParams($this->community, 1);
        $p2 = $ol->getBundleParams($this->community, 2);
        $p3 = $ol->getBundleParams($this->community, 3);
        $p4 = $ol->getBundleParams($this->community, 4);
        $o1->delete();
        $cutoff_at_zero1 = $p1->cutoff_at_zero;
        $slope_per_minute1 = $p1->slope_per_minute;
        $max_minutes1 = $p1->max_minutes;
        $baseline_mph1 = $p1->baseline_mph;
        $cutoff_at_zero2 = $p2->cutoff_at_zero;
        $slope_per_minute2 = $p2->slope_per_minute;
        $max_minutes2 = $p2->max_minutes;
        $baseline_mph2 = $p2->baseline_mph;

        $this->assertNull($p0);
        $this->assertEquals($cutoff_at_zero1, 7);
        $this->assertEquals($slope_per_minute1, 0.5);
        $this->assertEquals($max_minutes1, 10);
        $this->assertEquals($baseline_mph1, 10);
        $this->assertEquals($cutoff_at_zero2, 15);
        $this->assertEquals($slope_per_minute2, 0.5);
        $this->assertEquals($max_minutes2, 10);
        $this->assertEquals($baseline_mph2, 10);
        $this->assertNull($p3);
        $this->assertNull($p4);
    }

    public function testGetBundleParams2()
    {

        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $useDate1 = $now->format('Y-m-d H:i:s');

        $r1Id = $this->restaurant1->id_restaurant;

        $o1 = $this->defaultOrder($this->user, $r1Id, $useDate1, $this->community2);
        $o1->save();
        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o1);
        $p0 = $ol->getBundleParams($this->community2, 0);
        $p1 = $ol->getBundleParams($this->community2, 1);
        $p2 = $ol->getBundleParams($this->community2, 2);
        $p3 = $ol->getBundleParams($this->community2, 3);
        $p4 = $ol->getBundleParams($this->community2, 4);
        $o1->delete();
        $cutoff_at_zero1 = $p1->cutoff_at_zero;
        $slope_per_minute1 = $p1->slope_per_minute;
        $max_minutes1 = $p1->max_minutes;
        $baseline_mph1 = $p1->baseline_mph;
//        $cutoff_at_zero2 = $p2->cutoff_at_zero;
//        $slope_per_minute2 = $p2->slope_per_minute;
//        $max_minutes2 = $p2->max_minutes;
//        $baseline_mph2 = $p2->baseline_mph;

        $this->assertNull($p0);
        $this->assertEquals($cutoff_at_zero1, 0);
        $this->assertEquals($slope_per_minute1, 1);
        $this->assertEquals($max_minutes1, 5);
        $this->assertEquals($baseline_mph1, 15);
//        $this->assertEquals($cutoff_at_zero2, 1);
//        $this->assertEquals($slope_per_minute2, 1);
//        $this->assertEquals($max_minutes2, 10);
//        $this->assertEquals($baseline_mph2, 15);
        $this->assertNull($p2);
        $this->assertNull($p3);
        $this->assertNull($p4);
    }

    public function testGetBundleParams3()
    {

        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $useDate1 = $now->format('Y-m-d H:i:s');

        $r1Id = $this->restaurant1->id_restaurant;

        $o1 = $this->defaultOrder($this->user, $r1Id, $useDate1, $this->community3);
        $o1->save();
        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o1);
        $p0 = $ol->getBundleParams($this->community3, 0);
        $p1 = $ol->getBundleParams($this->community3, 1);
        $p2 = $ol->getBundleParams($this->community3, 2);
        $p3 = $ol->getBundleParams($this->community3, 3);
        $p4 = $ol->getBundleParams($this->community3, 4);
        $o1->delete();

        $this->assertNull($p0);
        $this->assertNull($p1);
        $this->assertNull($p2);
        $this->assertNull($p3);
        $this->assertNull($p4);
    }

    public function testGetParams1()
    {

        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $useDate1 = $now->format('Y-m-d H:i:s');

        $r1Id = $this->restaurant1->id_restaurant;

        $o1 = $this->defaultOrder($this->user, $r1Id, $useDate1, $this->community);
        $o1->save();
        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o1);

        $o1->delete();
        $this->assertEquals($ol->time_max_delay, 120);
        $this->assertEquals($ol->time_bundle, 600);
        $this->assertEquals($ol->max_bundle_size, 3);
        $this->assertEquals($ol->max_bundle_travel_time, 10);
        $this->assertEquals($ol->max_num_orders_delta, 4);
        $this->assertEquals($ol->max_num_unique_restaurants_delta, 3);
        $this->assertEquals($ol->free_driver_bonus, 5);
        $this->assertEquals($ol->order_ahead_correction1, 5);
        $this->assertEquals($ol->order_ahead_correction2, 10);
        $this->assertEquals($ol->order_ahead_correction_limit1, 10);
        $this->assertEquals($ol->order_ahead_correction_limit2, 30);
    }

    public function testGetParams2()
    {

        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $useDate1 = $now->format('Y-m-d H:i:s');

        $r1Id = $this->restaurant1->id_restaurant;

        $o1 = $this->defaultOrder($this->user, $r1Id, $useDate1, $this->community2);
        $o1->save();
        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o1);

        $o1->delete();
        $this->assertEquals($ol->time_max_delay, 50);
        $this->assertEquals($ol->time_bundle, 500);
        $this->assertEquals($ol->max_bundle_size, 2);
        $this->assertEquals($ol->max_bundle_travel_time, 8);
        $this->assertEquals($ol->max_num_orders_delta, 3);
        $this->assertEquals($ol->max_num_unique_restaurants_delta, 2);
        $this->assertEquals($ol->free_driver_bonus, 10);
        $this->assertEquals($ol->order_ahead_correction1, 5);
        $this->assertEquals($ol->order_ahead_correction2, 7);
        $this->assertEquals($ol->order_ahead_correction_limit1, 6);
        $this->assertEquals($ol->order_ahead_correction_limit2, 29);
    }


    public function testCalculateDriverScoreCorrection1a()
    {

        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $useDate1 = $now->format('Y-m-d H:i:s');

        $r1Id = $this->restaurant1->id_restaurant;

        $o1 = $this->defaultOrder($this->user, $r1Id, $useDate1, $this->community1);
        $o1->save();
        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o1);
        $sc0a = $ol->calculateDriverScoreCorrection($this->community, 10, 5, 0, false, false, false, false, false);
        $sc1a = $ol->calculateDriverScoreCorrection($this->community, 10, 5, 1, false, false, false, false, false);
        $sc1b = $ol->calculateDriverScoreCorrection($this->community, 10, 5, 1, true, false, false, false, false);
        $sc1c = $ol->calculateDriverScoreCorrection($this->community, 10, 5, 1, false, true, false, false, false);
        $sc1d = $ol->calculateDriverScoreCorrection($this->community, 10, 5, 1, false, false, true, false, false);
        $sc2a = $ol->calculateDriverScoreCorrection($this->community, 10, 5, 2, false, false, false, false, false);
        $sc3a = $ol->calculateDriverScoreCorrection($this->community, 10, 5, 3, false, false, false, false, false);
        $sc4a = $ol->calculateDriverScoreCorrection($this->community, 10, 5, 4, false, false, false, false, false);
        $o1->delete();
        $this->assertNull($sc0a);
        $this->assertEquals($sc1a, 9.5);
        $this->assertNull($sc1b);
        $this->assertNull($sc1c);
        $this->assertNull($sc1d);
        $this->assertEquals($sc2a, 17.5);
        $this->assertNull($sc3a);
        $this->assertNull($sc4a);
    }

    public function testCalculateDriverScoreCorrection1b()
    {

        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $useDate1 = $now->format('Y-m-d H:i:s');

        $r1Id = $this->restaurant1->id_restaurant;

        $o1 = $this->defaultOrder($this->user, $r1Id, $useDate1, $this->community1);
        $o1->save();
        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o1);
        $sc0a = $ol->calculateDriverScoreCorrection($this->community, 15, 5, 0, false, false, false, false, false);
        $sc1a = $ol->calculateDriverScoreCorrection($this->community, 15, 5, 1, false, false, false, false, false);
        $sc1b = $ol->calculateDriverScoreCorrection($this->community, 15, 5, 1, true, false, false, false, false);
        $sc1c = $ol->calculateDriverScoreCorrection($this->community, 15, 5, 1, false, true, false, false, false);
        $sc1d = $ol->calculateDriverScoreCorrection($this->community, 15, 5, 1, false, false, true, false, false);
        $sc2a = $ol->calculateDriverScoreCorrection($this->community, 15, 5, 2, false, false, false, false, false);
        $sc3a = $ol->calculateDriverScoreCorrection($this->community, 15, 5, 3, false, false, false, false, false);
        $sc4a = $ol->calculateDriverScoreCorrection($this->community, 15, 5, 4, false, false, false, false, false);
        $o1->delete();
        $this->assertNull($sc0a);
        $this->assertEquals(round($sc1a,2), 6.33);
        $this->assertNull($sc1b);
        $this->assertNull($sc1c);
        $this->assertNull($sc1d);
        $this->assertEquals(round($sc2a, 2), 11.67);
        $this->assertNull($sc3a);
        $this->assertNull($sc4a);
    }


    public function testCalculateDriverScoreCorrection1c()
    {

        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $useDate1 = $now->format('Y-m-d H:i:s');

        $r1Id = $this->restaurant1->id_restaurant;

        $o1 = $this->defaultOrder($this->user, $r1Id, $useDate1, $this->community1);
        $o1->save();
        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o1);
        $sc0a = $ol->calculateDriverScoreCorrection($this->community, 15, 5, 0, false, false, false, true, true);
        $sc1a = $ol->calculateDriverScoreCorrection($this->community, 15, 5, 1, false, false, false, true, true);
        $sc1b = $ol->calculateDriverScoreCorrection($this->community, 15, 5, 1, true, false, false, true, true);
        $sc1c = $ol->calculateDriverScoreCorrection($this->community, 15, 5, 1, false, true, false, true, true);
        $sc1d = $ol->calculateDriverScoreCorrection($this->community, 15, 5, 1, false, false, true, true, true);
        $sc2a = $ol->calculateDriverScoreCorrection($this->community, 15, 5, 2, false, false, false, true, true);
        $sc3a = $ol->calculateDriverScoreCorrection($this->community, 15, 5, 3, false, false, false, true, true);
        $sc4a = $ol->calculateDriverScoreCorrection($this->community, 15, 5, 4, false, false, false, true, true);
        $sc4b = $ol->calculateDriverScoreCorrection($this->community, 15, 45, 1, false, false, false, false, false);
        $sc4c = $ol->calculateDriverScoreCorrection($this->community, 15, 45, 1, false, false, false, true, false);
        $sc4d = $ol->calculateDriverScoreCorrection($this->community, 15, 45, 1, false, false, false, true, true);
        $o1->delete();
        $this->assertNull($sc0a);
        $this->assertEquals(round($sc1a,2), 6.33);
        $this->assertNull($sc1b);
        $this->assertNull($sc1c);
        $this->assertNull($sc1d);
        $this->assertEquals(round($sc2a, 2), 11.67);
        $this->assertNull($sc3a);
        $this->assertNull($sc4a);
        $this->assertNull($sc4b);
        $this->assertNull($sc4c);
        $this->assertEquals(round($sc4d,2), 8.0);
    }

    public function testCalculateDriverScoreCorrection2()
    {

        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $useDate1 = $now->format('Y-m-d H:i:s');

        $r1Id = $this->restaurant1->id_restaurant;

        $o1 = $this->defaultOrder($this->user, $r1Id, $useDate1, $this->community2);
        $o1->save();
        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o1);
        $sc0a = $ol->calculateDriverScoreCorrection($this->community2, 10, 5, 0, false, false, false, false, false);
        $sc1a = $ol->calculateDriverScoreCorrection($this->community2, 10, 5, 1, false, false, false, false, false);
        $sc1b = $ol->calculateDriverScoreCorrection($this->community2, 10, 5, 1, true, false, false, false, false);
        $sc1c = $ol->calculateDriverScoreCorrection($this->community2, 10, 5, 1, false, true, false, false, false);
        $sc1d = $ol->calculateDriverScoreCorrection($this->community2, 10, 5, 1, false, false, true, false, false);
        $sc2a = $ol->calculateDriverScoreCorrection($this->community2, 10, 5, 2, false, false, false, false, false);
        $sc3a = $ol->calculateDriverScoreCorrection($this->community2, 10, 5, 3, false, false, false, false, false);
        $sc4a = $ol->calculateDriverScoreCorrection($this->community2, 10, 5, 4, false, false, false, false, false);
        $o1->delete();
        $this->assertNull($sc0a);
        $this->assertEquals($sc1a, 7.5);
        $this->assertNull($sc1b);
        $this->assertNull($sc1c);
        $this->assertNull($sc1d);
        $this->assertNull($sc1d);
        $this->assertNull($sc3a);
        $this->assertNull($sc4a);
    }

    public function testCalculateDriverScoreCorrection3()
    {

        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $useDate1 = $now->format('Y-m-d H:i:s');

        $r1Id = $this->restaurant1->id_restaurant;

        $o1 = $this->defaultOrder($this->user, $r1Id, $useDate1, $this->community3);
        $o1->save();
        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o1);
        $sc0a = $ol->calculateDriverScoreCorrection($this->community3, 10, 5, 0, false, false, false, false, false);
        $sc1a = $ol->calculateDriverScoreCorrection($this->community3, 10, 5, 1, false, false, false, false, false);
        $sc1b = $ol->calculateDriverScoreCorrection($this->community3, 10, 5, 1, true, false, false, false, false);
        $sc1c = $ol->calculateDriverScoreCorrection($this->community3, 10, 5, 1, false, true, false, false, false);
        $sc1d = $ol->calculateDriverScoreCorrection($this->community3, 10, 5, 1, false, false, true, false, false);
        $sc2a = $ol->calculateDriverScoreCorrection($this->community3, 10, 5, 2, false, false, false, false, false);
        $sc3a = $ol->calculateDriverScoreCorrection($this->community3, 10, 5, 3, false, false, false, false, false);
        $sc4a = $ol->calculateDriverScoreCorrection($this->community3, 10, 5, 4, false, false, false, false, false);
        $o1->delete();
        $this->assertNull($sc0a);
        $this->assertEquals($sc1a, 7.5);
        $this->assertNull($sc1b);
        $this->assertNull($sc1c);
        $this->assertNull($sc1d);
        $this->assertEquals($sc2a, 7.5);
        $this->assertNull($sc3a);
        $this->assertNull($sc4a);
    }

    public function testAdminScore()
    {
        $useScore = 2.0;
        $useExperience = 3;
        $s = $this->defaultScore($this->driver1, $useScore, $useExperience);
        $s->save();
        $score = $this->driver1->score();
        $sc = $score->score;
        $experience = $score->experience;
        $s->delete();
        $this->assertEquals($sc, $useScore);
        $this->assertEquals($experience, $useExperience);
    }

    public function testAdminDefaultScore()
    {
        $useScore = 55.0;
        $s = $this->defaultScore($this->driver2, $useScore);
        $s->save();
        $score = $this->driver1->score();
        $sc = $score->score;
        $experience = $score->experience;
        $s->delete();
        $this->assertEquals($sc, Cockpit_Admin_Score::DEFAULT_SCORE);
        $this->assertEquals($experience, Cockpit_Admin_Score::DEFAULT_EXPERIENCE);
    }

    public function testAdminDefaultScore2()
    {
        $score = $this->driver1->score();
        $qString = "SELECT * FROM `admin_score` WHERE id_admin= ? ";
        $s = Cockpit_Admin_Score::q($qString, [$this->driver1->id_admin]);
        $s->delete();
        $this->assertEquals($s->count(), 1);
        $sc = $score->score;
        $experience = $score->experience;
        $this->assertEquals($sc, Cockpit_Admin_Score::DEFAULT_SCORE);
        $this->assertEquals($experience, Cockpit_Admin_Score::DEFAULT_EXPERIENCE);
    }

    public function testRestaurantClusterExist()
    {
        c::dbWrite()->query('delete from order_logistics_cluster where id_restaurant = ?',
            [$this->restaurant1->id_restaurant]);
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $useDate1 = $now->format('Y-m-d H:i:s');
        $chkTime = '00:03:00';

        $fakeClusterId = 999999;
        $olc = $this->defaultOLC($this->restaurant1, 0, '00:00:00', '01:00:00', $fakeClusterId);
        $olc->save();
        $clChk = Crunchbutton_Order_Logistics_Cluster::q('select * from order_logistics_cluster where id_restaurant= ? and id_restaurant_cluster = ?',
            [$this->restaurant1->id_restaurant, $fakeClusterId]);
        $countChk = $clChk->count();

        $cluster = $this->restaurant1->cluster($chkTime, 0);
        $olc->delete();
        $this->assertEquals($countChk, 1);
        $this->assertEquals($cluster->id_restaurant_cluster, $fakeClusterId);
    }


    public function testRestaurantClusterWrongTime()
    {
        c::dbWrite()->query('delete from order_logistics_cluster where id_restaurant = ?',
            [$this->restaurant1->id_restaurant]);
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $useDate1 = $now->format('Y-m-d H:i:s');

        $fakeClusterId = 999999;
        $olc = $this->defaultOLC($this->restaurant1, 0, '00:00:00', '01:00:00', $fakeClusterId);
        $olc->save();
        $clChk = Crunchbutton_Order_Logistics_Cluster::q('select * from order_logistics_cluster where id_restaurant= ? and id_restaurant_cluster = ?',
            [$this->restaurant1->id_restaurant, $fakeClusterId]);
        $countChk = $clChk->count();
        $cluster = $this->restaurant1->cluster('02:00:00', 0);
        $olc->delete();
        $clChk2 = Crunchbutton_Order_Logistics_Cluster::q('select * from order_logistics_cluster where id_restaurant= ? and id_restaurant_cluster = ?',
            [$this->restaurant1->id_restaurant, $fakeClusterId]);
        $countChk2 = $clChk2->count();
        $cluster = $this->restaurant1->cluster('02:00:00', 0);
        $cl = Crunchbutton_Order_Logistics_Cluster::q('select * from order_logistics_cluster where id_restaurant= ?',
            [$this->restaurant1->id_restaurant]);
        $count = $cl->count();
        $cl->delete();
        $this->assertEquals($countChk, 1);
        $this->assertEquals($countChk2, 0);
        $this->assertEquals($cluster->id_restaurant_cluster, $this->restaurant1->id_restaurant);
        $this->assertEquals($count, 1);
    }

    public function testDestinationListCount()
    {
        $d1 = $this->defaultDriverDestination(10);
        $d2 = $this->defaultRestaurantDestination(11);
        $d3 = $this->defaultCustomerDestination(12);

        $dl = new Crunchbutton_Order_Logistics_DestinationList(Crunchbutton_Optimizer_Input::DISTANCE_LATLON);
        $dl->addDriverDestination($d1);
        $dl->addDestinationPair($d2, $d3, true);
        $count = $dl->count();
        $clusterCount = count($dl->parking_clusters);
        $idMapCount = count($dl->id_map);
        $this->assertEquals($count, 3);
    }

    public function testNumDriverOrderActionsSinceSameAdminZeroActions()
    {

        $minutes = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $minDtString = $now->format('Y-m-d H:i:s');

        $numActions = Crunchbutton_Order_Priority::getNumDriverOrderActionsSince($minDtString, $this->driver1->id_admin);
        $this->assertEquals($numActions, 0);
    }

    public function testNumDriverOrderActionsSinceSameAdminOneActionOutside()
    {

        $minutes = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $minDtString = $now->format('Y-m-d H:i:s');

        $minutes = 240;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $o1 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();

        $numActions = Crunchbutton_Order_Priority::getNumDriverOrderActionsSince($minDtString, $this->driver1->id_admin);
        $oa1->delete();
        $o1->delete();
        $this->assertEquals($numActions, 0);
    }

    public function testNumDriverOrderActionsSinceSameAdminOneActionInside()
    {

        $minutes = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $minDtString = $now->format('Y-m-d H:i:s');

        $minutes = 60;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $o1 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();

        $numActions = Crunchbutton_Order_Priority::getNumDriverOrderActionsSince($minDtString, $this->driver1->id_admin);
        $oa1->delete();
        $o1->delete();
        $this->assertEquals($numActions, 1);
    }

    public function testNumDriverOrderActionsSinceDiffAdminOneActionInside()
    {

        $minutes = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $minDtString = $now->format('Y-m-d H:i:s');

        $minutes = 60;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $o1 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();

        $numActions = Crunchbutton_Order_Priority::getNumDriverOrderActionsSince($minDtString, $this->driver1->id_admin);
        $oa1->delete();
        $o1->delete();
        $this->assertEquals($numActions, 0);
    }

    public function testNumDriverOrderActionsSinceDiffAdminTwoActionsInside()
    {

        $minutes = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $minDtString = $now->format('Y-m-d H:i:s');

        $minutes = 60;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $minutes = 240;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate2 = $now->format('Y-m-d H:i:s');

        $o1 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $useDate2, $this->community);
        $o1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $o2 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o2->save();

        $oa2 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-pickedup',
            'note' => ''
        ]);
        $oa2->save();
        $this->associateOrderActionToOrder($o2, $oa2);

        $o3 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o3->save();

        $oa3 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-delivered',
            'note' => ''
        ]);
        $oa3->save();
        $this->associateOrderActionToOrder($o3, $oa3);

        $numActions = Crunchbutton_Order_Priority::getNumDriverOrderActionsSince($minDtString, $this->driver1->id_admin);
        $oa1->delete();
        $o1->delete();
        $oa2->delete();
        $o2->delete();
        $oa3->delete();
        $o3->delete();
        $this->assertEquals($numActions, 2);
    }

    public function testNumDriverOrderActionsSinceDiffAdminThreeActionsInside()
    {

        $minutes = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $minDtString = $now->format('Y-m-d H:i:s');

        $minutes = 60;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $o1 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $o2 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o2->save();

        $oa2 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-pickedup',
            'note' => ''
        ]);
        $oa2->save();
        $this->associateOrderActionToOrder($o2, $oa2);

        $o3 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o3->save();

        $oa3 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-delivered',
            'note' => ''
        ]);
        $oa3->save();
        $this->associateOrderActionToOrder($o3, $oa3);

        $numActions = Crunchbutton_Order_Priority::getNumDriverOrderActionsSince($minDtString, $this->driver1->id_admin);
        $oa1->delete();
        $o1->delete();
        $oa2->delete();
        $o2->delete();
        $oa3->delete();
        $o3->delete();
        $this->assertEquals($numActions, 3);
    }

    public function testNumDriverOrderActionsSinceDiffAdminThreeActionsInsideTwoUnqualifying1()
    {

        $minutes = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $minDtString = $now->format('Y-m-d H:i:s');

        $minutes = 60;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $o1 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-rejected',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $o2 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o2->save();

        $oa2 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-transfered',
            'note' => ''
        ]);
        $oa2->save();
        $this->associateOrderActionToOrder($o2, $oa2);

        $o3 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o3->save();

        $oa3 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-delivered',
            'note' => ''
        ]);
        $oa3->save();
        $this->associateOrderActionToOrder($o3, $oa3);

        $numActions = Crunchbutton_Order_Priority::getNumDriverOrderActionsSince($minDtString, $this->driver1->id_admin);
        $oa1->delete();
        $o1->delete();
        $oa2->delete();
        $o2->delete();
        $oa3->delete();
        $o3->delete();
        $this->assertEquals($numActions, 1);
    }

    public function testNumDriverOrderActionsSinceDiffAdminThreeActionsInsideTwoUnqualifying2()
    {

        $minutes = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $minDtString = $now->format('Y-m-d H:i:s');

        $minutes = 60;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $o1 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-canceled',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $o2 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o2->save();

        $oa2 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-text-5min',
            'note' => ''
        ]);
        $oa2->save();
        $this->associateOrderActionToOrder($o2, $oa2);

        $o3 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o3->save();

        $oa3 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-delivered',
            'note' => ''
        ]);
        $oa3->save();
        $this->associateOrderActionToOrder($o3, $oa3);

        $numActions = Crunchbutton_Order_Priority::getNumDriverOrderActionsSince($minDtString, $this->driver1->id_admin);
        $oa1->delete();
        $o1->delete();
        $oa2->delete();
        $o2->delete();
        $oa3->delete();
        $o3->delete();
        $this->assertEquals($numActions, 1);
    }

    public function testLastZeroPriorityWithZeroSavedSameAdmin()
    {
        $ops = Crunchbutton_Order_Priority::lastNExpiredSpecialPriorityOrders($minDtString, $this->driver1->id_admin, 0);
        $opsCount = $ops->count();
        $this->assertEquals($opsCount, 0);
    }

    public function testLastTwoPriorityWithZeroSavedSameAdmin()
    {
        $ops = Crunchbutton_Order_Priority::lastNExpiredSpecialPriorityOrders($minDtString, $this->driver1->id_admin, 2);
        $opsCount = $ops->count();
        $this->assertEquals($opsCount, 0);
    }

    public function testLastZeroPriorityWithOneSavedSameAdmin()
    {

        $minutes = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $minDtString = $now->format('Y-m-d H:i:s');

        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('+ ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');

        $o1 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o1->save();
        $op1 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver1,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_HIGH, 0, $useDate2, 0, 1);
        $op1->save();
        $ops = Crunchbutton_Order_Priority::lastNExpiredSpecialPriorityOrders($minDtString, $this->driver1->id_admin, 0);
        $opsCount = $ops->count();
        $op1->delete();
        $o1->delete();
        $this->assertEquals($opsCount, 0);
    }

    public function testLastTwoPriorityWithOneUnexpiredUnacceptedSavedSameAdmin()
    {
        $minutes = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $minDtString = $now->format('Y-m-d H:i:s');

        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('+ ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');

        $o1 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o1->save();
        $op1 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver1,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_HIGH, 0, $useDate2, 0, 1);
        $op1->save();
        $ops = Crunchbutton_Order_Priority::lastNExpiredSpecialPriorityOrders($minDtString, $this->driver1->id_admin, 2);
        $opsCount = $ops->count();
        $op1->delete();
        $o1->delete();
        $this->assertEquals($opsCount, 0);
    }


    public function testLastTwoPriorityWithOneUnexpiredAcceptedSavedSameAdmin()
    {
        $minutes = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $minDtString = $now->format('Y-m-d H:i:s');

        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('+ ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');

        $o1 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $op1 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver1,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_HIGH, 0, $useDate2, 0, 1);
        $op1->save();
        $ops = Crunchbutton_Order_Priority::lastNExpiredSpecialPriorityOrders($minDtString, $this->driver1->id_admin, 2);
        $opsCount = $ops->count();
        $op1->delete();
        $oa1->delete();
        $o1->delete();
        $this->assertEquals($opsCount, 0);
    }

    public function testLastTwoPriorityWithOneExpiredUnacceptedSavedSameAdmin()
    {
        $minutes = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $minDtString = $now->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 60;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');

        $o1 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o1->save();
        $op1 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver1,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_HIGH, 0, $useDate2, 0, 1);
        $op1->save();
        $ops = Crunchbutton_Order_Priority::lastNExpiredSpecialPriorityOrders($minDtString, $this->driver1->id_admin, 2);
        $opsCount = $ops->count();
        $op1->delete();
        $o1->delete();
        $this->assertEquals($opsCount, 1);
    }

    public function testLastTwoPriorityWithOneExpiredAcceptedSavedSameAdmin()
    {
        $minutes = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $minDtString = $now->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 60;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');

        $o1 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $op1 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver1,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_HIGH, 0, $useDate2, 0, 1);
        $op1->save();
        $ops = Crunchbutton_Order_Priority::lastNExpiredSpecialPriorityOrders($minDtString, $this->driver1->id_admin, 2);
        $opsCount = $ops->count();
        $op1->delete();
        $oa1->delete();
        $o1->delete();
        $this->assertEquals($opsCount, 0);
    }

    public function testLastTwoPriorityWithOneExpiredAccepted2SavedSameAdmin()
    {
        $minutes = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $minDtString = $now->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 60;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');

        $o1 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $op1 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver1,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_HIGH, 0, $useDate2, 0, 1);
        $op1->save();
        $ops = Crunchbutton_Order_Priority::lastNExpiredSpecialPriorityOrders($minDtString, $this->driver1->id_admin, 2);
        $opsCount = $ops->count();
        $op1->delete();
        $oa1->delete();
        $o1->delete();
        $this->assertEquals($opsCount, 1);
    }


    public function testLastTwoPriorityWithOneExpiredSavedSameAdminSpecial()
    {
        $minutes = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $minDtString = $now->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 60;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');

        $o1 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o1->save();
        $op1 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver1,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_HIGH, 0, $useDate2, 0, 1);
        $op1->save();
        $ops = Crunchbutton_Order_Priority::lastNExpiredSpecialPriorityOrders($minDtString, $this->driver1->id_admin, 2);
        $opsCount = $ops->count();
        $op1->delete();
        $o1->delete();
        $this->assertEquals($opsCount, 1);
    }

    public function testLastTwoPriorityWithOneExpiredSavedSameAdminNotSpecial1()
    {
        $minutes = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $minDtString = $now->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 60;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');

        $o1 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o1->save();
        $op1 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver1,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW, 0, $useDate2, 0, 1);
        $op1->save();
        $ops = Crunchbutton_Order_Priority::lastNExpiredSpecialPriorityOrders($minDtString, $this->driver1->id_admin, 2);
        $opsCount = $ops->count();
        $op1->delete();
        $o1->delete();
        $this->assertEquals($opsCount, 0);
    }

    public function testLastTwoPriorityWithOneExpiredSavedSameAdminNotSpecial2()
    {
        $minutes = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $minDtString = $now->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 60;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');

        $o1 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o1->save();
        $op1 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver1,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_HIGH, 0, $useDate2, 0, 2);
        $op1->save();
        $ops = Crunchbutton_Order_Priority::lastNExpiredSpecialPriorityOrders($minDtString, $this->driver1->id_admin, 2);
        $opsCount = $ops->count();
        $op1->delete();
        $o1->delete();
        $this->assertEquals($opsCount, 0);
    }

    public function testLastTwoPriorityWithOneExpiredSavedSameAdminNotSpecial3()
    {
        $minutes = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $minDtString = $now->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 60;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');

        $o1 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o1->save();
        $op1 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver1,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_HIGH, 0, $useDate2, 1, 1);
        $op1->save();
        $ops = Crunchbutton_Order_Priority::lastNExpiredSpecialPriorityOrders($minDtString, $this->driver1->id_admin, 2);
        $opsCount = $ops->count();
        $op1->delete();
        $o1->delete();
        $this->assertEquals($opsCount, 0);
    }

    public function testLastTwoPriorityWithOneExpiredSavedSameAdminNotSpecial4()
    {
        $minutes = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $minDtString = $now->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 60;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');

        $o1 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o1->save();
        $op1 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver1,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_HIGH, 0, $useDate2, 0, 1, Crunchbutton_Order_Logistics::LOGISTICS_SIMPLE_ALGO_VERSION);
        $op1->save();
        $ops = Crunchbutton_Order_Priority::lastNExpiredSpecialPriorityOrders($minDtString, $this->driver1->id_admin, 2);
        $opsCount = $ops->count();
        $op1->delete();
        $o1->delete();
        $this->assertEquals($opsCount, 0);
    }


    public function testLastZeroPriorityWithTwoSavedSameAdmin()
    {
        $minutes = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $minDtString = $now->format('Y-m-d H:i:s');

        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('+ ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');

        $o1 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o1->save();
        $op1 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver1,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_HIGH, 0, $useDate2, 0, 1);
        $op1->save();
        $o2 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o2->save();
        $op2 = $this->defaultOrderPriority($o2, $this->restaurant1, $this->driver1,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_HIGH, 0, $useDate2, 0, 1);
        $op2->save();

        $ops = Crunchbutton_Order_Priority::lastNExpiredSpecialPriorityOrders($minDtString, $this->driver1->id_admin, 0);
        $opsCount = $ops->count();
        $op1->delete();
        $op2->delete();
        $o1->delete();
        $o2->delete();
        $this->assertEquals($opsCount, 0);
    }

    public function testLastTwoPriorityWithTwoUnexpiredSavedSameAdmin()
    {

        $minutes = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $minDtString = $now->format('Y-m-d H:i:s');

        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('+ ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');

        $seconds = 60;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('+ ' . $seconds . ' seconds');
        $useDate3 = $now->format('Y-m-d H:i:s');

        $o1 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o1->save();
        $op1 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver1,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_HIGH, 0, $useDate2, 0, 1);
        $op1->save();
        $o2 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o2->save();
        $op2 = $this->defaultOrderPriority($o2, $this->restaurant1, $this->driver1,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_HIGH, 0, $useDate2, 0, 1);
        $op2->save();

        $ops = Crunchbutton_Order_Priority::lastNExpiredSpecialPriorityOrders($minDtString, $this->driver1->id_admin, 2);
        $opsCount = $ops->count();
        $op1->delete();
        $op2->delete();
        $o1->delete();
        $o2->delete();
        $this->assertEquals($opsCount, 0);
    }

    public function testLastTwoPriorityWithTwoExpiredSavedSameAdmin1()
    {
        $minutes = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $minDtString = $now->format('Y-m-d H:i:s');

        $seconds = 240;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');

        $seconds = 60;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate3 = $now->format('Y-m-d H:i:s');

        $o1 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o1->save();
        $op1 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver1,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_HIGH, 0, $useDate2, 0, 1);
        $op1->save();
        $o2 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o2->save();
        $op2 = $this->defaultOrderPriority($o2, $this->restaurant1, $this->driver1,
            $useDate2, Crunchbutton_Order_Priority::PRIORITY_HIGH, 0, $useDate3, 0, 1);
        $op2->save();

        $ops = Crunchbutton_Order_Priority::lastNExpiredSpecialPriorityOrders($minDtString, $this->driver1->id_admin, 2);
        $opsCount = $ops->count();
        $op1->delete();
        $op2->delete();
        $o1->delete();
        $o2->delete();
        $this->assertEquals($opsCount, 2);
        $this->assertEquals($op1->id_order_priority, $ops->get(1)->id_order_priority);
        $this->assertEquals($op2->id_order_priority, $ops->get(0)->id_order_priority);
    }


    public function testLastTwoPriorityWithTwoExpiredSavedSameAdmin2()
    {
        $minutes = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $minDtString = $now->format('Y-m-d H:i:s');

        $seconds = 240;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');

        $seconds = 60;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate3 = $now->format('Y-m-d H:i:s');

        $o1 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o1->save();
        $op1 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver1,
            $useDate2, Crunchbutton_Order_Priority::PRIORITY_HIGH, 0, $useDate3, 0, 1);
        $op1->save();
        $o2 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o2->save();
        $op2 = $this->defaultOrderPriority($o2, $this->restaurant1, $this->driver1,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_HIGH, 0, $useDate2, 0, 1);
        $op2->save();

        $ops = Crunchbutton_Order_Priority::lastNExpiredSpecialPriorityOrders($minDtString, $this->driver1->id_admin, 2);
        $opsCount = $ops->count();
        $op1->delete();
        $op2->delete();
        $o1->delete();
        $o2->delete();
        $this->assertEquals($opsCount, 2);
        $this->assertEquals($op1->id_order_priority, $ops->get(0)->id_order_priority);
        $this->assertEquals($op2->id_order_priority, $ops->get(1)->id_order_priority);
    }


    public function testLastOnePriorityWithThreeExpiredSavedSameAdmin2()
    {
        $minutes = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $minDtString = $now->format('Y-m-d H:i:s');

        $seconds = 240;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');

        $seconds = 30;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate3 = $now->format('Y-m-d H:i:s');

        $seconds = 60;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate4 = $now->format('Y-m-d H:i:s');

        $o1 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o1->save();
        $op1 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver1,
            $useDate2, Crunchbutton_Order_Priority::PRIORITY_HIGH, 0, $useDate3, 0, 1);
        $op1->save();
        $o2 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o2->save();
        $op2 = $this->defaultOrderPriority($o2, $this->restaurant1, $this->driver1,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_HIGH, 0, $useDate2, 0, 1);
        $op2->save();
        $o3 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o3->save();
        $op3 = $this->defaultOrderPriority($o3, $this->restaurant1, $this->driver1,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_HIGH, 0, $useDate4, 0, 1);
        $op3->save();

        $ops = Crunchbutton_Order_Priority::lastNExpiredSpecialPriorityOrders($minDtString, $this->driver1->id_admin, 1);
        $opsCount = $ops->count();
        $op1->delete();
        $op2->delete();
        $op3->delete();
        $o1->delete();
        $o2->delete();
        $o3->delete();
        $this->assertEquals($opsCount, 1);
        $this->assertEquals($op1->id_order_priority, $ops->get(0)->id_order_priority);
    }


    public function testLastTwoPriorityWithThreeExpiredSavedSameAdmin2()
    {
        $minutes = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $minDtString = $now->format('Y-m-d H:i:s');

        $seconds = 240;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');

        $seconds = 30;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate3 = $now->format('Y-m-d H:i:s');

        $seconds = 60;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate4 = $now->format('Y-m-d H:i:s');

        $o1 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o1->save();
        $op1 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver1,
            $useDate2, Crunchbutton_Order_Priority::PRIORITY_HIGH, 0, $useDate3, 0, 1);
        $op1->save();
        $o2 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o2->save();
        $op2 = $this->defaultOrderPriority($o2, $this->restaurant1, $this->driver1,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_HIGH, 0, $useDate2, 0, 1);
        $op2->save();
        $o3 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o3->save();
        $op3 = $this->defaultOrderPriority($o3, $this->restaurant1, $this->driver1,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_HIGH, 0, $useDate4, 0, 1);
        $op3->save();

        $ops = Crunchbutton_Order_Priority::lastNExpiredSpecialPriorityOrders($minDtString, $this->driver1->id_admin, 2);
        $opsCount = $ops->count();
        $op1->delete();
        $op2->delete();
        $op3->delete();
        $o1->delete();
        $o2->delete();
        $o3->delete();
        $this->assertEquals($opsCount, 2);
        $this->assertEquals($op1->id_order_priority, $ops->get(0)->id_order_priority);
        $this->assertEquals($op3->id_order_priority, $ops->get(1)->id_order_priority);
    }



    public function testLastTwoPriorityWithTwoSavedDiffAdmin1()
    {
        $minutes = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $minDtString = $now->format('Y-m-d H:i:s');

        $seconds = 240;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');

        $o1 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o1->save();
        $op1 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver2,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_HIGH, 0, $useDate2, 0, 1);
        $op1->save();
        $o2 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o2->save();
        $op2 = $this->defaultOrderPriority($o2, $this->restaurant1, $this->driver1,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_HIGH, 0, $useDate2, 0, 1);
        $op2->save();

        $ops = Crunchbutton_Order_Priority::lastNExpiredSpecialPriorityOrders($minDtString, $this->driver1->id_admin, 2);
        $opsCount = $ops->count();
        $op1->delete();
        $op2->delete();
        $o1->delete();
        $o2->delete();
        $this->assertEquals($opsCount, 1);
    }

    public function testLastTwoPriorityWithTwoSavedDiffAdmin2()
    {
        $minutes = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $minDtString = $now->format('Y-m-d H:i:s');

        $seconds = 240;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');

        $o1 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o1->save();
        $op1 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver2,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_HIGH, 0, $useDate2, 0, 1);
        $op1->save();
        $o2 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o2->save();
        $op2 = $this->defaultOrderPriority($o2, $this->restaurant1, $this->driver2,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_HIGH, 0, $useDate2, 0, 1);
        $op2->save();

        $ops = Crunchbutton_Order_Priority::lastNExpiredSpecialPriorityOrders($minDtString, $this->driver1->id_admin, 2);
        $opsCount = $ops->count();
        $op1->delete();
        $op2->delete();
        $o1->delete();
        $o2->delete();
        $this->assertEquals($opsCount, 0);
    }

    public function testOptimizerAPI1()
    {
        //opt.OptInput(nNodes, 1, 1.0, 5, 5, 120, 240, 5000)
        $i = new Crunchbutton_Optimizer_Input();
        $i->numNodes = 9;
        $i->driverMph = 1;
        $i->penaltyCoefficient = 1.0;
        $i->customerDropoffTime = 5;
        $i->restaurantPickupTime = 5;
        $i->slackMaxTime = 120;
        $i->horizon = 240;
        $i->maxRunTime = 5000;
        $i->distanceType = Crunchbutton_Optimizer_Input::DISTANCE_XY;
        $i->firstCoords = [40, 45, 45, 35, 40, 45, 40, 40, 40];
        $i->secondCoords = [50, 68, 70, 69, 69, 55, 69, 55, 69];
        $i->nodeTypes = [1, 3, 3, 2, 2, 3, 2, 3, 2];
        $i->orderTimes = [0, 50, 20, 50, 20, 15, 15, 60, 60];
        $i->earlyWindows = [0, 50, 20, 50, 20, 15, 15, 70, 70];
        $i->midWindows = [240, 90, 60, 240, 240, 55, 240, 100, 240];
        $i->lateWindows = [240, 170, 140, 170, 140, 135, 135, 180, 180];
        $i->pickupIdxs = [0, 3, 4, 0, 0, 6, 0, 8, 0];
        $i->deliveryIdxs = [0, 0, 0, 1, 2, 0, 5, 0, 7];
        $i->restaurantParkingTimes = [0, 0, 0, 5, 5, 0, 5, 0, 5];
        $i->restaurantServiceTimes = [0, 0, 0, 5, 5, 0, 5, 0, 5];
        $i->clustersParking = [[], [], [], [], [8, 6], [], [8, 4], [], [4, 6]];
        $i->clustersService = [[], [], [], [], [8, 6], [], [8, 4], [], [4, 6]];
        $d = $i->exports();
        $r = Crunchbutton_Optimizer::optimize($d);
        $this->assertNotNull($r);
        $result = new Crunchbutton_Optimizer_Result($r);
        $this->assertEquals($result->resultType, Crunchbutton_Optimizer_Result::RTYPE_OK);
        $this->assertNotNull($result->score);
        $this->assertEquals(round($result->score, 1), 312);
        $this->assertEquals($result->numBadTimes, 1);
    }

    public function testOptimizerAPI2()
    {
        //opt.OptInput(nNodes, 1, 1.0, 5, 5, 120, 240, 5000)
        $i = new Crunchbutton_Optimizer_Input();
        $i->numNodes = 9;
        $i->driverMph = 1;
        $i->penaltyCoefficient = 1.0;
        $i->customerDropoffTime = 5;
        $i->restaurantPickupTime = 5;
        $i->slackMaxTime = 120;
        $i->horizon = 240;
        $i->maxRunTime = 5000;
        $i->distanceType = Crunchbutton_Optimizer_Input::DISTANCE_XY;
        $i->firstCoords = [40, 45, 45, 35, 40, 45, 40, 40, 40];
        $i->secondCoords = [50, 68, 70, 69, 69, 55, 69, 55, 69];
        $i->nodeTypes = [1, 3, 3, 2, 2, 3, 2, 3, 2];
        $i->orderTimes = [0, 50, 20, 50, 20, 15, 15, 60, 60];
        $i->earlyWindows = [0, 50, 20, 50, 20, 15, 15, 70, 70];
        $i->midWindows = [240, 90, 60, 240, 240, 55, 240, 100, 240];
        $i->lateWindows = [240, 170, 140, 170, 140, 135, 135, 180, 180];
        $i->pickupIdxs = [0, 3, 4, 0, 0, 6, 0, 8, 0];
        $i->deliveryIdxs = [0, 0, 0, 1, 2, 0, 5, 0, 7];
        $i->restaurantParkingTimes = [0, 0, 0, 5, 5, 0, 5, 0, 5];
        $i->restaurantServiceTimes = [0, 0, 0, 0, 0, 0, 0, 0, 0];
        $i->clustersParking = [[], [], [], [], [8, 6], [], [8, 4], [], [4, 6]];
        $i->clustersService = [[], [], [], [], [8, 6], [], [8, 4], [], [4, 6]];
        $d = $i->exports();
        $r = Crunchbutton_Optimizer::optimize($d);
        $this->assertNotNull($r);
        $result = new Crunchbutton_Optimizer_Result($r);
        $this->assertEquals($result->resultType, Crunchbutton_Optimizer_Result::RTYPE_OK);
        $this->assertNotNull($result->score);
        $this->assertEquals(round($result->score, 1), 241);
        $this->assertEquals($result->numBadTimes, 0);
    }

    public function testOptimizerAPI3()
    {
        //opt.OptInput(nNodes, 1, 1.0, 5, 5, 120, 240, 5000)
        $i = new Crunchbutton_Optimizer_Input();
        $i->numNodes = 9;
        $i->driverMph = 1;
        $i->penaltyCoefficient = 1.0;
        $i->customerDropoffTime = 5;
        $i->restaurantPickupTime = 5;
        $i->slackMaxTime = 120;
        $i->horizon = 240;
        $i->maxRunTime = 5000;
        $i->distanceType = Crunchbutton_Optimizer_Input::DISTANCE_XY;
        $i->firstCoords = [40, 45, 45, 35, 40, 45, 40, 40, 40];
        $i->secondCoords = [50, 68, 70, 69, 69, 55, 69, 55, 69];
        $i->nodeTypes = [1, 3, 3, 2, 2, 3, 2, 3, 2];
        $i->orderTimes = [0, 50, 20, 50, 20, 15, 15, 60, 60];
        $i->earlyWindows = [0, 50, 20, 50, 20, 15, 15, 70, 70];
        $i->midWindows = [240, 90, 60, 240, 240, 55, 240, 100, 240];
        $i->lateWindows = [240, 170, 140, 170, 140, 135, 135, 180, 180];
        $i->pickupIdxs = [0, 3, 4, 0, 0, 6, 0, 8, 0];
        $i->deliveryIdxs = [0, 0, 0, 1, 2, 0, 5, 0, 7];
        $i->restaurantParkingTimes = [0, 0, 0, 5, 5, 0, 5, 0, 5];
        $i->restaurantServiceTimes = [0, 0, 0, 5, 5, 0, 5, 0, 5];
        $i->clustersParking = [[], [], [], [], [8, 6], [], [8, 4], [], [4, 6]];
        $i->clustersService = [[], [], [], [], [], [], [8], [], [6]];
        $d = $i->exports();
        $r = Crunchbutton_Optimizer::optimize($d);
        $this->assertNotNull($r);
        $result = new Crunchbutton_Optimizer_Result($r);
        $this->assertEquals($result->resultType, Crunchbutton_Optimizer_Result::RTYPE_OK);
        $this->assertNotNull($result->score);
        $this->assertEquals(round($result->score, 1), 319);
        $this->assertEquals($result->numBadTimes, 1);
    }


    public function testActiveDrivers()
    {
        $seconds = 50;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');


        $seconds = 20;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');

        $this->assertGreaterThan(50, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
        $seconds = Crunchbutton_Order_Logistics::TIME_MAX_DELAY - 50;
        $later = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $later->modify('+ ' . $seconds . ' seconds');
        $laterDate = $later->format('Y-m-d H:i:s');

        $o1 = $this->defaultOrder($this->user, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o1->save();
        $drivers = $o1->getDriversToNotify();
        $this->assertEquals($drivers->count(), 3);
    }


    public function testInactiveDriver()
    {
        $seconds = 50;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');


        $seconds = 20;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');

        $this->assertGreaterThan(50, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
        $seconds = Crunchbutton_Order_Logistics::TIME_MAX_DELAY - 50;
        $later = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $later->modify('+ ' . $seconds . ' seconds');
        $laterDate = $later->format('Y-m-d H:i:s');

        $o1 = $this->defaultOrder($this->user, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o1->save();
        $this->driver1->active = false;
        $this->driver1->save();

        $drivers = $o1->getDriversToNotify();
        $this->driver1->active = true;
        $this->driver1->save();
        $this->assertEquals($drivers->count(), 2);
    }


    public function testRestaurantsWithGeo()
    {
        $rs = Restaurant::getDeliveryRestaurantsWithGeoByIdCommunity($this->community->id_community);
        $this->assertEquals($rs->count(), 5);
    }


    // Missing info - defaults used - all drivers get high priority
    public function testLogisticsFirstorderMissingInfo()
    {
        $seconds = 50;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now, 10);
        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o1);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o1->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o1->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o1->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();

        $o1->delete();
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        foreach ($ol->drivers() as $driver) {
            $this->assertEquals($driver->__seconds, 0);
            $this->assertEquals($driver->__priority, true);
        }
        $this->assertEquals($ops->count(), 2);
        foreach ($ops as $op) {
            $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            $this->assertEquals($op->num_undelivered_orders, 0);
            $this->assertEquals($op->num_drivers_with_priority, 2);
        }
        $this->assertEquals($ol->numDriversWithPriority, 2);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 3);
        $this->assertEquals($olr2->count(), 3);
    }

    // Missing fake community fake customers - OK - high priority for all drivers because only one order
    public function testLogisticsMissingCommunityFakeCustomers()
    {
        $seconds = 50;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now, 10);

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 15, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o1);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('SELECT * FROM order_priority WHERE id_order = ?', [$o1->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('SELECT * FROM order_logistics_route WHERE id_order = ? AND id_admin = ?', [$o1->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('SELECT * FROM order_logistics_route WHERE id_order = ? AND id_admin = ?', [$o1->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            $this->assertEquals($driver->__seconds, 0);
            $this->assertEquals($driver->__priority, true);
        }
        $this->assertEquals($ops->count(), 2);
        foreach ($ops as $op) {
            $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            $this->assertEquals($op->num_undelivered_orders, 0);
            $this->assertEquals($op->num_drivers_with_priority, 2);
        }
        $this->assertEquals($ol->numDriversWithPriority, 2);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 3);
        $this->assertEquals($olr2->count(), 3);
    }

    // Missing fake community fake customers - high priority for all due to defaults
    public function testLogisticsOneOtherOrderMissingCommunityFakeCustomers()
    {
        $seconds = 50;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now, 10);

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();
//        $oa2 = new Order_Action([
//            'id_order' => $o2->id_order,
//            'id_admin' => $this->driver1->id_admin,
//            'timestamp' => $useDate2,
//            'type' => 'delivery-accepted',
//            'note' => ''
//        ]);
//        $oa2->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 15, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o1);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o1->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o1->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o1->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();
        $this->driver3->active = true;
        $this->driver3->save();
//        $oa2->delete();
        $o1->delete();
        $o2->delete();
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            $this->assertEquals($driver->__seconds, 0);
            $this->assertEquals($driver->__priority, true);
        }
        $this->assertEquals($ops->count(), 2);
        foreach ($ops as $op) {
            $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            $this->assertEquals($op->num_undelivered_orders, 0);
            $this->assertEquals($op->num_drivers_with_priority, 2);
        }
        $this->assertEquals($ol->numDriversWithPriority, 2);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 3);
        $this->assertEquals($olr2->count(), 3);
    }


    // Two drivers - no orders - same location
    //  First order comes in - both get priority
    public function testLogisticsFirstorderSameLocation1a()
    {
        $seconds = 50;
        $now1 = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now1->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now1->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now1, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now1, 10);

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 15, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o1);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o1->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o1->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o1->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            $this->assertEquals($driver->__seconds, 0);
            $this->assertEquals($driver->__priority, true);
        }
        $this->assertEquals($ops->count(), 2);
        foreach ($ops as $op) {
            $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            $this->assertEquals($op->num_undelivered_orders, 0);
            $this->assertEquals($op->num_drivers_with_priority, 2);
        }
        $this->assertEquals($ol->numDriversWithPriority, 2);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 3);
        $this->assertEquals($olr2->count(), 3);
    }

    // Two drivers - no orders - same location
    //  First order comes in and is a pre-order that is unprocessed
    //  Neither get priority because right now pre-orders do not get any sort of priority
    public function testLogisticsFirstorderSameLocation1b()
    {
        $seconds = 50;
        $now1 = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now1->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now1->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        $minutes = 90;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('+ ' . $minutes . ' minutes');
        $useDate3 = $now->format('Y-m-d H:i:s');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now1, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now1, 10);

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->date = NULL;
        $o1->preordered = 1;
        $o1->date_delivery = $useDate3;
        $o1->preordered_date = $useDate2;
        $o1->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 15, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o1);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o1->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o1->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o1->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            $this->assertEquals($driver->__seconds, 0);
            $this->assertEquals($driver->__priority, false);
        }
        $this->assertEquals($ops->count(), 2);
        foreach ($ops as $op) {
            $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_NO_ONE);
            $this->assertEquals($op->num_undelivered_orders, -1);
            $this->assertEquals($op->num_drivers_with_priority, 0);
        }
        $this->assertEquals($ol->numDriversWithPriority, 0);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 0);
        $this->assertEquals($olr2->count(), 0);
    }

    // Two drivers - no orders - same location
    //  First order comes in and is a pre-order that is processed
    //  Neither get priority because right now pre-orders do not get any sort of priority
    public function testLogisticsFirstorderSameLocation1c()
    {
        $seconds = 50;
        $now1 = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now1->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now1->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        $minutes = 45;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('+ ' . $minutes . ' minutes');
        $useDate3 = $now->format('Y-m-d H:i:s');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now1, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now1, 10);

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->preordered = 1;
        $o1->date_delivery = $useDate3;
        $o1->preordered_date = $useDate2;
        $o1->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 15, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o1);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o1->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o1->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o1->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            $this->assertEquals($driver->__seconds, 0);
            $this->assertEquals($driver->__priority, false);
        }
        $this->assertEquals($ops->count(), 2);
        foreach ($ops as $op) {
            $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_NO_ONE);
            $this->assertEquals($op->num_undelivered_orders, -1);
            $this->assertEquals($op->num_drivers_with_priority, 0);
        }
        $this->assertEquals($ol->numDriversWithPriority, 0);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 0);
        $this->assertEquals($olr2->count(), 0);
    }



    // Two drivers - no orders - same location
    //  First order comes in - driver 2 has a higher score and gets priority
    public function testLogisticsFirstorderSameLocationDifferentScore1()
    {
        $seconds = 50;
        $now1 = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now1->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now1->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now1, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now1, 10);

        $score1 = $this->defaultScore($this->driver1, 1, 1);
        $score2 = $this->defaultScore($this->driver2, 1.2, 1);
        $score1->save();
        $score2->save();

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 15, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o1);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o1->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o1->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o1->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        $score1->delete();
        $score2->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);
        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_undelivered_orders, 0);
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 3);
        $this->assertEquals($olr2->count(), 3);
    }


    // Two drivers - no orders - same location
    //  First order comes in - driver 2 has a higher experience and gets priority
    public function testLogisticsFirstorderSameLocationDifferentExp1()
    {
        $seconds = 50;
        $now1 = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now1->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now1->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now1, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now1, 10);

        $score1 = $this->defaultScore($this->driver1, 1, 1);
        $score2 = $this->defaultScore($this->driver2, 1, 2);
        $score1->save();
        $score2->save();

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 7, 6, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 6, 6, 6, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 15, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o1);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o1->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o1->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o1->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        $score1->delete();
        $score2->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);
        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_undelivered_orders, 0);
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 3);
        $this->assertEquals($olr2->count(), 3);
    }

    // Two drivers - no orders - same location
    //  First order comes in - driver 2 has a higher experience and gets priority
    public function testLogisticsFirstorderSameLocationDifferentExp2()
    {
        $seconds = 50;
        $now1 = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now1->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now1->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now1, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now1, 10);

        $score1 = $this->defaultScore($this->driver1, 1, 1);
        $score2 = $this->defaultScore($this->driver2, 1, 2);
        $score1->save();
        $score2->save();

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 6, 6, 6, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 7, 6, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 15, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o1);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o1->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o1->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o1->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        $score1->delete();
        $score2->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);
        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_undelivered_orders, 0);
            $this->assertEquals($op->num_drivers_with_priority, 1);

        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 3);
        $this->assertEquals($olr2->count(), 3);
    }

    // Two drivers - no orders - same location
    //  First order comes in - driver 2 has a higher experience and gets priority
    public function testLogisticsFirstorderSameLocationDifferentExp3()
    {
        $seconds = 50;
        $now1 = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now1->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now1->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now1, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now1, 10);

        $score1 = $this->defaultScore($this->driver1, 1, 0);
        $score2 = $this->defaultScore($this->driver2, 1, 2);
        $score1->save();
        $score2->save();

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 6, 6, 6, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 7, 6, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 15, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o1);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o1->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o1->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o1->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        $score1->delete();
        $score2->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);
        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_undelivered_orders, 0);
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 3);
        $this->assertEquals($olr2->count(), 3);
    }


    // Two drivers - no orders - same location
    //  First order comes in - driver 2 has a higher experience and gets priority
    public function testLogisticsFirstorderSameLocationDifferentExp4()
    {
        $seconds = 50;
        $now1 = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now1->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now1->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now1, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now1, 10);

        $score1 = $this->defaultScore($this->driver1, 1, 0);
        $score2 = $this->defaultScore($this->driver2, 1, 1);
        $score1->save();
        $score2->save();

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 6, 6, 6, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 7, 6, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 15, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o1);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o1->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o1->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o1->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        $score1->delete();
        $score2->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);
        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_undelivered_orders, 0);
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 3);
        $this->assertEquals($olr2->count(), 3);
    }

    // Two drivers - no orders - different locations, but still close
    //  First order comes in - first driver gets high priority because wait time < service + parking + travel time
    //   for the other driver.  About 6 minutes travel time + 5 min service + 5 min parking > 15 min
    public function testLogisticsFirstorderDifferentLocationWithLongWait1()
    {
        $seconds = 50;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.018, -118.281, $now, 10);
        $score1 = $this->defaultScore($this->driver1, 1, 1);
        $score2 = $this->defaultScore($this->driver2, 1, 1);
        $score1->save();
        $score2->save();

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 15, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o1);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o1->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o1->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o1->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        $score1->delete();
        $score2->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);
        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_undelivered_orders, 0);
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 3);
        $this->assertEquals($olr2->count(), 3);
    }


    // Two drivers - no orders - different locations, but still close
    //  First order comes in - both drivers gets high priority because wait time > service + parking + travel time
    public function testLogisticsFirstorderDifferentLocationWithLongWait2()
    {
        $seconds = 50;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.018, -118.281, $now, 10);
        $score1 = $this->defaultScore($this->driver1, 1, 1);
        $score2 = $this->defaultScore($this->driver2, 1, 1);
        $score1->save();
        $score2->save();

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 20, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o1);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('SELECT * FROM order_priority WHERE id_order = ?', [$o1->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o1->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o1->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        $score1->delete();
        $score2->delete();
        foreach ($ol->drivers() as $driver) {
            $this->assertEquals($driver->__seconds, 0);
            $this->assertEquals($driver->__priority, true);
        }
        $this->assertEquals($ops->count(), 2);
        foreach ($ops as $op) {
            $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            $this->assertEquals($op->num_undelivered_orders, 0);
            $this->assertEquals($op->num_drivers_with_priority, 2);
        }
        $this->assertEquals($ol->numDriversWithPriority, 2);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 3);
        $this->assertEquals($olr2->count(), 3);
    }


    // Two drivers - no orders - different locations, but still close
    //  First order comes in - driver2 gets high priority because of a higher score
    public function testLogisticsFirstorderDifferentLocationWithLongWaitDifferentScore1()
    {
        $seconds = 50;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.018, -118.281, $now, 10);
        $score1 = $this->defaultScore($this->driver1, 1, 1);
        $score2 = $this->defaultScore($this->driver2, 1.2, 1);
        $score1->save();
        $score2->save();

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 20, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o1);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('SELECT * FROM order_priority WHERE id_order = ?', [$o1->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o1->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o1->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        $score1->delete();
        $score2->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_undelivered_orders, 0);
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 3);
        $this->assertEquals($olr2->count(), 3);
    }


    // Two drivers - no orders - different locations, but still close
    //  First order comes in - both drivers gets high priority because wait time > service + parking + travel time
    //  This holds even when one driver has a faster parking time
    public function testLogisticsFirstorderDifferentLocationWithLongWaitDifferentExp1()
    {
        $seconds = 50;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.018, -118.281, $now, 10);
        $score1 = $this->defaultScore($this->driver1, 1, 1);
        $score2 = $this->defaultScore($this->driver2, 1, 2);
        $score1->save();
        $score2->save();

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 6, 5, 4, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 20, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o1);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('SELECT * FROM order_priority WHERE id_order = ?', [$o1->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o1->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o1->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        $score1->delete();
        $score2->delete();
        foreach ($ol->drivers() as $driver) {
            $this->assertEquals($driver->__seconds, 0);
            $this->assertEquals($driver->__priority, true);
        }
        $this->assertEquals($ops->count(), 2);
        foreach ($ops as $op) {
            $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            $this->assertEquals($op->num_undelivered_orders, 0);
            $this->assertEquals($op->num_drivers_with_priority, 2);
        }
        $this->assertEquals($ol->numDriversWithPriority, 2);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 3);
        $this->assertEquals($olr2->count(), 3);
    }


    // Two drivers - no orders - different locations, but still close
    //  First order comes in - both drivers gets high priority because wait time > service + parking + travel time
    //  This holds even when one driver has a faster service time
    public function testLogisticsFirstorderDifferentLocationWithLongWaitDifferentExp2()
    {
        $seconds = 50;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.018, -118.281, $now, 10);
        $score1 = $this->defaultScore($this->driver1, 1, 1);
        $score2 = $this->defaultScore($this->driver2, 1, 2);
        $score1->save();
        $score2->save();

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 6, 5, 4, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 20, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o1);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('SELECT * FROM order_priority WHERE id_order = ?', [$o1->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o1->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o1->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        $score1->delete();
        $score2->delete();
        foreach ($ol->drivers() as $driver) {
            $this->assertEquals($driver->__seconds, 0);
            $this->assertEquals($driver->__priority, true);
        }
        $this->assertEquals($ops->count(), 2);
        foreach ($ops as $op) {
            $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            $this->assertEquals($op->num_undelivered_orders, 0);
            $this->assertEquals($op->num_drivers_with_priority, 2);
        }
        $this->assertEquals($ol->numDriversWithPriority, 2);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 3);
        $this->assertEquals($olr2->count(), 3);
    }


    // Two drivers - no orders - different locations, but still close
    //  First order comes in - driver 2 should get priority because of no waiting time.
    public function testLogisticsFirstorderDifferentLocationNoWait()
    {
        $seconds = 50;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.018, -118.281, $now, 10);

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o1);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o1->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o1->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o1->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_undelivered_orders, 0);
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 3);
        $this->assertEquals($olr2->count(), 3);
    }


    // Two drivers - driver 1 has accepted order from Chipotle (with no waiting time).
    //  Both drivers are at the same location
    //  New Chipotle order comes in - driver 1 should get priority due to bundling.
    public function testLogisticsSecondOrderSameLocationNoWaitA1a1()
    {
        $seconds = 50;
        $now1 = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now1->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now1->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now1, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now1, 10);

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $o2->delete();
        $oa1->delete();
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->num_unpickedup_preorders, 0);
                $this->assertEquals($op->num_unpickedup_pos_in_range, 0);
                $this->assertEquals($op->num_orders_bundle_check, 1);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 0);
                $this->assertEquals($op->num_unpickedup_preorders, 0);
                $this->assertEquals($op->num_unpickedup_pos_in_range, 0);
                $this->assertEquals($op->num_orders_bundle_check, 0);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 5);
        $this->assertEquals($olr2->count(), 3);
    }

    // Two drivers - driver 1 has accepted order from Chipotle (with no waiting time).
    //  Both drivers are at the same location
    //  New Chipotle order comes in - driver 1 should get priority due to bundling.
    // Use different timezone
    public function testLogisticsSecondOrderSameLocationNoWaitA1a2()
    {
        $seconds = 50;
        $now1 = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now1->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now1->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now1, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now1, 10);

        $this->community->timezone = 'America/New_York';
        $this->community->save();
        $this->restaurant3->timezone = 'America/New_York';
        $this->restaurant3->save();
        $this->driver1->timezone = 'America/New_York';
        $this->driver1->save();
        $this->driver2->timezone = 'America/New_York';
        $this->driver2->save();
        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $o2->delete();
        $oa1->delete();
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        $this->community->timezone = 'America/Los_Angeles';
        $this->community->save();
        $this->restaurant3->timezone = 'America/Los_Angeles';
        $this->restaurant3->save();
        $this->driver1->timezone = 'America/Los_Angeles';
        $this->driver1->save();
        $this->driver2->timezone = 'America/Los_Angeles';
        $this->driver2->save();

        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->num_unpickedup_preorders, 0);
                $this->assertEquals($op->num_unpickedup_pos_in_range, 0);
                $this->assertEquals($op->num_orders_bundle_check, 1);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 0);
                $this->assertEquals($op->num_unpickedup_preorders, 0);
                $this->assertEquals($op->num_unpickedup_pos_in_range, 0);
                $this->assertEquals($op->num_orders_bundle_check, 0);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 5);
        $this->assertEquals($olr2->count(), 3);
    }

    // Two drivers - driver 1 has accepted preorder from Chipotle (with no waiting time).
    //  Both drivers are at the same location
    //  New Chipotle order comes in - both drivers should get high priority because the preorder was accepted
    //   early and the delivery is not for a while
    public function testLogisticsSecondOrderSameLocationNoWaitA1a3()
    {
        $seconds = 50;
        $now1 = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now1->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now1->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        $minutes = 80;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('+ ' . $minutes . ' minutes');
        $useDate3 = $now->format('Y-m-d H:i:s');

        $minutes = 240;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate4 = $now->format('Y-m-d H:i:s');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now1, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now1, 10);

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->date = $useDate1;
        $o1->preordered=1;
        $o1->date_delivery = $useDate3;
        $o1->preordered_date = $useDate1;
        $o1->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $o2->delete();
        $oa1->delete();
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            $this->assertEquals($driver->__seconds, 0);
            $this->assertEquals($driver->__priority, true);
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->num_unpickedup_preorders, 1);
                $this->assertEquals($op->num_unpickedup_pos_in_range, 0);
                $this->assertEquals($op->num_orders_bundle_check, 0);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 0);
                $this->assertEquals($op->num_unpickedup_preorders, 0);
                $this->assertEquals($op->num_unpickedup_pos_in_range, 0);
                $this->assertEquals($op->num_orders_bundle_check, 0);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            }
            $this->assertEquals($op->num_drivers_with_priority, 2);
        }
        $this->assertEquals($ol->numDriversWithPriority, 2);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 5);
        $this->assertEquals($olr2->count(), 3);
    }


    // Two drivers - driver 1 has accepted preorder from Chipotle (with no waiting time).
    //  Both drivers are at the same location
    //  New Chipotle order comes in - driver 1 should get priority because he accepted a while back and
    //   now order is within window
    public function testLogisticsSecondOrderSameLocationNoWaitA1a4()
    {
        $seconds = 50;
        $now1 = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now1->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now1->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        $minutes = 44;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('+ ' . $minutes . ' minutes');
        $useDate3 = $now->format('Y-m-d H:i:s');

        $minutes = 240;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate4 = $now->format('Y-m-d H:i:s');

        $minutes = 46;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate5 = $now->format('Y-m-d H:i:s');


        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now1, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now1, 10);

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate5, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->preordered=1;
        $o1->date_delivery = $useDate3;
        $o1->preordered_date = $useDate4;
        $o1->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $o2->delete();
        $oa1->delete();
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->num_unpickedup_preorders, 1);
                $this->assertEquals($op->num_unpickedup_pos_in_range, 1);
                $this->assertEquals($op->num_orders_bundle_check, 1);

                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 0);
                $this->assertEquals($op->num_unpickedup_preorders, 0);
                $this->assertEquals($op->num_unpickedup_pos_in_range, 0);
                $this->assertEquals($op->num_orders_bundle_check, 0);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 5);
        $this->assertEquals($olr2->count(), 3);
    }


    // Two drivers - driver 1 has accepted order from Chipotle (with no waiting time).
    //  Both drivers are at the same location
    //  New Chipotle order comes in - driver 1 should get priority due to bundling.
    public function testLogisticsSecondOrderSameLocationNoWaitA1b()
    {
        $seconds = 50;
        $now1 = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now1->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now1->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now1, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now1, 10);

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $o2->delete();
        $oa1->delete();
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->num_unpickedup_preorders, 0);
                $this->assertEquals($op->num_unpickedup_pos_in_range, 0);
                $this->assertEquals($op->num_orders_bundle_check, 1);
            } else {
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
                $this->assertEquals($op->num_undelivered_orders, 0);
                $this->assertEquals($op->num_unpickedup_preorders, 0);
                $this->assertEquals($op->num_unpickedup_pos_in_range, 0);
                $this->assertEquals($op->num_orders_bundle_check, 0);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 5);
        $this->assertEquals($olr2->count(), 3);
    }



    // Two drivers - driver 1 has accepted order from Chipotle (with no waiting time).
    //  Both drivers are at the same location
    //  New Chipotle order comes in - driver 1 should get priority due to bundling.
    public function testLogisticsSecondOrderSameLocationLongWaitA1()
    {
        $seconds = 50;
        $now1 = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now1->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now1->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now1, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now1, 10);

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 30, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 5, $dow);
        $olcs1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $o2->delete();
        $oa1->delete();
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->num_unpickedup_preorders, 0);
                $this->assertEquals($op->num_unpickedup_pos_in_range, 0);
                $this->assertEquals($op->num_orders_bundle_check, 1);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 0);
                $this->assertEquals($op->num_unpickedup_preorders, 0);
                $this->assertEquals($op->num_unpickedup_pos_in_range, 0);
                $this->assertEquals($op->num_orders_bundle_check, 0);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 5);
        $this->assertEquals($olr2->count(), 3);
    }

    // Two drivers - driver 1 has accepted order from Chipotle (with no waiting time).
    //  Both drivers are at the same location
    //  New Chipotle order comes in - driver 1 should get priority due to bundling.
    public function testLogisticsSecondOrderSameLocationLongWaitA2()
    {
        $seconds = 50;
        $now1 = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now1->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now1->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now1, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now1, 10);

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 40, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 5, $dow);
        $olcs1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $o2->delete();
        $oa1->delete();
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->num_unpickedup_preorders, 0);
                $this->assertEquals($op->num_unpickedup_pos_in_range, 0);
                $this->assertEquals($op->num_orders_bundle_check, 1);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 0);
                $this->assertEquals($op->num_unpickedup_preorders, 0);
                $this->assertEquals($op->num_unpickedup_pos_in_range, 0);
                $this->assertEquals($op->num_orders_bundle_check, 0);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 5);
        $this->assertEquals($olr2->count(), 3);
    }

    // Two drivers - driver 1 has accepted order from Chipotle (with no waiting time).
    //  Both drivers are at the same location
    //  New Chipotle order comes in - driver 1 should get priority due to bundling.
    public function testLogisticsSecondOrderSameLocationLongWaitA3()
    {
        $seconds = 50;
        $now1 = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now1->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now1->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now1, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now1, 10);

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 5, $dow);
        $olcs1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $o2->delete();
        $oa1->delete();
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->num_unpickedup_preorders, 0);
                $this->assertEquals($op->num_unpickedup_pos_in_range, 0);
                $this->assertEquals($op->num_orders_bundle_check, 1);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 0);
                $this->assertEquals($op->num_unpickedup_preorders, 0);
                $this->assertEquals($op->num_unpickedup_pos_in_range, 0);
                $this->assertEquals($op->num_orders_bundle_check, 0);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 5);
        $this->assertEquals($olr2->count(), 3);
    }


    // Two drivers - driver 1 has accepted order from Chipotle (with no waiting time).
    //  Both drivers are at the same location
    //  New Chipotle order comes in - driver 1 should get priority due to bundling.
    public function testLogisticsSecondOrderSameLocationLongWaitA4()
    {
        $seconds = 50;
        $now1 = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now1->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now1->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now1, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now1, 10);

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 10, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 5, $dow);
        $olcs1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $o2->delete();
        $oa1->delete();
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->num_unpickedup_preorders, 0);
                $this->assertEquals($op->num_unpickedup_pos_in_range, 0);
                $this->assertEquals($op->num_orders_bundle_check, 1);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 0);
                $this->assertEquals($op->num_unpickedup_preorders, 0);
                $this->assertEquals($op->num_unpickedup_pos_in_range, 0);
                $this->assertEquals($op->num_orders_bundle_check, 0);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 5);
        $this->assertEquals($olr2->count(), 3);
    }

    // Two drivers - driver 1 has accepted order from Chipotle (with no waiting time).
    //  Both drivers are at the same location
    //  New Chipotle order comes in - driver 1 should get priority due to bundling.
    public function testLogisticsSecondOrderSameLocationLongWaitA5()
    {
        $seconds = 50;
        $now1 = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now1->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now1->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now1, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now1, 10);

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 15, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 5, $dow);
        $olcs1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $o2->delete();
        $oa1->delete();
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->num_unpickedup_preorders, 0);
                $this->assertEquals($op->num_unpickedup_pos_in_range, 0);
                $this->assertEquals($op->num_orders_bundle_check, 1);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 0);
                $this->assertEquals($op->num_unpickedup_preorders, 0);
                $this->assertEquals($op->num_unpickedup_pos_in_range, 0);
                $this->assertEquals($op->num_orders_bundle_check, 0);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 5);
        $this->assertEquals($olr2->count(), 3);
    }


    // Two drivers - driver 1 has accepted order from Chipotle (with no waiting time).
    //  Both drivers are at the same location
    //  New Chipotle order comes in - driver 1 should get priority due to bundling.
    public function testLogisticsSecondOrderSameLocationLongWaitA6()
    {
        $seconds = 50;
        $now1 = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now1->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now1->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now1, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now1, 10);

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 20, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 5, $dow);
        $olcs1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $o2->delete();
        $oa1->delete();
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->num_unpickedup_preorders, 0);
                $this->assertEquals($op->num_unpickedup_pos_in_range, 0);
                $this->assertEquals($op->num_orders_bundle_check, 1);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 0);
                $this->assertEquals($op->num_unpickedup_preorders, 0);
                $this->assertEquals($op->num_unpickedup_pos_in_range, 0);
                $this->assertEquals($op->num_orders_bundle_check, 0);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 5);
        $this->assertEquals($olr2->count(), 3);
    }

    // Two drivers - driver 1 has accepted order from Chipotle (with no waiting time).
    //  Both drivers are at the same location
    //  New Chipotle order comes in - driver 1 should get priority due to bundling.
    public function testLogisticsSecondOrderSameLocationLongWaitB1()
    {
        $seconds = 50;
        $now1 = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now1->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now1->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now1, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now1, 10);

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 30, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $o2->delete();
        $oa1->delete();
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 0);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 5);
        $this->assertEquals($olr2->count(), 3);
    }

    // Two drivers - driver 1 has accepted order from Chipotle (with no waiting time).
    //  Both drivers are at the same location
    //  New Chipotle order comes in - driver 1 should get priority due to bundling.
    public function testLogisticsSecondOrderSameLocationLongWaitB2()
    {
        $seconds = 50;
        $now1 = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now1->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now1->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now1, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now1, 10);

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 40, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $o2->delete();
        $oa1->delete();
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 0);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 5);
        $this->assertEquals($olr2->count(), 3);
    }

    // Two drivers - driver 1 has accepted order from Chipotle (with no waiting time).
    //  Both drivers are at the same location
    //  New Chipotle order comes in - driver 1 should get priority due to bundling.
    public function testLogisticsSecondOrderSameLocationLongWaitB3()
    {
        $seconds = 50;
        $now1 = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now1->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now1->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now1, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now1, 10);

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $o2->delete();
        $oa1->delete();
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 0);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 5);
        $this->assertEquals($olr2->count(), 3);
    }


    // Two drivers - driver 1 has accepted order from Chipotle (with no waiting time).
    //  Both drivers are at the same location
    //  New Chipotle order comes in - driver 1 should get priority due to bundling.
    public function testLogisticsSecondOrderSameLocationLongWaitB4()
    {
        $seconds = 50;
        $now1 = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now1->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now1->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now1, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now1, 10);

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 10, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $o2->delete();
        $oa1->delete();
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 0);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 5);
        $this->assertEquals($olr2->count(), 3);
    }

    // Two drivers - driver 1 has accepted order from Chipotle (with no waiting time).
    //  Both drivers are at the same location
    //  New Chipotle order comes in - driver 1 should get priority due to bundling.
    public function testLogisticsSecondOrderSameLocationLongWaitB5()
    {
        $seconds = 50;
        $now1 = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now1->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now1->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now1, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now1, 10);

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 15, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $o2->delete();
        $oa1->delete();
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 0);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 5);
        $this->assertEquals($olr2->count(), 3);
    }


    // Two drivers - driver 1 has accepted order from Chipotle (with no waiting time).
    //  Both drivers are at the same location
    //  New Chipotle order comes in - driver 1 should get priority due to bundling.
    public function testLogisticsSecondOrderSameLocationLongWaitB6()
    {
        $seconds = 50;
        $now1 = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now1->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now1->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now1, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now1, 10);

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 20, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $o2->delete();
        $oa1->delete();
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 0);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 5);
        $this->assertEquals($olr2->count(), 3);
    }

    // Two drivers - driver 1 has accepted order from Chipotle (with no waiting time).
    //  Both drivers are at the same location
    //  New Chipotle order comes in - driver 1 should get priority due to bundling.
    public function testLogisticsSecondOrderSameLocationLongWaitC1()
    {
        $seconds = 50;
        $now1 = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now1->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now1->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now1, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now1, 10);

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 30, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 15, $dow);
        $olcs1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $o2->delete();
        $oa1->delete();
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 0);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 5);
        $this->assertEquals($olr2->count(), 3);
    }

    // Two drivers - driver 1 has accepted order from Chipotle (with no waiting time).
    //  Both drivers are at the same location
    //  New Chipotle order comes in - driver 1 should get priority due to bundling.
    public function testLogisticsSecondOrderSameLocationLongWaitC2()
    {
        $seconds = 50;
        $now1 = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now1->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now1->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now1, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now1, 10);

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 40, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 15, $dow);
        $olcs1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $o2->delete();
        $oa1->delete();
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 0);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 5);
        $this->assertEquals($olr2->count(), 3);
    }

    // Two drivers - driver 1 has accepted order from Chipotle (with no waiting time).
    //  Both drivers are at the same location
    //  New Chipotle order comes in - driver 1 should get priority due to bundling.
    public function testLogisticsSecondOrderSameLocationLongWaitC3()
    {
        $seconds = 50;
        $now1 = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now1->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now1->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now1, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now1, 10);

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 15, $dow);
        $olcs1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $o2->delete();
        $oa1->delete();
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 0);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 5);
        $this->assertEquals($olr2->count(), 3);
    }


    // Two drivers - driver 1 has accepted order from Chipotle (with no waiting time).
    //  Both drivers are at the same location
    //  New Chipotle order comes in - driver 1 should get priority due to bundling.
    public function testLogisticsSecondOrderSameLocationLongWaitC4()
    {
        $seconds = 50;
        $now1 = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now1->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now1->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now1, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now1, 10);

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 10, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 15, $dow);
        $olcs1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $o2->delete();
        $oa1->delete();
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 0);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 5);
        $this->assertEquals($olr2->count(), 3);
    }

    // Two drivers - driver 1 has accepted order from Chipotle (with no waiting time).
    //  Both drivers are at the same location
    //  New Chipotle order comes in - driver 1 should get priority due to bundling.
    public function testLogisticsSecondOrderSameLocationLongWaitC5()
    {
        $seconds = 50;
        $now1 = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now1->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now1->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now1, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now1, 10);

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 15, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 15, $dow);
        $olcs1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $o2->delete();
        $oa1->delete();
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 0);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 5);
        $this->assertEquals($olr2->count(), 3);
    }


    // Two drivers - driver 1 has accepted order from Chipotle (with no waiting time).
    //  Both drivers are at the same location
    //  New Chipotle order comes in - driver 1 should get priority due to bundling.
    public function testLogisticsSecondOrderSameLocationLongWaitC6()
    {
        $seconds = 50;
        $now1 = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now1->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now1->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now1, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now1, 10);

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 20, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 15, $dow);
        $olcs1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $o2->delete();
        $oa1->delete();
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 0);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 5);
        $this->assertEquals($olr2->count(), 3);
    }

    // Two drivers - driver 1 has accepted order from Chipotle (with no waiting time).
    //  Both drivers are at different locations, based on action algo
    //  New Chipotle order comes in - driver 1 should get priority due to bundling.
    public function testLogisticsSecondOrderDiffLocationNoWaitA1()
    {
        $seconds = 50;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $o2->delete();
        $oa1->delete();
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 0);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 5);
        $this->assertEquals($olr2->count(), 3);
    }

    // Two drivers - driver 1 has accepted order from Chipotle (with 20 min order-ahead time).
    //  Both drivers are at different locations, based on action algo
    //  New Chipotle order comes in - driver 1 should get priority due to bundling.
    public function testLogisticsSecondOrderDiffLocationLongWaitA1()
    {
        $seconds = 50;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $o2->delete();
        $oa1->delete();
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 0);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 5);
        $this->assertEquals($olr2->count(), 3);
    }


    // Two drivers - driver 1 has accepted order from Chipotle (with no waiting time).
    //  Both drivers are at the same location
    //  New Chipotle order comes in - driver 1 should get priority due to bundling.
    public function testLogisticsSecondOrderDiffLocationLongWaitC1()
    {
        $seconds = 50;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 30, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 15, $dow);
        $olcs1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $o2->delete();
        $oa1->delete();
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 0);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 5);
        $this->assertEquals($olr2->count(), 3);
    }

    // Two drivers - driver 1 has accepted order from Chipotle (with no waiting time).
    //  Both drivers are at the same location
    //  New Chipotle order comes in - driver 1 should get priority due to bundling.
    public function testLogisticsSecondOrderDiffLocationLongWaitC2()
    {
        $seconds = 50;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 40, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 15, $dow);
        $olcs1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $o2->delete();
        $oa1->delete();
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 0);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 5);
        $this->assertEquals($olr2->count(), 3);
    }

    // Two drivers - driver 1 has accepted order from Chipotle (with no waiting time).
    //  Both drivers are at the same location
    //  New Chipotle order comes in - driver 1 should get priority due to bundling.
    public function testLogisticsSecondOrderDiffLocationLongWaitC3()
    {
        $seconds = 50;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 15, $dow);
        $olcs1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $o2->delete();
        $oa1->delete();
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 0);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 5);
        $this->assertEquals($olr2->count(), 3);
    }


    // Two drivers - driver 1 has accepted order from Chipotle (with no waiting time).
    //  Both drivers are at the same location
    //  New Chipotle order comes in - driver 1 should get priority due to bundling.
    public function testLogisticsSecondOrderDiffLocationLongWaitC4()
    {
        $seconds = 50;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 10, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 15, $dow);
        $olcs1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $o2->delete();
        $oa1->delete();
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 0);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 5);
        $this->assertEquals($olr2->count(), 3);
    }

    // Two drivers - driver 1 has accepted order from Chipotle (with no waiting time).
    //  Both drivers are at the same location
    //  New Chipotle order comes in - driver 1 should get priority due to bundling.
    public function testLogisticsSecondOrderDiffLocationLongWaitC5()
    {
        $seconds = 50;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 15, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 15, $dow);
        $olcs1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $o2->delete();
        $oa1->delete();
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 0);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 5);
        $this->assertEquals($olr2->count(), 3);
    }


    // Two drivers - driver 1 has accepted order from Chipotle (with no waiting time).
    //  Both drivers are at the same location
    //  New Chipotle order comes in - driver 1 should get priority due to bundling.
    public function testLogisticsSecondOrderDiffLocationLongWaitC6()
    {
        $seconds = 50;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 20, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 15, $dow);
        $olcs1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $o2->delete();
        $oa1->delete();
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 0);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 5);
        $this->assertEquals($olr2->count(), 3);
    }

    // Two drivers - driver 1 has two accepted orders from Chipotle (with no waiting time).
    //  Both drivers are at the same location
    //  New Chipotle order comes in - driver 1 should get priority due to bundling.
    //  Bundled customers in the same location
    public function testLogisticsThirdOrderSameLocationNoWaitA1a()
    {
        $seconds = 50;
        $now1 = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now1->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now1->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now1, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now1, 10);

        // Chipotle
        $o1a = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1a->lat = 34.0284;
        $o1a->lon = -118.287;
        $o1a->save();

        // Chipotle
        $o1b = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1b->lat = 34.0284;
        $o1b->lon = -118.287;
        $o1b->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $oa1a = new Order_Action([
            'id_order' => $o1a->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1a->save();
        $this->associateOrderActionToOrder($o1a, $oa1a);

        $oa1b = new Order_Action([
            'id_order' => $o1b->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1b->save();
        $this->associateOrderActionToOrder($o1b, $oa1b);

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1a->delete();
        $o1b->delete();
        $o2->delete();
        $oa1a->delete();
        $oa1b->delete();
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 2);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 0);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 7);
        $this->assertEquals($olr2->count(), 3);
    }


    // Two drivers - driver 1 has two accepted orders from Chipotle (with no waiting time).
    //  Both drivers are at the same location
    //  New Chipotle order comes in - driver 1 should get priority due to bundling.
    //  Bundled customers in different locations
    public function testLogisticsThirdOrderSameLocationNoWaitA1b()
    {
        $seconds = 50;
        $now1 = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now1->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now1->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now1, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now1, 10);

        // Chipotle
        $o1a = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1a->lat = 34.0284;
        $o1a->lon = -118.287;
        $o1a->save();

        // Chipotle
        $o1b = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1b->lat = 34.026562;
        $o1b->lon = -118.272312;
        $o1b->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $oa1a = new Order_Action([
            'id_order' => $o1a->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1a->save();
        $this->associateOrderActionToOrder($o1a, $oa1a);

        $oa1b = new Order_Action([
            'id_order' => $o1b->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1b->save();
        $this->associateOrderActionToOrder($o1b, $oa1b);

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1a->delete();
        $o1b->delete();
        $o2->delete();
        $oa1a->delete();
        $oa1b->delete();
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 2);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 0);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 7);
        $this->assertEquals($olr2->count(), 3);
    }

    // Two drivers - driver 1 has two accepted orders from Chipotle (with no waiting time).
    // Drivers are at different locations
    //  New Chipotle order comes in - driver 1 should get priority due to bundling.
    //  Bundled customers in different locations
    public function testLogisticsThirdOrderDiffLocationNoWaitA1b()
    {
        $seconds = 50;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        // Chipotle
        $o1a = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1a->lat = 34.0284;
        $o1a->lon = -118.287;
        $o1a->save();

        // Chipotle
        $o1b = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1b->lat = 34.026562;
        $o1b->lon = -118.272312;
        $o1b->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $oa1a = new Order_Action([
            'id_order' => $o1a->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1a->save();
        $this->associateOrderActionToOrder($o1a, $oa1a);

        $oa1b = new Order_Action([
            'id_order' => $o1b->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1b->save();
        $this->associateOrderActionToOrder($o1b, $oa1b);

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1a->delete();
        $o1b->delete();
        $o2->delete();
        $oa1a->delete();
        $oa1b->delete();

        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 2);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 0);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 7);
        $this->assertEquals($olr2->count(), 3);
    }

    // Two drivers - driver 1 has two accepted orders from Chipotle (with no waiting time).
    // Drivers are at different locations
    //  New Chipotle order comes in - driver 1 should get priority due to bundling.
    //  Bundled customers in different locations
    public function testLogisticsThirdOrderDiffLocationNoWaitA1c()
    {
        $seconds = 50;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        // Chipotle
        $o1a = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1a->lat = 34.0284;
        $o1a->lon = -118.287;
        $o1a->save();

        // Chipotle
        $o1b = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1b->lat = 34.026562;
        $o1b->lon = -118.272312;
        $o1b->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.025458;
        $o2->lon = -118.291428;
        $o2->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $oa1a = new Order_Action([
            'id_order' => $o1a->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1a->save();
        $this->associateOrderActionToOrder($o1a, $oa1a);

        $oa1b = new Order_Action([
            'id_order' => $o1b->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1b->save();
        $this->associateOrderActionToOrder($o1b, $oa1b);

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1a->delete();
        $o1b->delete();
        $o2->delete();
        $oa1a->delete();
        $oa1b->delete();

        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 2);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 0);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 7);
        $this->assertEquals($olr2->count(), 3);
    }


    // Two drivers - driver 1 has two accepted orders from Chipotle (with 20 min order-ahead time).
    // Drivers are at different locations
    //  New Chipotle order comes in - driver 1 should get priority due to bundling.
    //  Bundled customers in different locations
    public function testLogisticsThirdOrderDiffLocationLongWaitA1b()
    {
        $seconds = 50;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        // Chipotle
        $o1a = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1a->lat = 34.0284;
        $o1a->lon = -118.287;
        $o1a->save();

        // Chipotle
        $o1b = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1b->lat = 34.026562;
        $o1b->lon = -118.272312;
        $o1b->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 10, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $oa1a = new Order_Action([
            'id_order' => $o1a->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1a->save();
        $this->associateOrderActionToOrder($o1a, $oa1a);

        $oa1b = new Order_Action([
            'id_order' => $o1b->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1b->save();
        $this->associateOrderActionToOrder($o1b, $oa1b);

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1a->delete();
        $o1b->delete();
        $o2->delete();
        $oa1a->delete();
        $oa1b->delete();

        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 2);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 0);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 7);
        $this->assertEquals($olr2->count(), 3);
    }


    // Two drivers - driver 1 has two accepted orders from Chipotle (with 20 min order-ahead time).
    // Drivers are at different locations
    //  New Chipotle order comes in - driver 1 should get priority due to bundling.
    //  Bundled customers in different locations
    public function testLogisticsThirdOrderDiffLocationLongWaitA1c()
    {
        $seconds = 50;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        // Chipotle
        $o1a = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1a->lat = 34.0284;
        $o1a->lon = -118.287;
        $o1a->save();

        // Chipotle
        $o1b = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1b->lat = 34.026562;
        $o1b->lon = -118.272312;
        $o1b->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.025458;
        $o2->lon = -118.291428;
        $o2->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 10, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $oa1a = new Order_Action([
            'id_order' => $o1a->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1a->save();
        $this->associateOrderActionToOrder($o1a, $oa1a);

        $oa1b = new Order_Action([
            'id_order' => $o1b->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1b->save();
        $this->associateOrderActionToOrder($o1b, $oa1b);

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1a->delete();
        $o1b->delete();
        $o2->delete();
        $oa1a->delete();
        $oa1b->delete();

        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 2);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 0);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 7);
        $this->assertEquals($olr2->count(), 3);
    }


    // Two drivers - driver 1 has three accepted orders from Chipotle (with no waiting time).
    //  Both drivers are at the same location
    //  New Chipotle order comes in - driver 2 should get priority due to bundle limit being exceeded.
    public function testLogisticsFourthOrderSameLocationNoWaitA1a()
    {
        $seconds = 50;
        $now1 = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now1->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now1->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now1, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now1, 10);

        // Chipotle
        $o1a = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1a->lat = 34.0284;
        $o1a->lon = -118.287;
        $o1a->save();

        // Chipotle
        $o1b = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1b->lat = 34.0284;
        $o1b->lon = -118.287;
        $o1b->save();

        // Chipotle
        $o1c = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1c->lat = 34.0284;
        $o1c->lon = -118.287;
        $o1c->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $oa1a = new Order_Action([
            'id_order' => $o1a->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1a->save();
        $this->associateOrderActionToOrder($o1a, $oa1a);

        $oa1b = new Order_Action([
            'id_order' => $o1b->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1b->save();
        $this->associateOrderActionToOrder($o1b, $oa1b);

        $oa1c = new Order_Action([
            'id_order' => $o1c->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1c->save();
        $this->associateOrderActionToOrder($o1c, $oa1c);


        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1a->delete();
        $o1b->delete();
        $o1c->delete();
        $o2->delete();
        $oa1a->delete();
        $oa1b->delete();
        $oa1c->delete();
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 0);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 3);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 9);
        $this->assertEquals($olr2->count(), 3);
    }

    // Two drivers - driver 1 has an accepted order from Chipotle from within the time window(with no waiting time).
    //  Both drivers are at the same location
    //  New Chipotle order comes in - driver 1 should get priority
    //  New customer in the same location
    public function testLogisticsSecondOrderSameLocationNoWaitA2a()
    {
        $seconds = 50;
        $now1 = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now1->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now1->format('Y-m-d H:i:s');

        $minutes = 8;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        $minutes = 5;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate3 = $now->format('Y-m-d H:i:s');


        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now1, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now1, 10);

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate3,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $o2->delete();
        $oa1->delete();
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 0);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 5);
        $this->assertEquals($olr2->count(), 3);
    }


    // Two drivers - driver 1 has an accepted order from Chipotle from within the time window(with no waiting time).
    //  Both drivers are at the same location
    //  New Chipotle order comes in - driver 1 should get priority
    //  New customer in a different location, but reasonably close to first customer
    public function testLogisticsSecondOrderSameLocationNoWaitA2b()
    {
        $seconds = 50;
        $now1 = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now1->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now1->format('Y-m-d H:i:s');

        $minutes = 8;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        $minutes = 5;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate3 = $now->format('Y-m-d H:i:s');


        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now1, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now1, 10);

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
//        $o2->lat = 34.026562;
//        $o2->lon = -118.272312;
        $o2->lat = 34.027368;
        $o2->lon = -118.291552;
        $o2->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate3,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $o2->delete();
        $oa1->delete();
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 0);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 5);
        $this->assertEquals($olr2->count(), 3);
    }

    // Two drivers - driver 1 has an accepted order from Chipotle from within the time window(with no waiting time).
    //  Both drivers are at the same location
    //  New Chipotle order comes in - driver 2 should get priority
    //  New customer in a different location, about a mile away from the first customer
    public function testLogisticsSecondOrderSameLocationNoWaitA2c()
    {
        $seconds = 0;
        $now1 = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now1->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now1->format('Y-m-d H:i:s');

        $minutes = 8;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        $minutes = 5;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate3 = $now->format('Y-m-d H:i:s');


        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now1, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now1, 10);

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.026562;
        $o2->lon = -118.272312;
        $o2->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate3,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $o2->delete();
        $oa1->delete();
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 0);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 5);
        $this->assertEquals($olr2->count(), 3);
    }


    // Two drivers - driver 1 has an accepted order from Chipotle from within the time window(with no waiting time).
    //  Both drivers are at different locations, based on action algo
    //  New Chipotle order comes in - driver 1 should get priority - slight advantage due to location
    //  New customer in a different location, about a mile away from the first customer
    public function testLogisticsSecondOrderDiffLocationNoWaitA2c()
    {
        $seconds = 50;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $minutes = 8;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        $minutes = 5;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate3 = $now->format('Y-m-d H:i:s');


        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.026562;
        $o2->lon = -118.272312;
        $o2->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate3,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $o2->delete();
        $oa1->delete();

        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 0);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 5);
        $this->assertEquals($olr2->count(), 3);
    }

    // Two drivers - driver 1 has two accepted orders from Chipotle from within the time window(with no waiting time).
    //  Both drivers are at the same location
    //  New Chipotle order comes in - driver 1 should get priority
    public function testLogisticsThirdOrderSameLocationNoWaitA2a()
    {
        $seconds = 50;
        $now1 = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now1->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now1->format('Y-m-d H:i:s');

        $minutes = 8;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        $minutes = 5;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate3 = $now->format('Y-m-d H:i:s');


        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now1, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now1, 10);

        // Chipotle
        $o1a = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1a->lat = 34.0284;
        $o1a->lon = -118.287;
        $o1a->save();

        $o1b = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1b->lat = 34.0284;
        $o1b->lon = -118.287;
        $o1b->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $oa1a = new Order_Action([
            'id_order' => $o1a->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate3,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1a->save();
        $this->associateOrderActionToOrder($o1a, $oa1a);

        $oa1b = new Order_Action([
            'id_order' => $o1b->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate3,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1b->save();
        $this->associateOrderActionToOrder($o1b, $oa1b);

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1a->delete();
        $o1b->delete();
        $o2->delete();
        $oa1a->delete();
        $oa1b->delete();
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 2);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 0);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 7);
        $this->assertEquals($olr2->count(), 3);
    }



    // Two drivers - driver 1 has an accepted order from Chipotle from within the time window(with no waiting time).
    //  Both drivers are at the same location
    //  New Chipotle order comes in - driver 1 should get priority
    public function testLogisticsSecondOrderSameLocationNoWaitA3()
    {
        $seconds = 50;
        $now1 = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now1->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now1->format('Y-m-d H:i:s');

        $minutes = 10;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        $minutes = 9;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate3 = $now->format('Y-m-d H:i:s');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now1, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now1, 10);

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate3,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $o2->delete();
        $oa1->delete();
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 0);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 5);
        $this->assertEquals($olr2->count(), 3);
    }

    // Two drivers - driver 1 has an accepted order from Chipotle from outside the time window
    //  Both drivers are at the same location
    //  New Chipotle order comes in - driver 2 should get priority
    public function testLogisticsSecondOrderSameLocationNoWaitA4()
    {
        $seconds = 50;
        $now1 = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now1->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now1->format('Y-m-d H:i:s');

        $minutes = 12;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        $minutes = 11;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate3 = $now->format('Y-m-d H:i:s');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now1, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now1, 10);

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate3,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $o2->delete();
        $oa1->delete();
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 0);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 5);
        $this->assertEquals($olr2->count(), 3);
    }

    // Two drivers - driver 1 has a picked up order from Chipotle (with no waiting time).
    //  Both drivers are at the same location
    //  New Chipotle order comes in - driver 2 should get priority
    public function testLogisticsSecondOrderSameLocationNoWaitA5()
    {
        $seconds = 50;
        $now1 = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now1->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now1->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now1, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now1, 10);

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-pickedup',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $o2->delete();
        $oa1->delete();
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 0);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 5);
        $this->assertEquals($olr2->count(), 3);
    }


    // Two drivers - driver 1 has an accepted order from Chipotle from outside the time window
    //  Driver 2 has an accepted order from Chipotle from even earlier outside the time window
    //  New Chipotle order comes in - driver 1 will get priority still due to bundling
    public function testLogisticsSecondOrderSameRestaurantBusySecondDriverA1a()
    {
        $seconds = 50;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $minutes = 12;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        $minutes = 11;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate3 = $now->format('Y-m-d H:i:s');

        $minutes = 40;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate4 = $now->format('Y-m-d H:i:s');

        $minutes = 30;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate5 = $now->format('Y-m-d H:i:s');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

//        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now, 10);
//        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now, 10);

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();

        // Chipotle
        $o3 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate4, $this->community);
        $o3->lat = 34.0284;
        $o3->lon = -118.287;
        $o3->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate3,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $oa2 = new Order_Action([
            'id_order' => $o3->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate5,
            'type' => 'delivery-pickedup',
            'note' => ''
        ]);
        $oa2->save();
        $this->associateOrderActionToOrder($o3, $oa2);


        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $o2->delete();
        $oa1->delete();
        $oa2->delete();

        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 5);
        $this->assertEquals($olr2->count(), 5);
    }


    // Two drivers - driver 1 has an accepted order from Chipotle from outside the time window
    //  Driver 2 has an accepted order from Chipotle from even earlier outside the time window
    //  New Chipotle order comes in - driver 2 will get priority because other 2nd customer is a distance away
    public function testLogisticsSecondOrderSameRestaurantBusySecondDriverA1b()
    {
        $seconds = 50;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $minutes = 12;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        $minutes = 11;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate3 = $now->format('Y-m-d H:i:s');

        $minutes = 40;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate4 = $now->format('Y-m-d H:i:s');

        $minutes = 30;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate5 = $now->format('Y-m-d H:i:s');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

//        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now, 10);
//        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now, 10);

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.00;
        $o2->lon = -118.282747;
        $o2->save();

        // Chipotle
        $o3 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate4, $this->community);
        $o3->lat = 34.0284;
        $o3->lon = -118.287;
        $o3->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate3,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $oa2 = new Order_Action([
            'id_order' => $o3->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate5,
            'type' => 'delivery-pickedup',
            'note' => ''
        ]);
        $oa2->save();
        $this->associateOrderActionToOrder($o3, $oa2);


        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $o2->delete();
        $oa1->delete();
        $oa2->delete();

        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 5);
        $this->assertEquals($olr2->count(), 5);
    }


    // Two drivers - driver 1 has an accepted order from Chipotle from inside the time window
    //  Driver 2 has an accepted order from Chipotle from outside the time window
    //  New Chipotle order comes in - driver 1 should get priority
    public function testLogisticsSecondOrderSameRestaurantBusySecondDriverA2()
    {
        $seconds = 50;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $minutes = 10;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        $minutes = 5;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate3 = $now->format('Y-m-d H:i:s');

        $minutes = 40;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate4 = $now->format('Y-m-d H:i:s');

        $minutes = 30;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate5 = $now->format('Y-m-d H:i:s');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

//        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now, 10);
//        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now, 10);

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();

        // Chipotle
        $o3 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate4, $this->community);
        $o3->lat = 34.0284;
        $o3->lon = -118.287;
        $o3->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate3,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $oa2 = new Order_Action([
            'id_order' => $o3->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate5,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa2->save();
        $this->associateOrderActionToOrder($o3, $oa2);


        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $o2->delete();
        $oa1->delete();
        $oa2->delete();

        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 5);
        $this->assertEquals($olr2->count(), 5);
    }

    // Two drivers - driver 1 has an accepted order from Chipotle from inside the time window
    //  Driver 2 has an accepted order from Chipotle from outside the time window
    //  New Chipotle order comes in - driver 1 should get priority
    public function testLogisticsSecondOrderSameRestaurantBusySecondDriverA3()
    {
        $seconds = 50;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $minutes = 10;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        $minutes = 5;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate3 = $now->format('Y-m-d H:i:s');

        $minutes = 40;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate4 = $now->format('Y-m-d H:i:s');

        $minutes = 30;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate5 = $now->format('Y-m-d H:i:s');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

//        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now, 10);
//        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now, 10);

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();

        // Chipotle
        $o3 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate4, $this->community);
        $o3->lat = 34.0284;
        $o3->lon = -118.287;
        $o3->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate3,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $oa2 = new Order_Action([
            'id_order' => $o3->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate5,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa2->save();
        $this->associateOrderActionToOrder($o3, $oa2);


        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $o2->delete();
        $oa1->delete();
        $oa2->delete();

        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 5);
        $this->assertEquals($olr2->count(), 5);
    }

    // Two drivers - driver 1 has an accepted order from Chipotle from inside the time window
    //  Driver 2 has an accepted order from Chipotle from outside the time window
    //  New Chipotle order comes in - driver 1 should get priority
    public function testLogisticsSecondOrderSameRestaurantBusySecondDriverA4()
    {
        $seconds = 50;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $minutes = 10;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        $minutes = 5;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate3 = $now->format('Y-m-d H:i:s');

        $minutes = 40;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate4 = $now->format('Y-m-d H:i:s');

        $minutes = 30;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate5 = $now->format('Y-m-d H:i:s');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

//        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now, 10);
//        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now, 10);

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();

        // Chipotle
        $o3 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate4, $this->community);
        $o3->lat = 34.0284;
        $o3->lon = -118.287;
        $o3->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate3,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $oa2 = new Order_Action([
            'id_order' => $o3->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate5,
            'type' => 'delivery-pickedup',
            'note' => ''
        ]);
        $oa2->save();
        $this->associateOrderActionToOrder($o3, $oa2);


        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $o2->delete();
        $oa1->delete();
        $oa2->delete();

        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 5);
        $this->assertEquals($olr2->count(), 5);
    }


    // Two drivers - driver 1 has an accepted order from Chipotle from inside the time window
    //  Driver 2 has an accepted order from Chipotle from outside the time window
    //  New Chipotle order comes in - driver 1 should get priority
    public function testLogisticsSecondOrderSameRestaurantBusySecondDriverA5()
    {
        $seconds = 50;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $minutes = 12;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        $minutes = 10;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate3 = $now->format('Y-m-d H:i:s');

        $minutes = 40;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate4 = $now->format('Y-m-d H:i:s');

        $minutes = 30;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate5 = $now->format('Y-m-d H:i:s');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

//        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now, 10);
//        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now, 10);

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();

        // Chipotle
        $o3 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate4, $this->community);
        $o3->lat = 34.0284;
        $o3->lon = -118.287;
        $o3->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate3,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $oa2 = new Order_Action([
            'id_order' => $o3->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate5,
            'type' => 'delivery-pickedup',
            'note' => ''
        ]);
        $oa2->save();
        $this->associateOrderActionToOrder($o3, $oa2);


        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $o2->delete();
        $oa1->delete();
        $oa2->delete();

        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 5);
        $this->assertEquals($olr2->count(), 5);
    }

    // Two drivers - driver 1 has an accepted order from Chipotle from inside the time window
    //  Driver 2 has an accepted order from Chipotle from outside the time window
    //  New McDs order comes in - driver 2 should get priority
    public function testLogisticsSecondOrderDifferentRestaurantBusySecondDriverA1()
    {
        $seconds = 50;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $minutes = 10;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        $minutes = 5;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate3 = $now->format('Y-m-d H:i:s');

        $minutes = 40;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate4 = $now->format('Y-m-d H:i:s');

        $minutes = 30;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate5 = $now->format('Y-m-d H:i:s');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

//        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now, 10);
//        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now, 10);

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        // McDs
        $o2 = $this->defaultOrder($this->user, $this->restaurant4->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();

        // Chipotle
        $o3 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate4, $this->community);
        $o3->lat = 34.0284;
        $o3->lon = -118.287;
        $o3->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $olp2 = $this->defaultOLP($this->restaurant4, $start, $end, 5, 5, 5, $dow);
        $olp2->save();

        $ols2 = $this->defaultOLS($this->restaurant4, $start, $end, 5, 5, 5, $dow);
        $ols2->save();

        $olot2 = $this->defaultOLOT($this->restaurant4, $start, $end, 0, 1, $dow);
        $olot2->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate3,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $oa2 = new Order_Action([
            'id_order' => $o3->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate5,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa2->save();
        $this->associateOrderActionToOrder($o3, $oa2);


        $olc1 = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc1->save();

        $olc2 = $this->defaultOLC($this->restaurant4, $dow, $start, $end, $this->restaurant4->id_restaurant);
        $olc2->save();


        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $o2->delete();
        $oa1->delete();
        $oa2->delete();

        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc1->delete();
        $olp2->delete();
        $ols2->delete();
        $olot2->delete();
        $olc2->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 5);
        $this->assertEquals($olr2->count(), 5);
    }


    // Two drivers - driver 1 has an accepted order from Chipotle from inside the time window
    //  Driver 2 has a pickedup order from Chipotle from outside the time window
    //  New McDs order comes in - driver 2 should get priority
    public function testLogisticsSecondOrderDifferentRestaurantBusySecondDriverA2()
    {
        $seconds = 50;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $minutes = 10;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        $minutes = 5;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate3 = $now->format('Y-m-d H:i:s');

        $minutes = 40;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate4 = $now->format('Y-m-d H:i:s');

        $minutes = 30;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate5 = $now->format('Y-m-d H:i:s');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

//        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now, 10);
//        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now, 10);

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        // McDs
        $o2 = $this->defaultOrder($this->user, $this->restaurant4->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();

        // Chipotle
        $o3 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate4, $this->community);
        $o3->lat = 34.0284;
        $o3->lon = -118.287;
        $o3->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $olp2 = $this->defaultOLP($this->restaurant4, $start, $end, 5, 5, 5, $dow);
        $olp2->save();

        $ols2 = $this->defaultOLS($this->restaurant4, $start, $end, 5, 5, 5, $dow);
        $ols2->save();

        $olot2 = $this->defaultOLOT($this->restaurant4, $start, $end, 0, 1, $dow);
        $olot2->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate3,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $oa2 = new Order_Action([
            'id_order' => $o3->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate5,
            'type' => 'delivery-pickedup',
            'note' => ''
        ]);
        $oa2->save();
        $this->associateOrderActionToOrder($o3, $oa2);


        $olc1 = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc1->save();

        $olc2 = $this->defaultOLC($this->restaurant4, $dow, $start, $end, $this->restaurant4->id_restaurant);
        $olc2->save();


        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $o2->delete();
        $oa1->delete();
        $oa2->delete();

        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc1->delete();
        $olp2->delete();
        $ols2->delete();
        $olot2->delete();
        $olc2->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 5);
        $this->assertEquals($olr2->count(), 5);
    }


    // Two drivers - driver 1 has an accepted order from Chipotle from inside the time window
    //  Driver 2 has no orders
    //  New McDs order comes in - driver 2 should get priority
    public function testLogisticsSecondOrderDifferentRestaurantFreeSecondDriverA1()
    {
        $seconds = 50;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $minutes = 10;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        $minutes = 5;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate3 = $now->format('Y-m-d H:i:s');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

//        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now, 10);
//        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now, 10);

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        // McDs
        $o2 = $this->defaultOrder($this->user, $this->restaurant4->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $olp2 = $this->defaultOLP($this->restaurant4, $start, $end, 5, 5, 5, $dow);
        $olp2->save();

        $ols2 = $this->defaultOLS($this->restaurant4, $start, $end, 5, 5, 5, $dow);
        $ols2->save();

        $olot2 = $this->defaultOLOT($this->restaurant4, $start, $end, 0, 1, $dow);
        $olot2->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate3,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $olc1 = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc1->save();

        $olc2 = $this->defaultOLC($this->restaurant4, $dow, $start, $end, $this->restaurant4->id_restaurant);
        $olc2->save();


        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $o2->delete();
        $oa1->delete();

        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc1->delete();
        $olp2->delete();
        $ols2->delete();
        $olot2->delete();
        $olc2->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 0);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 5);
        $this->assertEquals($olr2->count(), 3);
    }


    // Two drivers - driver 1 has two accepted orders from Chipotle from inside the time window
    //  Driver 2 has no orders
    //  New McDs order comes in - driver 2 should get priority
    public function testLogisticsSecondOrderDifferentRestaurantFreeSecondDriverA2()
    {
        $seconds = 50;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $minutes = 10;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        $minutes = 5;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $useDate3 = $now->format('Y-m-d H:i:s');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

//        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now, 10);
//        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now, 10);

        // Chipotle
        $o1a = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1a->lat = 34.0284;
        $o1a->lon = -118.287;
        $o1a->save();

        $o1b = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1b->lat = 34.0284;
        $o1b->lon = -118.287;
        $o1b->save();

        // McDs
        $o2 = $this->defaultOrder($this->user, $this->restaurant4->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $olp2 = $this->defaultOLP($this->restaurant4, $start, $end, 5, 5, 5, $dow);
        $olp2->save();

        $ols2 = $this->defaultOLS($this->restaurant4, $start, $end, 5, 5, 5, $dow);
        $ols2->save();

        $olot2 = $this->defaultOLOT($this->restaurant4, $start, $end, 0, 1, $dow);
        $olot2->save();

        $oa1a = new Order_Action([
            'id_order' => $o1a->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate3,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1a->save();
        $this->associateOrderActionToOrder($o1a, $oa1a);

        $oa1b = new Order_Action([
            'id_order' => $o1b->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate3,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1b->save();
        $this->associateOrderActionToOrder($o1b, $oa1b);

        $olc1 = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc1->save();

        $olc2 = $this->defaultOLC($this->restaurant4, $dow, $start, $end, $this->restaurant4->id_restaurant);
        $olc2->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1a->delete();
        $o1b->delete();
        $o2->delete();
        $oa1a->delete();
        $oa1b->delete();

        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc1->delete();
        $olp2->delete();
        $ols2->delete();
        $olot2->delete();
        $olc2->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 0);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 2);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 7);
        $this->assertEquals($olr2->count(), 3);
    }

    // Two drivers - driver 1 has accepted, refunded (do_not_reimburse_driver=true) order from Chipotle (with no waiting time).
    //  Both drivers are at the same location
    //  New Chipotle order comes in - no driver should get priority
    public function testLogisticsSecondOrderSameLocationNoWaitB1()
    {
        $seconds = 50;
        $now1 = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now1->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now1->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now1, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now1, 10);

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->refunded = true;
        $o1->do_not_reimburse_driver = true;
        $o1->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $o2->delete();
        $oa1->delete();
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            $this->assertEquals($driver->__seconds, 0);
            $this->assertEquals($driver->__priority, true);
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            $this->assertEquals($op->num_undelivered_orders, 0);
            $this->assertEquals($op->num_drivers_with_priority, 2);
        }
        $this->assertEquals($ol->numDriversWithPriority, 2);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 3);
        $this->assertEquals($olr2->count(), 3);
    }

    // Two drivers - driver 1 has accepted, refunded (do_not_reimburse_driver=false) order from Chipotle (with no waiting time).
    //  Both drivers are at the same location
    //  New Chipotle order comes in - driver 1 should get priority because he's still delivering the Chipotle order
    public function testLogisticsSecondOrderSameLocationNoWaitB2()
    {
        $seconds = 50;
        $now1 = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now1->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now1->format('Y-m-d H:i:s');

        $seconds = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');
        $dow = $now->format('w');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $now1, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $now1, 10);

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate2, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->refunded = true;
        $o1->do_not_reimburse_driver = false;
        $o1->save();

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $this->associateOrderActionToOrder($o1, $oa1);

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $o2->delete();
        $oa1->delete();
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops->count(), 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($op->num_undelivered_orders, 1);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->num_undelivered_orders, 0);
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 5);
        $this->assertEquals($olr2->count(), 3);
    }

    // Two drivers - driver 1 has an unaccepted priority order from Chipotle (with no waiting time).
    //  Both drivers are at the same location
    //  New Chipotle order comes in - driver 1 should get priority due to bundling.
    public function testLogisticsSecondOrderSameLocationNoWait2a()
    {
        $orders = [];
        $allops = [];
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));

        $seconds = 50;
        $earlier50 = clone $now;
        $earlier50->modify('- ' . $seconds . ' seconds');
        $earlier50String = $earlier50->format('Y-m-d H:i:s');

        $seconds = 120;
        $earlier120 = clone $now;
        $earlier120->modify('- ' . $seconds . ' seconds');
        $earlier120String = $earlier120->format('Y-m-d H:i:s');

        $dow = $now->format('w');

        $this->assertGreaterThan(50, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
        $seconds = Crunchbutton_Order_Logistics::TIME_MAX_DELAY - 50;

        $laterM50PlusMax = clone $now;
        $laterM50PlusMax->modify('+ ' . $seconds . ' seconds');
        $laterM50PlusMaxString = $laterM50PlusMax->format('Y-m-d H:i:s');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $earlier120, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $earlier120, 10);

        $chipotle_lat = 34.0284;
        $chipotle_lon = -118.287;

        // Chipotle
        $og = $this->createOrderGroupAndSave($this->user, $this->restaurant3, $now, 70,
            $this->community, $chipotle_lat, $chipotle_lon,
            [$this->driver1, $this->driver2],
            [Crunchbutton_Order_Priority::PRIORITY_HIGH, Crunchbutton_Order_Priority::PRIORITY_LOW], [0, 0]);
        $orders[] = $og['o'];
        $allops = array_merge($allops, $og['ops']);

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $earlier50String, $this->community);
        $o2->lat = $chipotle_lat;
        $o2->lon = $chipotle_lon;
        $o2->save();
        $orders[] = $o2;

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $drivers = $ol->drivers();

        $ops2 = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        foreach ($allops as $op) {
            $op->delete();
        }
        foreach ($orders as $o) {
            $o->delete();
        }
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();

//        foreach ($ol->drivers() as $driver) {
//            $scoreDiff = $driver->__scoreChange;
//            if ($driver->id_admin == $this->driver1->id_admin) {
//                print "Driver 1 score diff: $scoreDiff\n";
//            } else {
//                print "Driver 2 score diff: $scoreDiff\n";
//            }
//        }

        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops2->count(), 2);

        foreach ($ops2 as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_undelivered_orders, 0);
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 5);
        $this->assertEquals($olr2->count(), 3);

    }


    // Two drivers - driver 1 has an unaccepted priority order from Chipotle (with no waiting time).
    //  Both drivers are at the same location
    //  New McDs order comes in - driver 2 should get priority
    public function testLogisticsSecondOrderSameLocationDifferentRestaurantNoWait1()
    {
        $orders = [];
        $allops = [];
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));

        $seconds = 50;
        $earlier50 = clone $now;
        $earlier50->modify('- ' . $seconds . ' seconds');
        $earlier50String = $earlier50->format('Y-m-d H:i:s');

        $seconds = 120;
        $earlier120 = clone $now;
        $earlier120->modify('- ' . $seconds . ' seconds');
        $earlier120String = $earlier120->format('Y-m-d H:i:s');

        $dow = $now->format('w');

        $this->assertGreaterThan(50, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
        $seconds = Crunchbutton_Order_Logistics::TIME_MAX_DELAY - 50;

        $laterM50PlusMax = clone $now;
        $laterM50PlusMax->modify('+ ' . $seconds . ' seconds');
        $laterM50PlusMaxString = $laterM50PlusMax->format('Y-m-d H:i:s');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $earlier120, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $earlier120, 10);

        $customer_lat = 34.0284;
        $customer_lon = -118.287;

        // Chipotle
        $og = $this->createOrderGroupAndSave($this->user, $this->restaurant3, $now, 70,
            $this->community, $customer_lat, $customer_lon,
            [$this->driver1, $this->driver2],
            [Crunchbutton_Order_Priority::PRIORITY_HIGH, Crunchbutton_Order_Priority::PRIORITY_LOW], [0, 0]);
        $orders[] = $og['o'];
        $allops = array_merge($allops, $og['ops']);

        // McD
        $o2 = $this->defaultOrder($this->user, $this->restaurant4->id_restaurant, $earlier50String, $this->community);
        $o2->lat = $customer_lat;
        $o2->lon = $customer_lon;
        $o2->save();
        $orders[] = $o2;

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $olp2 = $this->defaultOLP($this->restaurant4, $start, $end, 5, 5, 5, $dow);
        $olp2->save();

        $ols2 = $this->defaultOLS($this->restaurant4, $start, $end, 5, 5, 5, $dow);
        $ols2->save();

        $olot2 = $this->defaultOLOT($this->restaurant4, $start, $end, 0, 1, $dow);
        $olot2->save();

        $olcs2 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs2->save();


        $olc1 = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc1->save();

        $olc2 = $this->defaultOLC($this->restaurant4, $dow, $start, $end, $this->restaurant4->id_restaurant);
        $olc2->save();


        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2, null,
            Crunchbutton_Optimizer_Input::DISTANCE_LATLON);
        $ol->process();

        $drivers = $ol->drivers();

        $ops2 = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        foreach ($allops as $op) {
            $op->delete();
        }
        foreach ($orders as $o) {
            $o->delete();
        }
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc1->delete();

        $olp2->delete();
        $ols2->delete();
        $olot2->delete();
        $olcs2->delete();
        $olc2->delete();

        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops2->count(), 2);

        foreach ($ops2 as $op) {
            if ($op->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_undelivered_orders, 0);
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 5);
        $this->assertEquals($olr2->count(), 3);

    }


    // Two drivers - driver 1 has an unaccepted priority order from Chipotle (with no waiting time).
    //  Both drivers are at the same location
    //  New McDs order comes in - driver 2 should get priority
    public function testLogisticsSecondOrderDifferentLocationDifferentRestaurantNoWait1()
    {
        $orders = [];
        $allops = [];
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));

        $seconds = 50;
        $earlier50 = clone $now;
        $earlier50->modify('- ' . $seconds . ' seconds');
        $earlier50String = $earlier50->format('Y-m-d H:i:s');

        $seconds = 120;
        $earlier120 = clone $now;
        $earlier120->modify('- ' . $seconds . ' seconds');
        $earlier120String = $earlier120->format('Y-m-d H:i:s');

        $dow = $now->format('w');

        $this->assertGreaterThan(50, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
        $seconds = Crunchbutton_Order_Logistics::TIME_MAX_DELAY - 50;

        $laterM50PlusMax = clone $now;
        $laterM50PlusMax->modify('+ ' . $seconds . ' seconds');
        $laterM50PlusMaxString = $laterM50PlusMax->format('Y-m-d H:i:s');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $customer_lat = 34.0284;
        $customer_lon = -118.287;

        // Chipotle
        $og = $this->createOrderGroupAndSave($this->user, $this->restaurant3, $now, 70,
            $this->community, $customer_lat, $customer_lon,
            [$this->driver1, $this->driver2],
            [Crunchbutton_Order_Priority::PRIORITY_HIGH, Crunchbutton_Order_Priority::PRIORITY_LOW], [0, 0]);
        $orders[] = $og['o'];
        $allops = array_merge($allops, $og['ops']);

        // McD
        $o2 = $this->defaultOrder($this->user, $this->restaurant4->id_restaurant, $earlier50String, $this->community);
        $o2->lat = $customer_lat;
        $o2->lon = $customer_lon;
        $o2->save();
        $orders[] = $o2;

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $olp2 = $this->defaultOLP($this->restaurant4, $start, $end, 5, 5, 5, $dow);
        $olp2->save();

        $ols2 = $this->defaultOLS($this->restaurant4, $start, $end, 5, 5, 5, $dow);
        $ols2->save();

        $olot2 = $this->defaultOLOT($this->restaurant4, $start, $end, 0, 1, $dow);
        $olot2->save();

        $olcs2 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs2->save();


        $olc1 = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc1->save();

        $olc2 = $this->defaultOLC($this->restaurant4, $dow, $start, $end, $this->restaurant4->id_restaurant);
        $olc2->save();


        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2, null,
            Crunchbutton_Optimizer_Input::DISTANCE_LATLON);
        $ol->process();

        $drivers = $ol->drivers();

        $ops2 = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        foreach ($allops as $op) {
            $op->delete();
        }
        foreach ($orders as $o) {
            $o->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc1->delete();

        $olp2->delete();
        $ols2->delete();
        $olot2->delete();
        $olcs2->delete();
        $olc2->delete();

        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops2->count(), 2);

        foreach ($ops2 as $op) {
            if ($op->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_undelivered_orders, 0);
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 5);
        $this->assertEquals($olr2->count(), 3);

    }

    // Two drivers - driver 1 has an unaccepted, refunded (do_not_reimburse_driver=true) priority order from Chipotle (with no waiting time).
    //  Both drivers are at the same location
    //  New Chipotle order comes in - both drivers get high priority
    public function testLogisticsSecondOrderSameLocationNoWait2b()
    {
        $orders = [];
        $allops = [];
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));

        $seconds = 50;
        $earlier50 = clone $now;
        $earlier50->modify('- ' . $seconds . ' seconds');
        $earlier50String = $earlier50->format('Y-m-d H:i:s');

        $seconds = 120;
        $earlier120 = clone $now;
        $earlier120->modify('- ' . $seconds . ' seconds');
        $earlier120String = $earlier120->format('Y-m-d H:i:s');

        $dow = $now->format('w');

        $this->assertGreaterThan(50, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
        $seconds = Crunchbutton_Order_Logistics::TIME_MAX_DELAY - 50;

        $laterM50PlusMax = clone $now;
        $laterM50PlusMax->modify('+ ' . $seconds . ' seconds');
        $laterM50PlusMaxString = $laterM50PlusMax->format('Y-m-d H:i:s');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $earlier120, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $earlier120, 10);

        $chipotle_lat = 34.0284;
        $chipotle_lon = -118.287;

        // Chipotle
        $og = $this->createOrderGroupAndSave($this->user, $this->restaurant3, $now, 70,
            $this->community, $chipotle_lat, $chipotle_lon,
            [$this->driver1, $this->driver2],
            [Crunchbutton_Order_Priority::PRIORITY_HIGH, Crunchbutton_Order_Priority::PRIORITY_LOW], [0, 0]);
        $orders[] = $og['o'];
        $og['o']->refunded = 1;
        $og['o']->do_not_reimburse_driver = 1;
        $og['o']->save();
        $allops = array_merge($allops, $og['ops']);

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $earlier50String, $this->community);
        $o2->lat = $chipotle_lat;
        $o2->lon = $chipotle_lon;
        $o2->save();
        $orders[] = $o2;

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $drivers = $ol->drivers();

        $ops2 = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        foreach ($allops as $op) {
            $op->delete();
        }
        foreach ($orders as $o) {
            $o->delete();
        }
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();

        foreach ($ol->drivers() as $driver) {
            $this->assertEquals($driver->__seconds, 0);
            $this->assertEquals($driver->__priority, true);
        }
        $this->assertEquals($ops2->count(), 2);

        foreach ($ops2 as $op) {
            $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            $this->assertEquals($op->num_undelivered_orders, 0);
            $this->assertEquals($op->num_drivers_with_priority, 2);
        }
        $this->assertEquals($ol->numDriversWithPriority, 2);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 3);
        $this->assertEquals($olr2->count(), 3);
    }

    // Two drivers - driver 1 has an unaccepted, refunded (do_not_reimburse_driver=false) priority order from Chipotle (with no waiting time).
    //  Both drivers are at the same location
    //  New Chipotle order comes in - driver 1 should get priority due to bundling.
    public function testLogisticsSecondOrderSameLocationNoWait2c()
    {
        $orders = [];
        $allops = [];
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));

        $seconds = 50;
        $earlier50 = clone $now;
        $earlier50->modify('- ' . $seconds . ' seconds');
        $earlier50String = $earlier50->format('Y-m-d H:i:s');

        $seconds = 120;
        $earlier120 = clone $now;
        $earlier120->modify('- ' . $seconds . ' seconds');
        $earlier120String = $earlier120->format('Y-m-d H:i:s');

        $dow = $now->format('w');

        $this->assertGreaterThan(50, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
        $seconds = Crunchbutton_Order_Logistics::TIME_MAX_DELAY - 50;

        $laterM50PlusMax = clone $now;
        $laterM50PlusMax->modify('+ ' . $seconds . ' seconds');
        $laterM50PlusMaxString = $laterM50PlusMax->format('Y-m-d H:i:s');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $earlier120, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $earlier120, 10);

        $chipotle_lat = 34.0284;
        $chipotle_lon = -118.287;

        // Chipotle
        $og = $this->createOrderGroupAndSave($this->user, $this->restaurant3, $now, 70,
            $this->community, $chipotle_lat, $chipotle_lon,
            [$this->driver1, $this->driver2],
            [Crunchbutton_Order_Priority::PRIORITY_HIGH, Crunchbutton_Order_Priority::PRIORITY_LOW], [0, 0]);
        $orders[] = $og['o'];
        $og['o']->refunded = 1;
        $og['o']->do_not_reimburse_driver = false;
        $og['o']->save();
        $allops = array_merge($allops, $og['ops']);

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $earlier50String, $this->community);
        $o2->lat = $chipotle_lat;
        $o2->lon = $chipotle_lon;
        $o2->save();
        $orders[] = $o2;

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $drivers = $ol->drivers();

        $ops2 = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        foreach ($allops as $op) {
            $op->delete();
        }
        foreach ($orders as $o) {
            $o->delete();
        }
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();

        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops2->count(), 2);

        foreach ($ops2 as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_undelivered_orders, 0);
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 5);
        $this->assertEquals($olr2->count(), 3);
    }

    // Two drivers - driver 1 has an unaccepted, expired priority order from Chipotle (with no waiting time).
    //  Both drivers are at the same location
    //  New Chipotle order comes in - both drivers get high priority
    public function testLogisticsSecondOrderSameLocationNoWait3()
    {
        $orders = [];
        $allops = [];
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));

        $seconds = 50;
        $earlier50 = clone $now;
        $earlier50->modify('- ' . $seconds . ' seconds');
        $earlier50String = $earlier50->format('Y-m-d H:i:s');

        $seconds = 120;
        $earlier120 = clone $now;
        $earlier120->modify('- ' . $seconds . ' seconds');

        $dow = $now->format('w');

        $this->assertGreaterThan(50, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
        $seconds = Crunchbutton_Order_Logistics::TIME_MAX_DELAY - 50;

        $laterM50PlusMax = clone $now;
        $laterM50PlusMax->modify('+ ' . $seconds . ' seconds');
        $laterM50PlusMaxString = $laterM50PlusMax->format('Y-m-d H:i:s');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $earlier120, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $earlier120, 10);

        $chipotle_lat = 34.0284;
        $chipotle_lon = -118.287;

        // Chipotle
        $og = $this->createOrderGroupAndSave($this->user, $this->restaurant3, $now, 200,
            $this->community, $chipotle_lat, $chipotle_lon,
            [$this->driver1, $this->driver2],
            [Crunchbutton_Order_Priority::PRIORITY_HIGH, Crunchbutton_Order_Priority::PRIORITY_LOW], [0, 0]);
        $orders[] = $og['o'];
        $allops = array_merge($allops, $og['ops']);

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $earlier50String, $this->community);
        $o2->lat = $chipotle_lat;
        $o2->lon = $chipotle_lon;
        $o2->save();
        $orders[] = $o2;

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $drivers = $ol->drivers();

        $ops2 = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);

        $olr1->delete();
        $olr2->delete();

        $this->driver3->active = true;
        $this->driver3->save();
        foreach ($allops as $op) {
            $op->delete();
        }
        foreach ($orders as $o) {
            $o->delete();
        }
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();

        foreach ($ol->drivers() as $driver) {
            $this->assertEquals($driver->__seconds, 0);
            $this->assertEquals($driver->__priority, true);
        }
        $this->assertEquals($ops2->count(), 2);

        foreach ($ops2 as $op) {
            $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            $this->assertEquals($op->num_undelivered_orders, 0);
            $this->assertEquals($op->num_drivers_with_priority, 2);
        }
        $this->assertEquals($ol->numDriversWithPriority, 2);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 3);
        $this->assertEquals($olr2->count(), 3);
    }


    //  Three drivers, none with orders
    //  All three drivers are at different locations
    //  New Chipotle order comes in - closest driver gets the priority
    public function testLogisticsThreeDriversNoOrdersNoWait()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $dow = $now->format('w');

        $seconds = 50;
        $earlier50 = clone $now;
        $earlier50->modify('- ' . $seconds . ' seconds');
        $earlier50String = $earlier50->format('Y-m-d H:i:s');

        $seconds = 120;
        $earlier120 = clone $now;
        $earlier120->modify('- ' . $seconds . ' seconds');
        $earlier120String = $earlier120->format('Y-m-d H:i:s');


        $this->assertGreaterThan(50, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
        $seconds = Crunchbutton_Order_Logistics::TIME_MAX_DELAY - 50;

        $laterM50PlusMax = clone $now;
        $laterM50PlusMax->modify('+ ' . $seconds . ' seconds');
        $laterM50PlusMaxString = $laterM50PlusMax->format('Y-m-d H:i:s');

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $earlier120, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.018, -118.281, $earlier120, 10);
        $driverLocs3 = $this->createAndSaveAdminLocations($this->driver3->id_admin, 34.0, -118.27, $earlier120, 10);

        $chipotle_lat = 34.0284;
        $chipotle_lon = -118.287;

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $earlier50String, $this->community);
        $o2->lat = $chipotle_lat;
        $o2->lon = $chipotle_lon;
        $o2->save();
        $orders[] = $o2;

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $drivers = $ol->drivers();

        $ops2 = Crunchbutton_Order_Priority::q('SELECT * FROM order_priority WHERE id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);
        $olr3 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver3->id_admin]);

        $olr1->delete();
        $olr2->delete();
        $olr3->delete();

        foreach ($orders as $o) {
            $o->delete();
        }
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        foreach ($driverLocs3 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();


        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops2->count(), 3);

        foreach ($ops2 as $op) {
            if ($op->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_undelivered_orders, 0);
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 3);
        $this->assertEquals($olr2->count(), 3);
        $this->assertEquals($olr3->count(), 3);
    }

    //  Three drivers, none with orders
    //  All three drivers are at the same location
    //  New Chipotle order comes in - all drivers get the priority
    public function testLogisticsThreeDriversSameLocationNoOrdersNoWait()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $dow = $now->format('w');

        $seconds = 50;
        $earlier50 = clone $now;
        $earlier50->modify('- ' . $seconds . ' seconds');
        $earlier50String = $earlier50->format('Y-m-d H:i:s');

        $seconds = 120;
        $earlier120 = clone $now;
        $earlier120->modify('- ' . $seconds . ' seconds');
        $earlier120String = $earlier120->format('Y-m-d H:i:s');

        $this->assertGreaterThan(50, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
        $seconds = Crunchbutton_Order_Logistics::TIME_MAX_DELAY - 50;

        $laterM50PlusMax = clone $now;
        $laterM50PlusMax->modify('+ ' . $seconds . ' seconds');
        $laterM50PlusMaxString = $laterM50PlusMax->format('Y-m-d H:i:s');

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0, -118.273, $earlier120, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0, -118.273, $earlier120, 10);
        $driverLocs3 = $this->createAndSaveAdminLocations($this->driver3->id_admin, 34.0, -118.273, $earlier120, 10);

        $chipotle_lat = 34.0284;
        $chipotle_lon = -118.287;

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $earlier50String, $this->community);
        $o2->lat = $chipotle_lat;
        $o2->lon = $chipotle_lon;
        $o2->save();
        $orders[] = $o2;

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $drivers = $ol->drivers();

        $ops2 = Crunchbutton_Order_Priority::q('SELECT * FROM order_priority WHERE id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);
        $olr3 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver3->id_admin]);

        $olr1->delete();
        $olr2->delete();
        $olr3->delete();

        foreach ($orders as $o) {
            $o->delete();
        }
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        foreach ($driverLocs3 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();


        foreach ($ol->drivers() as $driver) {
            $this->assertEquals($driver->__seconds, 0);
            $this->assertEquals($driver->__priority, true);

        }
        $this->assertEquals($ops2->count(), 3);

        foreach ($ops2 as $op) {
            $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            $this->assertEquals($op->num_undelivered_orders, 0);
            $this->assertEquals($op->num_drivers_with_priority, 3);
        }
        $this->assertEquals($ol->numDriversWithPriority, 3);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 3);
        $this->assertEquals($olr2->count(), 3);
        $this->assertEquals($olr3->count(), 3);
    }

    //  Three drivers, none with orders
    //  All three drivers are at the same location
    //  New Chipotle order comes in - all drivers get the priority
    public function testLogisticsThreeDriversSameLocationNoOrdersNoWaitOneNotYetInactive()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $dow = $now->format('w');

        $seconds = 50;
        $earlier50 = clone $now;
        $earlier50->modify('- ' . $seconds . ' seconds');
        $earlier50String = $earlier50->format('Y-m-d H:i:s');

        $seconds = 120;
        $earlier120 = clone $now;
        $earlier120->modify('- ' . $seconds . ' seconds');
        $earlier120String = $earlier120->format('Y-m-d H:i:s');

        $minutes = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $earlier120MinString = $now->format('Y-m-d H:i:s');

        $this->assertGreaterThan(50, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
        $seconds = Crunchbutton_Order_Logistics::TIME_MAX_DELAY - 50;

        $laterM50PlusMax = clone $now;
        $laterM50PlusMax->modify('+ ' . $seconds . ' seconds');
        $laterM50PlusMaxString = $laterM50PlusMax->format('Y-m-d H:i:s');

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0, -118.273, $earlier120, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0, -118.273, $earlier120, 10);
        $driverLocs3 = $this->createAndSaveAdminLocations($this->driver3->id_admin, 34.0, -118.273, $earlier120, 10);


        $o10 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $earlier120MinString, $this->community);
        $o10->save();

        $op10 = $this->defaultOrderPriority($o10, $this->restaurant1, $this->driver1,
            $earlier120MinString, Crunchbutton_Order_Priority::PRIORITY_HIGH, 0, $earlier120MinString, 0, 1);
        $op10->save();

        $chipotle_lat = 34.0284;
        $chipotle_lon = -118.287;


        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $earlier50String, $this->community);
        $o2->lat = $chipotle_lat;
        $o2->lon = $chipotle_lon;
        $o2->save();
        $orders[] = $o2;

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $drivers = $ol->drivers();

        $ops2 = Crunchbutton_Order_Priority::q('SELECT * FROM order_priority WHERE id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);
        $olr3 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver3->id_admin]);

        $olr1->delete();
        $olr2->delete();
        $olr3->delete();

        $op10->delete();
        $o10->delete();
        foreach ($orders as $o) {
            $o->delete();
        }
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        foreach ($driverLocs3 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();


        foreach ($ol->drivers() as $driver) {
            $this->assertEquals($driver->__seconds, 0);
            $this->assertEquals($driver->__priority, true);

        }
        $this->assertEquals($ops2->count(), 3);

        foreach ($ops2 as $op) {
            $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            $this->assertEquals($op->num_undelivered_orders, 0);
            $this->assertEquals($op->num_drivers_with_priority, 3);
        }
        $this->assertEquals($ol->numDriversWithPriority, 3);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 3);
        $this->assertEquals($olr2->count(), 3);
        $this->assertEquals($olr3->count(), 3);
    }

    //  Three drivers, none with orders
    //  All three drivers are at the same location
    //  New Chipotle order comes in - all drivers get the priority
    public function testLogisticsThreeDriversSameLocationNoOrdersNoWaitOneInactive()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $dow = $now->format('w');

        $seconds = 50;
        $earlier50 = clone $now;
        $earlier50->modify('- ' . $seconds . ' seconds');
        $earlier50String = $earlier50->format('Y-m-d H:i:s');

        $seconds = 120;
        $earlier120 = clone $now;
        $earlier120->modify('- ' . $seconds . ' seconds');
        $earlier120String = $earlier120->format('Y-m-d H:i:s');

        $minutes = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $earlier120MinString = $now->format('Y-m-d H:i:s');

        $this->assertGreaterThan(50, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
        $seconds = Crunchbutton_Order_Logistics::TIME_MAX_DELAY - 50;

        $laterM50PlusMax = clone $now;
        $laterM50PlusMax->modify('+ ' . $seconds . ' seconds');
        $laterM50PlusMaxString = $laterM50PlusMax->format('Y-m-d H:i:s');

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0, -118.273, $earlier120, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0, -118.273, $earlier120, 10);
        $driverLocs3 = $this->createAndSaveAdminLocations($this->driver3->id_admin, 34.0, -118.273, $earlier120, 10);

        $o10 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $earlier120MinString, $this->community);
        $o10->save();

        $op10 = $this->defaultOrderPriority($o10, $this->restaurant1, $this->driver1,
            $earlier120MinString, Crunchbutton_Order_Priority::PRIORITY_HIGH, 0, $earlier120MinString, 0, 1);
        $op10->save();

        $o11 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $earlier120MinString, $this->community);
        $o11->save();

        $op11 = $this->defaultOrderPriority($o11, $this->restaurant1, $this->driver1,
            $earlier120MinString, Crunchbutton_Order_Priority::PRIORITY_HIGH, 0, $earlier120MinString, 0, 1);
        $op11->save();

        $chipotle_lat = 34.0284;
        $chipotle_lon = -118.287;


        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $earlier50String, $this->community);
        $o2->lat = $chipotle_lat;
        $o2->lon = $chipotle_lon;
        $o2->save();
        $orders[] = $o2;

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $drivers = $ol->drivers();

        $ops2 = Crunchbutton_Order_Priority::q('SELECT * FROM order_priority WHERE id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);
        $olr3 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver3->id_admin]);

        $olr1->delete();
        $olr2->delete();
        $olr3->delete();

        $op10->delete();
        $o10->delete();
        $op11->delete();
        $o11->delete();
        foreach ($orders as $o) {
            $o->delete();
        }
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        foreach ($driverLocs3 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();


        foreach ($ol->drivers() as $driver) {
            $this->assertEquals($driver->__seconds, 0);
            $this->assertEquals($driver->__priority, true);

        }
        $this->assertEquals($ops2->count(), 3);

        foreach ($ops2 as $op) {
            $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            $this->assertEquals($op->num_undelivered_orders, 0);
            $this->assertEquals($op->num_drivers_with_priority, 3);
        }
        $this->assertEquals($ol->numDriversWithPriority, 3);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 1);
        $this->assertEquals($olr1->count(), 3);
        $this->assertEquals($olr2->count(), 3);
        $this->assertEquals($olr3->count(), 3);
    }

    //  Three drivers, none with orders
    //  All three drivers are at different locations
    //  New Chipotle order comes in - closest driver (who is active) gets the priority
    public function testLogisticsThreeDriversNoOrdersNoWaitOneInactive1()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $dow = $now->format('w');

        $seconds = 50;
        $earlier50 = clone $now;
        $earlier50->modify('- ' . $seconds . ' seconds');
        $earlier50String = $earlier50->format('Y-m-d H:i:s');

        $seconds = 120;
        $earlier120 = clone $now;
        $earlier120->modify('- ' . $seconds . ' seconds');
        $earlier120String = $earlier120->format('Y-m-d H:i:s');

        $minutes = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $earlier120MinString = $now->format('Y-m-d H:i:s');

        $this->assertGreaterThan(50, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
        $seconds = Crunchbutton_Order_Logistics::TIME_MAX_DELAY - 50;

        $laterM50PlusMax = clone $now;
        $laterM50PlusMax->modify('+ ' . $seconds . ' seconds');
        $laterM50PlusMaxString = $laterM50PlusMax->format('Y-m-d H:i:s');

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $earlier120, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.018, -118.281, $earlier120, 10);
        $driverLocs3 = $this->createAndSaveAdminLocations($this->driver3->id_admin, 34.0, -118.27, $earlier120, 10);

        $o10 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $earlier120MinString, $this->community);
        $o10->save();

        $op10 = $this->defaultOrderPriority($o10, $this->restaurant1, $this->driver1,
            $earlier120MinString, Crunchbutton_Order_Priority::PRIORITY_HIGH, 0, $earlier120MinString, 0, 1);
        $op10->save();

        $o11 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $earlier120MinString, $this->community);
        $o11->save();

        $op11 = $this->defaultOrderPriority($o11, $this->restaurant1, $this->driver1,
            $earlier120MinString, Crunchbutton_Order_Priority::PRIORITY_HIGH, 0, $earlier120MinString, 0, 1);
        $op11->save();

        $chipotle_lat = 34.0284;
        $chipotle_lon = -118.287;

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $earlier50String, $this->community);
        $o2->lat = $chipotle_lat;
        $o2->lon = $chipotle_lon;
        $o2->save();
        $orders[] = $o2;

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $drivers = $ol->drivers();

        $ops2 = Crunchbutton_Order_Priority::q('SELECT * FROM order_priority WHERE id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);
        $olr3 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver3->id_admin]);

        $olr1->delete();
        $olr2->delete();
        $olr3->delete();

        $op10->delete();
        $o10->delete();
        $op11->delete();
        $o11->delete();

        foreach ($orders as $o) {
            $o->delete();
        }
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        foreach ($driverLocs3 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();


        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops2->count(), 3);

        foreach ($ops2 as $op) {
            if ($op->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_undelivered_orders, 0);
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 3);
        $this->assertEquals($olr2->count(), 3);
        $this->assertEquals($olr3->count(), 3);
    }

    //  Three drivers, none with orders
    //  All three drivers are at different locations
    //  New Chipotle order comes in - closest driver is inactive and gets the priority along with another driver
    public function testLogisticsThreeDriversNoOrdersNoWaitOneInactive2()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $dow = $now->format('w');

        $seconds = 50;
        $earlier50 = clone $now;
        $earlier50->modify('- ' . $seconds . ' seconds');
        $earlier50String = $earlier50->format('Y-m-d H:i:s');

        $seconds = 120;
        $earlier120 = clone $now;
        $earlier120->modify('- ' . $seconds . ' seconds');
        $earlier120String = $earlier120->format('Y-m-d H:i:s');

        $minutes = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $earlier120MinString = $now->format('Y-m-d H:i:s');

        $this->assertGreaterThan(50, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
        $seconds = Crunchbutton_Order_Logistics::TIME_MAX_DELAY - 50;

        $laterM50PlusMax = clone $now;
        $laterM50PlusMax->modify('+ ' . $seconds . ' seconds');
        $laterM50PlusMaxString = $laterM50PlusMax->format('Y-m-d H:i:s');

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $earlier120, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.018, -118.281, $earlier120, 10);
        $driverLocs3 = $this->createAndSaveAdminLocations($this->driver3->id_admin, 34.0, -118.27, $earlier120, 10);

        $o10 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $earlier120MinString, $this->community);
        $o10->save();

        $op10 = $this->defaultOrderPriority($o10, $this->restaurant1, $this->driver2,
            $earlier120MinString, Crunchbutton_Order_Priority::PRIORITY_HIGH, 0, $earlier120MinString, 0, 1);
        $op10->save();

        $o11 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $earlier120MinString, $this->community);
        $o11->save();

        $op11 = $this->defaultOrderPriority($o11, $this->restaurant1, $this->driver2,
            $earlier120MinString, Crunchbutton_Order_Priority::PRIORITY_HIGH, 0, $earlier120MinString, 0, 1);
        $op11->save();

        $chipotle_lat = 34.0284;
        $chipotle_lon = -118.287;

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $earlier50String, $this->community);
        $o2->lat = $chipotle_lat;
        $o2->lon = $chipotle_lon;
        $o2->save();
        $orders[] = $o2;

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $drivers = $ol->drivers();

        $ops2 = Crunchbutton_Order_Priority::q('SELECT * FROM order_priority WHERE id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);
        $olr3 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver3->id_admin]);

        $olr1->delete();
        $olr2->delete();
        $olr3->delete();

        $op10->delete();
        $o10->delete();
        $op11->delete();
        $o11->delete();

        foreach ($orders as $o) {
            $o->delete();
        }
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        foreach ($driverLocs3 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();


        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin != $this->driver3->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops2->count(), 3);

        foreach ($ops2 as $op) {
            if ($op->id_admin != $this->driver3->id_admin) {
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_undelivered_orders, 0);
            $this->assertEquals($op->num_drivers_with_priority, 2);
        }
        $this->assertEquals($ol->numDriversWithPriority, 2);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 1);
        $this->assertEquals($olr1->count(), 3);
        $this->assertEquals($olr2->count(), 3);
        $this->assertEquals($olr3->count(), 3);
    }

    //  Three drivers, none with orders
    //  All three drivers are at different locations
    //  New Chipotle order comes in - two closest drivers are active and get the priority
    public function testLogisticsThreeDriversNoOrdersNoWaitOneInactive3()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $dow = $now->format('w');

        $seconds = 50;
        $earlier50 = clone $now;
        $earlier50->modify('- ' . $seconds . ' seconds');
        $earlier50String = $earlier50->format('Y-m-d H:i:s');

        $seconds = 120;
        $earlier120 = clone $now;
        $earlier120->modify('- ' . $seconds . ' seconds');
        $earlier120String = $earlier120->format('Y-m-d H:i:s');

        $minutes = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $earlier120MinString = $now->format('Y-m-d H:i:s');

        $this->assertGreaterThan(50, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
        $seconds = Crunchbutton_Order_Logistics::TIME_MAX_DELAY - 50;

        $laterM50PlusMax = clone $now;
        $laterM50PlusMax->modify('+ ' . $seconds . ' seconds');
        $laterM50PlusMaxString = $laterM50PlusMax->format('Y-m-d H:i:s');

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $earlier120, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.018, -118.281, $earlier120, 10);
        $driverLocs3 = $this->createAndSaveAdminLocations($this->driver3->id_admin, 34.018, -118.281, $earlier120, 10);

        $o10 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $earlier120MinString, $this->community);
        $o10->save();

        $op10 = $this->defaultOrderPriority($o10, $this->restaurant1, $this->driver1,
            $earlier120MinString, Crunchbutton_Order_Priority::PRIORITY_HIGH, 0, $earlier120MinString, 0, 1);
        $op10->save();

        $o11 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $earlier120MinString, $this->community);
        $o11->save();

        $op11 = $this->defaultOrderPriority($o11, $this->restaurant1, $this->driver1,
            $earlier120MinString, Crunchbutton_Order_Priority::PRIORITY_HIGH, 0, $earlier120MinString, 0, 1);
        $op11->save();

        $chipotle_lat = 34.0284;
        $chipotle_lon = -118.287;

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $earlier50String, $this->community);
        $o2->lat = $chipotle_lat;
        $o2->lon = $chipotle_lon;
        $o2->save();
        $orders[] = $o2;

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $drivers = $ol->drivers();

        $ops2 = Crunchbutton_Order_Priority::q('SELECT * FROM order_priority WHERE id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);
        $olr3 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver3->id_admin]);

        $olr1->delete();
        $olr2->delete();
        $olr3->delete();

        $op10->delete();
        $o10->delete();
        $op11->delete();
        $o11->delete();

        foreach ($orders as $o) {
            $o->delete();
        }
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        foreach ($driverLocs3 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();


        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin != $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops2->count(), 3);

        foreach ($ops2 as $op) {
            if ($op->id_admin != $this->driver1->id_admin) {
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_undelivered_orders, 0);
            $this->assertEquals($op->num_drivers_with_priority, 2);
        }
        $this->assertEquals($ol->numDriversWithPriority, 2);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 3);
        $this->assertEquals($olr2->count(), 3);
        $this->assertEquals($olr3->count(), 3);
    }

    //  Three drivers, none with orders
    //  All three drivers are at different locations
    //  New Chipotle order comes in - two closest drivers are inactive and get the priority along with another driver
    public function testLogisticsThreeDriversNoOrdersNoWaitTwoInactive1()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $dow = $now->format('w');

        $seconds = 50;
        $earlier50 = clone $now;
        $earlier50->modify('- ' . $seconds . ' seconds');
        $earlier50String = $earlier50->format('Y-m-d H:i:s');

        $seconds = 120;
        $earlier120 = clone $now;
        $earlier120->modify('- ' . $seconds . ' seconds');
        $earlier120String = $earlier120->format('Y-m-d H:i:s');

        $minutes = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $earlier120MinString = $now->format('Y-m-d H:i:s');

        $this->assertGreaterThan(50, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
        $seconds = Crunchbutton_Order_Logistics::TIME_MAX_DELAY - 50;

        $laterM50PlusMax = clone $now;
        $laterM50PlusMax->modify('+ ' . $seconds . ' seconds');
        $laterM50PlusMaxString = $laterM50PlusMax->format('Y-m-d H:i:s');

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $earlier120, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.018, -118.281, $earlier120, 10);
        $driverLocs3 = $this->createAndSaveAdminLocations($this->driver3->id_admin, 34.018, -118.281, $earlier120, 10);

        $o10 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $earlier120MinString, $this->community);
        $o10->save();

        $op10 = $this->defaultOrderPriority($o10, $this->restaurant1, $this->driver2,
            $earlier120MinString, Crunchbutton_Order_Priority::PRIORITY_HIGH, 0, $earlier120MinString, 0, 1);
        $op10->save();

        $o11 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $earlier120MinString, $this->community);
        $o11->save();

        $op11 = $this->defaultOrderPriority($o11, $this->restaurant1, $this->driver2,
            $earlier120MinString, Crunchbutton_Order_Priority::PRIORITY_HIGH, 0, $earlier120MinString, 0, 1);
        $op11->save();

        $o12 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $earlier120MinString, $this->community);
        $o12->save();

        $op12 = $this->defaultOrderPriority($o12, $this->restaurant1, $this->driver3,
            $earlier120MinString, Crunchbutton_Order_Priority::PRIORITY_HIGH, 0, $earlier120MinString, 0, 1);
        $op12->save();

        $o13 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $earlier120MinString, $this->community);
        $o13->save();

        $op13 = $this->defaultOrderPriority($o13, $this->restaurant1, $this->driver3,
            $earlier120MinString, Crunchbutton_Order_Priority::PRIORITY_HIGH, 0, $earlier120MinString, 0, 1);
        $op13->save();

        $chipotle_lat = 34.0284;
        $chipotle_lon = -118.287;

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $earlier50String, $this->community);
        $o2->lat = $chipotle_lat;
        $o2->lon = $chipotle_lon;
        $o2->save();
        $orders[] = $o2;

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $drivers = $ol->drivers();

        $ops2 = Crunchbutton_Order_Priority::q('SELECT * FROM order_priority WHERE id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);
        $olr3 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver3->id_admin]);

        $olr1->delete();
        $olr2->delete();
        $olr3->delete();

        $op10->delete();
        $o10->delete();
        $op11->delete();
        $o11->delete();
        $op12->delete();
        $o12->delete();
        $op13->delete();
        $o13->delete();

        foreach ($orders as $o) {
            $o->delete();
        }
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        foreach ($driverLocs3 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();


        foreach ($ol->drivers() as $driver) {
            $this->assertEquals($driver->__seconds, 0);
            $this->assertEquals($driver->__priority, true);
        }
        $this->assertEquals($ops2->count(), 3);

        foreach ($ops2 as $op) {
            $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            $this->assertEquals($op->num_undelivered_orders, 0);
            $this->assertEquals($op->num_drivers_with_priority, 3);
        }
        $this->assertEquals($ol->numDriversWithPriority, 3);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 2);
        $this->assertEquals($olr1->count(), 3);
        $this->assertEquals($olr2->count(), 3);
        $this->assertEquals($olr3->count(), 3);
    }

    //  Three drivers, none with orders
    //  All three drivers are at different locations, two are inactive
    //  New Chipotle order comes in - closest drivers is inactive and get the priority along with another driver
    public function testLogisticsThreeDriversNoOrdersNoWaitTwoInactive2()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $dow = $now->format('w');

        $seconds = 50;
        $earlier50 = clone $now;
        $earlier50->modify('- ' . $seconds . ' seconds');
        $earlier50String = $earlier50->format('Y-m-d H:i:s');

        $seconds = 120;
        $earlier120 = clone $now;
        $earlier120->modify('- ' . $seconds . ' seconds');
        $earlier120String = $earlier120->format('Y-m-d H:i:s');

        $minutes = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $earlier120MinString = $now->format('Y-m-d H:i:s');

        $this->assertGreaterThan(50, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
        $seconds = Crunchbutton_Order_Logistics::TIME_MAX_DELAY - 50;

        $laterM50PlusMax = clone $now;
        $laterM50PlusMax->modify('+ ' . $seconds . ' seconds');
        $laterM50PlusMaxString = $laterM50PlusMax->format('Y-m-d H:i:s');

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.2, -118.6, $earlier120, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.018, -118.281, $earlier120, 10);
        $driverLocs3 = $this->createAndSaveAdminLocations($this->driver3->id_admin, 34.0, -118.27, $earlier120, 10);

        $o10 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $earlier120MinString, $this->community);
        $o10->save();

        $op10 = $this->defaultOrderPriority($o10, $this->restaurant1, $this->driver2,
            $earlier120MinString, Crunchbutton_Order_Priority::PRIORITY_HIGH, 0, $earlier120MinString, 0, 1);
        $op10->save();

        $o11 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $earlier120MinString, $this->community);
        $o11->save();

        $op11 = $this->defaultOrderPriority($o11, $this->restaurant1, $this->driver2,
            $earlier120MinString, Crunchbutton_Order_Priority::PRIORITY_HIGH, 0, $earlier120MinString, 0, 1);
        $op11->save();

        $o12 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $earlier120MinString, $this->community);
        $o12->save();

        $op12 = $this->defaultOrderPriority($o12, $this->restaurant1, $this->driver3,
            $earlier120MinString, Crunchbutton_Order_Priority::PRIORITY_HIGH, 0, $earlier120MinString, 0, 1);
        $op12->save();

        $o13 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $earlier120MinString, $this->community);
        $o13->save();

        $op13 = $this->defaultOrderPriority($o13, $this->restaurant1, $this->driver3,
            $earlier120MinString, Crunchbutton_Order_Priority::PRIORITY_HIGH, 0, $earlier120MinString, 0, 1);
        $op13->save();

        $chipotle_lat = 34.0284;
        $chipotle_lon = -118.287;

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $earlier50String, $this->community);
        $o2->lat = $chipotle_lat;
        $o2->lon = $chipotle_lon;
        $o2->save();
        $orders[] = $o2;

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $drivers = $ol->drivers();

        $ops2 = Crunchbutton_Order_Priority::q('SELECT * FROM order_priority WHERE id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);
        $olr3 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver3->id_admin]);

        $olr1->delete();
        $olr2->delete();
        $olr3->delete();

        $op10->delete();
        $o10->delete();
        $op11->delete();
        $o11->delete();
        $op12->delete();
        $o12->delete();
        $op13->delete();
        $o13->delete();

        foreach ($orders as $o) {
            $o->delete();
        }
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        foreach ($driverLocs3 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();


        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin != $this->driver3->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops2->count(), 3);

        foreach ($ops2 as $op) {
            if ($op->id_admin != $this->driver3->id_admin) {
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_undelivered_orders, 0);
            $this->assertEquals($op->num_drivers_with_priority, 2);
        }
        $this->assertEquals($ol->numDriversWithPriority, 2);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 1);
        $this->assertEquals($olr1->count(), 3);
        $this->assertEquals($olr2->count(), 3);
        $this->assertEquals($olr3->count(), 3);
    }


    //  Three drivers, none with orders
    //  All three drivers are at different locations, one is inactive, one seems inactive
    //  New Chipotle order comes in - closest driver seems inactive but has delivered an order since
    //  and he is closest so he gets priority
    public function testLogisticsThreeDriversNoOrdersNoWaitTwoInactive3()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $dow = $now->format('w');

        $seconds = 50;
        $earlier50 = clone $now;
        $earlier50->modify('- ' . $seconds . ' seconds');
        $earlier50String = $earlier50->format('Y-m-d H:i:s');

        $seconds = 120;
        $earlier120 = clone $now;
        $earlier120->modify('- ' . $seconds . ' seconds');
        $earlier120String = $earlier120->format('Y-m-d H:i:s');

        $minutes = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $earlier120MinString = $now->format('Y-m-d H:i:s');

        $minutes = 60;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $earlier60MinString = $now->format('Y-m-d H:i:s');

        $this->assertGreaterThan(50, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
        $seconds = Crunchbutton_Order_Logistics::TIME_MAX_DELAY - 50;

        $laterM50PlusMax = clone $now;
        $laterM50PlusMax->modify('+ ' . $seconds . ' seconds');
        $laterM50PlusMaxString = $laterM50PlusMax->format('Y-m-d H:i:s');

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $earlier120, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.018, -118.281, $earlier120, 10);
        $driverLocs3 = $this->createAndSaveAdminLocations($this->driver3->id_admin, 34.0, -118.27, $earlier120, 10);

        $o10 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $earlier120MinString, $this->community);
        $o10->save();

        $op10 = $this->defaultOrderPriority($o10, $this->restaurant1, $this->driver2,
            $earlier120MinString, Crunchbutton_Order_Priority::PRIORITY_HIGH, 0, $earlier120MinString, 0, 1);
        $op10->save();

        $o11 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $earlier120MinString, $this->community);
        $o11->save();

        $op11 = $this->defaultOrderPriority($o11, $this->restaurant1, $this->driver2,
            $earlier120MinString, Crunchbutton_Order_Priority::PRIORITY_HIGH, 0, $earlier120MinString, 0, 1);
        $op11->save();

        $o12 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $earlier120MinString, $this->community);
        $o12->save();

        $op12 = $this->defaultOrderPriority($o12, $this->restaurant1, $this->driver3,
            $earlier120MinString, Crunchbutton_Order_Priority::PRIORITY_HIGH, 0, $earlier120MinString, 0, 1);
        $op12->save();

        $o13 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $earlier120MinString, $this->community);
        $o13->save();

        $op13 = $this->defaultOrderPriority($o13, $this->restaurant1, $this->driver3,
            $earlier120MinString, Crunchbutton_Order_Priority::PRIORITY_HIGH, 0, $earlier120MinString, 0, 1);
        $op13->save();

        $o20 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $earlier120MinString, $this->community);
        $o20->save();

        $oa20 = new Order_Action([
            'id_order' => $o20->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $earlier60MinString,
            'type' => 'delivery-delivered',
            'note' => ''
        ]);
        $oa20->save();
        $this->associateOrderActionToOrder($o20, $oa20);

        $chipotle_lat = 34.0284;
        $chipotle_lon = -118.287;

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $earlier50String, $this->community);
        $o2->lat = $chipotle_lat;
        $o2->lon = $chipotle_lon;
        $o2->save();
        $orders[] = $o2;

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $drivers = $ol->drivers();

        $ops2 = Crunchbutton_Order_Priority::q('SELECT * FROM order_priority WHERE id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);
        $olr3 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver3->id_admin]);

        $olr1->delete();
        $olr2->delete();
        $olr3->delete();

        $op10->delete();
        $o10->delete();
        $op11->delete();
        $o11->delete();
        $op12->delete();
        $o12->delete();
        $op13->delete();
        $o13->delete();

        $o20->delete();
        $oa20->delete();

        foreach ($orders as $o) {
            $o->delete();
        }
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        foreach ($driverLocs3 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();


        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops2->count(), 3);

        foreach ($ops2 as $op) {
            if ($op->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_undelivered_orders, 0);
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 3);
        $this->assertEquals($olr2->count(), 3);
        $this->assertEquals($olr3->count(), 3);
    }

    //  Three drivers, none with orders
    //  All three drivers are at different locations, one is inactive, one seems inactive
    //  New Chipotle order comes in - closest driver seems inactive but has delivered an order since
    //  and so only free driver gets the priority
    public function testLogisticsThreeDriversNoOrdersNoWaitTwoInactive4()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $dow = $now->format('w');

        $seconds = 50;
        $earlier50 = clone $now;
        $earlier50->modify('- ' . $seconds . ' seconds');
        $earlier50String = $earlier50->format('Y-m-d H:i:s');

        $seconds = 120;
        $earlier120 = clone $now;
        $earlier120->modify('- ' . $seconds . ' seconds');
        $earlier120String = $earlier120->format('Y-m-d H:i:s');

        $minutes = 120;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $earlier120MinString = $now->format('Y-m-d H:i:s');

        $minutes = 60;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $minutes . ' minutes');
        $earlier60MinString = $now->format('Y-m-d H:i:s');

        $this->assertGreaterThan(50, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
        $seconds = Crunchbutton_Order_Logistics::TIME_MAX_DELAY - 50;

        $laterM50PlusMax = clone $now;
        $laterM50PlusMax->modify('+ ' . $seconds . ' seconds');
        $laterM50PlusMaxString = $laterM50PlusMax->format('Y-m-d H:i:s');

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.018, -118.281, $earlier120, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $earlier120, 10);
        $driverLocs3 = $this->createAndSaveAdminLocations($this->driver3->id_admin, 34.0, -118.27, $earlier120, 10);

        $o10 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $earlier120MinString, $this->community);
        $o10->save();

        $op10 = $this->defaultOrderPriority($o10, $this->restaurant1, $this->driver2,
            $earlier120MinString, Crunchbutton_Order_Priority::PRIORITY_HIGH, 0, $earlier120MinString, 0, 1);
        $op10->save();

        $o11 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $earlier120MinString, $this->community);
        $o11->save();

        $op11 = $this->defaultOrderPriority($o11, $this->restaurant1, $this->driver2,
            $earlier120MinString, Crunchbutton_Order_Priority::PRIORITY_HIGH, 0, $earlier120MinString, 0, 1);
        $op11->save();

        $o12 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $earlier120MinString, $this->community);
        $o12->save();

        $op12 = $this->defaultOrderPriority($o12, $this->restaurant1, $this->driver3,
            $earlier120MinString, Crunchbutton_Order_Priority::PRIORITY_HIGH, 0, $earlier120MinString, 0, 1);
        $op12->save();

        $o13 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $earlier120MinString, $this->community);
        $o13->save();

        $op13 = $this->defaultOrderPriority($o13, $this->restaurant1, $this->driver3,
            $earlier120MinString, Crunchbutton_Order_Priority::PRIORITY_HIGH, 0, $earlier120MinString, 0, 1);
        $op13->save();

        $o20 = $this->defaultOrder($this->user2, $this->restaurant1->id_restaurant, $earlier120MinString, $this->community);
        $o20->save();

        $oa20 = new Order_Action([
            'id_order' => $o20->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $earlier60MinString,
            'type' => 'delivery-delivered',
            'note' => ''
        ]);
        $oa20->save();
        $this->associateOrderActionToOrder($o20, $oa20);

        $chipotle_lat = 34.0284;
        $chipotle_lon = -118.287;

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $earlier50String, $this->community);
        $o2->lat = $chipotle_lat;
        $o2->lon = $chipotle_lon;
        $o2->save();
        $orders[] = $o2;

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $drivers = $ol->drivers();

        $ops2 = Crunchbutton_Order_Priority::q('SELECT * FROM order_priority WHERE id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);
        $olr3 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver3->id_admin]);

        $olr1->delete();
        $olr2->delete();
        $olr3->delete();

        $op10->delete();
        $o10->delete();
        $op11->delete();
        $o11->delete();
        $op12->delete();
        $o12->delete();
        $op13->delete();
        $o13->delete();

        $o20->delete();
        $oa20->delete();

        foreach ($orders as $o) {
            $o->delete();
        }
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        foreach ($driverLocs3 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();


        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops2->count(), 3);

        foreach ($ops2 as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_undelivered_orders, 0);
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 3);
        $this->assertEquals($olr2->count(), 3);
        $this->assertEquals($olr3->count(), 3);
    }

    // Three drivers - driver 1 has an unaccepted, unexpired priority order from Chipotle (with no waiting time).
    //  All three drivers are at the same location
    //  New Chipotle order comes in - driver 1 gets the priority
    public function testLogisticsThreeDriversSecondOrderSameLocationNoWait1()
    {
        $orders = [];
        $allops = [];
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));

        $seconds = 50;
        $earlier50 = clone $now;
        $earlier50->modify('- ' . $seconds . ' seconds');
        $earlier50String = $earlier50->format('Y-m-d H:i:s');

        $seconds = 120;
        $earlier120 = clone $now;
        $earlier120->modify('- ' . $seconds . ' seconds');
        $earlier120String = $earlier120->format('Y-m-d H:i:s');

        $dow = $now->format('w');

        $this->assertGreaterThan(50, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
        $seconds = Crunchbutton_Order_Logistics::TIME_MAX_DELAY - 50;

        $laterM50PlusMax = clone $now;
        $laterM50PlusMax->modify('+ ' . $seconds . ' seconds');
        $laterM50PlusMaxString = $laterM50PlusMax->format('Y-m-d H:i:s');

        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, 34.0302, -118.273, $earlier120, 10);
        $driverLocs2 = $this->createAndSaveAdminLocations($this->driver2->id_admin, 34.0302, -118.273, $earlier120, 10);
        $driverLocs3 = $this->createAndSaveAdminLocations($this->driver3->id_admin, 34.0302, -118.273, $earlier120, 10);

        $chipotle_lat = 34.0284;
        $chipotle_lon = -118.287;

        // Chipotle
        $og = $this->createOrderGroupAndSave($this->user, $this->restaurant3, $now, 70,
            $this->community, $chipotle_lat, $chipotle_lon,
            [$this->driver1, $this->driver2, $this->driver3],
            [Crunchbutton_Order_Priority::PRIORITY_HIGH, Crunchbutton_Order_Priority::PRIORITY_LOW,
                Crunchbutton_Order_Priority::PRIORITY_LOW], [0, 0, 0]);
        $orders[] = $og['o'];
        $allops = array_merge($allops, $og['ops']);

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $earlier50String, $this->community);
        $o2->lat = $chipotle_lat;
        $o2->lon = $chipotle_lon;
        $o2->save();
        $orders[] = $o2;

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $olp1->save();

        $ols1 = $this->defaultOLS($this->restaurant3, $start, $end, 5, 5, 5, $dow);
        $ols1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $ol->process();

        $drivers = $ol->drivers();

        $ops2 = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $olr1 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver1->id_admin]);
        $olr2 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver2->id_admin]);
        $olr3 = Crunchbutton_Order_Logistics_Route::q('select * from order_logistics_route where id_order = ? and id_admin = ?', [$o2->id_order, $this->driver3->id_admin]);

        $olr1->delete();
        $olr2->delete();
        $olr3->delete();

        foreach ($allops as $op) {
            $op->delete();
        }
        foreach ($orders as $o) {
            $o->delete();
        }
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        foreach ($driverLocs2 as $l) {
            $l->delete();
        }
        foreach ($driverLocs3 as $l) {
            $l->delete();
        }
        $olp1->delete();
        $ols1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();

        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ops2->count(), 3);

        foreach ($ops2 as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertEquals($op->priority_given, Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
            $this->assertEquals($op->num_undelivered_orders, 0);
            $this->assertEquals($op->num_drivers_with_priority, 1);
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
        $this->assertEquals($ol->numInactiveDriversWithPriority, 0);
        $this->assertEquals($olr1->count(), 5);
        $this->assertEquals($olr2->count(), 3);
        $this->assertEquals($olr3->count(), 3);
    }





    public function defaultOrder($user, $restaurantId, $date, $community)
    {
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

    public function defaultOrderWithLoc($user, $restaurantId, $date, $community, $lat, $lon)
    {
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
                                         $priorityTime, $priority, $delay, $expiration, $numUndeliveredOrders,
                                         $numDriversWithPriority, $algoVersion=Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX_ALGO_VERSION)
    {
        return new Crunchbutton_Order_Priority([
            'id_order' => $order->id_order,
            'id_restaurant' => $restaurant->id_restaurant,
            'id_admin' => $driver->id_admin,
            'priority_time' => $priorityTime,
            'priority_algo_version' => $algoVersion,
            'priority_given' => $priority,
            'seconds_delay' => $delay,
            'priority_expiration' => $expiration,
            'num_undelivered_orders' => $numUndeliveredOrders,
            'num_drivers_with_priority' => $numDriversWithPriority
        ]);

    }

    public function createOrderGroupAndSave($user, $restaurant, $nowdt, $earlierSeconds, $community, $lat, $lon, $drivers,
                                            $priorities, $numUndeliveredOrdersArray, $lastActionEarlierSeconds = null,
                                            $actionDriverId = null, $actionString = null, $numDriversWithPriority=null)
    {
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
        if ($numDriversWithPriority != -1) {
            $numDriversWithPriority = 0;
            for ($i = 0; $i < $numDrivers; $i++) {
                $priority = $priorities[$i];
                if (!is_null($priority)) {
                    if ($priority == Crunchbutton_Order_Priority::PRIORITY_HIGH) {
                        $numDriversWithPriority++;
                    } else if ($priority == Crunchbutton_Order_Priority::PRIORITY_NO_ONE) {
                        $numDriversWithPriority++;
                    }
                }
            }
        }
        for ($i = 0; $i < $numDrivers; $i++) {
            $driver = $drivers[$i];
            $priority = $priorities[$i];
            $numUndeliveredOrders = $numUndeliveredOrdersArray[$i];
            if (!is_null($priority)) {
                if ($priority == Crunchbutton_Order_Priority::PRIORITY_HIGH) {
                    $op = $this->defaultOrderPriority($o, $restaurant, $driver,
                        $useDateString, $priority, 0, $laterDateString, $numUndeliveredOrders, $numDriversWithPriority);
                } else if ($priority == Crunchbutton_Order_Priority::PRIORITY_LOW) {
                    $op = $this->defaultOrderPriority($o, $restaurant, $driver,
                        $useDateString, $priority, Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDateString,
                        $numUndeliveredOrders, $numDriversWithPriority);
                } else if ($priority == Crunchbutton_Order_Priority::PRIORITY_NO_ONE) {
                    $op = $this->defaultOrderPriority($o, $restaurant, $driver,
                        $useDateString, $priority, 0, $useDateString, $numDriversWithPriority);
                } else {
                    $op = $this->defaultOrderPriority($o, $restaurant, $driver,
                        $useDateString, $priority, 0, $useDateString, $numDriversWithPriority);
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


    public function defaultOLP($restaurant, $start, $end, $duration0 = 5, $duration1 = 5, $duration2 = 5, $dow = 0)
    {
        return new Crunchbutton_Order_Logistics_Parking([
            'id_restaurant' => $restaurant->id_restaurant,
            'time_start_community' => $start,
            'time_end_community' => $end,
            'day_of_week' => $dow,
            'parking_duration0' => $duration0,
            'parking_duration1' => $duration1,
            'parking_duration2' => $duration2,
        ]);
    }

    public function defaultOLS($restaurant, $start, $end, $duration0 = 5, $duration1 = 5, $duration2 = 5, $dow = 0)
    {
        return new Crunchbutton_Order_Logistics_Service([
            'id_restaurant' => $restaurant->id_restaurant,
            'time_start_community' => $start,
            'time_end_community' => $end,
            'day_of_week' => $dow,
            'service_duration0' => $duration0,
            'service_duration1' => $duration1,
            'service_duration2' => $duration2
        ]);
    }


    public function defaultOLOT($restaurant, $start, $end, $otime = 15, $factor = 1, $dow = 0)
    {
        return new Crunchbutton_Order_Logistics_Ordertime([
            'id_restaurant' => $restaurant->id_restaurant,
            'time_start_community' => $start,
            'time_end_community' => $end,
            'day_of_week' => $dow,
            'order_time' => $otime,
            'scale_factor' => $factor
        ]);
    }

    public function defaultOLCS($community, $start, $end, $mph = 10, $dow = 0)
    {
        return new Crunchbutton_Order_Logistics_Communityspeed([
            'id_community' => $community->id_community,
            'time_start_community' => $start,
            'time_end_community' => $end,
            'day_of_week' => $dow,
            'mph' => $mph
        ]);
    }

    public function defaultOLBA($community, $address, $lat = 34.023281, $lon = -118.2881961)
    {
        return new Crunchbutton_Order_Logistics_Badaddress([
            'id_community' => $community->id_community,
            'address_lc' => $address,
            'lat' => $lat,
            'lon' => $lon
        ]);
    }

    public function defaultScore($admin, $score = Cockpit_Admin_Score::DEFAULT_SCORE, $experience = Cockpit_Admin_Score::DEFAULT_EXPERIENCE)
    {
        $qString = "SELECT * FROM `admin_score` WHERE id_admin= ? ";
        $s = Cockpit_Admin_Score::q($qString, [$admin->id_admin]);
        $s->delete();
        return new Cockpit_Admin_Score([
            'id_admin' => $admin->id_admin,
            'score' => $score,
            'experience' => $experience
        ]);
    }

    public function defaultOLC($restaurant, $dow, $start = "00:00:00", $end = "24:00:00", $clusterid = null)
    {
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

    public function defaultOLR($order, $node_id_order, $driver, $seq, $node_type, $leaving_time, $lat, $lon, $isFake = false)
    {
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

    public function defaultDriverDestination($id)
    {
        return new Crunchbutton_Order_Logistics_Destination([
            'id' => $id,
            'type' => Crunchbutton_Order_Logistics_Destination::TYPE_DRIVER
        ]);
    }

    public function defaultRestaurantDestination($id, $cluster = null)
    {
        return new Crunchbutton_Order_Logistics_Destination([
            'id' => $id,
            'cluster' => $cluster,
            'type' => Crunchbutton_Order_Logistics_Destination::TYPE_RESTAURANT
        ]);
    }

    public function defaultCustomerDestination($id)
    {
        return new Crunchbutton_Order_Logistics_Destination([
            'id' => $id,
            'type' => Crunchbutton_Order_Logistics_Destination::TYPE_CUSTOMER
        ]);
    }

    public function createAdminLocation($id_admin, $lat, $lon, $date)
    {
        return new Cockpit_Admin_Location([
            'id_admin' => $id_admin,
            'lat' => $lat,
            'lon' => $lon,
            'accuracy' => 50,
            'date' => $date
        ]);
    }

    public static function defaultOLBP($id_community, $cutoff_at_zero, $slope_per_minute, $max_minutes, $bundle_size, $baseline_mph)
    {
        return new Crunchbutton_Order_Logistics_Bundleparam([
            'id_community' => $id_community,
            'cutoff_at_zero' => $cutoff_at_zero,
            'slope_per_minute' => $slope_per_minute,
            'max_minutes' => $max_minutes,
            'bundle_size' => $bundle_size,
            'baseline_mph' => $baseline_mph
        ]);
    }

    public static function defaultOLParam($id_community, $algo_version, $time_max_delay, $time_bundle, $max_bundle_size,
                                          $max_bundle_travel_time, $max_num_orders_delta, $max_num_unique_restaurants_delta,
                                          $free_driver_bonus,
                                          $order_ahead_correction1, $order_ahead_correction2,
                                          $order_ahead_correction_limit1, $order_ahead_correction_limit2)
    {
        return new Crunchbutton_Order_Logistics_Param([
            'id_community' => $id_community,
            'algo_version' => $algo_version,
            'time_max_delay' => $time_max_delay,
            'time_bundle' => $time_bundle,
            'max_bundle_size' => $max_bundle_size,
            'max_bundle_travel_time' => $max_bundle_travel_time,
            'max_num_orders_delta' => $max_num_orders_delta,
            'max_num_unique_restaurants_delta' => $max_num_unique_restaurants_delta,
            'free_driver_bonus' => $free_driver_bonus,
            'order_ahead_correction1' => $order_ahead_correction1,
            'order_ahead_correction2' => $order_ahead_correction2,
            'order_ahead_correction_limit1' => $order_ahead_correction_limit1,
            'order_ahead_correction_limit2' => $order_ahead_correction_limit2
        ]);
    }

    // Locations are created every second from 0 to $window
    public function createAndSaveAdminLocations($id_admin, $lat, $lon, $dt, $window)
    {

        $locs = [];
        for ($i = 0; $i < $window; $i++) {
            $dt->modify('- ' . 1 . ' seconds');
            $date = $dt->format('Y-m-d H:i:s');

            $loc = new Cockpit_Admin_Location([
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

    public function associateOrderActionToOrder($order, $order_action)
    {
        $order->delivery_status = $order_action->id_order_action;
        $order->save();
    }

}
