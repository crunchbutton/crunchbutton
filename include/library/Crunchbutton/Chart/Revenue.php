<?php 
class Crunchbutton_Chart_Revenue extends Crunchbutton_Chart {
	
	public $unit = 'US$';
	public $description = 'US$';

	public function __construct() {
		parent::__construct();
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
			return array( 'data' => $parsedData, 'unit' => $this->unity, 'interval' => 'month' );
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
			return array( 'data' => $parsedData, 'unit' => $this->unity );
		}
		return $parsedData;
	}

}