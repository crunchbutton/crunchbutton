<?php

class Controller_home_charts extends Crunchbutton_Controller_Account {

	public $activeUsersInterval = 45; // Days
	public $queryOnlyCommunties = 'AND c.id_community IN (1, 4)';
	public $queryExcludeCommunties = "AND c.name != 'Testing' AND c.name IS NOT NULL";
	public $queryExcludeUsers = "AND o.name NOT LIKE '%test%' and o.name != 'Judd' and o.name != 'dave' and o.name != 'Nick' and o.name != 'Devin'";

	public function init() {

		$this->chart = c::getPagePiece(2);

		$this->title = c::getPagePiece(3);

		$this->number = c::getPagePiece(4);

		// Weeks to be shown
		$this->allWeeks = $this->allWeeks();
		$this->totalWeeks = sizeof( $this->allWeeks );

		$this->activeUsersInterval = ( $_REQUEST[ 'activeUserDays' ] ? $_REQUEST[ 'activeUserDays' ] : $this->activeUsersInterval ); 

		$this->from = ( $_REQUEST[ 'from' ] ? $_REQUEST[ 'from' ] : 1 ); 
		$this->from = ( ( $this->from  < 1 ) ? 1 : $this->from  );
		
		$this->to = ( $_REQUEST[ 'to' ] ? $_REQUEST[ 'to' ] : $this->totalWeeks ); 
		
		$this->weekFrom = $this->allWeeks[ $this->from  - 1 ];
		$this->weekTo = $this->allWeeks[ $this->to - 1 ];

		$query = '';
		$union = '';

		$to = $this->to;
		$from = $this->from;
		$allWeeks = $this->allWeeks;
		
		$allDays = $this->allDays();
		$allMonths = $this->allMonths();

		switch ( $this->chart ) {

			case 'new-users-per-week':

				$data = $this->newUsersByWeek();
				$this->render( array( 'data' => $data, 'unit' => 'users' ) );

			break;

			case 'new-users-per-week-by-community':

				for( $i = $this->from -1 ; $i < $this->to; $i++ ){
					$week = $this->allWeeks[ $i ];
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
				$data = $this->activeUsersByWeek();
				$this->render( array( 'data' => $data, 'unit' => 'users' ) );

			break;

			case 'active-users-per-month':
				$data = $this->activeUsersByMonth();
				$this->render( array( 'data' => $data, 'unit' => 'users' ) );

			break;

			case 'new-users-per-active-users':

				for( $i = $this->from -1 ; $i < $this->to; $i++ ){
					$week = $this->allWeeks[ $i ];
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
																			AND o.date >= STR_TO_DATE('{$week} Sunday', '%X%V %W') - INTERVAL {$this->activeUsersInterval} DAY
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

				for( $i = $this->from -1 ; $i < $this->to; $i++ ){
					$week = $this->allWeeks[ $i ];
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
																			AND o.date >= STR_TO_DATE('{$week} Sunday', '%X%V %W') - INTERVAL {$this->activeUsersInterval} DAY
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

				for( $i = $this->from -1 ; $i < $this->to; $i++ ){
					$week = $this->allWeeks[ $i ];
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
																	 AND o.date >= STR_TO_DATE('{$week} Sunday', '%X%V %W') - INTERVAL {$this->activeUsersInterval} DAY
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

				$activeUsers = $this->activeUsersByWeek();
				$newUsers = $this->newUsersByWeek();

				$data = [];
				for( $i = 0; $i < sizeof( $activeUsers ); $i++ ){
					$unique = $activeUsers[ $i ]->Total;
					$new = $newUsers[ $i ]->Total;
					if( $i - 1 >= 0 ){
						$uniquePrev = $activeUsers[ $i - 1 ]->Total;
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
					$data[] = ( object ) array( 'Label' => $activeUsers[ $i ]->Label, 'Total' => number_format( $result, 4 ), 'Type' => 'Total' );
				}

				$this->render( array( 'data' => $data, 'unit' => '' ) );
			break;

		case 'repeat-orders-per-active-user':

				$activeUsers = $this->activeUsersByWeek();
				$newUsers = $this->newUsersByWeek();
				$orders = $this->ordersByWeek();

				$data = [];
				for( $i = 0; $i < sizeof( $activeUsers ); $i++ ){
					$unique = $activeUsers[ $i ]->Total;
					$order = $orders[ $i ]->Total;
					$new = $newUsers[ $i ]->Total;
					if( $i - 1 >= 0 ){
						$uniquePrev = $activeUsers[ $i - 1 ]->Total;
					} else {
						$uniquePrev = 0;
					}
					
					// Formula (Orders minus New Users) / (Active Users) | Active Users = ( average of the current week and previous week's Active Users )
					$activeUsersAvg = ( $unique + $uniquePrev ) / 2;

					$ordersMinusNewUsers = $order - $new;

					if( $ordersMinusNewUsers != 0 && $activeUsersAvg != 0 ){
						$result = ( $order - $new ) / ( $activeUsersAvg );	
					} else {
						$result = 0;
					}

					$data[] = ( object ) array( 'Label' => $activeUsers[ $i ]->Label, 'Total' => number_format( $result, 4 ), 'Type' => 'Total' );
				}

				$this->render( array( 'data' => $data, 'unit' => '' ) );
			break;

		case 'churn-rate':

				$activeUsers = $this->activeUsersByWeek();
				$newUsers = $this->newUsersByWeek();

				$data = [];
				for( $i = 0; $i < sizeof( $activeUsers ); $i++ ){
					$unique = $activeUsers[ $i ]->Total;
					$new = $newUsers[ $i ]->Total;
					if( $i - 1 >= 0 ){
						$uniquePrev = $activeUsers[ $i - 1 ]->Total;
					} else {
						$uniquePrev = 0;
					}
					$churn = ( ( $uniquePrev + $new ) - $unique );
					// Do not show the negatives
					$churn = ( $churn < 0 )	? 0 : $churn;
					$data[] = ( object ) array( 'Label' => $activeUsers[ $i ]->Label, 'Total' => $churn, 'Type' => 'Users' );
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
						'title' => $this->title,
						'number' => $this->number,
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
									WHERE YEARWEEK(o.date) >= {$this->weekFrom} AND YEARWEEK(o.date) <= {$this->weekTo} 
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
										WHERE YEARWEEK(o.date) >= {$this->weekFrom} AND YEARWEEK(o.date) <= {$this->weekTo} 
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
										WHERE YEARWEEK(o.date) >= {$this->weekFrom} AND YEARWEEK(o.date) <= {$this->weekTo} 
											{$this->queryExcludeCommunties}
											{$this->queryExcludeUsers}
										GROUP BY YEARWEEK(date)
										ORDER BY YEARWEEK(date) DESC";

					$data = $this->parseDataWeeksSimple( $query, 'Orders' );

					$this->render( array( 'data' => $data, 'unit' => 'orders' ) );

				break;

			case 'orders-per-week':

					$data = $this->ordersByWeek();

					$this->render( array( 'data' => $data, 'unit' => 'orders' ) );

				break;

			case 'orders-per-week-by-community':

					$query = "SELECT YEARWEEK(date) AS Week,
													 COUNT(*) AS Total,
													 c.name AS 'Group'
										FROM `order` o
										INNER JOIN user u ON u.id_user = o.id_user
										LEFT JOIN community c ON o.id_community = c.id_community
										WHERE YEARWEEK(o.date) >= {$this->weekFrom} AND YEARWEEK(o.date) <= {$this->weekTo} 
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

					c::view()->display('charts/column', ['set' => [
						'chartId' => $chart,
						'data' => c::db()->get( $query ),
						'title' => $this->title,
						'number' => $this->number,
						'unit' => 'orders',
						'maxWeeks' => $maxWeeks,
						'ignoreWeekSum' => true,
						'weeks' => $weeks,
					]]); 
				break;

			case 'reclaimed-users':
					$query = "SELECT yearweek AS Week,
													 COUNT(*) AS Total
										FROM
											(SELECT last.total AS total,
															lastbutone.id_order AS id_order_last_but_one,
															lastbutone.date AS date_last_but_one,
															last.id_order AS id_order_last,
															last.date AS date_last,
															lastbutone.phone AS phone,
															YEARWEEK(last.date) AS yearweek,
															DATEDIFF(last.date, lastbutone.date) AS days
											 FROM
												 (SELECT *
													FROM
														(SELECT count(*) AS total,
																		max(orders.id_order) AS id_order,
																		max(orders.date) AS date,
																		orders.phone
														 FROM
															 (SELECT o.id_order,
																			 o.date, o.phone
																FROM `order` o) orders
														 GROUP BY phone HAVING total > 1) orders) last
											 INNER JOIN
												 (SELECT o.id_order,
																 o.phone,
																 o.date
													FROM `order` o
													INNER JOIN
														(SELECT MAX(o.id_order) AS id_order ,
																		o.phone,
																		o.date
														 FROM `order` o
														 INNER JOIN
															 (SELECT id_order,
																			 phone
																FROM
																	(SELECT count(*) AS total,
																					max(id_order) AS id_order,
																					phone
																	 FROM `order`
																	 GROUP BY phone HAVING total > 1) orders) last ON last.phone = o.phone
														 AND last.id_order > o.id_order
														 GROUP BY phone) lastbutone ON lastbutone.id_order = o.id_order) lastbutone ON last.phone = lastbutone.phone) orders
										WHERE days >= {$this->activeUsersInterval}
											AND yearweek >= {$this->weekFrom}
											AND yearweek <= {$this->weekTo}
										GROUP BY yearweek";

					$data = $this->parseDataWeeksSimple( $query, 'Users' );

					$this->render( array( 'data' => $data, 'unit' => 'users' ) );

				break;

			case 'gross-revenue':

					$query = "SELECT YEARWEEK(date) AS `Week`,
														CAST(SUM(final_price) AS DECIMAL(14, 2)) AS 'Total'
										FROM `order` o
										LEFT JOIN community c ON o.id_community = c.id_community
										WHERE YEARWEEK(o.date) >= {$this->weekFrom} AND YEARWEEK(o.date) <= {$this->weekTo} 
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
						'chartId' => $this->chart,
						'data' => c::db()->get( $query ),
						'title' => $this->title,
						'number' => $this->number,
						'unit' => 'orders',
					]]); 
				break;

			case 'weeks':
				$weeks = [];
				foreach( $this->allWeeks as $week ){
					$weeks[] = $this->parseWeek( $week, true );
				}
				echo json_encode( $weeks );
				break;
			default:
				break;
		}
	}

	private function render( $params ){

		return c::view()->display('charts/column', ['set' => [
						'chartId' => $this->chart,
						'data' => $params[ 'data' ] ,
						'title' => $this->title,
						'to' => $this->to,
						'from' => $this->from,
						'number' => $this->number,
						'unit' => $params[ 'unit' ] ,
						'totalWeeks' => $this->totalWeeks
					]]); 
	}

	private function parseDataWeeksGroup( $query ){

		$data = c::db()->get( $query );

		$_weeks = [];
		$groups = [];
		foreach ( $data as $item ) {
			$groups[ $item->Group ] = $item->Group;
			$_weeks[ $item->Week ][ $item->Group ] = $item->Total;
		}

		$data = [];

		for( $i = $this->from -1 ; $i < $this->to; $i++ ){
			$week = $this->allWeeks[ $i ];
			foreach( $groups as $group ){
				$total = ( $_weeks[ $week ][ $group ] ) ? $_weeks[ $week ][ $group ] : 0;
				$data[] = ( object ) array( 'Label' => $this->parseWeek( $week ), 'Total' => $total, 'Type' => $group  ); 
			}
		}

		return $data;
	}

	private function parseDataWeeksSimple( $query, $type = 'Total' ){

		$data = c::db()->get( $query );

		$_weeks = [];
		foreach ( $data as $item ) {
			$_weeks[ $item->Week ] = $item->Total;
		}

		$data = [];
		for( $i = $this->from -1 ; $i < $this->to; $i++ ){
			$week = $this->allWeeks[ $i ];
			$total = ( $_weeks[ $week ] ) ? $_weeks[ $week ] : 0;
			$data[] = ( object ) array( 'Label' => $this->parseWeek( $week ), 'Total' => $total, 'Type' => $type  ); 
		}

		return $data;
	}

	private function ordersByWeek(){
	
		$query = "SELECT YEARWEEK(date) AS Week,
											 COUNT(*) AS Total
								FROM `order` o
								INNER JOIN user u ON u.id_user = o.id_user
								LEFT JOIN community c ON o.id_community = c.id_community
								WHERE 
									YEARWEEK(o.date) >= {$this->weekFrom} AND YEARWEEK(o.date) <= {$this->weekTo} 
									{$this->queryExcludeCommunties}
									{$this->queryExcludeUsers}
								GROUP BY YEARWEEK(date)
								ORDER BY YEARWEEK(date) ASC";

		return $this->parseDataWeeksSimple( $query, 'Orders' );
	}

	private function activeUsersByMonth(){
		$query = '';
		$union = '';
$this->activeUsersByWeek();
exit;
		$months = $this->getMonthsFromWeeks();

		foreach ( $months as $month ) {
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
															 AND o.date >= STR_TO_DATE('{$week} Sunday', '%X%V %W') - INTERVAL {$this->activeUsersInterval} DAY
															 {$this->queryExcludeCommunties}
															 {$this->queryExcludeUsers}
															 {$this->queryOnlyCommunties}
														 GROUP BY u.phone) ActiveUsers";
				$union = ' UNION ';	
		}
		echo $query;exit;
		return $this->parseDataWeeksSimple( $query, 'Users' );
	}

	private function activeUsersByWeek(){
		$query = '';
		$union = '';
		for( $i = $this->from -1 ; $i < $this->to; $i++ ){
			$week = $this->allWeeks[ $i ];

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
															 {$this->queryOnlyCommunties}
														 GROUP BY u.phone) ActiveUsers";
				$union = ' UNION ';	

		}

		return $this->parseDataWeeksSimple( $query, 'Users' );
	}

	private function newUsersByWeek(){
		$query = '';
		$union = '';
		for( $i = $this->from -1 ; $i < $this->to; $i++ ){
			$week = $this->allWeeks[ $i ];
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
		return $this->parseDataWeeksSimple( $query, 'Users' );
	}

	private function weeks(){
		$query = "SELECT COUNT( DISTINCT( YEARWEEK( date ) ) ) AS weeks FROM `order`";
		$result = c::db()->get( $query );
		return $result->_items[0]->weeks; 
	}

	private function allMonths(){
		$query = "SELECT DISTINCT( DATE_FORMAT( o.date ,'%Y-%m') ) month FROM `order` o WHERE o.date IS NOT NULL ORDER BY month ASC";
		$results = c::db()->get( $query );
		$months = array();
		foreach ( $results as $result ) {
			if( !$result->month ){
				continue;
			}
			$months[] = $result->month;
		}
		return $months;
	}

	private function allDays(){
		$query = "SELECT DISTINCT( DATE_FORMAT( o.date ,'%Y-%m-%d') ) day FROM `order` o WHERE o.date IS NOT NULL ORDER BY day ASC";
		$results = c::db()->get( $query );
		$days = array();
		foreach ( $results as $result ) {
			if( !$result->day ){
				continue;
			}
			$days[] = $result->day;
		}
		return $days;
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

	private function getMonthsFromWeeks(){
		$months = [];
		for( $i = $this->from -1 ; $i < $this->to; $i++ ){
			$week = $this->allWeeks[ $i ];
			$month = date( 'Y-m', strtotime( substr( $week, 0, 4 ) . 'W' . substr( $week, 4, 2 ) ) );
			$months[ $month ] = $month;
		}
		return $months;
	}

	private function parseWeek( $week, $showYear = false ){
		$dateStr = ( $showYear ) ? 'M d Y' : 'M d';
		return date( $dateStr, strtotime( substr( $week, 0, 4 ) . 'W' . substr( $week, 4, 2 ) . '-7' ) );
	}
}