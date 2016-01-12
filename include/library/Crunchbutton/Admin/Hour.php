<?php

class Crunchbutton_Admin_Hour extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('admin_hour')
			->idVar('id_admin_hour')
			->load($id);
	}

	public function removeByDateIdAdmin( $date, $id_admin ){
		die ('#5584 deprecated');
		return c::dbWrite()->query( "DELETE from admin_hour WHERE id_admin = $id_admin AND DATE_FORMAT( date_start, '%Y-%m-%d' ) = '$date'" );
	}

	public function hoursByDateRestaurant( $id_restaurant, $date ){
		die ('#5584 deprecated');
		$hours = Crunchbutton_Admin_Hour::q( "SELECT DISTINCT( a.id_admin_hour ) id, a.*,
																							DATE_FORMAT( date_start, '%H' ) start_hour,
																							DATE_FORMAT( date_start, '%d' ) start_day,
																							DATE_FORMAT( date_end, '%H' ) end_hour,
																							DATE_FORMAT( date_end, '%d' ) end_day
																					FROM admin_hour a
																						INNER JOIN notification n ON a.id_admin = n.id_admin AND id_restaurant = {$id_restaurant}
																						WHERE ( DATE_FORMAT( date_start, '%Y-%m-%d' ) = '{$date}' || DATE_FORMAT( date_end, '%Y-%m-%d' ) = '{$date}' )" );
		$hasDriver = [];

		$day = intval( explode( '-', $date )[ 2 ] );
		foreach( $hours as $hour ){
			$start_hour = intval( $hour->start_hour );
			$start_day = intval( $hour->start_day );
			$end_hour = intval( $hour->end_hour );
			$end_day = intval( $hour->end_day );

			if( $end_day > $day ){
				$end_hour = 23;
			}
			if( $end_day == $day && $start_day < $day ){
				$start_hour = 0;
			}
			for( $i = $start_hour; $i <= $end_hour; $i++ ){
				$hasDriver[ $i ] = true;
			}
		}
		return $hasDriver;
	}

	public function segmentsByDate( $date, $_join = ', ', $id_admin = false ){
		die ('#5430 deprecated');
		/*
		$where = ( $id_admin ) ? ' AND id_admin = ' . $id_admin : '';
		$hours = Crunchbutton_Admin_Hour::q( "SELECT * FROM admin_hour WHERE id_admin IS NOT NULL AND DATE_FORMAT( date_start, '%Y-%m-%d' ) = '$date' $where ORDER BY date_start, id_admin ASC" );
		$admins = [];
		foreach ( $hours as $hour ) {
			$join = '';
			if( !$admins[ $hour->id_admin ] ){
				$admins[ $hour->id_admin ] = [ 'name' => $hour->admin()->name, 'login' => $hour->admin()->login, 'hours' => '', 'id_admin' => $hour->id_admin ];
			} else {
				$join = $_join;
			}
			$admins[ $hour->id_admin ][ 'hours' ] = $admins[ $hour->id_admin ][ 'hours' ] . $join . $hour->segment();
			$admins[ $hour->id_admin ][ 'pst' ] = $admins[ $hour->id_admin ][ 'pst' ] . $join . $hour->segment( 'PST' );
		}
		return $admins;
		*/
	}

	public function formatedToSegment( $dateTime ){
		if( $dateTime ){
			$hour = $dateTime->format( 'h' );
			if( $hour > 12 ){
				$hour = $hour - 12;
			}
			$min = $dateTime->format( 'i' );
			$ampm = $dateTime->format( 'a' );
			return intval( $hour ) . ( intval( $min ) > 0 ? ':' . intval( $min ) : '' ) . ' ' . $ampm ;
		}
	}

	public function segment( $timezone = false ){
		if( $timezone ){
			$date_start = DateTime::createFromFormat( 'Y-m-d H:i:s', $this->date_start, new DateTimeZone( $this->admin()->timezone ) );
			$date_start->setTimezone( new DateTimeZone( $timezone ) );
			$date_end = DateTime::createFromFormat( 'Y-m-d H:i:s', $this->date_end, new DateTimeZone( $this->admin()->timezone ) );
			$date_end->setTimezone( new DateTimeZone( $timezone ) );
		} else {
			$date_start = $this->date_start();
			$date_end = $this->date_end();
		}
		return $this->formatedToSegment( $date_start ) . ' - ' . $this->formatedToSegment( $date_end );
	}

	public function date_start(){
		if ( !isset( $this->_date_start ) ) {
			$this->_date_start = DateTime::createFromFormat( 'Y-m-d H:i:s', $this->date_start, new DateTimeZone( $this->admin()->timezone ) );
		}
		return $this->_date_start;
	}

	public function date_end(){
		if ( !isset( $this->_date_end ) ) {
			$this->_date_end = DateTime::createFromFormat( 'Y-m-d H:i:s', $this->date_end, new DateTimeZone( $this->admin()->timezone ) );
		}
		return $this->_date_end;
	}

	public function validateTimezone( $timezone ){
		if ( in_array( $timezone, DateTimeZone::listIdentifiers() ) ) {
			return true;
		}
		return false;
	}

	public function admin(){
		if ( !isset( $this->_admin ) ) {
			$this->_admin = Admin::o( $this->id_admin );
		}
		return $this->_admin;
	}

	public function segmentToDate( $date_base, $segment, $timezone = false ){
		$matches = [];
		preg_match( '/^ *(\d+)(?:\:(\d+))? *(am|pm) *(?:to|-) *(\d+)(?:\:(\d+))? *(am|pm) *$/i', $segment, $matches );
		$start_hour = $matches[ 1 ];
		$start_min = $matches[ 2 ];
		$start_ampm = strtolower( $matches[ 3 ] );
		$end_hour = $matches[ 4 ];
		$end_min = $matches[ 5 ];
		$end_ampm = strtolower( $matches[ 6 ] );
		$end_day = 0;
		if( $start_ampm == 'pm' && $start_hour < 12 ){
			$start_hour = $start_hour + 12;
		}
		if( $end_ampm == 'pm' && $end_hour == 12 ){
			$end_hour = 24;
		}
		if( $end_ampm == 'pm' && $end_hour < 12 ){
			$end_hour = $end_hour + 12;
		}
		if( $end_ampm == 'am' && $end_hour == 12 ){
			$end_hour = 24;
		}
		if( $end_ampm == 'am' && $end_hour < $start_hour ){
			$end_hour = $end_hour + 24;
		}
		if( $end_hour >= 24 ){
			$end_hour = $end_hour - 24;
			$end_day = 1;
		}

		$start_at = $start_hour . ':' . ( ( $start_min == '' ) ? '00' : $start_min );
		$date_start = DateTime::createFromFormat( 'Y-m-d H:i:s', $date_base->format( 'Y-m-d' ) . ' ' . $start_at . ':00', new DateTimeZone( $date_base->format( 'e' ) ) );
		$end_at = $end_hour . ':' . ( ( $end_min == '' ) ? '00' : $end_min );
		$date_end = DateTime::createFromFormat( 'Y-m-d H:i:s', $date_base->format( 'Y-m-d' ) . ' ' . $end_at. ':00', new DateTimeZone( $date_base->format( 'e' ) ) );

		if( $end_day ){
			$date_end->modify( '+' . $end_day . ' day' );
		}

		if( $timezone ){
			$date_start->setTimezone( new DateTimeZone( $timezone ) );
			$date_end->setTimezone( new DateTimeZone( $timezone ) );
		}

		if( $date_start->format( 'Y-m-d H:i:s' ) && $date_end->format( 'Y-m-d H:i:s' ) ){
			return [ 'start' => $date_start->format( 'Y-m-d H:i:s' ), 'end' => $date_end->format( 'Y-m-d H:i:s' ) ];
		}
		return false;
	}

}
