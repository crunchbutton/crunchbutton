<?php

// ticket count, order count, location tracking. should be run every 30 seconds

class Controller_api_heartbeat extends Crunchbutton_Controller_RestAccount {

	public function init() {
		// we are both posting and getting
		$r = [
			'tickets' => 0,
			'orders' => [
				'total' => 0,
				'new' => 0,
				'accepted' => 0,
				'pickedup' => 0
			]
		];


		// support
		$tickets = [];

		if (c::admin()->permission()->check(['global', 'support-all', 'support-view', 'support-crud' ])) {
			$q = 'SELECT count(*) as c from support where status="open"';
			$q = c::db()->get($q);
			$tickets = $q->get(0)->c;
		}

		$r['tickets'] = $tickets;


		// orders
		foreach (Order::deliveryOrders(12) as $order) {
			$r['orders']['total']++;
			switch ($order->deliveryLastStatus()['status']) {
				case 'new':
					$r['orders']['new']++;
					break;
				case 'accepted':
					$r['orders']['accepted']++;
					break;
				case 'pickedup':
					$r['orders']['pickedup']++;
					break;
			}
		}
		

		// location reporting
		$lat = $this->request()['lat'] ? $this->request()['lat'] : $this->request()['latitude'];
		$lon = $this->request()['lon'] ? $this->request()['lon'] : $this->request()['longitude'];
		
		if ($lat && $lon && c::admin()->id_admin) {
			(new Admin_Location([
				'id_admin' => c::admin()->id_admin,
				'date' => date('Y-m-d H:i:s'),
				'lat' => c::db()->escape($lat),
				'lon' => c::db()->escape($lon),
				'accuracy' => c::db()->escape($this->request()['accuracy'])
			]))->save();
		}


		echo json_encode($r);
		exit;
		

	}
}