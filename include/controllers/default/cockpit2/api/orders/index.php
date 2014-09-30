<?php

class Controller_api_orders extends Crunchbutton_Controller_RestAccount {

	public function init() {
		if (!$this->method() == 'get') {
			exit;
		}
		
		// manual query is faster than using the Order->exports
		
		// @todo: merge this with Order::find when we get rid of old cockpit/orders
		
		$limit = $this->request()['limit'] ? c::db()->escape($this->request()['limit']) : 25;
		
		$q = '
			SELECT
				`order`.*,
				restaurant.name as _restaurant_name,
				admin.name as _driver_name
			FROM `order`
			LEFT JOIN order_action ON order_action.id_order=`order`.id_order
			LEFT JOIN restaurant ON restaurant.id_restaurant=`order`.id_restaurant
			LEFT JOIN admin ON admin.id_admin=order_action.id_admin
			WHERE order_action.type != "delivery-rejected"
		';
		
		if (!c::admin()->permission()->check(['global', 'orders-all', 'orders-list-page'])) {
			$q .= '
				AND order_admin.id_admin = "'.c::admin()->id_admin.'"
			';
		}
		
		$q .= '
			GROUP BY `order`.id_order
			ORDER BY order_action.timestamp DESC, `order`.date DESC
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
		while ($o = $r->fetch()) {
			$d[] = $o;
		}
		echo json_encode($d);
		exit;
		

	}
}