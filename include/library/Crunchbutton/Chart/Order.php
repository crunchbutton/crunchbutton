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
																'orders-repeat-per-active-user-per-day' => array( 'title' => 'Day', 'interval' => 'day', 'type' => 'column', 'method' => 'repeatByActiveuserByDay'),
																'orders-repeat-per-active-user-per-week' => array( 'title' => 'Week', 'interval' => 'week', 'type' => 'column', 'method' => 'repeatByActiveuserByWeek', 'default' => true, 'filters' => array( array( 'title' => 'Community', 'type' => 'community', 'method' => 'repeatByActiveuserByWeekByCommunity' ) ) ),
																'orders-repeat-per-active-user-per-month' => array( 'title' => 'Month', 'interval' => 'month', 'type' => 'column', 'method' => 'repeatByActiveuserByMonth', 'filters' => array( array( 'title' => 'Community', 'type' => 'community', 'method' => 'repeatByActiveuserByMonthByCommunity' ) ) )
															)
												),
												'group-orders-per-day-per-community' => array(
														'title' => 'Orders per Day By Community',
														'tags' => array( 'especial' ),
														'charts' => array(
																'orders-per-day-per-community' => array( 'title' => 'Last 14 Days', 'interval' => 'day', 'type' => 'column', 'method' => 'byDayPerCommunitySelective' ),
															)
												),
												'group-delivered-orders-by-community' => array(
														'title' => 'Delivered',
														'tags' => array( 'especial' ),
														'charts' => array(
																'delivered-orders-per-day-per-community' => array( 'title' => 'Orders', 'interval' => 'hour', 'type' => 'column', 'method' => 'deliveredByDayPerCommunity' ),
															)
												),
												'group-orders-community' => array(
														'title' => 'Orders',
														'tags' => array( 'reps' ),
														'charts' => array(
																'orders-per-day-by-community' => array( 'title' => 'Day', 'interval' => 'day', 'type' => 'column-community', 'method' => 'ordersByDayByCommunity' ),
																'orders-per-week-by-community' => array( 'title' => 'Week', 'interval' => 'week', 'type' => 'column-community', 'method' => 'ordersByWeekByCommunity', 'default' => true ),
																'orders-per-month-by-community' => array( 'title' => 'Month', 'interval' => 'month', 'type' => 'column-community', 'method' => 'ordersByMonthByCommunity' ),
															)
												),
												'group-orders-median-delivery-time-community' => array(
														'title' => 'Avg Delivery Time',
														'tags' => array( 'reps' ),
														'charts' => array(
																'median-delivery-time-community-per-week-by-community' => array( 'title' => 'Week', 'interval' => 'week', 'type' => 'column-community', 'method' => 'ordersMedianDeliveryTimeByWeekByCommunity', 'default' => true ),
															)
												),
												'group-orders-median-delivery-time-community' => array(
														'title' => 'Avg Delivery Time',
														'tags' => array( 'reps' ),
														'charts' => array(
																'median-delivery-time-community-per-week-by-community' => array( 'title' => 'Week', 'interval' => 'week', 'type' => 'column-community', 'method' => 'ordersMedianDeliveryTimeByWeekByCommunity', 'default' => true ),
															)
												),
												'group-orders-delivered-in-greater-than-60-min-community' => array(
														'title' => 'Delivered in greater than 60 min',
														'tags' => array( 'reps' ),
														'charts' => array(
																'orders-delivered-in-greater-than-60-min-community' => array( 'title' => 'Week', 'interval' => 'week', 'type' => 'column-community', 'method' => 'ordersDelivedInGreaterThan60MinCommunity', 'default' => true ),
															)
												),
												'group-past-orders-per-restaurant-by-community' => array(
														'title' => 'Orders per restaurant (past 100 orders)',
														'tags' => array( 'reps' ),
														'charts' => array(
																'past-orders-per-restaurant-by-community' => array( 'title' => 'Week', 'interval' => 'week', 'type' => 'column-community', 'method' => 'ordersPerRestaurantByCommunity', 'default' => true ),
															)
												),
												/*
												'group-orders-repeat' => array(
														'title' => 'Repeat Orders',
														'tags' => array( 'main' ),
														'charts' => array(
																'orders-repeat-day' => array( 'title' => 'Day', 'interval' => 'day', 'type' => 'column', 'method' => 'repeatPerDay', 'filters' => array( array( 'title' => 'Community', 'type' => 'community', 'method' => 'repeatVsNewPerDayPerCommunity' ) ) ),
																'orders-repeat-week' => array( 'title' => 'Week', 'interval' => 'week', 'type' => 'column', 'method' => 'repeatPerWeek', 'filters' => array( array( 'title' => 'Community', 'type' => 'community', 'method' => 'repeatVsNewPerWeekPerCommunity' ) ), 'default' => true ),
																'orders-repeat-month' => array( 'title' => 'Month', 'interval' => 'month', 'type' => 'column', 'method' => 'repeatPerMonth', 'filters' => array( array( 'title' => 'Community', 'type' => 'community', 'method' => 'repeatVsNewPerMonthPerCommunity' ) ) ),
															)
												),
												*/
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
														'charts' => array(
																'orders-by-weekday-by-community' => array( 'title' => '', 'type' => 'area', 'method' => 'byWeekdayByCommunity' )
															)
												),
											'group-orders-track-frequece' => array(
														'title' => 'Track Orders Frequency',
														'tags' => array( 'detailed-analytics' ),
														'charts' => array(
																'orders-track-frequece' => array( 'title' => 'Orders', 'interval' => 'week', 'type' => 'area', 'method' => 'trackFrequence' ),
															)
												),
												'group-orders-per-restaurant-by-community' => array(
														'title' => 'Orders per Restaurant by Community',
														'charts' => array(
																'orders-per-restaurant-by-community' => array( 'title' => '', 'type' => 'pie_communities', 'method' => 'perRestaurantPerCommunity' )
															)
												),
												'group-orders-per-weekday-by-community' => array(
														'title' => 'Orders per Weekday by Community',
														'tags' => array( 'reps' ),
														'charts' => array(
																'orders-per-weekday-by-community' => array( 'title' => '', 'type' => 'column-community', 'method' => 'perWeekdayByCommunity' )
															)
												),
												'group-orders-per-hour-by-community' => array(
														'title' => 'Orders per Hour by Community',
														'tags' => array( 'reps' ),
														'charts' => array(
																'orders-per-hour-by-community' => array( 'title' => '', 'type' => 'column-community', 'method' => 'perHourByCommunity' )
															)
												),
										);

	public function __construct() {
		parent::__construct();
	}

	public function byWeekdayByCommunity( $render = false ){

		$query = "SELECT DATE_FORMAT(CONVERT_TZ(`date`, '-8:00', '-5:00'), '%W') AS `Day`,
										 COUNT(*) AS `Orders`,
										 c.name AS `Community`
							FROM `order` o
							LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant
							INNER JOIN restaurant_community rc ON r.id_restaurant = rc.id_restaurant
							INNER JOIN community c ON c.id_community = rc.id_community AND c.name NOT LIKE 'test%'
							WHERE env = 'live'
								{$this->queryExcludeUsers}
							GROUP BY DATE_FORMAT(CONVERT_TZ(`date`, '-8:00', '-5:00'), '%W'),
								c.name
							ORDER BY DATE_FORMAT(CONVERT_TZ(`date`, '-8:00', '-5:00'), '%Y%m%d'),
								c.name";
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
								INNER JOIN `user` u ON u.id_user = o.id_user
								LEFT JOIN restaurant_community rc ON o.id_restaurant = rc.id_restaurant
								LEFT JOIN community c ON rc.id_community = c.id_community {$this->queryExcludeCommunties}
								WHERE
									o.date >= '{$this->monthFrom}-01'
									AND
									DATE_FORMAT( o.date ,'%Y-%m') <= '{$this->monthTo}'
									{$this->queryExcludeUsers}
								GROUP BY DATE_FORMAT(o.date ,'%Y-%m') HAVING Month BETWEEN '{$this->monthFrom}' AND '{$this->monthTo}'";
		$parsedData = $this->parseDataMonthSimple( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit, 'interval' => 'month' );
		}
		return $parsedData;
	}

	public function ordersByMonthByCommunity( $render = false ){

		$community = ( $community ? $community : $_REQUEST[ 'community' ] );

		$query = "SELECT DATE_FORMAT( o.date ,'%Y-%m') AS Month,
											COUNT(*) AS Total
								FROM `order` o
								INNER JOIN `user` u ON u.id_user = o.id_user
								LEFT JOIN restaurant_community rc ON o.id_restaurant = rc.id_restaurant
								LEFT JOIN community c ON rc.id_community = c.id_community {$this->queryExcludeCommunties}
								WHERE
									o.date >= '{$this->monthFrom}-01' AND o.date <= LAST_DAY( STR_TO_DATE( '{$this->monthTo}', '%Y-%m' ) )
									{$this->queryExcludeUsers}
									AND c.id_community = {$community}
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
								INNER JOIN `user` u ON u.id_user = o.id_user
								LEFT JOIN restaurant_community rc ON o.id_restaurant = rc.id_restaurant
								LEFT JOIN community c ON rc.id_community = c.id_community {$this->queryExcludeCommunties}
								WHERE
									YEARWEEK(o.date) >= {$this->weekFrom} AND YEARWEEK(o.date) <= {$this->weekTo}
									{$this->queryExcludeUsers}
								GROUP BY YEARWEEK(date)
								ORDER BY YEARWEEK(date) ASC";

		$parsedData = $this->parseDataWeeksSimple( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit );
		}
		return $parsedData;
	}

	public function ordersMedianDeliveryTimeByWeekByCommunity( $render = false ){

		$community = ( $community ? $community : $_REQUEST[ 'community' ] );

		$query = "SELECT 	YEARWEEK(date) AS Week,
											o.id_order,
											o.date,
											oa.timestamp
								FROM `order` o
								INNER JOIN `user` u ON u.id_user = o.id_user
								LEFT JOIN restaurant_community rc ON o.id_restaurant = rc.id_restaurant
								LEFT JOIN community c ON rc.id_community = c.id_community {$this->queryExcludeCommunties}
								INNER JOIN order_action oa ON oa.id_order = o.id_order AND oa.type = '" . Crunchbutton_Order_Action::DELIVERY_DELIVERED . "'
								WHERE
									YEARWEEK(o.date) >= {$this->weekFrom} AND YEARWEEK(o.date) <= {$this->weekTo}
									{$this->queryExcludeUsers}
									AND c.id_community = {$community}
								GROUP BY YEARWEEK(date)
								ORDER BY YEARWEEK(date) ASC";

		$data = c::db()->get( $query );

		$weeks = [];
		foreach ( $data as $item ) {
			$weeks[ $item->Week ] = [ 'orders' => 0, 'time' => 0 ];
		}
		foreach ( $data as $item ) {
			$weeks[ $item->Week ][ 'orders' ]++;
			$ordered_at = new DateTime ( $item->date, new DateTimeZone( c::config()->timezone ) );
			$delivered_at = new DateTime ( $item->timestamp, new DateTimeZone( c::config()->timezone ) );

			$timeToDelivery = $delivered_at->diff( $ordered_at );

			$timeToDelivery = ( $timeToDelivery->days * 24 * 60 ) + ( $timeToDelivery->h * 60 ) + ( $timeToDelivery->i );

			$weeks[ $item->Week ][ 'time' ] += $timeToDelivery;
		}

		$_weeks = [];
		foreach ( $weeks as $week => $values ) {
			$_weeks[ $week ] = intval( $values[ 'time' ] / $values[ 'orders' ] );
		}

		$allWeeks = $this->allWeeks();

		$data = [];
		for( $i = $this->from -1 ; $i < $this->to; $i++ ){
			$week = $allWeeks[ $i ];
			$total = ( $_weeks[ $week ] ) ? $_weeks[ $week ] : 0;
			$data[] = ( object ) array( 'Label' => $this->parseWeek( $week ), 'Total' => $total, 'Type' => 'Minutes'  );
		}

		if( $render ){
			return array( 'data' => $data, 'unit' => 'minutes' );
		}
		return $parsedData;
	}


	public function ordersPerRestaurantByCommunity( $render = false ){

		$community = ( $community ? $community : $_REQUEST[ 'community' ] );

		$query = "SELECT
								r.name AS Restaurant, o.id_order
							FROM `order` o
							INNER JOIN restaurant r ON r.id_restaurant = o.id_restaurant
							WHERE
								o.id_community = {$community}
								{$this->queryExcludeUsers}
								ORDER BY o.id_order DESC LIMIT 100";

		$data = c::db()->get( $query );

		$weeks = [];
		foreach ( $data as $item ) {
			if( !$weeks[ $item->Restaurant ] ){
				$weeks[ $item->Restaurant ] = 0;
			}
			$weeks[ $item->Restaurant ]++;
		}

		$data = [];
		foreach ( $weeks as $restaurant => $val ) {
			$data[] = ( object ) array( 'Label' => 'Orders', 'Total' => $val, 'Type' => $restaurant );
		}

		if( $render ){
			return array( 'data' => $data, 'unit' => 'Orders' );
		}
		return $parsedData;
	}

	public function ordersDelivedInGreaterThan60MinCommunity( $render = false ){

		$community = ( $community ? $community : $_REQUEST[ 'community' ] );

		$query = "SELECT 	YEARWEEK(date) AS Week,
											o.id_order,
											o.date,
											oa.timestamp
								FROM `order` o
								INNER JOIN `user` u ON u.id_user = o.id_user
								LEFT JOIN restaurant_community rc ON o.id_restaurant = rc.id_restaurant
								LEFT JOIN community c ON rc.id_community = c.id_community {$this->queryExcludeCommunties}
								INNER JOIN order_action oa ON oa.id_order = o.id_order AND oa.type = '" . Crunchbutton_Order_Action::DELIVERY_DELIVERED . "'
								WHERE
									YEARWEEK(o.date) >= {$this->weekFrom} AND YEARWEEK(o.date) <= {$this->weekTo}
									{$this->queryExcludeUsers}
									AND c.id_community = {$community}
								GROUP BY YEARWEEK(date)
								ORDER BY YEARWEEK(date) ASC";

		$data = c::db()->get( $query );

		$weeks = [];
		foreach ( $data as $item ) {
			$weeks[ $item->Week ] = 0;
		}
		foreach ( $data as $item ) {

			$ordered_at = new DateTime ( $item->date, new DateTimeZone( c::config()->timezone ) );
			$delivered_at = new DateTime ( $item->timestamp, new DateTimeZone( c::config()->timezone ) );
			$timeToDelivery = $delivered_at->diff( $ordered_at );
			$timeToDelivery = ( $timeToDelivery->days * 24 * 60 ) + ( $timeToDelivery->h * 60 ) + ( $timeToDelivery->i );
			if( $timeToDelivery > 60 ){
				$weeks[ $item->Week ]++;
			}
		}

		$_weeks = [];
		foreach ( $weeks as $week => $val ) {
			$_weeks[ $week ] = $val;
		}
		$allWeeks = $this->allWeeks();

		$data = [];
		for( $i = $this->from -1 ; $i < $this->to; $i++ ){
			$week = $allWeeks[ $i ];
			$total = ( $_weeks[ $week ] ) ? $_weeks[ $week ] : 0;
			$data[] = ( object ) array( 'Label' => $this->parseWeek( $week ), 'Total' => $total, 'Type' => 'Orders'  );
		}

		if( $render ){
			return array( 'data' => $data, 'unit' => 'Orders' );
		}
		return $parsedData;
	}


	public function ordersByWeekByCommunity( $render = false ){

		$community = ( $community ? $community : $_REQUEST[ 'community' ] );

		$query = "SELECT YEARWEEK(date) AS Week,
											 COUNT(*) AS Total
								FROM `order` o
								INNER JOIN `user` u ON u.id_user = o.id_user
								LEFT JOIN restaurant_community rc ON o.id_restaurant = rc.id_restaurant
								LEFT JOIN community c ON rc.id_community = c.id_community {$this->queryExcludeCommunties}
								WHERE
									YEARWEEK(o.date) >= {$this->weekFrom} AND YEARWEEK(o.date) <= {$this->weekTo}
									{$this->queryExcludeUsers}
									AND c.id_community = {$community}
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
								INNER JOIN `user` u ON u.id_user = o.id_user
								LEFT JOIN restaurant_community rc ON o.id_restaurant = rc.id_restaurant
								LEFT JOIN community c ON rc.id_community = c.id_community {$this->queryExcludeCommunties}
								WHERE
									1 = 1
									{$this->queryExcludeUsers}
								GROUP BY DATE_FORMAT(o.date ,'%Y-%m-%d') HAVING Day BETWEEN '{$this->dayFrom}' AND '{$this->dayTo}'";

		$parsedData = $this->parseDataDaysSimple( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit, 'interval' => 'day' );
		}
		return $parsedData;
	}

	public function ordersByDayByCommunity( $render = false ){

		$community = ( $community ? $community : $_REQUEST[ 'community' ] );

		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );

		$this->dayTo = $now->format( 'Y-m-d' );
		$now->modify( '-2 weeks' );
		$this->dayFrom = $now->format( 'Y-m-d' );

		$this->dayFrom = ( $_REQUEST[ 'from' ] ? $this->allDays[ $_REQUEST[ 'from' ] - 1 ] : $this->dayFrom );
		$this->dayTo = ( $_REQUEST[ 'to' ] ? $this->allDays[ $_REQUEST[ 'to' ] - 1 ] : $this->dayTo );

		$this->from_day = ( $_REQUEST[ 'from' ] ? $_REQUEST[ 'from' ] : ( count( $this->allDays ) - 15 ) );
		$this->to_day = ( $_REQUEST[ 'to' ] ? $_REQUEST[ 'to' ] : count( $this->allDays ) );

		$query = "SELECT DATE_FORMAT(o.date ,'%Y-%m-%d') AS Day,
											 COUNT(*) AS Total
								FROM `order` o
								INNER JOIN `user` u ON u.id_user = o.id_user
								LEFT JOIN restaurant_community rc ON o.id_restaurant = rc.id_restaurant
								LEFT JOIN community c ON rc.id_community = c.id_community {$this->queryExcludeCommunties}
								WHERE
									1 = 1
									{$this->queryExcludeUsers}
								AND c.id_community = '{$community}'
								GROUP BY DATE_FORMAT(o.date ,'%Y-%m-%d') HAVING Day BETWEEN '{$this->dayFrom}' AND '{$this->dayTo}'";

		$parsedData = $this->parseDataDaysSimple( $query, $this->description );

		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit, 'interval' => 'day' );
		}
		return $parsedData;
	}


	public function byWeekPerRestaurant( $render = false ){

		$restaurant = ( $_REQUEST[ 'restaurant' ] ) ? $_REQUEST[ 'restaurant' ] : false;

		$query = "SELECT YEARWEEK(date) AS Week,
									 COUNT(*) AS Total,
									 r.name AS 'Group'
						FROM `order` o
						INNER JOIN `user` u ON u.id_user = o.id_user
						LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant
						WHERE YEARWEEK(o.date) >= {$this->weekFrom} AND YEARWEEK(o.date) <= {$this->weekTo}
							AND r.id_restaurant = '{$restaurant}'
							{$this->queryExcludeUsers}
						GROUP BY YEARWEEK(date), r.name
						ORDER BY YEARWEEK(date) DESC";

		$parsedData = $this->parseDataWeeksSimple( $query, $this->description );

		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit );
		}
		return $parsedData;
	}

	public function byWeekPerCommunity( $render = false ){

		$community = ( $_REQUEST[ 'community' ] ) ? $_REQUEST[ 'community' ] : false;

		if( $community ){
			$query = "SELECT YEARWEEK(date) AS Week,
										 COUNT(*) AS Total,
										 c.name AS 'Group'
							FROM `order` o
								INNER JOIN `user` u ON u.id_user = o.id_user
								LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant
								INNER JOIN restaurant_community rc ON r.id_restaurant = rc.id_restaurant
								INNER JOIN community c ON c.id_community = rc.id_community
							WHERE YEARWEEK(o.date) >= {$this->weekFrom} AND YEARWEEK(o.date) <= {$this->weekTo}
								AND c.id_community = '{$community}'
								{$this->queryExcludeUsers}
							GROUP BY YEARWEEK(date), c.name
							ORDER BY YEARWEEK(date) DESC";
				$parsedData = $this->parseDataWeeksSimple( $query, $this->description );
		} else {

			$query = "SELECT YEARWEEK(date) AS Week,
											 COUNT(*) AS Total,
											 c.name AS 'Group'
								FROM `order` o
								INNER JOIN `user` u ON u.id_user = o.id_user
								 LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant
								 INNER JOIN restaurant_community rc ON r.id_restaurant = rc.id_restaurant
								 INNER JOIN community c ON c.id_community = rc.id_community AND c.name NOT LIKE 'test%'
								WHERE YEARWEEK(o.date) >= {$this->weekFrom} AND YEARWEEK(o.date) <= {$this->weekTo}
									{$this->queryExcludeUsers}
								GROUP BY YEARWEEK(date), c.name
								ORDER BY YEARWEEK(date) DESC";
			$parsedData = $this->parseDataWeeksGroup( $query, $this->description );

		}
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
				$cohort = Crunchbutton_Chart_Cohort::_get( $id_chart_cohort, $cohort_type );
				$query = "SELECT DATE_FORMAT(o.date ,'%Y-%m-%d') AS Day,
													 COUNT(*) AS Total
										FROM `order` o
										INNER JOIN `user` u ON u.id_user = o.id_user
										LEFT JOIN restaurant_community rc ON o.id_restaurant = rc.id_restaurant
										LEFT JOIN community c ON rc.id_community = c.id_community {$this->queryExcludeCommunties}
										WHERE
											1 = 1
											{$cohort->toQuery()}
											{$this->queryExcludeUsers}
										GROUP BY DATE_FORMAT(o.date ,'%Y-%m-%d') HAVING Day BETWEEN '{$this->dayFrom}' AND '{$this->dayTo}'";
				break;

			case 'months':
				$month = $id_chart_cohort;
				$query = "SELECT DATE_FORMAT(o.date ,'%Y-%m-%d') AS Day,
													 COUNT(*) AS Total
										FROM `order` o
										INNER JOIN `user` u ON u.id_user = o.id_user
										LEFT JOIN restaurant_community rc ON o.id_restaurant = rc.id_restaurant
										LEFT JOIN community c ON rc.id_community = c.id_community {$this->queryExcludeCommunties}
										WHERE
											1 = 1
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
										INNER JOIN `user` u ON u.id_user = o.id_user
										LEFT JOIN restaurant_community rc ON o.id_restaurant = rc.id_restaurant
										LEFT JOIN community c ON rc.id_community = c.id_community {$this->queryExcludeCommunties}
										WHERE
											1 = 1
											{$this->queryExcludeUsers}
											AND o.phone IN ( SELECT DISTINCT( u.phone ) FROM credit c
											INNER JOIN promo_group_promo pg ON pg.id_promo_group = {$giftcard_group} AND pg.id_promo = c.id_promo
											INNER JOIN `user` u ON u.id_user = c.id_user )
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
				$cohort = Crunchbutton_Chart_Cohort::_get( $id_chart_cohort, $cohort_type );
				$query = "SELECT DATE_FORMAT( o.date ,'%Y-%m') AS Month,
													COUNT(*) AS Total
										FROM `order` o
										INNER JOIN `user` u ON u.id_user = o.id_user
										LEFT JOIN restaurant_community rc ON o.id_restaurant = rc.id_restaurant
										LEFT JOIN community c ON rc.id_community = c.id_community {$this->queryExcludeCommunties}
										WHERE
											o.date >= '{$this->monthFrom}-01' AND o.date <= LAST_DAY( STR_TO_DATE( '{$this->monthTo}', '%Y-%m' ) )
											{$this->queryExcludeUsers}
											{$cohort->toQuery()}
										GROUP BY DATE_FORMAT(o.date ,'%Y-%m') HAVING Month BETWEEN '{$this->monthFrom}' AND '{$this->monthTo}'";
				break;

			case 'months':
				$month = $id_chart_cohort;
				$query = "SELECT DATE_FORMAT( o.date ,'%Y-%m') AS Month,
													COUNT(*) AS Total
										FROM `order` o
										INNER JOIN `user` u ON u.id_user = o.id_user
										LEFT JOIN restaurant_community rc ON o.id_restaurant = rc.id_restaurant
										LEFT JOIN community c ON rc.id_community = c.id_community {$this->queryExcludeCommunties}
										WHERE
											o.date >= '{$this->monthFrom}-01' AND o.date <= LAST_DAY( STR_TO_DATE( '{$this->monthTo}', '%Y-%m' ) )
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
										INNER JOIN `user` u ON u.id_user = o.id_user
										LEFT JOIN restaurant_community rc ON o.id_restaurant = rc.id_restaurant
										LEFT JOIN community c ON rc.id_community = c.id_community {$this->queryExcludeCommunties}
										WHERE
											o.date >= '{$this->monthFrom}-01' AND o.date <= LAST_DAY( STR_TO_DATE( '{$this->monthTo}', '%Y-%m' ) )
											{$this->queryExcludeUsers}
										AND o.phone IN ( SELECT DISTINCT( u.phone ) FROM credit c
											INNER JOIN promo_group_promo pg ON pg.id_promo_group = {$giftcard_group} AND pg.id_promo = c.id_promo
											INNER JOIN `user` u ON u.id_user = c.id_user )
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
				$cohort = Crunchbutton_Chart_Cohort::_get( $id_chart_cohort, $cohort_type );
				$query = "SELECT YEARWEEK(date) AS Week,
													 COUNT(*) AS Total
										FROM `order` o
										INNER JOIN `user` u ON u.id_user = o.id_user
										LEFT JOIN restaurant_community rc ON o.id_restaurant = rc.id_restaurant
										LEFT JOIN community c ON rc.id_community = c.id_community {$this->queryExcludeCommunties}
										WHERE
											YEARWEEK(o.date) >= {$this->weekFrom} AND YEARWEEK(o.date) <= {$this->weekTo}
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
										INNER JOIN `user` u ON u.id_user = o.id_user
										LEFT JOIN restaurant_community rc ON o.id_restaurant = rc.id_restaurant
										LEFT JOIN community c ON rc.id_community = c.id_community {$this->queryExcludeCommunties}
										WHERE
											YEARWEEK(o.date) >= {$this->weekFrom} AND YEARWEEK(o.date) <= {$this->weekTo}
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
										INNER JOIN `user` u ON u.id_user = o.id_user
										LEFT JOIN restaurant_community rc ON o.id_restaurant = rc.id_restaurant
										LEFT JOIN community c ON rc.id_community = c.id_community {$this->queryExcludeCommunties}
										WHERE
											YEARWEEK(o.date) >= {$this->weekFrom} AND YEARWEEK(o.date) <= {$this->weekTo}
											{$this->queryExcludeUsers}
											AND o.phone IN ( SELECT DISTINCT( u.phone ) FROM credit c
											INNER JOIN promo_group_promo pg ON pg.id_promo_group = {$giftcard_group} AND pg.id_promo = c.id_promo
											INNER JOIN `user` u ON u.id_user = c.id_user )
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

public function byDayPerRestaurant( $render = false ){

		$restaurant = ( $_REQUEST[ 'restaurant' ] ) ? $_REQUEST[ 'restaurant' ] : false;

		$query = "SELECT DATE_FORMAT( date ,'%Y-%m-%d') AS Day,
										 COUNT(*) AS Total,
										 r.name AS 'Group'
							FROM `order` o
							INNER JOIN `user` u ON u.id_user = o.id_user
							LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant
							WHERE o.date >= '{$this->dayFrom}' AND o.date <= '{$this->dayTo}'
								AND r.id_restaurant = '{$restaurant}'
								{$this->queryExcludeUsers}
							GROUP BY DATE_FORMAT( date ,'%Y-%m-%d')
							ORDER BY DATE_FORMAT( date ,'%Y-%m-%d') DESC";

		$parsedData = $this->parseDataDaysSimple( $query, $this->description );

		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit, 'interval' => 'day' );
		}
		return $parsedData;
	}

	public function deliveredByDayPerCommunity(){

			$id_community = $_REQUEST[ 'id_community' ];
			$day = $_REQUEST[ 'day' ];

			$where_day = ( $day != 'All' ) ? "AND DATE_FORMAT( o.date, '%W' ) = '{$day}' " : "";

			$query = "SELECT COUNT(*) AS Total,
									DATE_FORMAT( o.date, '%H' ) AS Hour
									FROM `order` o
									INNER JOIN restaurant r ON r.id_restaurant = o.id_restaurant
									INNER JOIN restaurant_community rc ON rc.id_restaurant = r.id_restaurant AND rc.id_community = ?
									WHERE o.delivery_service = true {$where_day}
									AND o.date BETWEEN NOW() - INTERVAL 14 DAY AND NOW()
									GROUP BY Hour";

			$data = c::db()->get( $query, [$id_community]);

			$_hours = [];

			for( $i = 0; $i <= 12; $i++ ){
				$hour = $i . ( $i == 12 ? ' pm' : ' am' );
				$_hours[ $hour ] = 0;
			}
			for( $i = 1; $i <= 12; $i++ ){
				$hour = $i . ' pm';
				$_hours[ $hour ] = 0;
			}

			$community = Crunchbutton_Community::o( $id_community );

			foreach ( $data as $item ) {
				$date = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
				$date->setTime( $item->Hour, 00 );
				$date->setTimezone( new DateTimeZone( $community->timezone ) );
				$_hours[ $date->format( 'g a' ) ] = $item->Total;
			}

			$data = [];

			foreach( $_hours as $hour => $value ){
				$data[] = ( object ) array( 'Label' => $hour, 'Total' => $value, 'Type' => 'Hour'  );
			}
			return array( 'data' => $data, 'unit' => $this->unit, 'interval' => 'hour' );
	}

	public function byDayPerCommunitySelective(){

			$communities = $_REQUEST[ 'communities' ];
			$communities = explode( ',' , $communities );

			if( count( $communities ) == 0 ){
				echo 'Please select one or more communities';
			}

			$where = ' AND ( ';
			$or = '';
			foreach ( $communities as $community ) {
				$where .= $or . " c.id_community = '{$community}'";
				$or = ' OR ';
			}
			$where .= ' ) ';

			$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
			$this->dayTo = $now->format( 'Y-m-d' );
			$now->modify( '- 14 day' );
			$this->dayFrom = $now->format( 'Y-m-d' );

			$query = "SELECT 	DATE_FORMAT( date ,'%Y-%m-%d') AS Day,
												COUNT(*) AS Total,
												c.name AS `Group`
								FROM `order` o
									INNER JOIN restaurant r ON r.id_restaurant = o.id_restaurant
									INNER JOIN restaurant_community rc ON r.id_restaurant = rc.id_restaurant
									INNER JOIN community c ON c.id_community = rc.id_community AND c.name NOT LIKE 'test%'
								WHERE o.date >= '{$this->dayFrom}' AND o.date <= '{$this->dayTo}'
									{$this->queryExcludeUsers}
									{$where}
									GROUP BY DATE_FORMAT( date ,'%Y-%m-%d'), c.name
									ORDER BY DATE_FORMAT( date ,'%Y-%m-%d') DESC";

			$data = c::db()->get( $query );

			$_days = [];

			foreach ( $data as $item ) {
				$_days[ $item->Day ][ $item->Group ] = $item->Total;
			}

			$data = [];

			// Get the communities
			$query = "SELECT DISTINCT( c.name ) AS community FROM community c WHERE 1 = 1 $where";
			$results = c::db()->get( $query );
			$communities = array();
			foreach ( $results as $result ) {
				if( !$result->community ){
					continue;
				}
				$communities[] = $result->community;
			}

			$groups = $communities;

			// echo '<pre>';var_dump( $this->from_day );exit();

			$allDays = $this->allDays();

			for( $i = 0; $i < 14; $i++ ){
				$now->modify( '+ 1 day' );
				$day = $now->format( 'Y-m-d' );
				foreach( $groups as $group ){
					$total = ( $_days[ $day ][ $group ] ) ? $_days[ $day ][ $group ] : 0;
					$data[] = ( object ) array( 'Label' => $this->parseDay( $day ), 'Total' => $total, 'Type' => $group  );
				}
			}
			return array( 'data' => $data, 'unit' => $this->unit, 'interval' => 'day' );
	}

	public function byDayPerCommunity( $render = false, $_community = false ){

		$community = ( $_REQUEST[ 'community' ] ) ? $_REQUEST[ 'community' ] : false;

		if( !$community && $_community ){
			$community = $_community;
		}

		if( $community ){
			$query = "SELECT DATE_FORMAT( date ,'%Y-%m-%d') AS Day,
											 COUNT(*) AS Total,
											 c.name AS 'Group'
								FROM `order` o
								INNER JOIN `user` u ON u.id_user = o.id_user
								LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant
								INNER JOIN restaurant_community rc ON r.id_restaurant = rc.id_restaurant
								INNER JOIN community c ON c.id_community = rc.id_community AND c.name NOT LIKE 'test%'
								WHERE o.date >= '{$this->dayFrom}' AND o.date <= '{$this->dayTo}'
									AND c.id_community = '{$community}'
									{$this->queryExcludeUsers}
								GROUP BY DATE_FORMAT( date ,'%Y-%m-%d'), c.name
								ORDER BY DATE_FORMAT( date ,'%Y-%m-%d') DESC";
			$parsedData = $this->parseDataDaysSimple( $query, $this->description );
		} else {

			$query = "SELECT DATE_FORMAT( date ,'%Y-%m-%d') AS Day,
											COUNT(*) AS Total,
											c.name AS 'Group'
								FROM `order` o
								 INNER JOIN `user` u ON u.id_user = o.id_user
								 LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant
								 INNER JOIN restaurant_community rc ON r.id_restaurant = rc.id_restaurant
								 INNER JOIN community c ON c.id_community = rc.id_community AND c.name NOT LIKE 'test%'
								WHERE o.date >= '{$this->dayFrom}' AND o.date <= '{$this->dayTo}'
									{$this->queryExcludeUsers}
								GROUP BY DATE_FORMAT( date ,'%Y-%m-%d'), c.name
								ORDER BY DATE_FORMAT( date ,'%Y-%m-%d') DESC";

			$parsedData = $this->parseDataDaysGroup( $query, $this->description );
		}

		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit, 'interval' => 'day' );
		}
		return $parsedData;
	}

	public function byMonthPerCommunity( $render = false ){

		$community = ( $_REQUEST[ 'community' ] ) ? $_REQUEST[ 'community' ] : false;

		if( $community ){
			$query = "SELECT DATE_FORMAT( date ,'%Y-%m') AS Month,
											 COUNT(*) AS Total,
											 c.name AS 'Group'
								FROM `order` o
								INNER JOIN `user` u ON u.id_user = o.id_user
								LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant
								INNER JOIN restaurant_community rc ON r.id_restaurant = rc.id_restaurant
								INNER JOIN community c ON c.id_community = rc.id_community AND c.name NOT LIKE 'test%'
								WHERE o.date >= '{$this->monthFrom}-01' AND o.date <= LAST_DAY( STR_TO_DATE( '{$this->monthTo}', '%Y-%m' ) )
									AND c.id_community = '{$community}'
									{$this->queryExcludeUsers}
								GROUP BY DATE_FORMAT( date ,'%Y-%m'), c.name
								ORDER BY DATE_FORMAT( date ,'%Y-%m') DESC";
			$parsedData = $this->parseDataMonthSimple( $query, $this->description );
		} else {
			$query = "SELECT DATE_FORMAT( date ,'%Y-%m') AS Month,
											 COUNT(*) AS Total,
											 c.name AS 'Group'
								FROM `order` o
									LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant
								 INNER JOIN restaurant_community rc ON r.id_restaurant = rc.id_restaurant
								 INNER JOIN community c ON c.id_community = rc.id_community AND c.name NOT LIKE 'test%'
								WHERE o.date >= '{$this->monthFrom}-01' AND o.date <= LAST_DAY( STR_TO_DATE( '{$this->monthTo}', '%Y-%m' ) )
									{$this->queryExcludeUsers}
								GROUP BY DATE_FORMAT( date ,'%Y-%m'), c.name
								ORDER BY DATE_FORMAT( date ,'%Y-%m') DESC";
			$parsedData = $this->parseDataMonthGroup( $query, $this->description );
		}


		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit, 'interval' => 'month' );
		}
		return $parsedData;
	}

	public function byMonthPerRestaurant( $render = false ){

		$restaurant = ( $_REQUEST[ 'restaurant' ] ) ? $_REQUEST[ 'restaurant' ] : false;

		$query = "SELECT DATE_FORMAT( date ,'%Y-%m') AS Month,
										 COUNT(*) AS Total,
										 r.name AS 'Group'
							FROM `order` o
							INNER JOIN `user` u ON u.id_user = o.id_user
							LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant
							WHERE o.date >= '{$this->monthFrom}-01' AND o.date <= LAST_DAY( STR_TO_DATE( '{$this->monthTo}', '%Y-%m' ) )
								AND r.id_restaurant = '{$restaurant}'
								{$this->queryExcludeUsers}
							GROUP BY DATE_FORMAT( date ,'%Y-%m')
							ORDER BY DATE_FORMAT( date ,'%Y-%m') DESC";
		$parsedData = $this->parseDataMonthSimple( $query, $this->description );

		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit, 'interval' => 'month' );
		}
		return $parsedData;
	}

	public function perHourByCommunity( $render = false ){

		$community = ( $_REQUEST[ 'community' ] ? 'AND o.id_community = ' . $_REQUEST[ 'community' ] : '' );

		$query = "SELECT 	c.name AS 'Community',
											count(1) AS 'Orders',
											HOUR( o.date ) AS 'Day'
							FROM `order` o
								INNER JOIN community c ON c.id_community = o.id_community
								WHERE 1=1 {$community}
							GROUP BY HOUR( o.date )";

		$data = c::db()->get( $query );

		$community = Crunchbutton_Community::o( $_REQUEST[ 'community' ] );

		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );

		$parsedData = [];
		// Covert the hour to communitie's timezone
		foreach( $data as $hour ){
			$fake = $now->format( 'Y-m-d' ) . ' ' . ( $hour->Day > 9 ? $hour->Day : '0' . $hour->Day ) . ':00:00';
			$date = new DateTime( $fake, new DateTimeZone( c::config()->timezone ) );
			$date->setTimezone( new DateTimeZone( $community->timezone ) );
			$_hour = $date->format( 'H' ) . 'h (' . $date->format( 'T' ) . ')' ;
			$parsedData[] = (object) [ 'Community' => $hour->Community, 'Orders' => $hour->Orders, 'Day' => $_hour ];
		}

		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit, 'hideSlider' => true, 'hideGroups' => true );
		}
		return $data;
	}

	public function perWeekdayByCommunity( $render = false ){

		$community = ( $_REQUEST[ 'community' ] ? 'AND o.id_community = ' . $_REQUEST[ 'community' ] : '' );

		$query = "SELECT
								c.name AS 'Community',
								count(1) AS 'Orders',
								DAYNAME( o.date ) AS 'Day'
							FROM `order` o
								INNER JOIN community c ON c.id_community = o.id_community
								WHERE 1=1 {$community}
							GROUP BY WEEKDAY( o.date )";

		$data = c::db()->get( $query );
		if( $render ){
			return array( 'data' => $data, 'unit' => $this->unit, 'hideSlider' => true, 'hideGroups' => true );
		}
		return $data;
	}

	public function perRestaurantPerCommunity( $render = false ){

		$query = "SELECT r.name AS Restaurant,
										 orders.orders AS Total,
										 c.name AS 'Group'
							FROM
								(SELECT count(*) AS orders,
												o.id_restaurant
								 FROM `order` o
								 WHERE o.date BETWEEN CURDATE() - INTERVAL 1400 DAY AND CURDATE()
								 GROUP BY o.id_restaurant) orders
							INNER JOIN restaurant r ON r.id_restaurant = orders.id_restaurant
							INNER JOIN restaurant_community rc ON r.id_restaurant = rc.id_restaurant
							INNER JOIN community c ON c.id_community = rc.id_community AND c.name NOT LIKE 'test%'";
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
							INNER JOIN `user` u ON u.id_user = o.id_user
							LEFT JOIN restaurant_community rc ON o.id_restaurant = rc.id_restaurant
							LEFT JOIN community c ON rc.id_community = c.id_community {$this->queryExcludeCommunties}
							WHERE o.date >= '{$this->dayFrom}' AND o.date <= '{$this->dayTo}'
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
										 c.name AS 'Group'
							FROM `order` o
								INNER JOIN `user` u ON u.id_user = o.id_user
								LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant
								INNER JOIN restaurant_community rc ON r.id_restaurant = rc.id_restaurant
								INNER JOIN community c ON c.id_community = rc.id_community AND c.name NOT LIKE 'test%'
							WHERE 1 = 1
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
							INNER JOIN `user` u ON u.id_user = o.id_user
							LEFT JOIN restaurant_community rc ON o.id_restaurant = rc.id_restaurant
							LEFT JOIN community c ON rc.id_community = c.id_community {$this->queryExcludeCommunties}
							WHERE o.date >= '{$this->monthFrom}-01' AND o.date <= LAST_DAY( STR_TO_DATE( '{$this->monthTo}', '%Y-%m' ) )
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
										 c.name AS 'Group'
							FROM `order` o
							INNER JOIN `user` u ON u.id_user = o.id_user
							LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant
							INNER JOIN restaurant_community rc ON r.id_restaurant = rc.id_restaurant
							INNER JOIN community c ON c.id_community = rc.id_community AND c.name NOT LIKE 'test%'
							WHERE 1 = 1
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
					INNER JOIN `user` u ON u.id_user = o.id_user
					LEFT JOIN restaurant_community rc ON o.id_restaurant = rc.id_restaurant
					LEFT JOIN community c ON rc.id_community = c.id_community {$this->queryExcludeCommunties}
					WHERE YEARWEEK(o.date) >= {$this->weekFrom} AND YEARWEEK(o.date) <= {$this->weekTo}
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
										 c.name AS 'Group'
							FROM `order` o
							INNER JOIN `user` u ON u.id_user = o.id_user
							LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant
							INNER JOIN restaurant_community rc ON r.id_restaurant = rc.id_restaurant
							INNER JOIN community c ON c.id_community = rc.id_community AND c.name NOT LIKE 'test%'
							WHERE 1 = 1
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

	public function repeatPerDayByRestaurant( $render = false ){

		$user = new Crunchbutton_Chart_User();
		$newUsers = $user->newByDayByRestaurant();
		$orders = $this->byDayPerRestaurant();

		$data = [];
		$communities = [];
		$days = [];

		foreach ( $orders as $order ) {
			if( !$days[ $order->Label ] ){
				$days[ $order->Label ] = [];
			}
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
			$data[] = ( object ) array( 'Label' => $label, 'Total' => $repeat, 'Type' => 'Orders' );
		}

		if( $render ){
			return array( 'data' => $data, 'unit' => $this->unit, 'interval' => 'day' );
		}
		return $data;
	}

	public function repeatPerDayByCommunity( $render = false ){

		$user = new Crunchbutton_Chart_User();
		$newUsers = $user->newByDayByCommunity();
		$orders = $this->byDayPerCommunity();

		$data = [];
		$communities = [];
		$days = [];

		foreach ( $orders as $order ) {
			if( !$days[ $order->Label ] ){
				$days[ $order->Label ] = [];
			}
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
			$data[] = ( object ) array( 'Label' => $label, 'Total' => $repeat, 'Type' => 'Orders' );
		}

		if( $render ){
			return array( 'data' => $data, 'unit' => $this->unit, 'interval' => 'day' );
		}
		return $data;
	}

	public function repeatPerDayByCommunityGrouped( $render = false ){

		$user = new Crunchbutton_Chart_User();
		$newUsers = $user->newByDayByCommunityGrouped();
		$orders = $this->byDayPerCommunity();

		$data = [];
		$communities = [];
		$days = [];

		foreach ( $orders as $order ) {
			if( !$days[ $order->Label ] ){
				$days[ $order->Label ] = [];
			}
			$days[ $order->Label ][ 'Order' ][ $order->Type ] = $order->Total;
			$communities[ $order->Type ] = true;
		}

		foreach ( $newUsers as $new ) {
			if( !$days[ $new->Label ] ){
				$days[ $new->Label ] = [];
			}
			$days[ $new->Label ][ 'New' ][ $new->Type ] = $new->Total;
			$communities[ $new->Type ] = true;
		}

		$data = [];

		foreach ( $days as $label => $values ) {
			foreach( $communities as $community => $val ){
				$new = $values[ 'New' ][ $community ];
				$repeat = $values[ 'Order' ][ $community ] - $new;
				$data[] = ( object ) array( 'Label' => $label, 'Total' => $repeat, 'Type' => $community );
			}
		}

		if( $render ){
			return array( 'data' => $data, 'unit' => $this->unit, 'interval' => 'day' );
		}
		return $data;
	}

	public function repeatVsNewPerDayPerCommunity( $render = false ){

		$user = new Crunchbutton_Chart_User();
		$newUsers = $user->newByDayByCommunityGrouped();
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
		$newUsers = $user->newByMonthByCommunityGrouped();
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
		$newUsers = $user->newByWeekByCommunityGrouped();
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

	public function repeatPerWeekByRestaurant( $render = false ){
		$user = new Crunchbutton_Chart_User();
		$newUsers = $user->newByWeekByRestaurant();
		$orders = $this->byWeekPerRestaurant();

		$communities = [];
		$data = [];
		$weeks = [];

		foreach ( $orders as $order ) {
			if( !$weeks[ $order->Label ] ){
				$weeks[ $order->Label ] = [];
			}
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
			$data[] = ( object ) array( 'Label' => $label, 'Total' => $repeat, 'Type' => 'Orders' );
		}

		if( $render ){
			return array( 'data' => $data, 'unit' => $this->unit );
		}
		return $data;
	}


	public function repeatPerWeekByCommunity( $render = false ){
		$user = new Crunchbutton_Chart_User();
		$newUsers = $user->newByWeekByCommunity();
		$orders = $this->byWeekPerCommunity();

		$communities = [];
		$data = [];
		$weeks = [];

		foreach ( $orders as $order ) {
			if( !$weeks[ $order->Label ] ){
				$weeks[ $order->Label ] = [];
			}
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
			$data[] = ( object ) array( 'Label' => $label, 'Total' => $repeat, 'Type' => 'Orders' );
		}

		if( $render ){
			return array( 'data' => $data, 'unit' => $this->unit );
		}
		return $data;
	}

	public function repeatPerWeekByCommunityGrouped( $render = false ){
		$user = new Crunchbutton_Chart_User();
		$newUsers = $user->newByWeekByCommunityGrouped();
		$orders = $this->byWeekPerCommunity();

		$communities = [];
		$data = [];
		$weeks = [];

		foreach ( $orders as $order ) {
			if( !$weeks[ $order->Label ] ){
				$weeks[ $order->Label ] = [];
			}
			$weeks[ $order->Label ][ 'Order' ][ $order->Type ] = $order->Total;
			$communities[ $order->Type ] = true;
		}

		foreach ( $newUsers as $new ) {
			if( !$weeks[ $new->Label ] ){
				$weeks[ $new->Label ] = [];
			}
			$weeks[ $new->Label ][ 'New' ][ $new->Type ] = $new->Total;
			$communities[ $new->Type ] = true;
		}

		$data = [];

		foreach ( $weeks as $label => $values ) {
			foreach( $communities as $community => $val ){
				$new = $values[ 'New' ][ $community ];
				$repeat = $values[ 'Order' ][ $community ] - $new;
				$data[] = ( object ) array( 'Label' => $label, 'Total' => $repeat, 'Type' => $community );
			}
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

	public function repeatPerMonthByRestaurant( $render = false ){

		$user = new Crunchbutton_Chart_User();
		$newUsers = $user->newByMonthByRestaurant();
		$orders = $this->byMonthPerRestaurant();

		$data = [];
		$communities = [];
		$months = [];

		foreach ( $orders as $order ) {
			if( !$months[ $order->Label ] ){
				$months[ $order->Label ] = [];
			}
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
			$data[] = ( object ) array( 'Label' => $label, 'Total' => $repeat, 'Type' => 'Orders' );
		}

		if( $render ){
			return array( 'data' => $data, 'unit' => $this->unit, 'interval' => 'month' );
		}
		return $data;
	}

	public function repeatPerMonthByCommunity( $render = false ){

		$user = new Crunchbutton_Chart_User();
		$newUsers = $user->newByMonthByCommunity();
		$orders = $this->byMonthPerCommunity();

		$data = [];
		$communities = [];
		$months = [];

		foreach ( $orders as $order ) {
			if( !$months[ $order->Label ] ){
				$months[ $order->Label ] = [];
			}
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
			$data[] = ( object ) array( 'Label' => $label, 'Total' => $repeat, 'Type' => 'Orders' );
		}

		if( $render ){
			return array( 'data' => $data, 'unit' => $this->unit, 'interval' => 'month' );
		}
		return $data;
	}

	public function repeatPerMonthByCommunityGrouped( $render = false ){

		$user = new Crunchbutton_Chart_User();
		$newUsers = $user->newByMonthByCommunityGrouped();
		$orders = $this->byMonthPerCommunity();

		$data = [];
		$communities = [];
		$months = [];

		foreach ( $orders as $order ) {
			if( !$months[ $order->Label ] ){
				$months[ $order->Label ] = [];
			}
			$months[ $order->Label ][ 'Order' ][ $order->Type ] = $order->Total;
			$communities[ $order->Type ] = true;
		}

		foreach ( $newUsers as $new ) {
			if( !$months[ $new->Label ] ){
				$months[ $new->Label ] = [];
			}
			$months[ $new->Label ][ 'New' ][ $new->Type ] = $new->Total;
			$communities[ $new->Type ] = true;
		}

		$data = [];

		foreach ( $months as $label => $values ) {
			foreach( $communities as $community => $val ){
				$new = $values[ 'New' ][ $community ];
				$repeat = $values[ 'Order' ][ $community ] - $new;
				$data[] = ( object ) array( 'Label' => $label, 'Total' => $repeat, 'Type' => $community );
			}
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

	public function repeatByActiveuserByMonthByCommunity( $render = false ){

		$user = new Crunchbutton_Chart_User();

		$activeUsers = $user->activeByMonthByCommunity();
		$newUsers = $user->newByMonthByCommunityGrouped();
		$orders = $this->byMonthPerCommunity();

		$communities = $this->allCommunities();

		$_data = [];

		$_prev = [];

		foreach ( $activeUsers as $active ) {
			if( !$_data[ $active->Label ] ){
				$_data[ $active->Label ] = [];
			}
			$_data[ $active->Label ][ 'ActiveUser' ][ $active->Type ] = $active->Total;
			$_data[ $active->Label ][ 'ActiveUserPrev' ][ $active->Type ] = ( $_prev[ $active->Type ] ? $_prev[ $active->Type ] : 0 );
			$_prev[ $active->Type ] = $active->Total;
		}

		foreach ( $newUsers as $newUser ) {
			if( !$_data[ $newUser->Label ] ){
				$_data[ $newUser->Label ] = [];
			}
			$_data[ $newUser->Label ][ 'NewUser' ][ $newUser->Type ] = $newUser->Total;
		}

		foreach ( $orders as $order ) {
			if( !$_data[ $order->Label ] ){
				$_data[ $order->Label ] = [];
			}
			$_data[ $order->Label ][ 'Order' ][ $order->Type ] = $order->Total;
		}

		$data = [];

		foreach ( $_data as $label => $values ) {
			foreach( $communities as $community ){
				$new = $values[ 'NewUser' ][ $community ];
				$active = $values[ 'ActiveUser' ][ $community ];
				$prev = $values[ 'ActiveUserPrev' ][ $community ];
				$order = $values[ 'Order' ][ $community ];

				// Formula (Orders minus New Users) / (Active Users) | Active Users = ( average of the current week and previous week's Active Users )
				if( $active || $prev ){
					$activeUsersAvg = ( $active + $prev ) / 2;
				} else {
					$activeUsersAvg = 0;
				}

				$ordersMinusNewUsers = $order - $new;

				if( $ordersMinusNewUsers != 0 && $activeUsersAvg != 0 ){
					$result = ( $order - $new ) / ( $activeUsersAvg );
				} else {
					$result = 0;
				}
				if( $result < 0 ){
					$result = 0;
				}
				$data[] = ( object ) array( 'Label' => $label, 'Total' => $result, 'Type' => $community );
			}
		}

		if( $render ){
			return array( 'data' => $data, 'unit' => $this->unit, 'interval' => 'month' );
		}
		return $data;
	}

	public function repeatByActiveuserByWeekByCommunity( $render = false ){

		$user = new Crunchbutton_Chart_User();

		$activeUsers = $user->activeByWeekByCommunity();
		$newUsers = $user->newByWeekByCommunityGrouped();
		$orders = $this->byWeekPerCommunity();

		$communities = $this->allCommunities();

		$_data = [];

		$_prev = [];

		foreach ( $activeUsers as $active ) {
			if( !$_data[ $active->Label ] ){
				$_data[ $active->Label ] = [];
			}
			$_data[ $active->Label ][ 'ActiveUser' ][ $active->Type ] = $active->Total;
			$_data[ $active->Label ][ 'ActiveUserPrev' ][ $active->Type ] = ( $_prev[ $active->Type ] ? $_prev[ $active->Type ] : 0 );
			$_prev[ $active->Type ] = $active->Total;
		}

		foreach ( $newUsers as $newUser ) {
			if( !$_data[ $newUser->Label ] ){
				$_data[ $newUser->Label ] = [];
			}
			$_data[ $newUser->Label ][ 'NewUser' ][ $newUser->Type ] = $newUser->Total;
		}

		foreach ( $orders as $order ) {
			if( !$_data[ $order->Label ] ){
				$_data[ $order->Label ] = [];
			}
			$_data[ $order->Label ][ 'Order' ][ $order->Type ] = $order->Total;
		}

		$data = [];

		foreach ( $_data as $label => $values ) {
			foreach( $communities as $community ){
				$new = $values[ 'NewUser' ][ $community ];
				$active = $values[ 'ActiveUser' ][ $community ];
				$prev = $values[ 'ActiveUserPrev' ][ $community ];
				$order = $values[ 'Order' ][ $community ];

				// Formula (Orders minus New Users) / (Active Users) | Active Users = ( average of the current week and previous week's Active Users )
				if( $active || $prev ){
					$activeUsersAvg = ( $active + $prev ) / 2;
				} else {
					$activeUsersAvg = 0;
				}

				$ordersMinusNewUsers = $order - $new;

				if( $ordersMinusNewUsers != 0 && $activeUsersAvg != 0 ){
					$result = ( $order - $new ) / ( $activeUsersAvg );
				} else {
					$result = 0;
				}
				if( $result < 0 ){
					$result = 0;
				}

				$data[] = ( object ) array( 'Label' => $label, 'Total' => $result, 'Type' => $community );
			}
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

	public function totalOrdersByRestaurant( $id_restaurant ){
		$query = "SELECT
										 COUNT(*) AS Total
							FROM `order` o
							WHERE id_restaurant = {$id_restaurant}
								{$this->queryExcludeUsers}";
		$result = c::db()->get( $query );
		return $result->_items[0]->Total;
	}

	public function totalOrdersByCommunity( $id_community ){
		$query = "SELECT
										 COUNT(*) AS Total
							FROM `order` o
							INNER JOIN `user` u ON u.id_user = o.id_user
							LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant
							INNER JOIN restaurant_community rc ON r.id_restaurant = rc.id_restaurant
							WHERE rc.id_community = '{$id_community}'
								{$this->queryExcludeUsers}";
		$result = c::db()->get( $query );
		return $result->_items[0]->Total;
	}

	public function totalOrdersAll(){
		$query = "SELECT
										 COUNT(*) AS Total
							FROM `order` o
							INNER JOIN `user` u ON u.id_user = o.id_user
							LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant
							WHERE 1=1
								{$this->queryExcludeUsers}";
		$result = c::db()->get( $query );
		return $result->_items[0]->Total;
	}
}