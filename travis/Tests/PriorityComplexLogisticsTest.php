<?php

class PriorityComplexLogisticsTest extends PHPUnit_Framework_TestCase
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
            'delivery_logistics' => 2
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
            'value' => '_PHONE_',
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
            'value' => '_PHONE_',
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
        $cs->delete();
        $community->delete();
        $community2->delete();
		
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
    public function testOLPTZConversionLosAngeles()
    {

        $useDate = '2015-07-01 05:00:00';
        $useDT = new DateTime($useDate, new DateTimeZone(c::config()->timezone)); // Should be PST
        $dow = $useDT->format('w');

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = date("H:i:s", strtotime('2015-01-01 06:00:00'));
        $end2 = date("H:i:s", strtotime('2015-01-01 12:00:00'));

        $olp1 = $this->defaultOLP($this->restaurant1, $start, $end, 5, $dow);
        $olp2 = $this->defaultOLP($this->restaurant1, $end, $end2, 10, $dow);
        $olp1->save();
        $olp2->save();
        $newTZ = $this->restaurant1->community()->timezone;
        $useDT->setTimezone(new DateTimeZone($newTZ));
        $parking = $this->restaurant1->parking($useDT->format('H:i:s'), $dow);
        $olp1->delete();
        $olp2->delete();
        $this->assertTrue($parking->parking_duration == 5);
    }

    public function testOLPTZConversionNewYork()
    {

        $useDate = '2015-07-01 05:00:00';
        $useDT = new DateTime($useDate, new DateTimeZone(c::config()->timezone)); // Should be PST
//        var_dump($useDT);
        $dow = $useDT->format('w');

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = date("H:i:s", strtotime('2015-01-01 06:00:00'));
        $end2 = date("H:i:s", strtotime('2015-01-01 12:00:00'));

        $olp1 = $this->defaultOLP($this->restaurant5, $start, $end, 15, $dow);
        $olp2 = $this->defaultOLP($this->restaurant5, $end, $end2, 20, $dow);
        $olp1->save();
        $olp2->save();
        $newTZ = $this->restaurant5->community()->timezone;
        $useDT->setTimezone(new DateTimeZone($newTZ));
        $parking = $this->restaurant5->parking($useDT->format('H:i:s'), $dow);
//        var_dump($useDT);
//        print $useDT->format('H:i:s')."\n";
        $olp1->delete();
        $olp2->delete();
        $this->assertTrue($parking->parking_duration == 20);
    }

    public function testOLOTTZConversionLosAngeles()
    {

        $useDate = '2015-07-01 05:00:00';
        $useDT = new DateTime($useDate, new DateTimeZone(c::config()->timezone)); // Should be PST
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
        $this->assertTrue($ot->order_time == 5);
        $this->assertTrue($ot->scale_factor == 1);
    }

    public function testOLOTTZConversionNewYork()
    {

        $useDate = '2015-07-01 05:00:00';
        $useDT = new DateTime($useDate, new DateTimeZone(c::config()->timezone)); // Should be PST
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
        $this->assertTrue($ot->order_time == 20);
        $this->assertTrue($ot->scale_factor == 1);
    }

    public function testOLCSTZConversionLosAngeles()
    {

        $useDate = '2015-07-01 05:00:00';
        $useDT = new DateTime($useDate, new DateTimeZone(c::config()->timezone)); // Should be PST
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
        $this->assertTrue($cs->mph == 5);
    }

    public function testOLCSTZConversionNewYork()
    {

        $useDate = '2015-07-01 05:00:00';
        $useDT = new DateTime($useDate, new DateTimeZone(c::config()->timezone)); // Should be PST
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
        $this->assertTrue($cs->mph == 20);
    }

    public function testGoogleGeocode()
    {
        $address = "311 Highland Lake Circle Decatur, GA, 30033";
        $location = Crunchbutton_GoogleGeocode::geocode($address);
        $lat = round($location->lat, 2);
        $lon = round($location->lon, 2);
        $this->assertTrue($lat==33.80);
        $this->assertTrue($lon==-84.31);
    }


    public function testAdminLocationMissing()
    {
        $d1 = $this->driver1;
        $loc = $d1->location();
        $this->assertNull($loc);

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
        $this->assertTrue($geo->lat==$lat);
        $this->assertTrue($geo->lon==$lon);
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
        $this->assertTrue($geo->lat==$lat);
        $this->assertTrue($geo->lon==$lon);
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
        $this->assertTrue($geo->lat==$lat);
        $this->assertTrue($geo->lon==$lon);
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
        $this->assertTrue($geo->lat==$lat);
        $this->assertTrue($geo->lon==$lon);
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
        $this->assertTrue($geo->lat==$lat2);
        $this->assertTrue($geo->lon==$lon2);
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

        $this->assertTrue(round($geo->lat, 3)==$lat);
        $this->assertTrue(round($geo->lon, 3)==$lon);
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

        $this->assertTrue($geo->lat==$lat3);
        $this->assertTrue($geo->lon==$lon3);
    }

    public function testAdminScore()
    {
        $useScore = 2.0;
        $s = $this->defaultScore($this->driver1, $useScore);
        $s->save();
        $sc = $this->driver1->score();
        $s->delete();
        $this->assertTrue($sc == $useScore);
    }

    public function testAdminDefaultScore()
    {
        $useScore = 55.0;
        $s = $this->defaultScore($this->driver2, $useScore);
        $s->save();
        $sc = $this->driver1->score();
        $s->delete();
        $this->assertTrue($sc == Cockpit_Admin_Score::DEFAULT_SCORE);
    }

    public function testRestaurantClusterExist()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $useDate1 = $now->format('Y-m-d H:i:s');

        $fakeClusterId = 999999;
        $olc = $this->defaultOLC($this->restaurant1, 0, '00:00:00', '01:00:00', $fakeClusterId);
        $olc->save();
        $cluster = $this->restaurant1->cluster('00:03:00', 0);
        $olc->delete();
        $this->assertTrue($cluster->id_restaurant_cluster==$fakeClusterId);
    }

    public function testRestaurantClusterWrongTime()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $useDate1 = $now->format('Y-m-d H:i:s');

        $fakeClusterId = 999999;
        $olc = $this->defaultOLC($this->restaurant1, 0, '00:00:00', '01:00:00', $fakeClusterId);
        $olc->save();
        $cluster = $this->restaurant1->cluster('02:00:00', 0);
        $olc->delete();
        $cl = Crunchbutton_Order_Logistics_Cluster::q('select * from order_logistics_cluster where id_restaurant= ?',
            [$this->restaurant1->id_restaurant]);
        $count = $cl->count();
        $cl->delete();
        $this->assertTrue($cluster->id_restaurant_cluster==$this->restaurant1->id_restaurant);
        $this->assertTrue($count==1);
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
        $this->assertTrue($count==3);
    }


    public function testOptimizerAPI()
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
        $i->clusters = [[], [], [], [], [8, 6], [], [8, 4], [], [4,6]];
        $d = $i->exports();
        $r = Crunchbutton_Optimizer::optimize($d);
        $this->assertNotNull($r);
        $result = new Crunchbutton_Optimizer_Result($r);
        $this->assertTrue($result->resultType==Crunchbutton_Optimizer_Result::RTYPE_OK);
        $this->assertNotNull($result->score);
        $this->assertTrue($result->score==17.8);
        $this->assertTrue($result->numBadTimes==0);
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

        $this->assertTrue(50 < Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
        $seconds = Crunchbutton_Order_Logistics::TIME_MAX_DELAY - 50;
        $later = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $later->modify('+ ' . $seconds . ' seconds');
        $laterDate = $later->format('Y-m-d H:i:s');

        $o1 = $this->defaultOrder($this->user, $this->restaurant1->id_restaurant, $useDate1, $this->community);
        $o1->save();
        $drivers = $o1->getDriversToNotify();
        $this->assertTrue($drivers->count()==3);
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

        $this->assertTrue(50 < Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
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
        $this->assertTrue($drivers->count()==2);
    }


    public function testRestaurantsWithGeo()
    {
        $rs = Restaurant::getDeliveryRestaurantsWithGeoByIdCommunity($this->community->id_community);
        $this->assertTrue($rs->count()==4);
    }


    // Missing info - no one should get priority
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

        $driverLoc1 = $this->createAdminLocation($this->driver1->id_admin, 34.0302, -118.273, $useDate2);
        $driverLoc1->save();
        $driverLoc2 = $this->createAdminLocation($this->driver2->id_admin, 34.0302, -118.273, $useDate2);
        $driverLoc2->save();

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o1);

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o1->id_order]);

        $this->driver3->active = true;
        $this->driver3->save();

        $o1->delete();
        $driverLoc1->delete();
        $driverLoc2->delete();
        foreach ($ol->drivers() as $driver) {
            $this->assertTrue($driver->__seconds == 0);
        }
        $this->assertTrue($ops->count() == 2);
        foreach ($ops as $op) {
            $this->assertTrue($op->priority_given == Crunchbutton_Order_Priority::PRIORITY_NO_ONE);
        }
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

        $driverLoc1 = $this->createAdminLocation($this->driver1->id_admin, 34.0302, -118.273, $useDate2);
        $driverLoc1->save();
        $driverLoc2 = $this->createAdminLocation($this->driver2->id_admin, 34.0302, -118.273, $useDate2);
        $driverLoc2->save();

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, $dow);
        $olp1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 15, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o1);

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o1->id_order]);

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $driverLoc1->delete();
        $driverLoc2->delete();
        $olp1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            $this->assertTrue($driver->__seconds == 0);
        }
        $this->assertTrue($ops->count() == 2);
        foreach ($ops as $op) {
            $this->assertTrue($op->priority_given == Crunchbutton_Order_Priority::PRIORITY_HIGH);
        }
    }

    // Missing fake community fake customers - no priority
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

        $driverLoc1 = $this->createAdminLocation($this->driver1->id_admin, 34.0302, -118.273, $useDate2);
        $driverLoc1->save();
        $driverLoc2 = $this->createAdminLocation($this->driver2->id_admin, 34.0302, -118.273, $useDate2);
        $driverLoc2->save();

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
        $oa2 = new Order_Action([
            'id_order' => $o2->id_order,
            'id_admin' => $this->driver1->id_admin,
            'timestamp' => $useDate2,
            'type' => 'delivery-accepted',
            'note' => ''
        ]);
        $oa2->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, $dow);
        $olp1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 15, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o1);

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o1->id_order]);

        $this->driver3->active = true;
        $this->driver3->save();
        $oa2->delete();
        $o1->delete();
        $o2->delete();
        $driverLoc1->delete();
        $driverLoc2->delete();
        $olp1->delete();
        $olot1->delete();
        $olcs1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            $this->assertTrue($driver->__seconds == 0);
        }
        $this->assertTrue($ops->count() == 2);
        foreach ($ops as $op) {
            $this->assertTrue($op->priority_given == Crunchbutton_Order_Priority::PRIORITY_NO_ONE);
        }
    }


    // Two drivers - no orders - same location
    //  First order comes in - both get priority
    public function testLogisticsFirstorderSameLocation()
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

        $driverLoc1 = $this->createAdminLocation($this->driver1->id_admin, 34.0302, -118.273, $useDate2);
        $driverLoc1->save();
        $driverLoc2 = $this->createAdminLocation($this->driver2->id_admin, 34.0302, -118.273, $useDate2);
        $driverLoc2->save();

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, $dow);
        $olp1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 15, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $fc_lat = 34.0312;
        $fc_lon = -118.286;
        $fc1 = $this->createFakecustomer($this->community, $fc_lat, $fc_lon);
        $fc1->save();

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o1);

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o1->id_order]);

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $driverLoc1->delete();
        $driverLoc2->delete();
        $olp1->delete();
        $olot1->delete();
        $olcs1->delete();
        $fc1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            $this->assertTrue($driver->__seconds == 0);
        }
        $this->assertTrue($ops->count() == 2);
        foreach ($ops as $op) {
            $this->assertTrue($op->priority_given == Crunchbutton_Order_Priority::PRIORITY_HIGH);
        }
    }

    // Two drivers - no orders - different locations, but still close
    //  First order comes in - both get high priority because of wait time
    public function testLogisticsFirstorderDifferentLocationWithLongWait()
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

        $driverLoc1 = $this->createAdminLocation($this->driver1->id_admin, 34.0302, -118.273, $useDate2);
        $driverLoc1->save();
        $driverLoc2 = $this->createAdminLocation($this->driver2->id_admin, 34.018, -118.281, $useDate2);
        $driverLoc2->save();

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, $dow);
        $olp1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 15, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $fc_lat = 34.0312;
        $fc_lon = -118.286;
        $fc1 = $this->createFakecustomer($this->community, $fc_lat, $fc_lon);
        $fc1->save();

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o1);

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o1->id_order]);

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $driverLoc1->delete();
        $driverLoc2->delete();
        $olp1->delete();
        $olot1->delete();
        $olcs1->delete();
        $fc1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            $this->assertTrue($driver->__seconds == 0);
        }
        $this->assertTrue($ops->count() == 2);
        foreach ($ops as $op) {
            $this->assertTrue($op->priority_given == Crunchbutton_Order_Priority::PRIORITY_HIGH);
        }
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

        $driverLoc1 = $this->createAdminLocation($this->driver1->id_admin, 34.0302, -118.273, $useDate2);
        $driverLoc1->save();
        $driverLoc2 = $this->createAdminLocation($this->driver2->id_admin, 34.018, -118.281, $useDate2);
        $driverLoc2->save();

        // Chipotle
        $o1 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $useDate1, $this->community);
        $o1->lat = 34.0284;
        $o1->lon = -118.287;
        $o1->save();

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, $dow);
        $olp1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $fc_lat = 34.0312;
        $fc_lon = -118.286;
        $fc1 = $this->createFakecustomer($this->community, $fc_lat, $fc_lon);
        $fc1->save();

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o1);

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o1->id_order]);

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $driverLoc1->delete();
        $driverLoc2->delete();
        $olp1->delete();
        $olot1->delete();
        $olcs1->delete();
        $fc1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver2->id_admin) {
                $this->assertTrue($driver->__seconds == 0);
            }
            else{
                $this->assertTrue($driver->__seconds == Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
            }
        }
        $this->assertTrue($ops->count() == 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver2->id_admin) {
                $this->assertTrue($op->priority_given == Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else{
                $this->assertTrue($op->priority_given == Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
        }
    }


    // Two drivers - driver 1 has accepted order from Chipotle (with no waiting time).
    //  Both drivers are at the same location
    //  New Chipotle order comes in - driver 1 should get priority due to fake order/bundling.
    public function testLogisticsSecondOrderSameLocationNoWait()
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

        $driverLoc1 = $this->createAdminLocation($this->driver1->id_admin, 34.0302, -118.273, $useDate2);
        $driverLoc1->save();
        $driverLoc2 = $this->createAdminLocation($this->driver2->id_admin, 34.0302, -118.273, $useDate2);
        $driverLoc2->save();

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
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, $dow);
        $olp1->save();

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

        $fc_lat = 34.0312;
        $fc_lon = -118.286;
        $fc1 = $this->createFakecustomer($this->community, $fc_lat, $fc_lon);
        $fc1->save();

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);

        $ops = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $this->driver3->active = true;
        $this->driver3->save();
        $o1->delete();
        $o2->delete();
        $oa1->delete();
        $driverLoc1->delete();
        $driverLoc2->delete();
        $olp1->delete();
        $olot1->delete();
        $olcs1->delete();
        $fc1->delete();
        $olc->delete();
        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertTrue($driver->__seconds == 0);
            }
            else{
                $this->assertTrue($driver->__seconds == Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
            }
        }
        $this->assertTrue($ops->count() == 2);

        foreach ($ops as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertTrue($op->priority_given == Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else{
                $this->assertTrue($op->priority_given == Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
        }
    }

    // Two drivers - driver 1 has an unaccepted priority order from Chipotle (with no waiting time).
    //  Both drivers are at the same location
    //  New Chipotle order comes in - driver 1 should get priority due to fake order/bundling.
    public function testLogisticsSecondOrderSameLocationNoWait2()
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

        $this->assertTrue(50 < Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
        $seconds = Crunchbutton_Order_Logistics::TIME_MAX_DELAY - 50;

        $laterM50PlusMax = clone $now;
        $laterM50PlusMax->modify('+ ' . $seconds . ' seconds');
        $laterM50PlusMaxString = $laterM50PlusMax->format('Y-m-d H:i:s');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLoc1 = $this->createAdminLocation($this->driver1->id_admin, 34.0302, -118.273, $earlier120String);
        $driverLoc1->save();
        $driverLoc2 = $this->createAdminLocation($this->driver2->id_admin, 34.0302, -118.273, $earlier120String);
        $driverLoc2->save();

        $chipotle_lat = 34.0284;
        $chipotle_lon = -118.287;

        // Chipotle
        $og = $this->createOrderGroupAndSave($this->user, $this->restaurant3, $now, 70,
            $this->community, $chipotle_lat, $chipotle_lon,
            [$this->driver1, $this->driver2],
            [Crunchbutton_Order_Priority::PRIORITY_HIGH, Crunchbutton_Order_Priority::PRIORITY_LOW]);
        $orders[] = $og['o'];
        $allops = array_merge($allops, $og['ops']);

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $earlier50String, $this->community);
        $o2->lat = $chipotle_lat;
        $o2->lon = $chipotle_lon;
        $o2->save();
        $os[] = $o2;

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, $dow);
        $olp1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $fc_lat = 34.0312;
        $fc_lon = -118.286;
        $fc1 = $this->createFakecustomer($this->community, $fc_lat, $fc_lon);
        $fc1->save();

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $drivers = $ol->drivers();

        $ops2 = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $this->driver3->active = true;
        $this->driver3->save();
        foreach ($allops as $op) {
            $op->delete();
        }
        foreach ($orders as $o) {
            $o->delete();
        }
        $driverLoc1->delete();
        $driverLoc2->delete();
        $olp1->delete();
        $olot1->delete();
        $olcs1->delete();
        $fc1->delete();
        $olc->delete();


        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertTrue($driver->__seconds == 0);
            }
            else{
                $this->assertTrue($driver->__seconds == Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
            }
        }
        $this->assertTrue($ops2->count() == 2);

        foreach ($ops2 as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertTrue($op->priority_given == Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else{
                $this->assertTrue($op->priority_given == Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
        }


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
        $earlier120String = $earlier120->format('Y-m-d H:i:s');

        $dow = $now->format('w');

        $this->assertTrue(50 < Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
        $seconds = Crunchbutton_Order_Logistics::TIME_MAX_DELAY - 50;

        $laterM50PlusMax = clone $now;
        $laterM50PlusMax->modify('+ ' . $seconds . ' seconds');
        $laterM50PlusMaxString = $laterM50PlusMax->format('Y-m-d H:i:s');

        // Only want 2 drivers for now
        $this->driver3->active = false;
        $this->driver3->save();

        $driverLoc1 = $this->createAdminLocation($this->driver1->id_admin, 34.0302, -118.273, $earlier120String);
        $driverLoc1->save();
        $driverLoc2 = $this->createAdminLocation($this->driver2->id_admin, 34.0302, -118.273, $earlier120String);
        $driverLoc2->save();

        $chipotle_lat = 34.0284;
        $chipotle_lon = -118.287;

        // Chipotle
        $og = $this->createOrderGroupAndSave($this->user, $this->restaurant3, $now, 200,
            $this->community, $chipotle_lat, $chipotle_lon,
            [$this->driver1, $this->driver2],
            [Crunchbutton_Order_Priority::PRIORITY_HIGH, Crunchbutton_Order_Priority::PRIORITY_LOW]);
        $orders[] = $og['o'];
        $allops = array_merge($allops, $og['ops']);

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $earlier50String, $this->community);
        $o2->lat = $chipotle_lat;
        $o2->lon = $chipotle_lon;
        $o2->save();
        $os[] = $o2;

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, $dow);
        $olp1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $fc_lat = 34.0312;
        $fc_lon = -118.286;
        $fc1 = $this->createFakecustomer($this->community, $fc_lat, $fc_lon);
        $fc1->save();

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $drivers = $ol->drivers();

        $ops2 = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        $this->driver3->active = true;
        $this->driver3->save();
        foreach ($allops as $op) {
            $op->delete();
        }
        foreach ($orders as $o) {
            $o->delete();
        }
        $driverLoc1->delete();
        $driverLoc2->delete();
        $olp1->delete();
        $olot1->delete();
        $olcs1->delete();
        $fc1->delete();
        $olc->delete();


        foreach ($ol->drivers() as $driver) {
            $this->assertTrue($driver->__seconds == 0);
        }
        $this->assertTrue($ops2->count() == 2);

        foreach ($ops2 as $op) {
            $this->assertTrue($op->priority_given == Crunchbutton_Order_Priority::PRIORITY_HIGH);
        }


    }
    //  Three drivers, none with orders
    //  All three drivers are at different locations
    //  New Chipotle order comes in - closest driver gets the priority
    public function testLogisticsThreeDriversNoOrdersNoWait()
    {
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

        $this->assertTrue(50 < Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
        $seconds = Crunchbutton_Order_Logistics::TIME_MAX_DELAY - 50;

        $laterM50PlusMax = clone $now;
        $laterM50PlusMax->modify('+ ' . $seconds . ' seconds');
        $laterM50PlusMaxString = $laterM50PlusMax->format('Y-m-d H:i:s');

        $driverLoc1 = $this->createAdminLocation($this->driver1->id_admin, 34.0302, -118.273, $earlier120String);
        $driverLoc1->save();
        $driverLoc2 = $this->createAdminLocation($this->driver2->id_admin, 34.018, -118.281, $earlier120String);
        $driverLoc2->save();
        $driverLoc3 = $this->createAdminLocation($this->driver2->id_admin, 34.0, -118.27, $earlier120String);
        $driverLoc3->save();

        $chipotle_lat = 34.0284;
        $chipotle_lon = -118.287;

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $earlier50String, $this->community);
        $o2->lat = $chipotle_lat;
        $o2->lon = $chipotle_lon;
        $o2->save();
        $os[] = $o2;

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, $dow);
        $olp1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $fc_lat = 34.0312;
        $fc_lon = -118.286;
        $fc1 = $this->createFakecustomer($this->community, $fc_lat, $fc_lon);
        $fc1->save();

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $drivers = $ol->drivers();

        $ops2 = Crunchbutton_Order_Priority::q('SELECT * FROM order_priority WHERE id_order = ?', [$o2->id_order]);

        $driverLoc1->delete();
        $driverLoc2->delete();
        $driverLoc3->delete();
        $olp1->delete();
        $olot1->delete();
        $olcs1->delete();
        $fc1->delete();
        $olc->delete();


        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver2->id_admin) {
                $this->assertTrue($driver->__seconds == 0);
            } else {
                $this->assertTrue($driver->__seconds == Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
            }
        }
        $this->assertTrue($ops2->count() == 3);

        foreach ($ops2 as $op) {
            if ($op->id_admin == $this->driver2->id_admin) {
                $this->assertTrue($op->priority_given == Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertTrue($op->priority_given == Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
        }
    }
    // Three drivers - driver 1 has an unaccepted, unexpired priority order from Chipotle (with no waiting time).
    //  All three drivers are at the same location
    //  New Chipotle order comes in - driver 1 gets the priority
    public function testLogisticsThreeDriversSecondOrderSameLocationNoWait()
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

        $this->assertTrue(50 < Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
        $seconds = Crunchbutton_Order_Logistics::TIME_MAX_DELAY - 50;

        $laterM50PlusMax = clone $now;
        $laterM50PlusMax->modify('+ ' . $seconds . ' seconds');
        $laterM50PlusMaxString = $laterM50PlusMax->format('Y-m-d H:i:s');

        $driverLoc1 = $this->createAdminLocation($this->driver1->id_admin, 34.0302, -118.273, $earlier120String);
        $driverLoc1->save();
        $driverLoc2 = $this->createAdminLocation($this->driver2->id_admin, 34.0302, -118.273, $earlier120String);
        $driverLoc2->save();
        $driverLoc3 = $this->createAdminLocation($this->driver3->id_admin, 34.0302, -118.273, $earlier120String);
        $driverLoc3->save();

        $chipotle_lat = 34.0284;
        $chipotle_lon = -118.287;

        // Chipotle
        $og = $this->createOrderGroupAndSave($this->user, $this->restaurant3, $now, 70,
            $this->community, $chipotle_lat, $chipotle_lon,
            [$this->driver1, $this->driver2, $this->driver3],
            [Crunchbutton_Order_Priority::PRIORITY_HIGH, Crunchbutton_Order_Priority::PRIORITY_LOW,
            Crunchbutton_Order_Priority::PRIORITY_LOW]);
        $orders[] = $og['o'];
        $allops = array_merge($allops, $og['ops']);

        // Chipotle
        $o2 = $this->defaultOrder($this->user, $this->restaurant3->id_restaurant, $earlier50String, $this->community);
        $o2->lat = $chipotle_lat;
        $o2->lon = $chipotle_lon;
        $o2->save();
        $os[] = $o2;

        $start = date("H:i:s", strtotime('2015-01-01 00:00:00'));
        $end = '24:00:00';
        $olp1 = $this->defaultOLP($this->restaurant3, $start, $end, 5, $dow);
        $olp1->save();

        $olot1 = $this->defaultOLOT($this->restaurant3, $start, $end, 0, 1, $dow);
        $olot1->save();

        $olcs1 = $this->defaultOLCS($this->community, $start, $end, 10, $dow);
        $olcs1->save();

        $fc_lat = 34.0312;
        $fc_lon = -118.286;
        $fc1 = $this->createFakecustomer($this->community, $fc_lat, $fc_lon);
        $fc1->save();

        $olc = $this->defaultOLC($this->restaurant3, $dow, $start, $end, $this->restaurant3->id_restaurant);
        $olc->save();

        $ol = new Crunchbutton_Order_Logistics(Crunchbutton_Order_Logistics::LOGISTICS_COMPLEX, $o2);
        $drivers = $ol->drivers();

        $ops2 = Crunchbutton_Order_Priority::q('select * from order_priority where id_order = ?', [$o2->id_order]);

        foreach ($allops as $op) {
            $op->delete();
        }
        foreach ($orders as $o) {
            $o->delete();
        }
        $driverLoc1->delete();
        $driverLoc2->delete();
        $driverLoc3->delete();
        $olp1->delete();
        $olot1->delete();
        $olcs1->delete();
        $fc1->delete();
        $olc->delete();


        foreach ($ol->drivers() as $driver) {
            if ($driver->id_admin == $this->driver1->id_admin) {
                $this->assertTrue($driver->__seconds == 0);
            } else {
                $this->assertTrue($driver->__seconds == Crunchbutton_Order_Logistics::TIME_MAX_DELAY);
            }
        }
        $this->assertTrue($ops2->count() == 3);

        foreach ($ops2 as $op) {
            if ($op->id_admin == $this->driver1->id_admin) {
                $this->assertTrue($op->priority_given == Crunchbutton_Order_Priority::PRIORITY_HIGH);
            } else {
                $this->assertTrue($op->priority_given == Crunchbutton_Order_Priority::PRIORITY_LOW);
            }
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

    public function createOrderGroupAndSave($user, $restaurant, $nowdt, $earlierSeconds, $community, $lat, $lon, $drivers, $priorities, $lastActionEarlierSeconds=null, $lastAction=null, $actionDriver = null) {
        $og = [];
        $ops = [];
        $oa = null;

        $usedt = clone $nowdt;
        $usedt->modify('- ' . $earlierSeconds . ' seconds');
        $useDateString = $usedt->format('Y-m-d H:i:s');

        $laterdt = clone $usedt;
        $laterdt->modify('- ' . Crunchbutton_Order_Logistics::TIME_MAX_DELAY . ' seconds');
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
            if ($priority == Crunchbutton_Order_Priority::PRIORITY_HIGH) {
                $op = $this->defaultOrderPriority($o, $restaurant, $driver,
                    $useDateString, $priority, 0, $useDateString);
            } else if ($priority == Crunchbutton_Order_Priority::PRIORITY_LOW) {
                $op = $this->defaultOrderPriority($o, $restaurant, $driver,
                    $useDateString, $priority, Crunchbutton_Order_Logistics::TIME_MAX_DELAY, $laterDateString);
            }
            else if ($priority == Crunchbutton_Order_Priority::PRIORITY_NO_ONE) {
                $op = $this->defaultOrderPriority($o, $restaurant, $driver,
                    $useDateString, $priority, 0, $useDateString);
            }
            else {
                $op = $this->defaultOrderPriority($o, $restaurant, $driver,
                    $useDateString, $priority, 0, $useDateString);
            }
            $op->save();
            $ops[] = $op;
            if (!is_null($actionDriver) && $actionDrive == $driver->id_admin) {
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
