<?php

class Controller_home_charts extends Crunchbutton_Controller_Account {

	public $activeUsersInterval = 45; // Days
	public $queryOnlyCommunties = 'AND c.id_community IN (1, 4)';
	public $queryExcludeCommunties = "AND c.name != 'Testing' AND c.name IS NOT NULL";
	public $queryExcludeUsers = "AND o.name NOT LIKE '%test%' and o.name != 'Judd' and o.name != 'dave' and o.name != 'Nick' and o.name != 'Devin'";

	public function init() {

		$chart = c::getPagePiece(2);

		$title = c::getPagePiece(3);

		$number = c::getPagePiece(4);

		// Weeks to be shown
		$allWeeks = $this->allWeeks();
		$totalWeeks = sizeof( $allWeeks );

		$this->activeUsersInterval = ( $_REQUEST[ 'activeUserDays' ] ? $_REQUEST[ 'activeUserDays' ] : $this->activeUsersInterval ); 

		$from = ( $_REQUEST[ 'from' ] ? $_REQUEST[ 'from' ] : 1 ); 
		$from = ( ( $from < 1 ) ? 1 : $from );
		
		$to = ( $_REQUEST[ 'to' ] ? $_REQUEST[ 'to' ] : $totalWeeks ); 
		
		$weekFrom = $allWeeks[ $from - 1 ];
		$weekTo = $allWeeks[ $to - 1 ];

		$query = '';
		$union = '';

		switch ( $chart ) {

			case 'new-users-per-week':

				for( $i = $from -1 ; $i < $to; $i++ ){
					$week = $allWeeks[ $i ];
					$query .= $union . "SELECT '{$week}' AS Week,
																		 COUNT(*) AS Total
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
				}

				$data = $this->parseDataWeeksSimple( $query, 'Users' );

				$this->render( array( 'data' => $data, 'unit' => 'users' ) );

			break;

			case 'new-users-per-week-by-community':

				for( $i = $from -1 ; $i < $to; $i++ ){
					$week = $allWeeks[ $i ];
					$query .= $union . "SELECT '{$week}' AS Week,
																		 COUNT(*) AS Total,
																		 Orders.name AS `Group`
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
																	 {$this->queryOnlyCommunties}
																 GROUP BY u.phone HAVING orders = 1) Orders
															WHERE Orders.date BETWEEN STR_TO_DATE('{$week} Sunday', '%X%V %W') AND STR_TO_DATE('{$week} Saturday', '%X%V %W')
															GROUP BY Orders.name";
						$union = ' UNION ';
						$count++;
				}

				$data = $this->parseDataWeeksGroup( $query );
				$this->render( array( 'data' => $data, 'unit' => 'users' ) );

			break;

			case 'active-users-per-week':

				for( $i = $from -1 ; $i < $to; $i++ ){
					$week = $allWeeks[ $i ];
					$query .= $union . "SELECT '{$week}' AS Week,
																	 COUNT(*) AS Total
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
				}

				$data = $this->parseDataWeeksSimple( $query, 'Users' );

				$this->render( array( 'data' => $data, 'unit' => 'users' ) );

			break;

			case 'new-users-per-active-users':

				for( $i = $from -1 ; $i < $to; $i++ ){
					$week = $allWeeks[ $i ];
					$query .= $union . "SELECT ActiveUsers.Label as 'Week',
																		 CAST(NewUsers.NewUsers / ActiveUsers.ActiveUsers AS DECIMAL(14, 2)) AS 'Total'
															FROM
																( SELECT '{$week}' AS Label,
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
																(SELECT '{$week}' AS Label,
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
				}

				$data = $this->parseDataWeeksSimple( $query, 'Users' );

				$this->render( array( 'data' => $data, 'unit' => 'users' ) );

			break;

		case 'new-users-per-active-users-by-community':

				for( $i = $from -1 ; $i < $to; $i++ ){
					$week = $allWeeks[ $i ];
					$query .= $union . "SELECT ActiveUsers.Label AS Week,
																		 CAST( NewUsers.NewUsers / ActiveUsers.ActiveUsers AS DECIMAL(14, 2) ) AS 'Total',
																		 ActiveUsers.Community AS 'Group',
																		 'Users',
																		 ActiveUsers.ActiveUsers,
																		 NewUsers.NewUsers
															FROM
																(SELECT '{$week}' AS Label,
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
																			{$this->queryOnlyCommunties}
																		GROUP BY u.phone) ActiveUsers
																 GROUP BY Community) ActiveUsers
															LEFT JOIN
																( SELECT '{$week}' AS Label,
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
																			{$this->queryOnlyCommunties}
																		GROUP BY u.phone HAVING orders = 1) Orders
																 WHERE Orders.date BETWEEN STR_TO_DATE('{$week} Sunday', '%X%V %W') AND STR_TO_DATE('{$week} Saturday', '%X%V %W')
																 GROUP BY Orders.name ) NewUsers ON NewUsers.Label = ActiveUsers.Label
															AND NewUsers.Community = ActiveUsers.Community
															GROUP BY ActiveUsers.Community";
					$union = ' UNION ';
					$count++;	
				}

				$data = $this->parseDataWeeksGroup( $query );

				$this->render( array( 'data' => $data, 'unit' => 'users' ) ); 

			break;

			case 'active-users-per-week-by-community':

				for( $i = $from -1 ; $i < $to; $i++ ){
					$week = $allWeeks[ $i ];
					$query .= $union . "SELECT '{$week}' AS Week,
																		 COUNT(*) AS Total,
																		 ActiveUsers.name AS 'Group'
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
																	 {$this->queryOnlyCommunties}
																 GROUP BY u.phone) ActiveUsers
															GROUP BY ActiveUsers.name";
					$union = ' UNION ';			
				}

