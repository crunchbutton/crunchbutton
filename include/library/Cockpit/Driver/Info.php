<?php

class Cockpit_Driver_Info extends Cana_Table {

	const PHONE_TYPE_IPHONE = 'iPhone';
	const PHONE_TYPE_ANDROID = 'Android';
	const PHONE_TYPE_BLACKBERRY = 'Blackberry';
	const PHONE_TYPE_DUMBPHONE = 'Dumbphone';
	const PHONE_TYPE_OTHER = 'Other';

	const CARRIER_TYPE_ATT = 'ATT';
	const CARRIER_TYPE_VERIZON = 'Verizon';
	const CARRIER_TYPE_SPRINT = 'Sprint';
	const CARRIER_TYPE_T_MOBILE = 'T-Mobile';
	const CARRIER_TYPE_USCELLULAR = 'U.S. Cellular';
	const CARRIER_TYPE_OTHER = 'Other';

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('driver_info')
			->idVar('id_driver_info')
			->load($id);
	}

	public function carrierTypes(){
		return [ Cockpit_Driver_Info::CARRIER_TYPE_ATT,
						 Cockpit_Driver_Info::CARRIER_TYPE_VERIZON,
						 Cockpit_Driver_Info::CARRIER_TYPE_SPRINT,
						 Cockpit_Driver_Info::CARRIER_TYPE_T_MOBILE,
						 Cockpit_Driver_Info::CARRIER_TYPE_USCELLULAR,
						 Cockpit_Driver_Info::CARRIER_TYPE_OTHER ];
	}

	public function carrierTypeOther(){
		return Cockpit_Driver_Info::CARRIER_TYPE_OTHER;
	}

	public function phoneTypes(){
		return [ Cockpit_Driver_Info::PHONE_TYPE_IPHONE, Cockpit_Driver_Info::PHONE_TYPE_ANDROID, Cockpit_Driver_Info::PHONE_TYPE_BLACKBERRY, Cockpit_Driver_Info::PHONE_TYPE_DUMBPHONE, Cockpit_Driver_Info::PHONE_TYPE_OTHER ];
	}

	public function phoneTypeDefault(){
		return Cockpit_Driver_Info::PHONE_TYPE_OTHER;
	}

	public function byAdmin( $id_admin ){
		return Cockpit_Driver_Info::q( 'SELECT * FROM driver_info WHERE id_admin = ' . $id_admin );
	}

	public function exports(){
		$out = $this->properties();
		$types = Cockpit_Driver_Info::carrierTypes();
		if( !in_array( $out[ 'cell_carrier' ], $types ) ) {
			 $out[ 'carrier_type_other' ] = $out[ 'cell_carrier' ];
			 $out[ 'carrier_type' ] = Cockpit_Driver_Info::CARRIER_TYPE_OTHER;
		} else {
			$out[ 'carrier_type' ] = $out[ 'cell_carrier' ];
		}
		return $out;
	}

	public function pexcard_date(){
		if ( !isset( $this->_pexcard_date ) ) {
			$this->_pexcard_date = new DateTime( $this->pexcard_date, new DateTimeZone( c::config()->timezone ) );
		}
		return $this->_pexcard_date;
	}

}