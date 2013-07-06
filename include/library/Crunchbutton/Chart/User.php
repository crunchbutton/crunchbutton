<?php 
class Crunchbutton_Chart_User extends Crunchbutton_Chart {
	
	public $unit = 'users';
	public $description = 'Users';

	public $group = 'group1';

	public $groups = array( 
													'group1' => array( 
																						'users-new-per-day' => 'New Users per Day',
																						'users-new-per-week' => 'New Users per Week',
																						'users-new-per-month' => 'New Users per Month',
																						'users-active-per-week' => 'Active Users per Week',
																						'users-active-per-month' => 'Active Users per Month',
																						'users-unique-per-week' => 'Unique Users per Week',
																						'users-unique-per-month' => 'Unique Users per Month',
																						'users-new-per-active-users-per-week' => 'New Users per Active Users per Week',
																						'users-new-per-active-users-per-month' => 'New Users per Active Users per Month',
																						
																						'users-active-per-week-by-community' => 'Active Users per Week by Community',
																						'users-new-per-week-by-community' => 'New Users per Week by Community',
																						'users-new-per-active-users-by-community' => 'New Users per Active Users By Community',
																						'users-unique-per-week-by-community' => 'Unique Users per Week by Community',
																						'users-reclaimed-per-week' => 'Reclaimed Users per Week'
																			) 
										);

	public function __construct() {
		parent::__construct();
	}

