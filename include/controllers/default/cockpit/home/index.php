<?php

class Controller_home extends Crunchbutton_Controller_Account {
	public function init() {
		$data = [
			'all' => [
				'orders' => Order::q('select count(*) as c from `order` where env="live"')->c,
			],
			'day' => [
				'orders' => Order::q('
					select count(*) as c from `order`
					where
						env="live"
						and date > date_sub(now(), interval 24 hour)
				')->c,
				'tickets' => null
			]
		];

		c::view()->data = $data;
		
		$graphs['orders-by-date-by-community'] = [
			'title' => 'Orders by day by community',
			'type' => 'area',
			'data' => c::db()->get('
				SELECT
				    date_format(CONVERT_TZ(`date`, "-8:00","-5:00"), "%W") AS `Day`,
				    COUNT(*) AS `Orders`,
				    community.name AS `Community`
				FROM `order`
				LEFT JOIN community using(id_community)
				WHERE
					env="live"
					and community.name IS NOT NULL
					and community.name != "Testing"
				GROUP BY date_format(CONVERT_TZ(`date`, "-8:00","-5:00"), "%W"), id_community
				ORDER BY date_format(CONVERT_TZ(`date`, "-8:00","-5:00"), "%Y%m%d"), id_community
			')
		];
		

		c::view()->graphs = $graphs;
		c::view()->display('home/index');
	}
}