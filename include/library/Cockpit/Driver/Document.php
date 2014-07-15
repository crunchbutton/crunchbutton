<?php

class Cockpit_Driver_Document extends Cana_Table {

	const ID_DRIVERS_LICENCE = 4;
	const ID_AUTO_INSURANCE_CARD = 5;

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('driver_document')
			->idVar('id_driver_document')
			->load($id);
	}

	// see: https://github.com/crunchbutton/crunchbutton/issues/3393
	public function isRequired( $vehicle = false ){
		if( $this->required ){
			if( $vehicle ){
				if( ( $this->id_driver_document == Cockpit_Driver_Document::ID_DRIVERS_LICENCE && $vehicle == Crunchbutton_Admin::VEHICLE_BIKE ) ||
						( $this->id_driver_document == Cockpit_Driver_Document::ID_AUTO_INSURANCE_CARD && $vehicle == Crunchbutton_Admin::VEHICLE_BIKE ) ){
					return false;
				}
			}
			return true;
		}
		return false;
	}

	public function showDocument( $vehicle = false ){
		if( $this->required ){
			if( $vehicle ){
				if( ( $this->id_driver_document == Cockpit_Driver_Document::ID_DRIVERS_LICENCE && $vehicle == Crunchbutton_Admin::VEHICLE_BIKE ) ||
						( $this->id_driver_document == Cockpit_Driver_Document::ID_AUTO_INSURANCE_CARD && $vehicle == Crunchbutton_Admin::VEHICLE_BIKE ) ){
					return false;
				}
			}
			return true;
		}
		return false;
	}

	public function all(){
		return Cockpit_Driver_Document::q( 'SELECT * FROM driver_document ORDER BY `order` ASC' );
	}

	public function exports(){
		return $this->properties();
	}
}