<?php

class Controller_home_charts extends Crunchbutton_Controller_Account {

	public $activeUsersInterval = 45; // Days
	public $queryIncludeCommunties = 'AND c.id_community IN (1, 4)';
	public $queryExcludeCommunties = "AND c.name != 'Testing' AND c.name IS NOT NULL";
	public $queryExcludeUsers = "AND o.name NOT LIKE '%test%' and o.name != 'Judd' and o.name != 'dave' and o.name != 'Nick' and o.name != 'Devin'";

	public function init() {

		$chart = c::getPagePiece(2);
		$title = c::getPagePiece(3);

		switch ( $chart ) {

			case 'new-users-per-week':
				$weeks = $this->maxMinWeeks();
				$query = '';
				$union = '';

				$maxMinWeeks = $this->maxMinWeeks();
				$maxWeeks = sizeof( $maxMinWeeks );
				$weeks = ( $_REQUEST[ 'weeks' ] ? $_REQUEST[ 'weeks' ] : $maxWeeks );
				$actual = $maxMinWeeks[ $weeks ];

				$count = 0;
				foreach( $maxMinWeeks as $week ){
					if( $count > $weeks ){
						continue;
					}
					
					$query .= $union . "SELECT 'Week {$week}' AS Label,
																		 COUNT(*) AS Total,
																		 'Users'
															FROM
																(SELECT COUNT(*) orders,
																				u.phone,
																				o.date,
																				u.id_user
																 FROM `order` o
																 INNER JOIN user u ON u.id_user = o.id_user
																 LEFT JOIN community c ON o.id_community = c.id_community
																 WHERE o.date <= STR_TO_DATE('{$week} Saturday', '%X%V %W')
																	 {$this->queryExcludeCommunties}
																	 {$this->queryExcludeUsers}
																 GROUP BY u.phone HAVING orders = 1) Orders
															WHERE Orders.date BETWEEN STR_TO_DATE('{$week} Sunday', '%X%V %W') AND STR_TO_DATE('{$week} Saturday', '%X%V %W')";
						$union = ' UNION ';
						$count++;
				}

				c::view()->display('charts/column', ['set' => [
					'chartId' => $chart,
					'data' => c::db()->get( $query ),
					'title' => $title,
					'unit' => 'users',
					'maxWeeks' => $maxWeeks,
					'weeks' => $weeks,
				]]); 
			break;

			case 'new-users-per-week-by-community':
				$weeks = $this->maxMinWeeks();
				$query = '';
				$union = '';

				$maxMinWeeks = $this->maxMinWeeks();
				$maxWeeks = sizeof( $maxMinWeeks );
				$weeks = ( $_REQUEST[ 'weeks' ] ? $_REQUEST[ 'weeks' ] : $maxWeeks );
				$actual = $maxMinWeeks[ $weeks ];

				$count = 0;
				foreach( $maxMinWeeks as $week ){
					if( $count > $weeks ){
						continue;
					}
					$query .= $union . "SELECT 'Week {$week}' AS Label,
																		 COUNT(*) AS Total,
																		 Orders.name AS `Community`
															FROM
																(SELECT COUNT(*) orders,
																				u.phone,
																				o.date,
																				u.id_user,
																				c.name
																 FROM `order` o
																 INNER JOIN user u ON u.id_user = o.id_user
																 LEFT JOIN community c ON o.id_community = c.id_community
																 WHERE o.date <= STR_TO_DATE('{$week} Saturday', '%X%V %W')
																	 {$this->queryExcludeCommunties}
																	 {$this->queryExcludeUsers}
																	 {$this->queryIncludeCommunties}
																 GROUP BY u.phone HAVING orders = 1) Orders
															WHERE Orders.date BETWEEN STR_TO_DATE('{$week} Sunday', '%X%V %W') AND STR_TO_DATE('{$week} Saturday', '%X%V %W')
															GROUP BY Orders.name";
						$union = ' UNION ';
						$count++;
				}
				c::view()->display('charts/column', ['set' => [
					'chartId' => $chart,
					'data' => c::db()->get( $query ),
					'title' => $title,
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
				$weeks = ( $_REQUEST[ 'weeks' ] ? $_REQUEST[ 'weeks' ] : $maxWeeks );
				$actual = $maxMinWeeks[ $weeks ];

				$count = 0;
				foreach( $maxMinWeeks as $week ){
					if( $count > $weeks ){
						continue;
					}
					$query .= $union . "SELECT 'Week {$week}' AS Label,
																	 COUNT(*) AS Total,
																	 'Users'
														FROM
															( SELECT u.phone,
																			 o.date,
																			 u.id_user,
																			 c.name
															 FROM `order` o
															 INNER JOIN user u ON u.id_user = o.id_user
															 LEFT JOIN community c ON o.id_community = c.id_community
															 WHERE o.date <= STR_TO_DATE('{$week} Saturday', '%X%V %W')
																 AND o.date >= STR_TO_DATE('{$week} Saturday', '%X%V %W') - INTERVAL {$this->activeUsersInterval} DAY
																 {$this->queryExcludeCommunties}
																 {$this->queryExcludeUsers}
															 GROUP BY u.phone) ActiveUsers";
					$union = ' UNION ';
					$count++;	
				}
				c::view()->display('charts/column', ['set' => [
					'chartId' => $chart,
					'data' => c::db()->get( $query ),
					'title' => $title,
					'unit' => 'users',
					'maxWeeks' => $maxWeeks,
					'weeks' => $weeks,
				]]); 
			break;

			case 'new-users-per-active-users':
				$weeks = $this->maxMinWeeks();
				$query = '';
				$union = '';

				$maxMinWeeks = $this->maxMinWeeks();
				$maxWeeks = sizeof( $maxMinWeeks );
				$weeks = ( $_REQUEST[ 'weeks' ] ? $_REQUEST[ 'weeks' ] : $maxWeeks );
				$actual = $maxMinWeeks[ $weeks ];

				$count = 0;
				foreach( $maxMinWeeks as $week ){
					if( $count > $weeks ){
						continue;
					}
					$query .= $union . "SELECT ActiveUsers.Label,
																		 CAST(NewUsers.NewUsers / ActiveUsers.ActiveUsers AS DECIMAL(14, 2)) AS 'New Users per Active Users',
																		 'Users',
																		 ActiveUsers.ActiveUsers,
																		 NewUsers.NewUsers
															FROM
																( SELECT 'Week {$week}' AS Label,
																				 COUNT(*) AS ActiveUsers
																 FROM
																	 ( SELECT u.phone,
																						o.date,
																						u.id_user,
																						c.name
																		FROM `order` o
																		INNER JOIN user u ON u.id_user = o.id_user
																		LEFT JOIN community c ON o.id_community = c.id_community
																		WHERE o.date <= STR_TO_DATE('{$week} Saturday', '%X%V %W')
																			AND o.date >= STR_TO_DATE('{$week} Saturday', '%X%V %W') - INTERVAL {$this->activeUsersInterval} DAY
																			{$this->queryExcludeCommunties}
																			{$this->queryExcludeUsers}
																		GROUP BY u.phone) ActiveUsers) ActiveUsers
															INNER JOIN
																(SELECT 'Week {$week}' AS Label,
																				COUNT(*) AS NewUsers,
																				'Users'
																 FROM
																	 (SELECT COUNT(*) orders,
																					 u.phone,
																					 o.date,
																					 u.id_user
																		FROM `order` o
																		INNER JOIN user u ON u.id_user = o.id_user
																		LEFT JOIN community c ON o.id_community = c.id_community
																		WHERE o.date <= STR_TO_DATE('{$week} Saturday', '%X%V %W')
																			{$this->queryExcludeCommunties}
																			{$this->queryExcludeUsers}
																		GROUP BY u.phone HAVING orders = 1) Orders
																 WHERE Orders.date BETWEEN STR_TO_DATE('{$week} Sunday', '%X%V %W') AND STR_TO_DATE('{$week} Saturday', '%X%V %W')) NewUsers ON NewUsers.Label = ActiveUsers.Label";
					$union = ' UNION ';
					$count++;	
				}
				
				c::view()->display('charts/column', ['set' => [
					'chartId' => $chart,
					'data' => c::db()->get( $query ),
					'title' => $title,
					'unit' => 'users',
					'maxWeeks' => $maxWeeks,
					'weeks' => $weeks,
				]]); 
			break;

		case 'new-users-per-active-users-by-community':
				$weeks = $this->maxMinWeeks();
				$query = '';
				$union = '';

				$maxMinWeeks = $this->maxMinWeeks();
				$maxWeeks = sizeof( $maxMinWeeks );
				$weeks = ( $_REQUEST[ 'weeks' ] ? $_REQUEST[ 'weeks' ] : $maxWeeks );
				$actual = $maxMinWeeks[ $weeks ];

				$count = 0;
				foreach( $maxMinWeeks as $week ){
					if( $count > $weeks ){
						continue;
					}

					$query .= $union . "SELECT ActiveUsers.Label,
																		 CAST(NewUsers.NewUsers / ActiveUsers.ActiveUsers AS DECIMAL(14, 2)) AS 'New Users per Active Users',
																		 ActiveUsers.Community,
																		 'Users',
																		 ActiveUsers.ActiveUsers,
																		 NewUsers.NewUsers
															FROM
																(SELECT 'Week {$week}' AS Label,
																				COUNT(*) AS ActiveUsers,
																				name AS Community
																 FROM
																	 (SELECT u.phone,
																					 o.date,
																					 u.id_user,
																					 c.name
																		FROM `order` o
																		INNER JOIN user u ON u.id_user = o.id_user
																		LEFT JOIN community c ON o.id_community = c.id_community
																		WHERE o.date <= STR_TO_DATE('{$week} Saturday', '%X%V %W')
																			AND o.date >= STR_TO_DATE('{$week} Saturday', '%X%V %W') - INTERVAL {$this->activeUsersInterval} DAY
																			{$this->queryExcludeCommunties}
																			{$this->queryExcludeUsers}
																			{$this->queryIncludeCommunties}
																		GROUP BY u.phone) ActiveUsers
																 GROUP BY Community) ActiveUsers
															LEFT JOIN
																( SELECT 'Week {$week}' AS Label,
																				 COUNT(*) AS NewUsers,
																				 Orders.name AS `Community`
																 FROM
																	 (SELECT COUNT(*) orders,
																					 u.phone,
																					 o.date,
																					 u.id_user,
																					 c.name
																		FROM `order` o
																		INNER JOIN user u ON u.id_user = o.id_user
																		LEFT JOIN community c ON o.id_community = c.id_community
																		WHERE o.date <= STR_TO_DATE('{$week} Saturday', '%X%V %W')
																			{$this->queryExcludeCommunties}
																			{$this->queryExcludeUsers}
																			{$this->queryIncludeCommunties}
																		GROUP BY u.phone HAVING orders = 1) Orders
																 WHERE Orders.date BETWEEN STR_TO_DATE('{$week} Sunday', '%X%V %W') AND STR_TO_DATE('{$week} Saturday', '%X%V %W')
																 GROUP BY Orders.name ) NewUsers ON NewUsers.Label = ActiveUsers.Label
															AND NewUsers.Community = ActiveUsers.Community
															GROUP BY ActiveUsers.Community";
					$union = ' UNION ';
					$count++;	
				}

				c::view()->display('charts/column', ['set' => [
					'chartId' => $chart,
					'data' => c::db()->get( $query ),
					'title' => $title,
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
				$weeks = ( $_REQUEST[ 'weeks' ] ? $_REQUEST[ 'weeks' ] : $maxWeeks );
				$actual = $maxMinWeeks[ $weeks ];

				$count = 0;
				foreach( $maxMinWeeks as $week ){
					if( $count > $weeks ){
						continue;
					}
					$query .= $union . "SELECT 'Week {$week}' AS Label,
																		 COUNT(*) AS Users,
																		 ActiveUsers.name AS 'Community'
															FROM
																( SELECT u.phone,
																				 o.date,
																				 u.id_user,
																				 c.name
																 FROM `order` o
																 INNER JOIN user u ON u.id_user = o.id_user
																 LEFT JOIN community c ON o.id_community = c.id_community
																 WHERE o.date <= STR_TO_DATE('{$week} Saturday', '%X%V %W')
																	 AND o.date >= STR_TO_DATE('{$week} Saturday', '%X%V %W') - INTERVAL {$this->activeUsersInterval} DAY
																	 {$this->queryExcludeCommunties}
																	 {$this->queryExcludeUsers}
																	 {$this->queryIncludeCommunties}
																 GROUP BY u.phone) ActiveUsers
															GROUP BY ActiveUsers.name";
					$union = ' UNION ';
					$count++;	
				}
				c::view()->display('charts/column', ['set' => [
					'chartId' => $chart,
					'data' => c::db()->get( $query ),
					'title' => $title,
					'unit' => 'users',
					'maxWeeks' => $maxWeeks,
					'weeks' => $weeks,
				]]); 
			break;

		case 'churn-rate':
				$weeks = $this->maxMinWeeks();
				$query = '';
				$union = '';

				$maxMinWeeks = $this->maxMinWeeks();
				$maxWeeks = sizeof( $maxMinWeeks );
				$weeks = ( $_REQUEST[ 'weeks' ] ? $_REQUEST[ 'weeks' ] : $maxWeeks );
				$actual = $maxMinWeeks[ $weeks ];

				$count = 0;
				foreach( $maxMinWeeks as $week ){
					if( $count > $weeks ){
						continue;
					}
					$query .= $union . "SELECT 'Week {$week}' AS Label,
																		 COUNT(*) AS Total,
																		 'Users'
															FROM
																( SELECT u.phone,
																				 o.date,
																				 u.id_user,
																				 c.name
																 FROM `order` o
																 INNER JOIN user u ON u.id_user = o.id_user
																 LEFT JOIN community c ON o.id_community = c.id_community
																 WHERE o.date <= STR_TO_DATE('{$week} Saturday', '%X%V %W')
																	 AND o.date >= STR_TO_DATE('{$week} Saturday', '%X%V %W') - INTERVAL {$this->activeUsersInterval} DAY
																	 {$this->queryExcludeCommunties}
																	 {$this->queryExcludeUsers}
																	 {$this->queryIncludeCommunties}
																 GROUP BY u.phone) ActiveUsers";
					$union = ' UNION ';
						$count++;
				}

				$data = c::db()->get( $query );
				$_weeks = [];
				foreach ( $data as $item ) {
					$_weeks[] = array( 
													'Label' => $item->Label, 
													'Total' => $item->Total
												);
				}
				$data = [];
				for( $i = 0; $i < sizeof( $_weeks ); $i++ ){
					$prev = $_weeks[ $i + 1];
					$actual = $_weeks[ $i ];
					if( $prev && $prev[ 'Total' ] > 0 ){
						$percent = number_format( ( $actual[ 'Total' ] * 100 ) / $prev[ 'Total' ], 2 );
					} else {
						$percent = 0;
					}
					if( $percent > 100 ){
						$percent = 0;
					}
					$churn = ( $actual[ 'Total' ] - $prev[ 'Total' ] ) * -1;
					$churn = ( $churn < 0 )	? 0 : $churn;
					$data[] = ( object ) array( 'Label' => $actual[ 'Label' ], 'Total' => $churn, 'Users' => 'Users' );
				}

				c::view()->display('charts/column', ['set' => [
					'chartId' => $chart,
					'data' => $data,
					'title' => $title,
					'unit' => 'active users lost',
					'maxWeeks' => $maxWeeks,
					'weeks' => $weeks,
				]]); 
			break;

			case 'active-users-by-community':
					$query = "SELECT 'Users' AS Label,
													 COUNT(DISTINCT((u.phone))) AS Users,
													 c.name AS `Community`
										FROM `order` o
										INNER JOIN user u ON u.id_user = o.id_user
										LEFT JOIN community c ON o.id_community = c.id_community
										WHERE o.date BETWEEN CURDATE() - INTERVAL {$this->activeUsersInterval} DAY AND CURDATE()
											{$this->queryExcludeCommunties}
											{$this->queryExcludeUsers}
											{$this->queryIncludeCommunties}
										GROUP BY o.id_community";

					c::view()->display('charts/pie', ['set' => [
						'chartId' => $chart,
						'data' => c::db()->get( $query ),
						'title' => 'Active users per community',
						'unit' => 'users',
						'maxWeeks' => $maxWeeks,
						'weeks' => $weeks,
					]]);  
				break;

			case 'unique-users-per-week-by-community':
					$maxMinWeeks = $this->maxMinWeeks();
					$maxWeeks = sizeof( $maxMinWeeks );
					$weeks = ( $_REQUEST[ 'weeks' ] ? $_REQUEST[ 'weeks' ] : $maxWeeks );
					$actual = $maxMinWeeks[ ( $weeks >= $maxWeek ? ( $weeks - 1 ) : $weeks ) ];
					$query = "SELECT CONCAT('Week ', YEARWEEK(date)) AS `week`,
												 COUNT(DISTINCT((u.phone))) AS Users,
												 c.name AS `Community`
									FROM `order` o
									INNER JOIN user u ON u.id_user = o.id_user
									LEFT JOIN community c ON o.id_community = c.id_community
									WHERE YEARWEEK(o.date) >= {$actual}
										{$this->queryExcludeCommunties}
										{$this->queryExcludeUsers}
										{$this->queryIncludeCommunties}
									GROUP BY YEARWEEK(o.date),
													 o.id_community
									ORDER BY YEARWEEK(o.date) DESC";
					c::view()->display('charts/column', ['set' => [
						'chartId' => $chart,
						'data' => c::db()->get( $query ),
						'title' => $title,
						'unit' => 'users',
						'maxWeeks' => $maxWeeks,
						'ignoreWeekSum' => true,
						'weeks' => $weeks,
					]]); 
				break;

			case 'unique-users-per-week':
					$maxMinWeeks = $this->maxMinWeeks();
					$maxWeeks = sizeof( $maxMinWeeks );
					$weeks = ( $_REQUEST[ 'weeks' ] ? $_REQUEST[ 'weeks' ] : $maxWeeks );
					$actual = $maxMinWeeks[ ( $weeks >= $maxWeek ? ( $weeks - 1 ) : $weeks ) ];
					$query = "SELECT CONCAT('Week ', YEARWEEK(date)) AS `week`,
													 COUNT(DISTINCT((u.phone))) AS Users,
													 'Users' AS label
										FROM `order` o
										INNER JOIN user u ON u.id_user = o.id_user
										LEFT JOIN community c ON o.id_community = c.id_community
										WHERE YEARWEEK(o.date) >= {$actual}
											{$this->queryExcludeCommunties}
											{$this->queryExcludeUsers}
										GROUP BY YEARWEEK(o.date)
										ORDER BY YEARWEEK(o.date) DESC";
					c::view()->display('charts/column', ['set' => [
						'chartId' => $chart,
						'data' => c::db()->get( $query ),
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
					$weeks = ( $_REQUEST[ 'weeks' ] ? $_REQUEST[ 'weeks' ] : $maxWeeks );
					$actual = $maxMinWeeks[ ( $weeks >= $maxWeek ? ( $weeks - 1 ) : $weeks ) ];
					$query = "SELECT CONCAT('Week ', YEARWEEK(date)) AS `week`,
													 CAST(COUNT(*) / COUNT(DISTINCT((u.phone))) AS DECIMAL(14, 2)) 'Orders By User',
													 'Orders by User' AS label
										FROM `order` o
										INNER JOIN user u ON u.id_user = o.id_user
										LEFT JOIN community c ON o.id_community = c.id_community
										WHERE YEARWEEK(o.date) >= {$actual}
											{$this->queryExcludeCommunties}
											{$this->queryExcludeUsers}
										GROUP BY YEARWEEK(date)
										ORDER BY YEARWEEK(date) DESC";

					c::view()->display('charts/column', ['set' => [
						'chartId' => $chart,
						'data' => c::db()->get( $query ),
						'title' => $title,
						'unit' => 'orders',
						'ignoreWeekSum' => true,
						'maxWeeks' => $maxWeeks,
						'weeks' => $weeks,
					]]); 
				break;

			case 'orders-per-week':
					$maxMinWeeks = $this->maxMinWeeks();
					$maxWeeks = sizeof( $maxMinWeeks );
					$weeks = ( $_REQUEST[ 'weeks' ] ? $_REQUEST[ 'weeks' ] : $maxWeeks );
					$actual = $maxMinWeeks[ ( $weeks >= $maxWeek ? ( $weeks - 1 ) : $weeks ) ];
					$query = "SELECT CONCAT('Week ', YEARWEEK(date)) AS `week`,
													 COUNT(*) AS Orders,
													 'Orders' AS label
										FROM `order` o
										INNER JOIN user u ON u.id_user = o.id_user
										LEFT JOIN community c ON o.id_community = c.id_community
										WHERE YEARWEEK(o.date) >= {$actual}
											{$this->queryExcludeCommunties}
											{$this->queryExcludeUsers}
										GROUP BY YEARWEEK(date)
										ORDER BY YEARWEEK(date) DESC";

					c::view()->display('charts/column', ['set' => [
						'chartId' => $chart,
						'data' => c::db()->get( $query ),
						'title' => $title,
						'unit' => 'orders',
						'maxWeeks' => $maxWeeks,
						'ignoreWeekSum' => true,
						'weeks' => $weeks,
					]]); 
				break;

			case 'gross-revenue':
					$maxMinWeeks = $this->maxMinWeeks();
					$maxWeeks = sizeof( $maxMinWeeks );
					$weeks = ( $_REQUEST[ 'weeks' ] ? $_REQUEST[ 'weeks' ] : $maxWeeks );
					$actual = $maxMinWeeks[ ( $weeks >= $maxWeek ? ( $weeks - 1 ) : $weeks ) ];
					$query = "SELECT CONCAT('Week ', YEARWEEK(date)) AS `week`,
														CAST(SUM(final_price) AS DECIMAL(14, 2)) AS 'US$',
													 'US$' AS label
										FROM `order` o
										LEFT JOIN community c ON o.id_community = c.id_community
										WHERE YEARWEEK(o.date) >= {$actual}
											{$this->queryExcludeCommunties}
											{$this->queryExcludeUsers}
										GROUP BY YEARWEEK(date)
										ORDER BY YEARWEEK(date) DESC";
					c::view()->display('charts/column', ['set' => [
						'chartId' => $chart,
						'data' => c::db()->get( $query ),
						'title' => $title,
						'unit' => '',
						'maxWeeks' => $maxWeeks,
						'ignoreWeekSum' => true,
						'weeks' => $weeks,
					]]); 
				break;

			case 'orders-by-date-by-community':
					$query = "SELECT DATE_FORMAT(CONVERT_TZ(`date`, '-8:00', '-5:00'), '%W') AS `Day`,
													 COUNT(*) AS `Orders`,
													 c.name AS `Community`
										FROM `order` o
										LEFT JOIN community c ON o.id_community = c.id_community
										WHERE env = 'live'
											{$this->queryExcludeCommunties}
											{$this->queryExcludeUsers}
											AND c.id_community IN (1, 4)
										GROUP BY DATE_FORMAT(CONVERT_TZ(`date`, '-8:00', '-5:00'), '%W'),
														 c.id_community
										ORDER BY DATE_FORMAT(CONVERT_TZ(`date`, '-8:00', '-5:00'), '%Y%m%d'),
														 c.id_community";
					c::view()->display('charts/area', ['set' => [
						'chartId' => $chart,
						'data' => c::db()->get( $query ),
						'title' => $title,
						'unit' => 'orders',
					]]); 
				break;

			default:
				break;
		}
	}

	private function weeks(){
		$query = "SELECT COUNT( DISTINCT( YEARWEEK( date) ) ) AS weeks FROM `order`";
		$result = c::db()->get( $query );
		return $result->_items[0]->weeks; 
	}
	private function maxMinWeeks(){
		$query = "SELECT DISTINCT( YEARWEEK( o.date ) ) week FROM `order` o ORDER BY week DESC";
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
}