<?php

class Controller_settlement_begin extends Crunchbutton_Controller_Account {
	public function init() {

		if (!c::admin()->permission()->check(['global','settlement'])) {
			return;
		}

		$dates = explode(',',trim($_REQUEST['dates']));

		c::view()->settlement = new Settlement([
			'payment_method' => $_REQUEST['payment_method'],
			'start' => $_REQUEST['start'],
			'end' => $_REQUEST['end'],
		]);

		c::view()->layout('layout/ajax');
		c::view()->display('settlement/begin');
	}
}
