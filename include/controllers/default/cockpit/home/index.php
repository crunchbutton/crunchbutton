<?php

class Controller_home extends Crunchbutton_Controller_Account {
	public function init() {
	
		// select count(*) as users from `session` where date_activity>DATE_SUB(NOW(),INTERVAL 10 MINUTE);

		$data = [
			'all' => [
				'orders' => Order::q('select count(*) as c from `order` where env="live"')->c,
				'tickets' => Session_Twilio::q('
					select count(*) as c from support where status="open"
				')->c,
			],
			'day' => [
				'orders' => Order::q('
					select count(*) as c from `order`
					where
						env="live"
						and date > date_sub(now(), interval 24 hour)
				')->c
			],
			'week' => [
				'orders' => Order::q('
					select count(*) as c from `order`
					where
						env="live"
						and date > date_sub(now(), interval 1 week)
				')->c			
			]
		];

		c::view()->data = $data;
/*
		$graphs['orders-by-date-by-community'] = [
			'title' => 'Orders by day by community',
			'type' => 'area',
			'unit' => 'orders',
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
*/

		// Get the last users (diferent phones) by date
		$days = [1, 7,14,30,60,90, 365];
		$preQuery = 'SELECT 
										"{days} day(s)" AS day, COUNT(*) AS total, IF( result.total > 1, "Returned", "New" ) type 
									FROM (
													SELECT filter.phone, filter.filter, total.total
														FROM ( SELECT u.phone, COUNT(*) AS filter FROM user u INNER JOIN `order` o ON u.id_user = o.id_user and o.date BETWEEN CURDATE() - INTERVAL {days} DAY AND CURDATE() GROUP BY u.phone ) filter
													INNER JOIN ( SELECT u.phone, COUNT(*) AS total FROM user u INNER JOIN `order` o ON u.id_user = o.id_user GROUP BY u.phone ) total ON filter.phone = total.phone ) result
									GROUP BY type';

		$query = '';
		$union = '';
		foreach ( $days as $day ) {
			$query .= $union . str_replace( '{days}', $day, $preQuery );
			$union = ' UNION ';
		}
echo $query;exit;
		$graphs['active-users-by-date'] = [
			'title' => 'Active users by date',
			'type' => 'area',
			'unit' => 'users',
			'data' => c::db()->get( $query  )
		];



		c::view()->graphs = $graphs;
		c::view()->display('home/index');
	}
}