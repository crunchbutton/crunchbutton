<?php

class Controller_api_orders extends Crunchbutton_Controller_RestAccount {

	public function init() {
		if ($this->method() != 'get') {
			exit;
		}

		// manual query is faster than using the Order->exports

		// @todo: merge this with Order::find when we get rid of old cockpit/orders

		$limit = $this->request()['limit'] ? $this->request()['limit'] : 20;
		$search = $this->request()['search'] ? $this->request()['search'] : '';
		$page = $this->request()['page'] ? $this->request()['page'] : 1;
		$user = $this->request()['user'] ? $this->request()['user'] : null;
		$phone = $this->request()['phone'] ? $this->request()['phone'] : null;
		$datestart = $this->request()['datestart'] ? $this->request()['datestart'] : null;
		$dateend = $this->request()['dateend'] ? $this->request()['dateend'] : null;
		$restaurant = $this->request()['restaurant'] ? $this->request()['restaurant'] : null;
		$community = $this->request()['community'] ? $this->request()['community'] : null;
		$export = $this->request()['export'] ? true : false;
		$getCount = $this->request()['fullcount'] && $this->request()['fullcount'] != 'false' ? true : false;

		$keys = [];

		if ($page == 1) {
			$offset = '0';
		} else {
			$offset = ($page-1) * $limit;
		}

		$q = '
			SELECT
				-WILD-
			FROM `order`
			left JOIN restaurant ON restaurant.id_restaurant=`order`.id_restaurant
			left JOIN restaurant_community ON restaurant_community.id_restaurant=restaurant.id_restaurant
			left JOIN community ON community.id_community=restaurant_community.id_community
			
			LEFT JOIN order_action ON order_action.id_order=`order`.id_order
			LEFT JOIN admin ON admin.id_admin=order_action.id_admin

			WHERE `order`.id_restaurant IS NOT NULL
		';

//			LEFT JOIN ( SELECT MAX( id_support ) AS id_support, id_order FROM support WHERE id_order IS NOT NULL GROUP BY id_order ) support ON support.id_order = `order`.id_order

		if (!c::admin()->permission()->check(['global', 'orders-all', 'orders-list-page'])) {
			// Order::deliveryOrders( $lastHours );
			$q .= '
				AND order_admin.id_admin = "'.c::admin()->id_admin.'"
			';
		}

		if ($user) {
			$q .= '
				AND `order`.id_user=?
			';
			$keys[] = $user;
		}

		if ($phone) {
			$q .= '
				AND `order`.phone=?
			';
			$keys[] = $phone;
		}

		if ($community) {
			$q .= '
				AND community.id_community=?
			';
			$keys[] = $community;
		}

		if ($restaurant) {
			$q .= '
				AND restaurant.id_restaurant=?
			';
			$keys[] = $restaurant;
		}
		
		if ($datestart) {
			$datestart = date('Y-m-d', strtotime($datestart));
			$q .= '
				AND `order`.date >= ?
			';
			$keys[] = $datestart;
		}
		
		if ($dateend) {
			$dateend = date('Y-m-d', strtotime($dateend));
			$q .= '
				AND `order`.date <= date_add(?, interval 1 day)
			';
			$keys[] = $dateend;
		}

		if ($search) {

			// Keys
			switch ( true ) {

				// Keys
				case ( strpos( $search, 'phone:' ) !== false ):
					$phone = str_replace( 'phone:' , '', $search );
					$phone = str_replace( '-' , '', $phone );
					$q .= 'AND order.phone = ? ';
					$keys[] = $phone;
					break;

				default:
					$s = Crunchbutton_Query::search([
						'search' => stripslashes($search),
						'fields' => [
							'restaurant.name' => 'like',
							'admin.name' => 'like',
							'`order`.phone' => 'like',
							'`order`.name' => 'like',
							'`order`.address' => 'like',
							'`order`.notes' => 'like',
							'`order`.id_order' => 'inteq'
						]
					]);
					$q .= $s['query'];
					$keys = array_merge($keys, $s['keys']);
					break;
			}
		}


		$count = 0;

		// get the count
		if ($getCount) {
			$r = c::db()->query(str_replace('-WILD-','COUNT(DISTINCT `order`.id_order) as c', $q), $keys);
			while ($c = $r->fetch()) {
				$count = $c->c;
			}
		}

		$q .= '
			GROUP BY `order`.id_order
			ORDER BY `order`.id_order DESC
		';
		if (!$export) {
			$q .= '
				LIMIT ?
				OFFSET ?
			';
			$keys[] = $getCount ? $limit : $limit+1;
			$keys[] = $offset;
		}

		// do the query
		$data = [];
		$query = str_replace('-WILD-','
			`order`.*,
			max(restaurant.name) as _restaurant_name,
			max(restaurant.phone) as _restaurant_phone,
			max(restaurant.permalink) as _restaurant_permalink,
			bool_and(restaurant.confirmation) as _restaurant_confirmation,
			max(community.name )as _community_name,
			max(community.permalink) as _community_permalink,
			max(community.id_community) as _community_id,
			max(admin.name) as _driver_name,
			max(admin.id_admin) as _driver_id
		', $q);

		$r = c::db()->query($query, $keys);

		$i = 1;
		$more = false;

		while ($o = $r->fetch()) {

			if (!$export && !$getCount && $i == $limit + 1) {
				$more = true;
				break;
			}

			$o->status = Order::o( $o->id_order )->status()->last();
			$restaurant = Restaurant::o( $o->id_restaurant );
			$o->delivery_it_self = $restaurant->deliveryItSelf();
			// See: #3763
			if( !$o->lat ){
				$user = User::o( $o->id_user );
				if( $user->id_user && $user->location_lon == $o->lon && $user->location_lat ){
					$o->lat = $user->location_lat;
				} else if( !$o->lon && $user->location_lon && $user->location_lat ){
					$o->lon = $user->location_lon;
					$o->lat = $user->location_lat;
				}
			}

			$boolFields = ['confirmed','refunded','delivery_service','do_not_reimburse_driver','paid_with_cb_card','pay_if_refunded','asked_to_call'];
			
			foreach (get_object_vars($o) as $key => $value) {
				$type = gettype($value);

				if (($type == 'string' || $type == 'integer') && in_array($key, $boolFields)) {
					$o->{$key} = $o->{$key} ? true : false;
				} elseif ($type == 'string' && is_numeric($value)) {
					if (strpos($value, '.') === false) {
						$o->{$key} = intval($o->{$key});
					} else {
						$o->{$key} = floatval($o->{$key});
					}
				}
				
			}

			$data[] = $o;
			$i++;
		}
		
		$pages = ceil($count / $limit);
		
		if ($export) {
			// @todo: make these layouts actulay do something. they are from old cockpit and need to be migrated
			c::view()->orders = $data;
			c::view()->layout('layout/csv');
			c::view()->display('orders/csv', ['display' => true, 'filter' => false]);
		} else {

			echo json_encode([
				'more' => $getCount ? $pages > $page : $more,
				'count' => intval($count),
				'pages' => $pages,
				'page' => intval($page),
				'results' => $data
			], JSON_PRETTY_PRINT);
			// this aparantly doesnt always work JSON_NUMERIC_CHECK 
		}

	}
}