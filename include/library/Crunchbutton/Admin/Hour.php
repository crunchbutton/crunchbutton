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
		return c::db()->query( "DELETE from admin_hour WHERE id_admin = $id_admin AND DATE_FORMAT( date_start, '%Y-%m-%d' ) = '$date'" );
	}

	public function segmentsByDate( $date, $_join = ', ', $id_admin = false ){
		$where = ( $id_admin ) ? ' AND id_admin = ' . $id_admin : '';
		$hours = Crunchbutton_Admin_Hour::q( "SELECT * FROM admin_hour WHERE DATE_FORMAT( date_start, '%Y-%m-%d' ) = '$date' $where ORDER BY id_admin, date_start ASC" );
		$admins = [];
		foreach ( $hours as $hour ) {
			$join = '';
			if( !$admins[ $hour->id_admin ] ){
				$admins[ $hour->id_admin ] = [ 'name' => $hour->admin()->name, 'login' => $hour->admin()->login, 'hours' => '', 'id_admin' => $hour->id_admin ];
			} else {	
				$join = $_join;
			}
			$admins[ $hour->id_admin ][ 'hours' ] = $admins[ $hour->id_admin ][ 'hours' ] . $join . $hour->segment();
		}
		return $admins;
	}

	public function formatedToSegment( $dateTime ){
		$hour = $dateTime->format( 'h' );
		if( $hour > 12 ){
			$hour = $hour - 12;
		}
		$min = $dateTime->format( 'i' );
		$ampm = $dateTime->format( 'a' );
		return intval( $hour ) . ( intval( $min ) > 0 ? ':' . intval( $min ) : '' ) . ' ' . $ampm ;
	}

	public function segment(){
		return $this->formatedToSegment( $this->date_start() ) . ' - ' . $this->formatedToSegment( $this->date_end() );
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

	public function admin(){
		if ( !isset( $this->_admin ) ) {
			$this->_admin = Admin::o( $this->id_admin );
		}
		return $this->_admin;
	}

	public function segmentToDate( $date_base, $segment ){
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
		if( $date_start->format( 'Y-m-d H:i:s' ) && $date_end->format( 'Y-m-d H:i:s' ) ){
			return [ 'start' => $date_start->format( 'Y-m-d H:i:s' ), 'end' => $date_end->format( 'Y-m-d H:i:s' ) ];
		}
		return false;
	}

}