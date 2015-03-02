<?php

class Controller_api_restaurants extends Crunchbutton_Controller_Rest {

	public function init() {
		// sub queries for special cases

		switch (c::getPagePiece(2)) {
			// list of restaurants that were already paid
			case 'paid-list':
				$restaurants = Crunchbutton_Restaurant::q( 'SELECT DISTINCT(r.id_restaurant) AS id_restaurant, r.name  FROM restaurant r
																			INNER JOIN payment p ON p.id_restaurant = r.id_restaurant
																		ORDER BY r.name ASC' );
				$export = [];
				$export[] = array( 'id_restaurant' => 0, 'name' => 'All' );
				foreach( $restaurants as $restaurant ){
					$export[] = array( 'id_restaurant' => $restaurant->id_restaurant, 'name' => $restaurant->name );
				}
				echo json_encode( $export );
				break;

			case 'eta':
				$out = [];
				$restaurants = Crunchbutton_Restaurant::q( 'SELECT * FROM restaurant WHERE active = true AND delivery_service = true ORDER BY name ASC' );
				foreach( $restaurants as $restaurant ){
					if( $restaurant->open() ){
						$community = $restaurant->community()->name;
						$drivers = $restaurant->activeDrivers();
						$out[] = array_merge( [ 'restaurant' => $restaurant->name, 'community' => $community ], $restaurant->smartETA( true ) );
					}
				}
				echo json_encode( $out );exit;
				break;
			// Simple list returns just the name and id
			case 'list':
				$restaurants = Crunchbutton_Restaurant::active();
				$export = [];
				foreach( $restaurants as $restaurant ){
					$export[] = array( 'id_restaurant' => $restaurant->id_restaurant, 'name' => $restaurant->name );
				}
				echo json_encode( $export );
				break;

			case 'no-payment-method':
				$restaurants = Crunchbutton_Restaurant::with_no_payment_method();
				$export = [];
				foreach( $restaurants as $restaurant ){
					$export[] = array( 'id_restaurant' => $restaurant->id_restaurant, 'name' => $restaurant->name );
				}
				echo json_encode( $export );
				break;

			default:
				// the main query for the list view
				$this->_query();
				break;
		}
	}

	private function _query() {

		$limit = $this->request()['limit'] ? $this->request()['limit'] : 20;
		$search = $this->request()['search'] ? $this->request()['search'] : '';
		$page = $this->request()['page'] ? $this->request()['page'] : 1;
		$status = $this->request()['status'] ? $this->request()['status'] : 'all';
		$community = $this->request()['community'] ? $this->request()['community'] : null;
		$keys = [];

		if ($page == 1) {
			$offset = '0';
		} else {
			$offset = ($page-1) * $limit;
		}

		$q = '
			SELECT
				-WILD-
			FROM restaurant
			LEFT JOIN `order` ON restaurant.id_restaurant=`order`.id_restaurant
		';
		if ($community) {
			$q .= '
				LEFT JOIN restaurant_community ON restaurant.id_restaurant=restaurant_community.id_restaurant
			';
		}
		$q .='
			WHERE
				restaurant.name IS NOT NULL
		';

		if ($status != 'all') {
			$q .= '
				AND active="'.($status == 'active' ? '1' : '0').'"
			';
		}

		if ($community) {
			$q .= '
				AND restaurant_community.id_community=?
			';
			$keys[] = $community;
		}

		if ($search) {
			$s = Crunchbutton_Query::search([
				'search' => stripslashes($search),
				'fields' => [
					'restaurant.name' => 'like',
					'restaurant.address' => 'like',
					'restaurant.phone' => 'like',
					'restaurant.community' => 'like',
					'restaurant.permalink' => 'like',
					'restaurant.id_restaurant' => 'liker'
				]
			]);
			$q .= $s['query'];
			$keys = array_merge($keys, $s['keys']);
		}

		$q .= '
			GROUP BY restaurant.id_restaurant
		';

		// get the count
		$count = 0;
		$r = c::db()->query(str_replace('-WILD-','COUNT(*) c', $q), $keys);
		while ($c = $r->fetch()) {
			$count++;
		}

		$q .= '
			ORDER BY restaurant.name ASC
			LIMIT ?, ?	
		';
		$keys[] = $offset;
		$keys[] = $limit;

		// do the query
		$data = [];
		$r = c::db()->query(str_replace('-WILD-','
			restaurant.*,
			(SELECT MAX(`order`.date) FROM `order` WHERE `order`.id_restaurant = restaurant.id_restaurant) as _order_date,
			COUNT(`order`.id_order) orders
		', $q), $keys);
		while ($s = $r->fetch()) {
			$restaurant = Restaurant::o($s);
			$out = $s;
			$out->delivery_is_self = $restaurant->deliveryItSelf();
			$out->communities = [];
			foreach ($restaurant->communities() as $community) {
				$out->communities[] = $community->properties();
			}

/*
			$unset = ['email','timezone','testphone','txt'];
			foreach ($unset as $un) {
				unset($staff[$un]);
			}
*/
			$data[] = $out;
//			$data[] = $s;
		}

		echo json_encode([
			'count' => intval($count),
			'pages' => ceil($count / $limit),
			'page' => $page,
			'results' => $data
		]);
	}
}