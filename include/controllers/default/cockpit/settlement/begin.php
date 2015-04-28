<?php

class Controller_settlement_begin extends Crunchbutton_Controller_Account {
	public function init() {
		
		die('#5430 deprecated');

		/*
		if (!c::admin()->permission()->check(['global','settlement'])) {
			return;
		}

		$dates = explode(',',trim($_REQUEST['dates']));

		$settlement = new Settlement( [ 'payment_method' => $_REQUEST['payment_method'], 'start' => $_REQUEST['start'], 'end' => $_REQUEST['end'] ] );
		c::view()->restaurants = $settlement->start();

		c::view()->layout('layout/ajax');
		c::view()->display('settlement/begin');
		*/
	}
}
