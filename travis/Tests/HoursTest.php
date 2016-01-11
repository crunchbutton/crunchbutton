<?php

class HoursTest extends PHPUnit_Framework_TestCase {

	// because settlement filters stuff with 'test' on its name
	const NAME = 'HOURS Travis';
	const SHIFTS_CREATED = 2;

	public static function setUpBeforeClass() {

		$name = self::NAME;

		// restaurant stuff
		$r1 = new Restaurant([ 'name' => $name . ' 3rd Party Delivery', 'formal_relationship' => false, 'active' => true, 'delivery' => true, 'credit' => 1, 'delivery_fee' => '1.5', 'confirmation' => 0, 'community' => 'test', 'timezone' => 'America/Los_Angeles', 'open_for_business' => true, 'delivery_service' => false ] );
		$r1->save();
		$r2 = new Restaurant([ 'name' => $name . ' Delivery Service', 'formal_relationship' => false, 'active' => true, 'delivery' => true, 'credit' => 1, 'delivery_fee' => '1.5', 'confirmation' => 0, 'community' => 'test', 'timezone' => 'America/Los_Angeles', 'open_for_business' => true, 'delivery_service' => true ] );
		$r2->save();

		// community
		$c1 = new Community(['name' => $name, 'active' => 1, 'timezone' => 'America/New_York', 'driver-group' => 'drivers-testlogistics', 'range' => 2, 'private' => 1, 'loc_lat' => 34.023281, 'loc_lon' => -118.2881961, 'delivery_logistics' => null, 'auto_close' => true, 'combine_restaurant_driver_hours' => false ]);
		$c1->save();

		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		$now->setTimezone( new DateTimeZone( $c1->timezone ) );
		$today = strtolower( $now->format( 'D' ) );
		$now->modify( '- 1 hour' );
		$date_start = $now->format( 'Y-m-d H:i' );
		$now->modify( '+30 minutes' );
		$date_end = $now->format( 'Y-m-d H:i' );
		$cs1 = new Community_Shift([ 'id_community' => $c1->id_community, 'date_start' => $date_start, 'date_end' => $date_end, 'active' => 1 ]);
		$cs1->save();
		$date_start = $now->format( 'Y-m-d H:i' );
		$now->modify( '+2 hour' );
		$date_end = $now->format( 'Y-m-d H:i' );
		$cs2 = new Community_Shift([ 'id_community' => $c1->id_community, 'date_start' => $date_start, 'date_end' => $date_end, 'active' => 1 ]);
		$cs2->save();

		// driver
		$d = new Admin( [ 'name' => $name, 'login' => null, 'active' => 1 ] );
		$d->save();

	}

	public function shifts(){
		if( !$this->_shifts ){
			$name = self::NAME;
			$this->_shifts = Community_Shift::q( 'SELECT * FROM community_shift INNER JOIN community ON community.id_community = community_shift.id_community WHERE community.name = ?', [ $name ] );
		}
		return $this->_shifts;
	}

	public function deliveryServiceRestaurant(){
		if( !$this->_deliveryServiceRestaurant ){
			$name = self::NAME;
			$this->_deliveryServiceRestaurant = Restaurant::q( 'SELECT * FROM restaurant WHERE name = ? ORDER BY id_restaurant DESC LIMIT 1', [$name.' Delivery Service'])->get( 0 );
		}
		return $this->_deliveryServiceRestaurant;
	}

	public function thirdParyDeliveryRestaurant(){
		if( !$this->_thirdParyDeliveryRestaurant ){
			$name = self::NAME;
			$this->_thirdParyDeliveryRestaurant = Restaurant::q( 'SELECT * FROM restaurant WHERE name = ? ORDER BY id_restaurant DESC LIMIT 1', [$name.' 3rd Party Delivery'])->get( 0 );
		}
		return $this->_thirdParyDeliveryRestaurant;
	}

	public function driver(){
		if( !$this->_driver ){
			$name = self::NAME;
			$this->_driver = Admin::q( 'SELECT * FROM admin WHERE name = ? ORDER BY id_admin DESC LIMIT 1', [$name])->get( 0 );
		}
		return $this->_driver;
	}

	public function community(){
		if( !$this->_community ){
			$name = self::NAME;
			$this->_community = Community::q( 'SELECT * FROM community WHERE name = ? ORDER BY id_community DESC LIMIT 1', [$name])->get( 0 );
		}
		return $this->_community;
	}

	public static function tearDownAfterClass() {

		$name = self::NAME;

		// delete shift assignment
		c::dbWrite()->query( 'DELETE admin_shift_assign.* FROM admin_shift_assign INNER JOIN community_shift ON community_shift.id_community_shift = admin_shift_assign.id_community_shift INNER JOIN community ON community.id_community = community_shift.id_community WHERE community.name = ?', [ $name ] );

		// delete shift
		c::dbWrite()->query( 'DELETE community_shift.* FROM community_shift INNER JOIN community ON community.id_community = community_shift.id_community WHERE community.name = ?', [ $name ] );

		// delete driver
		c::dbWrite()->query( 'DELETE FROM admin WHERE admin.name = ?', [ $name ] );

		// delete restaurants
		c::dbWrite()->query( 'DELETE FROM restaurant WHERE restaurant.name = ?', [$name.' 3rd Party Delivery'] );
		c::dbWrite()->query( 'DELETE FROM restaurant WHERE restaurant.name = ?', [$name.' Delivery Service'] );

		// delete community
		c::dbWrite()->query( 'DELETE FROM community WHERE community.name = ?', [$name] );

	}

