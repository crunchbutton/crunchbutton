<?php

class Cockpit_Driver_Info extends Cana_Table {

	const PHONE_TYPE_IPHONE = 'iPhone';
	const PHONE_TYPE_ANDROID = 'Android';
	const PHONE_TYPE_BLACKBERRY = 'Blackberry';
	const PHONE_TYPE_DUMBPHONE = 'Dumbphone';
	const PHONE_TYPE_OTHER = 'Other';

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('driver_info')
			->idVar('id_driver_info')
			->load($id);
	}

	public function phoneTypes(){
		return [ Cockpit_Driver_Info::PHONE_TYPE_IPHONE, Cockpit_Driver_Info::PHONE_TYPE_ANDROID, Cockpit_Driver_Info::PHONE_TYPE_BLACKBERRY, Cockpit_Driver_Info::PHONE_TYPE_DUMBPHONE, Cockpit_Driver_Info::PHONE_TYPE_OTHER ];
	}

	public function phoneTypesDefault(){
		return Cockpit_Driver_Info::PHONE_TYPE_OTHER;
	}

	public function byAdmin( $id_admin ){
		return Cockpit_Driver_Info::q( 'SELECT * FROM driver_info WHERE id_admin = ' . $id_admin );
	}

	public function exports(){
		return $this->properties();
	}

	public function pexcard_date(){
		if ( !isset( $this->_pexcard_date ) ) {
			$this->_pexcard_date = new DateTime( $this->pexcard_date, new DateTimeZone( c::config()->timezone ) );
		}
		return $this->_pexcard_date;
	}

}