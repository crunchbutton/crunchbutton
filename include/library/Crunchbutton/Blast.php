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
			$this->_users = Blast_User::q('select * from blast_user where id_blast="'.$this->id_blast.'"');
		}
		return $this->_users;
	}
	
	public static function getQue() {
		$que = self::q('
			select blast.* from blast
			where
				status="new"
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
				$this->status = 'complete';
				$this->save();
			} else {
				// just to send the event again with updated data
				$blast = Blast::o($this->id_blast);
				$blast->save();
			}
		}
	}
	
	public function save() {
		$new = $this->id_blast ? false : true;

		parent::save();

		$res = Event::emit([
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

			$status = Crunchbutton_Message_Sms::send([
				'to' => $user->phone,
				'message' => $user->message()
			]);

			$log = new Blast_User_Log([
				'id_blast_user' => $user->id_blast_user,
				'date' => date('Y-m-d H:i:s'),
				'status' => $status ? '1' : '0'
			]);

			$log->save();
		}
		
		return $ran;
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