	public function activeByMonth( $render = false ){

		$query = '';
		$union = '';

		$allMonths = $this->allMonths();

		for( $i = $this->from_month -1 ; $i < $this->to_month; $i++ ){
			$month = $allMonths[ $i ];
			$query .= $union . "SELECT '{$month}' AS Month,
																 COUNT(*) AS Total
													FROM
														( SELECT u.phone,
																		 o.date,
																		 u.id_user,
																		 c.name
														 FROM `order` o
														 INNER JOIN user u ON u.id_user = o.id_user
														 LEFT JOIN community c ON o.id_community = c.id_community
														 WHERE o.date <= LAST_DAY( STR_TO_DATE( '{$month}', '%Y-%m' ) )
															 AND o.date >= '{$month}-01' - INTERVAL {$this->activeUsersInterval} DAY
															 {$this->queryExcludeCommunties}
															 {$this->queryExcludeUsers}
															 {$this->queryOnlyCommunties}
														 GROUP BY u.phone ) ActiveUsers";

				$union = ' UNION ';	
		}	

		$parsedData = $this->parseDataMonthSimple( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unity, 'interval' => 'month' );
		}
		return $parsedData;
	}

	public function newByMonth( $render = false ){

		$query = "SELECT SUM(1) AS Total,
										 DATE_FORMAT(o.date ,'%Y-%m') AS Month
							FROM `order` o
							INNER JOIN
								(SELECT min(id_order) id_order,
												u.phone
								 FROM `order` o
								 INNER JOIN user u ON u.id_user = o.id_user
								 LEFT JOIN community c ON o.id_community = c.id_community
								 {$this->queryExcludeCommunties}
								 {$this->queryExcludeUsers}
								 GROUP BY u.phone) orders ON o.id_order = orders.id_order
							GROUP BY DATE_FORMAT(o.date ,'%Y-%m') HAVING Month BETWEEN '{$this->monthFrom}' AND '{$this->monthTo}'";
	
		$parsedData = $this->parseDataMonthSimple( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unity, 'interval' => 'month' );
		}
		return $parsedData;
	}

	public function reclaimedByWeek( $render = false ){

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
		$parsedData = $this->parseDataWeeksSimple( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unity, 'interval' => 'month' );
		}
		return $parsedData;
	}

	public function activeByWeek( $render = false ){


		$allWeeks = $this->allWeeks();

		$query = '';
		$union = '';
		for( $i = $this->from -1 ; $i < $this->to; $i++ ){
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

		$parsedData = $this->parseDataWeeksSimple( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unity );
		}
		return $parsedData;
	}

	public function newByDay( $render = false ){

		$query = "SELECT SUM(1) AS Total,
										 DATE_FORMAT(o.date ,'%Y-%m-%d') AS Day
							FROM `order` o
							INNER JOIN
								(SELECT min(id_order) id_order,
												u.phone
								 FROM `order` o
								 INNER JOIN user u ON u.id_user = o.id_user
								 LEFT JOIN community c ON o.id_community = c.id_community
								 WHERE 1=1 
										{$this->queryExcludeCommunties}
										{$this->queryExcludeUsers}
								 GROUP BY u.phone) orders ON o.id_order = orders.id_order
							GROUP BY DATE_FORMAT(o.date ,'%Y-%m-%d') HAVING Day BETWEEN '{$this->dayFrom}' AND '{$this->dayTo}'";

		$parsedData = $this->parseDataDaysSimple( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unity, 'interval' => 'day' );
		}
		return $parsedData;
	}

	public function newByWeek( $render = false ){

		$query = "SELECT SUM(1) AS Total,
										 YEARWEEK(o.date) AS Week
							FROM `order` o
							INNER JOIN
								(SELECT min(id_order) id_order,
												u.phone
								 FROM `order` o
								 INNER JOIN user u ON u.id_user = o.id_user
								 LEFT JOIN community c ON o.id_community = c.id_community
								 WHERE 1=1 
										{$this->queryExcludeCommunties}
										{$this->queryExcludeUsers}
								 GROUP BY u.phone) orders ON o.id_order = orders.id_order
							GROUP BY YEARWEEK(o.date) HAVING Week BETWEEN '{$this->weekFrom}' AND '{$this->weekTo}'";
	
		$parsedData = $this->parseDataWeeksSimple( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unity );
		}
		return $parsedData;
	}

	public function newPerActiveByWeekByCommunity( $render = false ){

		$query = '';
		$union = '';

		$allWeeks = $this->allWeeks();

		for( $i = $this->from -1 ; $i < $this->to; $i++ ){
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
																		community AS Community
														 FROM
															 (SELECT u.phone,
																			 o.date,
																			 u.id_user,
																			 r.community
																FROM `order` o
																INNER JOIN user u ON u.id_user = o.id_user
																LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant 
																WHERE o.date <= STR_TO_DATE('{$week} Saturday', '%X%V %W')
																	AND o.date >= STR_TO_DATE('{$week} Sunday', '%X%V %W') - INTERVAL {$this->activeUsersInterval} DAY
																	AND r.community IS NOT NULL
																	{$this->queryExcludeUsers}
																GROUP BY u.phone) ActiveUsers
														 GROUP BY Community) ActiveUsers
													LEFT JOIN
														( SELECT '{$week}' AS Label,
																		 COUNT(*) AS NewUsers,
																		 community AS Community
														 FROM
															 (SELECT COUNT(*) orders,
																			 u.phone,
																			 o.date,
																			 u.id_user,
																			 r.community
																FROM `order` o
																INNER JOIN user u ON u.id_user = o.id_user
																LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant 
																WHERE o.date <= STR_TO_DATE('{$week} Saturday', '%X%V %W')
																	{$this->queryExcludeUsers}
																	AND r.community IS NOT NULL
																GROUP BY u.phone HAVING orders = 1) Orders
														 WHERE Orders.date BETWEEN STR_TO_DATE('{$week} Sunday', '%X%V %W') AND STR_TO_DATE('{$week} Saturday', '%X%V %W')
														 GROUP BY Orders.community ) NewUsers ON NewUsers.Label = ActiveUsers.Label
													AND NewUsers.Community = ActiveUsers.Community
													GROUP BY ActiveUsers.Community";
			$union = ' UNION ';
			$count++;	
		}

		$parsedData = $this->parseDataWeeksGroup( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unity );
		}
		return $parsedData;

	}

	public function newByWeekByCommunity( $render = false ){

		$query = "SELECT SUM(1) AS Total,
										 YEARWEEK(o.date) AS Week,
										 community AS `Group`
							FROM `order` o
							INNER JOIN
								(SELECT min(id_order) id_order,
												u.phone,
												r.community
								 FROM `order` o
								 INNER JOIN user u ON u.id_user = o.id_user
								 LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant 
								 WHERE r.community IS NOT NULL
										{$this->queryExcludeUsers}
								 GROUP BY u.phone, r.community) orders ON o.id_order = orders.id_order
							GROUP BY YEARWEEK(o.date) HAVING Week BETWEEN '{$this->weekFrom}' AND '{$this->weekTo}'";

		$parsedData = $this->parseDataWeeksGroup( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unity );
		}
		return $parsedData;
	}

	public function activeByWeekByCommunity( $render = false ){

		$query = '';
		$union = '';

		$allWeeks = $this->allWeeks();

		for( $i = $this->from -1 ; $i < $this->to; $i++ ){
			$week = $allWeeks[ $i ];
			$query .= $union . "SELECT '{$week}' AS Week,
																 COUNT(*) AS Total,
																 community AS 'Group'
													FROM
														( SELECT u.phone,
																		 o.date,
																		 u.id_user,
																		 r.community
														 FROM `order` o
														 INNER JOIN user u ON u.id_user = o.id_user
														 LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant 
														 WHERE o.date <= STR_TO_DATE('{$week} Saturday', '%X%V %W')
															 AND o.date >= STR_TO_DATE('{$week} Sunday', '%X%V %W') - INTERVAL {$this->activeUsersInterval} DAY
															 AND r.community IS NOT NULL
															 {$this->queryExcludeUsers}
														 GROUP BY u.phone) ActiveUsers
													GROUP BY ActiveUsers.community";
			$union = ' UNION ';			
		}

		$parsedData = $this->parseDataWeeksGroup( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unity );
		}
		return $parsedData;
	}

	public function uniqueByWeekByCommunity( $render = false ){

		$query = "SELECT YEARWEEK(date) AS `Week`,
									 COUNT(DISTINCT((u.phone))) AS Total,
									 r.community AS `Group`
						FROM `order` o
						LEFT JOIN user u ON u.id_user = o.id_user
						LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant 
						WHERE YEARWEEK(o.date) >= {$this->weekFrom} AND YEARWEEK(o.date) <= {$this->weekTo} 
							AND r.community IS NOT NULL
							{$this->queryExcludeUsers}
						GROUP BY YEARWEEK(o.date),
										 r.community
						ORDER BY YEARWEEK(o.date) DESC";

		$parsedData = $this->parseDataWeeksGroup( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unity );
		}
		return $parsedData;
	}

	public function newPerActiveByWeek( $render = false ){

		$activeUsers = $this->activeByWeek();
		$newUsers = $this->newByWeek();

		$data = [];
		for( $i = 0; $i < sizeof( $activeUsers ); $i++ ){
			$active = $activeUsers[ $i ]->Total;
			$new = $newUsers[ $i ]->Total;

			if( $active != 0 && $new != 0 ){
				$result = $new / $active;	
			} else {
				$result = 0;
			}
			$data[] = ( object ) array( 'Label' => $activeUsers[ $i ]->Label, 'Total' => number_format( $result, 2 ), 'Type' => 'Total' );
		}

		if( $render ){
			return array( 'data' => $data, 'unit' => $this->unity );
		}
		return $data;
	}

	public function newPerActiveByMonth( $render = false ){

		$activeUsers = $this->activeByMonth();
		$newUsers = $this->newByMonth();

		$data = [];
		for( $i = 0; $i < sizeof( $activeUsers ); $i++ ){
			$active = $activeUsers[ $i ]->Total;
			$new = $newUsers[ $i ]->Total;

			if( $active != 0 && $new != 0 ){
				$result = $new / $active;	
			} else {
				$result = 0;
			}
			$data[] = ( object ) array( 'Label' => $activeUsers[ $i ]->Label, 'Total' => number_format( $result, 2 ), 'Type' => 'Total' );
		}

		if( $render ){
			return array( 'data' => $data, 'unit' => $this->unity, 'interval' => 'month' );
		}
		return $data;
	}

	public function uniqueByWeek( $render = false ){

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

		$parsedData = $this->parseDataWeeksSimple( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unity );
		}
		return $parsedData;
	}

	public function uniqueByMonth( $render = false ){

		$query = "SELECT DATE_FORMAT( o.date ,'%Y-%m') AS Month,
										 COUNT( DISTINCT( ( u.phone ) ) ) AS Total
							FROM `order` o
							INNER JOIN user u ON u.id_user = o.id_user
							LEFT JOIN community c ON o.id_community = c.id_community
							WHERE 
								o.date >= '{$this->monthFrom}-01' AND o.date <= LAST_DAY( STR_TO_DATE( '{$this->monthTo}', '%Y-%m' ) )
								{$this->queryExcludeCommunties}
								{$this->queryExcludeUsers}
							GROUP BY Month
							ORDER BY Month ASC";

		$parsedData = $this->parseDataMonthSimple( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unity, 'interval' => 'month' );
		}
		return $parsedData;
	}

}