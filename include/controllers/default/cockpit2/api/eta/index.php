<?php

class Controller_api_eta extends Crunchbutton_Controller_Rest {

	public function init() {

		$limit = $this->request()['limit'] ?$this->request()['limit'] : 20;
		$search = $this->request()['search'] ?$this->request()['search'] : '';
		$page = $this->request()['page'] ?$this->request()['page'] : 1;
		$community = $this->request()['community'] ?$this->request()['community'] : 'all';
		$open = $this->request()['open'] ?$this->request()['open'] : 'all';

		$keys = [];

		if ($limit == 'none') {
			$page = 1;
		}

		if ($page == 1) {
			$offset = '0';
		} else {
			$offset = ($page-1) * $limit;
		}


		$q = '
			SELECT
				-WILD-
			FROM order_eta oe
				INNER JOIN `order` o ON o.id_order = oe.id_order
				INNER JOIN order_action oa ON oa.id_order = oe.id_order AND oa.type = "' . Crunchbutton_Order_Action::DELIVERY_DELIVERED . '"
				INNER JOIN restaurant r ON o.id_restaurant = r.id_restaurant
				INNER JOIN community c ON c.id_community = o.id_community
				INNER JOIN admin a ON oa.id_admin = a.id_admin


			WHERE oe.method = "' . Crunchbutton_Order_Eta::METHOD_SMART_ETA . '"
		';

		if ($community != 'all') {
			$q .= '
				AND c.id_community="'. $community .'""
			';
		}

		if ($search) {
			$s = Crunchbutton_Query::search([
				'search' => stripslashes($search),
				'fields' => [
					'c.name' => 'like',
					'd.name' => 'like',
					'r.name' => 'like',
					'o.name' => 'like',
				]
			]);
			$q .= $s['query'];
			$keys = array_merge($keys, $s['keys']);
		}


		// get the count
		$count = 0;
		$r = c::db()->query( str_replace('-WILD-','COUNT(*) c', $q ), $keys );
		while ($c = $r->fetch()) {
			$count = $c->c;
		}

		$q .= '
			ORDER BY oa.timestamp DESC
		';
		if ($limit != 'none') {
			$q .= '
				LIMIT '.intval($limit).'
				OFFSET '.intval($offset).'
			';
		}

		// do the query
		$data = [];
		$r = c::db()->query(str_replace('-WILD-','
			o.id_order,
			oe.time AS eta,
			o.date AS ordered_at,
			oa.timestamp AS delivered_at,
			r.name AS restaurant,
			o.name AS customer,
			a.name AS driver,
			c.name AS community
		', $q), $keys);
		while ($s = $r->fetch()) {
			$ordered_at = new DateTime( $s->ordered_at, new DateTimeZone( c::config()->timezone ) );
			$delivered_at = new DateTime( $s->delivered_at, new DateTimeZone( c::config()->timezone ) );
			$timeToDelivery = $ordered_at->diff( $delivered_at );
			$timeToDelivery = ceil( Crunchbutton_Util::intervalToSeconds( $timeToDelivery ) / 60 );
			$s->delivered_at_timestamp = Crunchbutton_Util::dateToUnixTimestamp( $delivered_at );
			$s->ordered_at_timestamp = Crunchbutton_Util::dateToUnixTimestamp( $ordered_at );
			$s->diff = $s->eta - $timeToDelivery;
			$s->minutes_to_delivery = $timeToDelivery;
			$data[] = $s;
		}

		echo json_encode([
			'count' => intval($count),
			'pages' => $limit == 'none' ? '1' : ceil($count / $limit),
			'page' => $page,
			'results' => $data
		]);
	}
}
