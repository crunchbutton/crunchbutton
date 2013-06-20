<?php

class Controller_home_charts extends Crunchbutton_Controller_Account {

	public $activeUsersInterval = 30; // Days
	public $weeksToShowDefault = 20;

	public function init() {

		$chart = c::getPagePiece(2);

		switch ( $chart ) {
			case 'new-users-per-week':
				$weeks = $this->maxMinWeeks();
				$query = '';
				$union = '';

				$maxMinWeeks = $this->maxMinWeeks();
				$maxWeeks = sizeof( $maxMinWeeks );
				$weeks = ( $_REQUEST[ 'weeks' ] ? $_REQUEST[ 'weeks' ] : $this->weeksToShowDefault );
				$actual = $maxMinWeeks[ $weeks ];

				$count = 0;
				foreach( $maxMinWeeks as $week ){
					if( $count > $weeks ){
						continue;
					}
					$query .= $union . "SELECT 'Week {$week}' AS Label, COUNT(*) AS Users, 'Active users' FROM (
																SELECT 
																	COUNT(*) orders, u.phone, o.date, u.id_user
																FROM 
																	`order` o 
																INNER JOIN user u ON u.id_user = o.id_user
																LEFT JOIN community c ON o.id_community = c.id_community
																WHERE 
																		o.date <= STR_TO_DATE('{$week} Saturday', '%X%V %W') 
																	AND  
																		c.name IS NOT NULL
																	AND 
																		c.name != 'Testing' 
																	GROUP BY u.phone 
																	HAVING orders = 1	
																) Orders
															WHERE 
																Orders.date BETWEEN STR_TO_DATE('{$week} Sunday', '%X%V %W') AND STR_TO_DATE('{$week} Saturday', '%X%V %W')";
						$union = ' UNION ';
						$count++;
						
				}
				c::view()->display('charts/column', ['set' => [
					'chartId' => $chart,
					'data' => c::db()->get( $query  ),
					'title' => 'New users per week',
					'unit' => 'users',
					'maxWeeks' => $maxWeeks,
					'weeks' => $weeks,
				]]); 
			break;
			case 'new-users-per-week-community':
				$weeks = $this->maxMinWeeks();
				$query = '';
				$union = '';

				$maxMinWeeks = $this->maxMinWeeks();
				$maxWeeks = sizeof( $maxMinWeeks );
				$weeks = ( $_REQUEST[ 'weeks' ] ? $_REQUEST[ 'weeks' ] : $this->weeksToShowDefault );
				$actual = $maxMinWeeks[ $weeks ];

				$count = 0;
				foreach( $maxMinWeeks as $week ){
					if( $count > $weeks ){
						continue;
					}
					$query .= $union . "SELECT 'Week {$week}' AS Label, COUNT(*) AS Users, Orders.name AS `Community` FROM (
																SELECT 
																	COUNT(*) orders, u.phone, o.date, u.id_user, c.name
																FROM 
																	`order` o 
																INNER JOIN user u ON u.id_user = o.id_user
																LEFT JOIN community c ON o.id_community = c.id_community
																WHERE 
																		o.date <= STR_TO_DATE('{$week} Saturday', '%X%V %W') 
																	AND  
																		c.name IS NOT NULL 
																	AND 
																		c.name != 'Testing' 
																	AND c.id_community IN ( 1, 4 )
																	GROUP BY u.phone 
																	HAVING orders = 1
																) Orders
															WHERE 
																Orders.date BETWEEN STR_TO_DATE('{$week} Sunday', '%X%V %W') AND STR_TO_DATE('{$week} Saturday', '%X%V %W') 
															GROUP BY Orders.name";
						$union = ' UNION ';
						$count++;
				}
				c::view()->display('charts/column', ['set' => [
					'chartId' => $chart,
					'data' => c::db()->get( $query  ),
					'title' => 'New users per week by community',
					'unit' => 'users',
					'maxWeeks' => $maxWeeks,
					'weeks' => $weeks,
				]]); 
			break;

			case 'active-users-per-week':
				$weeks = $this->maxMinWeeks();
				$query = '';
				$union = '';

				$maxMinWeeks = $this->maxMinWeeks();
				$maxWeeks = sizeof( $maxMinWeeks );
				$weeks = ( $_REQUEST[ 'weeks' ] ? $_REQUEST[ 'weeks' ] : $this->weeksToShowDefault );
				$actual = $maxMinWeeks[ $weeks ];

