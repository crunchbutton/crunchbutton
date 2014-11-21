<?php

class Controller_api_orders extends Crunchbutton_Controller_RestAccount {

	public function init() {
		if ($this->method() != 'get') {
			exit;
		}
		
		// manual query is faster than using the Order->exports
		
		// @todo: merge this with Order::find when we get rid of old cockpit/orders
		
		$limit = $this->request()['limit'] ? c::db()->escape($this->request()['limit']) : 25;
		$search = $this->request()['search'] ? c::db()->escape($this->request()['search']) : '';
		$page = $this->request()['page'] ? c::db()->escape($this->request()['page']) : 1;
		$user = $this->request()['user'] ? c::db()->escape($this->request()['user']) : null;
		$phone = $this->request()['phone'] ? c::db()->escape($this->request()['phone']) : null;
		
		if ($page == 1) {
			$offset = '0';
		} else {
			$offset = ($page-1) * $limit;
		}

		
		/*
		$q = '
			SELECT
				`order`.*,
				restaurant.name as _restaurant_name,
				restaurant.community as _community_name,
				admin.name as _driver_name,
				admin.id_admin as _driver_id
			FROM `order`
			LEFT JOIN order_action ON order_action.id_order=`order`.id_order
			LEFT JOIN restaurant ON restaurant.id_restaurant=`order`.id_restaurant
			LEFT JOIN admin ON admin.id_admin=order_action.id_admin
			WHERE order_action.type != "delivery-rejected"
		';
		*/
		
		$q = '
			SELECT
				-WILD-
			FROM `order`
			LEFT JOIN order_action ON order_action.id_order=`order`.id_order
			LEFT JOIN restaurant ON restaurant.id_restaurant=`order`.id_restaurant
			LEFT JOIN admin ON admin.id_admin=order_action.id_admin
			WHERE `order`.id_restaurant IS NOT NULL
		';
		
		if (!c::admin()->permission()->check(['global', 'orders-all', 'orders-list-page'])) {
			// Order::deliveryOrders( $lastHours );
			$q .= '
				AND order_admin.id_admin = "'.c::admin()->id_admin.'"
			';
		}
		
		if ($user) {
			$q .= '
				AND `order`.id_user="'.$user.'"
			';
		}
		
		if ($phone) {
			$q .= '
				AND `order`.phone="'.$phone.'"
			';
		}
		
		if ($search) {
			$search  = stripslashes($search);
			$words = preg_split("/[\s,]*\\\"([^\\\"]+)\\\"[\s,]*|" . "[\s,]*'([^']+)'[\s,]*|" . "[\s,]+/", $search, 0, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

			foreach ($words as $word) {
				$sq .= ($sq ? ' AND ' : '').'(
					restaurant.name LIKE "%'.$word.'%"
					OR admin.name LIKE "%'.$word.'%"
					OR order.name LIKE "%'.$word.'%"
					OR order.phone LIKE "%'.$word.'%"
					OR order.address LIKE "%'.$word.'%"
					OR order.id_order LIKE "'.$word.'%"
				)';
			}
			$q .= '
				AND ('.$sq.')
			';
		}
		
		$q .= '
			GROUP BY `order`.id_order
		';

		// get the count
		$count = 0;
		$r = c::db()->query(str_replace('-WILD-','COUNT(*) c', $q));
		while ($c = $r->fetch()) {
			$count++;
		}

		$q .= '
			ORDER BY `order`.id_order DESC
			LIMIT '.$offset.', '.$limit.'
		';
		
		// do the query
		$data = [];
		$r = c::db()->query(str_replace('-WILD-','
			`order`.*,
			restaurant.name as _restaurant_name,
			restaurant.community as _community_name,
			admin.name as _driver_name,
			admin.id_admin as _driver_id
		', $q));

		while ($o = $r->fetch()) {
			$o->status = Order::o($o->id_order)->status()->last();
			$data[] = $o;
		}

		echo json_encode([
			'count' => intval($count),
			'pages' => ceil($count / $limit),
			'page' => $page,
			'results' => $data
		]);

	}
}