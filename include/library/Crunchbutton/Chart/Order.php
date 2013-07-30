<?php 
class Crunchbutton_Chart_Order extends Crunchbutton_Chart {
	
	public $unit = 'orders';
	public $description = 'Orders';

	public $groups = array( 
												'group-orders' => array(
														'title' => 'Orders',
														'tags' => array( 'investors' ),
														'charts' => array(  
																'orders-per-day' => array( 'title' => 'Day', 'interval' => 'day', 'type' => 'column', 'method' => 'byDay', 'filters' => array( array( 'title' => 'Community', 'type' => 'community', 'method' => 'byDayPerCommunity' ), array( 'title' => 'Cohort', 'type' => 'cohort', 'method' => 'byDayCohort' ) ) ),
																'orders-per-week' => array( 'title' => 'Week', 'interval' => 'week', 'type' => 'column', 'method' => 'byWeek', 'filters' => array( array( 'title' => 'Community', 'type' => 'community', 'method' => 'byWeekPerCommunity' ), array( 'title' => 'Cohort', 'type' => 'cohort', 'method' => 'byWeekCohort' ) ) ),
																'orders-per-month' => array( 'title' => 'Month', 'interval' => 'month', 'type' => 'column', 'method' => 'byMonth', 'filters' => array( array( 'title' => 'Community', 'type' => 'community', 'method' => 'byMonthPerCommunity') , array( 'title' => 'Cohort', 'type' => 'cohort', 'method' => 'byMonthCohort' ) ) ),
															)
												),
												'group-orders-per-user' => array(
														'title' => 'Orders per User',
														'tags' => array( 'investors' ),
														'charts' => array(  
																'orders-per-user-per-day' => array( 'title' => 'Day', 'interval' => 'day', 'type' => 'column', 'method' => 'byUsersPerDay', 'filters' => array( array( 'title' => 'Community', 'type' => 'community', 'method' => 'byUsersPerDayByCommunity' ) ) ),
																'orders-per-user-per-week' => array( 'title' => 'Week', 'interval' => 'week', 'type' => 'column', 'method' => 'byUsersPerWeek', 'filters' => array( array( 'title' => 'Community', 'type' => 'community', 'method' => 'byUsersPerWeekByCommunity' ) ) ),
																'orders-per-user-per-month' => array( 'title' => 'Month', 'interval' => 'month', 'type' => 'column', 'method' => 'byUsersPerMonth', 'filters' => array( array( 'title' => 'Community', 'type' => 'community', 'method' => 'byUsersPerMonthByCommunity' ) ) ),
															)
												),
												'group-orders-per-active-user' => array(
														'title' => 'Repeat Orders per Active User',
														'tags' => array( 'main' ),
														'charts' => array(  
																'orders-repeat-per-active-user-per-day' => array( 'title' => 'Day', 'interval' => 'day', 'type' => 'column', 'method' => 'repeatByActiveuserByDay' ),
																'orders-repeat-per-active-user-per-week' => array( 'title' => 'Week', 'interval' => 'week', 'type' => 'column', 'method' => 'repeatByActiveuserByWeek', 'default' => true ),
																'orders-repeat-per-active-user-per-month' => array( 'title' => 'Month', 'interval' => 'month', 'type' => 'column', 'method' => 'repeatByActiveuserByMonth' ),
															)
												),
												'group-orders-repeat' => array(
														'title' => 'Repeat Orders',
														'tags' => array( 'main' ),
														'charts' => array(  
																'orders-repeat-day' => array( 'title' => 'Day', 'interval' => 'day', 'type' => 'column', 'method' => 'repeatPerDay', 'filters' => array( array( 'title' => 'Community', 'type' => 'community', 'method' => 'repeatVsNewPerDayPerCommunity' ) ) ),
																'orders-repeat-week' => array( 'title' => 'Week', 'interval' => 'week', 'type' => 'column', 'method' => 'repeatPerWeek', 'filters' => array( array( 'title' => 'Community', 'type' => 'community', 'method' => 'repeatVsNewPerWeekPerCommunity' ) ), 'default' => true ),
																'orders-repeat-month' => array( 'title' => 'Month', 'interval' => 'month', 'type' => 'column', 'method' => 'repeatPerMonth', 'filters' => array( array( 'title' => 'Community', 'type' => 'community', 'method' => 'repeatVsNewPerMonthPerCommunity' ) ) ),
															)
												),
												'group-orders-repeat-vs-news' => array(
														'title' => 'Repeat vs. New Orders',
														'charts' => array(  
																'orders-repeat-vs-news-day' => array( 'title' => 'Day', 'interval' => 'day', 'type' => 'column', 'method' => 'repeatVsNewPerDay', 'filters' => array( array( 'title' => 'Community', 'type' => 'community', 'method' => 'repeatVsNewPerDayPerCommunity' ) ) ),
																'orders-repeat-vs-news-week' => array( 'title' => 'Week', 'interval' => 'week', 'type' => 'column', 'method' => 'repeatVsNewPerWeek', 'filters' => array( array( 'title' => 'Community', 'type' => 'community', 'method' => 'repeatVsNewPerWeekPerCommunity' ) ) ),
																'orders-repeat-vs-news-month' => array( 'title' => 'Month', 'interval' => 'month', 'type' => 'column', 'method' => 'repeatVsNewPerMonth', 'filters' => array( array( 'title' => 'Community', 'type' => 'community', 'method' => 'repeatVsNewPerMonthPerCommunity' ) ) ),
															)
												),
												'group-orders-by-weekday-by-community' => array(
														'title' => 'Orders by Weekday by Community',
														'tags' => array( 'detailed-analytics' ),
														'charts' => array(  
																'orders-by-weekday-by-community' => array( 'title' => '', 'type' => 'area', 'method' => 'byWeekdayByCommunity' )
															)
												),
												'group-orders-per-restaurant-by-community' => array(
														'title' => 'Orders per Restaurant by Community',
														'tags' => array( 'detailed-analytics' ),
														'charts' => array(  
																'orders-per-restaurant-by-community' => array( 'title' => '', 'type' => 'pie_communities', 'method' => 'perRestaurantPerCommunity' )
															)
												),
												'group-orders-track-frequece' => array(
														'title' => 'Track Orders Frequency',
														'tags' => array( 'detailed-analytics' ),
														'charts' => array(  
																'orders-track-frequece' => array( 'title' => 'Orders', 'interval' => 'week', 'type' => 'area', 'method' => 'trackFrequence' ),
															)
												),
										);

