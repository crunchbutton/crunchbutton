<?php
class Crunchbutton_Chart_User extends Crunchbutton_Chart {

	public $unit = 'users';
	public $description = 'Users';

	public $groups = array(

												'group-new-users' => array(
														'title' => 'New Users',
														'tags' => array( 'main' ),
														'charts' => array(
																'users-new-per-day' => array( 'title' => 'Day', 'interval' => 'day', 'type' => 'column', 'method' => 'newByDay', 'filters' => array( array( 'title' => 'Community', 'type' => 'community', 'method' => 'newByDayByCommunityGrouped' ), array( 'title' => 'Cohort', 'type' => 'cohort', 'method' => 'newByDayCohort' ) ) ),
																'users-new-per-week' => array( 'title' => 'Week', 'interval' => 'week', 'type' => 'column', 'method' => 'newByWeek', 'filters' => array( array( 'title' => 'Community', 'type' => 'community', 'method' => 'newByWeekByCommunityGrouped' ), array( 'title' => 'Cohort', 'type' => 'cohort', 'method' => 'newByWeekCohort' ) ), 'default' => true ),
																'users-new-per-month' => array( 'title' => 'Month', 'interval' => 'month', 'type' => 'column', 'method' => 'newByMonth', 'filters' => array( array( 'title' => 'Community', 'type' => 'community', 'method' => 'newByMonthByCommunityGrouped' ), array( 'title' => 'Cohort', 'type' => 'cohort', 'method' => 'newByMonthCohort' ) ) ),
															)
												),
												'group-historical-new-users' => array(
														'title' => 'New + Unchurned Users',
														'tags' => array( 'detailed-analytics' ),
														'charts' => array(
																'users-new-per-day-historical' => array( 'title' => 'Day', 'interval' => 'day', 'type' => 'column', 'method' => 'newByDayHistorical' ),
																'users-new-per-week-historical' => array( 'title' => 'Week', 'interval' => 'week', 'type' => 'column', 'method' => 'newByWeekHistorical' ),
																'users-new-per-month-historical' => array( 'title' => 'Month', 'interval' => 'month', 'type' => 'column', 'method' => 'newByMonthHistorical' ),
															)
												),
												'group-new-users-community' => array(
														'title' => 'New Users',
														'tags' => array( 'reps' ),
														'charts' => array(
																'users-new-per-day-by-community' => array( 'title' => 'Day', 'interval' => 'day', 'type' => 'column-community', 'method' => 'newByDayByCommunity' ),
																'users-new-per-week-by-community' => array( 'title' => 'Week', 'interval' => 'week', 'type' => 'column-community', 'method' => 'newByWeekByCommunity', 'default' => true ),
																'users-new-per-month-by-community' => array( 'title' => 'Month', 'interval' => 'month', 'type' => 'column-community', 'method' => 'newByMonthByCommunity' ),
															)
												),
												'group-new-users-restaurant' => array(
														'title' => 'New Users',
														'tags' => array( 'reps' ),
														'charts' => array(
																'users-new-per-day-by-restaurant' => array( 'title' => 'Day', 'interval' => 'day', 'type' => 'column-restaurant', 'method' => 'newByDayByRestaurant' ),
																'users-new-per-week-by-restaurant' => array( 'title' => 'Week', 'interval' => 'week', 'type' => 'column-restaurant', 'method' => 'newByWeekByRestaurant', 'default' => true ),
																'users-new-per-month-by-restaurant' => array( 'title' => 'Month', 'interval' => 'month', 'type' => 'column-restaurant', 'method' => 'newByMonthByRestaurant' ),
															)
												),
												'group-users-repeat-community' => array(
														'title' => 'Repeat Orders',
														'tags' => array( 'reps' ),
														'charts' => array(
																'orders-repeat-day-by-community' => array( 'title' => 'Day', 'interval' => 'day', 'type' => 'column-community', 'method' => 'repeatPerDayByCommunity' ),
																'orders-repeat-week-by-community' => array( 'title' => 'Week', 'interval' => 'week', 'type' => 'column-community', 'method' => 'repeatPerWeekByCommunity', 'default' => true ),
																'orders-repeat-month-by-community' => array( 'title' => 'Month', 'interval' => 'month', 'type' => 'column-community', 'method' => 'repeatPerMonthByCommunity' ),
															)
												),
												'group-users-repeat-restaurant' => array(
														'title' => 'Repeat Orders',
														'tags' => array( 'reps' ),
														'charts' => array(
																'orders-repeat-day-by-restaurant' => array( 'title' => 'Day', 'interval' => 'day', 'type' => 'column-restaurant', 'method' => 'repeatPerDayByRestaurant' ),
																'orders-repeat-week-by-restaurant' => array( 'title' => 'Week', 'interval' => 'week', 'type' => 'column-restaurant', 'method' => 'repeatPerWeekByRestaurant', 'default' => true ),
																'orders-repeat-month-by-restaurant' => array( 'title' => 'Month', 'interval' => 'month', 'type' => 'column-restaurant', 'method' => 'repeatPerMonthByRestaurant' ),
															)
												),
												'group-users-repeat' => array(
														'title' => 'Repeat Orders',
														'tags' => array( 'main' ),
														'charts' => array(
																'orders-repeat-day' => array( 'title' => 'Day', 'interval' => 'day', 'type' => 'column', 'method' => 'repeatPerDay', 'filters' => array( array( 'title' => 'Community', 'type' => 'community', 'method' => 'repeatPerDayByCommunityGrouped' ) ) ),
																'orders-repeat-week' => array( 'title' => 'Week', 'interval' => 'week', 'type' => 'column', 'method' => 'repeatPerWeek', 'default' => true, 'filters' => array( array( 'title' => 'Community', 'type' => 'community', 'method' => 'repeatPerWeekByCommunityGrouped' ) ) ),
																'orders-repeat-month' => array( 'title' => 'Month', 'interval' => 'month', 'type' => 'column', 'method' => 'repeatPerMonth', 'filters' => array( array( 'title' => 'Community', 'type' => 'community', 'method' => 'repeatPerMonthByCommunityGrouped' ) ) ),
															)
												),
												'group-users-reclaimed' => array(
														'tags' => array( 'detailed-analytics' ),
														'title' => 'Reclaimed Users',
														'charts' => array(
																'users-reclaimed-per-day' => array( 'title' => 'Day', 'interval' => 'day', 'type' => 'column', 'method' => 'reclaimedByDay' ),
																'users-reclaimed-per-week' => array( 'title' => 'Week', 'interval' => 'week', 'type' => 'column', 'method' => 'reclaimedByWeek' ),
																'users-reclaimed-per-month' => array( 'title' => 'Month', 'interval' => 'month', 'type' => 'column', 'method' => 'reclaimedByMonth' ),
															)
												),

												'group-users-track-frequece' => array(
														'title' => 'Track User Frequency',
														'tags' => array( 'detailed-analytics' ),
														'charts' => array(
																'users-track-frequece' => array( 'title' => 'Users', 'interval' => 'week', 'type' => 'area', 'method' => 'trackFrequence' ),
															)
												),

												'group-new-users-per-active-user' => array(
														'title' => 'New Users per Active Users',
														'tags' => array( 'main' ),
														'charts' => array(
																'users-new-per-active-users-per-day' => array( 'title' => 'Day', 'interval' => 'day', 'type' => 'column', 'method' => 'newPerActiveByDay' ),
																'users-new-per-active-users-per-week' => array( 'title' => 'Week', 'interval' => 'week', 'type' => 'column', 'method' => 'newPerActiveByWeek', 'filters' => array( array( 'title' => 'Community', 'type' => 'community', 'method' => 'newPerActiveByWeekByCommunity' ) ), 'default' => true ),
																'users-new-per-active-users-per-month' => array( 'title' => 'Month', 'interval' => 'month', 'type' => 'column', 'method' => 'newPerActiveByMonth', 'filters' => array( array( 'title' => 'Community', 'type' => 'community', 'method' => 'newPerActiveByMonthByCommunity' ) ) )
															)
												),

												'group-new-users-per-active-user-by-community' => array(
														'title' => 'New Users per Active Users',
														'tags' => array( 'main','reps' ),
														'charts' => array(
																'users-new-per-active-users-per-day-by-community' => array( 'title' => 'Day', 'interval' => 'day', 'type' => 'column-community', 'method' => 'newPerActiveByDay' ),
																'users-new-per-active-users-per-week-by-community' => array( 'title' => 'Week', 'interval' => 'week', 'type' => 'column-community', 'method' => 'newPerActiveByWeek', 'default' => true ),
																'users-new-per-active-users-per-month-by-community' => array( 'title' => 'Month', 'interval' => 'month', 'type' => 'column-community', 'method' => 'newPerActiveByMonth' )
															)
												),

												'group-unique-users' => array(
														'title' => 'Unique Users',
														'charts' => array(
																'users-unique-per-day' => array( 'title' => 'Day', 'interval' => 'day', 'type' => 'column', 'method' => 'uniqueByDay', 'filters' => array( array( 'title' => 'Community', 'type' => 'community', 'method' => 'uniqueByDayByCommunity' ) ) ),
																'users-unique-per-week' => array( 'title' => 'Week', 'interval' => 'week', 'type' => 'column', 'method' => 'uniqueByWeek', 'filters' => array( array( 'title' => 'Community', 'type' => 'community', 'method' => 'uniqueByWeekByCommunity' ) ) ),
																'users-unique-per-month' => array( 'title' => 'Month', 'interval' => 'month', 'type' => 'column', 'method' => 'uniqueByMonth',  'filters' => array( array( 'title' => 'Community', 'type' => 'community', 'method' => 'uniqueByMonthByCommunity' ) ) ),
															)
												),

												'group-active-users' => array(
														'title' => 'Active Users',
														'tags' => array( 'investors' ),
														'charts' => array(
																'users-active-per-day' => array( 'title' => 'Day', 'type' => 'column', 'interval' => 'day', 'method' => 'activeByDay', 'filters' => array( array( 'title' => 'Cohort', 'type' => 'cohort', 'method' => 'activeByDayCohort' ) ) ),
																'users-active-per-week' => array( 'title' => 'Week', 'type' => 'column', 'interval' => 'week', 'method' => 'activeByWeek', 'filters' => array( array( 'title' => 'Community', 'type' => 'community', 'method' => 'activeByWeekByCommunity' ), array( 'title' => 'Cohort', 'type' => 'cohort', 'method' => 'activeByWeekCohort' ) ) ),
																'users-active-per-month' => array( 'title' => 'Month', 'type' => 'column', 'interval' => 'month', 'method' => 'activeByMonth', 'filters' => array( array( 'title' => 'Community', 'type' => 'community', 'method' => 'activeByMonthByCommunity' ), array( 'title' => 'Cohort', 'type' => 'cohort', 'method' => 'activeByMonthCohort' ) ) ),
															)
												),
										);

