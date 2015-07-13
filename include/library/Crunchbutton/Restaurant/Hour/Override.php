<?php

class Crunchbutton_Restaurant_Hour_Override extends Cana_Table {

	const TYPE_CLOSED = 'close';
	const TYPE_OPENED = 'open';

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('restaurant_hour_override')
			->idVar('id_restaurant_hour_override')
			->load($id);
	}

	public static function getNexts(){
		$hasPermissionToAllRestaurants = c::admin()->permission()->check( [ 'global', 'restaurants-all', 'restaurants-crud' ] );
		$where = '';
		if( !$hasPermissionToAllRestaurants ){
			$id_restaurants = Restaurant::restaurantsUserHasPermission();
			$where = 'AND ho.id_restaurant IN(' . join( $id_restaurants, ',' ) . ')';
		}
		$today = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
		$today_mysql = $today->format( 'Y-m-d' );

		$today->modify( '+30 days' );
		$month_mysql = $today->format( 'Y-m-d' );

		$query = "SELECT r.name, ho.* FROM restaurant_hour_override ho
								INNER JOIN restaurant r ON r.id_restaurant = ho.id_restaurant
								WHERE
									date_start >= '{$today_mysql} 00:00:00'
									AND
									date_start <= '{$month_mysql} 23:59:59'
									{$where}
								ORDER BY r.name ASC, ho.date_start ASC";
		return Crunchbutton_Restaurant_Hour_Override::q( $query );
	}

	public function forceClose( $id_restaurant ){
		$restaurant = Restaurant::o( $id_restaurant );
		$today = new DateTime( 'now', new DateTimeZone( $restaurant->timezone ) );
		$today_mysql = $today->format('Y-m-d H:i');
		$type_closed = Crunchbutton_Restaurant_Hour_Override::TYPE_CLOSED;
		$overrides = Crunchbutton_Restaurant_Hour_Override::q( "SELECT * FROM restaurant_hour_override WHERE date_start <= '{$today_mysql}' AND date_end >= '{$today_mysql}' AND type = '{$type_closed}' AND id_restaurant = {$id_restaurant} LIMIT 1" );
		if( $overrides->count() > 0 ){
			return $overrides->notes;
		}
		return false;
	}

	public function forceOpen( $id_restaurant ){
		$restaurant = Restaurant::o( $id_restaurant );
		$today = new DateTime( 'now', new DateTimeZone( $restaurant->timezone ) );
		$today_mysql = $today->format('Y-m-d H:i');
		$type_opened = Crunchbutton_Restaurant_Hour_Override::TYPE_OPENED;
		$overrides = Crunchbutton_Restaurant_Hour_Override::q( "SELECT * FROM restaurant_hour_override WHERE date_start <= '{$today_mysql}' and date_end >= '{$today_mysql}' AND type = '{$type_opened}'  AND id_restaurant = {$id_restaurant} LIMIT 1" );
		if( $overrides->count() > 0 ){
			return true;
		}
		return false;
	}
/*
i dont think this is used anywhere
	public static function restaurantIsOpen( $id_restaurant ){
		$restaurant = Restaurant::o( $id_restaurant );
		$today = new DateTime( 'now', new DateTimeZone( $restaurant->timezone ) );
		$today_mysql = $today->format('Y-m-d H:i');
		$overrides = Crunchbutton_Restaurant_Hour_Override::q( "SELECT * FROM restaurant_hour_override WHERE date_start <= '{$today_mysql}' and date_end >= '{$today_mysql}' AND id_restaurant = {$id_restaurant} " );
		if( $overrides->count() > 0 ){
			foreach( $overrides as $override ){
				if( $override->type == Crunchbutton_Restaurant_Hour_Override::TYPE_CLOSED ){
					return false;
				}
				if( $override->type == Crunchbutton_Restaurant_Hour_Override::TYPE_OPENED ){
					return true;
				}
			}
		}
		return true;
	}
	*/

	public function restaurant(){
		return Restaurant::o( $this->id_restaurant );
	}

	public function status(){
		if ( !isset( $this->_status ) ) {
			$this->_status = ( $this->type == Crunchbutton_Restaurant_Hour_Override::TYPE_CLOSED ) ? 'Closed' : 'Opened';
		}
		return $this->_status;
	}

	public function admin(){
		return Admin::o( $this->id_admin );
	}

	function date_start(){
		if ( !isset( $this->_date_start ) ) {
			$this->_date_start = new DateTime( $this->date_start, new DateTimeZone( $this->restaurant()->timezone ) );
		}
		return $this->_date_start;
	}

	function date_end(){
		if ( !isset( $this->_date_end ) ) {
			$this->_date_end = new DateTime( $this->date_end, new DateTimeZone( $this->restaurant()->timezone ) );
		}
		return $this->_date_end;
	}

}