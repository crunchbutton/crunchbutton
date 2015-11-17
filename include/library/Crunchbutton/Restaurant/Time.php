<?php

class Crunchbutton_Restaurant_Time extends Cana_Table {

	const MAX_AGE = 300; // 5 minutes

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('restaurant_time')
			->idVar('id_restaurant_time')
			->load($id);
	}

	public static function getTime( $id_restaurant ){
		$time = Crunchbutton_Restaurant_Time::q( 'SELECT * FROM restaurant_time WHERE id_restaurant = ? ORDER BY id_restaurant_time DESC LIMIT 1', [ $id_restaurant ] )->get( 0 );
		if( $time->id_restaurant_time ){
			$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
			$diff = $now->diff( $time->date() );
			if( Crunchbutton_Util::intervalToSeconds( $diff ) < self::MAX_AGE ){
				return self::parseTime( $time );
			}
		}
		$time = Crunchbutton_Restaurant_Time::register( $id_restaurant );
		return self::parseTime( $time );
	}

	public static function parseTime( $time ){
		$time = $time->properties();
		$time[ 'next_open_time_message' ] = $time[ 'next_open_time_message' ] ? json_decode( $time[ 'next_open_time_message' ] ) : null;
		$time[ 'next_open_time_message_utc' ] = $time[ 'next_open_time_message_utc' ] ? json_decode( $time[ 'next_open_time_message_utc' ] ) : null;
		$time[ 'hours_next_24_hours' ] = $time[ 'hours_next_24_hours' ] ? json_decode( $time[ 'hours_next_24_hours' ] ) : null;
		return $time;
	}

	public function date(){
		if( !isset( $this->_datetime ) ){
			$this->_datetime = new DateTime( $this->datetime, new DateTimeZone( c::config()->timezone ) );
		}
		return $this->_datetime;
	}

	public static function register( $id_restaurant ){

		$restaurant = Restaurant::o( $id_restaurant );

		if( $restaurant->id_restaurant ){

			$timezone = new DateTimeZone( $restaurant->timezone );
			$date = new DateTime( 'now ', $timezone ) ;

			$time = new Crunchbutton_Restaurant_Time;
			$time->id_restaurant = $restaurant->id_restaurant;
			$time->datetime = date( 'Y-m-d H:i:s' );
			$time->open = $restaurant->open();

			$next_open_time = $restaurant->next_open_time();
			$time->next_open_time = ( $next_open_time ) ? $next_open_time->format( 'Y-m-d H:i' ) : null;

			$next_open_time_utc = $restaurant->next_open_time( true );
			$time->next_open_time_utc = ( $next_open_time_utc ) ? $next_open_time_utc->format( 'Y-m-d H:i' ) : null;

			$time->tzoffset = ( $date->getOffset() ) / 60 / 60;
			$time->tzabbr = $date->format('T');

			if( $time->next_open_time ){
				$time->next_open_time_message = json_encode( $restaurant->next_open_time_message() );
			}

			if( $time->next_open_time_utc ){
				$time->next_open_time_message_utc = json_encode( $restaurant->next_open_time_message( true ) );
			}

			$time->closed_message = $restaurant->closed_message();
			$time->hours_next_24_hours = json_encode( $restaurant->hours_next_24_hours( true ) );

			$time->save();

			return Crunchbutton_Restaurant_Time::o( $time->id_restaurant_time );

		}
	}

	public function store(){

		$restaurants = c::db()->query( 'SELECT DISTINCT( restaurant.id_restaurant ) id_restaurant FROM restaurant INNER JOIN hour ON hour.id_restaurant = restaurant.id_restaurant WHERE restaurant.active = true AND restaurant.open_for_business = true' );

		while ( $restaurant = $restaurants->fetch() ) {
			// self::register( $restaurant->id_restaurant );
		}
	}

}