	public function __construct() {
		parent::__construct();
	}

	public function activeHistoricalByDay( $render = false ){

		$query = [];

		$allMonths = $this->allDays();

		$now = time();

		for( $i = $this->from_day -1 ; $i < $this->to_day; $i++ ){
			$day = $allMonths[ $i ];

			$days = floor( ( $now - strtotime( $day ) ) / ( 60 * 60 * 24 ) ) + $this->activeUsersInterval;

			$query[] = "SELECT '{$day}' AS Day,
													COUNT(*) AS Total FROM
													( SELECT DISTINCT( o.phone ) FROM `order` o WHERE o.date <= '{$day}'  ) a,
													( SELECT DISTINCT( o.phone ) FROM `order` o WHERE o.date >= NOW() - INTERVAL $days DAY ) b
										WHERE a.phone = b.phone";
		}

		$parsedData = $this->parseDataDaysSimple( join( ' UNION ', $query ), $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit, 'interval' => 'day' );
		}
		return $parsedData;
	}

	public function activeByDay( $render = false ){

		$query = [];

		$community = ( $_REQUEST[ 'community' ] ? 'AND o.id_community = ' . $_REQUEST[ 'community' ] : '' );

		$allMonths = $this->allDays();
		for( $i = $this->from_day -1 ; $i < $this->to_day; $i++ ){
			$day = $allMonths[ $i ];
			$query[] = "SELECT '{$day}' AS Day,
																 COUNT(*) AS Total
													FROM
														( SELECT o.phone,
																		 o.date,
																		 o.id_user
														 FROM `order` o
														 WHERE o.date <= '{$day}'
														 	 AND o.date >= '{$day}' - INTERVAL {$this->activeUsersInterval} DAY
															 {$this->queryExcludeUsers}
															 {$community}
														 GROUP BY o.phone ) ActiveUsers";
		}

		$parsedData = $this->parseDataDaysSimple( join( ' UNION ', $query ), $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit, 'interval' => 'day' );
		}
		return $parsedData;
	}

	public function activeByDayCohort( $render = false ){
		$id_chart_cohort = $_GET[ 'id_chart_cohort' ];
		$cohort_type = $_GET[ 'cohort_type' ];

		switch ( $cohort_type ) {
			case 'cohort':
				$allWeeks = $this->allWeeks();

				$query = '';
				$union = '';

				$id_chart_cohort = $_GET[ 'id_chart_cohort' ];
				$cohort_type = $_GET[ 'cohort_type' ];

				$cohort = Crunchbutton_Chart_Cohort::_get( $id_chart_cohort, $cohort_type );

				$cohortQuery = $cohort->toQuery();

				$allDays = $this->allDays();

				for( $i = $this->from_day -1 ; $i < $this->to_day; $i++ ){
					$day = $allDays[ $i ];
					$query .= $union . "SELECT '{$day}' AS Day,
																		 COUNT(*) AS Total
															FROM
																( SELECT u.phone,
																				 o.date,
																				 u.id_user,
																				 c.name
																 FROM `order` o
																 INNER JOIN `user` u ON u.id_user = o.id_user
																 LEFT JOIN restaurant_community rc ON o.id_restaurant = rc.id_restaurant
LEFT JOIN community c ON rc.id_community = c.id_community {$this->queryExcludeCommunties}
																 WHERE o.date <= '{$day}'
																 	 AND o.date >= '{$day}' - INTERVAL {$this->activeUsersInterval} DAY
																	 {$this->queryExcludeUsers}
																	 {$this->queryOnlyCommunties}
																	 {$cohortQuery}
																 GROUP BY u.phone ) ActiveUsers";
						$union = ' UNION ';
				}
				break;

			case 'months':

				$month_cohort = $id_chart_cohort;

				$query = '';
				$union = '';

				$allDays = $this->allDays();

				for( $i = $this->from_day -1 ; $i < $this->to_day; $i++ ){
					$day = $allDays[ $i ];

					$query .= $union . "SELECT '{$day}' AS Day,
																		 COUNT(*) AS Total
															FROM
																( SELECT u.phone,
																				 o.date,
																				 u.id_user,
																				 c.name
																 FROM `order` o
																 INNER JOIN `user` u ON u.id_user = o.id_user
																 LEFT JOIN restaurant_community rc ON o.id_restaurant = rc.id_restaurant
LEFT JOIN community c ON rc.id_community = c.id_community {$this->queryExcludeCommunties}
																 WHERE o.date <= '{$day}'
																 	 AND o.date >= '{$day}' - INTERVAL {$this->activeUsersInterval} DAY
																	 {$this->queryExcludeUsers}
																	 {$this->queryOnlyCommunties}
																	AND o.date >= '{$month_cohort}-01'
																	AND o.phone IN( SELECT o.phone
																	 FROM `order` o
																	 INNER JOIN
																		 (SELECT min(id_order) id_order,
																						 o.phone
																			FROM `order` o
																			GROUP BY o.phone) orders ON o.id_order = orders.id_order
																	 AND DATE_FORMAT(o.date ,'%Y-%m') = '{$month_cohort}' )
																 GROUP BY u.phone ) ActiveUsers";
						$union = ' UNION ';
				}
				break;

			case 'giftcard':

				$giftcard_group = $id_chart_cohort;

				$query = '';
				$union = '';

				$allDays = $this->allDays();

				for( $i = $this->from_day -1 ; $i < $this->to_day; $i++ ){
					$day = $allDays[ $i ];

					$query .= $union . "SELECT '{$day}' AS Day,
																		 COUNT(*) AS Total
															FROM
																( SELECT u.phone,
																				 o.date,
																				 u.id_user,
																				 c.name
																 FROM `order` o
																 INNER JOIN `user` u ON u.id_user = o.id_user
																 LEFT JOIN restaurant_community rc ON o.id_restaurant = rc.id_restaurant
LEFT JOIN community c ON rc.id_community = c.id_community {$this->queryExcludeCommunties}
																 WHERE o.date <= '{$day}'
																 	 AND o.date >= '{$day}' - INTERVAL {$this->activeUsersInterval} DAY
																	 {$this->queryExcludeUsers}
																	 {$this->queryOnlyCommunties}
																	AND o.phone IN ( SELECT DISTINCT( u.phone ) FROM credit c
																	INNER JOIN promo_group_promo pg ON pg.id_promo_group = {$giftcard_group} AND pg.id_promo = c.id_promo
																	INNER JOIN `user` u ON u.id_user = c.id_user )
																 GROUP BY u.phone ) ActiveUsers";
						$union = ' UNION ';
				}
				break;
		}

		$parsedData = $this->parseDataDaysSimple( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit, 'interval' => 'day' );
		}
		return $parsedData;
	}

	public function activeByMonth( $render = false ){

		$query = [];

		$community = ( $_REQUEST[ 'community' ] ? 'AND o.id_community = ' . $_REQUEST[ 'community' ] : '' );

		$allMonths = $this->allMonths();
		for( $i = $this->from_month -1 ; $i < $this->to_month; $i++ ){
			$month = $allMonths[ $i ];
			$query[] = "SELECT '{$month}' AS Month,
																 COUNT(*) AS Total
													FROM
														( SELECT o.phone,
																		 o.date,
																		 o.id_user
														 FROM `order` o
														 WHERE o.date <= LAST_DAY( STR_TO_DATE( '{$month}', '%Y-%m' ) )
															 AND o.date >= '{$month}-01' - INTERVAL {$this->activeUsersInterval} DAY
															 {$this->queryExcludeUsers}
															 {$community}
														 GROUP BY o.phone ) ActiveUsers";
		}

		$parsedData = $this->parseDataMonthSimple( join( ' UNION ', $query ), $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit, 'interval' => 'month' );
		}
		return $parsedData;
	}

	public function activeByMonthCohort( $render = false ){

		$id_chart_cohort = $_GET[ 'id_chart_cohort' ];
		$cohort_type = $_GET[ 'cohort_type' ];

		switch ( $cohort_type ) {
			case 'cohort':
				$cohort = Crunchbutton_Chart_Cohort::_get( $id_chart_cohort, $cohort_type );
				$query = '';
				$union = '';

				$cohortQuery = $cohort->toQuery();

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
																 INNER JOIN `user` u ON u.id_user = o.id_user
																 LEFT JOIN restaurant_community rc ON o.id_restaurant = rc.id_restaurant
LEFT JOIN community c ON rc.id_community = c.id_community {$this->queryExcludeCommunties}
																 WHERE o.date <= LAST_DAY( STR_TO_DATE( '{$month}', '%Y-%m' ) )
																	 AND o.date >= '{$month}-01' - INTERVAL {$this->activeUsersInterval} DAY
																	 {$this->queryExcludeUsers}
																	 {$this->queryOnlyCommunties}
																	 {$cohortQuery}
																 GROUP BY u.phone ) ActiveUsers";

						$union = ' UNION ';
				}
				break;

			case 'months':

				$month_cohort = $id_chart_cohort;

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
																 INNER JOIN `user` u ON u.id_user = o.id_user
																 LEFT JOIN restaurant_community rc ON o.id_restaurant = rc.id_restaurant
