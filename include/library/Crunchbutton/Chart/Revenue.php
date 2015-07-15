<?php 
class Crunchbutton_Chart_Revenue extends Crunchbutton_Chart {
	
	public $unit = 'US$';
	public $description = 'US$';
	
	public $groups = array( 
												'group-revenue' => array(
														'title' => 'Gross Revenue',
														'tags' => array( 'investors' ),
														'charts' => array(  
																'gross-revenue-per-day' => array( 'title' => 'Day', 'interval' => 'day', 'type' => 'column', 'method' => 'byDay', 'filters' => array( array( 'title' => 'Community', 'type' => 'community', 'method' => 'byDayByCommunity' ) ) ),
																'gross-revenue-per-week' => array( 'title' => 'Week', 'interval' => 'week', 'type' => 'column', 'method' => 'byWeek', 'filters' => array( array( 'title' => 'Community', 'type' => 'community', 'method' => 'byWeekByCommunity' ) ) ),
																'gross-revenue-per-month' => array( 'title' => 'Month', 'interval' => 'month', 'type' => 'column', 'method' => 'byMonth', 'filters' => array( array( 'title' => 'Community', 'type' => 'community', 'method' => 'byMonthByCommunity' ) ) ),
															)
												),
												'group-revenue-by-community' => array(
														'title' => 'Gross Revenue',
														'tags' => array( 'reps' ),
														'charts' => array(  
																'gross-revenue-per-day-by-community' => array( 'title' => 'Day', 'interval' => 'day', 'type' => 'column-community', 'method' => 'byDayByCommunity' ),
																'gross-revenue-per-week-by-community' => array( 'title' => 'Week', 'interval' => 'week', 'type' => 'column-community', 'method' => 'byWeekByCommunity' ),
																'gross-revenue-per-month-by-community' => array( 'title' => 'Month', 'interval' => 'month', 'type' => 'column-community', 'method' => 'byMonthByCommunity' ),
															),
												),
												'group-revenue-by-restaurant' => array(
														'title' => 'Gross Revenue',
														'tags' => array( 'reps' ),
														'charts' => array(  
																'gross-revenue-per-day-by-restaurant' => array( 'title' => 'Day', 'interval' => 'day', 'type' => 'column-restaurant', 'method' => 'byDayByRestaurant' ),
																'gross-revenue-per-week-by-restaurant' => array( 'title' => 'Week', 'interval' => 'week', 'type' => 'column-restaurant', 'method' => 'byWeekByRestaurant' ),
																'gross-revenue-per-month-by-restaurant' => array( 'title' => 'Month', 'interval' => 'month', 'type' => 'column-restaurant', 'method' => 'byMonthByRestaurant' ),
															),
												),
										);

	public function __construct() {
		parent::__construct();
	}

