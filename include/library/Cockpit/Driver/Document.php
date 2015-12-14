<?php

class Cockpit_Driver_Document extends Cana_Table {

	const ID_DRIVERS_LICENCE = 4;
	const ID_AUTO_INSURANCE_CARD = 5;
	const ID_INDY_CONTRACTOR_AGREEMENT_ORDER = 1;
	const ID_INDY_CONTRACTOR_AGREEMENT_HOURLY = 6;
	const ID_INDY_CONTRACTOR_AGREEMENT_BRAND_REP = 9;
	const ID_INDY_CONTRACTOR_AGREEMENT_COMMUNITY_MANAGER = 16;
	const ID_DRIVER_W9 = 2;
	const ID_COMMUNITY_MANAGER_W9 = 12;

	const TYPE_DRIVER = 'driver';
	const TYPE_MARKETING_REP = 'marketing-rep';

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

	public function driver(){
		return Cockpit_Driver_Document::q( 'SELECT * FROM driver_document WHERE type = ? AND active = 1 ORDER BY `order` ASC', [Cockpit_Driver_Document::TYPE_DRIVER]);
	}

	public function marketing_rep(){
		return Cockpit_Driver_Document::q( 'SELECT * FROM driver_document WHERE type = ? AND active = 1 ORDER BY `order` ASC', [Cockpit_Driver_Document::TYPE_MARKETING_REP]);
	}

	public function exports(){
		return $this->properties();
	}
}