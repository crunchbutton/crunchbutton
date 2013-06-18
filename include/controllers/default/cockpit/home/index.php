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

		// Get the last users (different phones) by date
		$days = [ 1, 7, 30 ];
		$preQuery = 'SELECT 
										"Last {days} day(s)" AS day, COUNT(*) AS Users, IF( result.total > 1, "Returned", "1st time users" ) serie 
									FROM (
													SELECT filter.phone, filter.filter, total.total
														FROM ( SELECT u.phone, COUNT(*) AS filter FROM user u INNER JOIN `order` o ON u.id_user = o.id_user AND o.date BETWEEN CURDATE() - INTERVAL {days} DAY AND CURDATE() AND o.env = "live" GROUP BY u.phone ) filter
													INNER JOIN ( SELECT u.phone, COUNT(*) AS total FROM user u INNER JOIN `order` o ON u.id_user = o.id_user AND o.env = "live" GROUP BY u.phone ) total ON filter.phone = total.phone ) result
									GROUP BY serie';
		$query = '';
		$union = '';
		foreach ( $days as $day ) {
			$query .= $union . str_replace( '{days}', $day, $preQuery );
			$union = ' UNION ';
		}
		$graphs['active-users'] = [
			'title' => 'Active Users',
			'type' => 'area',
			'unit' => 'users',
			'tooltip' => "function() {
				var total = 0;
				var body = '';
				$.each( this.points, function( i, point ) {
					body += '<br/><span style=\"color:' + point.series.color + '\">' +  point.series.name + '</span>: ' + point.y + ' users (' + point.percentage.toFixed(2) + '%)';
					total += point.y;
				});
				var html = '<b>Total: ' + total + ' users</b>' + body;
				return html;}",
			'data' => c::db()->get( $query  )
		];

		// Orders per Active User (NOT counting 1st-time orders)
		$preQuery = 'SELECT  "Last {days} day(s)" AS day, SUM( total ) as Orders, "1st time users" as serie FROM (
									SELECT 
										u.phone, 
										COUNT(*) AS total,
										allorders.allorders 
									FROM user u 
										INNER JOIN `order` o ON u.id_user = o.id_user AND o.date BETWEEN CURDATE() - INTERVAL {days} DAY AND CURDATE() AND o.env = "live"
										INNER JOIN ( SELECT u.phone, COUNT(*) AS allorders FROM user u INNER JOIN `order` o ON u.id_user = o.id_user AND o.env = "live" GROUP BY u.phone ) allorders ON allorders.phone = u.phone 
										WHERE u.phone IS NOT NULL
										GROUP BY u.phone HAVING allorders = 1 ) orders
								UNION
								SELECT  "Last {days} day(s)" AS day, SUM( total ) as Orders, "Returned" as serie FROM (
								SELECT 
										u.phone, 
										COUNT(*) AS total,
										allorders.allorders 
									FROM user u 
									INNER JOIN `order` o ON u.id_user = o.id_user AND o.date BETWEEN CURDATE() - INTERVAL {days} DAY AND CURDATE() AND o.env = "live"
									INNER JOIN ( SELECT u.phone, COUNT(*) AS allorders FROM user u INNER JOIN `order` o ON u.id_user = o.id_user AND o.env = "live" GROUP BY u.phone ) allorders ON allorders.phone = u.phone 
									WHERE u.phone IS NOT NULL
										GROUP BY u.phone HAVING allorders > 1 ) orders';

		$query = '';
		$union = '';
		foreach ( $days as $day ) {			
			$query .= $union . str_replace( '{days}', $day, $preQuery );
			$union = ' UNION ';
		}

		$graphs['orders-per-active'] = [
			'title' => 'Orders per Users',
			'type' => 'column',
			'unit' => 'orders',
			'tooltip' => "function() {
				var total = 0;
				var body = '';
				$.each( this.points, function( i, point ) {
					body += '<br/><span style=\"color:' + point.series.color + '\">' +  point.series.name + '</span>: ' + point.y + ' orders (' + point.percentage.toFixed(2) + '%)';
					total += point.y;
				});
				var html = '<b>Total: ' + total + ' orders</b>' + body;
				return html;}",
			'data' => c::db()->get( $query  )
		];

		// Total orders
		$preQuery = 'SELECT "Last {days} day(s)" AS day, COUNT( * ) as Orders, "Orders" as serie FROM `order` o WHERE env="live" AND o.date BETWEEN CURDATE() - INTERVAL {days} DAY AND CURDATE()';

		$query = '';
		$union = '';
		foreach ( $days as $day ) {			
			$query .= $union . str_replace( '{days}', $day, $preQuery );
			$union = ' UNION ';
		}

		$graphs['total-orders'] = [
			'title' => 'Total orders',
			'type' => 'column',
			'unit' => 'orders',
			'tooltip' => "false",
			'data' => c::db()->get( $query  )
		];

		// Gross Revenue
		$preQuery = 'SELECT "Last {days} day(s)" AS day, CAST( SUM( o.final_price ) AS DECIMAL( 14, 2 ) ) as Dollar, "US$" as serie FROM `order` o WHERE env="live" AND o.date BETWEEN CURDATE() - INTERVAL {days} DAY AND CURDATE()';

		$query = '';
		$union = '';
		foreach ( $days as $day ) {			
			$query .= $union . str_replace( '{days}', $day, $preQuery );
			$union = ' UNION ';
		}

		$graphs['gross-revenue'] = [
			'title' => 'Gross Revenue',
			'type' => 'column',
			'unit' => '',
			'tooltip' => "false",
			'data' => c::db()->get( $query  )
		];

		c::view()->graphs = $graphs;
		c::view()->display('home/index');
	}
}