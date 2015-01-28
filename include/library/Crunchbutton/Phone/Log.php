<?php

class Crunchbutton_Phone_Log extends Cana_Table{

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('phone_log')
			->idVar('id_phone_log')
			->load($id);
	}

	public static function log($to, $from, $type = 'message', $direction = 'outgoing', $reason = '') {
		$to = Phone::byPhone($to)->id_phone;
		$from = Phone::byPhone($from)->id_phone;

		if (!$to || !$from) {
			return false;
		}

		$log = new Phone_Log([
			'id_phone_to' => $to,
			'id_phone_from' => $from,
			'date' => date('Y-m-d H:i:s'),
			'type' => $type,
			'direction' => $direction,
			'reason' => $reason
		]);
		$log->save();

		return $log;
	}

	public function wasAppLinkAlreadySent( $phone ){
		$log = Crunchbutton_Phone_Log::q( "SELECT * FROM phone_log pl INNER JOIN phone p ON p.id_phone = pl.id_phone_to AND p.phone = '" . $phone . "' AND reason = '" . Crunchbutton_Message_Sms::REASON_APP_DOWNLOAD . "'" );
		if( $log->count() > 0 ){
			return true;
		}
		return false;
	}


	public function from() {
		return Phone::o($this->id_phone_from);
	}

	public function to() {
		return Phone::o($this->id_phone_to);
	}
}