<?php

class Crunchbutton_Phone_Log extends Cana_Table{

	const STATUS_ACCEPTED = 'accepted';
	const STATUS_QUEUED = 'queued';
	const STATUS_SENDING = 'sending';
	const STATUS_SENT = 'sent';
	const STATUS_DELIVERED = 'delivered';
	const STATUS_RECEIVED = 'received';

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('phone_log')
			->idVar('id_phone_log')
			->load($id);
	}

	public static function byTwilioId( $twilio_id ){
		return Crunchbutton_Phone_Log::q( 'SELECT * FROM phone_log WHERE twilio_id = ? ORDER BY id_phone_log DESC LIMIT 1', [ $twilio_id ] )->get( 0 );
	}

	public static function log($to, $from, $type = 'message', $direction = 'outgoing', $reason = '', $twilio_id = null, $status = null) {
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
			'reason' => $reason,
			'twilio_id' => $twilio_id,
			'status' => $status
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