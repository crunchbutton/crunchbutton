<?php

class Crunchbutton_Admin_Group extends Cana_Table_Trackchange {
	public function __construct($id = null) {
		parent::__construct();
		$this->changeOptions([
			'author_id' => 'id_author',
			'track_new' => true
		]);
		$this
			->table('admin_group')
			->idVar('id_admin_group')
			->load($id);
	}
}