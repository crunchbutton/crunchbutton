<?php

class Controller_api_communities extends Crunchbutton_Controller_Rest {

	public function init() {

		$limit = $this->request()['limit'] ?$this->request()['limit'] : 20;
		$search = $this->request()['search'] ?$this->request()['search'] : '';
		$page = $this->request()['page'] ?$this->request()['page'] : 1;
		$status = $this->request()['status'] ?$this->request()['status'] : 'all';
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
			FROM community
			LEFT JOIN restaurant_community ON community.id_community=restaurant_community.id_community
			LEFT JOIN restaurant ON restaurant_community.id_restaurant=restaurant.id_restaurant
			LEFT JOIN `order` ON restaurant.id_restaurant=`order`.id_order

			WHERE 
				community.name IS NOT NULL
		';
		
		if ($status != 'all') {
			$q .= '
				AND active="'.($status == 'active' ? '1' : '0').'"
			';
		}
		
		if ($search) {
			$s = Crunchbutton_Query::search([
				'search' => stripslashes($search),
				'fields' => [
					'community.name' => 'like',
					'community.permalink' => 'like',
					'community.name_alt' => 'like',
					'community.id_community' => 'liker'
				]
			]);
			$q .= $s['query'];
			$keys = array_merge($keys, $s['keys']);
		}
		
		$q .= '
			GROUP BY community.id_community
		';
		
		// get the count
		$count = 0;
		$r = c::db()->query(str_replace('-WILD-','COUNT(*) c', $q), $keys);
		while ($c = $r->fetch()) {
			$count++;
		}

		$q .= '
			ORDER BY community.name ASC
		';
		if ($limit != 'none') {
			$q .= '
				LIMIT ?, ?
			';
			$keys[] = $offset;
			$keys[] = $limit;
		}
		
		// do the query
		$data = [];
		$r = c::db()->query(str_replace('-WILD-','
			community.*,
			(SELECT MAX(`order`.date) FROM `order` WHERE `order`.id_restaurant = restaurant.id_restaurant) as _order_date,
			COUNT(`restaurant`.id_restaurant) restaurants
		', $q), $keys);
		while ($s = $r->fetch()) {
			/*
			$restaurant = Restaurant::o($s);
			$out = $s;
			$out->communities = [];

			foreach ($restaurant->communities() as $community) {
				$out->communities[] = $community->properties();
			}
			*/
			
			// get whether its 3rd or not
			$restaurant = Community::o($s);
			$s->type = $restaurant->type();

//			$data[] = $out;
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