<?php

class Controller_api_metrics_example extends Crunchbutton_Controller_RestAccount {

	public function init() {
		if ($this->method() != 'get') {
			exit;
		}

		if (!c::admin()->permission()->check(['global', 'support-all', 'support-view', 'support-crud'])) {
			$this->error(401, true);
		}

		// cant get postgres to work right now
		/*
		$r = c::app()->metricsDB()->query('select * from tests');
		foreach ($r as $o) {
			var_dump($o);
		}
		exit;
		*/

		$days = $this->request()['days'] ? $this->request()['days'] : 90;
		$keys = [];
		$keys[] = $days;

		$data = [];
		$q = '
			select c as "Orders", name as "Community" from
			(
				select count(*) as c, community.name from `order`
				left join restaurant on restaurant.id_restaurant=`order`.id_restaurant
				left join restaurant_community on restaurant_community.id_restaurant=restaurant.id_restaurant
				left join community on community.id_community=restaurant_community.id_community
				where community.active=true
				and community.name not like "%test%"
				and restaurant.name not like "%test%"
				and order.name not like "%test%"
				and community.name not like "%apply%"
				and restaurant.delivery_service=1
				and order.date > date_sub(now(), interval ? day)
				group by community.id_community
				order by c desc
			) t
			where c > 10
			limit 10
		';

		$r = c::db()->query( $q, $keys );
		while ($o = $r->fetch()) {
			$data[] = (array)$o;
		}

		echo json_encode([
			'title' => 'Example',
			'data' => $data
		]);

	}
}