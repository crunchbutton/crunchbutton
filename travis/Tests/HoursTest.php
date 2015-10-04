<?php

class HoursTest extends PHPUnit_Framework_TestCase {

	// because settlement filters stuff with 'test' on its name
	const NAME = 'HOURS Travis';

	public static function setUpBeforeClass() {

		$name = self::NAME;
		// restaurant stuff
		$r1 = new Restaurant([ 'name' => $name . ' 3rd Party Delivery', 'formal_relationship' => false, 'active' => true, 'delivery' => true, 'credit' => 1, 'delivery_fee' => '1.5', 'confirmation' => 0, 'community' => 'test', 'timezone' => 'America/Los_Angeles', 'open_for_business' => true, 'delivery_service' => false ] );
		$r1->save();
		$r2 = new Restaurant([ 'name' => $name . ' Delivery Service', 'formal_relationship' => false, 'active' => true, 'delivery' => true, 'credit' => 1, 'delivery_fee' => '1.5', 'confirmation' => 0, 'community' => 'test', 'timezone' => 'America/Los_Angeles', 'open_for_business' => true, 'delivery_service' => false ] );
		$r2->save();
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

	public static function tearDownAfterClass() {
		$name = self::NAME;
		Restaurant::q( 'SELECT * FROM restaurant WHERE name = ?', [$name.' 3rd Party Delivery'] )->delete();
		Restaurant::q( 'SELECT * FROM restaurant WHERE name = ?', [$name.' Delivery Service'] )->delete();
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
		$now->modify( '+ 1 hour' );
		$close = $now->format( 'H:i' );
		$now->modify( '- 2 hour' );
		$open = $now->format( 'H:i' );

		$this->assertEquals( $this->deliveryServiceRestaurant()->open(), false );

		$hour = new Hour([
			'id_restaurant' => $this->deliveryServiceRestaurant()->id_restaurant,
			'day' => $today,
			'time_open' => $open,
			'time_close' => $close
		]);
		$hour->save();
		$this->assertEquals( $this->deliveryServiceRestaurant()->open(), true );

		$this->assertEquals( $this->thirdParyDeliveryRestaurant()->open(), false );
		$hour = new Hour([
			'id_restaurant' => $this->thirdParyDeliveryRestaurant()->id_restaurant,
			'day' => $today,
			'time_open' => $open,
			'time_close' => $close
		]);
		$hour->save();
		$this->assertEquals( $this->thirdParyDeliveryRestaurant()->open(), true );
	}
}