	public function byDay( $render = false ){

		$query = "SELECT DATE_FORMAT( o.date ,'%Y-%m-%d') AS Day,
											CAST(SUM(final_price) AS DECIMAL(14, 2)) AS 'Total'
							FROM `order` o
							LEFT JOIN restaurant_community rc ON o.id_restaurant = rc.id_restaurant 
LEFT JOIN community c ON rc.id_community = c.id_community {$this->queryExcludeCommunties}
							WHERE o.date >= '{$this->dayFrom}' AND o.date <= '{$this->dayTo}'
								{$this->queryExcludeUsers}
							GROUP BY Day
							ORDER BY Day DESC";

		$parsedData = $this->parseDataDaysSimple( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit, 'interval' => 'day' );
		}
		return $parsedData;
	}

	public function byDayByRestaurant( $render = false ){

		$restaurant = ( $_REQUEST[ 'restaurant' ] ) ? $_REQUEST[ 'restaurant' ] : false;

		$query = "SELECT DATE_FORMAT( o.date ,'%Y-%m-%d') AS Day,
											CAST(SUM(final_price) AS DECIMAL(14, 2)) AS 'Total',
											r.name AS `Group`
							FROM `order` o
						LEFT JOIN user u ON u.id_user = o.id_user
						LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant 
						WHERE o.date >= '{$this->dayFrom}' AND o.date <= '{$this->dayTo}'
							AND r.id_restaurant = '{$restaurant}'
							{$this->queryExcludeUsers}
						GROUP BY DATE_FORMAT( o.date ,'%Y-%m-%d'),
										 r.name
						ORDER BY DATE_FORMAT( o.date ,'%Y-%m-%d') DESC";

		$parsedData = $this->parseDataDaysSimple( $query, $this->description );
		
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit, 'interval' => 'day' );
		}
		return $parsedData;	
	}

	public function byDayByCommunity( $render = false ){

		$community = ( $_REQUEST[ 'community' ] ) ? $_REQUEST[ 'community' ] : false;

		if( $community ){
			$query = "SELECT DATE_FORMAT( o.date ,'%Y-%m-%d') AS Day,
												CAST(SUM(final_price) AS DECIMAL(14, 2)) AS 'Total',
												c.name AS `Group`
								FROM `order` o
							LEFT JOIN user u ON u.id_user = o.id_user
							LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant 
							INNER JOIN restaurant_community rc ON r.id_restaurant = rc.id_restaurant
							INNER JOIN community c ON c.id_community = rc.id_community AND c.name NOT LIKE 'test%'
							WHERE o.date >= '{$this->dayFrom}' AND o.date <= '{$this->dayTo}'
								AND c.id_community = '{$community}'
								{$this->queryExcludeUsers}
							GROUP BY DATE_FORMAT( o.date ,'%Y-%m-%d'),
											 c.name
							ORDER BY DATE_FORMAT( o.date ,'%Y-%m-%d') DESC";

			$parsedData = $this->parseDataDaysSimple( $query, $this->description );
		} else {
			$query = "SELECT DATE_FORMAT( o.date ,'%Y-%m-%d') AS Day,
												CAST(SUM(final_price) AS DECIMAL(14, 2)) AS 'Total',
												c.name  AS `Group`
								FROM `order` o
							LEFT JOIN user u ON u.id_user = o.id_user
							LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant 
							INNER JOIN restaurant_community rc ON r.id_restaurant = rc.id_restaurant
							INNER JOIN community c ON c.id_community = rc.id_community AND c.name NOT LIKE 'test%'
							WHERE o.date >= '{$this->dayFrom}' AND o.date <= '{$this->dayTo}'
								{$this->queryExcludeUsers}
							GROUP BY DATE_FORMAT( o.date ,'%Y-%m-%d'),
											 c.name
							ORDER BY DATE_FORMAT( o.date ,'%Y-%m-%d') DESC";

			$parsedData = $this->parseDataDaysGroup( $query, $this->description );
		}

		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit, 'interval' => 'day' );
		}
		return $parsedData;	
	}

	public function byMonth( $render = false ){

		$query = "SELECT DATE_FORMAT( o.date ,'%Y-%m') AS Month,
											CAST(SUM(final_price) AS DECIMAL(14, 2)) AS 'Total'
							FROM `order` o
							LEFT JOIN restaurant_community rc ON o.id_restaurant = rc.id_restaurant 
LEFT JOIN community c ON rc.id_community = c.id_community {$this->queryExcludeCommunties}
							WHERE o.date >= '{$this->monthFrom}-01' AND o.date <= LAST_DAY( STR_TO_DATE( '{$this->monthTo}', '%Y-%m' ) )
								{$this->queryExcludeUsers}
							GROUP BY Month
							ORDER BY Month DESC";

		$parsedData = $this->parseDataMonthSimple( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit, 'interval' => 'month' );
		}
		return $parsedData;
	}

	public function byMonthByRestaurant( $render = false ){

		$restaurant = ( $_REQUEST[ 'restaurant' ] ) ? $_REQUEST[ 'restaurant' ] : false;

		$query = "SELECT DATE_FORMAT( o.date ,'%Y-%m') AS Month,
											CAST(SUM(final_price) AS DECIMAL(14, 2)) AS 'Total',
											r.name AS `Group`
							FROM `order` o
						LEFT JOIN user u ON u.id_user = o.id_user
						LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant 
						WHERE o.date >= '{$this->monthFrom}-01' AND o.date <= LAST_DAY( STR_TO_DATE( '{$this->monthTo}', '%Y-%m' ) )
							AND r.id_restaurant = '{$restaurant}'
							{$this->queryExcludeUsers}
						GROUP BY DATE_FORMAT( o.date ,'%Y-%m'),
										 r.name
						ORDER BY DATE_FORMAT( o.date ,'%Y-%m') DESC";

		$parsedData = $this->parseDataMonthSimple( $query, $this->description );

		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit, 'interval' => 'month' );
		}
		return $parsedData;	
	}

	public function byMonthByCommunity( $render = false ){

		$community = ( $_REQUEST[ 'community' ] ) ? $_REQUEST[ 'community' ] : false;

		if( $community ){
			$query = "SELECT DATE_FORMAT( o.date ,'%Y-%m') AS Month,
												CAST(SUM(final_price) AS DECIMAL(14, 2)) AS 'Total',
												c.name AS `Group`
								FROM `order` o
							LEFT JOIN user u ON u.id_user = o.id_user
							LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant 
							INNER JOIN restaurant_community rc ON r.id_restaurant = rc.id_restaurant
							INNER JOIN community c ON c.id_community = rc.id_community AND c.name NOT LIKE 'test%'
							WHERE o.date >= '{$this->monthFrom}-01' AND o.date <= LAST_DAY( STR_TO_DATE( '{$this->monthTo}', '%Y-%m' ) )
								AND c.id_community = '{$community}'
								{$this->queryExcludeUsers}
							GROUP BY DATE_FORMAT( o.date ,'%Y-%m'),
											 c.name
							ORDER BY DATE_FORMAT( o.date ,'%Y-%m') DESC";

			$parsedData = $this->parseDataMonthSimple( $query, $this->description );
		} else {
			$query = "SELECT DATE_FORMAT( o.date ,'%Y-%m') AS Month,
												CAST(SUM(final_price) AS DECIMAL(14, 2)) AS 'Total',
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
		}

		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit, 'interval' => 'month' );
		}
		return $parsedData;	
	}

	public function byWeekByRestaurant( $render = false ){

		$restaurant = ( $_REQUEST[ 'restaurant' ] ) ? $_REQUEST[ 'restaurant' ] : false;
		
		$query = "SELECT YEARWEEK(date) AS `Week`,
											CAST(SUM(final_price) AS DECIMAL(14, 2)) AS 'Total',
											r.name AS `Group`
							FROM `order` o
						LEFT JOIN user u ON u.id_user = o.id_user
						LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant 
						WHERE YEARWEEK(o.date) >= {$this->weekFrom} AND YEARWEEK(o.date) <= {$this->weekTo}
							AND r.id_restaurant = '{$restaurant}'
							{$this->queryExcludeUsers}
						GROUP BY YEARWEEK(date),
										 r.name
						ORDER BY YEARWEEK(date) DESC";

		$parsedData = $this->parseDataWeeksSimple( $query, $this->description );

		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit );
		}
		return $parsedData;	
	}

	public function byWeekByCommunity( $render = false ){

		$community = ( $_REQUEST[ 'community' ] ) ? $_REQUEST[ 'community' ] : false;

		if( $community ){
			$query = "SELECT YEARWEEK(date) AS `Week`,
												CAST(SUM(final_price) AS DECIMAL(14, 2)) AS 'Total',
												c.name AS `Group`
								FROM `order` o
							LEFT JOIN user u ON u.id_user = o.id_user
							LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant 
							INNER JOIN restaurant_community rc ON r.id_restaurant = rc.id_restaurant
							INNER JOIN community c ON c.id_community = rc.id_community AND c.name NOT LIKE 'test%'
							WHERE YEARWEEK(o.date) >= {$this->weekFrom} AND YEARWEEK(o.date) <= {$this->weekTo}
								AND c.id_community = '{$community}'
								{$this->queryExcludeUsers}
							GROUP BY YEARWEEK(date),
											 c.name
							ORDER BY YEARWEEK(date) DESC";

			$parsedData = $this->parseDataWeeksSimple( $query, $this->description );
		} else {
			$query = "SELECT YEARWEEK(date) AS `Week`,
												CAST(SUM(final_price) AS DECIMAL(14, 2)) AS 'Total',
												c.name AS `Group`
								FROM `order` o
								LEFT JOIN user u ON u.id_user = o.id_user
								LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant 
								INNER JOIN restaurant_community rc ON r.id_restaurant = rc.id_restaurant
								INNER JOIN community c ON c.id_community = rc.id_community AND c.name NOT LIKE 'test%'
							WHERE YEARWEEK(o.date) >= {$this->weekFrom} AND YEARWEEK(o.date) <= {$this->weekTo}
								{$this->queryExcludeUsers}
							GROUP BY YEARWEEK(date),
											 c.name
							ORDER BY YEARWEEK(date) DESC";

			$parsedData = $this->parseDataWeeksGroup( $query, $this->description );
		}
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit );
		}
		return $parsedData;	
	}

	public function byWeek( $render = false ){
		$query = "SELECT YEARWEEK(date) AS `Week`,
											CAST(SUM(final_price) AS DECIMAL(14, 2)) AS 'Total'
							FROM `order` o
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

}