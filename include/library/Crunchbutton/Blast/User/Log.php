<?php

class Crunchbutton_Blast_User_Log extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('blast_user_log')
			->idVar('id_blast_user_log')
			->load($id);
	}
}