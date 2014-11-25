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

		$limit = $this->request()['limit'] ? c::db()->escape($this->request()['limit']) : 25;
		$search = $this->request()['search'] ? c::db()->escape($this->request()['search']) : '';
		$page = $this->request()['page'] ? c::db()->escape($this->request()['page']) : 1;
		$status = $this->request()['status'] ? c::db()->escape($this->request()['status']) : 'all';
		$community = $this->request()['community'] ? c::db()->escape($this->request()['community']) : 'all';
		
		if ($page == 1) {
			$offset = '0';
		} else {
			$offset = ($page-1) * $limit;
		}


		$q = '
			SELECT
				-WILD-
			FROM restaurant
			LEFT JOIN `order` using(id_restaurant)
			WHERE 
				restaurant.name IS NOT NULL
		';
		
		if ($status != 'all') {
			$q .= '
				AND active="'.($status == 'active' ? '1' : '0').'"
			';
		}
		
		if ($search) {
			$q .= Crunchbutton_Query::search([
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
		}
		
		$q .= '
			GROUP BY restaurant.id_restaurant
		';
		
		// get the count
		$count = 0;
		$r = c::db()->query(str_replace('-WILD-','COUNT(*) c', $q));
		while ($c = $r->fetch()) {
			$count++;
		}

		$q .= '
			ORDER BY restaurant.name ASC
			LIMIT '.$offset.', '.$limit.'
		';
		
		// do the query
		$data = [];
		$r = c::db()->query(str_replace('-WILD-','
			restaurant.*,
			(SELECT MAX(`order`.date) FROM `order` WHERE `order`.id_restaurant = restaurant.id_restaurant) as _order_date,
			COUNT(`order`.id_order) orders
		', $q));
		while ($s = $r->fetch()) {
//			$restaurant = Restaurant::o($s)->exports();
/*
			$unset = ['email','timezone','testphone','txt'];
			foreach ($unset as $un) {
				unset($staff[$un]);
			}
*/
//			$data[] = $restaurant;
			$data[] = $s;
		}

		echo json_encode([
			'count' => intval($count),
			'pages' => ceil($count / $limit),
			'page' => $page,
			'results' => $data
		]);
	}
}