<?php

class PrioritySimpleLogisticsTest extends PHPUnit_Framework_TestCase
{

    // TODO: Test that this works correctly for different time zones
    public static function setUpBeforeClass()
    {
        $community_tz1 = 'America/Los_Angeles';
        $name = get_called_class();
        $hours = 2;
        $now = new DateTime('now', new DateTimeZone($community_tz1));
        $now->modify('- ' . $hours . ' hours');
        $useDateEarly = $now->format('Y-m-d H:i:s');
        $now = new DateTime('now', new DateTimeZone($community_tz1));
        $now->modify('+ ' . $hours . ' hours');
        $useDateLater = $now->format('Y-m-d H:i:s');

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
            'delivery_service' => true
        ]);
        $r1->save();
        $restaurants[] = $r1;

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
            'delivery_service' => true
        ]);
        $r2->save();
        $restaurants[] = $r2;

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
            'delivery_service' => true
        ]);
        $r3->save();
        $restaurants[] = $r3;

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
            'delivery_service' => true
        ]);
        $r4->save();
        $restaurants[] = $r4;

        $r5 = new Restaurant([
            'name' => $name . ' - FIVE',
            'active' => 1,
            'delivery' => 1,
            'credit' => 1,
            'delivery_fee' => '1.5',
            'confirmation' => 0,
            'community' => 'test',
            'timezone' => $community_tz1,
            'open_for_business' => true,
            'delivery_service' => true
        ]);
        $r5->save();
        $restaurants[] = $r5;

        $r6 = new Restaurant([
            'name' => $name . ' - SIX',
            'active' => 1,
            'delivery' => 1,
            'credit' => 1,
            'delivery_fee' => '1.5',
            'confirmation' => 0,
            'community' => 'test',
            'timezone' => $community_tz1,
            'open_for_business' => true,
            'delivery_service' => true
        ]);
        $r6->save();
        $restaurants[] = $r6;

        $c = new Community([
            'name' => $name,
            'active' => 1,
            'timezone' => $community_tz1,
            'driver-group' => 'drivers-testlogistics',
            'range' => 2,
            'private' => 1,
            'loc_lat' => 34.023281,
            'loc_lon' => -118.2881961,
            'delivery_logistics' => 1
        ]);
        $c->save();

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
            'id_community' => $c->id_community
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
            'name' => $name,
            'phone' => $_ENV['DEBUG_PHONE'],
            'address' => '123 main',
            'active' => 1
        ]);
        $u->save();

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

        $community = Community::q('select * from community where name = ?', [$name])->get(0);
        $communityId = $community->id_community;
        $cs = Crunchbutton_Community_Shift::q('select * from community_shift where id_community=?', [$communityId])->get(0);
        $csId = $cs->id_community_shift;

        Crunchbutton_Admin_Shift_Assign::q('select * from admin_shift_assign where id_community_shift=?', [$csId])->delete();
        $cs->delete();
        $community->delete();

        Restaurant::q('select * from restaurant where name = ?', [$name . ' - ONE'])->delete();
        Restaurant::q('select * from restaurant where name = ?', [$name . ' - TWO'])->delete();
        Restaurant::q('select * from restaurant where name = ?', [$name . ' - THREE'])->delete();
        Restaurant::q('select * from restaurant where name = ?', [$name . ' - FOUR'])->delete();
        Restaurant::q('select * from restaurant where name = ?', [$name . ' - FIVE'])->delete();
        Restaurant::q('select * from restaurant where name = ?', [$name . ' - SIX'])->delete();
        Admin::q('select * from admin where name=?', [$name . ' - ONE'])->delete();
        Admin::q('select * from admin where name=?', [$name . ' - TWO'])->delete();
        Admin::q('select * from admin where name=?', [$name . ' - THREE'])->delete();
        User::q('select * from `user` where name=?', [$name])->delete();
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
        $this->user = User::q('select * from `user` where name=? order by id_user desc limit 1', [$name])->get(0);
        $this->community = Community::q('select * from community where name=? order by id_community desc limit 1', [$name])->get(0);
    }

    public function tearDown()
    {
        $name = get_called_class();
        $this->restaurant1 = Restaurant::q('select * from restaurant where name=? order by id_restaurant desc limit 1', [$name . ' - ONE'])->get(0);
        $this->restaurant2 = Restaurant::q('select * from restaurant where name=? order by id_restaurant desc limit 1', [$name . ' - TWO'])->get(0);
        $this->restaurant3 = Restaurant::q('select * from restaurant where name=? order by id_restaurant desc limit 1', [$name . ' - THREE'])->get(0);
        $this->restaurant4 = Restaurant::q('select * from restaurant where name=? order by id_restaurant desc limit 1', [$name . ' - FOUR'])->get(0);
        $this->restaurant5 = Restaurant::q('select * from restaurant where name=? order by id_restaurant desc limit 1', [$name . ' - FIVE'])->get(0);
        $this->restaurant6 = Restaurant::q('select * from restaurant where name=? order by id_restaurant desc limit 1', [$name . ' - SIX'])->get(0);

        Crunchbutton_Order_Priority::q('select * from order_priority where id_restaurant = ?', [$this->restaurant1->id_restaurant])->delete();
        Crunchbutton_Order_Priority::q('select * from order_priority where id_restaurant = ?', [$this->restaurant2->id_restaurant])->delete();
        Crunchbutton_Order_Priority::q('select * from order_priority where id_restaurant = ?', [$this->restaurant3->id_restaurant])->delete();
        Crunchbutton_Order_Priority::q('select * from order_priority where id_restaurant = ?', [$this->restaurant4->id_restaurant])->delete();
        Crunchbutton_Order_Priority::q('select * from order_priority where id_restaurant = ?', [$this->restaurant5->id_restaurant])->delete();
        Crunchbutton_Order_Priority::q('select * from order_priority where id_restaurant = ?', [$this->restaurant6->id_restaurant])->delete();
    }


    // A high priority order should result in Crunchbutton_Order_Priority::checkOrderInArray returning true
    public function testCheckHighPriorityOrderInArray()
    {
        $seconds = 20;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate = $now->format('Y-m-d H:i:s');

        $seconds = Crunchbutton_Order_Logistics::TIME_MAX_DELAY - 20;
        $later = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $later->modify('+ ' . $seconds . ' seconds');
        $laterDate = $later->format('Y-m-d H:i:s');

        $o1 = $this->defaultOrder($this->user, $this->restaurant1->id_restaurant, $useDate, $this->community);
        $o1->id_order = 9999999999;

        $pR1D1 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver1,
            $useDate, Crunchbutton_Order_Priority::PRIORITY_HIGH,
            0, $laterDate);

        $checkPriorityArray[] = $pR1D1;

        $checkVal = Crunchbutton_Order_Priority::checkOrderInArray($o1->id_order, $checkPriorityArray);
        $this->assertTrue($checkVal);

    }

    // A low priority order should result in Crunchbutton_Order_Priority::checkOrderInArray returning false
    public function testCheckLowPriorityOrderInArray()
    {
        $seconds = 20;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate = $now->format('Y-m-d H:i:s');

        $seconds = Crunchbutton_Order_Logistics::TIME_MAX_DELAY - 20;
        $later = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $later->modify('+ ' . $seconds . ' seconds');
        $laterDate = $later->format('Y-m-d H:i:s');

        $o1 = $this->defaultOrder($this->user, $this->restaurant1->id_restaurant, $useDate, $this->community);
        $o1->id_order = 9999999999;

        $pR1D1 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver1,
            $useDate, Crunchbutton_Order_Priority::PRIORITY_LOW,
            Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);

        $checkPriorityArray[] = $pR1D1;
        $checkVal = Crunchbutton_Order_Priority::checkOrderInArray($o1->id_order, $checkPriorityArray);
        $this->assertFalse($checkVal);

    }

    // A "no-one" priority order should result in Crunchbutton_Order_Priority::checkOrderInArray returning false
    public function testCheckNoPriorityOrderInArray()
    {
        $seconds = 20;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate = $now->format('Y-m-d H:i:s');

        $seconds = Crunchbutton_Order_Logistics::TIME_MAX_DELAY - 20;
        $later = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $later->modify('+ ' . $seconds . ' seconds');
        $laterDate = $later->format('Y-m-d H:i:s');

        $o1 = $this->defaultOrder($this->user, $this->restaurant1->id_restaurant, $useDate, $this->community);
        $o1->id_order = 9999999999;

        $pR1D1 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver1,
            $useDate, Crunchbutton_Order_Priority::PRIORITY_NO_ONE,
            0, $laterDate);

        $checkPriorityArray[] = $pR1D1;
        $checkVal = Crunchbutton_Order_Priority::checkOrderInArray($o1->id_order, $checkPriorityArray);
        $this->assertFalse($checkVal);

    }

    // A high priority order should result in Crunchbutton_Order_Priority::checkOrderInArray returning false
    //  if the array checked against is empty
    public function testCheckPriorityOrderInEmptyArray()
    {
        $seconds = 20;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate = $now->format('Y-m-d H:i:s');

        $o1 = $this->defaultOrder($this->user, $this->restaurant1->id_restaurant, $useDate, $this->community);
        $o1->id_order = 9999999999;

        $checkPriorityArray = [];
        $checkVal = Crunchbutton_Order_Priority::checkOrderInArray($o1->id_order, $checkPriorityArray);
        $this->assertFalse($checkVal);

    }

    // A bogus id_order should result in Crunchbutton_Order_Priority::checkOrderInArray returning false
    public function testNoOrderInArray()
    {
        $seconds = 20;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate = $now->format('Y-m-d H:i:s');

        $seconds = Crunchbutton_Order_Logistics::TIME_MAX_DELAY - 20;
        $later = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $later->modify('+ ' . $seconds . ' seconds');
        $laterDate = $later->format('Y-m-d H:i:s');

        $o1 = $this->defaultOrder($this->user, $this->restaurant1->id_restaurant, $useDate, $this->community);
        $o1->id_order = 9999999999;

        $pR1D1 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver1,
            $useDate, Crunchbutton_Order_Priority::PRIORITY_HIGH,
            0, $laterDate);

        $checkPriorityArray[] = $pR1D1;
        $checkVal = Crunchbutton_Order_Priority::checkOrderInArray(-1, $checkPriorityArray);
        $this->assertFalse($checkVal);

    }


    // Make sure we're saving to the db as expected
    public function testSaveOrderPriorityToDb()
    {
        $seconds = 20;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate = $now->format('Y-m-d H:i:s');

        $seconds = Crunchbutton_Order_Logistics::TIME_MAX_DELAY - 20;
        $later = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $later->modify('+ ' . $seconds . ' seconds');
        $laterDate = $later->format('Y-m-d H:i:s');

        $o1 = $this->defaultOrder($this->user, $this->restaurant1->id_restaurant, $useDate, $this->community);
        $o1->save();

        $pR1D1 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver1,
            $useDate, Crunchbutton_Order_Priority::PRIORITY_HIGH,
            0, $laterDate);
        $pR1D1->save();

        $retrievePriorities = Crunchbutton_Order_Priority::q('select * from order_priority where id_order_priority=?', [$pR1D1->id_order_priority]);

        $o1->delete();
        $pR1D1->delete();
        $this->assertNotNull($retrievePriorities);
        $this->assertEquals($retrievePriorities->count(), 1);
        $retrievePriority = $retrievePriorities->get(0);
        $this->assertEquals($retrievePriority->id_order, $pR1D1->id_order);
        $this->assertEquals($retrievePriority->id_restaurant, $pR1D1->id_restaurant);
        $this->assertEquals($retrievePriority->id_admin, $pR1D1->id_admin);
        $this->assertEquals($retrievePriority->priority_time, $pR1D1->priority_time);
        $this->assertEquals($retrievePriority->priority_algo_version, $pR1D1->priority_algo_version);
        $this->assertEquals($retrievePriority->priority_given, $pR1D1->priority_given);
        $this->assertEquals($retrievePriority->seconds_delay, $pR1D1->seconds_delay);
        $this->assertEquals($retrievePriority->priority_expiration, $pR1D1->priority_expiration);
    }

    // Make sure we're saving to the db as expected
    public function testSaveOrderPriorityToDb2()
    {
        $seconds = 20;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate = $now->format('Y-m-d H:i:s');

        $seconds = Crunchbutton_Order_Logistics::TIME_MAX_DELAY - 20;
        $later = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $later->modify('+ ' . $seconds . ' seconds');
        $laterDate = $later->format('Y-m-d H:i:s');

        $o1 = $this->defaultOrder($this->user, $this->restaurant1->id_restaurant, $useDate, $this->community);
        $o1->save();

        $pR1D1 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver1,
            $useDate, Crunchbutton_Order_Priority::PRIORITY_NO_ONE,
            Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);
        $pR1D1->save();

        $retrievePriorities = Crunchbutton_Order_Priority::q('select * from order_priority where id_order_priority=?', [$pR1D1->id_order_priority]);

        $o1->delete();
        $pR1D1->delete();
        $this->assertNotNull($retrievePriorities);
        $this->assertEquals($retrievePriorities->count(), 1);
        $retrievePriority = $retrievePriorities->get(0);
        $this->assertEquals($retrievePriority->id_order, $pR1D1->id_order);
        $this->assertEquals($retrievePriority->id_restaurant, $pR1D1->id_restaurant);
        $this->assertEquals($retrievePriority->id_admin, $pR1D1->id_admin);
        $this->assertEquals($retrievePriority->priority_time, $pR1D1->priority_time);
        $this->assertEquals($retrievePriority->priority_algo_version, $pR1D1->priority_algo_version);
        $this->assertEquals($retrievePriority->priority_given, $pR1D1->priority_given);
        $this->assertEquals($retrievePriority->seconds_delay, $pR1D1->seconds_delay);
        $this->assertEquals($retrievePriority->priority_expiration, $pR1D1->priority_expiration);
    }


    // We should correctly retrieve this OrderPriority because:
    //   1. It's the correct restaurant
    //   2. It's the correct admin
    //   3. Its creation time is within the time window
    public function testGetOrderPriorityInWindow()
    {
        $seconds = 20;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate = $now->format('Y-m-d H:i:s');

        $seconds = Crunchbutton_Order_Logistics::TIME_MAX_DELAY - 20;
        $later = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $later->modify('+ ' . $seconds . ' seconds');
        $laterDate = $later->format('Y-m-d H:i:s');

        $r1Id = $this->restaurant1->id_restaurant;

        $o1 = $this->defaultOrder($this->user, $this->restaurant1->id_restaurant, $useDate, $this->community);
        $o1->save();

        $pR1D1 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver1,
            $useDate, Crunchbutton_Order_Priority::PRIORITY_HIGH,
            0, $laterDate);
        $pR1D1->save();

        $ps = Crunchbutton_Order_Priority::priorityOrders(60, $this->driver1->id_admin, $r1Id);

        $this->assertEquals($ps->count(), 1);
        $pR1D1->delete();
        $o1->delete();

    }

    // We should correctly NOT retrieve this OrderPriority because:
    //   1. Its creation time is outside the time window
    public function testGetPriorityOrderOutsideWindow()
    {
        $seconds = 200;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate = $now->format('Y-m-d H:i:s');

        $this->assertLessThan(200, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
        $seconds = 200 - Crunchbutton_Order_Logistics::TIME_MAX_DELAY;
        $later = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $later->modify('- ' . $seconds . ' seconds');
        $laterDate = $later->format('Y-m-d H:i:s');

        $r1Id = $this->restaurant1->id_restaurant;

        $o1 = $this->defaultOrder($this->user, $this->restaurant1->id_restaurant, $useDate, $this->community);
        $o1->save();

        $pR1D1 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver1,
            $useDate, Crunchbutton_Order_Priority::PRIORITY_HIGH,
            0, $laterDate);
        $pR1D1->save();

        $ps = Crunchbutton_Order_Priority::priorityOrders(60, $this->driver1->id_admin, $r1Id);

        $this->assertNotEquals($ps->count(), 1);
        $pR1D1->delete();
        $o1->delete();

    }

    // We should correctly retrieve 3 of the OrderPriority's because:
    //   1. It's the correct restaurant
    //   2. It's the correct admin
    //   3. Its creation time is within the time window
    // The rest will fail at least of these criteria.
    public function testGetPriorityOrdersInsideAndOutsideWindow()
    {
        $seconds = 200;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 20;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');

        $seconds = 120;
        $later = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $later->modify('+ ' . $seconds . ' seconds');
        $laterDate = $now->format('Y-m-d H:i:s');

        $r1Id = $this->restaurant1->id_restaurant;

        $o1 = $this->defaultOrder($this->user, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o1->save();

        // Outside time window - bad
        $pR1D1a = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver1,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_HIGH,
            0, $useDate1);
        $pR1D1a->save();

        // Inside time window - good
        $pR1D1b = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver1,
            $useDate2, Crunchbutton_Order_Priority::PRIORITY_HIGH,
            0, $laterDate);
        $pR1D1b->save();

        // Inside time window - good
        $pR1D1c = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver1,
            $useDate2, Crunchbutton_Order_Priority::PRIORITY_HIGH,
            0, $laterDate);
        $pR1D1c->save();

        // Inside time window - good
        $pR1D1d = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver1,
            $useDate2, Crunchbutton_Order_Priority::PRIORITY_HIGH,
            0, $laterDate);
        $pR1D1d->save();

        // Different admin - bad
        $pR1D2e = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver2,
            $useDate2, Crunchbutton_Order_Priority::PRIORITY_HIGH,
            0, $laterDate);
        $pR1D2e->save();

        // Different restaurant - bad
        $pR2D1f = $this->defaultOrderPriority($o1, $this->restaurant2, $this->driver1,
            $useDate2, Crunchbutton_Order_Priority::PRIORITY_HIGH,
            0, $laterDate);

        $pR2D1f->save();

        $ps = Crunchbutton_Order_Priority::priorityOrders(60, $this->driver1->id_admin, $r1Id);

        $this->assertEquals($ps->count(), 3);
        $pR1D1a->delete();
        $pR1D1b->delete();
        $pR1D1c->delete();
        $pR1D1d->delete();
        $pR1D2e->delete();
        $pR2D1f->delete();
        $o1->delete();

    }

    // This test isn't for the logistics code per se
    public function testGetDriversToNotify()
    {
        $useDate = date('Y-m-d H:i:s');
        $o1 = $this->defaultOrder($this->user, $this->restaurant1->id_restaurant, $useDate, $this->community);
        $o1->save();

        $drivers = $o1->getDriversToNotify();
        $this->assertEquals($drivers->count(), 3);
        $o1->delete();
    }

    // This test isn't for the logistics code per se
    public function testRestaurantsHeDeliveryFor()
    {
        $d = $this->driver1->restaurantsHeDeliveryFor();
        $this->assertEquals($d->count(), 6);
    }

    // This test isn't for the logistics code per se
    public function testAllPlacesHeDeliveryFor()
    {
        $d = $this->driver1->allPlacesHeDeliveryFor();
        $this->assertEquals(count($d), 6);
    }


    // This test isn't for the logistics code per se
    public function testGetDeliveryOrders()
    {
        $hours = 2;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $hours . ' hours');
        $useDateBad = $now->format('Y-m-d H:i:s');

        $seconds = 20;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDateGood = $now->format('Y-m-d H:i:s');


        $o1 = $this->defaultOrder($this->user, $this->restaurant1->id_restaurant, $useDateBad, $this->community);
        $o1->save();

        $o2 = $this->defaultOrder($this->user, $this->restaurant1->id_restaurant, $useDateGood, $this->community);
        $o2->save();

        $o3 = $this->defaultOrder($this->user, $this->restaurant2->id_restaurant, $useDateGood, $this->community);
        $o3->save();

        $ordersUnfiltered = Order::deliveryOrders(1, false, $this->driver1);
        $o1->delete();
        $o2->delete();
        $o3->delete();
        $this->assertEquals($ordersUnfiltered->count(), 2);

    }


    // This test isn't for the logistics code per se
    public function testOrderStatus()
    {
        $seconds = 20;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate = $now->format('Y-m-d H:i:s');

        $r1Id = $this->restaurant1->id_restaurant;

        $o1 = $this->defaultOrder($this->user, $this->restaurant1->id_restaurant, $useDate, $this->community);
        $o1->save();

        $lastStatus = $o1->status()->last()['status'];
        $o1->delete();
        $this->assertEquals($lastStatus, "new");
    }

    // No other orders in the system, no priority
    public function testLogisticsSingleOrder()
    {
        $seconds = 20;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate = $now->format('Y-m-d H:i:s');

        $r1Id = $this->restaurant1->id_restaurant;

        $o1 = $this->defaultOrder($this->user, $this->restaurant1->id_restaurant, $useDate, $this->community);
        $o1->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_SIMPLE, $o1);
        $ol->process();

        foreach ($ol->drivers() as $driver) {
            $this->assertEquals($driver->__seconds, 0);
            $this->assertEquals($driver->__priority, true);
        }
        $this->assertEquals($ol->numDriversWithPriority, 3);
        $o1->delete();
    }

    // One other new order in the system within the last n minutes, given to driver 1.
    //  New order from same restaurant.
    //  Should assign to driver 1.
    public function testLogisticsTwoOrdersSameRestaurant()
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

        $pR1D1 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver1,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_HIGH,
            0, $laterDate);
        $pR1D1->save();

        $pR1D2 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver2,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
            Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);
        $pR1D2->save();

        $pR1D3 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver3,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
            Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);

        $pR1D3->save();

        $pR1D4 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver4,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
            Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);
        $pR1D4->save();

        $o2 = $this->defaultOrder($this->user, $this->restaurant1->id_restaurant, $useDate2, $this->community);
        $o2->save();


        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_SIMPLE, $o2);
        $ol->process();

        $pR1D1->delete();
        $pR1D2->delete();
        $pR1D3->delete();
        $pR1D4->delete();
        $o1->delete();
        $o2->delete();
        foreach ($ol->drivers() as $driver) {
//            print "Driver seconds: ".$driver->id_admin." ".$driver->__seconds."\n";
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);

    }

    // One other new order in the system within the last n minutes, given to driver 1.
    //  New order from same restaurant and customer 2 is in a similar location
    //  Should assign to driver 1.
    public function testLogisticsTwoOrdersSameRestaurantWithGeo1()
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
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        $pR1D1 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver1,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_HIGH,
            0, $laterDate);
        $pR1D1->save();

        $pR1D2 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver2,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
            Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);
        $pR1D2->save();

        $pR1D3 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver3,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
            Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);

        $pR1D3->save();

        $pR1D4 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver4,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
            Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);
        $pR1D4->save();

        $o2 = $this->defaultOrder($this->user, $this->restaurant1->id_restaurant, $useDate2, $this->community);
        $o2->lat = 34.0284;
        $o2->lon = -118.287;
        $o2->save();


        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_SIMPLE, $o2);
        $ol->process();

        $pR1D1->delete();
        $pR1D2->delete();
        $pR1D3->delete();
        $pR1D4->delete();
        $o1->delete();
        $o2->delete();
        foreach ($ol->drivers() as $driver) {
//            print "Driver seconds: ".$driver->id_admin." ".$driver->__seconds."\n";
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);

    }

    // One other new order in the system within the last n minutes, given to driver 1.
    //  New order from same restaurant and customer 2 is in a different location
    //  Should assign to neither driver.
    public function testLogisticsTwoOrdersSameRestaurantWithGeo2()
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
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        $pR1D1 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver1,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_HIGH,
            0, $laterDate);
        $pR1D1->save();

        $pR1D2 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver2,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
            Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);
        $pR1D2->save();

        $pR1D3 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver3,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
            Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);

        $pR1D3->save();

        $pR1D4 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver4,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
            Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);
        $pR1D4->save();

        $o2 = $this->defaultOrder($this->user, $this->restaurant1->id_restaurant, $useDate2, $this->community);
        $o2->lat = 35.0284;
        $o2->lon = -120.287;
        $o2->save();


        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_SIMPLE, $o2);
        $ol->process();

        $pR1D1->delete();
        $pR1D2->delete();
        $pR1D3->delete();
        $pR1D4->delete();
        $o1->delete();
        $o2->delete();
        foreach ($ol->drivers() as $driver) {
//            print "Driver seconds: ".$driver->id_admin." ".$driver->__seconds."\n";
            $this->assertEquals($driver->__seconds, 0);
            $this->assertEquals($driver->__priority, true);
        }
        $this->assertEquals($ol->numDriversWithPriority, 3);

    }


    // One other new order in the system within the last n minutes, given to driver 1, but refunded and do_not_reimburse_driver = true.
    //  New order from same restaurant.
    //  Should assign to neither driver.
    public function testLogisticsTwoOrdersSameRestaurantWithRefundA()
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
        $o1->refunded = true;
        $o1->do_not_reimburse_driver = true;
        $o1->save();

        $pR1D1 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver1,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_HIGH,
            0, $laterDate);
        $pR1D1->save();

        $pR1D2 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver2,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
            Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);
        $pR1D2->save();

        $pR1D3 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver3,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
            Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);

        $pR1D3->save();

        $pR1D4 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver4,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
            Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);
        $pR1D4->save();

        $o2 = $this->defaultOrder($this->user, $this->restaurant1->id_restaurant, $useDate2, $this->community);
        $o2->save();


        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_SIMPLE, $o2);
        $ol->process();

        $pR1D1->delete();
        $pR1D2->delete();
        $pR1D3->delete();
        $pR1D4->delete();
        $o1->delete();
        $o2->delete();
        foreach ($ol->drivers() as $driver) {
//            print "Driver seconds: ".$driver->id_admin." ".$driver->__seconds."\n";
            $this->assertEquals($driver->__seconds, 0);
            $this->assertEquals($driver->__priority, true);
        }
        $this->assertEquals($ol->numDriversWithPriority, 3);
    }


    // One other new order in the system within the last n minutes, given to driver 1, but refunded and do_not_reimburse_driver = false.
    //  New order from same restaurant.
    //  Should assign to driver 1.
    public function testLogisticsTwoOrdersSameRestaurantWithRefundB()
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
        $o1->refunded = true;
        $o1->do_not_reimburse_driver = false;
        $o1->save();

        $pR1D1 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver1,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_HIGH,
            0, $laterDate);
        $pR1D1->save();

        $pR1D2 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver2,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
            Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);
        $pR1D2->save();

        $pR1D3 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver3,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
            Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);

        $pR1D3->save();

        $pR1D4 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver4,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
            Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);
        $pR1D4->save();

        $o2 = $this->defaultOrder($this->user, $this->restaurant1->id_restaurant, $useDate2, $this->community);
        $o2->save();


        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_SIMPLE, $o2);
        $ol->process();

        $pR1D1->delete();
        $pR1D2->delete();
        $pR1D3->delete();
        $pR1D4->delete();
        $o1->delete();
        $o2->delete();
        foreach ($ol->drivers() as $driver) {
//            print "Driver seconds: ".$driver->id_admin." ".$driver->__seconds."\n";
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
    }


    // One other new order in the system within the last n minutes, given to driver 1.
    //  New order from different restaurant.
    //  Should assign to no one.
    public function testLogisticsTwoOrdersDiffRestaurant()
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

        $pR1D1 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver1,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_HIGH,
            0, $laterDate);
        $pR1D1->save();

        $pR1D2 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver2,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
            Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);
        $pR1D2->save();

        $pR1D3 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver3,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
            Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);

        $pR1D3->save();

        $pR1D4 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver4,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
            Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);
        $pR1D4->save();

        $o2 = $this->defaultOrder($this->user, $this->restaurant2->id_restaurant, $useDate2, $this->community);
        $o2->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_SIMPLE, $o2);
        $ol->process();

        $pR1D1->delete();
        $pR1D2->delete();
        $pR1D3->delete();
        $pR1D4->delete();
        $o1->delete();
        $o2->delete();
        foreach ($ol->drivers() as $driver) {
            $this->assertEquals($driver->__seconds, 0);
            $this->assertEquals($driver->__priority, true);
        }
        $this->assertEquals($ol->numDriversWithPriority, 3);
    }

    // One other new order in the system past the last n minutes, given to driver 1, but not accepted, and
    //   now expired.
    // Therefore, new order is given to no one.
    public function testLogisticsTwoOrdersButOneOld()
    {
        $seconds = 500;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');


        $seconds = 20;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');

        $r1Id = $this->restaurant1->id_restaurant;

        $this->assertLessThan(500, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
        $seconds = 500 - Crunchbutton_Order_Logistics::TIME_MAX_DELAY;
        $later = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $later->modify('- ' . $seconds . ' seconds');
        $laterDate = $later->format('Y-m-d H:i:s');

        $o1 = $this->defaultOrder($this->user, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o1->save();

        $pR1D1 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver1,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_HIGH,
            0, $laterDate);
        $pR1D1->save();

        $pR1D2 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver2,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
            Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);
        $pR1D2->save();

        $pR1D3 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver3,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
            Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);

        $pR1D3->save();

        $pR1D4 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver4,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
            Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);
        $pR1D4->save();

        $o2 = $this->defaultOrder($this->user, $this->restaurant1->id_restaurant, $useDate2, $this->community);
        $o2->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_SIMPLE, $o2);
        $ol->process();

        $pR1D1->delete();
        $pR1D2->delete();
        $pR1D3->delete();
        $pR1D4->delete();
        $o1->delete();
        $o2->delete();
        foreach ($ol->drivers() as $driver) {
            $this->assertEquals($driver->__seconds, 0);
            $this->assertEquals($driver->__priority, true);
        }
        $this->assertEquals($ol->numDriversWithPriority, 3);
    }

    // Many new orders in the system within the last n minutes, given to driver 1, and should not assign to any driver
    public function testLogisticsGTMaxOrders()
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


        $ops = [];
        for ($i = 1; $i <= Crunchbutton_Order_Logistics::MAX_BUNDLE_SIZE; $i++) {
            $o1 = $this->defaultOrder($this->user, $this->restaurant1->id_restaurant, $useDate1, $this->community);
            $o1->save();
            $ops[] = $o1;

            $pR1D1 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver1,
                $useDate1, Crunchbutton_Order_Priority::PRIORITY_HIGH,
                0, $laterDate);
            $pR1D1->save();
            $ops[] = $pR1D1;

            $pR1D2 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver2,
                $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
                Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);
            $pR1D2->save();
            $ops[] = $pR1D2;

            $pR1D3 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver3,
                $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
                Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);
            $pR1D3->save();
            $ops[] = $pR1D3;

            $pR1D4 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver4,
                $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
                Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);
            $pR1D4->save();
            $ops[] = $pR1D4;
        }

        $o2 = $this->defaultOrder($this->user, $this->restaurant1->id_restaurant, $useDate2, $this->community);
        $o2->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_SIMPLE, $o2);
        $ol->process();

        foreach ($ops as $op) {
            $op->delete();
        }
        $o2->delete();
        foreach ($ol->drivers() as $driver) {
            $this->assertEquals($driver->__seconds, 0);
            $this->assertEquals($driver->__priority, true);
        }
        $this->assertEquals($ol->numDriversWithPriority, 3);
    }

    // One delivered order in the system within the last n minutes, delivered by driver 1
    //  New order from same restaurant
    //  Should assign to no one
    //   Note - not realistically consistent with the priority in the system
    public function testLogisticsDeliveredOrder()
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

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-delivered',
            'note' => ''
        ]);
        $oa1->save();
        $o1->delivery_status = $oa1->id_order_action;
        $o1->save();
        $pR1D1 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver1,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_HIGH,
            0, $laterDate);
        $pR1D1->save();

        $pR1D2 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver2,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
            Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);
        $pR1D2->save();

        $pR1D3 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver3,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
            Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);

        $pR1D3->save();

        $pR1D4 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver4,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
            Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);
        $pR1D4->save();

        $o2 = $this->defaultOrder($this->user, $this->restaurant1->id_restaurant, $useDate2, $this->community);
        $o2->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_SIMPLE, $o2);
        $ol->process();

        $pR1D1->delete();
        $pR1D2->delete();
        $pR1D3->delete();
        $pR1D4->delete();
        $oa1->delete();
        $o1->delete();
        $o2->delete();
        foreach ($ol->drivers() as $driver) {
            $this->assertEquals($driver->__seconds, 0);
            $this->assertEquals($driver->__priority, true);
        }
        $this->assertEquals($ol->numDriversWithPriority, 3);
    }

    // One picked-up order in the system within the last n minutes, by driver 1
    //  New order from same restaurant
    //  Should assign to drivers 2 or 3
    public function testLogisticsPickedupOrder()
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

        $r1Id = $this->restaurant1->id_restaurant;

        $o1 = $this->defaultOrder($this->user, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-pickedup',
            'note' => ''
        ]);
        $oa1->save();
        $o1->delivery_status = $oa1->id_order_action;
        $o1->save();
        $pR1D1 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver1,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_HIGH,
            0, $laterDate);
        $pR1D1->save();

        $pR1D2 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver2,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
            Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);
        $pR1D2->save();

        $pR1D3 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver3,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
            Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);

        $pR1D3->save();

        $pR1D4 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver4,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
            Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);
        $pR1D4->save();

        $o2 = $this->defaultOrder($this->user, $this->restaurant1->id_restaurant, $useDate2, $this->community);
        $o2->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_SIMPLE, $o2);
        $ol->process();

        $pR1D1->delete();
        $pR1D2->delete();
        $pR1D3->delete();
        $pR1D4->delete();
        $oa1->delete();
        $o1->delete();
        $o2->delete();
        foreach ($ol->drivers() as $driver) {
//            print "Driver seconds: ".$driver->id_admin." ".$driver->__seconds."\n";
            if ($driver->id_admin != $this->driver1->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ol->numDriversWithPriority, 2);
    }


    // One order accepted in the system within the last n minutes by driver 2
    //  New order from same restaurant
    //  Should assign to driver 2
    // Also give a high priority to driver 1 for prev order, just to make sure the code doesn't screw up there
    public function testLogisticsAcceptedOrder()
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

        $r1Id = $this->restaurant1->id_restaurant;

        $o1 = $this->defaultOrder($this->user, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $o1->delivery_status = $oa1->id_order_action;
        $o1->save();

        $pR1D1 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver1,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_HIGH,
            0, $laterDate);
        $pR1D1->save();

        $pR1D2 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver2,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
            Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);
        $pR1D2->save();

        $pR1D3 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver3,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
            Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);

        $pR1D3->save();

        $pR1D4 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver4,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
            Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);
        $pR1D4->save();

        $o2 = $this->defaultOrder($this->user, $this->restaurant1->id_restaurant, $useDate2, $this->community);
        $o2->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_SIMPLE, $o2);
        $ol->process();

        $pR1D1->delete();
        $pR1D2->delete();
        $pR1D3->delete();
        $pR1D4->delete();
        $oa1->delete();
        $o1->delete();
        $o2->delete();
        foreach ($ol->drivers() as $driver) {
//            print "Driver seconds: ".$driver->id_admin." ".$driver->__seconds."\n";
            if ($driver->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
    }

    // One order accepted in the system within the last n minutes by driver 2.  However, order is refunded and do_not_reimburse_driver = true
    //  New order from same restaurant
    //  Should assign priority to no driver.
    // Also give a high priority to driver 1 for prev order, just to make sure the code doesn't screw up there
    public function testLogisticsAcceptedOrderWithRefundA()
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

        $r1Id = $this->restaurant1->id_restaurant;

        $o1 = $this->defaultOrder($this->user, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o1->refunded = true;
        $o1->do_not_reimburse_driver = true;
        $o1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $o1->delivery_status = $oa1->id_order_action;
        $o1->save();

        $pR1D1 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver1,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_HIGH,
            0, $laterDate);
        $pR1D1->save();

        $pR1D2 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver2,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
            Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);
        $pR1D2->save();

        $pR1D3 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver3,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
            Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);

        $pR1D3->save();

        $pR1D4 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver4,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
            Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);
        $pR1D4->save();

        $o2 = $this->defaultOrder($this->user, $this->restaurant1->id_restaurant, $useDate2, $this->community);
        $o2->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_SIMPLE, $o2);
        $ol->process();

        $pR1D1->delete();
        $pR1D2->delete();
        $pR1D3->delete();
        $pR1D4->delete();
        $oa1->delete();
        $o1->delete();
        $o2->delete();
        foreach ($ol->drivers() as $driver) {
            $this->assertEquals($driver->__seconds, 0);
            $this->assertEquals($driver->__priority, true);
        }
        $this->assertEquals($ol->numDriversWithPriority, 3);
    }

    // One order accepted in the system within the last n minutes by driver 2.  However, order is refunded and do_not_reimburse_driver = false
    //  New order from same restaurant
    //  Should assign to driver 2
    // Also give a high priority to driver 1 for prev order, just to make sure the code doesn't screw up there
    public function testLogisticsAcceptedOrderWithRefundB()
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

        $r1Id = $this->restaurant1->id_restaurant;

        $o1 = $this->defaultOrder($this->user, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o1->refunded = true;
        $o1->do_not_reimburse_driver = false;
        $o1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $o1->delivery_status = $oa1->id_order_action;
        $o1->save();

        $pR1D1 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver1,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_HIGH,
            0, $laterDate);
        $pR1D1->save();

        $pR1D2 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver2,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
            Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);
        $pR1D2->save();

        $pR1D3 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver3,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
            Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);

        $pR1D3->save();

        $pR1D4 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver4,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
            Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);
        $pR1D4->save();

        $o2 = $this->defaultOrder($this->user, $this->restaurant1->id_restaurant, $useDate2, $this->community);
        $o2->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_SIMPLE, $o2);
        $ol->process();

        $pR1D1->delete();
        $pR1D2->delete();
        $pR1D3->delete();
        $pR1D4->delete();
        $oa1->delete();
        $o1->delete();
        $o2->delete();
        foreach ($ol->drivers() as $driver) {
//            print "Driver seconds: ".$driver->id_admin." ".$driver->__seconds."\n";
            if ($driver->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
    }


    // One order accepted in the system outside the last n minutes by driver 2
    //  New order from same restaurant
    //  Priority given to drivers 1 and 3
    // Also give a high priority to driver 1 for prev order, just to make sure the code doesn't screw up there
    public function testLogisticsAcceptedOldOrder()
    {
        $seconds = 1000;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 20;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');

        $this->assertLessThan(1000, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
        $seconds = 1000 - Crunchbutton_Order_Logistics::TIME_MAX_DELAY;
        $later = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $later->modify('- ' . $seconds . ' seconds');
        $laterDate = $later->format('Y-m-d H:i:s');

        $r1Id = $this->restaurant1->id_restaurant;

        $o1 = $this->defaultOrder($this->user, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $o1->delivery_status = $oa1->id_order_action;
        $o1->save();

        $pR1D1 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver1,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_HIGH,
            0, $laterDate);
        $pR1D1->save();

        $pR1D2 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver2,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
            Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);
        $pR1D2->save();

        $pR1D3 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver3,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
            Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);

        $pR1D3->save();

        $pR1D4 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver4,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
            Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);
        $pR1D4->save();

        $o2 = $this->defaultOrder($this->user, $this->restaurant1->id_restaurant, $useDate2, $this->community);
        $o2->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_SIMPLE, $o2);
        $ol->process();

        $pR1D1->delete();
        $pR1D2->delete();
        $pR1D3->delete();
        $pR1D4->delete();
        $oa1->delete();
        $o1->delete();
        $o2->delete();
        foreach ($ol->drivers() as $driver) {
//            print "Driver seconds: ".$driver->id_admin." ".$driver->__seconds."\n";
            if ($driver->id_admin != $this->driver2->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ol->numDriversWithPriority, 2);
    }

    // One order accepted in the system outside the last n minutes by driver 2
    //   One order accepted in the system inside of the last n minutes by driver 2
    //  New order from same restaurant
    //  Priority given to drivers 1 and 3
    // Also give a high priority to driver 1 for prev order, just to make sure the code doesn't screw up there
    public function testLogisticsAcceptedTwoOrdersOutsideAndInside()
    {
        $seconds = 1000;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $this->assertLessThan(1000, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
        $seconds = 1000 - Crunchbutton_Order_Logistics::TIME_MAX_DELAY;
        $later = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $later->modify('- ' . $seconds . ' seconds');
        $laterDate1 = $later->format('Y-m-d H:i:s');

        $seconds = 300;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');

        $this->assertLessThan(300, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
        $seconds = 300 - Crunchbutton_Order_Logistics::TIME_MAX_DELAY;
        $later = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $later->modify('- ' . $seconds . ' seconds');
        $laterDate2 = $later->format('Y-m-d H:i:s');

        $seconds = 20;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate3 = $now->format('Y-m-d H:i:s');


        $o1 = $this->defaultOrder($this->user, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $o1->delivery_status = $oa1->id_order_action;
        $o1->save();

        $o2 = $this->defaultOrder($this->user, $this->restaurant1->id_restaurant, $useDate2, $this->community);
        $o2->save();

        $oa2 = new Order_Action([
            'id_order' => $o2->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa2->save();
        $o2->delivery_status = $oa2->id_order_action;
        $o2->save();

        $ops = [];

        $pR1D1a = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver1,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_HIGH,
            0, $laterDate1);
        $pR1D1a->save();
        $ops[] = $pR1D1a;

        $pR1D2a = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver2,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
            Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate1);
        $pR1D2a->save();
        $ops[] = $pR1D2a;

        $pR1D3a = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver3,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
            Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate1);
        $pR1D3a->save();
        $ops[] = $pR1D3a;

        $pR1D4a = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver4,
            $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
            Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate1);
        $pR1D4a->save();
        $ops[] = $pR1D4a;

        $pR1D1b = $this->defaultOrderPriority($o2, $this->restaurant1, $this->driver1,
            $useDate2, Crunchbutton_Order_Priority::PRIORITY_HIGH,
            0, $laterDate2);
        $pR1D1b->save();
        $ops[] = $pR1D1b;

        $pR1D2b = $this->defaultOrderPriority($o2, $this->restaurant1, $this->driver2,
            $useDate2, Crunchbutton_Order_Priority::PRIORITY_LOW,
            Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate2);
        $pR1D2b->save();
        $ops[] = $pR1D2b;

        $pR1D3b = $this->defaultOrderPriority($o2, $this->restaurant1, $this->driver3,
            $useDate2, Crunchbutton_Order_Priority::PRIORITY_LOW,
            Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate2);
        $pR1D3b->save();
        $ops[] = $pR1D3b;

        $pR1D4b = $this->defaultOrderPriority($o2, $this->restaurant1, $this->driver4,
            $useDate2, Crunchbutton_Order_Priority::PRIORITY_LOW,
            Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate2);
        $pR1D4b->save();
        $ops[] = $pR1D4b;


        $o3 = $this->defaultOrder($this->user, $this->restaurant1->id_restaurant, $useDate3, $this->community);
        $o3->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_SIMPLE, $o3);
        $ol->process();

        foreach ($ops as $op) {
            $op->delete();
        }
        $oa1->delete();
        $oa2->delete();
        $o1->delete();
        $o2->delete();
        $o3->delete();
        foreach ($ol->drivers() as $driver) {
//            print "Driver seconds: ".$driver->id_admin." ".$driver->__seconds."\n";
            if ($driver->id_admin != $this->driver2->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ol->numDriversWithPriority, 2);
    }

    // N -1 orders accepted in the system inside the last n minutes by driver 2
    //  New order from same restaurant
    //  Priority given to driver 2
    public function testLogisticsMaxAcceptedOrders()
    {
        $seconds = 300;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 20;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');

        $this->assertLessThan(300, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
        $seconds = 300 - Crunchbutton_Order_Logistics::TIME_MAX_DELAY;
        $later = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $later->modify('- ' . $seconds . ' seconds');
        $laterDate = $later->format('Y-m-d H:i:s');

        $r1Id = $this->restaurant1->id_restaurant;

        $orders = [];
        $oas = [];

        for ($i = 1; $i < Crunchbutton_Order_Logistics::MAX_BUNDLE_SIZE; $i++) {
            $o1 = $this->defaultOrder($this->user, $this->restaurant1->id_restaurant, $useDate1, $this->community);
            $o1->save();
            $orders[] = $o1;

            $oa1 = new Order_Action([
                'id_order' => $o1->id_order,
                'id_admin' => $this->driver2->id_admin,
                'timestamp' => $useDate1,
                'type' => 'delivery-accepted',
                'note' => ''
            ]);
            $oa1->save();
            $o1->delivery_status = $oa1->id_order_action;
            $o1->save();
            $oas[] = $oa1;

            $pR1D1 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver1,
                $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
                Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);
            $pR1D1->save();
            $oas[] = $pR1D1;

            $pR1D2 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver2,
                $useDate1, Crunchbutton_Order_Priority::PRIORITY_HIGH,
                0, $laterDate);
            $pR1D2->save();
            $oas[] = $pR1D2;

            $pR1D3 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver3,
                $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
                Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);
            $pR1D3->save();
            $oas[] = $pR1D3;

            $pR1D4 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver4,
                $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
                Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);
            $pR1D4->save();
            $oas[] = $pR1D4;

        }

        $o2 = $this->defaultOrder($this->user, $this->restaurant1->id_restaurant, $useDate2, $this->community);
        $o2->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_SIMPLE, $o2);
        $ol->process();

        foreach ($orders as $order) {
            $order->delete();
        }
        foreach ($oas as $oa) {
            $oa->delete();
        }

        $o2->delete();
        foreach ($ol->drivers() as $driver) {
//            print "Driver seconds: ".$driver->id_admin." ".$driver->__seconds."\n";
            if ($driver->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
    }

    // N orders accepted in the system inside the last n minutes by driver 2
    //  New order from same restaurant
    //  Priority given to drivers 1 and 3
    public function testLogisticsTooManyAcceptedOrders()
    {
        $seconds = 300;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 20;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');

        $this->assertLessThan(300, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
        $seconds = 300 - Crunchbutton_Order_Logistics::TIME_MAX_DELAY;
        $later = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $later->modify('- ' . $seconds . ' seconds');
        $laterDate = $later->format('Y-m-d H:i:s');

        $r1Id = $this->restaurant1->id_restaurant;

        $orders = [];
        $oas = [];

        for ($i = 1; $i <= Crunchbutton_Order_Logistics::MAX_BUNDLE_SIZE; $i++) {
            $o1 = $this->defaultOrder($this->user, $this->restaurant1->id_restaurant, $useDate1, $this->community);
            $o1->save();
            $orders[] = $o1;

            $oa1 = new Order_Action([
                'id_order' => $o1->id_order,
                'id_admin' => $this->driver2->id_admin,
                'timestamp' => $useDate1,
                'type' => 'delivery-accepted',
                'note' => ''
            ]);
            $oa1->save();
            $o1->delivery_status = $oa1->id_order_action;
            $o1->save();
            $oas[] = $oa1;

            $pR1D1 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver1,
                $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
                Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);
            $pR1D1->save();
            $oas[] = $pR1D1;

            $pR1D2 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver2,
                $useDate1, Crunchbutton_Order_Priority::PRIORITY_HIGH,
                0, $laterDate);
            $pR1D2->save();
            $oas[] = $pR1D2;

            $pR1D3 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver3,
                $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
                Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);
            $pR1D3->save();
            $oas[] = $pR1D3;

            $pR1D4 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver4,
                $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
                Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);
            $pR1D4->save();
            $oas[] = $pR1D4;


        }

        $o2 = $this->defaultOrder($this->user, $this->restaurant1->id_restaurant, $useDate2, $this->community);
        $o2->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_SIMPLE, $o2);
        $ol->process();

        foreach ($orders as $order) {
            $order->delete();
        }
        foreach ($oas as $oa) {
            $oa->delete();
        }

        $o2->delete();
        foreach ($ol->drivers() as $driver) {
//            print "Driver seconds: ".$driver->id_admin." ".$driver->__seconds."\n";
            if ($driver->id_admin != $this->driver2->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ol->numDriversWithPriority, 2);
    }

    // N -1 orders accepted in the system inside the last n minutes by driver 2
    // One new order given to driver 2 but not accepted yet
    //  New order from same restaurant
    //  Priority given to drivers 1 and 3
    public function testLogisticsMaxAcceptedPlusOneNewOrders()
    {
        $seconds = 300;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 20;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');

        $this->assertLessThan(300, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
        $seconds = 300 - Crunchbutton_Order_Logistics::TIME_MAX_DELAY;
        $later = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $later->modify('- ' . $seconds . ' seconds');
        $laterDate = $later->format('Y-m-d H:i:s');

        $this->assertGreaterThan(20, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
        $seconds = Crunchbutton_Order_Logistics::TIME_MAX_DELAY - 20;
        $later = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $later->modify('+ ' . $seconds . ' seconds');
        $laterDate2 = $later->format('Y-m-d H:i:s');

        $r1Id = $this->restaurant1->id_restaurant;

        $orders = [];
        $oas = [];

        for ($i = 1; $i < Crunchbutton_Order_Logistics::MAX_BUNDLE_SIZE; $i++) {
            $o1 = $this->defaultOrder($this->user, $this->restaurant1->id_restaurant, $useDate1, $this->community);
            $o1->save();
            $orders[] = $o1;

            $oa1 = new Order_Action([
                'id_order' => $o1->id_order,
                'id_admin' => $this->driver2->id_admin,
                'timestamp' => $useDate1,
                'type' => 'delivery-accepted',
                'note' => ''
            ]);
            $oa1->save();
            $o1->delivery_status = $oa1->id_order_action;
            $o1->save();
            $oas[] = $oa1;

            $pR1D1 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver1,
                $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
                Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);
            $pR1D1->save();
            $oas[] = $pR1D1;

            $pR1D2 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver2,
                $useDate1, Crunchbutton_Order_Priority::PRIORITY_HIGH,
                0, $laterDate);
            $pR1D2->save();
            $oas[] = $pR1D2;

            $pR1D3 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver3,
                $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
                Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);
            $pR1D3->save();
            $oas[] = $pR1D3;

            $pR1D4 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver4,
                $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
                Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);
            $pR1D4->save();
            $oas[] = $pR1D4;

        }
        $o2 = $this->defaultOrder($this->user, $this->restaurant1->id_restaurant, $useDate2, $this->community);
        $o2->save();

        $pR1D1 = $this->defaultOrderPriority($o2, $this->restaurant1, $this->driver1,
            $useDate2, Crunchbutton_Order_Priority::PRIORITY_LOW,
            Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate2);
        $pR1D1->save();
        $oas[] = $pR1D1;

        $pR1D2 = $this->defaultOrderPriority($o2, $this->restaurant1, $this->driver2,
            $useDate2, Crunchbutton_Order_Priority::PRIORITY_HIGH,
            0, $laterDate2);
        $pR1D2->save();
        $oas[] = $pR1D2;

        $pR1D3 = $this->defaultOrderPriority($o2, $this->restaurant1, $this->driver3,
            $useDate2, Crunchbutton_Order_Priority::PRIORITY_LOW,
            Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate2);
        $pR1D3->save();
        $oas[] = $pR1D3;

        $pR1D4 = $this->defaultOrderPriority($o2, $this->restaurant1, $this->driver4,
            $useDate2, Crunchbutton_Order_Priority::PRIORITY_LOW,
            Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate2);
        $pR1D4->save();
        $oas[] = $pR1D4;

        $o3 = $this->defaultOrder($this->user, $this->restaurant1->id_restaurant, $useDate2, $this->community);
        $o3->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_SIMPLE, $o3);
        $ol->process();

        foreach ($orders as $order) {
            $order->delete();
        }
        foreach ($oas as $oa) {
            $oa->delete();
        }

        $o2->delete();
        $o3->delete();
        foreach ($ol->drivers() as $driver) {
//            print "Driver seconds: ".$driver->id_admin." ".$driver->__seconds."\n";
            if ($driver->id_admin != $this->driver2->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ol->numDriversWithPriority, 2);
    }

    // N -1 orders accepted in the system inside the last n minutes by driver 2
    // One new order given to driver 2 but not accepted yet, but priority is expired
    //  New order from same restaurant
    //  Priority given to driver 2
    public function testLogisticsMaxAcceptedPlusOneNewExpiredOrders()
    {
        $seconds = 500;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 400;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');

        $this->assertLessThan(500, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
        $seconds = 500 - Crunchbutton_Order_Logistics::TIME_MAX_DELAY;
        $later = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $later->modify('- ' . $seconds . ' seconds');
        $laterDate = $later->format('Y-m-d H:i:s');

        $this->assertLessThan(400, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
        $seconds = 400 - Crunchbutton_Order_Logistics::TIME_MAX_DELAY;
        $later = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $later->modify('+ ' . $seconds . ' seconds');
        $laterDate2 = $later->format('Y-m-d H:i:s');


        $orders = [];
        $oas = [];

        for ($i = 1; $i < Crunchbutton_Order_Logistics::MAX_BUNDLE_SIZE; $i++) {
            $o1 = $this->defaultOrder($this->user, $this->restaurant1->id_restaurant, $useDate1, $this->community);
            $o1->save();
            $orders[] = $o1;

            $oa1 = new Order_Action([
                'id_order' => $o1->id_order,
                'id_admin' => $this->driver2->id_admin,
                'timestamp' => $useDate1,
                'type' => 'delivery-accepted',
                'note' => ''
            ]);
            $oa1->save();
            $o1->delivery_status = $oa1->id_order_action;
            $o1->save();
            $oas[] = $oa1;

            $pR1D1 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver1,
                $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
                Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);
            $pR1D1->save();
            $oas[] = $pR1D1;

            $pR1D2 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver2,
                $useDate1, Crunchbutton_Order_Priority::PRIORITY_HIGH,
                0, $laterDate);
            $pR1D2->save();
            $oas[] = $pR1D2;

            $pR1D3 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver3,
                $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
                Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);
            $pR1D3->save();
            $oas[] = $pR1D3;

            $pR1D4 = $this->defaultOrderPriority($o1, $this->restaurant1, $this->driver4,
                $useDate1, Crunchbutton_Order_Priority::PRIORITY_LOW,
                Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate);
            $pR1D4->save();
            $oas[] = $pR1D4;

        }
        $o2 = $this->defaultOrder($this->user, $this->restaurant1->id_restaurant, $useDate2, $this->community);
        $o2->save();

        $pR1D1 = $this->defaultOrderPriority($o2, $this->restaurant1, $this->driver1,
            $useDate2, Crunchbutton_Order_Priority::PRIORITY_LOW,
            Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate2);
        $pR1D1->save();
        $oas[] = $pR1D1;

        $pR1D2 = $this->defaultOrderPriority($o2, $this->restaurant1, $this->driver2,
            $useDate2, Crunchbutton_Order_Priority::PRIORITY_HIGH,
            0, $laterDate2);
        $pR1D2->save();
        $oas[] = $pR1D2;

        $pR1D3 = $this->defaultOrderPriority($o2, $this->restaurant1, $this->driver3,
            $useDate2, Crunchbutton_Order_Priority::PRIORITY_LOW,
            Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate2);
        $pR1D3->save();
        $oas[] = $pR1D3;

        $pR1D4 = $this->defaultOrderPriority($o2, $this->restaurant1, $this->driver4,
            $useDate2, Crunchbutton_Order_Priority::PRIORITY_LOW,
            Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDate2);
        $pR1D4->save();
        $oas[] = $pR1D4;

        $o3 = $this->defaultOrder($this->user, $this->restaurant1->id_restaurant, $useDate2, $this->community);
        $o3->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_SIMPLE, $o3);
        $ol->process();

        foreach ($orders as $order) {
            $order->delete();
        }
        foreach ($oas as $oa) {
            $oa->delete();
        }

        $o2->delete();
        $o3->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
    }

    // 2 orders accepted in the system inside the last n minutes by driver 1
    // 1 order accepted in the system inside the last n minutes by driver 2
    //  New order from same restaurant
    //  Priority given to driver 2
    public function testLogisticsGiveToFewerAccepted()
    {
        $seconds = 300;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 20;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');

        $this->assertLessThan(300, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
        $seconds = 300 - Crunchbutton_Order_Logistics::TIME_MAX_DELAY;
        $later = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $later->modify('- ' . $seconds . ' seconds');
        $laterDate = $later->format('Y-m-d H:i:s');

        $r1Id = $this->restaurant1->id_restaurant;

        $orders = [];
        $oas = [];

        $o1 = $this->defaultOrder($this->user, $r1Id, $useDate1, $this->community);
        $o1->save();
        $orders[] = $o1;

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $o1->delivery_status = $oa1->id_order_action;
        $o1->save();
        $oas[] = $oa1;


        $o2 = $this->defaultOrder($this->user, $r1Id, $useDate1, $this->community);
        $o2->save();
        $orders[] = $o2;

        $oa2 = new Order_Action([
            'id_order' => $o2->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa2->save();
        $o2->delivery_status = $oa2->id_order_action;
        $o2->save();
        $oas[] = $oa2;

        $o3 = $this->defaultOrder($this->user, $r1Id, $useDate1, $this->community);
        $o3->save();
        $orders[] = $o3;

        $oa3 = new Order_Action([
            'id_order' => $o3->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa3->save();
        $o3->delivery_status = $oa3->id_order_action;
        $o3->save();
        $oas[] = $oa3;

        $o4 = $this->defaultOrder($this->user, $r1Id, $useDate2, $this->community);
        $o4->save();
        $orders[] = $o4;

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_SIMPLE, $o4);
        $ol->process();

        foreach ($orders as $order) {
            $order->delete();
        }
        foreach ($oas as $oa) {
            $oa->delete();
        }
//        foreach ($ol->drivers() as $driver) {
//            print "Driver seconds: ".$driver->id_admin." ".$driver->__seconds."\n";
//        }

        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
    }

    // 2 orders accepted in the system inside the last n minutes by driver 1
    // 2 order accepted in the system inside the last n minutes by driver 2
    //  New order from same restaurant
    //  Priority given to drivers 1 and 2
    public function testLogisticsGiveToEqualAccepted()
    {
        $seconds = 300;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 20;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');

        $this->assertLessThan(300, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
        $seconds = 300 - Crunchbutton_Order_Logistics::TIME_MAX_DELAY;
        $later = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $later->modify('- ' . $seconds . ' seconds');
        $laterDate = $later->format('Y-m-d H:i:s');

        $r1Id = $this->restaurant1->id_restaurant;

        $orders = [];
        $oas = [];

        $o1 = $this->defaultOrder($this->user, $r1Id, $useDate1, $this->community);
        $o1->save();
        $orders[] = $o1;

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $o1->delivery_status = $oa1->id_order_action;
        $o1->save();
        $oas[] = $oa1;


        $o2 = $this->defaultOrder($this->user, $r1Id, $useDate1, $this->community);
        $o2->save();
        $orders[] = $o2;

        $oa2 = new Order_Action([
            'id_order' => $o2->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa2->save();
        $o2->delivery_status = $oa2->id_order_action;
        $o2->save();
        $oas[] = $oa2;

        $o3 = $this->defaultOrder($this->user, $r1Id, $useDate1, $this->community);
        $o3->save();
        $orders[] = $o3;

        $oa3 = new Order_Action([
            'id_order' => $o3->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa3->save();
        $o3->delivery_status = $oa3->id_order_action;
        $o3->save();
        $oas[] = $oa3;

        $o4 = $this->defaultOrder($this->user, $r1Id, $useDate1, $this->community);
        $o4->save();
        $orders[] = $o4;

        $oa4 = new Order_Action([
            'id_order' => $o4->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa4->save();
        $o4->delivery_status = $oa4->id_order_action;
        $o4->save();
        $oas[] = $oa4;

        $o5 = $this->defaultOrder($this->user, $r1Id, $useDate2, $this->community);
        $o5->save();
        $orders[] = $o5;

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_SIMPLE, $o5);
        $ol->process();

        foreach ($orders as $order) {
            $order->delete();
        }
        foreach ($oas as $oa) {
            $oa->delete();
        }
//        foreach ($ol->drivers() as $driver) {
//            print "Driver seconds: ".$driver->id_admin." ".$driver->__seconds."\n";
//        }

        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin || $driver->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ol->numDriversWithPriority, 2);
    }




    // 2 orders accepted in the system inside the last n minutes by driver 1
    // 2 order accepted in the system inside the last n minutes by driver 2
    //  New order from different restaurant
    //  Priority given to driver 3
    public function testLogisticsGiveToFreeDriver1()
    {
        $seconds = 300;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 20;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');

        $this->assertLessThan(300, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
        $seconds = 300 - Crunchbutton_Order_Logistics::TIME_MAX_DELAY;
        $later = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $later->modify('- ' . $seconds . ' seconds');
        $laterDate = $later->format('Y-m-d H:i:s');

        $r1Id = $this->restaurant1->id_restaurant;
        $r2Id = $this->restaurant2->id_restaurant;

        $orders = [];
        $oas = [];

        $o1 = $this->defaultOrder($this->user, $r1Id, $useDate1, $this->community);
        $o1->save();
        $orders[] = $o1;

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $o1->delivery_status = $oa1->id_order_action;
        $o1->save();
        $oas[] = $oa1;


        $o2 = $this->defaultOrder($this->user, $r1Id, $useDate1, $this->community);
        $o2->save();
        $orders[] = $o2;

        $oa2 = new Order_Action([
            'id_order' => $o2->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa2->save();
        $o2->delivery_status = $oa2->id_order_action;
        $o2->save();
        $oas[] = $oa2;

        $o3 = $this->defaultOrder($this->user, $r1Id, $useDate1, $this->community);
        $o3->save();
        $orders[] = $o3;

        $oa3 = new Order_Action([
            'id_order' => $o3->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa3->save();
        $o3->delivery_status = $oa3->id_order_action;
        $o3->save();
        $oas[] = $oa3;

        $o4 = $this->defaultOrder($this->user, $r1Id, $useDate1, $this->community);
        $o4->save();
        $orders[] = $o4;

        $oa4 = new Order_Action([
            'id_order' => $o4->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa4->save();
        $o4->delivery_status = $oa4->id_order_action;
        $o4->save();
        $oas[] = $oa4;

        $o5 = $this->defaultOrder($this->user, $r2Id, $useDate2, $this->community);
        $o5->save();
        $orders[] = $o5;

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_SIMPLE, $o5);
        $ol->process();

        foreach ($orders as $order) {
            $order->delete();
        }
        foreach ($oas as $oa) {
            $oa->delete();
        }
//        foreach ($ol->drivers() as $driver) {
//            print "Driver seconds: ".$driver->id_admin." ".$driver->__seconds."\n";
//        }

        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin || $driver->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            } else {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);

            }
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
    }

    // 2 orders accepted in the system inside the last n minutes by driver 1 for r1
    // 2 order accepted in the system inside the last n minutes by driver 2 for r2
    //  New order from r3
    //  Priority given to driver 3
    public function testLogisticsGiveToFreeDriver2()
    {
        $seconds = 300;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 20;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');

        $this->assertLessThan(300, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
        $seconds = 300 - Crunchbutton_Order_Logistics::TIME_MAX_DELAY;
        $later = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $later->modify('- ' . $seconds . ' seconds');
        $laterDate = $later->format('Y-m-d H:i:s');

        $r1Id = $this->restaurant1->id_restaurant;
        $r2Id = $this->restaurant2->id_restaurant;
        $r3Id = $this->restaurant3->id_restaurant;

        $orders = [];
        $oas = [];

        $o1 = $this->defaultOrder($this->user, $r1Id, $useDate1, $this->community);
        $o1->save();
        $orders[] = $o1;

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $o1->delivery_status = $oa1->id_order_action;
        $o1->save();
        $oas[] = $oa1;


        $o2 = $this->defaultOrder($this->user, $r1Id, $useDate1, $this->community);
        $o2->save();
        $orders[] = $o2;

        $oa2 = new Order_Action([
            'id_order' => $o2->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa2->save();
        $o2->delivery_status = $oa2->id_order_action;
        $o2->save();
        $oas[] = $oa2;

        $o3 = $this->defaultOrder($this->user, $r2Id, $useDate1, $this->community);
        $o3->save();
        $orders[] = $o3;

        $oa3 = new Order_Action([
            'id_order' => $o3->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa3->save();
        $o3->delivery_status = $oa3->id_order_action;
        $o3->save();
        $oas[] = $oa3;

        $o4 = $this->defaultOrder($this->user, $r2Id, $useDate1, $this->community);
        $o4->save();
        $orders[] = $o4;

        $oa4 = new Order_Action([
            'id_order' => $o4->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa4->save();
        $o4->delivery_status = $oa4->id_order_action;
        $o4->save();
        $oas[] = $oa4;

        $o5 = $this->defaultOrder($this->user, $r3Id, $useDate2, $this->community);
        $o5->save();
        $orders[] = $o5;

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_SIMPLE, $o5);
        $ol->process();

        foreach ($orders as $order) {
            $order->delete();
        }
        foreach ($oas as $oa) {
            $oa->delete();
        }
//        foreach ($ol->drivers() as $driver) {
//            print "Driver seconds: ".$driver->id_admin." ".$driver->__seconds."\n";
//        }

        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin || $driver->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            } else {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);

            }
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
    }


    // 4 orders accepted/picked-up in the system inside the last n minutes by driver 1 for r1, r2, r3, r4
    // 4 orders accepted in the system inside the last n minutes by driver 2 for r2, r3, r4, r5
    //  New order from r6
    //  Priority given to driver 3
    public function testLogisticsGiveToFreeDriver3()
    {
        $seconds = 300;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 20;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');

        $this->assertLessThan(300, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
        $seconds = 300 - Crunchbutton_Order_Logistics::TIME_MAX_DELAY;
        $later = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $later->modify('- ' . $seconds . ' seconds');
        $laterDate = $later->format('Y-m-d H:i:s');

        $r1Id = $this->restaurant1->id_restaurant;
        $r2Id = $this->restaurant2->id_restaurant;
        $r3Id = $this->restaurant3->id_restaurant;
        $r4Id = $this->restaurant4->id_restaurant;
        $r5Id = $this->restaurant5->id_restaurant;
        $r6Id = $this->restaurant6->id_restaurant;
        $orders = [];
        $oas = [];

        $o1 = $this->defaultOrder($this->user, $r1Id, $useDate1, $this->community);
        $o1->save();
        $orders[] = $o1;

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $o1->delivery_status = $oa1->id_order_action;
        $o1->save();
        $oas[] = $oa1;


        $o2 = $this->defaultOrder($this->user, $r2Id, $useDate1, $this->community);
        $o2->save();
        $orders[] = $o2;

        $oa2 = new Order_Action([
            'id_order' => $o2->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-pickedup',
            'note' => ''
        ]);
        $oa2->save();
        $o2->delivery_status = $oa2->id_order_action;
        $o2->save();
        $oas[] = $oa2;

        $o3 = $this->defaultOrder($this->user, $r3Id, $useDate1, $this->community);
        $o3->save();
        $orders[] = $o3;

        $oa3 = new Order_Action([
            'id_order' => $o3->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa3->save();
        $o3->delivery_status = $oa3->id_order_action;
        $o3->save();
        $oas[] = $oa3;

        $o4 = $this->defaultOrder($this->user, $r4Id, $useDate1, $this->community);
        $o4->save();
        $orders[] = $o4;

        $oa4 = new Order_Action([
            'id_order' => $o4->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-pickedup',
            'note' => ''
        ]);
        $oa4->save();
        $o4->delivery_status = $oa4->id_order_action;
        $o4->save();
        $oas[] = $oa4;

        $o2b = $this->defaultOrder($this->user, $r2Id, $useDate1, $this->community);
        $o2b->save();
        $orders[] = $o2b;

        $oa2b = new Order_Action([
            'id_order' => $o2b->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa2b->save();
        $o2b->delivery_status = $oa2b->id_order_action;
        $o2b->save();

        $oas[] = $oa2b;

        $o3b = $this->defaultOrder($this->user, $r3Id, $useDate1, $this->community);
        $o3b->save();
        $orders[] = $o3b;

        $oa3b = new Order_Action([
            'id_order' => $o3b->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa3b->save();
        $o3b->delivery_status = $oa3b->id_order_action;
        $o3b->save();
        $oas[] = $oa3b;

        $o4b = $this->defaultOrder($this->user, $r4Id, $useDate1, $this->community);
        $o4b->save();
        $orders[] = $o4b;

        $oa4b = new Order_Action([
            'id_order' => $o4b->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa4b->save();
        $o4b->delivery_status = $oa4b->id_order_action;
        $o4b->save();
        $oas[] = $oa4b;

        $o5b = $this->defaultOrder($this->user, $r5Id, $useDate1, $this->community);
        $o5b->save();
        $orders[] = $o5b;

        $oa5b = new Order_Action([
            'id_order' => $o5b->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa5b->save();
        $o5b->delivery_status = $oa5b->id_order_action;
        $o5b->save();
        $oas[] = $oa5b;

        $o6 = $this->defaultOrder($this->user, $r6Id, $useDate2, $this->community);
        $o6->save();
        $orders[] = $o6;

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_SIMPLE, $o6);
        $ol->process();

        foreach ($orders as $order) {
            $order->delete();
        }
        foreach ($oas as $oa) {
            $oa->delete();
        }
//        foreach ($ol->drivers() as $driver) {
//            print "Driver seconds: ".$driver->id_admin." ".$driver->__seconds."\n";
//        }

        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin || $driver->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            } else {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);

            }
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
    }


    // 4 orders accepted/picked-up in the system inside the last n minutes by driver 1 for r1, r2, r3, r4
    // 4 orders accepted in the system inside the last n minutes by driver 2 for r2, r3, r4, r5
    //  New order from r1
    //  Priority given to driver 1
    public function testLogisticsGiveToFreeDriver4()
    {
        $seconds = 300;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 20;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');

        $this->assertLessThan(300, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
        $seconds = 300 - Crunchbutton_Order_Logistics::TIME_MAX_DELAY;
        $later = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $later->modify('- ' . $seconds . ' seconds');
        $laterDate = $later->format('Y-m-d H:i:s');

        $r1Id = $this->restaurant1->id_restaurant;
        $r2Id = $this->restaurant2->id_restaurant;
        $r3Id = $this->restaurant3->id_restaurant;
        $r4Id = $this->restaurant4->id_restaurant;
        $r5Id = $this->restaurant5->id_restaurant;
        $r6Id = $this->restaurant6->id_restaurant;
        $orders = [];
        $oas = [];

        $o1 = $this->defaultOrder($this->user, $r1Id, $useDate1, $this->community);
        $o1->save();
        $orders[] = $o1;

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $o1->delivery_status = $oa1->id_order_action;
        $o1->save();
        $oas[] = $oa1;


        $o2 = $this->defaultOrder($this->user, $r2Id, $useDate1, $this->community);
        $o2->save();
        $orders[] = $o2;

        $oa2 = new Order_Action([
            'id_order' => $o2->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-pickedup',
            'note' => ''
        ]);
        $oa2->save();
        $o2->delivery_status = $oa2->id_order_action;
        $o2->save();
        $oas[] = $oa2;

        $o3 = $this->defaultOrder($this->user, $r3Id, $useDate1, $this->community);
        $o3->save();
        $orders[] = $o3;

        $oa3 = new Order_Action([
            'id_order' => $o3->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa3->save();
        $o3->delivery_status = $oa3->id_order_action;
        $o3->save();
        $oas[] = $oa3;

        $o4 = $this->defaultOrder($this->user, $r4Id, $useDate1, $this->community);
        $o4->save();
        $orders[] = $o4;

        $oa4 = new Order_Action([
            'id_order' => $o4->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-pickedup',
            'note' => ''
        ]);
        $oa4->save();
        $o4->delivery_status = $oa4->id_order_action;
        $o4->save();
        $oas[] = $oa4;

        $o2b = $this->defaultOrder($this->user, $r2Id, $useDate1, $this->community);
        $o2b->save();
        $orders[] = $o2b;

        $oa2b = new Order_Action([
            'id_order' => $o2b->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa2b->save();
        $o2b->delivery_status = $oa2b->id_order_action;
        $o2b->save();
        $oas[] = $oa2b;

        $o3b = $this->defaultOrder($this->user, $r3Id, $useDate1, $this->community);
        $o3b->save();
        $orders[] = $o3b;

        $oa3b = new Order_Action([
            'id_order' => $o3b->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa3b->save();
        $o3->delivery_status = $oa3b->id_order_action;
        $o3->save();
        $oas[] = $oa3b;

        $o4b = $this->defaultOrder($this->user, $r4Id, $useDate1, $this->community);
        $o4b->save();
        $orders[] = $o4b;

        $oa4b = new Order_Action([
            'id_order' => $o4b->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa4b->save();
        $o4b->delivery_status = $oa4b->id_order_action;
        $o4b->save();
        $oas[] = $oa4b;

        $o5b = $this->defaultOrder($this->user, $r5Id, $useDate1, $this->community);
        $o5b->save();
        $orders[] = $o5b;

        $oa5b = new Order_Action([
            'id_order' => $o5b->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa5b->save();
        $o5b->delivery_status = $oa5b->id_order_action;
        $o5b->save();
        $oas[] = $oa5b;

        $o1b = $this->defaultOrder($this->user, $r1Id, $useDate2, $this->community);
        $o1b->save();
        $orders[] = $o1b;

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_SIMPLE, $o1b);
        $ol->process();

        foreach ($orders as $order) {
            $order->delete();
        }
        foreach ($oas as $oa) {
            $oa->delete();
        }
//        foreach ($ol->drivers() as $driver) {
//            print "Driver seconds: ".$driver->id_admin." ".$driver->__seconds."\n";
//        }

        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver2->id_admin || $driver->id_admin == $this->driver3->id_admin) {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            } else {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);

            }
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
    }

    // 4 orders accepted/picked-up in the system inside the last n minutes by driver 1 for r1, r2(pickedup), r3, r4
    // 4 orders accepted in the system inside the last n minutes by driver 2 for r2(accepted), r3, r4, r5
    //  New order from r2
    //  Priority given to driver 2
    public function testLogisticsGiveToFreeDriver5()
    {
        $seconds = 300;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 20;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');

        $this->assertLessThan(300, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
        $seconds = 300 - Crunchbutton_Order_Logistics::TIME_MAX_DELAY;
        $later = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $later->modify('- ' . $seconds . ' seconds');
        $laterDate = $later->format('Y-m-d H:i:s');

        $r1Id = $this->restaurant1->id_restaurant;
        $r2Id = $this->restaurant2->id_restaurant;
        $r3Id = $this->restaurant3->id_restaurant;
        $r4Id = $this->restaurant4->id_restaurant;
        $r5Id = $this->restaurant5->id_restaurant;
        $r6Id = $this->restaurant6->id_restaurant;
        $orders = [];
        $oas = [];

        $o1 = $this->defaultOrder($this->user, $r1Id, $useDate1, $this->community);
        $o1->save();
        $orders[] = $o1;

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $o1->delivery_status = $oa1->id_order_action;
        $o1->save();
        $oas[] = $oa1;


        $o2 = $this->defaultOrder($this->user, $r2Id, $useDate1, $this->community);
        $o2->save();
        $orders[] = $o2;

        $oa2 = new Order_Action([
            'id_order' => $o2->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-pickedup',
            'note' => ''
        ]);
        $oa2->save();
        $o2->delivery_status = $oa2->id_order_action;
        $o2->save();
        $oas[] = $oa2;

        $o3 = $this->defaultOrder($this->user, $r3Id, $useDate1, $this->community);
        $o3->save();
        $orders[] = $o3;

        $oa3 = new Order_Action([
            'id_order' => $o3->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa3->save();
        $o3->delivery_status = $oa3->id_order_action;
        $o3->save();
        $oas[] = $oa3;

        $o4 = $this->defaultOrder($this->user, $r4Id, $useDate1, $this->community);
        $o4->save();
        $orders[] = $o4;

        $oa4 = new Order_Action([
            'id_order' => $o4->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-pickedup',
            'note' => ''
        ]);
        $oa4->save();
        $o4->delivery_status = $oa4->id_order_action;
        $o4->save();
        $oas[] = $oa4;

        $o2b = $this->defaultOrder($this->user, $r2Id, $useDate1, $this->community);
        $o2b->save();
        $orders[] = $o2b;

        $oa2b = new Order_Action([
            'id_order' => $o2b->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa2b->save();
        $o2b->delivery_status = $oa2b->id_order_action;
        $o2b->save();
        $oas[] = $oa2b;

        $o3b = $this->defaultOrder($this->user, $r3Id, $useDate1, $this->community);
        $o3b->save();
        $orders[] = $o3b;

        $oa3b = new Order_Action([
            'id_order' => $o3b->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa3b->save();
        $o3b->delivery_status = $oa3b->id_order_action;
        $o3b->save();
        $oas[] = $oa3b;

        $o4b = $this->defaultOrder($this->user, $r4Id, $useDate1, $this->community);
        $o4b->save();
        $orders[] = $o4b;

        $oa4b = new Order_Action([
            'id_order' => $o4b->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa4b->save();
        $o4b->delivery_status = $oa4b->id_order_action;
        $o4b->save();
        $oas[] = $oa4b;

        $o5b = $this->defaultOrder($this->user, $r5Id, $useDate1, $this->community);
        $o5b->save();
        $orders[] = $o5b;

        $oa5b = new Order_Action([
            'id_order' => $o5b->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa5b->save();
        $o5b->delivery_status = $oa5b->id_order_action;
        $o5b->save();
        $oas[] = $oa5b;

        $o2c = $this->defaultOrder($this->user, $r2Id, $useDate2, $this->community);
        $o2c->save();
        $orders[] = $o2c;

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_SIMPLE, $o2c);
        $ol->process();

        foreach ($orders as $order) {
            $order->delete();
        }
        foreach ($oas as $oa) {
            $oa->delete();
        }
//        foreach ($ol->drivers() as $driver) {
//            print "Driver seconds: ".$driver->id_admin." ".$driver->__seconds."\n";
//        }

        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin || $driver->id_admin == $this->driver3->id_admin) {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            } else {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            }
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
    }


    // 4 orders accepted/picked-up in the system inside the last n minutes by driver 1 for r1, r2(accepted), r3, r4
    // 4 orders accepted in the system inside the last n minutes by driver 2 for r2(accepted), r3, r4, r5
    //  New order from r2
    //  Priority given to drivers 1 and 2
    public function testLogisticsGiveToFreeDriver6()
    {
        $seconds = 300;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 20;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');

        $this->assertLessThan(300, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
        $seconds = 300 - Crunchbutton_Order_Logistics::TIME_MAX_DELAY;
        $later = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $later->modify('- ' . $seconds . ' seconds');
        $laterDate = $later->format('Y-m-d H:i:s');

        $r1Id = $this->restaurant1->id_restaurant;
        $r2Id = $this->restaurant2->id_restaurant;
        $r3Id = $this->restaurant3->id_restaurant;
        $r4Id = $this->restaurant4->id_restaurant;
        $r5Id = $this->restaurant5->id_restaurant;
        $r6Id = $this->restaurant6->id_restaurant;
        $orders = [];
        $oas = [];

        $o1 = $this->defaultOrder($this->user, $r1Id, $useDate1, $this->community);
        $o1->save();
        $orders[] = $o1;

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $o1->delivery_status = $oa1->id_order_action;
        $o1->save();
        $oas[] = $oa1;


        $o2 = $this->defaultOrder($this->user, $r2Id, $useDate1, $this->community);
        $o2->save();
        $orders[] = $o2;

        $oa2 = new Order_Action([
            'id_order' => $o2->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa2->save();
        $o2->delivery_status = $oa2->id_order_action;
        $o2->save();
        $oas[] = $oa2;

        $o3 = $this->defaultOrder($this->user, $r3Id, $useDate1, $this->community);
        $o3->save();
        $orders[] = $o3;

        $oa3 = new Order_Action([
            'id_order' => $o3->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa3->save();
        $o3->delivery_status = $oa3->id_order_action;
        $o3->save();
        $oas[] = $oa3;

        $o4 = $this->defaultOrder($this->user, $r4Id, $useDate1, $this->community);
        $o4->save();
        $orders[] = $o4;

        $oa4 = new Order_Action([
            'id_order' => $o4->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-pickedup',
            'note' => ''
        ]);
        $oa4->save();
        $o4->delivery_status = $oa4->id_order_action;
        $o4->save();
        $oas[] = $oa4;

        $o2b = $this->defaultOrder($this->user, $r2Id, $useDate1, $this->community);
        $o2b->save();
        $orders[] = $o2b;

        $oa2b = new Order_Action([
            'id_order' => $o2b->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa2b->save();
        $o2b->delivery_status = $oa2b->id_order_action;
        $o2b->save();
        $oas[] = $oa2b;

        $o3b = $this->defaultOrder($this->user, $r3Id, $useDate1, $this->community);
        $o3b->save();
        $orders[] = $o3b;

        $oa3b = new Order_Action([
            'id_order' => $o3b->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa3b->save();
        $o3b->delivery_status = $oa3b->id_order_action;
        $o3b->save();
        $oas[] = $oa3b;

        $o4b = $this->defaultOrder($this->user, $r4Id, $useDate1, $this->community);
        $o4b->save();
        $orders[] = $o4b;

        $oa4b = new Order_Action([
            'id_order' => $o4b->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa4b->save();
        $o4b->delivery_status = $oa4b->id_order_action;
        $o4b->save();
        $oas[] = $oa4b;

        $o5b = $this->defaultOrder($this->user, $r5Id, $useDate1, $this->community);
        $o5b->save();
        $orders[] = $o5b;

        $oa5b = new Order_Action([
            'id_order' => $o5b->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa5b->save();
        $o5b->delivery_status = $oa5b->id_order_action;
        $o5b->save();
        $oas[] = $oa5b;

        $o2c = $this->defaultOrder($this->user, $r2Id, $useDate2, $this->community);
        $o2c->save();
        $orders[] = $o2c;

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_SIMPLE, $o2c);
        $ol->process();

        foreach ($orders as $order) {
            $order->delete();
        }
        foreach ($oas as $oa) {
            $oa->delete();
        }
//        foreach ($ol->drivers() as $driver) {
//            print "Driver seconds: ".$driver->id_admin." ".$driver->__seconds."\n";
//        }

        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin || $driver->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            } else {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            }
        }
        $this->assertEquals($ol->numDriversWithPriority, 2);
    }

    // 4 orders accepted/picked-up in the system inside the last n minutes by driver 1 for r1, r2(pickedup), r3, r4
    // 4 orders accepted in the system inside the last n minutes by driver 2 for r2(pickedup), r3, r4, r5
    //  New order from r2
    //  Priority given to driver 3
    public function testLogisticsGiveToFreeDriver7()
    {
        $seconds = 300;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 20;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');

        $this->assertLessThan(300, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
        $seconds = 300 - Crunchbutton_Order_Logistics::TIME_MAX_DELAY;
        $later = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $later->modify('- ' . $seconds . ' seconds');
        $laterDate = $later->format('Y-m-d H:i:s');

        $r1Id = $this->restaurant1->id_restaurant;
        $r2Id = $this->restaurant2->id_restaurant;
        $r3Id = $this->restaurant3->id_restaurant;
        $r4Id = $this->restaurant4->id_restaurant;
        $r5Id = $this->restaurant5->id_restaurant;
        $r6Id = $this->restaurant6->id_restaurant;
        $orders = [];
        $oas = [];

        $o1 = $this->defaultOrder($this->user, $r1Id, $useDate1, $this->community);
        $o1->save();
        $orders[] = $o1;

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $o1->delivery_status = $oa1->id_order_action;
        $o1->save();
        $oas[] = $oa1;


        $o2 = $this->defaultOrder($this->user, $r2Id, $useDate1, $this->community);
        $o2->save();
        $orders[] = $o2;

        $oa2 = new Order_Action([
            'id_order' => $o2->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-pickedup',
            'note' => ''
        ]);
        $oa2->save();
        $o2->delivery_status = $oa2->id_order_action;
        $o2->save();
        $oas[] = $oa2;

        $o3 = $this->defaultOrder($this->user, $r3Id, $useDate1, $this->community);
        $o3->save();
        $orders[] = $o3;

        $oa3 = new Order_Action([
            'id_order' => $o3->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa3->save();
        $o3->delivery_status = $oa3->id_order_action;
        $o3->save();
        $oas[] = $oa3;

        $o4 = $this->defaultOrder($this->user, $r4Id, $useDate1, $this->community);
        $o4->save();
        $orders[] = $o4;

        $oa4 = new Order_Action([
            'id_order' => $o4->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-pickedup',
            'note' => ''
        ]);
        $oa4->save();
        $o4->delivery_status = $oa4->id_order_action;
        $o4->save();
        $oas[] = $oa4;

        $o2b = $this->defaultOrder($this->user, $r2Id, $useDate1, $this->community);
        $o2b->save();
        $orders[] = $o2b;

        $oa2b = new Order_Action([
            'id_order' => $o2b->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-pickedup',
            'note' => ''
        ]);
        $oa2b->save();
        $o2b->delivery_status = $oa2b->id_order_action;
        $o2b->save();
        $oas[] = $oa2b;

        $o3b = $this->defaultOrder($this->user, $r3Id, $useDate1, $this->community);
        $o3b->save();
        $orders[] = $o3b;

        $oa3b = new Order_Action([
            'id_order' => $o3b->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa3b->save();
        $o3b->delivery_status = $oa3b->id_order_action;
        $o3b->save();
        $oas[] = $oa3b;

        $o4b = $this->defaultOrder($this->user, $r4Id, $useDate1, $this->community);
        $o4b->save();
        $orders[] = $o4b;

        $oa4b = new Order_Action([
            'id_order' => $o4b->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa4b->save();
        $o4b->delivery_status = $oa4b->id_order_action;
        $o4b->save();
        $oas[] = $oa4b;

        $o5b = $this->defaultOrder($this->user, $r5Id, $useDate1, $this->community);
        $o5b->save();
        $orders[] = $o5b;

        $oa5b = new Order_Action([
            'id_order' => $o5b->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa5b->save();
        $o5b->delivery_status = $oa5b->id_order_action;
        $o5b->save();
        $oas[] = $oa5b;

        $o2c = $this->defaultOrder($this->user, $r2Id, $useDate2, $this->community);
        $o2c->save();
        $orders[] = $o2c;

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_SIMPLE, $o2c);
        $ol->process();

        foreach ($orders as $order) {
            $order->delete();
        }
        foreach ($oas as $oa) {
            $oa->delete();
        }
//        foreach ($ol->drivers() as $driver) {
//            print "Driver seconds: ".$driver->id_admin." ".$driver->__seconds."\n";
//        }

        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin || $driver->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            } else {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);
            }
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
    }


    // 2 orders pickedup in the system inside the last n minutes by driver 1 for r1
    // 2 order pickedup in the system inside the last n minutes by driver 2 for r2
    //  New order from r3
    //  Priority given to driver 3
    public function testLogisticsGiveToFreeDriver8()
    {
        $seconds = 300;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 20;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');

        $this->assertLessThan(300, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
        $seconds = 300 - Crunchbutton_Order_Logistics::TIME_MAX_DELAY;
        $later = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $later->modify('- ' . $seconds . ' seconds');
        $laterDate = $later->format('Y-m-d H:i:s');

        $r1Id = $this->restaurant1->id_restaurant;
        $r2Id = $this->restaurant2->id_restaurant;
        $r3Id = $this->restaurant3->id_restaurant;

        $orders = [];
        $oas = [];

        $o1 = $this->defaultOrder($this->user, $r1Id, $useDate1, $this->community);
        $o1->save();
        $orders[] = $o1;

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-pickedup',
            'note' => ''
        ]);
        $oa1->save();
        $o1->delivery_status = $oa1->id_order_action;
        $o1->save();
        $oas[] = $oa1;


        $o2 = $this->defaultOrder($this->user, $r1Id, $useDate1, $this->community);
        $o2->save();
        $orders[] = $o2;

        $oa2 = new Order_Action([
            'id_order' => $o2->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-pickedup',
            'note' => ''
        ]);
        $oa2->save();
        $o2->delivery_status = $oa2->id_order_action;
        $o2->save();
        $oas[] = $oa2;

        $o3 = $this->defaultOrder($this->user, $r2Id, $useDate1, $this->community);
        $o3->save();
        $orders[] = $o3;

        $oa3 = new Order_Action([
            'id_order' => $o3->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-pickedup',
            'note' => ''
        ]);
        $oa3->save();
        $o3->delivery_status = $oa3->id_order_action;
        $o3->save();
        $oas[] = $oa3;

        $o4 = $this->defaultOrder($this->user, $r2Id, $useDate1, $this->community);
        $o4->save();
        $orders[] = $o4;

        $oa4 = new Order_Action([
            'id_order' => $o4->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-pickedup',
            'note' => ''
        ]);
        $oa4->save();
        $o4->delivery_status = $oa4->id_order_action;
        $o4->save();
        $oas[] = $oa4;

        $o5 = $this->defaultOrder($this->user, $r3Id, $useDate2, $this->community);
        $o5->save();
        $orders[] = $o5;

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_SIMPLE, $o5);
        $ol->process();

        foreach ($orders as $order) {
            $order->delete();
        }
        foreach ($oas as $oa) {
            $oa->delete();
        }
//        foreach ($ol->drivers() as $driver) {
//            print "Driver seconds: ".$driver->id_admin." ".$driver->__seconds."\n";
//        }

        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin || $driver->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            } else {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);

            }
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
    }


    // 2 orders pickedup in the system inside the last n minutes by driver 1 for r1
    // 1 order pickedup in the system inside the last n minutes by driver 2 for r2
    //  New order from r3
    //  Priority given to driver 3
    public function testLogisticsGiveToFreeDriver9()
    {
        $seconds = 300;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 20;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');

        $this->assertLessThan(300, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
        $seconds = 300 - Crunchbutton_Order_Logistics::TIME_MAX_DELAY;
        $later = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $later->modify('- ' . $seconds . ' seconds');
        $laterDate = $later->format('Y-m-d H:i:s');

        $r1Id = $this->restaurant1->id_restaurant;
        $r2Id = $this->restaurant2->id_restaurant;
        $r3Id = $this->restaurant3->id_restaurant;

        $orders = [];
        $oas = [];

        $o1 = $this->defaultOrder($this->user, $r1Id, $useDate1, $this->community);
        $o1->save();
        $orders[] = $o1;

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-pickedup',
            'note' => ''
        ]);
        $oa1->save();
        $o1->delivery_status = $oa1->id_order_action;
        $o1->save();
        $oas[] = $oa1;


        $o2 = $this->defaultOrder($this->user, $r1Id, $useDate1, $this->community);
        $o2->save();
        $orders[] = $o2;

        $oa2 = new Order_Action([
            'id_order' => $o2->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-pickedup',
            'note' => ''
        ]);
        $oa2->save();
        $o2->delivery_status = $oa2->id_order_action;
        $o2->save();
        $oas[] = $oa2;

        $o3 = $this->defaultOrder($this->user, $r2Id, $useDate1, $this->community);
        $o3->save();
        $orders[] = $o3;

        $oa3 = new Order_Action([
            'id_order' => $o3->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-pickedup',
            'note' => ''
        ]);
        $oa3->save();
        $o3->delivery_status = $oa3->id_order_action;
        $o3->save();
        $oas[] = $oa3;

        $o5 = $this->defaultOrder($this->user, $r3Id, $useDate2, $this->community);
        $o5->save();
        $orders[] = $o5;

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_SIMPLE, $o5);
        $ol->process();

        foreach ($orders as $order) {
            $order->delete();
        }
        foreach ($oas as $oa) {
            $oa->delete();
        }
//        foreach ($ol->drivers() as $driver) {
//            print "Driver seconds: ".$driver->id_admin." ".$driver->__seconds."\n";
//        }

        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin || $driver->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            } else {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);

            }
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
    }


    // 4 orders accepted/picked-up in the system inside the last n minutes by driver 1 for r1, r2(pickedup), r3, r4
    // 3 orders accepted in the system inside the last n minutes by driver 2 for r2(pickedup), r3, r4
    // 2 orders accepted in the system inside the last n minutes by driver 3 for r2(pickedup), r3
    //  New order from r6
    //  Priority given to all
    public function testLogisticsGiveToFreeDriver10()
    {
        $seconds = 300;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 20;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');

        $this->assertLessThan(300, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
        $seconds = 300 - Crunchbutton_Order_Logistics::TIME_MAX_DELAY;
        $later = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $later->modify('- ' . $seconds . ' seconds');
        $laterDate = $later->format('Y-m-d H:i:s');

        $r1Id = $this->restaurant1->id_restaurant;
        $r2Id = $this->restaurant2->id_restaurant;
        $r3Id = $this->restaurant3->id_restaurant;
        $r4Id = $this->restaurant4->id_restaurant;
        $r5Id = $this->restaurant5->id_restaurant;
        $r6Id = $this->restaurant6->id_restaurant;
        $orders = [];
        $oas = [];

        $o1 = $this->defaultOrder($this->user, $r1Id, $useDate1, $this->community);
        $o1->save();
        $orders[] = $o1;

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $o1->delivery_status = $oa1->id_order_action;
        $o1->save();
        $oas[] = $oa1;


        $o2 = $this->defaultOrder($this->user, $r2Id, $useDate1, $this->community);
        $o2->save();
        $orders[] = $o2;

        $oa2 = new Order_Action([
            'id_order' => $o2->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-pickedup',
            'note' => ''
        ]);
        $oa2->save();
        $o2->delivery_status = $oa2->id_order_action;
        $o2->save();
        $oas[] = $oa2;

        $o3 = $this->defaultOrder($this->user, $r3Id, $useDate1, $this->community);
        $o3->save();
        $orders[] = $o3;

        $oa3 = new Order_Action([
            'id_order' => $o3->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa3->save();
        $o3->delivery_status = $oa3->id_order_action;
        $o3->save();
        $oas[] = $oa3;

        $o4 = $this->defaultOrder($this->user, $r4Id, $useDate1, $this->community);
        $o4->save();
        $orders[] = $o4;

        $oa4 = new Order_Action([
            'id_order' => $o4->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-pickedup',
            'note' => ''
        ]);
        $oa4->save();
        $o4->delivery_status = $oa4->id_order_action;
        $o4->save();
        $oas[] = $oa4;

        $o2b = $this->defaultOrder($this->user, $r2Id, $useDate1, $this->community);
        $o2b->save();
        $orders[] = $o2b;

        $oa2b = new Order_Action([
            'id_order' => $o2b->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-pickedup',
            'note' => ''
        ]);
        $oa2b->save();
        $o2b->delivery_status = $oa2b->id_order_action;
        $o2b->save();
        $oas[] = $oa2b;

        $o3b = $this->defaultOrder($this->user, $r3Id, $useDate1, $this->community);
        $o3b->save();
        $orders[] = $o3b;

        $oa3b = new Order_Action([
            'id_order' => $o3b->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa3b->save();
        $o3b->delivery_status = $oa3b->id_order_action;
        $o3b->save();
        $oas[] = $oa3b;

        $o4b = $this->defaultOrder($this->user, $r4Id, $useDate1, $this->community);
        $o4b->save();
        $orders[] = $o4b;

        $oa4b = new Order_Action([
            'id_order' => $o4b->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa4b->save();
        $o4b->delivery_status = $oa4b->id_order_action;
        $o4b->save();
        $oas[] = $oa4b;

        $o2c = $this->defaultOrder($this->user, $r2Id, $useDate1, $this->community);
        $o2c->save();
        $orders[] = $o2c;

        $oa2c = new Order_Action([
            'id_order' => $o2c->id_order,
            'id_admin' => $this->driver3->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-pickedup',
            'note' => ''
        ]);
        $oa2c->save();
        $o2c->delivery_status = $oa2c->id_order_action;
        $o2c->save();
        $oas[] = $oa2c;

        $o3c = $this->defaultOrder($this->user, $r3Id, $useDate1, $this->community);
        $o3c->save();
        $orders[] = $o3c;

        $oa3c = new Order_Action([
            'id_order' => $o3c->id_order,
            'id_admin' => $this->driver3->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa3c->save();
        $o3c->delivery_status = $oa3c->id_order_action;
        $o3c->save();
        $oas[] = $oa3c;

        $o6 = $this->defaultOrder($this->user, $r2Id, $useDate2, $this->community);
        $o6->save();
        $orders[] = $o6;

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_SIMPLE, $o6);
        $ol->process();

        foreach ($orders as $order) {
            $order->delete();
        }
        foreach ($oas as $oa) {
            $oa->delete();
        }
//        foreach ($ol->drivers() as $driver) {
//            print "Driver seconds: ".$driver->id_admin." ".$driver->__seconds."\n";
//        }

        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin || $driver->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            } else {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);

            }
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
    }


    // 3 orders accepted/picked-up in the system inside the last n minutes by driver 1 for r1, r2(pickedup), r3
    // 3 orders accepted in the system inside the last n minutes by driver 2 for r2(pickedup), r3, r4
    // 2 orders accepted in the system inside the last n minutes by driver 3 for r2(pickedup), r3
    //  New order from r6
    //  Priority given to all
    public function testLogisticsGiveToFreeDriver11()
    {
        $seconds = 300;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 20;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');

        $this->assertLessThan(300, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
        $seconds = 300 - Crunchbutton_Order_Logistics::TIME_MAX_DELAY;
        $later = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $later->modify('- ' . $seconds . ' seconds');
        $laterDate = $later->format('Y-m-d H:i:s');

        $r1Id = $this->restaurant1->id_restaurant;
        $r2Id = $this->restaurant2->id_restaurant;
        $r3Id = $this->restaurant3->id_restaurant;
        $r4Id = $this->restaurant4->id_restaurant;
        $r5Id = $this->restaurant5->id_restaurant;
        $r6Id = $this->restaurant6->id_restaurant;
        $orders = [];
        $oas = [];

        $o1 = $this->defaultOrder($this->user, $r1Id, $useDate1, $this->community);
        $o1->save();
        $orders[] = $o1;

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $o1->delivery_status = $oa1->id_order_action;
        $o1->save();
        $oas[] = $oa1;

        $o2 = $this->defaultOrder($this->user, $r2Id, $useDate1, $this->community);
        $o2->save();
        $orders[] = $o2;

        $oa2 = new Order_Action([
            'id_order' => $o2->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-pickedup',
            'note' => ''
        ]);
        $oa2->save();
        $o2->delivery_status = $oa2->id_order_action;
        $o2->save();
        $oas[] = $oa2;

        $o3 = $this->defaultOrder($this->user, $r3Id, $useDate1, $this->community);
        $o3->save();
        $orders[] = $o3;

        $oa3 = new Order_Action([
            'id_order' => $o3->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa3->save();
        $o3->delivery_status = $oa3->id_order_action;
        $o3->save();
        $oas[] = $oa3;

        $o2b = $this->defaultOrder($this->user, $r2Id, $useDate1, $this->community);
        $o2b->save();
        $orders[] = $o2b;

        $oa2b = new Order_Action([
            'id_order' => $o2b->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-pickedup',
            'note' => ''
        ]);
        $oa2b->save();
        $o2b->delivery_status = $oa2b->id_order_action;
        $o2b->save();
        $oas[] = $oa2b;

        $o3b = $this->defaultOrder($this->user, $r3Id, $useDate1, $this->community);
        $o3b->save();
        $orders[] = $o3b;

        $oa3b = new Order_Action([
            'id_order' => $o3b->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa3b->save();
        $o3b->delivery_status = $oa3b->id_order_action;
        $o3b->save();
        $oas[] = $oa3b;

        $o4b = $this->defaultOrder($this->user, $r4Id, $useDate1, $this->community);
        $o4b->save();
        $orders[] = $o4b;

        $oa4b = new Order_Action([
            'id_order' => $o4b->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa4b->save();
        $o4b->delivery_status = $oa4b->id_order_action;
        $o4b->save();
        $oas[] = $oa4b;

        $o2c = $this->defaultOrder($this->user, $r2Id, $useDate1, $this->community);
        $o2c->save();
        $orders[] = $o2c;

        $oa2c = new Order_Action([
            'id_order' => $o2c->id_order,
            'id_admin' => $this->driver3->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-pickedup',
            'note' => ''
        ]);
        $oa2c->save();
        $o2c->delivery_status = $oa2c->id_order_action;
        $o2c->save();
        $oas[] = $oa2c;

        $o3c = $this->defaultOrder($this->user, $r3Id, $useDate1, $this->community);
        $o3c->save();
        $orders[] = $o3c;

        $oa3c = new Order_Action([
            'id_order' => $o3c->id_order,
            'id_admin' => $this->driver3->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa3c->save();
        $o3c->delivery_status = $oa3c->id_order_action;
        $o3c->save();
        $oas[] = $oa3c;

        $o6 = $this->defaultOrder($this->user, $r2Id, $useDate2, $this->community);
        $o6->save();
        $orders[] = $o6;

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_SIMPLE, $o6);
        $ol->process();

        foreach ($orders as $order) {
            $order->delete();
        }
        foreach ($oas as $oa) {
            $oa->delete();
        }
//        foreach ($ol->drivers() as $driver) {
//            print "Driver seconds: ".$driver->id_admin." ".$driver->__seconds."\n";
//        }

        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin || $driver->id_admin == $this->driver2->id_admin) {
                $this->assertEquals($driver->__seconds, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
                $this->assertEquals($driver->__priority, false);
            } else {
                $this->assertEquals($driver->__seconds, 0);
                $this->assertEquals($driver->__priority, true);

            }
        }
        $this->assertEquals($ol->numDriversWithPriority, 1);
    }

    // 4 orders accepted/picked-up in the system inside the last n minutes by driver 1 for r1, r2(pickedup), r3, r4
    // 4 orders accepted in the system inside the last n minutes by driver 2 for r2(pickedup), r3, r4, r5
    // 4 orders accepted in the system inside the last n minutes by driver 3 for r2(pickedup), r3, r4, r5
    //  New order from r2
    //  Priority given to all
    public function testLogisticsAllBusy()
    {
        $seconds = 300;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 20;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');

        $this->assertLessThan(300, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
        $seconds = 300 - Crunchbutton_Order_Logistics::TIME_MAX_DELAY;
        $later = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $later->modify('- ' . $seconds . ' seconds');
        $laterDate = $later->format('Y-m-d H:i:s');

        $r1Id = $this->restaurant1->id_restaurant;
        $r2Id = $this->restaurant2->id_restaurant;
        $r3Id = $this->restaurant3->id_restaurant;
        $r4Id = $this->restaurant4->id_restaurant;
        $r5Id = $this->restaurant5->id_restaurant;
        $r6Id = $this->restaurant6->id_restaurant;
        $orders = [];
        $oas = [];

        $o1 = $this->defaultOrder($this->user, $r1Id, $useDate1, $this->community);
        $o1->save();
        $orders[] = $o1;

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $o1->delivery_status = $oa1->id_order_action;
        $o1->save();
        $oas[] = $oa1;


        $o2 = $this->defaultOrder($this->user, $r2Id, $useDate1, $this->community);
        $o2->save();
        $orders[] = $o2;

        $oa2 = new Order_Action([
            'id_order' => $o2->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-pickedup',
            'note' => ''
        ]);
        $oa2->save();
        $o2->delivery_status = $oa3->id_order_action;
        $o2->save();
        $oas[] = $oa2;

        $o3 = $this->defaultOrder($this->user, $r3Id, $useDate1, $this->community);
        $o3->save();
        $orders[] = $o3;

        $oa3 = new Order_Action([
            'id_order' => $o3->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa3->save();
        $o3->delivery_status = $oa3->id_order_action;
        $o3->save();
        $oas[] = $oa3;

        $o4 = $this->defaultOrder($this->user, $r4Id, $useDate1, $this->community);
        $o4->save();
        $orders[] = $o4;

        $oa4 = new Order_Action([
            'id_order' => $o4->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-pickedup',
            'note' => ''
        ]);
        $oa4->save();
        $o4->delivery_status = $oa4->id_order_action;
        $o4->save();
        $oas[] = $oa4;

        $o2b = $this->defaultOrder($this->user, $r2Id, $useDate1, $this->community);
        $o2b->save();
        $orders[] = $o2b;

        $oa2b = new Order_Action([
            'id_order' => $o2b->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-pickedup',
            'note' => ''
        ]);
        $oa2b->save();
        $o2b->delivery_status = $oa2b->id_order_action;
        $o2b->save();
        $oas[] = $oa2b;

        $o3b = $this->defaultOrder($this->user, $r3Id, $useDate1, $this->community);
        $o3b->save();
        $orders[] = $o3b;

        $oa3b = new Order_Action([
            'id_order' => $o3b->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa3b->save();
        $o3b->delivery_status = $oa3b->id_order_action;
        $o3b->save();
        $oas[] = $oa3b;

        $o4b = $this->defaultOrder($this->user, $r4Id, $useDate1, $this->community);
        $o4b->save();
        $orders[] = $o4b;

        $oa4b = new Order_Action([
            'id_order' => $o4b->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa4b->save();
        $o4b->delivery_status = $oa4b->id_order_action;
        $o4b->save();
        $oas[] = $oa4b;

        $o5b = $this->defaultOrder($this->user, $r5Id, $useDate1, $this->community);
        $o5b->save();
        $orders[] = $o5b;

        $oa5b = new Order_Action([
            'id_order' => $o5b->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa5b->save();
        $o5b->delivery_status = $oa5b->id_order_action;
        $o5b->save();
        $oas[] = $oa5b;

        $o2c = $this->defaultOrder($this->user, $r2Id, $useDate1, $this->community);
        $o2c->save();
        $orders[] = $o2c;

        $oa2c = new Order_Action([
            'id_order' => $o2c->id_order,
            'id_admin' => $this->driver3->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-pickedup',
            'note' => ''
        ]);
        $oa2c->save();
        $o2c->delivery_status = $oa2c->id_order_action;
        $o2c->save();
        $oas[] = $oa2c;

        $o3c = $this->defaultOrder($this->user, $r3Id, $useDate1, $this->community);
        $o3c->save();
        $orders[] = $o3c;

        $oa3c = new Order_Action([
            'id_order' => $o3c->id_order,
            'id_admin' => $this->driver3->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa3c->save();
        $o3c->delivery_status = $oa3c->id_order_action;
        $o3c->save();
        $oas[] = $oa3c;

        $o4c = $this->defaultOrder($this->user, $r4Id, $useDate1, $this->community);
        $o4c->save();
        $orders[] = $o4c;

        $oa4c = new Order_Action([
            'id_order' => $o4c->id_order,
            'id_admin' => $this->driver3->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa4c->save();
        $o4c->delivery_status = $oa4c->id_order_action;
        $o4c->save();
        $oas[] = $oa4c;

        $o5c = $this->defaultOrder($this->user, $r5Id, $useDate1, $this->community);
        $o5c->save();
        $orders[] = $o5c;

        $oa5c = new Order_Action([
            'id_order' => $o5c->id_order,
            'id_admin' => $this->driver3->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa5c->save();
        $o5c->delivery_status = $oa5c->id_order_action;
        $o5c->save();
        $oas[] = $oa5c;

        $o2d = $this->defaultOrder($this->user, $r2Id, $useDate2, $this->community);
        $o2d->save();
        $orders[] = $o2d;

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_SIMPLE, $o2d);
        $ol->process();

        foreach ($orders as $order) {
            $order->delete();
        }
        foreach ($oas as $oa) {
            $oa->delete();
        }
//        foreach ($ol->drivers() as $driver) {
//            print "Driver seconds: ".$driver->id_admin." ".$driver->__seconds."\n";
//        }

        foreach ($ol->drivers() as $driver) {
            $this->assertEquals($driver->__seconds, 0);
            $this->assertEquals($driver->__priority, true);
        }
        $this->assertEquals($ol->numDriversWithPriority, 3);
    }

    // 4 orders accepted/picked-up in the system inside the last n minutes by driver 1 for r1, r2(pickedup), r3, r4
    // 4 orders accepted in the system inside the last n minutes by driver 2 for r2(pickedup), r3, r4, r5
    // 4 orders accepted in the system inside the last n minutes by driver 3 for r2(pickedup), r3, r4, r5
    //  New order from r6
    //  Priority given to all
    public function testLogisticsAllBusy2()
    {
        $seconds = 300;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate1 = $now->format('Y-m-d H:i:s');

        $seconds = 20;
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $now->modify('- ' . $seconds . ' seconds');
        $useDate2 = $now->format('Y-m-d H:i:s');

        $this->assertLessThan(300, Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
        $seconds = 300 - Crunchbutton_Order_Logistics::TIME_MAX_DELAY;
        $later = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $later->modify('- ' . $seconds . ' seconds');
        $laterDate = $later->format('Y-m-d H:i:s');

        $r1Id = $this->restaurant1->id_restaurant;
        $r2Id = $this->restaurant2->id_restaurant;
        $r3Id = $this->restaurant3->id_restaurant;
        $r4Id = $this->restaurant4->id_restaurant;
        $r5Id = $this->restaurant5->id_restaurant;
        $r6Id = $this->restaurant6->id_restaurant;
        $orders = [];
        $oas = [];

        $o1 = $this->defaultOrder($this->user, $r1Id, $useDate1, $this->community);
        $o1->save();
        $orders[] = $o1;

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();
        $o1->delivery_status = $oa1->id_order_action;
        $o1->save();
        $oas[] = $oa1;


        $o2 = $this->defaultOrder($this->user, $r2Id, $useDate1, $this->community);
        $o2->save();
        $orders[] = $o2;

        $oa2 = new Order_Action([
            'id_order' => $o2->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-pickedup',
            'note' => ''
        ]);
        $oa2->save();
        $o2->delivery_status = $oa2->id_order_action;
        $o2->save();
        $oas[] = $oa2;

        $o3 = $this->defaultOrder($this->user, $r3Id, $useDate1, $this->community);
        $o3->save();
        $orders[] = $o3;

        $oa3 = new Order_Action([
            'id_order' => $o3->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa3->save();
        $o3->delivery_status = $oa3->id_order_action;
        $o3->save();
        $oas[] = $oa3;

        $o4 = $this->defaultOrder($this->user, $r4Id, $useDate1, $this->community);
        $o4->save();
        $orders[] = $o4;

        $oa4 = new Order_Action([
            'id_order' => $o4->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-pickedup',
            'note' => ''
        ]);
        $oa4->save();
        $o4->delivery_status = $oa4->id_order_action;
        $o4->save();
        $oas[] = $oa4;

        $o2b = $this->defaultOrder($this->user, $r2Id, $useDate1, $this->community);
        $o2b->save();
        $orders[] = $o2b;

        $oa2b = new Order_Action([
            'id_order' => $o2b->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-pickedup',
            'note' => ''
        ]);
        $oa2b->save();
        $o2b->delivery_status = $oa2b->id_order_action;
        $o2b->save();
        $oas[] = $oa2b;

        $o3b = $this->defaultOrder($this->user, $r3Id, $useDate1, $this->community);
        $o3b->save();
        $orders[] = $o3b;

        $oa3b = new Order_Action([
            'id_order' => $o3b->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa3b->save();
        $o3b->delivery_status = $oa3b->id_order_action;
        $o3b->save();
        $oas[] = $oa3b;

        $o4b = $this->defaultOrder($this->user, $r4Id, $useDate1, $this->community);
        $o4b->save();
        $orders[] = $o4b;

        $oa4b = new Order_Action([
            'id_order' => $o4b->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa4b->save();
        $o4b->delivery_status = $oa4b->id_order_action;
        $o4b->save();
        $oas[] = $oa4b;

        $o5b = $this->defaultOrder($this->user, $r5Id, $useDate1, $this->community);
        $o5b->save();
        $orders[] = $o5b;

        $oa5b = new Order_Action([
            'id_order' => $o5b->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa5b->save();
        $o5b->delivery_status = $oa5b->id_order_action;
        $o5b->save();
        $oas[] = $oa5b;

        $o2c = $this->defaultOrder($this->user, $r2Id, $useDate1, $this->community);
        $o2c->save();
        $orders[] = $o2c;

        $oa2c = new Order_Action([
            'id_order' => $o2c->id_order,
            'id_admin' => $this->driver3->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-pickedup',
            'note' => ''
        ]);
        $oa2c->save();
        $o2c->delivery_status = $oa2c->id_order_action;
        $o2c->save();
        $oas[] = $oa2c;

        $o3c = $this->defaultOrder($this->user, $r3Id, $useDate1, $this->community);
        $o3c->save();
        $orders[] = $o3c;

        $oa3c = new Order_Action([
            'id_order' => $o3c->id_order,
            'id_admin' => $this->driver3->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa3c->save();
        $o3c->delivery_status = $oa3c->id_order_action;
        $o3c->save();
        $oas[] = $oa3c;

        $o4c = $this->defaultOrder($this->user, $r4Id, $useDate1, $this->community);
        $o4c->save();
        $orders[] = $o4c;

        $oa4c = new Order_Action([
            'id_order' => $o4c->id_order,
            'id_admin' => $this->driver3->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa4c->save();
        $o4c->delivery_status = $oa4c->id_order_action;
        $o4c->save();
        $oas[] = $oa4c;

        $o5c = $this->defaultOrder($this->user, $r5Id, $useDate1, $this->community);
        $o5c->save();
        $orders[] = $o5c;

        $oa5c = new Order_Action([
            'id_order' => $o5c->id_order,
            'id_admin' => $this->driver3->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa5c->save();
        $o5c->delivery_status = $oa5c->id_order_action;
        $o5c->save();
        $oas[] = $oa5c;

        $o6 = $this->defaultOrder($this->user, $r2Id, $useDate2, $this->community);
        $o6->save();
        $orders[] = $o6;

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_SIMPLE, $o6);
        $ol->process();

        foreach ($orders as $order) {
            $order->delete();
        }
        foreach ($oas as $oa) {
            $oa->delete();
        }
//        foreach ($ol->drivers() as $driver) {
//            print "Driver seconds: ".$driver->id_admin." ".$driver->__seconds."\n";
//        }

        foreach ($ol->drivers() as $driver) {
            $this->assertEquals($driver->__seconds, 0);
            $this->assertEquals($driver->__priority, true);
        }
        $this->assertEquals($ol->numDriversWithPriority, 3);
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
            'priority_algo_version' => Crunchbutton_Order_Logistics::LOGISTICS_SIMPLE_ALGO_VERSION,
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

}