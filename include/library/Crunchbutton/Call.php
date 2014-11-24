<?php

class Crunchbutton_Call extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('call')
			->idVar('id_call')
			->load($id);
	}

	public static function createFromTwilio($data) {
		$call = new Call([
			'data' => json_encode($data),
			'date' => date('Y-m-d H:i:s')
		]);
		$call->save();
		return $call;
	}
}