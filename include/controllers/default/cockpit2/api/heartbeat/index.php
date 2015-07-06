<?php

// ticket count, order count, location tracking. should be run every 30 seconds

class Controller_api_heartbeat extends Crunchbutton_Controller_RestAccount {

	public function init() {
		// we are both posting and getting
		$r = [
			'tickets' => 0,
			'timestamp' => 0,
			'orders' => [
				'total' => 0,
				'new' => 0,
				'accepted' => 0,
				'pickedup' => 0
			]
		];


		// support
		$tickets = null;

		if (c::admin()->permission()->check(['global', 'support-all', 'support-view', 'support-crud' ])) {
			$q = "SELECT COUNT( DISTINCT( s.id_support ) ) AS c FROM support s INNER JOIN support_message sm ON s.id_support = sm.id_support WHERE s.status = 'open' ";
			$q = c::db()->get($q);
			$tickets = $q->get(0)->c;

			// get the last support message #4337
			$q = "SELECT UNIX_TIMESTAMP( date ) AS timestamp FROM support INNER JOIN support_message ON support_message.id_support = support.id_support WHERE status = 'open' ORDER BY id_support_message DESC LIMIT 1";
			$q = c::db()->get($q);
			$timestamp = $q->get(0)->timestamp;
		}

		$r['timestamp'] = $timestamp;
		$r['tickets'] = $tickets;

		// orders
		foreach (Order::deliveryOrders(12) as $order) {
			$r['orders']['total']++;
			switch ($order->status()->last()['status']) {
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

		$r['working'] = c::user()->isWorking();


		// location reporting
		$lat = $this->request()['lat'] ? $this->request()['lat'] : $this->request()['latitude'];
		$lon = $this->request()['lon'] ? $this->request()['lon'] : $this->request()['longitude'];

		if ($lat && $lon && c::admin()->id_admin) {
			(new Admin_Location([
				'id_admin' => c::admin()->id_admin,
				'date' => date('Y-m-d H:i:s'),
				'lat' => $lat,
				'lon' => $lon,
				'accuracy' => $this->request()['accuracy']
			]))->save();
		}


		echo json_encode($r);
		exit;


	}
}