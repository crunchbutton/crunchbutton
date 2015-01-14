<?php

class Crunchbutton_Email_Address_Log extends Cana_Table{

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('email_address_log')
			->idVar('id_email_address_log')
			->load($id);
	}

	public static function log( $params ) {

		$log = [];

		$email_to = Crunchbutton_Email_Address::byEmail( $params[ 'email_address_to' ] );
		$log[ 'id_email_to' ] = $email_to->id_email_address;

		$email_from = Crunchbutton_Email_Address::byEmail( $params[ 'email_address_from' ] );
		$log[ 'id_email_from' ] = $email_from->id_email_address;

		$log[ 'reason' ] = $params[ 'reason' ];
		$log[ 'subject' ] = $params[ 'subject' ];

		if ( !$log[ 'id_email_to' ] || !$log[ 'id_email_from' ] ) {
			return false;
		}

		$log[ 'date' ] = date( 'Y-m-d H:i:s' );

		$log = new Crunchbutton_Email_Address_Log( [
			'id_email_address_to' => $log[ 'id_email_to' ],
			'id_email_address_from' => $log[ 'id_email_from' ],
			'reason' => $log[ 'reason' ],
			'subject' => $log[ 'subject' ],
			'reason' => $log[ 'reason' ],
			'date' => $log[ 'date' ]
		] );

		$log->save();

		return $log;
	}

	public function email_to() {
		return Crunchbutton_Email_Address::o( $this->id_email_to );
	}

	public function emailfrom() {
		return Crunchbutton_Email_Address::o( $this->id_emailfrom );
	}
}