LEFT JOIN community c ON rc.id_community = c.id_community {$this->queryExcludeCommunties}
																 WHERE o.date <= LAST_DAY( STR_TO_DATE( '{$month}', '%Y-%m' ) )
																	 AND o.date >= '{$month}-01' - INTERVAL {$this->activeUsersInterval} DAY
																	 {$this->queryExcludeUsers}
																	 {$this->queryOnlyCommunties}
																	AND o.date >= '{$month_cohort}-01'
																	AND o.phone IN( SELECT o.phone
																	 FROM `order` o
																	 INNER JOIN
																		 (SELECT min(id_order) id_order,
																						 o.phone
																			FROM `order` o
																			GROUP BY o.phone) orders ON o.id_order = orders.id_order
																	 AND DATE_FORMAT(o.date ,'%Y-%m') = '{$month_cohort}' )
																 GROUP BY u.phone ) ActiveUsers";
						$union = ' UNION ';
				}
				break;

			case 'giftcard':

				$giftcard_group = $id_chart_cohort;

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
																 INNER JOIN `user` u ON u.id_user = o.id_user
																 LEFT JOIN restaurant_community rc ON o.id_restaurant = rc.id_restaurant
LEFT JOIN community c ON rc.id_community = c.id_community {$this->queryExcludeCommunties}
																 WHERE o.date <= LAST_DAY( STR_TO_DATE( '{$month}', '%Y-%m' ) )
																	 AND o.date >= '{$month}-01' - INTERVAL {$this->activeUsersInterval} DAY
																	 {$this->queryExcludeUsers}
																	 {$this->queryOnlyCommunties}
																	AND o.phone IN ( SELECT DISTINCT( u.phone ) FROM credit c
																	INNER JOIN promo_group_promo pg ON pg.id_promo_group = {$giftcard_group} AND pg.id_promo = c.id_promo
																	INNER JOIN `user` u ON u.id_user = c.id_user )
																 GROUP BY u.phone ) ActiveUsers";
						$union = ' UNION ';
				}
				break;

		}

		$parsedData = $this->parseDataMonthSimple( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit, 'interval' => 'month' );
		}
		return $parsedData;
	}

	public function reclaimedByDay( $render = false ){

		$query = "SELECT day AS Day,
										 COUNT(*) AS Total
							FROM
								(SELECT last.total AS total,
												lastbutone.id_order AS id_order_last_but_one,
												lastbutone.date AS date_last_but_one,
												last.id_order AS id_order_last,
												last.date AS date_last,
												lastbutone.phone AS phone,
												DATE_FORMAT(last.date ,'%Y-%m-%d') AS day,
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
								AND day BETWEEN '{$this->dayFrom}' AND '{$this->dayTo}'
							GROUP BY day";

		$parsedData = $this->parseDataDaysSimple( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit, 'interval' => 'day' );
		}
		return $parsedData;
	}

	public function reclaimedByMonth( $render = false ){

		$query = "SELECT month AS Month,
										 COUNT(*) AS Total
							FROM
								(SELECT last.total AS total,
												lastbutone.id_order AS id_order_last_but_one,
												lastbutone.date AS date_last_but_one,
												last.id_order AS id_order_last,
												last.date AS date_last,
												lastbutone.phone AS phone,
												DATE_FORMAT( last.date ,'%Y-%m') AS month,
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
								AND month BETWEEN '{$this->monthFrom}' AND '{$this->monthTo}'
							GROUP BY month";

		$parsedData = $this->parseDataMonthSimple( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit, 'interval' => 'month' );
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
			return array( 'data' => $parsedData, 'unit' => $this->unit );
		}
		return $parsedData;
	}

	public function activeByWeek( $render = false ){

		$community = ( $_REQUEST[ 'community' ] ? 'AND o.id_community = ' . $_REQUEST[ 'community' ] : '' );

		$allWeeks = $this->allWeeks();

		$query = [];
		for( $i = $this->from -1 ; $i < $this->to; $i++ ){
			$week = $allWeeks[ $i ];

			$query[] = "SELECT '{$week}' AS Week,
																 COUNT(*) AS Total
													FROM
														( SELECT o.phone,
																		 o.date,
																		 o.id_user
														 FROM `order` o
														 WHERE o.date <= STR_TO_DATE('{$week} Saturday', '%X%V %W')
															 AND o.date >= STR_TO_DATE('{$week} Saturday', '%X%V %W') - INTERVAL {$this->activeUsersInterval} DAY
															 {$community}
															 {$this->queryExcludeUsers}
														 GROUP BY o.phone) ActiveUsers";
		}

		$parsedData = $this->parseDataWeeksSimple( join( ' UNION ', $query ), $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit );
		}
		return $parsedData;
	}

	public function activeByWeekCohort( $render = false ){

		$id_chart_cohort = $_GET[ 'id_chart_cohort' ];
		$cohort_type = $_GET[ 'cohort_type' ];

		switch ( $cohort_type ) {
			case 'cohort':
				$allWeeks = $this->allWeeks();

				$query = '';
				$union = '';

				$id_chart_cohort = $_GET[ 'id_chart_cohort' ];
				$cohort_type = $_GET[ 'cohort_type' ];

				$cohort = Crunchbutton_Chart_Cohort::_get( $id_chart_cohort, $cohort_type );

				$cohortQuery = $cohort->toQuery();

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
																 INNER JOIN `user` u ON u.id_user = o.id_user
																 LEFT JOIN restaurant_community rc ON o.id_restaurant = rc.id_restaurant
LEFT JOIN community c ON rc.id_community = c.id_community {$this->queryExcludeCommunties}
																 WHERE o.date <= STR_TO_DATE('{$week} Saturday', '%X%V %W')
																	 AND o.date >= STR_TO_DATE('{$week} Saturday', '%X%V %W') - INTERVAL {$this->activeUsersInterval} DAY
																	 {$this->queryExcludeUsers}
																	 {$cohortQuery}
																 GROUP BY u.phone) ActiveUsers";
						$union = ' UNION ';
				}
				break;

			case 'months':

				$month_cohort = $id_chart_cohort;

				$query = '';
				$union = '';

				$allWeeks = $this->allWeeks();

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
																 INNER JOIN `user` u ON u.id_user = o.id_user
																 LEFT JOIN restaurant_community rc ON o.id_restaurant = rc.id_restaurant
LEFT JOIN community c ON rc.id_community = c.id_community {$this->queryExcludeCommunties}
																 WHERE o.date <= STR_TO_DATE('{$week} Saturday', '%X%V %W')
																	 AND o.date >= STR_TO_DATE('{$week} Saturday', '%X%V %W') - INTERVAL {$this->activeUsersInterval} DAY
																	 {$this->queryExcludeUsers}
																	AND o.date >= '{$month_cohort}-01'
																	AND o.phone IN( SELECT o.phone
																	 FROM `order` o
																	 INNER JOIN
																		 (SELECT min(id_order) id_order,
																						 o.phone
																			FROM `order` o
																			GROUP BY o.phone) orders ON o.id_order = orders.id_order
																	 AND DATE_FORMAT(o.date ,'%Y-%m') = '{$month_cohort}' )
																 GROUP BY u.phone) ActiveUsers";
						$union = ' UNION ';
				}
				break;

			case 'giftcard':

				$giftcard_group = $id_chart_cohort;

				$query = '';
				$union = '';

				$allWeeks = $this->allWeeks();

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
																 INNER JOIN `user` u ON u.id_user = o.id_user
																 LEFT JOIN restaurant_community rc ON o.id_restaurant = rc.id_restaurant
