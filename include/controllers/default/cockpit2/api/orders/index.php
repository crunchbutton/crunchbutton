<?php

class Controller_api_orders extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if ( !c::admin()->permission()->check(['global','orders-all','orders-list-page'] ) && !c::admin()->isCommunityManager() ) {
			$this->error(401, true);
		}

		if ($this->method() != 'get') {
			exit;
		}

		// manual query is faster than using the Order->exports

		// @todo: merge this with Order::find when we get rid of old cockpit/orders

		$limit = $this->request()['limit'] ? $this->request()['limit'] : 20;
		$search = $this->request()['search'] ? $this->request()['search'] : '';
		$page = $this->request()['page'] ? $this->request()['page'] : 1;
		$user = $this->request()['user'] ? $this->request()['user'] : null;
		$driver = $this->request()['driver'] ? $this->request()['driver'] : null;
		$phone = $this->request()['phone'] ? $this->request()['phone'] : null;
		$datestart = $this->request()['datestart'] ? $this->request()['datestart'] : null;
		$dateend = $this->request()['dateend'] ? $this->request()['dateend'] : null;
		$restaurant = $this->request()['restaurant'] ? $this->request()['restaurant'] : null;
		$community = $this->request()['community'] ? $this->request()['community'] : null;
		$type = $this->request()['type'] ? $this->request()['type'] : 'all';
		$export = $this->request()['export'] ? true : false;
		$getCount = $this->request()['fullcount'] && $this->request()['fullcount'] != 'false' ? true : false;

		if( intval( $limit ) > 200 && !$export ){
			$limit = 200;
		}

		$campusManager = $this->request()['campusManager'] ? $this->request()['campusManager'] : false;

		if ( !c::admin()->permission()->check(['global','orders-all'] ) && c::admin()->isCampusManager() || $campusManager ) {
			$campusManager = true;
			$community = c::user()->getMarketingRepGroups();
		}

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
			LEFT JOIN phone ON phone.id_phone=`order`.id_phone

		';

		if( $driver ){
			$q .= '
				INNER JOIN order_action oa ON oa.id_order = `order`.id_order AND oa.type = ? AND oa.id_admin = ?
			';
			$keys[] = Crunchbutton_Order_Action::DELIVERY_ACCEPTED;
			$keys[] = $driver;
		}

		$q .= '
			WHERE `order`.id_restaurant IS NOT NULL
		';

		if( $type == 'pre-orders' ){
			$q .= '
				AND `order`.preordered = 1
			';
		}

//			LEFT JOIN ( SELECT MAX( id_support ) AS id_support, id_order FROM support WHERE id_order IS NOT NULL GROUP BY id_order ) support ON support.id_order = `order`.id_order

		if ($user) {
			$q .= '
				AND `order`.id_user=?
			';
			$keys[] = $user;
		}

		if ($phone) {
			$q .= '
				AND phone.phone=?
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
					$q .= 'AND phone.phone = ? ';
					$keys[] = $phone;
					break;

				default:
					$s = Crunchbutton_Query::search([
						'search' => stripslashes($search),
						'fields' => [
							'restaurant.name' => 'like',
							'admin.name' => 'like',
							'community.name' => 'like',
							'`order`.name' => 'like',
							'`order`.address' => 'like',
							'`order`.notes' => 'like',
							'`order`.id_order' => 'inteq',
							'`order`.txn' => 'eq',
							'`order`.phone' => 'likephone',
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

		$q .= '
			LIMIT '.intval($getCount ? $limit : $limit+1).'
			OFFSET '.intval($offset).'
		';

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
			max(admin.id_admin) as _driver_id,
			restaurant.formal_relationship
		', $q);

		$r = c::db()->query($query, $keys);

		$i = 1;
		$more = false;

		while ($o = $r->fetch()) {

			if( $export ){
				$o = Order::o( $o->id_order );
			} else {
				if (!$export && !$getCount && $i == $limit + 1) {
					$more = true;
					break;
				}
				$order = Order::o( $o->id_order );
				$o->status = $order->status()->last();

				if( $o->status['driver']['id_admin'] ){
					$driver = Admin::o( $o->status['driver']['id_admin'] );
					$o->vehicle = $driver->vehicle();
				}


				$o->minutes_to_delivery = $order->minutesToDelivery();
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

				if( $order->campus_cash ){
					$o->campus_cash_charged = false;
					if( $order->campus_cash_charged() ){
						$o->campus_cash_charged = true;
					}
				}

				if( $campusManager ){
					$o->orders_by_phone = Order::totalOrdersByPhone( $o->phone );
					$o->new_customer = ( $o->orders_by_phone == 1 );
					$o->returning_customer = ( $o->orders_by_phone > 1 );
				}

				$boolFields = ['confirmed','refunded','delivery_service','do_not_reimburse_driver','paid_with_cb_card','pay_if_refunded','asked_to_call', 'formal_relationship', 'campus_cash'];

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
			}

			if( $o->preordered && $o->preordered_date ){
				$o->date = $o->preordered_date;
			}

			// remove unnecessary properties
			if( $campusManager ){
				$remove = [ 'price', 'price_plus_delivery_markup', 'final_price', 'final_price_plus_delivery_markup', 'delivery_service_markup', 'delivery_service_markup_value', 'tax', 'tip', 'txn', 'delivery_fee', 'processor', 'tip_type', 'pay_if_refunded', 'id_agent', 'id_session', 'fee_restaurant', 'paid_with_cb_card', 'id_user_payment_type', 'asked_to_call', 'lon', 'lat', 'reimburse_cash_order', 'do_not_pay_restaurant', 'do_not_pay_driver', 'reward_delivery_free', 'likely_test', 'geomatched', 'id_phone', 'id_user', 'service_fee', 'do_not_reimburse_driver', 'id_address', 'preordered' ];
				foreach( $remove as $key ){
					unset( $o->$key );
				}

			}

			$data[] = $o;
			$i++;
		}

		$pages = ceil($count / $limit);

		if ($export) {
			c::view()->orders = $data;
			c::view()->title = 'Orders_Export_' . date( 'Y-m-d' );
			c::view()->layout('layout/csv');
			c::view()->display('csv/orders', ['display' => true, 'filter' => false]);
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
