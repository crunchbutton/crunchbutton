<?php

class PrioritySimpleLogisticsTest extends PHPUnit_Framework_TestCase
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
            'timezone' => 'America/Los_Angeles',
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
            'timezone' => 'America/Los_Angeles',
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
            'timezone' => 'America/Los_Angeles',
            'open_for_business' => true,
            'delivery_service' => true
        ]);
        $r4->save();
        $restaurants[] = $r4;


        $c = new Community([
            'name' => $name,
            'active' => 1,
            'timezone' => 'America/Los_Angeles',
            'driver-group' => 'drivers-testlogistics',
            'range' => 2,
            'private' => 1,
            'delivery_logistics' => true
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
            'active' => 1
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
            'active' => 1
        ]);
        $a2->save();
        $drivers[] = $a2;

        $an2 = new Admin_Notification([
            'id_admin' => $a2->id_admin,
            'type' => 'sms',
            'value' => '_PHONE_',
            'active' => true
        ]);
        $an2->save();

        $a3 = new Admin([
            'name' => $name . ' - THREE',
            'login' => null,
            'active' => 1
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
            'phone' => '_PHONE_',
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

        Crunchbutton_Order_Priority::q('select * from order_priority where id_restaurant = ?', [$this->restaurant1->id_restaurant])->delete();
        Crunchbutton_Order_Priority::q('select * from order_priority where id_restaurant = ?', [$this->restaurant2->id_restaurant])->delete();
        Crunchbutton_Order_Priority::q('select * from order_priority where id_restaurant = ?', [$this->restaurant3->id_restaurant])->delete();
        Crunchbutton_Order_Priority::q('select * from order_priority where id_restaurant = ?', [$this->restaurant4->id_restaurant])->delete();
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
        $this->assertEquals($d->count(), 4);
    }

    // This test isn't for the logistics code per se
    public function testAllPlacesHeDeliveryFor()
    {
        $d = $this->driver1->allPlacesHeDeliveryFor();
        $this->assertEquals(count($d), 4);
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
        foreach ($ol->drivers() as $driver) {
            $this->assertEquals($driver->__seconds, 0);
            $this->assertEquals($driver->__priority, false);
        }
        $this->assertEquals($ol->numDriversWithPriority, 0);
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

    // One other new order in the system within the last n minutes, given to driver 1, but refunded.
    //  New order from same restaurant.
    //  Should assign to neither driver.
    public function testLogisticsTwoOrdersSameRestaurantWithRefund()
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

        $pR1D1->delete();
        $pR1D2->delete();
        $pR1D3->delete();
        $pR1D4->delete();
        $o1->delete();
        $o2->delete();
        foreach ($ol->drivers() as $driver) {
//            print "Driver seconds: ".$driver->id_admin." ".$driver->__seconds."\n";
            $this->assertEquals($driver->__seconds, 0);
            $this->assertEquals($driver->__priority, false);
        }
        $this->assertEquals($ol->numDriversWithPriority, 0);
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

        $pR1D1->delete();
        $pR1D2->delete();
        $pR1D3->delete();
        $pR1D4->delete();
        $o1->delete();
        $o2->delete();
        foreach ($ol->drivers() as $driver) {
            $this->assertEquals($driver->__seconds, 0);
            $this->assertEquals($driver->__priority, false);
        }
        $this->assertEquals($ol->numDriversWithPriority, 0);
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

        $pR1D1->delete();
        $pR1D2->delete();
        $pR1D3->delete();
        $pR1D4->delete();
        $o1->delete();
        $o2->delete();
        foreach ($ol->drivers() as $driver) {
            $this->assertEquals($driver->__seconds, 0);
            $this->assertEquals($driver->__priority, false);
        }
        $this->assertEquals($ol->numDriversWithPriority, 0);
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
        foreach ($ops as $op) {
            $op->delete();
        }
        $o2->delete();
        foreach ($ol->drivers() as $driver) {
            $this->assertEquals($driver->__seconds, 0);
            $this->assertEquals($driver->__priority, false);
        }
        $this->assertEquals($ol->numDriversWithPriority, 0);
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

        $pR1D1->delete();
        $pR1D2->delete();
        $pR1D3->delete();
        $pR1D4->delete();
        $oa1->delete();
        $o1->delete();
        $o2->delete();
        foreach ($ol->drivers() as $driver) {
            $this->assertEquals($driver->__seconds, 0);
            $this->assertEquals($driver->__priority, false);
        }
        $this->assertEquals($ol->numDriversWithPriority, 0);
    }

    // One picked-up order in the system within the last n minutes, by driver 1
    //  New order from same restaurant
    //  Should assign to no one
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

        $pR1D1->delete();
        $pR1D2->delete();
        $pR1D3->delete();
        $pR1D4->delete();
        $oa1->delete();
        $o1->delete();
        $o2->delete();
        foreach ($ol->drivers() as $driver) {
            $this->assertEquals($driver->__seconds, 0);
            $this->assertEquals($driver->__priority, false);
        }
        $this->assertEquals($ol->numDriversWithPriority, 0);
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

    // One order accepted in the system within the last n minutes by driver 2.  However, order is refunded.
    //  New order from same restaurant
    //  Should assign priority to no driver.
    // Also give a high priority to driver 1 for prev order, just to make sure the code doesn't screw up there
    public function testLogisticsAcceptedOrderWithRefund()
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
        $o1->save();

        $oa1 = new Order_Action([
            'id_order' => $o1->id_order,
            'id_admin' => $this->driver2->id_admin,
            'timestamp' => $useDate1,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa1->save();

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

        $pR1D1->delete();
        $pR1D2->delete();
        $pR1D3->delete();
        $pR1D4->delete();
        $oa1->delete();
        $o1->delete();
        $o2->delete();
        foreach ($ol->drivers() as $driver) {
            $this->assertEquals($driver->__seconds, 0);
            $this->assertEquals($driver->__priority, false);
        }
        $this->assertEquals($ol->numDriversWithPriority, 0);
    }


    // One order accepted in the system outside the last n minutes by driver 2
    //  New order from same restaurant
    //  No priority given to any driver
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

        $pR1D1->delete();
        $pR1D2->delete();
        $pR1D3->delete();
        $pR1D4->delete();
        $oa1->delete();
        $o1->delete();
        $o2->delete();
        foreach ($ol->drivers() as $driver) {
//            print "Driver seconds: ".$driver->id_admin." ".$driver->__seconds."\n";
            $this->assertEquals($driver->__seconds, 0);
            $this->assertEquals($driver->__priority, false);
        }
        $this->assertEquals($ol->numDriversWithPriority, 0);
    }

    // One order accepted in the system outside the last n minutes by driver 2
    //   One order accepted in the system inside of the last n minutes by driver 2
    //  New order from same restaurant
    //  No priority given to any driver
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
            $this->assertEquals($driver->__seconds, 0);
            $this->assertEquals($driver->__priority, false);
        }
        $this->assertEquals($ol->numDriversWithPriority, 0);
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
    //  No priority given to any driver
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
        foreach ($orders as $order) {
            $order->delete();
        }
        foreach ($oas as $oa) {
            $oa->delete();
        }

        $o2->delete();
        foreach ($ol->drivers() as $driver) {
//            print "Driver seconds: ".$driver->id_admin." ".$driver->__seconds."\n";
            $this->assertEquals($driver->__seconds, 0);
            $this->assertEquals($driver->__priority, false);
        }
        $this->assertEquals($ol->numDriversWithPriority, 0);
    }

    // N -1 orders accepted in the system inside the last n minutes by driver 2
    // One new order given to driver 2 but not accepted yet
    //  New order from same restaurant
    //  No priority given to any driver
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
        foreach ($orders as $order) {
            $order->delete();
        }
        foreach ($oas as $oa) {
            $oa->delete();
        }

        $o2->delete();
        $o3->delete();
        foreach ($ol->drivers() as $driver) {
            $this->assertEquals($driver->__seconds, 0);
            $this->assertEquals($driver->__priority, false);
        }
        $this->assertEquals($ol->numDriversWithPriority, 0);
    }

    // N -1 orders accepted in the system inside the last n minutes by driver 2
    // One new order given to driver 2 but not accepted yet, but priority is expired
    //  New order from same restaurant
    //  No priority given to any driver
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
        $oas[] = $oa3;

        $o4 = $this->defaultOrder($this->user, $r1Id, $useDate2, $this->community);
        $o4->save();
        $orders[] = $o4;

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_SIMPLE, $o4);
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
        $oas[] = $oa4;

        $o5 = $this->defaultOrder($this->user, $r1Id, $useDate2, $this->community);
        $o5->save();
        $orders[] = $o5;

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_SIMPLE, $o5);
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
}