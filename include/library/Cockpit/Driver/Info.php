<?php

class Cockpit_Driver_Info extends Cana_Table_Trackchange {

	const PHONE_TYPE_IPHONE = 'iPhone';
	const PHONE_TYPE_ANDROID = 'Android';
	const PHONE_TYPE_BLACKBERRY = 'Blackberry';
	const PHONE_TYPE_DUMBPHONE = 'Dumbphone';
	const PHONE_TYPE_OTHER = 'Other';
	//const ANDROID_TYPE_OTHER = 'Other'; //michal
	const CARRIER_TYPE_ATT = 'ATT';
	const CARRIER_TYPE_VERIZON = 'Verizon';
	const CARRIER_TYPE_SPRINT = 'Sprint';
	const CARRIER_TYPE_T_MOBILE = 'T-Mobile';
	const CARRIER_TYPE_USCELLULAR = 'U.S. Cellular';
	const CARRIER_TYPE_OTHER = 'Other';

	const UNKNOWN_TYPE = 'Unknown';

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('driver_info')
			->idVar('id_driver_info')
			->load($id);
	}


	public function carrierTypes(){
		return [ self::CARRIER_TYPE_ATT,
						 self::CARRIER_TYPE_VERIZON,
						 self::CARRIER_TYPE_SPRINT,
						 self::CARRIER_TYPE_T_MOBILE,
						 self::CARRIER_TYPE_USCELLULAR,
						 self::CARRIER_TYPE_OTHER,
						 self::UNKNOWN_TYPE ];
	}

	public function carrierTypeOther(){
		return self::CARRIER_TYPE_OTHER;
	}

	public function phoneTypes(){
		return [ 	self::PHONE_TYPE_IPHONE,
							self::PHONE_TYPE_ANDROID,
							self::PHONE_TYPE_BLACKBERRY,
							self::PHONE_TYPE_DUMBPHONE,
							self::PHONE_TYPE_OTHER,
						 self::UNKNOWN_TYPE ];
	}

//	public function androidTypeOther(){
//		return self::ANDROID_TYPE_OTHER;
//	}//michal

	public function iPhoneTypes(){
		return [	'3G',
							'3GS',
							'4',
							'4S',
							'5',
							'5C',
							'5S',
							'6',
							'6 Plus',
							'Other'  ];
	}

		public function tshirtSizes(){
				return [	'S',
									'M',
									'L',
									'XL',
									'XXL' ];
		}

	public function androidTypes(){
//		$addedSubtypes = self::q( 'select phone_subtype from driver_info
//			where
//			phone_type="android"
//			and phone_subtype is not null
//			and phone_subtype != "Other"
//			group by phone_subtype');

				$types = [ 	'Samsung Galaxy S3',
								'Samsung Galaxy S4',
								'Samsung Galaxy Note3',
								'Samsung Galaxy S5',
								'Samsung Galaxy S6',
								'Motorola Moto G',
								'Samsung Galaxy Tab3 7.0',
								'Samsung Galaxy S3 Mini',
								'Samsung Galaxy Note2',
								'Samsung Galaxy S Duos 2',
								'Samsung Galaxy S2',
								'Samsung Galaxy S4 Mini',
								'Samsung Galaxy Grand',
								'Samsung Galaxy Y',
								'Samsung Galaxy Grand2',
								'Samsung Galaxy Core',
								'Samsung Galaxy Grand Neo',
								'Samsung Galaxy S',
								'Samsung Galaxy Win',
								'Samsung Galaxy Star Advance',
								'Samsung Galaxy Ace'];
//		foreach ($addedSubtypes as $addedSubtype) {
//		array_push($types,
//			$addedSubtype->phone_subtype);
//		}

		sort( $types );
		$types[] = 'Other';
		//$types[] = self::ANDROID_TYPE_OTHER;
		return $types;
	}

	public function androidVersion(){
		$types = [ 	'Froyo (2.2)',
								'Gingerbread (2.3.3 - 2.3.7)',
								'Ice Cream Sandwich (4.0.3 - 4.0.4)',
								'Jelly Bean (4.1.x)',
								'Jelly Bean (4.2.x)',
								'Jelly Bean (4.3.x)',
								'KitKat (4.4)',
								'Lollipop (5.0)',
								 ];

		asort( $types );
		$types[] = 'Other';
		return $types;
	}

	public function phoneTypeDefault(){
		return self::PHONE_TYPE_OTHER;
	}

	public static function byAdmin( $id_admin ){
		return self::q( 'SELECT * FROM driver_info WHERE id_admin = ' . $id_admin );
	}

	public function stopHelpOutNotification(){
		$this->down_to_help_out_stop = date( 'Y-m-d' );
		$this->save();
	}

	public function couldReceiveHelpOutNotification(){

		if( !$this->down_to_help_out ){
			return false;
		}

		if( !$this->down_to_help_out_stop ){
			return true;
		}
		if( $this->down_to_help_out_stop == date( 'Y-m-d' ) ){
			return false;
		}
		$this->down_to_help_out_stop = null;
		$this->save();
		return true;
	}

	public function exports(){
		$out = $this->properties();
		$types = self::carrierTypes();
		if( !in_array( $out[ 'cell_carrier' ], $types ) ) {
			 $out[ 'carrier_type_other' ] = $out[ 'cell_carrier' ];
			 $out[ 'carrier_type' ] = self::CARRIER_TYPE_OTHER;
		} else {
			$out[ 'carrier_type' ] = $out[ 'cell_carrier' ];
		}
		if( !$out[ 'down_to_help_out' ] ){
			$out[ 'down_to_help_out' ] = false;
		} else {
			$out[ 'down_to_help_out' ] = true;
		}

		if( !$out[ 'weekend_driver' ] ){
			$out[ 'weekend_driver' ] = false;
		} else {
			$out[ 'weekend_driver' ] = true;
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