<?php

class Crunchbutton_Call extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('call')
			->idVar('id_call')
			->load($id);
	}

	public function save($new = false) {
		$new = $this->id_call ? false : true;

		parent::save();

		Event::create([
			'room' => [
				'call.'.$this->id_call,
				'calls'
			]
		], $new ? 'create' : 'update', $this->exports());
	}

	public function exports(){
		$out = $this->properties();
		$out[ 'name' ] = 'unknown';
		if( $this->id_admin_from ){
			$admin = Admin::o( $this->id_admin_from );
			$out[ 'name' ] = $admin->name;
		}
		if( $this->id_user_from ){
			$user = User::o( $this->id_user_from );
			$out[ 'name' ] = $user->name;
		}
		return $out;
	}

	public static function byTwilioId($id) {
		if (!$id) {
			return null;
		}
		return self::q('select * from `call` where twilio_id=? limit 1',[$id])->get(0);
	}

	public static function logFromTwilio($data) {
		$call = self::byTwilioId($data['CallSid']);
		if (!$call->id_call) {
			$call = self::createFromTwilio($data);
		}
		if ($data['CallDuration']) {
			$end = new DateTime($call->date_start);
			$end->modify('-'.$data['CallDuration'].' seconds');
			$call->date_end = $end->format('Y-m-d H:i:s');
			$call->status = 'completed';
			$call->save();
		}
		if ($data['RecordingUrl']) {
			$call->recording_url = $data['RecordingUrl'];
			$call->recording_sid = $data['RecordingSid'];
			$call->recording_duration = $data['RecordingDuration'];
			$call->save();
		}
		return $call;
	}

	public static function createFromTwilio($data) {
		$call = new Call([
			'data' => json_encode($data),
			'date_start' => date('Y-m-d H:i:s'),
			'direction' => 'inbound',
			'twilio_id' => $data['CallSid'],
			'status' => $data['CallStatus'],
			'from' => Phone::clean($data['From']),
			'to' => Phone::clean($data['To']),
			'location_to' => $data['ToCity'].', '.$data['ToState'].' '.$data['ToZip'],
			'location_from' => $data['FromCity'].', '.$data['FromState'].' '.$data['FromZip'],
		]);
		$call->associateForeignKeys();
		$call->save();

		return $call;
	}

	public function associateForeignKeys() {
		if ($this->direction == 'outbound') {
			$this->id_admin_to = Admin::q('select * from admin where active=true and phone="'.$this->to.'" limit 1')->get(0)->id_admin;
			$this->id_user_to = Admin::q('select * from `user` where active=true and phone="'.$this->to.'" order by id_user desc limit 1')->get(0)->id_user;

		} elseif ($this->direction == 'inbound') {
			$this->id_admin_from = Admin::q('select * from admin where active=true and phone="'.$this->from.'" limit 1')->get(0)->id_admin;
			$this->id_user_from = Admin::q('select * from `user` where active=true and phone="'.$this->from.'" order by id_user desc limit 1')->get(0)->id_user;

			$this->id_support = Admin::q('
				select support.* from support
				left join support_message using(id_support)
				where
					support.phone="'.$this->from.'"
					and datediff(now(), support_message.date) < 1
				order by support.id_support desc
				limit 1
			')->get(0)->id_suport;
		}
	}
}