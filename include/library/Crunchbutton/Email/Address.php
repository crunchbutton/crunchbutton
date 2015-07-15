<?php

class Crunchbutton_Email_Address extends Cana_Table{

	const REASON_NEW_USER = 'New user';
	const REASON_NOTIFY_CS = 'Notify CS';
	const REASON_ORDER = 'Order';
	const REASON_PROMO = 'Promo';
	const REASON_RULES = 'Rules';
	const REASON_CRON_ERROR = 'Cron Error';

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('email_address')
			->idVar('id_email_address')
			->load($id);
	}

	public static function byEmail( $email ) {
		$email = trim( $email );
		if ( !$email ) {
			return null;
		}
		$_email = Crunchbutton_Email_Address::q( 'SELECT * FROM email_address WHERE email = ? ', [$email]);
		if ( !$_email->id_email_address ) {
			$_email = new Crunchbutton_Email_Address();
			$_email->email = $email;
			$_email->save();
		}
		return $_email;
	}

}