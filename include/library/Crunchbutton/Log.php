<?php

class Crunchbutton_Log extends Cana_Table {
	public static function __callStatic($func, $args) {
		$log = new Log;
		$log->level = $func;
		$log->data = json_encode($args);
		$log->date = date('Y-m-d H:i:s');
		$log->save();
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('log')
			->idVar('id_log')
			->load($id);
	}
}