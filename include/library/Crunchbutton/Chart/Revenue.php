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
										);

	public function __construct() {
		parent::__construct();
	}

	public function byDay( $render = false ){

		$query = "SELECT DATE_FORMAT( o.date ,'%Y-%m-%d') AS Day,
											CAST(SUM(final_price) AS DECIMAL(14, 2)) AS 'Total'
							FROM `order` o
							LEFT JOIN community c ON o.id_community = c.id_community
							WHERE o.date >= '{$this->dayFrom}' AND o.date <= '{$this->dayTo}'
								{$this->queryExcludeCommunties}
								{$this->queryExcludeUsers}
							GROUP BY Day
							ORDER BY Day DESC";

		$parsedData = $this->parseDataDaysSimple( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit, 'interval' => 'day' );
		}
		return $parsedData;
	}

	public function byDayByCommunity( $render = false ){

		$query = "SELECT DATE_FORMAT( o.date ,'%Y-%m-%d') AS Day,
											CAST(SUM(final_price) AS DECIMAL(14, 2)) AS 'Total',
											r.community AS `Group`
							FROM `order` o
						LEFT JOIN user u ON u.id_user = o.id_user
						LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant 
						WHERE o.date >= '{$this->dayFrom}' AND o.date <= '{$this->dayTo}'
							AND r.community IS NOT NULL
							{$this->queryExcludeUsers}
						GROUP BY DATE_FORMAT( o.date ,'%Y-%m-%d'),
										 r.community
						ORDER BY DATE_FORMAT( o.date ,'%Y-%m-%d') DESC";

		$parsedData = $this->parseDataDaysGroup( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit, 'interval' => 'day' );
		}
		return $parsedData;	
	}

	public function byMonth( $render = false ){

		$query = "SELECT DATE_FORMAT( o.date ,'%Y-%m') AS Month,
											CAST(SUM(final_price) AS DECIMAL(14, 2)) AS 'Total'
							FROM `order` o
							LEFT JOIN community c ON o.id_community = c.id_community
							WHERE o.date >= '{$this->monthFrom}-01' AND o.date <= LAST_DAY( STR_TO_DATE( '{$this->monthTo}', '%Y-%m' ) )
								{$this->queryExcludeCommunties}
								{$this->queryExcludeUsers}
							GROUP BY Month
							ORDER BY Month DESC";

		$parsedData = $this->parseDataMonthSimple( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit, 'interval' => 'month' );
		}
		return $parsedData;
	}

	public function byMonthByCommunity( $render = false ){

		$query = "SELECT DATE_FORMAT( o.date ,'%Y-%m') AS Month,
											CAST(SUM(final_price) AS DECIMAL(14, 2)) AS 'Total',
											r.community AS `Group`
							FROM `order` o
						LEFT JOIN user u ON u.id_user = o.id_user
						LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant 
						WHERE o.date >= '{$this->monthFrom}-01' AND o.date <= LAST_DAY( STR_TO_DATE( '{$this->monthTo}', '%Y-%m' ) )
							AND r.community IS NOT NULL
							{$this->queryExcludeUsers}
						GROUP BY DATE_FORMAT( o.date ,'%Y-%m'),
										 r.community
						ORDER BY DATE_FORMAT( o.date ,'%Y-%m') DESC";

		$parsedData = $this->parseDataMonthGroup( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit, 'interval' => 'month' );
		}
		return $parsedData;	
	}

	public function byWeekByCommunity( $render = false ){

		$query = "SELECT YEARWEEK(date) AS `Week`,
											CAST(SUM(final_price) AS DECIMAL(14, 2)) AS 'Total',
											r.community AS `Group`
							FROM `order` o
						LEFT JOIN user u ON u.id_user = o.id_user
						LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant 
						WHERE YEARWEEK(o.date) >= {$this->weekFrom} AND YEARWEEK(o.date) <= {$this->weekTo}
							AND r.community IS NOT NULL
							{$this->queryExcludeUsers}
						GROUP BY YEARWEEK(date),
										 r.community
						ORDER BY YEARWEEK(date) DESC";

		$parsedData = $this->parseDataWeeksGroup( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit );
		}
		return $parsedData;	
	}

	public function byWeek( $render = false ){
		$query = "SELECT YEARWEEK(date) AS `Week`,
											CAST(SUM(final_price) AS DECIMAL(14, 2)) AS 'Total'
							FROM `order` o
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

}