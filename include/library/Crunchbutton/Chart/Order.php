<?php 
class Crunchbutton_Chart_Order extends Crunchbutton_Chart {
	
	public $unit = 'orders';
	public $description = 'Orders';

	public $group = 'group1';

	public $groups = array( 
													'group1' => array( 
																						'orders-per-day' => 'Orders per Day',
																						'orders-per-week' => 'Orders per Week',
																						'orders-per-month' => 'Orders per Month',
																						'orders-by-user-per-week' => 'Orders by User per Week',
																						'orders-by-user-per-month' => 'Orders by User per Month',
																						'orders-per-week-by-community' => 'Orders per Week by Community',
																						'orders-repeat-per-active-user' => 'Repeat Orders per Active User',
																						'orders-by-weekday-by-community' => 'Orders by Weekday by Community',
																						'orders-per-restaurant-by-community' => 'Orders per Restaurant by Community',	
																						'orders-repeat-vs-news-per-day' => 'Repeat vs. New Orders per Day',
																						'orders-repeat-vs-news-per-week' => 'Repeat vs. New Orders per Week',
																						'orders-repeat-vs-news-per-month' => 'Repeat vs. New Orders per Month',
																						'orders-repeat-vs-news-per-week-per-community' => 'Repeat vs. New Orders per Week per Community',
																			) 
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
			return array( 'data' => $data, 'unit' => $this->unity );
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
			return array( 'data' => $parsedData, 'unit' => $this->unity, 'interval' => 'month' );
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
			return array( 'data' => $parsedData, 'unit' => $this->unity );
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
			return array( 'data' => $parsedData, 'unit' => $this->unity, 'interval' => 'day' );
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
			return array( 'data' => $parsedData, 'unit' => $this->unity );
		}
		return $parsedData;
	}

	public function byDayPerCommunity( $render = false ){

		$query = "SELECT DATE_FORMAT(o.date ,'%Y-%m-%d') AS Day,
										 COUNT(*) AS Total,
										 r.community AS 'Group'
							FROM `order` o
							INNER JOIN user u ON u.id_user = o.id_user
							LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant 
							WHERE 1 = 1
								AND r.community IS NOT NULL
								{$this->queryExcludeUsers}
							GROUP BY DATE_FORMAT(o.date ,'%Y-%m-%d') HAVING Day BETWEEN '{$this->dayFrom}' AND '{$this->dayTo}'";

		$parsedData = $this->parseDataDaysGroup( $query, $this->description );
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unity );
		}
		return $parsedData;
	}

	public function perRestaurantPerCommunity( $render = false ){

		$query = "SELECT 
						     r.name AS Restaurant,
						     orders.orders AS Total,
							   r.community AS 'Group'
						FROM
						  (SELECT count(*) AS orders,
						          o.id_restaurant
						   FROM `order` o
						   WHERE o.date BETWEEN CURDATE() - INTERVAL 14 DAY AND CURDATE()
						   GROUP BY o.id_restaurant) orders
						INNER JOIN restaurant r ON r.id_restaurant = orders.id_restaurant";

		$data = c::db()->get( $query );
		$groups = [];
		foreach( $data as $item ){
			$groups[ $item->Group ][] = array( 'Restaurant' => $item->Restaurant, 'Orders' => $item->Total );
		}

		if( $render ){
			return array( 'data' => $groups, 'unit' => $this->unity );
		}
		return $data;
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
			return array( 'data' => $parsedData, 'unit' => $this->unity, 'interval' => 'month' );
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
			return array( 'data' => $parsedData, 'unit' => $this->unity );
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
			return array( 'data' => $data, 'unit' => $this->unity, 'interval' => 'day' );
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
			return array( 'data' => $data, 'unit' => $this->unity );
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
			return array( 'data' => $data, 'unit' => $this->unity );
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
			return array( 'data' => $data, 'unit' => $this->unity, 'interval' => 'month' );
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
			return array( 'data' => $data, 'unit' => $this->unity );
		}
		return $data;
	}

}