LEFT JOIN community c ON rc.id_community = c.id_community {$this->queryExcludeCommunties}
																 WHERE o.date <= STR_TO_DATE('{$week} Saturday', '%X%V %W')
																	 AND o.date >= STR_TO_DATE('{$week} Saturday', '%X%V %W') - INTERVAL {$this->activeUsersInterval} DAY
																	 {$this->queryExcludeUsers}
																	AND o.phone IN ( SELECT DISTINCT( u.phone ) FROM credit c
																	INNER JOIN promo_group_promo pg ON pg.id_promo_group = {$giftcard_group} AND pg.id_promo = c.id_promo
																	INNER JOIN `user` u ON u.id_user = c.id_user )
																 GROUP BY u.phone) ActiveUsers";
						$union = ' UNION ';
				}

					break;
		}

		$parsedData = $this->parseDataWeeksSimple( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit );
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
																			 c.name AS community
																FROM `order` o
																INNER JOIN `user` u ON u.id_user = o.id_user
																LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant
								 								INNER JOIN restaurant_community rc ON r.id_restaurant = rc.id_restaurant
								 								INNER JOIN community c ON c.id_community = rc.id_community AND c.name NOT LIKE 'test%'
																WHERE o.date <= STR_TO_DATE('{$week} Saturday', '%X%V %W')
																	AND o.date >= STR_TO_DATE('{$week} Sunday', '%X%V %W') - INTERVAL {$this->activeUsersInterval} DAY
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
																			 c.name AS community
																FROM `order` o
																INNER JOIN `user` u ON u.id_user = o.id_user
																LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant
								 								INNER JOIN restaurant_community rc ON r.id_restaurant = rc.id_restaurant
								 								INNER JOIN community c ON c.id_community = rc.id_community AND c.name NOT LIKE 'test%'
																WHERE o.date <= STR_TO_DATE('{$week} Saturday', '%X%V %W')
																	{$this->queryExcludeUsers}
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
			return array( 'data' => $parsedData, 'unit' => $this->unit );
		}
		return $parsedData;

	}

	public function newPerActiveByMonthByCommunity( $render = false ){

		$query = '';
		$union = '';

		$allMonths = $this->allMonths();

		for( $i = $this->from_month -1 ; $i < $this->to_month; $i++ ){
			$month = $allMonths[ $i ];
			$query .= $union . "SELECT ActiveUsers.Label AS Month,
																 CAST( NewUsers.NewUsers / ActiveUsers.ActiveUsers AS DECIMAL(14, 2) ) AS 'Total',
																 ActiveUsers.Community AS 'Group',
																 'Users',
																 ActiveUsers.ActiveUsers,
																 NewUsers.NewUsers
													FROM
														(SELECT '{$month}' AS Label,
																		COUNT(*) AS ActiveUsers,
																		community AS Community
														 FROM
															 (SELECT u.phone,
																			 o.date,
																			 u.id_user,
																			 c.name AS community
																FROM `order` o
																INNER JOIN `user` u ON u.id_user = o.id_user
																LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant
								 								INNER JOIN restaurant_community rc ON r.id_restaurant = rc.id_restaurant
								 								INNER JOIN community c ON c.id_community = rc.id_community AND c.name NOT LIKE 'test%'
																WHERE o.date <= LAST_DAY( STR_TO_DATE( '{$month}', '%Y-%m' ) )
																	AND o.date >= '{$month}-01' - INTERVAL {$this->activeUsersInterval} DAY
																	{$this->queryExcludeUsers}
																GROUP BY u.phone) ActiveUsers
														 GROUP BY Community) ActiveUsers
													LEFT JOIN
														( SELECT '{$month}' AS Label,
																		 COUNT(*) AS NewUsers,
																		 community AS Community
														 FROM
															 (SELECT COUNT(*) orders,
																			 u.phone,
																			 o.date,
																			 u.id_user,
																			 c.name AS community
																FROM `order` o
																INNER JOIN `user` u ON u.id_user = o.id_user
																LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant
								 								INNER JOIN restaurant_community rc ON r.id_restaurant = rc.id_restaurant
								 								INNER JOIN community c ON c.id_community = rc.id_community AND c.name NOT LIKE 'test%'
																WHERE o.date <= LAST_DAY( STR_TO_DATE( '{$month}', '%Y-%m' ) )
																	{$this->queryExcludeUsers}
																GROUP BY u.phone HAVING orders = 1) Orders
														 WHERE Orders.date BETWEEN '{$month}-01' AND LAST_DAY( STR_TO_DATE( '{$month}', '%Y-%m' ) )
														 GROUP BY Orders.community ) NewUsers ON NewUsers.Label = ActiveUsers.Label
													AND NewUsers.Community = ActiveUsers.Community
													GROUP BY ActiveUsers.Community";
			$union = ' UNION ';
			$count++;
		}

		$parsedData = $this->parseDataMonthGroup( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit, 'interval' => 'month' );
		}
		return $parsedData;

	}

	public function newPerActiveByDayByCommunity( $render = false ){

		$query = '';
		$union = '';

		$allDays = $this->allDays();

		for( $i = $this->from_day -1 ; $i < $this->to_day; $i++ ){
			$day = $allDays[ $i ];
			$query .= $union . "SELECT ActiveUsers.Label AS Day,
																 CAST( NewUsers.NewUsers / ActiveUsers.ActiveUsers AS DECIMAL(14, 2) ) AS 'Total',
																 ActiveUsers.Community AS 'Group',
																 'Users',
																 ActiveUsers.ActiveUsers,
																 NewUsers.NewUsers
													FROM
														(SELECT '{$day}' AS Label,
																		COUNT(*) AS ActiveUsers,
																		community AS Community
														 FROM
															 (SELECT u.phone,
																			 o.date,
																			 u.id_user,
																			 c.name AS community
																FROM `order` o
																INNER JOIN `user` u ON u.id_user = o.id_user
																LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant
																INNER JOIN restaurant_community rc ON r.id_restaurant = rc.id_restaurant
																INNER JOIN community c ON c.id_community = rc.id_community AND c.name NOT LIKE 'test%'
																WHERE o.date <= '{$day}'
																	AND o.date >= '{$day}' - INTERVAL {$this->activeUsersInterval} DAY
																	{$this->queryExcludeUsers}
																GROUP BY u.phone) ActiveUsers
														 GROUP BY Community) ActiveUsers
													LEFT JOIN
														( SELECT '{$day}' AS Label,
																		 COUNT(*) AS NewUsers,
																		 community AS Community
														 FROM
															 (SELECT COUNT(*) orders,
																			 u.phone,
																			 o.date,
																			 u.id_user,
																			 c.name AS community
																FROM `order` o
																INNER JOIN `user` u ON u.id_user = o.id_user
																LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant
																INNER JOIN restaurant_community rc ON r.id_restaurant = rc.id_restaurant
								 								INNER JOIN community c ON c.id_community = rc.id_community AND c.name NOT LIKE 'test%'
																WHERE o.date <= '{$day}'
																	{$this->queryExcludeUsers}
																GROUP BY u.phone HAVING orders = 1) Orders
														 WHERE Orders.date = '{$day}'
														 GROUP BY Orders.community ) NewUsers ON NewUsers.Label = ActiveUsers.Label
													AND NewUsers.Community = ActiveUsers.Community
													GROUP BY ActiveUsers.Community";
			$union = ' UNION ';
			$count++;
		}

		$parsedData = $this->parseDataDaysGroup( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit, 'interval' => 'day' );
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
																		 c.name AS community
														 FROM `order` o
														 INNER JOIN `user` u ON u.id_user = o.id_user
														 LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant
								 						 INNER JOIN restaurant_community rc ON r.id_restaurant = rc.id_restaurant
								 						 INNER JOIN community c ON c.id_community = rc.id_community AND c.name NOT LIKE 'test%'
														 WHERE o.date <= STR_TO_DATE('{$week} Saturday', '%X%V %W')
															 AND o.date >= STR_TO_DATE('{$week} Sunday', '%X%V %W') - INTERVAL {$this->activeUsersInterval} DAY
															 {$this->queryExcludeUsers}
														 GROUP BY u.phone) ActiveUsers
													GROUP BY ActiveUsers.community";
			$union = ' UNION ';
		}

		$parsedData = $this->parseDataWeeksGroup( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit );
		}
		return $parsedData;
	}

	public function activeByMonthByCommunity( $render = false ){

		$query = '';
		$union = '';

		$allMonths = $this->allMonths();

		for( $i = $this->from_month -1 ; $i < $this->to_month; $i++ ){
			$month = $allMonths[ $i ];
			$query .= $union . "SELECT DATE_FORMAT( date ,'%Y-%m') AS Month,
																 COUNT(*) AS Total,
																 community AS 'Group'
													FROM
														( SELECT u.phone,
																		 o.date,
																		 u.id_user,
																		 c.name AS community
														 FROM `order` o
														 INNER JOIN `user` u ON u.id_user = o.id_user
														 LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant
														 INNER JOIN restaurant_community rc ON r.id_restaurant = rc.id_restaurant
														 INNER JOIN community c ON c.id_community = rc.id_community AND c.name NOT LIKE 'test%'
														 WHERE o.date <= LAST_DAY( STR_TO_DATE( '{$month}', '%Y-%m' ) )
															 AND o.date >= '{$month}-01' - INTERVAL {$this->activeUsersInterval} DAY
															 {$this->queryExcludeUsers}
														 GROUP BY u.phone ) ActiveUsers
													GROUP BY ActiveUsers.community";
			$union = ' UNION ';
		}

		$parsedData = $this->parseDataMonthGroup( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit, 'interval' => 'month' );
		}
		return $parsedData;
	}

	public function uniqueByWeekByCommunity( $render = false ){

		$query = "SELECT YEARWEEK(date) AS `Week`,
									 COUNT(DISTINCT((u.phone))) AS Total,
									 c.name AS `Group`
						FROM `order` o
						INNER JOIN `user` u ON u.id_user = o.id_user
						LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant
						INNER JOIN restaurant_community rc ON r.id_restaurant = rc.id_restaurant
						INNER JOIN community c ON c.id_community = rc.id_community AND c.name NOT LIKE 'test%'
						WHERE YEARWEEK(o.date) >= {$this->weekFrom} AND YEARWEEK(o.date) <= {$this->weekTo}
							{$this->queryExcludeUsers}
						GROUP BY YEARWEEK(o.date),
										c.name
						ORDER BY YEARWEEK(o.date) DESC";

		$parsedData = $this->parseDataWeeksGroup( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit );
		}
		return $parsedData;
	}

	public function uniqueByDayByCommunity( $render = false ){

		$query = "SELECT DATE_FORMAT( o.date ,'%Y-%m-%d') AS Day,
									 COUNT(DISTINCT((u.phone))) AS Total,
									 c.name AS `Group`
						FROM `order` o
						INNER JOIN `user` u ON u.id_user = o.id_user
						LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant
						INNER JOIN restaurant_community rc ON r.id_restaurant = rc.id_restaurant
						INNER JOIN community c ON c.id_community = rc.id_community AND c.name NOT LIKE 'test%'
						WHERE o.date >= '{$this->dayFrom}' AND o.date <= '{$this->dayTo}'
							{$this->queryExcludeUsers}
						GROUP BY DATE_FORMAT( o.date ,'%Y-%m-%d'),
										 c.name
						ORDER BY DATE_FORMAT( o.date ,'%Y-%m-%d') DESC";

		$parsedData = $this->parseDataDaysGroup( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit, 'interval' => 'day' );
		}
		return $parsedData;
	}

	public function uniqueByMonthByCommunity( $render = false ){

		$query = "SELECT DATE_FORMAT( o.date ,'%Y-%m') AS Month,
									 COUNT(DISTINCT((u.phone))) AS Total,
									 c.name AS `Group`
						FROM `order` o
						INNER JOIN `user` u ON u.id_user = o.id_user
						LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant
						INNER JOIN restaurant_community rc ON r.id_restaurant = rc.id_restaurant
						INNER JOIN community c ON c.id_community = rc.id_community AND c.name NOT LIKE 'test%'
						WHERE o.date >= '{$this->monthFrom}-01' AND o.date <= LAST_DAY( STR_TO_DATE( '{$this->monthTo}', '%Y-%m' ) )
							{$this->queryExcludeUsers}
						GROUP BY DATE_FORMAT( o.date ,'%Y-%m'),
										c.name
						ORDER BY DATE_FORMAT( o.date ,'%Y-%m') DESC";

		$parsedData = $this->parseDataMonthGroup( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit, 'interval' => 'month' );
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
			return array( 'data' => $data, 'unit' => $this->unit );
		}
		return $data;
	}

		public function newPerActiveByDay( $render = false ){

		$activeUsers = $this->activeByDay();
		$newUsers = $this->newByDay();

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
			return array( 'data' => $data, 'unit' => $this->unit, 'interval' => 'day' );
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
			return array( 'data' => $data, 'unit' => $this->unit, 'interval' => 'month' );
		}
		return $data;
	}

	public function uniqueByDay( $render = false ){

		$query = "SELECT DATE_FORMAT( o.date ,'%Y-%m-%d') AS Day,
										 COUNT( DISTINCT( ( u.phone ) ) ) AS Total
							FROM `order` o
							INNER JOIN `user` u ON u.id_user = o.id_user
							LEFT JOIN restaurant_community rc ON o.id_restaurant = rc.id_restaurant
LEFT JOIN community c ON rc.id_community = c.id_community {$this->queryExcludeCommunties}
							WHERE
								o.date >= '{$this->dayFrom}' AND o.date <= '{$this->dayTo}'
								{$this->queryExcludeUsers}
							GROUP BY Day
							ORDER BY Day ASC";

		$parsedData = $this->parseDataDaysSimple( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit, 'interval' => 'day' );
		}
		return $parsedData;
	}

	public function uniqueByWeek( $render = false ){

		$query = "SELECT YEARWEEK(date) AS `Week`,
										 COUNT( DISTINCT( ( u.phone ) ) ) AS Total
							FROM `order` o
							INNER JOIN `user` u ON u.id_user = o.id_user
							LEFT JOIN restaurant_community rc ON o.id_restaurant = rc.id_restaurant
LEFT JOIN community c ON rc.id_community = c.id_community {$this->queryExcludeCommunties}
							WHERE YEARWEEK(o.date) >= {$this->weekFrom} AND YEARWEEK(o.date) <= {$this->weekTo}
								{$this->queryExcludeUsers}
							GROUP BY YEARWEEK(o.date)
							ORDER BY YEARWEEK(o.date) DESC";

		$parsedData = $this->parseDataWeeksSimple( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit );
		}
		return $parsedData;
	}

	public function uniqueByMonth( $render = false ){

		$query = "SELECT DATE_FORMAT( o.date ,'%Y-%m') AS Month,
										 COUNT( DISTINCT( ( u.phone ) ) ) AS Total
							FROM `order` o
							INNER JOIN `user` u ON u.id_user = o.id_user
							LEFT JOIN restaurant_community rc ON o.id_restaurant = rc.id_restaurant
LEFT JOIN community c ON rc.id_community = c.id_community {$this->queryExcludeCommunties}
							WHERE
								o.date >= '{$this->monthFrom}-01' AND o.date <= LAST_DAY( STR_TO_DATE( '{$this->monthTo}', '%Y-%m' ) )
								{$this->queryExcludeUsers}
							GROUP BY Month
							ORDER BY Month ASC";

		$parsedData = $this->parseDataMonthSimple( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit, 'interval' => 'month' );
		}
		return $parsedData;
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
								INNER JOIN `user` u ON u.id_user = o.id_user
								LEFT JOIN restaurant_community rc ON o.id_restaurant = rc.id_restaurant
