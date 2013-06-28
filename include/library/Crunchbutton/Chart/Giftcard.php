<?php 

class Crunchbutton_Chart_Giftcard extends Crunchbutton_Chart {
	
	public $unit = 'gift cards';
	public $description = 'Gift cards';

	public function __construct() {
		parent::__construct();
	}

	public function createdByDay( $render = false ){
		$query = "SELECT 
								DATE_FORMAT( p.date ,'%Y-%m-%d' ) AS Day, 
								COUNT(*) AS Total
								FROM promo p
								WHERE DATE_FORMAT( p.date ,'%Y-%m-%d' )
							GROUP BY DATE_FORMAT( p.date ,'%Y-%m-%d' ) HAVING Day BETWEEN '{$this->dayFrom}' AND '{$this->dayTo}'";
		$parsedData = $this->parseDataDaysSimple( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unity, 'interval' => 'day' );
		}
		return $parsedData;
	}

	public function redeemedByDay( $render = false ){
		$query = "SELECT 
									DATE_FORMAT(c.date ,'%Y-%m-%d') AS Day,
									COUNT(*) AS Total
						FROM credit c
						WHERE id_promo
						GROUP BY DATE_FORMAT(c.date ,'%Y-%m-%d') HAVING Day BETWEEN '{$this->dayFrom}' AND '{$this->dayTo}'";
		$parsedData = $this->parseDataDaysSimple( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unity, 'interval' => 'day' );
		}
		return $parsedData;
	}

	public function redeemedPerGroupByDay( $render = false ){
		$query = "SELECT DATE_FORMAT(c.date ,'%Y-%m-%d') AS Day,
										 COUNT(*) AS Total,
										 promos.Name AS 'Group' 
							FROM credit c
							INNER JOIN ( SELECT pgp.id_promo, pg.name FROM promo_group pg
							INNER JOIN promo_group_promo pgp ON pgp.id_promo_group = pg.id_promo_group ) promos ON promos.id_promo = c.id_promo
							GROUP BY promos.Name, DATE_FORMAT(c.date ,'%Y-%m-%d') HAVING Day BETWEEN '{$this->dayFrom}' AND '{$this->dayTo}'";
		$parsedData = $this->parseDataDaysGroup( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unity, 'interval' => 'day' );
		}
		return $parsedData;
	}

	public function createdByWeek( $render = false ){
		$query = "SELECT 
								YEARWEEK(p.date) AS Week,
								COUNT(*) AS Total
								FROM promo p
								WHERE YEARWEEK(p.date)
							GROUP BY YEARWEEK(p.date) HAVING Week BETWEEN '{$this->weekFrom}' AND '{$this->weekTo}'";

		$parsedData = $this->parseDataWeeksSimple( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unity );
		}
		return $parsedData;
	}

	public function redeemedByWeek( $render = false ){
		$query = "SELECT 
									YEARWEEK(c.date) AS Week,
									COUNT(*) AS Total
						FROM credit c
						WHERE YEARWEEK(c.date)
						GROUP BY YEARWEEK(c.date) HAVING Week BETWEEN '{$this->weekFrom}' AND '{$this->weekTo}'";

		$parsedData = $this->parseDataWeeksSimple( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unity );
		}
		return $parsedData;
	}

	public function redeemedPerGroupByWeek( $render = false ){
		$query = "SELECT YEARWEEK(c.date) AS Week,
										 COUNT(*) AS Total,
										 promos.Name AS 'Group' 
							FROM credit c
							INNER JOIN ( SELECT pgp.id_promo, pg.name FROM promo_group pg
							INNER JOIN promo_group_promo pgp ON pgp.id_promo_group = pg.id_promo_group ) promos ON promos.id_promo = c.id_promo
							GROUP BY promos.Name, YEARWEEK(c.date) HAVING Week BETWEEN '{$this->weekFrom}' AND '{$this->weekTo}'";

		$parsedData = $this->parseDataWeeksGroup( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unity );
		}
		return $parsedData;
	}

	public function createdByMonth( $render = false ){
		$query = "SELECT 
								DATE_FORMAT( p.date ,'%Y-%m' ) AS Month, 
								COUNT(*) AS Total
								FROM promo p
								WHERE DATE_FORMAT( p.date ,'%Y-%m' )
							GROUP BY DATE_FORMAT( p.date ,'%Y-%m' ) HAVING Month BETWEEN '{$this->monthFrom}' AND '{$this->monthTo}'";

		$parsedData = $this->parseDataMonthSimple( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unity, 'interval' => 'month' );
		}
		return $parsedData;
	}

	public function redeemedByMonth( $render = false ){
		$query = "SELECT 
									DATE_FORMAT(c.date ,'%Y-%m') AS Month,
									COUNT(*) AS Total
						FROM credit c
						WHERE id_promo
						GROUP BY DATE_FORMAT( c.date ,'%Y-%m' ) HAVING Month BETWEEN '{$this->monthFrom}' AND '{$this->monthTo}'";
		
		$parsedData = $this->parseDataMonthSimple( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unity, 'interval' => 'month' );
		}
		return $parsedData;
	}

	public function redeemedPerGroupByMonth( $render = false ){
		$query = "SELECT DATE_FORMAT(c.date ,'%Y-%m') AS Month,
										 COUNT(*) AS Total,
										 promos.Name AS 'Group' 
							FROM credit c
							INNER JOIN ( SELECT pgp.id_promo, pg.name FROM promo_group pg
							INNER JOIN promo_group_promo pgp ON pgp.id_promo_group = pg.id_promo_group ) promos ON promos.id_promo = c.id_promo
							GROUP BY promos.Name, DATE_FORMAT(c.date ,'%Y-%m') HAVING Month BETWEEN '{$this->monthFrom}' AND '{$this->monthTo}'";

		$parsedData = $this->parseDataMonthGroup( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unity, 'interval' => 'month' );
		}
		return $parsedData;
	}

}