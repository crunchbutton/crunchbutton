<?php

class Crunchbutton_Blast extends Cana_Table {
	const BLAST_CHUNK_SIZE = 25;

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('blast')
			->idVar('id_blast')
			->load($id);
	}

	public function users() {
		if (!$this->id_blast) {
			return;
		}
		if (!isset($this->_users)) {
			$this->_users = Blast_User::q('
				select blast_user.*, blast_user_log.id_blast_user_log as sent from blast_user
				left join blast_user_log using(id_blast_user)
				where blast_user.id_blast="'.$this->id_blast.'"
				group by blast_user.id_blast_user
			');
		}
		return $this->_users;
	}

	public static function getQue() {
		$que = self::q('
			select blast.* from blast
			where
				status!="canceled"
				and status!="complete"
				and status!="failed"
				and date <= NOW()
			order by date desc
		');
		return $que;
	}

	public function run() {

		if ($this->status != 'canceled' && $this->status != 'complete' && $this->status != 'failed') {

			$this->status = 'blasting';
			$this->save();

			$this->_runChunk();

			if ($this->progress() == $this->users()->count()) {
				echo 'progress: '.$this->progress()."\n";
				echo 'users: '.$this->users()->count()."\n";
				$this->status = 'complete';
				$this->save();
			} else {
				// just to send the event again with updated data
				$blast = Blast::o($this->id_blast);
				$blast->save();
			}
		}
	}

	public function save($new = false) {
		$new = $this->id_blast ? false : true;

		parent::save();

		$res = Event::create([
			'room' => [
				'blast.'.$this->id_blast,
				'blasts'
			]
		], $new ? 'create' : 'update', $this->exports());
	}

	private function _runChunk() {

		$users = Blast_User::q('
			select blast_user.* from blast_user
			left join blast_user_log using(id_blast_user)
			where blast_user.id_blast="'.$this->id_blast.'"
			and blast_user_log.id_blast_user_log is null
			limit '.Crunchbutton_Blast::BLAST_CHUNK_SIZE.'
		');

		foreach ($users as $user) {
			$ran = true;

			$phone = $user->phone;
			$message = $user->message();

			// #6162
			$message . "\n'STOP' to unsub";

			$status = Crunchbutton_Message_Sms::send([
				'to' => $phone,
				'message' => $message,
				'reason' => Crunchbutton_Message_Sms::REASON_BLAST
			]);

			$log = new Blast_User_Log([
				'id_blast_user' => $user->id_blast_user,
				'date' => date('Y-m-d H:i:s'),
				'status' => $status ? '1' : '0'
			]);

			$this->_support_ticket( $phone, $message );

			$log->save();
		}

		return $ran;
	}

	private function _support_ticket( $phone, $message ){
		$admin = Admin::getByPhone( $phone );
		if( $admin->id_admin ){
			Crunchbutton_Support::createNewWarning(  [ 'body' => $message, 'phone' => $phone, 'dont_open_ticket' => true ] );
		}
	}

	public function progress() {
		if (!isset($this->_progress)) {
			$users = Blast_User::q('
				select blast_user.* from blast_user
				left join blast_user_log using(id_blast_user)
				where blast_user.id_blast="'.$this->id_blast.'"
				and blast_user_log.id_blast_user_log is not null
			');
			$this->_progress = $users->count();
		}
		return $this->_progress;
	}

	public function importData($data) {
		$data = self::parseCsv($data);
		foreach ($data as $item) {
			$phone = $item['phone'];
			if ($phone) {
				unset($item['phone']);
				$user = new Blast_User([
					'id_blast' => $this->id_blast,
					'phone' => $phone,
					'data' => json_encode($item)
				]);
				$user->save();
			}
		}
	}

	public static function parseCsv($data) {
		$parser = new Cana_Csv(['useHeaders' => true]);
		$out = $parser->parse($data);
		return $out;
	}

	public function exports() {
		$data = $this->properties();
		$data['progress'] = $this->progress();

		return $data;
	}
}