<?php

class Crunchbutton_Community_Chain extends Cana_Table{
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('community_chain')
			->idVar('id_community_chain')
			->load($id);
	}
}