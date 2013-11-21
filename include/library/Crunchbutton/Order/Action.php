<?php

class Crunchbutton_Order_Action extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('order_action')
			->idVar('id_order_action')
			->load($id);
	}
}