	public function __construct() {
		parent::__construct();
	}

	public function byWeekdayByCommunity( $render = false ){

		$query = "SELECT DATE_FORMAT(CONVERT_TZ(`date`, '-8:00', '-5:00'), '%W') AS `Day`,
										 COUNT(*) AS `Orders`,
										 r.community AS `Community`
							FROM `order` o
							LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant 
							WHERE env = 'live'
								{$this->queryExcludeUsers}
								AND r.community IS NOT NULL
							GROUP BY DATE_FORMAT(CONVERT_TZ(`date`, '-8:00', '-5:00'), '%W'),
								r.community
							ORDER BY DATE_FORMAT(CONVERT_TZ(`date`, '-8:00', '-5:00'), '%Y%m%d'),
								r.community";
		$data = c::db()->get( $query );
		if( $render ){
			return array( 'data' => $data, 'unit' => $this->unit, 'hideSlider' => true, 'hideGroups' => true );
		}
		return $data;
	}

	public function byMonth( $render = false ){

		$query = "SELECT DATE_FORMAT( o.date ,'%Y-%m') AS Month,
											COUNT(*) AS Total
								FROM `order` o
								INNER JOIN user u ON u.id_user = o.id_user
								LEFT JOIN community c ON o.id_community = c.id_community
								WHERE 
									o.date >= '{$this->monthFrom}-01' AND o.date <= LAST_DAY( STR_TO_DATE( '{$this->monthTo}', '%Y-%m' ) )
									{$this->queryExcludeCommunties}
									{$this->queryExcludeUsers}
								GROUP BY DATE_FORMAT(o.date ,'%Y-%m') HAVING Month BETWEEN '{$this->monthFrom}' AND '{$this->monthTo}'";

		$parsedData = $this->parseDataMonthSimple( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit, 'interval' => 'month' );
		}
		return $parsedData;
	}

	public function byWeek( $render = false ){

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

		$parsedData = $this->parseDataWeeksSimple( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit );
		}
		return $parsedData;
	}

	public function byDay( $render = false ){

		$query = "SELECT DATE_FORMAT(o.date ,'%Y-%m-%d') AS Day,
											 COUNT(*) AS Total
								FROM `order` o
								INNER JOIN user u ON u.id_user = o.id_user
								LEFT JOIN community c ON o.id_community = c.id_community
								WHERE 
									1 = 1
									{$this->queryExcludeCommunties}
									{$this->queryExcludeUsers}
								GROUP BY DATE_FORMAT(o.date ,'%Y-%m-%d') HAVING Day BETWEEN '{$this->dayFrom}' AND '{$this->dayTo}'";

		$parsedData = $this->parseDataDaysSimple( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit, 'interval' => 'day' );
		}
		return $parsedData;
	}

	public function byWeekPerCommunity( $render = false ){

		$query = "SELECT YEARWEEK(date) AS Week,
										 COUNT(*) AS Total,
										 r.community AS 'Group'
							FROM `order` o
							INNER JOIN user u ON u.id_user = o.id_user
							LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant 
							WHERE YEARWEEK(o.date) >= {$this->weekFrom} AND YEARWEEK(o.date) <= {$this->weekTo} 
								AND r.community IS NOT NULL
								{$this->queryExcludeUsers}
							GROUP BY YEARWEEK(date), r.community
							ORDER BY YEARWEEK(date) DESC";
		$parsedData = $this->parseDataWeeksGroup( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit );
		}
		return $parsedData;
	}

	public function byDayCohort( $render = false ){

		$id_chart_cohort = $_GET[ 'id_chart_cohort' ];
		$cohort_type = $_GET[ 'cohort_type' ];

		switch ( $cohort_type ) {
			case 'cohort':
				$cohort = Crunchbutton_Chart_Cohort::get( $id_chart_cohort, $cohort_type );
				$query = "SELECT DATE_FORMAT(o.date ,'%Y-%m-%d') AS Day,
													 COUNT(*) AS Total
										FROM `order` o
										INNER JOIN user u ON u.id_user = o.id_user
										LEFT JOIN community c ON o.id_community = c.id_community
										WHERE 
											1 = 1
											{$cohort->toQuery()}
											{$this->queryExcludeCommunties}
											{$this->queryExcludeUsers}
										GROUP BY DATE_FORMAT(o.date ,'%Y-%m-%d') HAVING Day BETWEEN '{$this->dayFrom}' AND '{$this->dayTo}'";
				break;
			
			case 'months':
				$month = $id_chart_cohort;
				$query = "SELECT DATE_FORMAT(o.date ,'%Y-%m-%d') AS Day,
													 COUNT(*) AS Total
										FROM `order` o
										INNER JOIN user u ON u.id_user = o.id_user
										LEFT JOIN community c ON o.id_community = c.id_community
										WHERE 
											1 = 1
											{$this->queryExcludeCommunties}
											{$this->queryExcludeUsers}
											AND o.phone IN( SELECT o.phone
												 FROM `order` o
												 INNER JOIN
													 (SELECT min(id_order) id_order,
																	 o.phone
														FROM `order` o
														GROUP BY o.phone) orders ON o.id_order = orders.id_order
												 AND DATE_FORMAT(o.date ,'%Y-%m') = '{$month}' )
											AND DATE_FORMAT(o.date ,'%Y-%m') >= '{$month}'
										GROUP BY DATE_FORMAT(o.date ,'%Y-%m-%d') HAVING Day BETWEEN '{$this->dayFrom}' AND '{$this->dayTo}'";
				break;

			case 'giftcard':
				$giftcard_group = $id_chart_cohort;
				$query = "SELECT DATE_FORMAT(o.date ,'%Y-%m-%d') AS Day,
													 COUNT(*) AS Total
										FROM `order` o
										INNER JOIN user u ON u.id_user = o.id_user
										LEFT JOIN community c ON o.id_community = c.id_community
										WHERE 
											1 = 1
											{$this->queryExcludeCommunties}
											{$this->queryExcludeUsers}
											AND o.phone IN ( SELECT DISTINCT( u.phone ) FROM credit c 
											INNER JOIN promo_group_promo pg ON pg.id_promo_group = {$giftcard_group} AND pg.id_promo = c.id_promo
											INNER JOIN user u ON u.id_user = c.id_user )
										GROUP BY DATE_FORMAT(o.date ,'%Y-%m-%d') HAVING Day BETWEEN '{$this->dayFrom}' AND '{$this->dayTo}'";
				break;



		}

		$parsedData = $this->parseDataDaysSimple( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit, 'interval' => 'day' );
		}
		return $parsedData;
	}

	public function byMonthCohort( $render = false ){

		$id_chart_cohort = $_GET[ 'id_chart_cohort' ];
		$cohort_type = $_GET[ 'cohort_type' ];

		switch ( $cohort_type ) {
			case 'cohort':
				$cohort = Crunchbutton_Chart_Cohort::get( $id_chart_cohort, $cohort_type );
				$query = "SELECT DATE_FORMAT( o.date ,'%Y-%m') AS Month,
													COUNT(*) AS Total
										FROM `order` o
										INNER JOIN user u ON u.id_user = o.id_user
										LEFT JOIN community c ON o.id_community = c.id_community
										WHERE 
											o.date >= '{$this->monthFrom}-01' AND o.date <= LAST_DAY( STR_TO_DATE( '{$this->monthTo}', '%Y-%m' ) )
											{$this->queryExcludeCommunties}
											{$this->queryExcludeUsers}
											{$cohort->toQuery()}
										GROUP BY DATE_FORMAT(o.date ,'%Y-%m') HAVING Month BETWEEN '{$this->monthFrom}' AND '{$this->monthTo}'";
				break;
			
			case 'months':
				$month = $id_chart_cohort;
				$query = "SELECT DATE_FORMAT( o.date ,'%Y-%m') AS Month,
													COUNT(*) AS Total
										FROM `order` o
										INNER JOIN user u ON u.id_user = o.id_user
										LEFT JOIN community c ON o.id_community = c.id_community
										WHERE 
											o.date >= '{$this->monthFrom}-01' AND o.date <= LAST_DAY( STR_TO_DATE( '{$this->monthTo}', '%Y-%m' ) )
											{$this->queryExcludeCommunties}
											{$this->queryExcludeUsers}
											AND o.phone IN( SELECT o.phone
												 FROM `order` o
												 INNER JOIN
													 (SELECT min(id_order) id_order,
																	 o.phone
														FROM `order` o
														GROUP BY o.phone) orders ON o.id_order = orders.id_order
												 AND DATE_FORMAT(o.date ,'%Y-%m') = '{$month}' )
											AND DATE_FORMAT(o.date ,'%Y-%m') >= '{$month}'
										GROUP BY DATE_FORMAT(o.date ,'%Y-%m') HAVING Month BETWEEN '{$this->monthFrom}' AND '{$this->monthTo}'";
				break;
			case 'giftcard':
				$giftcard_group = $id_chart_cohort;
				$query = "SELECT DATE_FORMAT( o.date ,'%Y-%m') AS Month,
													COUNT(*) AS Total
										FROM `order` o
										INNER JOIN user u ON u.id_user = o.id_user
										LEFT JOIN community c ON o.id_community = c.id_community
										WHERE 
											o.date >= '{$this->monthFrom}-01' AND o.date <= LAST_DAY( STR_TO_DATE( '{$this->monthTo}', '%Y-%m' ) )
											{$this->queryExcludeCommunties}
											{$this->queryExcludeUsers}
										AND o.phone IN ( SELECT DISTINCT( u.phone ) FROM credit c 
											INNER JOIN promo_group_promo pg ON pg.id_promo_group = {$giftcard_group} AND pg.id_promo = c.id_promo
											INNER JOIN user u ON u.id_user = c.id_user )
										GROUP BY DATE_FORMAT(o.date ,'%Y-%m') HAVING Month BETWEEN '{$this->monthFrom}' AND '{$this->monthTo}'";
				break;
		}

		$parsedData = $this->parseDataMonthSimple( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit, 'interval' => 'month' );
		}
		return $parsedData;
	}

	public function byWeekCohort( $render = false ){

		$id_chart_cohort = $_GET[ 'id_chart_cohort' ];
		$cohort_type = $_GET[ 'cohort_type' ];

		switch ( $cohort_type ) {
			case 'cohort':
				$cohort = Crunchbutton_Chart_Cohort::get( $id_chart_cohort, $cohort_type );
				$query = "SELECT YEARWEEK(date) AS Week,
													 COUNT(*) AS Total
										FROM `order` o
										INNER JOIN user u ON u.id_user = o.id_user
										LEFT JOIN community c ON o.id_community = c.id_community
										WHERE 
											YEARWEEK(o.date) >= {$this->weekFrom} AND YEARWEEK(o.date) <= {$this->weekTo} 
											{$this->queryExcludeCommunties}
											{$this->queryExcludeUsers}
											{$cohort->toQuery()}
										GROUP BY YEARWEEK(date)
										ORDER BY YEARWEEK(date) ASC";
				break;
			
			case 'months':
				$month = $id_chart_cohort;
				$query = "SELECT YEARWEEK(date) AS Week,
													 COUNT(*) AS Total
										FROM `order` o
										INNER JOIN user u ON u.id_user = o.id_user
										LEFT JOIN community c ON o.id_community = c.id_community
										WHERE 
											YEARWEEK(o.date) >= {$this->weekFrom} AND YEARWEEK(o.date) <= {$this->weekTo} 
											{$this->queryExcludeCommunties}
											{$this->queryExcludeUsers}
											AND o.phone IN( SELECT o.phone
												 FROM `order` o
												 INNER JOIN
													 (SELECT min(id_order) id_order,
																	 o.phone
														FROM `order` o
														GROUP BY o.phone) orders ON o.id_order = orders.id_order
												 AND DATE_FORMAT(o.date ,'%Y-%m') = '{$month}' )
											AND DATE_FORMAT(o.date ,'%Y-%m') >= '{$month}'
										GROUP BY YEARWEEK(date)
										ORDER BY YEARWEEK(date) ASC";
				break;
			case 'giftcard':
				$giftcard_group = $id_chart_cohort;
				$query = "SELECT YEARWEEK(date) AS Week,
													 COUNT(*) AS Total
										FROM `order` o
										INNER JOIN user u ON u.id_user = o.id_user
										LEFT JOIN community c ON o.id_community = c.id_community
										WHERE 
											YEARWEEK(o.date) >= {$this->weekFrom} AND YEARWEEK(o.date) <= {$this->weekTo} 
											{$this->queryExcludeCommunties}
											{$this->queryExcludeUsers}
											AND o.phone IN ( SELECT DISTINCT( u.phone ) FROM credit c 
											INNER JOIN promo_group_promo pg ON pg.id_promo_group = {$giftcard_group} AND pg.id_promo = c.id_promo
											INNER JOIN user u ON u.id_user = c.id_user )
										GROUP BY YEARWEEK(date)
										ORDER BY YEARWEEK(date) ASC";
											
				break;
		}

		$parsedData = $this->parseDataWeeksSimple( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit );
		}
		return $parsedData;
	}

	public function byDayPerCommunity( $render = false ){

		$query = "SELECT DATE_FORMAT( date ,'%Y-%m-%d') AS Day,
										 COUNT(*) AS Total,
										 r.community AS 'Group'
							FROM `order` o
							INNER JOIN user u ON u.id_user = o.id_user
							LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant 
							WHERE o.date >= '{$this->dayFrom}' AND o.date <= '{$this->dayTo}'
								AND r.community IS NOT NULL
								{$this->queryExcludeUsers}
							GROUP BY DATE_FORMAT( date ,'%Y-%m-%d'), r.community
							ORDER BY DATE_FORMAT( date ,'%Y-%m-%d') DESC";

		$parsedData = $this->parseDataDaysGroup( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit, 'interval' => 'day' );
		}
		return $parsedData;
	}

	public function byMonthPerCommunity( $render = false ){

		$query = "SELECT DATE_FORMAT( date ,'%Y-%m') AS Month,
										 COUNT(*) AS Total,
										 r.community AS 'Group'
							FROM `order` o
							INNER JOIN user u ON u.id_user = o.id_user
							LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant 
							WHERE o.date >= '{$this->monthFrom}-01' AND o.date <= LAST_DAY( STR_TO_DATE( '{$this->monthTo}', '%Y-%m' ) )
								AND r.community IS NOT NULL
								{$this->queryExcludeUsers}
							GROUP BY DATE_FORMAT( date ,'%Y-%m'), r.community
							ORDER BY DATE_FORMAT( date ,'%Y-%m') DESC";
		$parsedData = $this->parseDataMonthGroup( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit, 'interval' => 'month' );
		}
		return $parsedData;
	}

	public function perRestaurantPerCommunity( $render = false ){

		$query = "SELECT r.name AS Restaurant,
										 orders.orders AS Total,
										 r.community AS 'Group'
							FROM
								(SELECT count(*) AS orders,
												o.id_restaurant
								 FROM `order` o
								 WHERE o.date BETWEEN CURDATE() - INTERVAL 14 DAY AND CURDATE()
								 GROUP BY o.id_restaurant) orders
							INNER JOIN restaurant r ON r.id_restaurant = orders.id_restaurant
							WHERE r.community IS NOT NULL";

		$data = c::db()->get( $query );
		$groups = [];
		foreach( $data as $item ){
			$groups[ $item->Group ][] = array( 'Restaurant' => $item->Restaurant, 'Orders' => $item->Total );
		}

		if( $render ){
			return array( 'data' => $groups, 'unit' => $this->unit );
		}
		return $data;
	}

	public function byUsersPerDay( $render = false ){

		$query = "SELECT DATE_FORMAT( o.date ,'%Y-%m-%d') AS Day,
										 CAST(COUNT(*) / COUNT(DISTINCT((u.phone))) AS DECIMAL(14, 2)) Total
							FROM `order` o
							INNER JOIN user u ON u.id_user = o.id_user
							LEFT JOIN community c ON o.id_community = c.id_community
							WHERE o.date >= '{$this->dayFrom}' AND o.date <= '{$this->dayTo}'
								{$this->queryExcludeCommunties}
								{$this->queryExcludeUsers}
							GROUP BY Day
							ORDER BY Day ASC";

		$parsedData = $this->parseDataDaysSimple( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit, 'interval' => 'day' );
		}
		return $parsedData;
	}

	public function byUsersPerDayByCommunity( $render = false ){
		$query = "SELECT DATE_FORMAT( o.date ,'%Y-%m-%d') AS Day,
										 CAST(COUNT(*) / COUNT(DISTINCT((u.phone))) AS DECIMAL(14, 2)) Total,
										 r.community AS 'Group'
							FROM `order` o
							INNER JOIN user u ON u.id_user = o.id_user
							LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant 
							WHERE 1 = 1
								AND r.community IS NOT NULL
							GROUP BY DATE_FORMAT(o.date ,'%Y-%m-%d') HAVING Day BETWEEN '{$this->dayFrom}' AND '{$this->dayTo}'";

		$parsedData = $this->parseDataDaysGroup( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit, 'interval' => 'day' );
		}
		return $parsedData;
	}

	public function byUsersPerMonth( $render = false ){

		$query = "SELECT DATE_FORMAT( o.date ,'%Y-%m') AS Month,
										 CAST(COUNT(*) / COUNT(DISTINCT((u.phone))) AS DECIMAL(14, 2)) Total
							FROM `order` o
							INNER JOIN user u ON u.id_user = o.id_user
							LEFT JOIN community c ON o.id_community = c.id_community
							WHERE o.date >= '{$this->monthFrom}-01' AND o.date <= LAST_DAY( STR_TO_DATE( '{$this->monthTo}', '%Y-%m' ) )
								{$this->queryExcludeCommunties}
								{$this->queryExcludeUsers}
							GROUP BY Month
							ORDER BY Month ASC";

		$parsedData = $this->parseDataMonthSimple( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit, 'interval' => 'month' );
		}
		return $parsedData;
	}

	public function byUsersPerMonthByCommunity( $render = false ){
		$query = "SELECT DATE_FORMAT( o.date ,'%Y-%m') AS Month,
										 CAST(COUNT(*) / COUNT(DISTINCT((u.phone))) AS DECIMAL(14, 2)) Total,
										 r.community AS 'Group'
							FROM `order` o
							INNER JOIN user u ON u.id_user = o.id_user
							LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant 
							WHERE 1 = 1
								AND r.community IS NOT NULL
							GROUP BY DATE_FORMAT(o.date ,'%Y-%m') HAVING Month BETWEEN '{$this->monthFrom}' AND '{$this->monthTo}'";

		$parsedData = $this->parseDataMonthGroup( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit, 'interval' => 'month' );
		}
		return $parsedData;
	}

	public function byUsersPerWeek( $render = false ){

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

		$parsedData = $this->parseDataWeeksSimple( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit );
		}
		return $parsedData;
	}

	public function byUsersPerWeekByCommunity( $render = false ){
		$query = "SELECT YEARWEEK(date) AS Week,
										 CAST(COUNT(*) / COUNT(DISTINCT((u.phone))) AS DECIMAL(14, 2)) Total,
										 r.community AS 'Group'
							FROM `order` o
							INNER JOIN user u ON u.id_user = o.id_user
							LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant 
							WHERE 1 = 1
								AND r.community IS NOT NULL
							GROUP BY YEARWEEK(date) HAVING Week BETWEEN '{$this->weekFrom}' AND '{$this->weekTo}'";

		$parsedData = $this->parseDataWeeksGroup( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit );
		}
		return $parsedData;
	}

	public function repeatVsNewPerDay( $render = false ){

		$user = new Crunchbutton_Chart_User();
		$newUsers = $user->newByDay();
		$orders = $this->byDay();

		$data = [];

		$days = [];

		foreach ( $orders as $order ) {
			$days[ $order->Label ] = [];
			$days[ $order->Label ][ 'Order' ] = $order->Total;
		}

		foreach ( $newUsers as $new ) {
			if( !$days[ $new->Label ] ){
				$days[ $new->Label ] = [];	
			}
			$days[ $new->Label ][ 'New' ] = $new->Total;
		}

		$data = [];

		foreach ( $days as $label => $values ) {
			$new = $values[ 'New' ];
			$repeat = $values[ 'Order' ] - $new;
			$data[] = ( object ) array( 'Label' => $label, 'Total' => $new, 'Type' => 'New'  ); 
			$data[] = ( object ) array( 'Label' => $label, 'Total' => $repeat, 'Type' => 'Repeated'  ); 
		}

		if( $render ){
			return array( 'data' => $data, 'unit' => $this->unit, 'interval' => 'day' );
		}
		return $data;
	}

	public function repeatPerDay( $render = false ){

		$user = new Crunchbutton_Chart_User();
		$newUsers = $user->newByDay();
		$orders = $this->byDay();

		$data = [];

		$days = [];

		foreach ( $orders as $order ) {
			$days[ $order->Label ] = [];
			$days[ $order->Label ][ 'Order' ] = $order->Total;
		}

		foreach ( $newUsers as $new ) {
			if( !$days[ $new->Label ] ){
				$days[ $new->Label ] = [];	
			}
			$days[ $new->Label ][ 'New' ] = $new->Total;
		}

		$data = [];

		foreach ( $days as $label => $values ) {
			$new = $values[ 'New' ];
			$repeat = $values[ 'Order' ] - $new;
			$data[] = ( object ) array( 'Label' => $label, 'Total' => $repeat, 'Type' => 'Repeated'  ); 
		}

		if( $render ){
			return array( 'data' => $data, 'unit' => $this->unit, 'interval' => 'day' );
		}
		return $data;
	}

	public function repeatVsNewPerDayPerCommunity( $render = false ){

		$user = new Crunchbutton_Chart_User();
		$newUsers = $user->newByDayByCommunity();
		$orders = $this->byDayPerCommunity();

		$data = [];

		$days = [];
		$groups = [];

		foreach ( $orders as $order ) {
			if( !$days[ $order->Label ] ){
				$days[ $order->Label ] = [];	
			}
			$days[ $order->Label ][ 'Order' ][ $order->Type ] = $order->Total;
			$groups[ $order->Type ] = $order->Type;
		}

		foreach ( $newUsers as $new ) {
			if( !$days[ $new->Label ] ){
				$days[ $new->Label ] = [];	
			}
			$days[ $new->Label ][ 'New' ][ $new->Type ] = $new->Total;
			$groups[ $new->Type ] = $new->Type;
		}

		$data = [];

		foreach ( $days as $label => $values ) {
			foreach( $groups as $group ){
				$new = ( $values[ 'New' ][ $group ] ) ? $values[ 'New' ][ $group ] : 0;
				$orders = ( $values[ 'Order' ][ $group ] ) ? $values[ 'Order' ][ $group ] : 0;
				$repeat = $orders - $new;
				$data[] = ( object ) array( 'Label' => $label, 'Total' => $new, 'Type' => "$group New" );
				$data[] = ( object ) array( 'Label' => $label, 'Total' => $repeat, 'Type' => "$group Repeated" );
			}
		}

		if( $render ){
			return array( 'data' => $data, 'unit' => $this->unit, 'interval' => 'day' );
		}

		return $data;
	}

	public function repeatVsNewPerMonthPerCommunity( $render = false ){

		$user = new Crunchbutton_Chart_User();
		$newUsers = $user->newByMonthByCommunity();
		$orders = $this->byMonthPerCommunity();

		$data = [];

		$months = [];
		$groups = [];

		foreach ( $orders as $order ) {
			if( !$months[ $order->Label ] ){
				$months[ $order->Label ] = [];	
			}
			$months[ $order->Label ][ 'Order' ][ $order->Type ] = $order->Total;
			$groups[ $order->Type ] = $order->Type;
		}

		foreach ( $newUsers as $new ) {
			if( !$months[ $new->Label ] ){
				$months[ $new->Label ] = [];	
			}
			$months[ $new->Label ][ 'New' ][ $new->Type ] = $new->Total;
			$groups[ $new->Type ] = $new->Type;
		}

		$data = [];

		foreach ( $months as $label => $values ) {
			foreach( $groups as $group ){
				$new = ( $values[ 'New' ][ $group ] ) ? $values[ 'New' ][ $group ] : 0;
				$orders = ( $values[ 'Order' ][ $group ] ) ? $values[ 'Order' ][ $group ] : 0;
				$repeat = $orders - $new;
				$data[] = ( object ) array( 'Label' => $label, 'Total' => $new, 'Type' => "$group New" );
				$data[] = ( object ) array( 'Label' => $label, 'Total' => $repeat, 'Type' => "$group Repeated" );
			}
		}

		if( $render ){
			return array( 'data' => $data, 'unit' => $this->unit, 'interval' => 'month' );
		}

		return $data;
	}

	public function repeatVsNewPerWeekPerCommunity( $render = false ){

		$user = new Crunchbutton_Chart_User();
		$newUsers = $user->newByWeekByCommunity();
		$orders = $this->byWeekPerCommunity();

		$data = [];

		$weeks = [];
		$groups = [];

		foreach ( $orders as $order ) {
			if( !$weeks[ $order->Label ] ){
				$weeks[ $order->Label ] = [];	
			}
			$weeks[ $order->Label ][ 'Order' ][ $order->Type ] = $order->Total;
			$groups[ $order->Type ] = $order->Type;
		}

		foreach ( $newUsers as $new ) {
			if( !$weeks[ $new->Label ] ){
				$weeks[ $new->Label ] = [];	
			}
			$weeks[ $new->Label ][ 'New' ][ $new->Type ] = $new->Total;
			$groups[ $new->Type ] = $new->Type;
		}

		$data = [];

		foreach ( $weeks as $label => $values ) {
			foreach( $groups as $group ){
				$new = ( $values[ 'New' ][ $group ] ) ? $values[ 'New' ][ $group ] : 0;
				$orders = ( $values[ 'Order' ][ $group ] ) ? $values[ 'Order' ][ $group ] : 0;
				$repeat = $orders - $new;
				$data[] = ( object ) array( 'Label' => $label, 'Total' => $new, 'Type' => "$group New" );
				$data[] = ( object ) array( 'Label' => $label, 'Total' => $repeat, 'Type' => "$group Repeated" );
			}
		}

		if( $render ){
			return array( 'data' => $data, 'unit' => $this->unit );
		}

		return $data;
	}

	public function repeatVsNewPerWeek( $render = false ){

		$user = new Crunchbutton_Chart_User();
		$newUsers = $user->newByWeek();
		$orders = $this->byWeek();

		$data = [];

		$weeks = [];

		foreach ( $orders as $order ) {
			$weeks[ $order->Label ] = [];
			$weeks[ $order->Label ][ 'Order' ] = $order->Total;
		}

		foreach ( $newUsers as $new ) {
			if( !$weeks[ $new->Label ] ){
				$weeks[ $new->Label ] = [];	
			}
			$weeks[ $new->Label ][ 'New' ] = $new->Total;
		}

		$data = [];

		foreach ( $weeks as $label => $values ) {
			$new = $values[ 'New' ];
			$repeat = $values[ 'Order' ] - $new;
			$data[] = ( object ) array( 'Label' => $label, 'Total' => $new, 'Type' => 'New'  ); 
			$data[] = ( object ) array( 'Label' => $label, 'Total' => $repeat, 'Type' => 'Repeated'  ); 
		}

		if( $render ){
			return array( 'data' => $data, 'unit' => $this->unit );
		}
		return $data;
	}

	public function repeatPerWeek( $render = false ){

		$user = new Crunchbutton_Chart_User();
		$newUsers = $user->newByWeek();
		$orders = $this->byWeek();

		$data = [];

		$weeks = [];

		foreach ( $orders as $order ) {
			$weeks[ $order->Label ] = [];
			$weeks[ $order->Label ][ 'Order' ] = $order->Total;
		}

		foreach ( $newUsers as $new ) {
			if( !$weeks[ $new->Label ] ){
				$weeks[ $new->Label ] = [];	
			}
			$weeks[ $new->Label ][ 'New' ] = $new->Total;
		}

		$data = [];

		foreach ( $weeks as $label => $values ) {
			$new = $values[ 'New' ];
			$repeat = $values[ 'Order' ] - $new;
			$data[] = ( object ) array( 'Label' => $label, 'Total' => $repeat, 'Type' => 'Repeated'  ); 
		}

		if( $render ){
			return array( 'data' => $data, 'unit' => $this->unit );
		}
		return $data;
	}

	public function repeatVsNewPerMonth( $render = false ){

		$user = new Crunchbutton_Chart_User();
		$newUsers = $user->newByMonth();
		$orders = $this->byMonth();

		$data = [];

		$months = [];

		foreach ( $orders as $order ) {
			$months[ $order->Label ] = [];
			$months[ $order->Label ][ 'Order' ] = $order->Total;
		}

		foreach ( $newUsers as $new ) {
			if( !$months[ $new->Label ] ){
				$months[ $new->Label ] = [];	
			}
			$months[ $new->Label ][ 'New' ] = $new->Total;
		}

		$data = [];

		foreach ( $months as $label => $values ) {
			$new = $values[ 'New' ];
			$repeat = $values[ 'Order' ] - $new;
			$data[] = ( object ) array( 'Label' => $label, 'Total' => $new, 'Type' => 'New'  ); 
			$data[] = ( object ) array( 'Label' => $label, 'Total' => $repeat, 'Type' => 'Repeated'  ); 
		}

		if( $render ){
			return array( 'data' => $data, 'unit' => $this->unit, 'interval' => 'month' );
		}
		return $data;
	}

	public function repeatPerMonth( $render = false ){

		$user = new Crunchbutton_Chart_User();
		$newUsers = $user->newByMonth();
		$orders = $this->byMonth();

		$data = [];

		$months = [];

		foreach ( $orders as $order ) {
			$months[ $order->Label ] = [];
			$months[ $order->Label ][ 'Order' ] = $order->Total;
		}

		foreach ( $newUsers as $new ) {
			if( !$months[ $new->Label ] ){
				$months[ $new->Label ] = [];	
			}
			$months[ $new->Label ][ 'New' ] = $new->Total;
		}

		$data = [];

		foreach ( $months as $label => $values ) {
			$new = $values[ 'New' ];
			$repeat = $values[ 'Order' ] - $new;
			$data[] = ( object ) array( 'Label' => $label, 'Total' => $repeat, 'Type' => 'Repeated'  ); 
		}

		if( $render ){
			return array( 'data' => $data, 'unit' => $this->unit, 'interval' => 'month' );
		}
		return $data;
	}
	public function repeatByActiveuserByWeek( $render = false ){

		$user = new Crunchbutton_Chart_User();

		$activeUsers = $user->activeByWeek();
		$newUsers = $user->newByWeek();
		$orders = $this->byWeek();

		$data = [];
		for( $i = 0; $i < sizeof( $activeUsers ); $i++ ){
			$active = $activeUsers[ $i ]->Total;
			$order = $orders[ $i ]->Total;
			$new = $newUsers[ $i ]->Total;
			if( $i - 1 >= 0 ){
				$activePrev = $activeUsers[ $i - 1 ]->Total;
			} else {
				$activePrev = 0;
			}
			
			// Formula (Orders minus New Users) / (Active Users) | Active Users = ( average of the current week and previous week's Active Users )
			$activeUsersAvg = ( $active + $activePrev ) / 2;

			$ordersMinusNewUsers = $order - $new;

			if( $ordersMinusNewUsers != 0 && $activeUsersAvg != 0 ){
				$result = ( $order - $new ) / ( $activeUsersAvg );	
			} else {
				$result = 0;
			}

			$data[] = ( object ) array( 'Label' => $activeUsers[ $i ]->Label, 'Total' => number_format( $result, 4 ), 'Type' => 'Total' );
		}

		if( $render ){
			return array( 'data' => $data, 'unit' => $this->unit );
		}
		return $data;
	}

	public function repeatByActiveuserByMonth( $render = false ){

		$user = new Crunchbutton_Chart_User();

		$activeUsers = $user->activeByMonth();
		$newUsers = $user->newByMonth();
		$orders = $this->byMonth();

		$data = [];
		for( $i = 0; $i < sizeof( $activeUsers ); $i++ ){
			$active = $activeUsers[ $i ]->Total;
			$order = $orders[ $i ]->Total;
			$new = $newUsers[ $i ]->Total;
			if( $i - 1 >= 0 ){
				$activePrev = $activeUsers[ $i - 1 ]->Total;
			} else {
				$activePrev = 0;
			}
			
			// Formula (Orders minus New Users) / (Active Users) | Active Users = ( average of the current week and previous week's Active Users )
			$activeUsersAvg = ( $active + $activePrev ) / 2;

			$ordersMinusNewUsers = $order - $new;

			if( $ordersMinusNewUsers != 0 && $activeUsersAvg != 0 ){
				$result = ( $order - $new ) / ( $activeUsersAvg );	
			} else {
				$result = 0;
			}

			$data[] = ( object ) array( 'Label' => $activeUsers[ $i ]->Label, 'Total' => number_format( $result, 4 ), 'Type' => 'Total' );
		}

		if( $render ){
			return array( 'data' => $data, 'unit' => $this->unit, 'interval' => 'month' );
		}
		return $data;
	}

	public function repeatByActiveuserByDay( $render = false ){

		$user = new Crunchbutton_Chart_User();

		$activeUsers = $user->activeByDay();
		$newUsers = $user->newByDay();
		$orders = $this->byDay();

		$data = [];
		for( $i = 0; $i < sizeof( $activeUsers ); $i++ ){
			$active = $activeUsers[ $i ]->Total;
			$order = $orders[ $i ]->Total;
			$new = $newUsers[ $i ]->Total;
			if( $i - 1 >= 0 ){
				$activePrev = $activeUsers[ $i - 1 ]->Total;
			} else {
				$activePrev = 0;
			}
			
			// Formula (Orders minus New Users) / (Active Users) | Active Users = ( average of the current week and previous week's Active Users )
			$activeUsersAvg = ( $active + $activePrev ) / 2;

			$ordersMinusNewUsers = $order - $new;

			if( $ordersMinusNewUsers != 0 && $activeUsersAvg != 0 ){
				$result = ( $order - $new ) / ( $activeUsersAvg );	
			} else {
				$result = 0;
			}

			$data[] = ( object ) array( 'Label' => $activeUsers[ $i ]->Label, 'Total' => number_format( $result, 4 ), 'Type' => 'Total' );
		}

		if( $render ){
			return array( 'data' => $data, 'unit' => $this->unit, 'interval' => 'day' );
		}
		return $data;
	}

	public function trackFrequence( $render = false ){
		
		$allWeeks = $this->allWeeks();

		$query = '';
		$union = '';

		$_data = [];

		for( $i = $this->from -1 ; $i < $this->to; $i++ ){
			$week = $allWeeks[ $i ];
			$_data[ $week ] = array( 
											'Power' => 0,
											'Weekly' => 0,
											'Bi-Weekly' => 0,
											'Tri-Weekly' => 0,
											'Monthly' => 0
											);
			$query .= $union . 
								"SELECT YEARWEEK(date) AS `Week`,
										 u.phone AS 'Phone'
								FROM `order` o
								INNER JOIN user u ON u.id_user = o.id_user
								LEFT JOIN community c ON o.id_community = c.id_community
								WHERE YEARWEEK(o.date) = {$week} 
									{$this->queryExcludeCommunties}
									{$this->queryExcludeUsers}";
			$union = ' UNION ';
		}

		$users = c::db()->get( $query );
		
		foreach( $users as $user ){

			$week = $user->Week;

			$query = "SELECT * FROM 
								( SELECT o.date AS day1 FROM `order` o WHERE o.phone = '{$user->Phone}' AND YEARWEEK(o.date) <= {$user->Week} ORDER BY id_order DESC LIMIT 3, 1 ) day1, 
								( SELECT o.date AS day2 FROM `order` o WHERE o.phone = '{$user->Phone}' AND YEARWEEK(o.date) <= {$user->Week} ORDER BY id_order DESC LIMIT 1 ) day2";

			$days = c::db()->get( $query );
			$days = $days->_items[0];
			
			$query = "SELECT COUNT(*) AS orders FROM `order` o WHERE o.phone = '{$user->Phone}' AND YEARWEEK(o.date) = '{$user->Week}'";
			$orders = c::db()->get( $query );
			$orders = $orders->_items[0]->orders;

			if( $days ){

				$interval = date_diff( date_create( $days->day1 ), date_create( $days->day2 ) );
				$days = intval( $interval->format('%d') );

				if( $days <= 4 ){ $_data[ $week ][ 'Power' ] += $orders; }
				if( $days > 4 && $days < 11 ){ $_data[ $week ][ 'Weekly' ] += $orders; }
				if( $days > 11 && $days < 18 ){ $_data[ $week ][ 'Bi-Weekly' ] += $orders; }
				if( $days > 18 && $days < 25 ){ $_data[ $week ][ 'Tri-Weekly' ] += $orders; }
				if( $days > 25 ){ $_data[ $week ][ 'Monthly' ] += $orders; }
			}
		}
		
		$data = [];
		foreach( $_data as $week => $info ){
			foreach( $info as $type => $value ){
				$data[] = ( object ) array( 'Label' => $week, 'Total' => $value, 'Type' => $type );
			}
		}

		if( $render ){
			return array( 'data' => $data, 'unit' => $this->unit, 'hideGroups' => true );
		}
		return $data;
	}


}