				$count = 0;
				foreach( $maxMinWeeks as $week ){
					if( $count > $weeks ){
						continue;
					}
					$query .= $union . "SELECT 'Week {$week}' AS Label, COUNT(*) AS Users, 'ActiveUsers' FROM (
																SELECT 
																	u.phone, o.date, u.id_user, c.name
																FROM 
																	`order` o 
																INNER JOIN user u ON u.id_user = o.id_user
																LEFT JOIN community c ON o.id_community = c.id_community
																WHERE 
																		o.date <= STR_TO_DATE('{$week} Saturday', '%X%V %W') 
																	AND 
																		o.date >= STR_TO_DATE('{$week} Saturday', '%X%V %W')  - INTERVAL {$this->activeUsersInterval} DAY 
																	AND 
																		c.name IS NOT NULL 
																	AND 
																		c.name != 'Testing' 
																GROUP BY u.phone ) ActiveUsers";
					$union = ' UNION ';
					$count++;	
				}

				c::view()->display('charts/column', ['set' => [
					'chartId' => $chart,
					'data' => c::db()->get( $query  ),
					'title' => 'Active users per week',
					'unit' => 'users',
					'maxWeeks' => $maxWeeks,
					'weeks' => $weeks,
				]]); 
			break;

		case 'active-users-per-week-by-community':
				$weeks = $this->maxMinWeeks();
				$query = '';
				$union = '';

				$maxMinWeeks = $this->maxMinWeeks();
				$maxWeeks = sizeof( $maxMinWeeks );
				$weeks = ( $_REQUEST[ 'weeks' ] ? $_REQUEST[ 'weeks' ] : $this->weeksToShowDefault );
				$actual = $maxMinWeeks[ $weeks ];

				$count = 0;
				foreach( $maxMinWeeks as $week ){
					if( $count > $weeks ){
						continue;
					}
					$query .= $union . "SELECT 'Week {$week}' AS Label, COUNT(*) AS Users, ActiveUsers.name AS 'Community' FROM (
																SELECT 
																	u.phone, o.date, u.id_user, c.name
																FROM 
																	`order` o 
																INNER JOIN user u ON u.id_user = o.id_user
																LEFT JOIN community c ON o.id_community = c.id_community
																WHERE 
																		o.date <= STR_TO_DATE('{$week} Saturday', '%X%V %W') 
																	AND 
																		o.date >= STR_TO_DATE('{$week} Saturday', '%X%V %W')  - INTERVAL {$this->activeUsersInterval} DAY 
																	AND 
																		c.name IS NOT NULL 
																	AND 
																		c.name != 'Testing' 
																	AND c.id_community IN ( 1, 4 )
																GROUP BY u.phone ) ActiveUsers
														 GROUP BY ActiveUsers.name";
					$union = ' UNION ';
						$count++;
				}

				c::view()->display('charts/column', ['set' => [
					'chartId' => $chart,
					'data' => c::db()->get( $query  ),
					'title' => 'Active users per week by Community',
					'unit' => 'users',
					'maxWeeks' => $maxWeeks,
					'weeks' => $weeks,
				]]); 
			break;

			case 'active-users-by-community':
					$query = 'SELECT 
											"Users" AS Label,
											COUNT( DISTINCT( ( u.phone ) ) ) AS Users, 
											c.name AS `Community`
										FROM `order` o 
										INNER JOIN user u ON u.id_user = o.id_user 
										LEFT JOIN community c ON o.id_community = c.id_community
										WHERE c.name IS NOT NULL AND c.name != "Testing" 
											AND c.id_community IN ( 1, 4 )
											AND  o.date BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE()
										GROUP BY o.id_community ';
					c::view()->display('charts/pie', ['set' => [
						'chartId' => $chart,
						'data' => c::db()->get( $query  ),
						'title' => 'Active users per community',
						'unit' => 'users',
						'maxWeeks' => $maxWeeks,
						'weeks' => $weeks,
					]]);  
				break;

			case 'users-per-week-by-community':
					$maxMinWeeks = $this->maxMinWeeks();
					$maxWeeks = sizeof( $maxMinWeeks );
					$weeks = ( $_REQUEST[ 'weeks' ] ? $_REQUEST[ 'weeks' ] : $this->weeksToShowDefault );
					$actual = $maxMinWeeks[ ( $weeks >= $maxWeek ? ( $weeks - 1 ) : $weeks ) ];
					$query = 'SELECT 
											CONCAT( "Week ", YEARWEEK( date ) ) AS `week`, 
											COUNT( DISTINCT( ( u.phone ) ) ) AS Users, 
											c.name AS `Community`
										FROM `order` o 
										INNER JOIN user u ON u.id_user = o.id_user 
										LEFT JOIN community c ON o.id_community = c.id_community
										WHERE c.name IS NOT NULL  AND c.name != "Testing" 
											AND c.id_community IN ( 1, 4 )
										AND YEARWEEK(o.date) >= ' . $actual . '
										GROUP BY YEARWEEK(o.date), o.id_community 
										ORDER BY YEARWEEK(o.date) DESC';

					c::view()->display('charts/column', ['set' => [
						'chartId' => $chart,
						'data' => c::db()->get( $query  ),
						'title' => 'Unique users per week by Community',
						'unit' => 'users',
						'maxWeeks' => $maxWeeks,
						'ignoreWeekSum' => true,
						'weeks' => $weeks,
					]]); 
				break;

			case 'users-per-week':
					$maxMinWeeks = $this->maxMinWeeks();
					$maxWeeks = sizeof( $maxMinWeeks );
					$weeks = ( $_REQUEST[ 'weeks' ] ? $_REQUEST[ 'weeks' ] : $this->weeksToShowDefault );
					$actual = $maxMinWeeks[ ( $weeks >= $maxWeek ? ( $weeks - 1 ) : $weeks ) ];
					$query = 'SELECT 
											CONCAT( "Week ", YEARWEEK( date ) ) AS `week`, 
											COUNT( DISTINCT( ( u.phone ) ) ) AS Users, 
											"Users" AS label 
										FROM `order` o 
										INNER JOIN user u ON u.id_user = o.id_user 
										LEFT JOIN community c ON o.id_community = c.id_community
										WHERE c.name IS NOT NULL  AND c.name != "Testing"  AND YEARWEEK(o.date) >= ' . $actual . '
										GROUP BY YEARWEEK(o.date) 
										ORDER BY YEARWEEK(o.date) DESC';
					c::view()->display('charts/column', ['set' => [
						'chartId' => $chart,
						'data' => c::db()->get( $query  ),
						'title' => 'Unique users per week',
						'unit' => 'users',
						'maxWeeks' => $maxWeeks,
						'weeks' => $weeks,
						'ignoreWeekSum' => true,
					]]); 
				break;

			case 'orders-by-user-week':
					$maxMinWeeks = $this->maxMinWeeks();
					$maxWeeks = sizeof( $maxMinWeeks );
					$weeks = ( $_REQUEST[ 'weeks' ] ? $_REQUEST[ 'weeks' ] : $this->weeksToShowDefault );
					$actual = $maxMinWeeks[ ( $weeks >= $maxWeek ? ( $weeks - 1 ) : $weeks ) ];
					$query = 'SELECT 
												CONCAT( "Week ", YEARWEEK( date ) ) AS `week`, 
												CAST(COUNT(*) / COUNT( DISTINCT( ( u.phone ) ) ) AS DECIMAL( 14, 2 ) ) "Orders By User",
												"Orders by User" AS label 
										FROM `order` o
										INNER JOIN user u ON u.id_user = o.id_user 
										LEFT JOIN community c ON o.id_community = c.id_community
										WHERE c.name IS NOT NULL  AND c.name != "Testing"  AND YEARWEEK(o.date) >= ' . $actual . '
										GROUP BY YEARWEEK(date) 
										ORDER BY YEARWEEK(date) DESC';

					c::view()->display('charts/column', ['set' => [
						'chartId' => $chart,
						'data' => c::db()->get( $query  ),
						'title' => 'Orders by User per week',
						'unit' => 'orders',
						'ignoreWeekSum' => true,
						'maxWeeks' => $maxWeeks,
						'weeks' => $weeks,
					]]); 
				break;

			case 'orders-per-week':
					$maxMinWeeks = $this->maxMinWeeks();
					$maxWeeks = sizeof( $maxMinWeeks );
					$weeks = ( $_REQUEST[ 'weeks' ] ? $_REQUEST[ 'weeks' ] : $this->weeksToShowDefault );
					$actual = $maxMinWeeks[ ( $weeks >= $maxWeek ? ( $weeks - 1 ) : $weeks ) ];
					$query = 'SELECT 
												CONCAT( "Week ", YEARWEEK( date ) ) AS `week`, 
												COUNT(*) AS Orders, 
												"Orders" AS label 
										FROM `order` o
										INNER JOIN user u ON u.id_user = o.id_user 
										LEFT JOIN community c ON o.id_community = c.id_community
										WHERE c.name IS NOT NULL  AND c.name != "Testing"  AND YEARWEEK(o.date) >= ' . $actual . '
										GROUP BY YEARWEEK(date) 
										ORDER BY YEARWEEK(date) DESC';

					c::view()->display('charts/column', ['set' => [
						'chartId' => $chart,
						'data' => c::db()->get( $query  ),
						'title' => 'Orders per week',
						'unit' => 'orders',
						'maxWeeks' => $maxWeeks,
						'ignoreWeekSum' => true,
						'weeks' => $weeks,
					]]); 
				break;

			case 'gross-revenue':
					$maxMinWeeks = $this->maxMinWeeks();
					$maxWeeks = sizeof( $maxMinWeeks );
					$weeks = ( $_REQUEST[ 'weeks' ] ? $_REQUEST[ 'weeks' ] : $this->weeksToShowDefault );
					$actual = $maxMinWeeks[ ( $weeks >= $maxWeek ? ( $weeks - 1 ) : $weeks ) ];
					$query = 'SELECT 
											CONCAT( "Week ", YEARWEEK( date ) ) AS `week`, 
											CAST( SUM( final_price ) AS DECIMAL( 14, 2 ) ) AS "US$", 
											"US$" AS label 
										FROM `order` o
										LEFT JOIN community c ON o.id_community = c.id_community
										WHERE c.name IS NOT NULL  AND c.name != "Testing"  AND YEARWEEK(o.date) >= ' . $actual . '
										GROUP BY YEARWEEK(date) 
										ORDER BY YEARWEEK(date) DESC';
					c::view()->display('charts/column', ['set' => [
						'chartId' => $chart,
						'data' => c::db()->get( $query  ),
						'title' => 'Gross revenue',
						'unit' => '',
						'maxWeeks' => $maxWeeks,
						'ignoreWeekSum' => true,
						'weeks' => $weeks,
					]]); 
				break;

			case 'orders-by-date-by-community':
					$query = 'SELECT
											DATE_FORMAT( CONVERT_TZ( `date`, "-8:00", "-5:00" ), "%W" ) AS `Day`,
											COUNT(*) AS `Orders`,
											community.name AS `Community`
										FROM `order`
										LEFT JOIN community USING( id_community )
										WHERE
											env = "live"
											AND community.name IS NOT NULL
											AND community.name != "Testing"
											AND community.id_community IN ( 1, 4 )
										GROUP BY DATE_FORMAT( CONVERT_TZ( `date`, "-8:00", "-5:00" ), "%W" ), id_community
										ORDER BY DATE_FORMAT( CONVERT_TZ( `date`, "-8:00", "-5:00" ), "%Y%m%d" ), id_community';
					c::view()->display('charts/area', ['set' => [
						'chartId' => $chart,
						'data' => c::db()->get( $query  ),
						'title' => 'Orders by day by community',
						'unit' => 'orders',
					]]); 
				break;

			default:
				break;
		}
	}

	private function weeks(){
		$query = 'SELECT COUNT( DISTINCT( YEARWEEK( date) ) ) AS weeks FROM `order`';
		$result = c::db()->get( $query );
		return $result->_items[0]->weeks; 
	}
	private function maxMinWeeks(){
		$query = 'SELECT DISTINCT( YEARWEEK( o.date ) ) week FROM `order` o ORDER BY week DESC';
		$results = c::db()->get( $query );
		$weeks = array();
		foreach ( $results as $result ) {
			if( !$result->week ){
				continue;
			}
			$weeks[] = $result->week;
		}
		return $weeks; 
	}
	private function tooltipWeekJS(){
		return "function() {
						var total = 0;
						var body = '';
						$.each( this.points, function( i, point ) {
							body += '<br/><span style=\"color:' + point.series.color + '\">' +  point.series.name + '</span>: ' + point.y + ' users (' + point.percentage.toFixed(2) + '%)';
							total += point.y;
						});
						var html = '<b>Total: ' + total + ' users - ' + this.x + '</b>' + body;
						return html;}";
	}
}

/*


/*
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
*/