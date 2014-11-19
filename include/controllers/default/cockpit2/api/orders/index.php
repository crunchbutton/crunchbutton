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
		
		if (!c::admin()->permission()->check(['global', 'orders-all', 'orders-list-page'])) {
			// Order::deliveryOrders( $lastHours );
			$q .= '
				AND order_admin.id_admin = "'.c::admin()->id_admin.'"
			';
		}
		
		if ($search) {
			$words = preg_split("/[\s,]*\\\"([^\\\"]+)\\\"[\s,]*|" . "[\s,]*'([^']+)'[\s,]*|" . "[\s,]+/", $search, 0, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
			foreach ($words as $word) {
				$sq .= ($sq ? ' OR ' : '').'(
					restaurant.name LIKE "%'.$word.'%"
					OR admin.name LIKE "%'.$word.'%"
					OR order.name LIKE "%'.$word.'%"
				)';
			}
			$q .= '
				AND ('.$sq.')
			';
		}
		
		$q .= '
			GROUP BY `order`.id_order
			ORDER BY `order`.id_order DESC
			LIMIT '.$limit.'
		';

		// @todo: fix speed
		// echo Order::q($q)->json();
		/*
		foreach (Order::q($q) as $o) {
			$d[] = $o->exports();
		}
		echo json_encode($d);
		*/

		$r = c::db()->query($q);
		$d = [];
		while ($o = $r->fetch()) {
			$o->status = Order::o($o->id_order)->status()->last();
			$d[] = $o;
		}
		echo json_encode($d);
		exit;
		

	}
}