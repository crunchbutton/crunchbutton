<?php

class Crunchbutton_Call extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('call')
			->idVar('id_call')
			->load($id);
	}
	
	public static function byTwilioId($id) {
		if (!$id) {
			return null;
		}
		return self::q('select * from call where twilio_id="'.c::db()->escape($id).'" limit 1')->get(0);
	}
	
	public static function logFromTwilio($data) {
		$call = self::byTwilioId($data['CallSid']);
		if (!$call->id_call) {
			$call = self::createFromTwilio($data);
		}
		return $call;
	}

	public static function createFromTwilio($data) {
		$call = new Call([
			'data' => json_encode($data),
			'date' => date('Y-m-d H:i:s'),
			'direction' => 'inbound',
			'twilio_id' => $data['CallSid'],
			'status' => $data['CallStatus'],
			'from' => $data['From'],
			'to' => $data['To'],
			'location_to' => $data['ToCity'].', '.$data['ToState'].' '.$data['ToZip'],
			'location_from' => $data['FromCity'].', '.$data['FromState'].' '.$data['FromZip'],
		]);
		$call->save();

		return $call;
	}
}