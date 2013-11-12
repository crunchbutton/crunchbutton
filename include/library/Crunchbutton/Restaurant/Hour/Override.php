<?php

class Crunchbutton_Restaurant_Hour_Override extends Cana_Table {

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('restaurant_hour_override')
			->idVar('id_restaurant_hour_override')
			->load($id);
	}

	public function restaurant(){
		return Restaurant::o( $this->id_restaurant );
	}

	public function status(){
		if ( !isset( $this->_status ) ) {
			$this->_status = ( $this->type == 'close' ) ? 'Closed' : 'Opened';
		}
		return $this->_status;
	}

	public function admin(){
		return Admin::o( $this->id_admin );
	}

	function date_start(){
		if ( !isset( $this->_date_start ) ) {
			$this->_date_start = new DateTime( $this->date_start, new DateTimeZone( c::config()->timezone ) );
			$this->_date_start->setTimezone( new DateTimeZone( $this->restaurant()->timezone ) );
		}
		return $this->_date_start;
	}

	function date_end(){
		if ( !isset( $this->_date_end ) ) {
			$this->_date_end = new DateTime( $this->date_end, new DateTimeZone( c::config()->timezone ) );
			$this->_date_end->setTimezone( new DateTimeZone( $this->restaurant()->timezone ) );
		}
		return $this->_date_end;
	}

}