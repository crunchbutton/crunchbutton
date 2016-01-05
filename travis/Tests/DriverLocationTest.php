<?php

class DriverLocationTest extends PHPUnit_Framework_TestCase
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
        if( $cs ){
            $cs->delete();
        }
        if( $community ){
            $community->delete();
        }
        if( $community2 ){
            $community2->delete();
        }

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

    // No locations within the time window
    public function testNoDriverLocationFromDbWithinTimeWindow()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));

        $seconds = 120;
        $earlier120 = clone $now;
        $earlier120->modify('- ' . $seconds . ' seconds');

        $earlier1hr = clone $now;
        $earlier1hr->modify('- ' . 60 . ' minutes');

        $loc = $this->driver1->lastLocationWithMinTime($earlier1hr);
        $this->assertNull($loc);
    }

    // Single static location within the time window
    public function testDriverLocationFromDbWithinTimeWindow()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));

        $seconds = 120;
        $earlier120 = clone $now;
        $earlier120->modify('- ' . $seconds . ' seconds');

        $earlier1hr = clone $now;
        $earlier1hr->modify('- ' . 60 . ' minutes');

        $driverLat = 34.0302;
        $driverLon = -118.273;
        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, $driverLat, $driverLon, $earlier120, 10);
        $loc = $this->driver1->lastLocationWithMinTime($earlier1hr);
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        $this->assertEquals(round($loc->lat, 4), $driverLat);
        $this->assertEquals(round($loc->lon, 4), $driverLon);

    }

    // Single static location outside the time window
    public function testDriverLocationFromDbOutsideTimeWindow()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));

        $seconds = 120;
        $earlier120 = clone $now;
        $earlier120->modify('- ' . $seconds . ' seconds');

        $driverLat = 34.0302;
        $driverLon = -118.273;
        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, $driverLat, $driverLon, $earlier120, 10);
        $loc = $this->driver1->lastLocationWithMinTime($now);
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        $this->assertNull($loc);
    }


    // No locations within the time window
    public function testNoDriverLocationsFromDbWithinTimeWindow()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));

        $seconds = 120;
        $earlier120 = clone $now;
        $earlier120->modify('- ' . $seconds . ' seconds');

        $earlier1hr = clone $now;
        $earlier1hr->modify('- ' . 60 . ' minutes');

        $locs = $this->driver1->locationsWithMinTime($earlier1hr);
        $this->assertEquals($locs->count(), 0);
    }

    // Single static location within the time window
    public function testDriverLocationsFromDbWithinTimeWindow()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));

        $seconds = 120;
        $earlier120 = clone $now;
        $earlier120->modify('- ' . $seconds . ' seconds');

        $earlier1hr = clone $now;
        $earlier1hr->modify('- ' . 60 . ' minutes');

        $driverLat = 34.0302;
        $driverLon = -118.273;
        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, $driverLat, $driverLon, $earlier120, 10);

        $locs = $this->driver1->locationsWithMinTime($earlier1hr);
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        $this->assertEquals($locs->count(), 10);
        for ($i = 0; $i < 10; $i++) {
            $this->assertEquals(round($locs->get($i)->lat, 4), $driverLat);
            $this->assertEquals(round($locs->get($i)->lon, 4), $driverLon);

        }

    }

    // Single static location within the time window, all at the same time, should reduce to one location
    public function testDistinctDriverLocationsFromDbWithinTimeWindow()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));

        $seconds = 120;
        $earlier120 = clone $now;
        $earlier120->modify('- ' . $seconds . ' seconds');

        $earlier1hr = clone $now;
        $earlier1hr->modify('- ' . 60 . ' minutes');

        $driverLat = 34.0302;
        $driverLon = -118.273;
        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, $driverLat, $driverLon, $earlier120, 10, 0);

        $locs = $this->driver1->locationsWithMinTime($earlier1hr);
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        $this->assertEquals($locs->count(), 1);
        for ($i = 0; $i < 1; $i++) {
            $this->assertEquals(round($locs->get($i)->lat, 4), $driverLat);
            $this->assertEquals(round($locs->get($i)->lon, 4), $driverLon);

        }

    }


    // Single static location outside the time window
    public function testDriverLocationsFromDbOutsideTimeWindow()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));

        $seconds = 120;
        $earlier120 = clone $now;
        $earlier120->modify('- ' . $seconds . ' seconds');

        $driverLat = 34.0302;
        $driverLon = -118.273;
        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, $driverLat, $driverLon, $earlier120, 10);

        $locs = $this->driver1->locationsWithMinTime($now);
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        $this->assertEquals($locs->count(), 0);

    }

    // Single static location outside the time window
    public function testDriverLocationsFromDbPartiallyOutsideTimeWindow()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));

        $seconds = 120;
        $earlier120 = clone $now;
        $earlier120->modify('- ' . $seconds . ' seconds');

        $seconds = 126;
        $earlier126 = clone $now;
        $earlier126->modify('- ' . $seconds . ' seconds');

        $driverLat = 34.0302;
        $driverLon = -118.273;
        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, $driverLat, $driverLon, $earlier120, 10);
        $locs = $this->driver1->locationsWithMinTime($earlier126);
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        $this->assertEquals($locs->count(), 5);

    }


    // Single static location within the time window
    public function testDriverLocationWithinTimeWindow()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));

        $seconds = 120;
        $earlier120 = clone $now;
        $earlier120->modify('- ' . $seconds . ' seconds');
        $lat = 34.023281;
        $lon = -118.2881961;

        $driverLat = 34.0302;
        $driverLon = -118.273;
        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, $driverLat, $driverLon, $earlier120, 10);
        $cc = new Crunchbutton_Order_Location($lat, $lon);
        $dl = new Crunchbutton_Order_Logistics_DriverLocation($cc);
        $geo = $dl->calcDriverGeoFromLocations($this->driver1, $now);
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        $this->assertEquals(round($geo->lat, 4), $driverLat);
        $this->assertEquals(round($geo->lon, 4), $driverLon);
    }

    // Single static location outside the time window
    public function testDriverLocationOutsideTimeWindow()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));

        $minutes = 120;
        $earlier120 = clone $now;
        $earlier120->modify('- ' . $minutes . ' minutes');
        $lat = 34.023281;
        $lon = -118.2881961;

        $driverLat = 34.0302;
        $driverLon = -118.273;
        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, $driverLat, $driverLon, $earlier120, 10);
        $cc = new Crunchbutton_Order_Location($lat, $lon);
        $dl = new Crunchbutton_Order_Logistics_DriverLocation($cc);
        $geo = $dl->calcDriverGeoFromLocations($this->driver1, $now);
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        $this->assertNull($geo);

    }

    // Single static location outside the time window
    public function testDriverLocationOutsideTimeWindow2()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));

        $minutes = 120;
        $earlier120 = clone $now;
        $earlier120->modify('- ' . $minutes . ' minutes');
        $lat = 34.023281;
        $lon = -118.2881961;

        $driverLat = 34.0302;
        $driverLon = -118.273;
        $driverLocs1 = $this->createAndSaveAdminLocations($this->driver1->id_admin, $driverLat, $driverLon, $earlier120, 10);
        $cc = new Crunchbutton_Order_Location($lat, $lon);
        $dl = new Crunchbutton_Order_Logistics_DriverLocation($cc);
        $dl->determineDriverGeo($this->driver1, $now);
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        $this->assertEquals(round($dl->lat, 6), $lat);
        $this->assertEquals(round($dl->lon, 7), $lon);

    }

    // Moving driver - take only 15 points - assume assorted by time descending
    public function testMovingDriverRegression()
    {

        c::db()->query('delete from admin_location where id_admin = ?',
            [$this->driver1->id_admin]);
        $now = new DateTime('2015-06-30 18:13:25', new DateTimeZone(c::config()->timezone));
        $date = $now->format('Y-m-d H:i:s');
        $ts = $now->getTimestamp();
        // For community center
        $lat = 34.023281;
        $lon = -118.2881961;

        $predLat = 34.02777;
        $predLon = -118.27;

        $lats = [34.0301, 34.0299, 34.0294, 34.0268, 34.0268, 34.0246, 34.0246, 34.0246, 34.0239, 34.0238, 34.0237,
            34.0217, 34.0217, 34.0217, 34.0217, 34.0217, 34.0186, 34.0186, 34.0169, 34.0169, 34.017, 34.017, 34.018];
        $lons = [-118.27, -118.27, -118.27, -118.272, -118.272, -118.273, -118.273, -118.273, -118.274, -118.274,
            -118.274, -118.275, -118.275, -118.275, -118.275, -118.276, -118.277, -118.277, -118.279, -118.279,
            -118.279, -118.279, -118.281];
        $tss = [1435713194, 1435713193, 1435713192, 1435713174, 1435713158, 1435713147, 1435713127, 1435713107,
            1435713097, 1435713096, 1435713095, 1435713087, 1435713067, 1435713065, 1435713064, 1435713063, 1435713047,
            1435713044, 1435713027, 1435713007, 1435712996, 1435712995, 1435712987];
        $driverLocs1 = $this->createAndSaveAdminLocationsFromArrays($this->driver1->id_admin, $lats, $lons, $tss);
        $numLocs = count($lats);
        $als = Cockpit_Admin_Location::q('select * from admin_location where id_admin = ?',
            [$this->driver1->id_admin]);
        $count = $als->count();
        $cc = new Crunchbutton_Order_Location($lat, $lon);
        $dl = new Crunchbutton_Order_Logistics_DriverLocation($cc);
        $dl->determineDriverGeo($this->driver1, $now);
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        $this->assertEquals($ts, 1435713205);

        $this->assertEquals($count, $numLocs);
        $this->assertEquals(round($dl->lat, 5), $predLat);
        $this->assertEquals(round($dl->lon, 2), $predLon);

    }

    public function testTimeZone()
    {
        $stat = c::db()->query("select timediff(now(),convert_tz(now(),@@session.time_zone,'+00:00'))")->fetchAll();
//        var_dump($stat);
        $stat = c::db()->query("SELECT @@global.time_zone, @@session.time_zone")->fetchAll();
//        var_dump($stat);
        $tz = c::config()->timezone;
//        print "The timezone $tz\n";
        $this->assertEquals($tz, "UTC");
    }

    // Moving driver - less than 4 points - only take the EW average
    public function testMovingDriverAverage()
    {
        c::db()->query('delete from admin_location where id_admin = ?',
            [$this->driver1->id_admin]);
        $now = new DateTime('2015-06-30 18:13:25', new DateTimeZone(c::config()->timezone));
        $date = $now->format('Y-m-d H:i:s');
        $ts = $now->getTimestamp();
        // For community center
        $lat = 34.023281;
        $lon = -118.2881961;

        $predLat = 34.02922;
        $predLon = -118.2704;

        $lats = [34.0301, 34.0299, 34.0294, 34.0268];
        $lons = [-118.27, -118.27, -118.27, -118.272];
        $tss = [1435713194, 1435713193, 1435713192, 1435713174];
        $driverLocs1 = $this->createAndSaveAdminLocationsFromArrays($this->driver1->id_admin, $lats, $lons, $tss);
        $numLocs = count($lats);
        $als = Cockpit_Admin_Location::q('select * from admin_location where id_admin = ?',
            [$this->driver1->id_admin]);
        $count = $als->count();
        $cc = new Crunchbutton_Order_Location($lat, $lon);
        $dl = new Crunchbutton_Order_Logistics_DriverLocation($cc);
        $dl->determineDriverGeo($this->driver1, $now);
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }

        $this->assertEquals($ts, 1435713205);
        $this->assertEquals($count, $numLocs);
        $this->assertEquals(round($dl->lat, 5), $predLat);
        $this->assertEquals(round($dl->lon, 4), $predLon);

    }

    public function testSuperFastDriverEW1()
    {
        c::db()->query('delete from admin_location where id_admin = ?',
            [$this->driver1->id_admin]);
        $now = new DateTime('2015-06-30 18:13:25', new DateTimeZone(c::config()->timezone));
        $date = $now->format('Y-m-d H:i:s');
        $ts = $now->getTimestamp();
        // For community center
        $lat = 34.023281;
        $lon = -118.2881961;

        $predLat = 680.5554;
        $predLon = -2365.4254;

        $lats = [680.602, 680.598, 680.588, 680.536, 680.536, 680.492, 680.492, 680.492, 680.478, 680.476, 680.474,
            680.434, 680.434, 680.434, 680.434, 680.434, 680.372, 680.372, 680.338, 680.338, 680.34, 680.34, 680.36];
        $lons = [-2365.4, -2365.4, -2365.4, -2365.44, -2365.44, -2365.46, -2365.46, -2365.46, -2365.48, -2365.48,
            -2365.48, -2365.5, -2365.5, -2365.5, -2365.5, -2365.52, -2365.54, -2365.54, -2365.58, -2365.58, -2365.58,
            -2365.58, -2365.62];
        $tss = [1435713194, 1435713193, 1435713192, 1435713174, 1435713158, 1435713147, 1435713127, 1435713107,
            1435713097, 1435713096, 1435713095, 1435713087, 1435713067, 1435713065, 1435713064, 1435713063, 1435713047,
            1435713044, 1435713027, 1435713007, 1435712996, 1435712995, 1435712987];
        $driverLocs1 = $this->createAndSaveAdminLocationsFromArrays($this->driver1->id_admin, $lats, $lons, $tss);
        $numLocs = count($lats);
        $als = Cockpit_Admin_Location::q('select * from admin_location where id_admin = ?',
            [$this->driver1->id_admin]);
        $count = $als->count();
        $cc = new Crunchbutton_Order_Location($lat, $lon);
        $dl = new Crunchbutton_Order_Logistics_DriverLocation($cc);
        $dl->determineDriverGeo($this->driver1, $now);
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        $this->assertEquals($ts, 1435713205);
        $this->assertEquals($count, $numLocs);
        $this->assertEquals(round($dl->lat, 4), $predLat);
        $this->assertEquals(round($dl->lon, 4), $predLon);

    }

    public function testSuperFastDriverEW2()
    {
        c::db()->query('delete from admin_location where id_admin = ?',
            [$this->driver1->id_admin]);
        $now = new DateTime('2015-06-30 18:13:25', new DateTimeZone(c::config()->timezone));
        $date = $now->format('Y-m-d H:i:s');
        $ts = $now->getTimestamp();
        // For community center
        $lat = 34.023281;
        $lon = -118.2881961;

        $predLat = -680.5554;
        $predLon = 2365.4254;

        $lats = [-680.602, -680.598, -680.588, -680.536, -680.536, -680.492, -680.492, -680.492, -680.478, -680.476,
            -680.474, -680.434, -680.434, -680.434, -680.434, -680.434, -680.372, -680.372, -680.338, -680.338, -680.34,
            -680.34, -680.36];
        $lons = [2365.4, 2365.4, 2365.4, 2365.44, 2365.44, 2365.46, 2365.46, 2365.46, 2365.48, 2365.48, 2365.48,
            2365.5, 2365.5, 2365.5, 2365.5, 2365.52, 2365.54, 2365.54, 2365.58, 2365.58, 2365.58, 2365.58, 2365.62];
        $tss = [1435713194, 1435713193, 1435713192, 1435713174, 1435713158, 1435713147, 1435713127, 1435713107,
            1435713097, 1435713096, 1435713095, 1435713087, 1435713067, 1435713065, 1435713064, 1435713063, 1435713047,
            1435713044, 1435713027, 1435713007, 1435712996, 1435712995, 1435712987];
        $driverLocs1 = $this->createAndSaveAdminLocationsFromArrays($this->driver1->id_admin, $lats, $lons, $tss);
        $numLocs = count($lats);
        $als = Cockpit_Admin_Location::q('select * from admin_location where id_admin = ?',
            [$this->driver1->id_admin]);
        $count = $als->count();
        $cc = new Crunchbutton_Order_Location($lat, $lon);
        $dl = new Crunchbutton_Order_Logistics_DriverLocation($cc);
        $dl->determineDriverGeo($this->driver1, $now);
        foreach ($driverLocs1 as $l) {
            $l->delete();
        }
        $this->assertEquals($count, $numLocs);
        $this->assertEquals(round($dl->lat, 4), $predLat);
        $this->assertEquals(round($dl->lon, 4), $predLon);

    }

    // Test weighted average
    public function testWavg()
    {
        // Community center
        $lat = 34.023281;
        $lon = -118.2881961;

        $cc = new Crunchbutton_Order_Location($lat, $lon);
        $dl = new Crunchbutton_Order_Logistics_DriverLocation($cc);

        $avg = $dl->wavg(1, 2, 0, 0);
        $this->assertEquals($avg, 0);

    }

    // Test weighted average
    public function testWavg2()
    {
        // Community center
        $lat = 34.023281;
        $lon = -118.2881961;

        $cc = new Crunchbutton_Order_Location($lat, $lon);
        $dl = new Crunchbutton_Order_Logistics_DriverLocation($cc);

        $avg = $dl->wavg(1, 2, 2.25, 2.75);
        $this->assertEquals($avg, 1.55);
    }

    // Single order picked up 10 minutes ago
    //  Chipotle - order
    //  Customer - near USC
    public function testDLWithPickedUpOrder()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $curTimestamp = $now->getTimestamp();

        $minutes = 10;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp = $earlierMin->getTimestamp();

        // Community center
        $communityLat = 34.023281;
        $communityLon = -118.2881961;

        $cc = new Crunchbutton_Order_Location($communityLat, $communityLon);

        // Chipotle
        $restaurantLat = 34.017;
        $restaurantLon = -118.282;
        $restaurantGeo = new Crunchbutton_Order_Location($restaurantLat, $restaurantLon);

        // Customer location
        $orderLat = 34.0311;
        $orderLon = -118.288;

        $avgLat = 34.02405;
        $avgLon = -118.285;

        $dl = new Crunchbutton_Order_Logistics_DriverLocation($cc);
        $dl->addPickedUpOrder($actionTimestamp, $curTimestamp, $restaurantGeo, $orderLat, $orderLon);
        $dl->determineDriverGeo($this->driver1, $now);

        $this->assertEquals(round($dl->lat, 5), $avgLat);
        $this->assertEquals(round($dl->lon, 3), $avgLon);

    }

    // Single order picked up 19 minutes ago
    //  Chipotle - order
    //  Customer - near USC
    public function testDLWithPickedUpOrder2()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $curTimestamp = $now->getTimestamp();

        $minutes = 19;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp = $earlierMin->getTimestamp();

        // Community center
        $communityLat = 34.023281;
        $communityLon = -118.2881961;

        $cc = new Crunchbutton_Order_Location($communityLat, $communityLon);

        // Chipotle
        $restaurantLat = 34.017;
        $restaurantLon = -118.282;
        $restaurantGeo = new Crunchbutton_Order_Location($restaurantLat, $restaurantLon);

        // Customer location
        $orderLat = 34.0311;
        $orderLon = -118.288;

        $avgLat = 34.0304;
        $avgLon = -118.2877;

        $dl = new Crunchbutton_Order_Logistics_DriverLocation($cc);
        $dl->addPickedUpOrder($actionTimestamp, $curTimestamp, $restaurantGeo, $orderLat, $orderLon);
        $dl->determineDriverGeo($this->driver1, $now);

        $this->assertEquals(round($dl->lat, 4), $avgLat);
        $this->assertEquals(round($dl->lon, 4), $avgLon);

    }


    // Single order picked up 40 minutes ago
    //  Chipotle - order
    //  Customer - near USC
    public function testDLWithPickedUpOrder3()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $curTimestamp = $now->getTimestamp();

        $minutes = 40;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp = $earlierMin->getTimestamp();

        // Community center
        $communityLat = 34.023281;
        $communityLon = -118.2881961;

        $cc = new Crunchbutton_Order_Location($communityLat, $communityLon);

        // Chipotle
        $restaurantLat = 34.017;
        $restaurantLon = -118.282;
        $restaurantGeo = new Crunchbutton_Order_Location($restaurantLat, $restaurantLon);

        // Customer location
        $orderLat = 34.0311;
        $orderLon = -118.288;

        $avgLat = 34.0297;
        $avgLon = -118.2874;

        $dl = new Crunchbutton_Order_Logistics_DriverLocation($cc);
        $dl->addPickedUpOrder($actionTimestamp, $curTimestamp, $restaurantGeo, $orderLat, $orderLon);
        $dl->determineDriverGeo($this->driver1, $now);

        $this->assertEquals(round($dl->lat, 4), $avgLat);
        $this->assertEquals(round($dl->lon, 4), $avgLon);

    }


    // Single order delivered 10 minutes ago
    //  Chipotle - order
    //  Customer - near USC
    public function testDLWithDeliveredOrder()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $curTimestamp = $now->getTimestamp();

        $minutes = 10;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp = $earlierMin->getTimestamp();

        // Community center
        $communityLat = 34.023281;
        $communityLon = -118.2881961;

        $cc = new Crunchbutton_Order_Location($communityLat, $communityLon);

        // Chipotle - used as cluster for where driver is headed after delivery
        $restaurantLat = 34.017;
        $restaurantLon = -118.282;
        $restaurantGeo = new Crunchbutton_Order_Location($restaurantLat, $restaurantLon);

        // Customer location
        $orderLat = 34.0311;
        $orderLon = -118.288;

        $avgLat = 34.02405;
        $avgLon = -118.285;

        $dl = new Crunchbutton_Order_Logistics_DriverLocation($cc);
        $dl->addDeliveredOrder($actionTimestamp, $curTimestamp, $orderLat, $orderLon, $restaurantGeo);
        $dl->determineDriverGeo($this->driver1, $now);

        $this->assertEquals(round($dl->lat, 5), $avgLat);
        $this->assertEquals(round($dl->lon, 3), $avgLon);

    }

    // Single order delivered 40 minutes ago
    //  Chipotle - order
    //  Customer - near USC
    // Put the driver at 90% of way between community center and chipotle.
    public function testDLWithDeliveredOldOrder()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $curTimestamp = $now->getTimestamp();

        $minutes = 40;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp = $earlierMin->getTimestamp();

        // Community center
        $communityLat = 34.023281;
        $communityLon = -118.2881961;

        $cc = new Crunchbutton_Order_Location($communityLat, $communityLon);

        // Chipotle - used as cluster for where driver is headed after delivery
        $restaurantLat = 34.017;
        $restaurantLon = -118.282;
        $restaurantGeo = new Crunchbutton_Order_Location($restaurantLat, $restaurantLon);

        // Customer location
        $orderLat = 34.0311;
        $orderLon = -118.288;

        $avgLat = 34.0176;
        $avgLon = -118.2826;

        $dl = new Crunchbutton_Order_Logistics_DriverLocation($cc);
        $dl->addDeliveredOrder($actionTimestamp, $curTimestamp, $orderLat, $orderLon, $restaurantGeo);
        $dl->determineDriverGeo($this->driver1, $now);

        $this->assertEquals(round($dl->lat, 4), $avgLat);
        $this->assertEquals(round($dl->lon, 4), $avgLon);

    }


    // Three orders delivered 25, 15, and 10 minutes ago
    //  Five Guys - 25 minutes ago
    //  Pizza Studio - 15 minutes ago
    //  Chipotle - 10 minutes ago
    //  Customers - near USC
    //  If Pizza Studio was found first, that is used as the destination location
    //  Origination location = Chipotle customer
    public function testDLWith3DeliveredOrders()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $curTimestamp = $now->getTimestamp();

        $minutes = 10;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp10 = $earlierMin->getTimestamp();

        $minutes = 15;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp15 = $earlierMin->getTimestamp();

        $minutes = 20;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp20 = $earlierMin->getTimestamp();

        // Community center
        $communityLat = 34.023281;
        $communityLon = -118.2881961;

        $cc = new Crunchbutton_Order_Location($communityLat, $communityLon);

        // Chipotle
        $restaurant1Lat = 34.017;
        $restaurant1Lon = -118.282;
        $restaurant1Geo = new Crunchbutton_Order_Location($restaurant1Lat, $restaurant1Lon);

        // Pizza Studio
        $restaurant2Lat = 34.0187;
        $restaurant2Lon = -118.282;
        $restaurant2Geo = new Crunchbutton_Order_Location($restaurant2Lat, $restaurant2Lon);

        // Five Guys
        $restaurant3Lat = 34.0269;
        $restaurant3Lon = -118.276;
        $restaurant3Geo = new Crunchbutton_Order_Location($restaurant3Lat, $restaurant3Lon);

        // Customer location
        $order1Lat = 34.0311;
        $order1Lon = -118.288;

        $order2Lat = 34.0284;
        $order2Lon = -118.287;

        $order3Lat = 34.0248;
        $order3Lon = -118.28;

        $avgLat = 34.0249;
        $avgLon = -118.2850;

        $dl = new Crunchbutton_Order_Logistics_DriverLocation($cc);
        $dl->addDeliveredOrder($actionTimestamp15, $curTimestamp, $order2Lat, $order2Lon, $restaurant2Geo);
        $dl->addDeliveredOrder($actionTimestamp10, $curTimestamp, $order1Lat, $order1Lon, $restaurant1Geo);
        $dl->addDeliveredOrder($actionTimestamp20, $curTimestamp, $order3Lat, $order3Lon, $restaurant3Geo);
        $dl->determineDriverGeo($this->driver1, $now);

        $this->assertEquals(round($dl->lat, 4), $avgLat);
        $this->assertEquals(round($dl->lon, 4), $avgLon);

    }


    // Single order accepted 10 minutes ago
    //  Chipotle - order
    //  Customer - near USC
    //  Should be 90% of the way from the community center to the restaurant
    public function testDLWithAcceptedOrder()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $curTimestamp = $now->getTimestamp();

        $minutes = 10;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp = $earlierMin->getTimestamp();

        // Community center
        $communityLat = 34.023281;
        $communityLon = -118.2881961;

        $cc = new Crunchbutton_Order_Location($communityLat, $communityLon);

        // Chipotle
        $restaurantLat = 34.017;
        $restaurantLon = -118.282;
        $restaurantGeo = new Crunchbutton_Order_Location($restaurantLat, $restaurantLon);

        $avgLat = 34.0176;
        $avgLon = -118.2826;

        $dl = new Crunchbutton_Order_Logistics_DriverLocation($cc);
        $dl->addAcceptedOrder($actionTimestamp, $curTimestamp, $restaurantGeo);
        $dl->determineDriverGeo($this->driver1, $now);

        $this->assertEquals(round($dl->lat, 4), $avgLat);
        $this->assertEquals(round($dl->lon, 4), $avgLon);

    }

    // Three orders accepted 20, 15, and 10 minutes ago
    //  Chipotle - 20 min ago
    //  Pizza Studio - 15 min ago
    //  Five Guys - 10 min ago
    //  Customer - near USC
    //  Should be 90% of the way from the community center to Chipotle
    public function testDLWith3AcceptedOrders()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $curTimestamp = $now->getTimestamp();

        $minutes = 10;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp10 = $earlierMin->getTimestamp();

        $minutes = 15;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp15 = $earlierMin->getTimestamp();

        $minutes = 20;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp20 = $earlierMin->getTimestamp();

        // Community center
        $communityLat = 34.023281;
        $communityLon = -118.2881961;

        $cc = new Crunchbutton_Order_Location($communityLat, $communityLon);

        // Chipotle
        $restaurant1Lat = 34.017;
        $restaurant1Lon = -118.282;
        $restaurant1Geo = new Crunchbutton_Order_Location($restaurant1Lat, $restaurant1Lon);

        // Pizza Studio
        $restaurant2Lat = 34.0187;
        $restaurant2Lon = -118.282;
        $restaurant2Geo = new Crunchbutton_Order_Location($restaurant2Lat, $restaurant2Lon);

        // Five Guys
        $restaurant3Lat = 34.0269;
        $restaurant3Lon = -118.276;
        $restaurant3Geo = new Crunchbutton_Order_Location($restaurant3Lat, $restaurant3Lon);

        $avgLat = 34.0176;
        $avgLon = -118.2826;

        $dl = new Crunchbutton_Order_Logistics_DriverLocation($cc);
        $dl->addAcceptedOrder($actionTimestamp10, $curTimestamp, $restaurant3Geo);
        $dl->addAcceptedOrder($actionTimestamp20, $curTimestamp, $restaurant1Geo);
        $dl->addAcceptedOrder($actionTimestamp15, $curTimestamp, $restaurant2Geo);
        $dl->determineDriverGeo($this->driver1, $now);

        $this->assertEquals(round($dl->lat, 4), $avgLat);
        $this->assertEquals(round($dl->lon, 4), $avgLon);

    }


    // Two orders delivered 25 and 5 minutes ago
    //  One order picked up 15 minutes ago.
    //  Five Guys - 25 minutes ago
    //  Pizza Studio - 15 minutes ago
    //  Chipotle - 5 minutes ago
    //  Customers - near USC
    //  Should be traveling from the Chipotle customer to the Pizza Studio customer

    public function testDLWith2Delivered1PickedupOrders()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $curTimestamp = $now->getTimestamp();

        $minutes = 5;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp5 = $earlierMin->getTimestamp();

        $minutes = 15;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp15 = $earlierMin->getTimestamp();

        $minutes = 20;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp20 = $earlierMin->getTimestamp();

        // Community center
        $communityLat = 34.023281;
        $communityLon = -118.2881961;

        $cc = new Crunchbutton_Order_Location($communityLat, $communityLon);

        // Chipotle
        $restaurant1Lat = 34.017;
        $restaurant1Lon = -118.282;
        $restaurant1Geo = new Crunchbutton_Order_Location($restaurant1Lat, $restaurant1Lon);

        // Pizza Studio
        $restaurant2Lat = 34.0187;
        $restaurant2Lon = -118.282;
        $restaurant2Geo = new Crunchbutton_Order_Location($restaurant2Lat, $restaurant2Lon);

        // Five Guys
        $restaurant3Lat = 34.0269;
        $restaurant3Lon = -118.276;
        $restaurant3Geo = new Crunchbutton_Order_Location($restaurant3Lat, $restaurant3Lon);

        // Customer location
        $order1Lat = 34.0311;
        $order1Lon = -118.288;

        $order2Lat = 34.0284;
        $order2Lon = -118.287;

        $order3Lat = 34.0248;
        $order3Lon = -118.28;

        $avgLat = 34.02975;
        $avgLon = -118.2875;

        $dl = new Crunchbutton_Order_Logistics_DriverLocation($cc);
        $dl->addPickedUpOrder($actionTimestamp15, $curTimestamp, $restaurant2Geo, $order2Lat, $order2Lon);
        $dl->addDeliveredOrder($actionTimestamp5, $curTimestamp, $order1Lat, $order1Lon, $restaurant1Geo);
        $dl->addDeliveredOrder($actionTimestamp20, $curTimestamp, $order3Lat, $order3Lon, $restaurant3Geo);
        $dl->determineDriverGeo($this->driver1, $now);

        $this->assertEquals(round($dl->lat, 5), $avgLat);
        $this->assertEquals(round($dl->lon, 4), $avgLon);

    }

    // Two orders delivered 25 and 5 minutes ago
    //  One order picked up 15 minutes ago.
    //  Five Guys - 25 minutes ago
    //  Pizza Studio - 15 minutes ago
    //  Chipotle - 5 minutes ago
    //  Customers - near USC
    //  Should be traveling from the Chipotle customer to the Pizza Studio customer
    //   Change the order of adding the orders to the DriverLocation to see if that affects things.  It shouldn't.

    public function testDLWith2Delivered1PickedupOrders2()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $curTimestamp = $now->getTimestamp();

        $minutes = 5;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp5 = $earlierMin->getTimestamp();

        $minutes = 15;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp15 = $earlierMin->getTimestamp();

        $minutes = 20;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp20 = $earlierMin->getTimestamp();

        // Community center
        $communityLat = 34.023281;
        $communityLon = -118.2881961;

        $cc = new Crunchbutton_Order_Location($communityLat, $communityLon);

        // Chipotle
        $restaurant1Lat = 34.017;
        $restaurant1Lon = -118.282;
        $restaurant1Geo = new Crunchbutton_Order_Location($restaurant1Lat, $restaurant1Lon);

        // Pizza Studio
        $restaurant2Lat = 34.0187;
        $restaurant2Lon = -118.282;
        $restaurant2Geo = new Crunchbutton_Order_Location($restaurant2Lat, $restaurant2Lon);

        // Five Guys
        $restaurant3Lat = 34.0269;
        $restaurant3Lon = -118.276;
        $restaurant3Geo = new Crunchbutton_Order_Location($restaurant3Lat, $restaurant3Lon);

        // Customer location
        $order1Lat = 34.0311;
        $order1Lon = -118.288;

        $order2Lat = 34.0284;
        $order2Lon = -118.287;

        $order3Lat = 34.0248;
        $order3Lon = -118.28;

        $avgLat = 34.02975;
        $avgLon = -118.2875;

        $dl = new Crunchbutton_Order_Logistics_DriverLocation($cc);
        $dl->addDeliveredOrder($actionTimestamp5, $curTimestamp, $order1Lat, $order1Lon, $restaurant1Geo);
        $dl->addDeliveredOrder($actionTimestamp20, $curTimestamp, $order3Lat, $order3Lon, $restaurant3Geo);
        $dl->addPickedUpOrder($actionTimestamp15, $curTimestamp, $restaurant2Geo, $order2Lat, $order2Lon);
        $dl->determineDriverGeo($this->driver1, $now);

        $this->assertEquals(round($dl->lat, 5), $avgLat);
        $this->assertEquals(round($dl->lon, 4), $avgLon);

    }

    // Two orders delivered 25 and 17 minutes ago
    //  One order picked up 15 minutes ago.
    //  Five Guys - 25 minutes ago
    //  Pizza Studio - 15 minutes ago
    //  Chipotle - 17 minutes ago
    //  Customers - near USC
    //  Should be traveling from the Pizza Studio to the Pizza Studio customer

    public function testDLWith2Delivered1PickedupOrders3()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $curTimestamp = $now->getTimestamp();

        $minutes = 17;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp17 = $earlierMin->getTimestamp();

        $minutes = 15;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp15 = $earlierMin->getTimestamp();

        $minutes = 20;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp20 = $earlierMin->getTimestamp();

        // Community center
        $communityLat = 34.023281;
        $communityLon = -118.2881961;

        $cc = new Crunchbutton_Order_Location($communityLat, $communityLon);

        // Chipotle
        $restaurant1Lat = 34.017;
        $restaurant1Lon = -118.282;
        $restaurant1Geo = new Crunchbutton_Order_Location($restaurant1Lat, $restaurant1Lon);

        // Pizza Studio
        $restaurant2Lat = 34.0187;
        $restaurant2Lon = -118.282;
        $restaurant2Geo = new Crunchbutton_Order_Location($restaurant2Lat, $restaurant2Lon);

        // Five Guys
        $restaurant3Lat = 34.0269;
        $restaurant3Lon = -118.276;
        $restaurant3Geo = new Crunchbutton_Order_Location($restaurant3Lat, $restaurant3Lon);

        // Customer location
        $order1Lat = 34.0311;
        $order1Lon = -118.288;

        $order2Lat = 34.0284;
        $order2Lon = -118.287;

        $order3Lat = 34.0248;
        $order3Lon = -118.28;

        $avgLat = 34.026;
        $avgLon = -118.286;

        $dl = new Crunchbutton_Order_Logistics_DriverLocation($cc);
        $dl->addPickedUpOrder($actionTimestamp15, $curTimestamp, $restaurant2Geo, $order2Lat, $order2Lon);
        $dl->addDeliveredOrder($actionTimestamp17, $curTimestamp, $order1Lat, $order1Lon, $restaurant1Geo);
        $dl->addDeliveredOrder($actionTimestamp20, $curTimestamp, $order3Lat, $order3Lon, $restaurant3Geo);
        $dl->determineDriverGeo($this->driver1, $now);

        $this->assertEquals(round($dl->lat, 3), $avgLat);
        $this->assertEquals(round($dl->lon, 3), $avgLon);

    }

    // Two orders delivered 25 and 17 minutes ago
    //  One order picked up 15 minutes ago.
    //  Five Guys - 25 minutes ago
    //  Pizza Studio - 15 minutes ago
    //  Chipotle - 17 minutes ago
    //  Customers - near USC
    //  Should be traveling from the Pizza Studio to the Pizza Studio customer
    //   Change the order of adding the orders to the DriverLocation to see if that affects things.  It shouldn't.

    public function testDLWith2Delivered1PickedupOrders4()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $curTimestamp = $now->getTimestamp();

        $minutes = 17;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp17 = $earlierMin->getTimestamp();

        $minutes = 15;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp15 = $earlierMin->getTimestamp();

        $minutes = 20;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp20 = $earlierMin->getTimestamp();

        // Community center
        $communityLat = 34.023281;
        $communityLon = -118.2881961;

        $cc = new Crunchbutton_Order_Location($communityLat, $communityLon);

        // Chipotle
        $restaurant1Lat = 34.017;
        $restaurant1Lon = -118.282;
        $restaurant1Geo = new Crunchbutton_Order_Location($restaurant1Lat, $restaurant1Lon);

        // Pizza Studio
        $restaurant2Lat = 34.0187;
        $restaurant2Lon = -118.282;
        $restaurant2Geo = new Crunchbutton_Order_Location($restaurant2Lat, $restaurant2Lon);

        // Five Guys
        $restaurant3Lat = 34.0269;
        $restaurant3Lon = -118.276;
        $restaurant3Geo = new Crunchbutton_Order_Location($restaurant3Lat, $restaurant3Lon);

        // Customer location
        $order1Lat = 34.0311;
        $order1Lon = -118.288;

        $order2Lat = 34.0284;
        $order2Lon = -118.287;

        $order3Lat = 34.0248;
        $order3Lon = -118.28;

        $avgLat = 34.026;
        $avgLon = -118.286;

        $dl = new Crunchbutton_Order_Logistics_DriverLocation($cc);
        $dl->addDeliveredOrder($actionTimestamp20, $curTimestamp, $order3Lat, $order3Lon, $restaurant3Geo);
        $dl->addDeliveredOrder($actionTimestamp17, $curTimestamp, $order1Lat, $order1Lon, $restaurant1Geo);
        $dl->addPickedUpOrder($actionTimestamp15, $curTimestamp, $restaurant2Geo, $order2Lat, $order2Lon);
        $dl->determineDriverGeo($this->driver1, $now);

        $this->assertEquals(round($dl->lat, 3), $avgLat);
        $this->assertEquals(round($dl->lon, 3), $avgLon);

    }

    // Two orders delivered 25 and 5 minutes ago
    //  One order accepted 15 minutes ago.
    //  Five Guys - 25 minutes ago
    //  Pizza Studio - 15 minutes ago
    //  Chipotle - 5 minutes ago
    //  Customers - near USC
    //  Should be traveling from the Chipotle customer to Pizza Studio

    public function testDLWith2Delivered1AcceptedOrders()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $curTimestamp = $now->getTimestamp();

        $minutes = 5;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp5 = $earlierMin->getTimestamp();

        $minutes = 15;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp15 = $earlierMin->getTimestamp();

        $minutes = 20;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp20 = $earlierMin->getTimestamp();

        // Community center
        $communityLat = 34.023281;
        $communityLon = -118.2881961;

        $cc = new Crunchbutton_Order_Location($communityLat, $communityLon);

        // Chipotle
        $restaurant1Lat = 34.017;
        $restaurant1Lon = -118.282;
        $restaurant1Geo = new Crunchbutton_Order_Location($restaurant1Lat, $restaurant1Lon);

        // Pizza Studio
        $restaurant2Lat = 34.0187;
        $restaurant2Lon = -118.282;
        $restaurant2Geo = new Crunchbutton_Order_Location($restaurant2Lat, $restaurant2Lon);

        // Five Guys
        $restaurant3Lat = 34.0269;
        $restaurant3Lon = -118.276;
        $restaurant3Geo = new Crunchbutton_Order_Location($restaurant3Lat, $restaurant3Lon);

        // Customer location
        $order1Lat = 34.0311;
        $order1Lon = -118.288;

        $order2Lat = 34.0284;
        $order2Lon = -118.287;

        $order3Lat = 34.0248;
        $order3Lon = -118.28;

        $avgLat = 34.0280;
        $avgLon = -118.2865;

        $dl = new Crunchbutton_Order_Logistics_DriverLocation($cc);
        $dl->addAcceptedOrder($actionTimestamp15, $curTimestamp, $restaurant2Geo);
        $dl->addDeliveredOrder($actionTimestamp5, $curTimestamp, $order1Lat, $order1Lon, $restaurant1Geo);
        $dl->addDeliveredOrder($actionTimestamp20, $curTimestamp, $order3Lat, $order3Lon, $restaurant3Geo);
        $dl->determineDriverGeo($this->driver1, $now);

        $this->assertEquals(round($dl->lat, 4), $avgLat);
        $this->assertEquals(round($dl->lon, 4), $avgLon);

    }

    // Two orders delivered 25 and 5 minutes ago
    //  One order accepted 15 minutes ago.
    //  Five Guys - 25 minutes ago
    //  Pizza Studio - 15 minutes ago
    //  Chipotle - 5 minutes ago
    //  Customers - near USC
    //  Should be traveling from the Chipotle customer to Pizza Studio
    //   Change the order of adding the orders to the DriverLocation to see if that affects things.  It shouldn't.
    public function testDLWith2Delivered1AcceptedOrders2()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $curTimestamp = $now->getTimestamp();

        $minutes = 5;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp5 = $earlierMin->getTimestamp();

        $minutes = 15;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp15 = $earlierMin->getTimestamp();

        $minutes = 20;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp20 = $earlierMin->getTimestamp();

        // Community center
        $communityLat = 34.023281;
        $communityLon = -118.2881961;

        $cc = new Crunchbutton_Order_Location($communityLat, $communityLon);

        // Chipotle
        $restaurant1Lat = 34.017;
        $restaurant1Lon = -118.282;
        $restaurant1Geo = new Crunchbutton_Order_Location($restaurant1Lat, $restaurant1Lon);

        // Pizza Studio
        $restaurant2Lat = 34.0187;
        $restaurant2Lon = -118.282;
        $restaurant2Geo = new Crunchbutton_Order_Location($restaurant2Lat, $restaurant2Lon);

        // Five Guys
        $restaurant3Lat = 34.0269;
        $restaurant3Lon = -118.276;
        $restaurant3Geo = new Crunchbutton_Order_Location($restaurant3Lat, $restaurant3Lon);

        // Customer location
        $order1Lat = 34.0311;
        $order1Lon = -118.288;

        $order2Lat = 34.0284;
        $order2Lon = -118.287;

        $order3Lat = 34.0248;
        $order3Lon = -118.28;

        $avgLat = 34.0280;
        $avgLon = -118.2865;

        $dl = new Crunchbutton_Order_Logistics_DriverLocation($cc);
        $dl->addDeliveredOrder($actionTimestamp5, $curTimestamp, $order1Lat, $order1Lon, $restaurant1Geo);
        $dl->addAcceptedOrder($actionTimestamp15, $curTimestamp, $restaurant2Geo);
        $dl->addDeliveredOrder($actionTimestamp20, $curTimestamp, $order3Lat, $order3Lon, $restaurant3Geo);
        $dl->determineDriverGeo($this->driver1, $now);

        $this->assertEquals(round($dl->lat, 4), $avgLat);
        $this->assertEquals(round($dl->lon, 4), $avgLon);

    }


    // One orders delivered 5 minutes ago
    //  Two orders picked up 20 and 15 minutes ago.
    //  Five Guys - 15 minutes ago
    //  Pizza Studio - 20 minutes ago
    //  Chipotle - 5 minutes ago
    //  Customers - near USC
    //  Should be traveling from the Chipotle customer to the Pizza Studio customer

    public function testDLWith1Delivered2PickedupOrders()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $curTimestamp = $now->getTimestamp();

        $minutes = 5;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp5 = $earlierMin->getTimestamp();

        $minutes = 15;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp15 = $earlierMin->getTimestamp();

        $minutes = 20;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp20 = $earlierMin->getTimestamp();

        // Community center
        $communityLat = 34.023281;
        $communityLon = -118.2881961;

        $cc = new Crunchbutton_Order_Location($communityLat, $communityLon);

        // Chipotle
        $restaurant1Lat = 34.017;
        $restaurant1Lon = -118.282;
        $restaurant1Geo = new Crunchbutton_Order_Location($restaurant1Lat, $restaurant1Lon);

        // Pizza Studio
        $restaurant2Lat = 34.0187;
        $restaurant2Lon = -118.282;
        $restaurant2Geo = new Crunchbutton_Order_Location($restaurant2Lat, $restaurant2Lon);

        // Five Guys
        $restaurant3Lat = 34.0269;
        $restaurant3Lon = -118.276;
        $restaurant3Geo = new Crunchbutton_Order_Location($restaurant3Lat, $restaurant3Lon);

        // Customer location
        $order1Lat = 34.0311;
        $order1Lon = -118.288;

        $order2Lat = 34.0284;
        $order2Lon = -118.287;

        $order3Lat = 34.0248;
        $order3Lon = -118.28;

        $avgLat = 34.02975;
        $avgLon = -118.2875;

        $dl = new Crunchbutton_Order_Logistics_DriverLocation($cc);
        $dl->addPickedUpOrder($actionTimestamp20, $curTimestamp, $restaurant2Geo, $order2Lat, $order2Lon);
        $dl->addPickedUpOrder($actionTimestamp15, $curTimestamp, $restaurant3Geo, $order3Lat, $order3Lon);
        $dl->addDeliveredOrder($actionTimestamp5, $curTimestamp, $order1Lat, $order1Lon, $restaurant1Geo);
        $dl->determineDriverGeo($this->driver1, $now);

        $this->assertEquals(round($dl->lat, 5), $avgLat);
        $this->assertEquals(round($dl->lon, 4), $avgLon);

    }

    // One orders delivered 5 minutes ago
    //  Two orders picked up 20 and 15 minutes ago.
    //  Five Guys - 15 minutes ago
    //  Pizza Studio - 20 minutes ago
    //  Chipotle - 5 minutes ago
    //  Customers - near USC
    //  Should be traveling from the Chipotle customer to the Pizza Studio customer
    //   Change the order of adding the orders to the DriverLocation to see if that affects things.  It shouldn't.

    public function testDLWith1Delivered2PickedupOrders2()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $curTimestamp = $now->getTimestamp();

        $minutes = 5;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp5 = $earlierMin->getTimestamp();

        $minutes = 15;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp15 = $earlierMin->getTimestamp();

        $minutes = 20;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp20 = $earlierMin->getTimestamp();

        // Community center
        $communityLat = 34.023281;
        $communityLon = -118.2881961;

        $cc = new Crunchbutton_Order_Location($communityLat, $communityLon);

        // Chipotle
        $restaurant1Lat = 34.017;
        $restaurant1Lon = -118.282;
        $restaurant1Geo = new Crunchbutton_Order_Location($restaurant1Lat, $restaurant1Lon);

        // Pizza Studio
        $restaurant2Lat = 34.0187;
        $restaurant2Lon = -118.282;
        $restaurant2Geo = new Crunchbutton_Order_Location($restaurant2Lat, $restaurant2Lon);

        // Five Guys
        $restaurant3Lat = 34.0269;
        $restaurant3Lon = -118.276;
        $restaurant3Geo = new Crunchbutton_Order_Location($restaurant3Lat, $restaurant3Lon);

        // Customer location
        $order1Lat = 34.0311;
        $order1Lon = -118.288;

        $order2Lat = 34.0284;
        $order2Lon = -118.287;

        $order3Lat = 34.0248;
        $order3Lon = -118.28;

        $avgLat = 34.02975;
        $avgLon = -118.2875;

        $dl = new Crunchbutton_Order_Logistics_DriverLocation($cc);
        $dl->addPickedUpOrder($actionTimestamp20, $curTimestamp, $restaurant2Geo, $order2Lat, $order2Lon);
        $dl->addPickedUpOrder($actionTimestamp15, $curTimestamp, $restaurant3Geo, $order3Lat, $order3Lon);
        $dl->addDeliveredOrder($actionTimestamp5, $curTimestamp, $order1Lat, $order1Lon, $restaurant1Geo);
        $dl->determineDriverGeo($this->driver1, $now);

        $this->assertEquals(round($dl->lat, 5), $avgLat);
        $this->assertEquals(round($dl->lon, 4), $avgLon);

    }

    // One orders delivered 17 minutes ago
    //  Two orders picked up 20 and 15 minutes ago.
    //  Five Guys - 15 minutes ago
    //  Pizza Studio - 20 minutes ago
    //  Chipotle - 17 minutes ago
    //  Customers - near USC
    //  Should be traveling from Five Guys to the Pizza Studio customer

    public function testDLWith1Delivered2PickedupOrders3()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $curTimestamp = $now->getTimestamp();

        $minutes = 17;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp17 = $earlierMin->getTimestamp();

        $minutes = 15;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp15 = $earlierMin->getTimestamp();

        $minutes = 20;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp20 = $earlierMin->getTimestamp();

        // Community center
        $communityLat = 34.023281;
        $communityLon = -118.2881961;

        $cc = new Crunchbutton_Order_Location($communityLat, $communityLon);

        // Chipotle
        $restaurant1Lat = 34.017;
        $restaurant1Lon = -118.282;
        $restaurant1Geo = new Crunchbutton_Order_Location($restaurant1Lat, $restaurant1Lon);

        // Pizza Studio
        $restaurant2Lat = 34.0187;
        $restaurant2Lon = -118.282;
        $restaurant2Geo = new Crunchbutton_Order_Location($restaurant2Lat, $restaurant2Lon);

        // Five Guys
        $restaurant3Lat = 34.0269;
        $restaurant3Lon = -118.276;
        $restaurant3Geo = new Crunchbutton_Order_Location($restaurant3Lat, $restaurant3Lon);

        // Customer location
        $order1Lat = 34.0311;
        $order1Lon = -118.288;

        $order2Lat = 34.0284;
        $order2Lon = -118.287;

        $order3Lat = 34.0248;
        $order3Lon = -118.28;

        $avgLat = 34.0280;
        $avgLon = -118.28425;

        $dl = new Crunchbutton_Order_Logistics_DriverLocation($cc);
        $dl->addPickedUpOrder($actionTimestamp20, $curTimestamp, $restaurant2Geo, $order2Lat, $order2Lon);
        $dl->addPickedUpOrder($actionTimestamp15, $curTimestamp, $restaurant3Geo, $order3Lat, $order3Lon);
        $dl->addDeliveredOrder($actionTimestamp17, $curTimestamp, $order1Lat, $order1Lon, $restaurant1Geo);
        $dl->determineDriverGeo($this->driver1, $now);

        $this->assertEquals(round($dl->lat, 4), $avgLat);
        $this->assertEquals(round($dl->lon, 5), $avgLon);

    }


    // One orders delivered 17 minutes ago
    //  Two orders picked up 20 and 15 minutes ago.
    //  Five Guys - 15 minutes ago
    //  Pizza Studio - 20 minutes ago
    //  Chipotle - 17 minutes ago
    //  Customers - near USC
    //  Should be traveling from Five Guys to the Pizza Studio customer
    //   Change the order of adding the orders to the DriverLocation to see if that affects things.  It shouldn't.

    public function testDLWith1Delivered2PickedupOrders4()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $curTimestamp = $now->getTimestamp();

        $minutes = 17;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp17 = $earlierMin->getTimestamp();

        $minutes = 15;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp15 = $earlierMin->getTimestamp();

        $minutes = 20;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp20 = $earlierMin->getTimestamp();

        // Community center
        $communityLat = 34.023281;
        $communityLon = -118.2881961;

        $cc = new Crunchbutton_Order_Location($communityLat, $communityLon);

        // Chipotle
        $restaurant1Lat = 34.017;
        $restaurant1Lon = -118.282;
        $restaurant1Geo = new Crunchbutton_Order_Location($restaurant1Lat, $restaurant1Lon);

        // Pizza Studio
        $restaurant2Lat = 34.0187;
        $restaurant2Lon = -118.282;
        $restaurant2Geo = new Crunchbutton_Order_Location($restaurant2Lat, $restaurant2Lon);

        // Five Guys
        $restaurant3Lat = 34.0269;
        $restaurant3Lon = -118.276;
        $restaurant3Geo = new Crunchbutton_Order_Location($restaurant3Lat, $restaurant3Lon);

        // Customer location
        $order1Lat = 34.0311;
        $order1Lon = -118.288;

        $order2Lat = 34.0284;
        $order2Lon = -118.287;

        $order3Lat = 34.0248;
        $order3Lon = -118.28;

        $avgLat = 34.0280;
        $avgLon = -118.28425;

        $dl = new Crunchbutton_Order_Logistics_DriverLocation($cc);
        $dl->addPickedUpOrder($actionTimestamp15, $curTimestamp, $restaurant3Geo, $order3Lat, $order3Lon);
        $dl->addDeliveredOrder($actionTimestamp17, $curTimestamp, $order1Lat, $order1Lon, $restaurant1Geo);
        $dl->addPickedUpOrder($actionTimestamp20, $curTimestamp, $restaurant2Geo, $order2Lat, $order2Lon);
        $dl->determineDriverGeo($this->driver1, $now);

        $this->assertEquals(round($dl->lat, 4), $avgLat);
        $this->assertEquals(round($dl->lon, 5), $avgLon);

    }

    // One orders delivered 5 minutes ago
    //  One order accepted 20 minutes ago
    //  One order picked up 15 minutes ago
    //  Five Guys - 20 minutes ago
    //  Pizza Studio - 15 minutes ago
    //  Chipotle - 5 minutes ago
    //  Customers - near USC
    //  Should be traveling from the Chipotle customer to the Pizza Studio customer

    public function testDLWith1Delivered1Pickedup1Accept()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $curTimestamp = $now->getTimestamp();

        $minutes = 5;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp5 = $earlierMin->getTimestamp();

        $minutes = 15;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp15 = $earlierMin->getTimestamp();

        $minutes = 20;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp20 = $earlierMin->getTimestamp();

        // Community center
        $communityLat = 34.023281;
        $communityLon = -118.2881961;

        $cc = new Crunchbutton_Order_Location($communityLat, $communityLon);

        // Chipotle
        $restaurant1Lat = 34.017;
        $restaurant1Lon = -118.282;
        $restaurant1Geo = new Crunchbutton_Order_Location($restaurant1Lat, $restaurant1Lon);

        // Pizza Studio
        $restaurant2Lat = 34.0187;
        $restaurant2Lon = -118.282;
        $restaurant2Geo = new Crunchbutton_Order_Location($restaurant2Lat, $restaurant2Lon);

        // Five Guys
        $restaurant3Lat = 34.0269;
        $restaurant3Lon = -118.276;
        $restaurant3Geo = new Crunchbutton_Order_Location($restaurant3Lat, $restaurant3Lon);

        // Customer location
        $order1Lat = 34.0311;
        $order1Lon = -118.288;

        $order2Lat = 34.0284;
        $order2Lon = -118.287;

        $order3Lat = 34.0248;
        $order3Lon = -118.28;

        $avgLat = 34.02975;
        $avgLon = -118.2875;

        $dl = new Crunchbutton_Order_Logistics_DriverLocation($cc);
        $dl->addPickedUpOrder($actionTimestamp15, $curTimestamp, $restaurant2Geo, $order2Lat, $order2Lon);
        $dl->addAcceptedOrder($actionTimestamp20, $curTimestamp, $restaurant3Geo);
        $dl->addDeliveredOrder($actionTimestamp5, $curTimestamp, $order1Lat, $order1Lon, $restaurant1Geo);
        $dl->determineDriverGeo($this->driver1, $now);

        $this->assertEquals(round($dl->lat, 5), $avgLat);
        $this->assertEquals(round($dl->lon, 4), $avgLon);

    }


    // One orders delivered 5 minutes ago
    //  One order accepted 20 minutes ago
    //  One order picked up 15 minutes ago
    //  Five Guys - 20 minutes ago
    //  Pizza Studio - 15 minutes ago
    //  Chipotle - 5 minutes ago
    //  Customers - near USC
    //  Should be traveling from the Chipotle customer to the Pizza Studio customer
    //   Change the order of adding the orders to the DriverLocation to see if that affects things.  It shouldn't.

    public function testDLWith1Delivered1Pickedup1Accept2()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $curTimestamp = $now->getTimestamp();

        $minutes = 5;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp5 = $earlierMin->getTimestamp();

        $minutes = 15;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp15 = $earlierMin->getTimestamp();

        $minutes = 20;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp20 = $earlierMin->getTimestamp();

        // Community center
        $communityLat = 34.023281;
        $communityLon = -118.2881961;

        $cc = new Crunchbutton_Order_Location($communityLat, $communityLon);

        // Chipotle
        $restaurant1Lat = 34.017;
        $restaurant1Lon = -118.282;
        $restaurant1Geo = new Crunchbutton_Order_Location($restaurant1Lat, $restaurant1Lon);

        // Pizza Studio
        $restaurant2Lat = 34.0187;
        $restaurant2Lon = -118.282;
        $restaurant2Geo = new Crunchbutton_Order_Location($restaurant2Lat, $restaurant2Lon);

        // Five Guys
        $restaurant3Lat = 34.0269;
        $restaurant3Lon = -118.276;
        $restaurant3Geo = new Crunchbutton_Order_Location($restaurant3Lat, $restaurant3Lon);

        // Customer location
        $order1Lat = 34.0311;
        $order1Lon = -118.288;

        $order2Lat = 34.0284;
        $order2Lon = -118.287;

        $order3Lat = 34.0248;
        $order3Lon = -118.28;

        $avgLat = 34.02975;
        $avgLon = -118.2875;

        $dl = new Crunchbutton_Order_Logistics_DriverLocation($cc);
        $dl->addAcceptedOrder($actionTimestamp20, $curTimestamp, $restaurant3Geo);
        $dl->addPickedUpOrder($actionTimestamp15, $curTimestamp, $restaurant2Geo, $order2Lat, $order2Lon);
        $dl->addDeliveredOrder($actionTimestamp5, $curTimestamp, $order1Lat, $order1Lon, $restaurant1Geo);
        $dl->determineDriverGeo($this->driver1, $now);

        $this->assertEquals(round($dl->lat, 5), $avgLat);
        $this->assertEquals(round($dl->lon, 4), $avgLon);

    }

    // One orders delivered 5 minutes ago
    //  One order accepted 20 minutes ago
    //  One order picked up 15 minutes ago
    //  Five Guys - 20 minutes ago
    //  Pizza Studio - 15 minutes ago
    //  Chipotle - 5 minutes ago
    //  Customers - near USC
    //  Should be traveling from the Chipotle customer to the Pizza Studio customer
    //   Change the order of adding the orders to the DriverLocation to see if that affects things.  It shouldn't.

    public function testDLWith1Delivered1Pickedup1Accept3()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $curTimestamp = $now->getTimestamp();

        $minutes = 5;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp5 = $earlierMin->getTimestamp();

        $minutes = 15;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp15 = $earlierMin->getTimestamp();

        $minutes = 20;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp20 = $earlierMin->getTimestamp();

        // Community center
        $communityLat = 34.023281;
        $communityLon = -118.2881961;

        $cc = new Crunchbutton_Order_Location($communityLat, $communityLon);

        // Chipotle
        $restaurant1Lat = 34.017;
        $restaurant1Lon = -118.282;
        $restaurant1Geo = new Crunchbutton_Order_Location($restaurant1Lat, $restaurant1Lon);

        // Pizza Studio
        $restaurant2Lat = 34.0187;
        $restaurant2Lon = -118.282;
        $restaurant2Geo = new Crunchbutton_Order_Location($restaurant2Lat, $restaurant2Lon);

        // Five Guys
        $restaurant3Lat = 34.0269;
        $restaurant3Lon = -118.276;
        $restaurant3Geo = new Crunchbutton_Order_Location($restaurant3Lat, $restaurant3Lon);

        // Customer location
        $order1Lat = 34.0311;
        $order1Lon = -118.288;

        $order2Lat = 34.0284;
        $order2Lon = -118.287;

        $order3Lat = 34.0248;
        $order3Lon = -118.28;

        $avgLat = 34.02975;
        $avgLon = -118.2875;

        $dl = new Crunchbutton_Order_Logistics_DriverLocation($cc);
        $dl->addDeliveredOrder($actionTimestamp5, $curTimestamp, $order1Lat, $order1Lon, $restaurant1Geo);
        $dl->addAcceptedOrder($actionTimestamp20, $curTimestamp, $restaurant3Geo);
        $dl->addPickedUpOrder($actionTimestamp15, $curTimestamp, $restaurant2Geo, $order2Lat, $order2Lon);
        $dl->determineDriverGeo($this->driver1, $now);

        $this->assertEquals(round($dl->lat, 5), $avgLat);
        $this->assertEquals(round($dl->lon, 4), $avgLon);

    }


    // Two orders picked up 30 and 5 minutes ago
    //  One order accepted 15 minutes ago.
    //  Five Guys - 30 minutes ago
    //  Pizza Studio - 15 minutes ago
    //  Chipotle - 5 minutes ago
    //  Customers - near USC
    //  Should be traveling from the Chipotle to Five Guys customer.

    public function testDLWith2Pickedup1AcceptedOrders()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $curTimestamp = $now->getTimestamp();

        $minutes = 5;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp5 = $earlierMin->getTimestamp();

        $minutes = 15;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp15 = $earlierMin->getTimestamp();

        $minutes = 30;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp30 = $earlierMin->getTimestamp();

        // Community center
        $communityLat = 34.023281;
        $communityLon = -118.2881961;

        $cc = new Crunchbutton_Order_Location($communityLat, $communityLon);

        // Chipotle
        $restaurant1Lat = 34.017;
        $restaurant1Lon = -118.282;
        $restaurant1Geo = new Crunchbutton_Order_Location($restaurant1Lat, $restaurant1Lon);

        // Pizza Studio
        $restaurant2Lat = 34.0187;
        $restaurant2Lon = -118.282;
        $restaurant2Geo = new Crunchbutton_Order_Location($restaurant2Lat, $restaurant2Lon);

        // Five Guys
        $restaurant3Lat = 34.0269;
        $restaurant3Lon = -118.276;
        $restaurant3Geo = new Crunchbutton_Order_Location($restaurant3Lat, $restaurant3Lon);

        // Customer location
        $order1Lat = 34.0311;
        $order1Lon = -118.288;

        $order2Lat = 34.0284;
        $order2Lon = -118.287;

        $order3Lat = 34.0248;
        $order3Lon = -118.28;

        $avgLat = 34.01895;
        $avgLon = -118.2815;

        $dl = new Crunchbutton_Order_Logistics_DriverLocation($cc);
        $dl->addAcceptedOrder($actionTimestamp15, $curTimestamp, $restaurant2Geo);
        $dl->addPickedUpOrder($actionTimestamp5, $curTimestamp, $restaurant1Geo, $order1Lat, $order1Lon);
        $dl->addPickedUpOrder($actionTimestamp30, $curTimestamp, $restaurant3Geo, $order3Lat, $order3Lon);
        $dl->determineDriverGeo($this->driver1, $now);

        $this->assertEquals(round($dl->lat, 5), $avgLat);
        $this->assertEquals(round($dl->lon, 4), $avgLon);

    }

    // Two orders picked up 30 and 5 minutes ago
    //  One order accepted 15 minutes ago.
    //  Five Guys - 30 minutes ago
    //  Pizza Studio - 15 minutes ago
    //  Chipotle - 5 minutes ago
    //  Customers - near USC
    //  Should be traveling from the Chipotle to Five Guys customer.
    //   Change the order of adding the orders to the DriverLocation to see if that affects things.  It shouldn't.
    public function testDLWith2Pickedup1AcceptedOrders2()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $curTimestamp = $now->getTimestamp();

        $minutes = 5;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp5 = $earlierMin->getTimestamp();

        $minutes = 15;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp15 = $earlierMin->getTimestamp();

        $minutes = 30;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp30 = $earlierMin->getTimestamp();

        // Community center
        $communityLat = 34.023281;
        $communityLon = -118.2881961;

        $cc = new Crunchbutton_Order_Location($communityLat, $communityLon);

        // Chipotle
        $restaurant1Lat = 34.017;
        $restaurant1Lon = -118.282;
        $restaurant1Geo = new Crunchbutton_Order_Location($restaurant1Lat, $restaurant1Lon);

        // Pizza Studio
        $restaurant2Lat = 34.0187;
        $restaurant2Lon = -118.282;
        $restaurant2Geo = new Crunchbutton_Order_Location($restaurant2Lat, $restaurant2Lon);

        // Five Guys
        $restaurant3Lat = 34.0269;
        $restaurant3Lon = -118.276;
        $restaurant3Geo = new Crunchbutton_Order_Location($restaurant3Lat, $restaurant3Lon);

        // Customer location
        $order1Lat = 34.0311;
        $order1Lon = -118.288;

        $order2Lat = 34.0284;
        $order2Lon = -118.287;

        $order3Lat = 34.0248;
        $order3Lon = -118.28;

        $avgLat = 34.01895;
        $avgLon = -118.2815;

        $dl = new Crunchbutton_Order_Logistics_DriverLocation($cc);
        $dl->addPickedUpOrder($actionTimestamp30, $curTimestamp, $restaurant3Geo, $order3Lat, $order3Lon);
        $dl->addPickedUpOrder($actionTimestamp5, $curTimestamp, $restaurant1Geo, $order1Lat, $order1Lon);
        $dl->addAcceptedOrder($actionTimestamp15, $curTimestamp, $restaurant2Geo);
        $dl->determineDriverGeo($this->driver1, $now);

        $this->assertEquals(round($dl->lat, 5), $avgLat);
        $this->assertEquals(round($dl->lon, 4), $avgLon);

    }


    // Two orders picked up 50 and 30 minutes ago
    //  One order accepted 15 minutes ago.
    //  Five Guys - 30 minutes ago
    //  Pizza Studio - 15 minutes ago
    //  Chipotle - 5 minutes ago
    //  Customers - near USC
    //  Should be traveling from the Chipotle to Five Guys customer.  90% effect should come into play here.

    public function testDLWith2Pickedup1AcceptedOrders3()
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $curTimestamp = $now->getTimestamp();

        $minutes = 30;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp30 = $earlierMin->getTimestamp();

        $minutes = 15;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp15 = $earlierMin->getTimestamp();

        $minutes = 50;
        $earlierMin = clone $now;
        $earlierMin->modify('- ' . $minutes . ' minutes');
        $actionTimestamp50 = $earlierMin->getTimestamp();

        // Community center
        $communityLat = 34.023281;
        $communityLon = -118.2881961;

        $cc = new Crunchbutton_Order_Location($communityLat, $communityLon);

        // Chipotle
        $restaurant1Lat = 34.017;
        $restaurant1Lon = -118.282;
        $restaurant1Geo = new Crunchbutton_Order_Location($restaurant1Lat, $restaurant1Lon);

        // Pizza Studio
        $restaurant2Lat = 34.0187;
        $restaurant2Lon = -118.282;
        $restaurant2Geo = new Crunchbutton_Order_Location($restaurant2Lat, $restaurant2Lon);

        // Five Guys
        $restaurant3Lat = 34.0269;
        $restaurant3Lon = -118.276;
        $restaurant3Geo = new Crunchbutton_Order_Location($restaurant3Lat, $restaurant3Lon);

        // Customer location
        $order1Lat = 34.0311;
        $order1Lon = -118.288;

        $order2Lat = 34.0284;
        $order2Lon = -118.287;

        $order3Lat = 34.0248;
        $order3Lon = -118.28;

        $avgLat = 34.02402;
        $avgLon = -118.2802;

        $dl = new Crunchbutton_Order_Logistics_DriverLocation($cc);
        $dl->addAcceptedOrder($actionTimestamp15, $curTimestamp, $restaurant2Geo);
        $dl->addPickedUpOrder($actionTimestamp30, $curTimestamp, $restaurant1Geo, $order1Lat, $order1Lon);
        $dl->addPickedUpOrder($actionTimestamp50, $curTimestamp, $restaurant3Geo, $order3Lat, $order3Lon);
        $dl->determineDriverGeo($this->driver1, $now);

        $this->assertEquals(round($dl->lat, 5), $avgLat);
        $this->assertEquals(round($dl->lon, 4), $avgLon);

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

    // Locations are created every second from 0 to $window
    public function createAndSaveAdminLocations($id_admin, $lat, $lon, $dt, $window, $timediff=1) {

        $locs = [];
        for ($i = 0; $i < $window; $i++) {
            $dt->modify('- ' . $timediff . ' seconds');
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

    // Locations are created every second from 0 to $window
    public function createAndSaveAdminLocationsFromArrays($id_admin, $lats, $lons, $tss) {

        $locs = [];

        $dt = new DateTime();
        $dt->setTimezone(new DateTimeZone(c::config()->timezone));
        for ($i = 0; $i < count($lats); $i++) {
            $dt->setTimestamp($tss[$i]);

            $date = $dt->format('Y-m-d H:i:s');

            $loc =  new Cockpit_Admin_Location([
                'id_admin' => $id_admin,
                'lat' => $lats[$i],
                'lon' => $lons[$i],
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


}
