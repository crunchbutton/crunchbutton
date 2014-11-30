<?php

class Crunchbutton_Order_Change extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('order_change')
			->idVar('id_order_change')
			->load($id);
	}
}