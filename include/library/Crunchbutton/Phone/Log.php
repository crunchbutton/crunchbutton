<?php

class Crunchbutton_Phone_Log extends Cana_Table{

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('phone_log')
			->idVar('id_phone_log')
			->load($id);
	}
	
	public static function log($to, $from, $type = 'message') {
		$log = new Phone_Log([
			'id_phone_to' => Phone::byPhone($to)->id_phone,
			'id_phone_from' => Phone::byPhone($from)->id_phone,
			'date' => date('Y-m-d H:i:s'),
			'type' => $type
		]);
		$log->save();
		return $log;
	}
	
	public function from() {
		return Phone::o($this->id_phone_from);
	}

	public function to() {
		return Phone::o($this->id_phone_to);
	}
}