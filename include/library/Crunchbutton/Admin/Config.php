<?php

class Crunchbutton_Admin_Config extends Cana_Table {
	public function __construct( $id = null ) {
		parent::__construct();
		$this
			->table('admin_config')
			->idVar('id_admin_config')
			->load($id);
	}
}