	public function setUp() {

	}

	public function testRestaurantsWereCreated(){
		$this->assertEquals( is_numeric( $this->thirdParyDeliveryRestaurant()->id_restaurant ), true );
		$this->assertEquals( is_numeric( $this->deliveryServiceRestaurant()->id_restaurant ), true );
	}

	public function testRestaurantIsOpenBasic() {

		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		$today = strtolower( $now->format( 'D' ) );
		$now->modify( '- 1 hour' );
		$open = $now->format( 'H:i' );
		$now->modify( '+ 2 hour' );
		$close = $now->format( 'H:i' );

		$this->assertEquals( $this->deliveryServiceRestaurant()->open(), false );

		$hour = new Hour([
			'id_restaurant' => $this->deliveryServiceRestaurant()->id_restaurant,
			'day' => $today,
			'time_open' => $open,
			'time_close' => $close
		]);

		$hour->save();
		// $this->assertEquals( $this->deliveryServiceRestaurant()->open(), true );

		$this->assertEquals( $this->thirdParyDeliveryRestaurant()->open(), false );
		$hour = new Hour([
			'id_restaurant' => $this->thirdParyDeliveryRestaurant()->id_restaurant,
			'day' => $today,
			'time_open' => $open,
			'time_close' => $close
		]);
		$hour->save();
		// $this->assertEquals( $this->thirdParyDeliveryRestaurant()->open(), true );
	}

	public function testShiftCreating(){
		$shifts = $this->shifts();
		$this->assertEquals( $shifts->count(), self::SHIFTS_CREATED );
	}

	public static function fakeNotCli(){
		$_SERVER['ACT_AS_NOT_IS_CLI'] = true;
	}

	public static function unfakeNotCli(){
		$_SERVER['ACT_AS_NOT_IS_CLI'] = false;
	}

	public static function fakeIsCockpit(){
		$_SERVER['HTTP_HOST'] = 'cockpit.la';
	}

	public static function unfakeIsCockpit(){
		$_SERVER['HTTP_HOST'] = '';
	}

	public function testRelateRestaurantCommunity(){
		// relate
		$this->deliveryServiceRestaurant()->saveCommunity( $this->community()->id_community );
		$this->thirdParyDeliveryRestaurant()->saveCommunity( $this->community()->id_community );

		$id_community_1 = $this->deliveryServiceRestaurant()->community()->id_community;
		$id_community_2 = $this->thirdParyDeliveryRestaurant()->community()->id_community;

		$this->assertEquals( $id_community_1, $this->community()->id_community );
		$this->assertEquals( $id_community_2, $this->community()->id_community );
	}

	public function testRestaurantsStatusAfterRelatedWithCommunity() {
		// it should be closed because it now has a community related but the community has no drivers
		// $this->assertEquals( $this->deliveryServiceRestaurant()->open(), false );
		// $this->assertEquals( $this->thirdParyDeliveryRestaurant()->open(), true );
	}
/*
	public function testAutoCloseCommunity(){

		self::fakeIsCockpit();
		self::fakeNotCli();

		$community = $this->community();
		$community->shutDownCommunity();

		$this->assertEquals( $community->isAutoClosed(), true );

		self::unfakeIsCockpit();
		self::unfakeNotCli();

	}

	public function testRestaurantsIsClosedAfterCloseCommunity() {
		$this->assertEquals( $this->deliveryServiceRestaurant()->open(), false );
		$this->assertEquals( $this->thirdParyDeliveryRestaurant()->open(), true );
	}
*/
	public function testShiftAssignment(){

		$shift_assigned = 0;
		$driver = $this->driver();

		foreach( $this->shifts() as $shift ){
			$assignment = new Crunchbutton_Admin_Shift_Assign();
			$assignment->id_admin = $this->driver()->id_admin;
			$assignment->id_community_shift = $shift->id_community_shift;
			$assignment->confirmed = true;
			$assignment->date = date('Y-m-d H:i:s');
			$assignment->save();
			$shift_assigned++;
			$this->assertEquals( is_numeric( $assignment->id_admin_shift_assign ), true );
		}
		$this->assertEquals( $shift_assigned, self::SHIFTS_CREATED );
	}
/*
	public function testReopenAutoClosedCommunity(){

		self::fakeIsCockpit();
		self::fakeNotCli();

		$community = $this->community();
		$community->reopenAutoClosedCommunity();
		$this->assertEquals( $community->isAutoClosed(), false );

		self::unfakeIsCockpit();
		self::unfakeNotCli();
	}

	public function testRestaurantsAreOpenAfterReopenCommunity() {
		continue here!
	}
*/
}
