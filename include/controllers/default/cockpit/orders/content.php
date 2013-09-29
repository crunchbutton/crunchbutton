<?php

class Controller_orders_content extends Crunchbutton_Controller_Account {
	public function init() {
		if (!c::admin()->permission()->check(['global', 'orders'])) {
			return ;
		}
		$search = [];
		if ($_REQUEST['limit']) {
			$search['limit'] = intval($_REQUEST['limit']);
		}

		if ($_REQUEST['search']) {

			if ($_REQUEST['search']{0} == '#') {
				$search['order'] = substr($_REQUEST['search'],1);
			} else {
				$search['search'] = $_REQUEST['search'];
			}

		}
		
		if ($_REQUEST['env']) {
			$search['env'] = $_REQUEST['env'];
		}
		
		if ($_REQUEST['processor']) {
			$search['processor'] = $_REQUEST['processor'];
		}
		
		if ($_REQUEST['dates']) {
			$dates = explode(',',$_REQUEST['dates']);
			$search['start'] = $dates[0];
			$search['end'] = $dates[1];
		}
		
		if ($_REQUEST['restaurant']) {
			$search['restaurant'] = $_REQUEST['restaurant'];
		}
		
		if ($_REQUEST['community']) {
			$search['community'] = $_REQUEST['community'];
		}

		c::view()->orders = Order::find($search);

		if ($_REQUEST['export']) {
			c::view()->layout('layout/csv');
			c::view()->display('orders/csv', ['display' => true, 'filter' => false]);
		} else {
			c::view()->layout('layout/ajax');
			c::view()->display('orders/content');
		}
	}
}