				$data = $this->parseDataWeeksGroup( $query );

				$this->render( array( 'data' => $data, 'unit' => 'users' ) );

			break;

		case 'churn-rate-per-active-user':
				$queryActiveUsers = '';
				$queryNewUsers = '';

				for( $i = $from -1 ; $i < $to; $i++ ){
					$week = $allWeeks[ $i ];

					$queryActiveUsers .= $union . "SELECT '{$week}' AS Week,
																		 COUNT(*) AS Total
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
																	 {$this->queryOnlyCommunties}
																 GROUP BY u.phone) ActiveUsers";

					$queryNewUsers .= $union . "SELECT '{$week}' AS Week,
																		 COUNT(*) AS Total
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

				}
				$uniqueUsers = $this->parseDataWeeksSimple( $queryActiveUsers );
				$newUsers = $this->parseDataWeeksSimple( $queryNewUsers );
				$data = [];
				for( $i = 0; $i < sizeof( $uniqueUsers ); $i++ ){
					$unique = $uniqueUsers[ $i ]->Total;
					$new = $newUsers[ $i ]->Total;
					if( $i - 1 >= 0 ){
						$uniquePrev = $uniqueUsers[ $i - 1 ]->Total;
					} else {
						$uniquePrev = 0;
					}
					
					$lost = ( ( $uniquePrev + $new ) - $unique );
					$lost = ( $lost < 0 )	? 0 : $lost;

					// Formula: so, divide the number lost by the previous week's total
					if( $uniquePrev != 0 && $lost != 0 ){
						$result = $lost / $uniquePrev;	
					} else {
						$result = 0;
					}
					$data[] = ( object ) array( 'Label' => $uniqueUsers[ $i ]->Label, 'Total' => number_format( $result, 4 ), 'Type' => 'Result' );
				}

				$this->render( array( 'data' => $data, 'unit' => '' ) );
			break;

		case 'churn-rate':

				$queryActiveUsers = '';
				$queryNewUsers = '';

				for( $i = $from -1 ; $i < $to; $i++ ){
					$week = $allWeeks[ $i ];

					$queryActiveUsers .= $union . "SELECT '{$week}' AS Week,
																		 COUNT(*) AS Total
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
																	 {$this->queryOnlyCommunties}
																 GROUP BY u.phone) ActiveUsers";

					$queryNewUsers .= $union . "SELECT '{$week}' AS Week,
																		 COUNT(*) AS Total
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

				}
				$uniqueUsers = $this->parseDataWeeksSimple( $queryActiveUsers );
				$newUsers = $this->parseDataWeeksSimple( $queryNewUsers );
				$data = [];
				for( $i = 0; $i < sizeof( $uniqueUsers ); $i++ ){
					$unique = $uniqueUsers[ $i ]->Total;
					$new = $newUsers[ $i ]->Total;
					if( $i - 1 >= 0 ){
						$uniquePrev = $uniqueUsers[ $i - 1 ]->Total;
					} else {
						$uniquePrev = 0;
					}
					$churn = ( ( $uniquePrev + $new ) - $unique );
					// Do not show the negatives
					$churn = ( $churn < 0 )	? 0 : $churn;
					$data[] = ( object ) array( 'Label' => $uniqueUsers[ $i ]->Label, 'Total' => $churn, 'Type' => 'Users' );
				}
				$this->render( array( 'data' => $data, 'unit' => 'users' ) );
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
											{$this->queryOnlyCommunties}
										GROUP BY o.id_community";

					c::view()->display('charts/pie', ['set' => [
						'chartId' => $chart,
						'data' => c::db()->get( $query ),
						'title' => $title,
						'number' => $number,
						'unit' => 'users',
						'maxWeeks' => $maxWeeks,
						'weeks' => $weeks,
					]]);  
				break;

			case 'unique-users-per-week-by-community':

					$query = "SELECT YEARWEEK(date) AS `Week`,
												 COUNT(DISTINCT((u.phone))) AS Total,
												 c.name AS `Group`
									FROM `order` o
									LEFT JOIN user u ON u.id_user = o.id_user
									LEFT JOIN community c ON o.id_community = c.id_community
									WHERE YEARWEEK(o.date) >= {$weekFrom} AND YEARWEEK(o.date) <= {$weekTo} 
										{$this->queryExcludeCommunties}
										{$this->queryExcludeUsers}
										{$this->queryOnlyCommunties}
									GROUP BY YEARWEEK(o.date),
													 o.id_community
									ORDER BY YEARWEEK(o.date) DESC";

					$data = $this->parseDataWeeksGroup( $query );

					$this->render( array( 'data' => $data, 'unit' => 'users' ) );

				break;

			case 'unique-users-per-week':

					$query = "SELECT YEARWEEK(date) AS `Week`,
													 COUNT( DISTINCT( ( u.phone ) ) ) AS Total
										FROM `order` o
										INNER JOIN user u ON u.id_user = o.id_user
										LEFT JOIN community c ON o.id_community = c.id_community
										WHERE YEARWEEK(o.date) >= {$weekFrom} AND YEARWEEK(o.date) <= {$weekTo} 
											{$this->queryExcludeCommunties}
											{$this->queryExcludeUsers}
										GROUP BY YEARWEEK(o.date)
										ORDER BY YEARWEEK(o.date) DESC";

					$data = $this->parseDataWeeksSimple( $query, 'Users' );

					$this->render( array( 'data' => $data, 'unit' => 'users' ) );
				break;

			case 'orders-by-user-week':

					$query = "SELECT YEARWEEK(date) AS Week,
													 CAST(COUNT(*) / COUNT(DISTINCT((u.phone))) AS DECIMAL(14, 2)) Total
										FROM `order` o
										INNER JOIN user u ON u.id_user = o.id_user
										LEFT JOIN community c ON o.id_community = c.id_community
										WHERE YEARWEEK(o.date) >= {$weekFrom} AND YEARWEEK(o.date) <= {$weekTo} 
											{$this->queryExcludeCommunties}
											{$this->queryExcludeUsers}
										GROUP BY YEARWEEK(date)
										ORDER BY YEARWEEK(date) DESC";

					$data = $this->parseDataWeeksSimple( $query, 'Orders' );

					$this->render( array( 'data' => $data, 'unit' => 'orders' ) );

				break;

			case 'orders-per-week':

					$query = "SELECT YEARWEEK(date) AS Week,
													 COUNT(*) AS Total
										FROM `order` o
										INNER JOIN user u ON u.id_user = o.id_user
										LEFT JOIN community c ON o.id_community = c.id_community
										WHERE 
											YEARWEEK(o.date) >= {$weekFrom} AND YEARWEEK(o.date) <= {$weekTo} 
											{$this->queryExcludeCommunties}
											{$this->queryExcludeUsers}
										GROUP BY YEARWEEK(date)
										ORDER BY YEARWEEK(date) ASC";

					$data = $this->parseDataWeeksSimple( $query, 'Orders' );

					$this->render( array( 'data' => $data, 'unit' => 'orders' ) );

				break;

			case 'orders-per-week-by-community':

					$query = "SELECT YEARWEEK(date) AS Week,
													 COUNT(*) AS Total,
													 c.name AS 'Group'
										FROM `order` o
										INNER JOIN user u ON u.id_user = o.id_user
										LEFT JOIN community c ON o.id_community = c.id_community
										WHERE YEARWEEK(o.date) >= {$weekFrom} AND YEARWEEK(o.date) <= {$weekTo} 
											{$this->queryExcludeCommunties}
											{$this->queryOnlyCommunties}
											{$this->queryExcludeUsers}
										GROUP BY YEARWEEK(date), c.name
										ORDER BY YEARWEEK(date) DESC";

					$data = $this->parseDataWeeksGroup( $query );

					$this->render( array( 'data' => $data, 'unit' => 'orders' ) );

				break;

			case 'orders-using-giftcard-per-week':
					$allWeeks = $this->allWeeks();
					$maxWeeks = sizeof( $allWeeks );
					$weeks = ( $_REQUEST[ 'weeks' ] ? $_REQUEST[ 'weeks' ] : $maxWeeks );
					$actual = $allWeeks[ ( $weeks >= $maxWeek ? ( $weeks - 1 ) : $weeks ) ];
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
echo $query ;exit;
					c::view()->display('charts/column', ['set' => [
						'chartId' => $chart,
						'data' => c::db()->get( $query ),
						'title' => $title,
						'number' => $number,
						'unit' => 'orders',
						'maxWeeks' => $maxWeeks,
						'ignoreWeekSum' => true,
						'weeks' => $weeks,
					]]); 
				break;

			case 'gross-revenue':

					$query = "SELECT YEARWEEK(date) AS `Week`,
														CAST(SUM(final_price) AS DECIMAL(14, 2)) AS 'Total'
										FROM `order` o
										LEFT JOIN community c ON o.id_community = c.id_community
										WHERE YEARWEEK(o.date) >= {$weekFrom} AND YEARWEEK(o.date) <= {$weekTo} 
											{$this->queryExcludeCommunties}
											{$this->queryExcludeUsers}
										GROUP BY YEARWEEK(date)
										ORDER BY YEARWEEK(date) DESC";

				$data = $this->parseDataWeeksSimple( $query, 'US$' );

				$this->render( array( 'data' => $data, 'unit' => 'US$' ) );

				break;

			case 'orders-by-weekday-by-community':
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
						'number' => $number,
						'unit' => 'orders',
					]]); 
				break;

			case 'weeks':
				$allWeeks = $this->allWeeks();
				$weeks = [];
				foreach( $allWeeks as $week ){
					$weeks[] = $this->parseWeek( $week, true );
				}
				echo json_encode( $weeks );
				break;
			default:
				break;
		}
	}

	private function render( $params ){

		$chart = c::getPagePiece(2);

		$title = c::getPagePiece(3);

		$number = c::getPagePiece(4);

		// Weeks to be shown
		$allWeeks = $this->allWeeks();
		$totalWeeks = sizeof( $allWeeks );

		$from = ( $_REQUEST[ 'from' ] ? $_REQUEST[ 'from' ] : 1 ); 
		$from = ( ( $from < 1 ) ? 1 : $from );
		
		$to = ( $_REQUEST[ 'to' ] ? $_REQUEST[ 'to' ] : $totalWeeks ); 
		
		$weekFrom = $allWeeks[ $from - 1 ];
		$weekTo = $allWeeks[ $to - 1 ];

		return c::view()->display('charts/column', ['set' => [
						'chartId' => $chart,
						'data' => $params[ 'data' ] ,
						'title' => $title,
						'to' => $to,
						'from' => $from,
						'number' => $number,
						'unit' => $params[ 'unit' ] ,
						'totalWeeks' => $totalWeeks
					]]); 
	}

	private function parseWeek( $week, $showYear = false ){
		$dateStr = ( $showYear ) ? 'M d Y' : 'M d';
		return date( $dateStr, strtotime( substr( $week, 0, 4 ) . 'W' . substr( $week, 4, 2 ) . '-7' ) );
	}

	private function parseDataWeeksGroup( $query ){

		$allWeeks = $this->allWeeks();
		$totalWeeks = sizeof( $allWeeks );

		$from = ( $_REQUEST[ 'from' ] ? $_REQUEST[ 'from' ] : 1 ); 
		$from = ( ( $from < 1 ) ? 1 : $from );
		
		$to = ( $_REQUEST[ 'to' ] ? $_REQUEST[ 'to' ] : $totalWeeks ); 
		
		$weekFrom = $allWeeks[ $from - 1 ];
		$weekTo = $allWeeks[ $to - 1 ];

		$data = c::db()->get( $query );

		$_weeks = [];
		$groups = [];
		foreach ( $data as $item ) {
			$groups[ $item->Group ] = $item->Group;
			$_weeks[ $item->Week ][ $item->Group ] = $item->Total;
		}

		$data = [];

		for( $i = $from -1 ; $i < $to; $i++ ){
			$week = $allWeeks[ $i ];
			foreach( $groups as $group ){
				$total = ( $_weeks[ $week ][ $group ] ) ? $_weeks[ $week ][ $group ] : 0;
				$data[] = ( object ) array( 'Label' => $this->parseWeek( $week ), 'Total' => $total, 'Type' => $group  ); 
			}
		}

		return $data;
	}

	private function parseDataWeeksSimple( $query, $type = 'Total' ){

		$allWeeks = $this->allWeeks();
		$totalWeeks = sizeof( $allWeeks );

		$from = ( $_REQUEST[ 'from' ] ? $_REQUEST[ 'from' ] : 1 ); 
		$from = ( ( $from < 1 ) ? 1 : $from );
		
		$to = ( $_REQUEST[ 'to' ] ? $_REQUEST[ 'to' ] : $totalWeeks ); 
		
		$weekFrom = $allWeeks[ $from - 1 ];
		$weekTo = $allWeeks[ $to - 1 ];

		$data = c::db()->get( $query );

		$_weeks = [];
		foreach ( $data as $item ) {
			$_weeks[ $item->Week ] = $item->Total;
		}
		$data = [];
		for( $i = $from -1 ; $i < $to; $i++ ){
			$week = $allWeeks[ $i ];
			$total = ( $_weeks[ $week ] ) ? $_weeks[ $week ] : 0;
			$data[] = ( object ) array( 'Label' => $this->parseWeek( $week ), 'Total' => $total, 'Type' => $type  ); 
		}

		return $data;
	}

	private function weeks(){
		$query = "SELECT COUNT( DISTINCT( YEARWEEK( date ) ) ) AS weeks FROM `order`";
		$result = c::db()->get( $query );
		return $result->_items[0]->weeks; 
	}

	private function allWeeks(){
		$query = "SELECT DISTINCT( YEARWEEK( o.date ) ) week FROM `order` o WHERE YEARWEEK( o.date ) IS NOT NULL ORDER BY week ASC";
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