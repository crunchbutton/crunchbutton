<?php

class Crunchbutton_Address extends Cana_Table{

	const STATUS_APPROVED = 'approved';
	const STATUS_BLOCLED = 'blocked';

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table( 'address' )
			->idVar( 'id_address' )
			->load( $id );
	}

	public static function byAddress( $_address ) {
		$_address = trim( $_address );
		if ( !$_address ) {
			return null;
		}
		$address = self::q( 'SELECT * FROM address WHERE address = ? ', [ $_address ] );
		if ( !$address->id_address ) {
			$address = new Crunchbutton_Address();
			$address->address = $_address;
			$address->save();
		}
		return $address;
	}
}