LEFT JOIN community c ON rc.id_community = c.id_community {$this->queryExcludeCommunties}
								WHERE YEARWEEK(o.date) = {$week}
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

			if( $days ){

				$interval = date_diff( date_create( $days->day1 ), date_create( $days->day2 ) );
				$days = intval( $interval->format('%d') );

				if( $days <= 4 ){ $_data[ $week ][ 'Power' ]++; }
				if( $days > 4 && $days <= 11 ){ $_data[ $week ][ 'Weekly' ]++; }
				if( $days > 11 && $days <= 18 ){ $_data[ $week ][ 'Bi-Weekly' ]++; }
				if( $days > 18 && $days <= 25 ){ $_data[ $week ][ 'Tri-Weekly' ]++; }
				if( $days > 25 ){ $_data[ $week ][ 'Monthly' ]++; }
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

	public function newByMonth( $render = false ){

		$community = ( $_REQUEST[ 'community' ] ? 'AND o.id_community = ' . $_REQUEST[ 'community' ] : '' );

		$query = "SELECT SUM(1) AS Total,
										 DATE_FORMAT(o.date ,'%Y-%m') AS Month
							FROM `order` o
							INNER JOIN
								(SELECT min(id_order) id_order,
												u.phone
								 FROM `order` o
								 INNER JOIN `user` u ON u.id_user = o.id_user
								 LEFT JOIN restaurant_community rc ON o.id_restaurant = rc.id_restaurant
LEFT JOIN community c ON rc.id_community = c.id_community {$this->queryExcludeCommunties}
								 WHERE 1 = 1
								 {$community}
								 {$this->queryExcludeUsers}
								 GROUP BY u.phone) orders ON o.id_order = orders.id_order
							GROUP BY DATE_FORMAT(o.date ,'%Y-%m') HAVING Month BETWEEN '{$this->monthFrom}' AND '{$this->monthTo}'";

		$parsedData = $this->parseDataMonthSimple( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit, 'interval' => 'month' );
		}
		return $parsedData;
	}

	public function newByMonthByCommunityGrouped( $render = false ){

		$query = "SELECT SUM(1) AS Total,
										 DATE_FORMAT(o.date ,'%Y-%m') AS Month,
										 community AS `Group`
							FROM `order` o
							INNER JOIN
								(SELECT min(id_order) id_order,
												u.phone,
												c.name AS community
								 FROM `order` o
								 INNER JOIN `user` u ON u.id_user = o.id_user
								 LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant
								 INNER JOIN restaurant_community rc ON r.id_restaurant = rc.id_restaurant
								 INNER JOIN community c ON c.id_community = rc.id_community AND c.name NOT LIKE 'test%'
								 WHERE 1 = 1
										{$this->queryExcludeUsers}
								 GROUP BY u.phone) orders ON o.id_order = orders.id_order
							GROUP BY DATE_FORMAT(o.date ,'%Y-%m') HAVING Month BETWEEN '{$this->monthFrom}' AND '{$this->monthTo}'";

		$parsedData = $this->parseDataMonthGroup( $query, $this->description );

		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit, 'interval' => 'month' );
		}
		return $parsedData;
	}

	public function newByDayByCommunityGrouped( $render = false ){
		$query = "SELECT SUM(1) AS Total,
										 DATE_FORMAT(o.date ,'%Y-%m-%d') AS Day,
										 community AS `Group`
							FROM `order` o
							INNER JOIN
								(SELECT min(id_order) id_order,
												u.phone,
												c.name AS community
								 FROM `order` o
								 INNER JOIN `user` u ON u.id_user = o.id_user
								 LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant
								 INNER JOIN restaurant_community rc ON r.id_restaurant = rc.id_restaurant
								 INNER JOIN community c ON c.id_community = rc.id_community AND c.name NOT LIKE 'test%'
								 WHERE 1 = 1
										{$this->queryExcludeUsers}
								 GROUP BY u.phone) orders ON o.id_order = orders.id_order
							GROUP BY DATE_FORMAT(o.date ,'%Y-%m-%d') HAVING Day BETWEEN '{$this->dayFrom}' AND '{$this->dayTo}'";
		$parsedData = $this->parseDataDaysGroup( $query, $this->description );

		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit, 'interval' => 'day' );
		}
		return $parsedData;
	}

	public function newByDayHistorical( $render = false ){
		$newUsers = $this->newByDay();
		$usersReclaimed = $this->reclaimedByDay();
		$activeUsers = $this->activeByDay();
		$data = [];
		for( $i = 0; $i < count( $newUsers ); $i++ ){
			$data[] = ( object ) array(
											'Label' => $newUsers[ $i ]->Label,
											'Total' => ( ( $newUsers[ $i ]->Total + $usersReclaimed[ $i ]->Total ) / $activeUsers[ $i ]->Total ),
											'Type' => $newUsers[ $i ]->Type,
											);
		}
		if( $render ){
			return array( 'data' => $data, 'unit' => 'Users', 'interval' => 'day' );
		}
		return $data;
	}

	public function newByWeekHistorical( $render = false ){
		$newUsers = $this->newByWeek();
		$usersReclaimed = $this->reclaimedByWeek();
		$activeUsers = $this->activeByWeek();
		$data = [];
		for( $i = 0; $i < count( $newUsers ); $i++ ){
			$data[] = ( object ) array(
											'Label' => $newUsers[ $i ]->Label,
											'Total' => ( ( $newUsers[ $i ]->Total + $usersReclaimed[ $i ]->Total ) / $activeUsers[ $i ]->Total ),
											'Type' => $newUsers[ $i ]->Type,
											);
		}
		if( $render ){
			return array( 'data' => $data, 'unit' => 'Users', 'interval' => 'week' );
		}
		return $data;
	}

	public function newByMonthHistorical( $render = false ){
		$newUsers = $this->newByMonth();
		$usersReclaimed = $this->reclaimedByMonth();
		$activeUsers = $this->activeByMonth();
		$data = [];
		for( $i = 0; $i < count( $newUsers ); $i++ ){
			$data[] = ( object ) array(
											'Label' => $newUsers[ $i ]->Label,
											'Total' => ( ( $newUsers[ $i ]->Total + $usersReclaimed[ $i ]->Total ) / $activeUsers[ $i ]->Total ),
											'Type' => $newUsers[ $i ]->Type,
											);
		}
		if( $render ){
			return array( 'data' => $data, 'unit' => 'Users', 'interval' => 'month' );
		}
		return $data;
	}
	public function newByDay( $render = false ){

		$community = ( $_REQUEST[ 'community' ] ? 'AND o.id_community = ' . $_REQUEST[ 'community' ] : '' );

		$query = "SELECT SUM(1) AS Total,
										 DATE_FORMAT(o.date ,'%Y-%m-%d') AS Day
							FROM `order` o
							INNER JOIN
								(SELECT min(id_order) id_order,
												u.phone
								 FROM `order` o
								 INNER JOIN `user` u ON u.id_user = o.id_user
								 LEFT JOIN restaurant_community rc ON o.id_restaurant = rc.id_restaurant
LEFT JOIN community c ON rc.id_community = c.id_community {$this->queryExcludeCommunties}
								 WHERE 1=1
								 		{$community}
										{$this->queryExcludeUsers}
								 GROUP BY u.phone) orders ON o.id_order = orders.id_order
							GROUP BY DATE_FORMAT(o.date ,'%Y-%m-%d') HAVING Day BETWEEN '{$this->dayFrom}' AND '{$this->dayTo}'";

		$parsedData = $this->parseDataDaysSimple( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit, 'interval' => 'day' );
		}
		return $parsedData;
	}

	public function newByMonthCohort( $render = false ){

		$id_chart_cohort = $_GET[ 'id_chart_cohort' ];
		$cohort_type = $_GET[ 'cohort_type' ];

		switch ( $cohort_type ) {
			case 'cohort':

				$cohort = Crunchbutton_Chart_Cohort::_get( $id_chart_cohort, $cohort_type )	;

				$query = "SELECT SUM(1) AS Total,
												 DATE_FORMAT(o.date ,'%Y-%m') AS Month
									FROM `order` o
									INNER JOIN
										(SELECT min(id_order) id_order,
														u.phone
										 FROM `order` o
										 INNER JOIN `user` u ON u.id_user = o.id_user
										 LEFT JOIN restaurant_community rc ON o.id_restaurant = rc.id_restaurant
LEFT JOIN community c ON rc.id_community = c.id_community {$this->queryExcludeCommunties}
										 WHERE 1 = 1
										 {$this->queryExcludeUsers}
										 {$cohort->toQuery()}
										 GROUP BY u.phone) orders ON o.id_order = orders.id_order
									GROUP BY DATE_FORMAT(o.date ,'%Y-%m') HAVING Month BETWEEN '{$this->monthFrom}' AND '{$this->monthTo}'";
				break;

			case 'months':
				$month = $id_chart_cohort;
				$query = "SELECT SUM(1) AS Total,
												 DATE_FORMAT(o.date ,'%Y-%m') AS Month
									FROM `order` o
									INNER JOIN
										(SELECT min(id_order) id_order,
														u.phone
										 FROM `order` o
										 INNER JOIN `user` u ON u.id_user = o.id_user
										 LEFT JOIN restaurant_community rc ON o.id_restaurant = rc.id_restaurant
LEFT JOIN community c ON rc.id_community = c.id_community {$this->queryExcludeCommunties}
										 WHERE 1 = 1
										 AND DATE_FORMAT(o.date ,'%Y-%m') = '{$month}'
										 {$this->queryExcludeUsers}
										 GROUP BY u.phone) orders ON o.id_order = orders.id_order
									GROUP BY DATE_FORMAT(o.date ,'%Y-%m') HAVING Month BETWEEN '{$this->monthFrom}' AND '{$this->monthTo}'";
				break;

			case 'giftcard':
				$giftcard_group = $id_chart_cohort;
				$query = "SELECT SUM(1) AS Total,
												 DATE_FORMAT(o.date ,'%Y-%m') AS Month
									FROM `order` o
									INNER JOIN
										(SELECT min(id_order) id_order,
														u.phone
										 FROM `order` o
										 INNER JOIN `user` u ON u.id_user = o.id_user
										 LEFT JOIN restaurant_community rc ON o.id_restaurant = rc.id_restaurant
LEFT JOIN community c ON rc.id_community = c.id_community {$this->queryExcludeCommunties}
										 WHERE 1 = 1
										 AND o.phone IN ( SELECT DISTINCT( u.phone ) FROM credit c
																			INNER JOIN promo_group_promo pg ON pg.id_promo_group = {$giftcard_group} AND pg.id_promo = c.id_promo
																			INNER JOIN `user` u ON u.id_user = c.id_user )
										 {$this->queryExcludeUsers}
										 GROUP BY u.phone) orders ON o.id_order = orders.id_order
									GROUP BY DATE_FORMAT(o.date ,'%Y-%m') HAVING Month BETWEEN '{$this->monthFrom}' AND '{$this->monthTo}'";
				break;
		}



		$parsedData = $this->parseDataMonthSimple( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit, 'interval' => 'month' );
		}
		return $parsedData;
	}

	public function newByDayCohort( $render = false ){

		$id_chart_cohort = $_GET[ 'id_chart_cohort' ];
		$cohort_type = $_GET[ 'cohort_type' ];

		switch ( $cohort_type ) {
			case 'cohort':
				$cohort = Crunchbutton_Chart_Cohort::_get( $id_chart_cohort, $cohort_type );
				$cohortQuery = $cohort->toQuery();
				$query = "SELECT SUM(1) AS Total,
												 DATE_FORMAT(o.date ,'%Y-%m-%d') AS Day
									FROM `order` o
									INNER JOIN
										(SELECT min(id_order) id_order,
														u.phone
										 FROM `order` o
										 INNER JOIN `user` u ON u.id_user = o.id_user
										 LEFT JOIN restaurant_community rc ON o.id_restaurant = rc.id_restaurant
LEFT JOIN community c ON rc.id_community = c.id_community {$this->queryExcludeCommunties}
										 WHERE 1=1
												{$this->queryExcludeUsers}
												{$cohort->toQuery()}
										 GROUP BY u.phone) orders ON o.id_order = orders.id_order
									GROUP BY DATE_FORMAT(o.date ,'%Y-%m-%d') HAVING Day BETWEEN '{$this->dayFrom}' AND '{$this->dayTo}'";
				break;

			case 'months':
				$month = $id_chart_cohort;
				$query = "SELECT SUM(1) AS Total,
												 DATE_FORMAT(o.date ,'%Y-%m-%d') AS Day
									FROM `order` o
									INNER JOIN
										(SELECT min(id_order) id_order,
														u.phone
										 FROM `order` o
										 INNER JOIN `user` u ON u.id_user = o.id_user
										 LEFT JOIN restaurant_community rc ON o.id_restaurant = rc.id_restaurant
LEFT JOIN community c ON rc.id_community = c.id_community {$this->queryExcludeCommunties}
										 WHERE 1=1
										 		AND DATE_FORMAT(o.date ,'%Y-%m') = '{$month}'
												{$this->queryExcludeUsers}
										 GROUP BY u.phone) orders ON o.id_order = orders.id_order
									GROUP BY DATE_FORMAT(o.date ,'%Y-%m-%d') HAVING Day BETWEEN '{$this->dayFrom}' AND '{$this->dayTo}'";
				break;
			case 'giftcard':
				$giftcard_group = $id_chart_cohort;
				$query = "SELECT SUM(1) AS Total,
												 DATE_FORMAT(o.date ,'%Y-%m-%d') AS Day
									FROM `order` o
									INNER JOIN
										(SELECT min(id_order) id_order,
														u.phone
										 FROM `order` o
										 INNER JOIN `user` u ON u.id_user = o.id_user
										 LEFT JOIN restaurant_community rc ON o.id_restaurant = rc.id_restaurant
LEFT JOIN community c ON rc.id_community = c.id_community {$this->queryExcludeCommunties}
										 WHERE 1=1
												 AND o.phone IN ( SELECT DISTINCT( u.phone ) FROM credit c
																		INNER JOIN promo_group_promo pg ON pg.id_promo_group = {$giftcard_group} AND pg.id_promo = c.id_promo
																		INNER JOIN `user` u ON u.id_user = c.id_user )
												{$this->queryExcludeUsers}
										 GROUP BY u.phone) orders ON o.id_order = orders.id_order
									GROUP BY DATE_FORMAT(o.date ,'%Y-%m-%d') HAVING Day BETWEEN '{$this->dayFrom}' AND '{$this->dayTo}'";

				break;
		}



		$parsedData = $this->parseDataDaysSimple( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit, 'interval' => 'day' );
		}
		return $parsedData;
	}

	public function newByWeekCohort( $render = false ){

		$id_chart_cohort = $_GET[ 'id_chart_cohort' ];
		$cohort_type = $_GET[ 'cohort_type' ];

		switch ( $cohort_type ) {
			case 'cohort':
				$cohort = Crunchbutton_Chart_Cohort::_get( $id_chart_cohort, $cohort_type );
				$query = "SELECT SUM(1) AS Total,
												 YEARWEEK(o.date) AS Week
									FROM `order` o
									INNER JOIN
										(SELECT min(id_order) id_order,
														u.phone
										 FROM `order` o
										 INNER JOIN `user` u ON u.id_user = o.id_user
										 LEFT JOIN restaurant_community rc ON o.id_restaurant = rc.id_restaurant
LEFT JOIN community c ON rc.id_community = c.id_community {$this->queryExcludeCommunties}
										 WHERE 1=1
												{$this->queryExcludeUsers}
												{$cohort->toQuery()}
										 GROUP BY u.phone) orders ON o.id_order = orders.id_order
									GROUP BY YEARWEEK(o.date) HAVING Week BETWEEN '{$this->weekFrom}' AND '{$this->weekTo}'";
				break;

			case 'months':
				$month = $id_chart_cohort;
				$query = "SELECT SUM(1) AS Total,
												 YEARWEEK(o.date) AS Week
									FROM `order` o
									INNER JOIN
										(SELECT min(id_order) id_order,
														u.phone
										 FROM `order` o
										 INNER JOIN `user` u ON u.id_user = o.id_user
										 LEFT JOIN restaurant_community rc ON o.id_restaurant = rc.id_restaurant
LEFT JOIN community c ON rc.id_community = c.id_community {$this->queryExcludeCommunties}
										 WHERE 1=1
										 		AND DATE_FORMAT(o.date ,'%Y-%m') = '{$month}'
												{$this->queryExcludeUsers}
										 GROUP BY u.phone) orders ON o.id_order = orders.id_order
									GROUP BY YEARWEEK(o.date) HAVING Week BETWEEN '{$this->weekFrom}' AND '{$this->weekTo}'";
				break;
			case 'giftcard':
				$giftcard_group = $id_chart_cohort;
				$query = "SELECT SUM(1) AS Total,
												 YEARWEEK(o.date) AS Week
									FROM `order` o
									INNER JOIN
										(SELECT min(id_order) id_order,
														u.phone
										 FROM `order` o
										 INNER JOIN `user` u ON u.id_user = o.id_user
										 LEFT JOIN restaurant_community rc ON o.id_restaurant = rc.id_restaurant
LEFT JOIN community c ON rc.id_community = c.id_community {$this->queryExcludeCommunties}
										 WHERE 1=1
										 AND o.phone IN ( SELECT DISTINCT( u.phone ) FROM credit c
																			INNER JOIN promo_group_promo pg ON pg.id_promo_group = {$giftcard_group} AND pg.id_promo = c.id_promo
																			INNER JOIN `user` u ON u.id_user = c.id_user )
												{$this->queryExcludeUsers}
										 GROUP BY u.phone) orders ON o.id_order = orders.id_order
									GROUP BY YEARWEEK(o.date) HAVING Week BETWEEN '{$this->weekFrom}' AND '{$this->weekTo}'";
				break;

		}



		$parsedData = $this->parseDataWeeksSimple( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit );
		}
		return $parsedData;
	}

	public function newByWeek( $render = false ){

		$community = ( $_REQUEST[ 'community' ] ? 'AND o.id_community = ' . $_REQUEST[ 'community' ] : '' );

		$query = "SELECT SUM(1) AS Total,
										 YEARWEEK(o.date) AS Week
							FROM `order` o
							INNER JOIN
								(SELECT min(id_order) id_order,
												u.phone
								 FROM `order` o
								 INNER JOIN `user` u ON u.id_user = o.id_user
								 LEFT JOIN restaurant_community rc ON o.id_restaurant = rc.id_restaurant
									LEFT JOIN community c ON rc.id_community = c.id_community {$this->queryExcludeCommunties}
								 WHERE 1=1
								 		{$community}
										{$this->queryExcludeUsers}
								 GROUP BY u.phone) orders ON o.id_order = orders.id_order
							GROUP BY YEARWEEK(o.date) HAVING Week BETWEEN '{$this->weekFrom}' AND '{$this->weekTo}'";
		$parsedData = $this->parseDataWeeksSimple( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit );
		}
		return $parsedData;
	}

	public function newByMonthByRestaurant( $render = false ){

		$restaurant = $_REQUEST[ 'restaurant' ];

		$query = "SELECT SUM(1) AS Total,
										 DATE_FORMAT(o.date ,'%Y-%m') AS Month,
										 restaurant AS `Group`
							FROM `order` o
							INNER JOIN
								(SELECT min(id_order) id_order,
												u.phone,
												r.name as restaurant
								 FROM `order` o
								 INNER JOIN `user` u ON u.id_user = o.id_user
								 LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant
								 WHERE r.id_restaurant = '{$restaurant}'
										{$this->queryExcludeUsers}
								 GROUP BY u.phone) orders ON o.id_order = orders.id_order
							GROUP BY DATE_FORMAT(o.date ,'%Y-%m') HAVING Month BETWEEN '{$this->monthFrom}' AND '{$this->monthTo}'";

		$parsedData = $this->parseDataMonthSimple( $query, $this->description );

		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit, 'interval' => 'month' );
		}
		return $parsedData;
	}

	public function newByMonthByCommunity( $render = false ){

		$community = $_REQUEST[ 'community' ];

		$query = "SELECT SUM(1) AS Total,
										 DATE_FORMAT(o.date ,'%Y-%m') AS Month,
										 community AS `Group`
							FROM `order` o
							INNER JOIN
								(SELECT min(id_order) id_order,
												u.phone,
												c.name AS community
								 FROM `order` o
								 INNER JOIN `user` u ON u.id_user = o.id_user
								 LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant
								 INNER JOIN restaurant_community rc ON r.id_restaurant = rc.id_restaurant
								 INNER JOIN community c ON c.id_community = rc.id_community AND c.name NOT LIKE 'test%'
								 WHERE c.id_community = '{$community}'
										{$this->queryExcludeUsers}
								 GROUP BY u.phone) orders ON o.id_order = orders.id_order
							GROUP BY DATE_FORMAT(o.date ,'%Y-%m') HAVING Month BETWEEN '{$this->monthFrom}' AND '{$this->monthTo}'";

		$parsedData = $this->parseDataMonthSimple( $query, $this->description );

		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit, 'interval' => 'month' );
		}
		return $parsedData;
	}

	public function newByDayByRestaurant( $render = false ){

		$restaurant = $_REQUEST[ 'restaurant' ];

		$query = "SELECT SUM(1) AS Total,
										 DATE_FORMAT(o.date ,'%Y-%m-%d') AS Day,
										 restaurant AS `Group`
							FROM `order` o
							INNER JOIN
								(SELECT min(id_order) id_order,
												u.phone,
												r.name as restaurant
								 FROM `order` o
								 INNER JOIN `user` u ON u.id_user = o.id_user
								 LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant
								 WHERE r.id_restaurant = '{$restaurant}'
										{$this->queryExcludeUsers}
								 GROUP BY u.phone) orders ON o.id_order = orders.id_order
							GROUP BY DATE_FORMAT(o.date ,'%Y-%m-%d') HAVING Day BETWEEN '{$this->dayFrom}' AND '{$this->dayTo}'";

		$parsedData = $this->parseDataDaysSimple( $query, $this->description );

		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit, 'interval' => 'day' );
		}
		return $parsedData;
	}

	public function newByDayByCommunity( $render = false, $community = false ){

		$community = ( $community ? $community : $_REQUEST[ 'community' ] );

		$query = "SELECT SUM(1) AS Total,
										 DATE_FORMAT(o.date ,'%Y-%m-%d') AS Day,
										 community AS `Group`
							FROM `order` o
							INNER JOIN
								(SELECT min(id_order) id_order,
												u.phone,
												c.name as community
								 FROM `order` o
								 INNER JOIN `user` u ON u.id_user = o.id_user
								 LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant
								 INNER JOIN restaurant_community rc ON r.id_restaurant = rc.id_restaurant
								 INNER JOIN community c ON c.id_community = rc.id_community AND c.name NOT LIKE 'test%'
								 WHERE c.id_community = '{$community}'
										{$this->queryExcludeUsers}
								 GROUP BY u.phone) orders ON o.id_order = orders.id_order
							GROUP BY DATE_FORMAT(o.date ,'%Y-%m-%d') HAVING Day BETWEEN '{$this->dayFrom}' AND '{$this->dayTo}'";

		$parsedData = $this->parseDataDaysSimple( $query, $this->description );

		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit, 'interval' => 'day' );
		}
		return $parsedData;
	}

	public function newByWeekByRestaurant( $render = false ){

		$restaurant = $_REQUEST[ 'restaurant' ];

		$query = "SELECT SUM(1) AS Total,
										 YEARWEEK(o.date) AS Week,
										 restaurant AS `Group`
							FROM `order` o
							INNER JOIN
								(SELECT min(id_order) id_order,
												u.phone,
												r.name as restaurant
								 FROM `order` o
								 INNER JOIN `user` u ON u.id_user = o.id_user
								 LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant
								 WHERE r.id_restaurant = '{$restaurant}'
										{$this->queryExcludeUsers}
								 GROUP BY u.phone, r.name) orders ON o.id_order = orders.id_order
							GROUP BY YEARWEEK(o.date) HAVING Week BETWEEN '{$this->weekFrom}' AND '{$this->weekTo}'";
		$parsedData = $this->parseDataWeeksSimple( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit );
		}
		return $parsedData;
	}

	public function newByWeekByCommunity( $render = false ){

		$community = $_REQUEST[ 'community' ];

		$query = "SELECT SUM(1) AS Total,
										 YEARWEEK(o.date) AS Week,
										 community AS `Group`
							FROM `order` o
							INNER JOIN
								(SELECT min(id_order) id_order,
												u.phone,
												c.name AS community
								 FROM `order` o
								 INNER JOIN `user` u ON u.id_user = o.id_user
								 LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant
								 INNER JOIN restaurant_community rc ON r.id_restaurant = rc.id_restaurant
								 INNER JOIN community c ON c.id_community = rc.id_community AND c.name NOT LIKE 'test%'
								 WHERE c.id_community = '{$community}'
										{$this->queryExcludeUsers}
								 GROUP BY u.phone, c.name ) orders ON o.id_order = orders.id_order
							GROUP BY YEARWEEK(o.date) HAVING Week BETWEEN '{$this->weekFrom}' AND '{$this->weekTo}'";
		$parsedData = $this->parseDataWeeksSimple( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit );
		}
		return $parsedData;
	}

	public function newByWeekByCommunityGrouped( $render = false ){

		$query = "SELECT SUM(1) AS Total,
										 YEARWEEK(o.date) AS Week,
										 community AS `Group`
							FROM `order` o
							INNER JOIN
								(SELECT min(id_order) id_order,
												u.phone,
												c.name AS community
								 FROM `order` o
								 INNER JOIN `user` u ON u.id_user = o.id_user
								 LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant
								 INNER JOIN restaurant_community rc ON r.id_restaurant = rc.id_restaurant
								 INNER JOIN community c ON c.id_community = rc.id_community AND c.name NOT LIKE 'test%'
								 WHERE 1 = 1
										{$this->queryExcludeUsers}
								 GROUP BY u.phone, c.name) orders ON o.id_order = orders.id_order
							GROUP BY YEARWEEK(o.date) HAVING Week BETWEEN '{$this->weekFrom}' AND '{$this->weekTo}'";

		$parsedData = $this->parseDataWeeksGroup( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit );
		}
		return $parsedData;
	}

	public function repeatPerDay( $render = false ){
		$order = new Crunchbutton_Chart_Order();
		return array( 'data' => $order->repeatPerDay( false ), 'unit' =>$order->unit, 'interval' => 'day' );
	}

	public function repeatPerDayByCommunityGrouped( $render = false ){
		$order = new Crunchbutton_Chart_Order();
		return array( 'data' => $order->repeatPerDayByCommunityGrouped( false ), 'unit' =>$order->unit, 'interval' => 'day' );
	}

	public function repeatPerDayByCommunity( $render = false ){
		$order = new Crunchbutton_Chart_Order();
		return array( 'data' => $order->repeatPerDayByCommunity( false ), 'unit' =>$order->unit, 'interval' => 'day' );
	}

	public function repeatPerWeek( $render = false ){
		$order = new Crunchbutton_Chart_Order();
		return array( 'data' => $order->repeatPerWeek( false ), 'unit' =>$order->unit, 'interval' => 'week' );
	}

	public function repeatPerWeekByCommunityGrouped( $render = false ){
		$order = new Crunchbutton_Chart_Order();
		return array( 'data' => $order->repeatPerWeekByCommunityGrouped( false ), 'unit' =>$order->unit, 'interval' => 'week' );
	}

	public function repeatPerWeekByCommunity( $render = false ){
		$order = new Crunchbutton_Chart_Order();
		return array( 'data' => $order->repeatPerWeekByCommunity( false ), 'unit' =>$order->unit, 'interval' => 'week' );
	}

	public function repeatPerWeekByRestaurant( $render = false ){
		$order = new Crunchbutton_Chart_Order();
		return array( 'data' => $order->repeatPerWeekByRestaurant( false ), 'unit' =>$order->unit, 'interval' => 'week' );
	}

	public function repeatPerDayByRestaurant( $render = false ){
		$order = new Crunchbutton_Chart_Order();
		return array( 'data' => $order->repeatPerDayByRestaurant( false ), 'unit' =>$order->unit, 'interval' => 'day' );
	}

	public function repeatPerMonthByRestaurant( $render = false ){
		$order = new Crunchbutton_Chart_Order();
		return array( 'data' => $order->repeatPerMonthByRestaurant( false ), 'unit' =>$order->unit, 'interval' => 'month' );
	}

	public function repeatPerMonth( $render = false ){
		$order = new Crunchbutton_Chart_Order();
		return array( 'data' => $order->repeatPerMonth( false ), 'unit' =>$order->unit, 'interval' => 'month' );
	}

	public function repeatPerMonthByCommunityGrouped( $render = false ){
		$order = new Crunchbutton_Chart_Order();
		return array( 'data' => $order->repeatPerMonthByCommunityGrouped( false ), 'unit' =>$order->unit, 'interval' => 'month' );
	}

	public function repeatPerMonthByCommunity( $render = false ){
		$order = new Crunchbutton_Chart_Order();
		return array( 'data' => $order->repeatPerMonthByCommunity( false ), 'unit' =>$order->unit, 'interval' => 'month' );
	}

	public function totalUsersAll(){
		$query = "SELECT
										 COUNT( DISTINCT( o.phone ) ) AS Total
							FROM `order` o
							INNER JOIN `user` u ON u.id_user = o.id_user
								{$this->queryExcludeUsers}";
		$result = c::db()->get( $query );
		return $result->_items[0]->Total;
	}

	public function totalUsersByCommunity( $id_community ){
		$query = "SELECT
										 COUNT( DISTINCT( o.phone ) ) AS Total
							FROM `order` o
							INNER JOIN `user` u ON u.id_user = o.id_user
							LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant
							INNER JOIN restaurant_community rc ON r.id_restaurant = rc.id_restaurant AND rc.id_community = ?
								{$this->queryExcludeUsers}";
		$result = c::db()->get( $query, [$id_community]);
		return $result->_items[0]->Total;
	}

}