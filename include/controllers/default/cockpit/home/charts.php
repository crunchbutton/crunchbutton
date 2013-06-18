<?php

class Controller_home_charts extends Crunchbutton_Controller_Account {
	public function init() {

		$chart = c::getPagePiece(2);

		switch ( $chart ) {
			
			case 'users-per-week-by-community':
					$maxMinWeeks = $this->maxMinWeeks();
					$maxWeeks = sizeof( $maxMinWeeks ) - 1;
					$weeks = ( $_REQUEST[ 'weeks' ] ? $_REQUEST[ 'weeks' ] : $maxWeeks );
					$actual = $maxMinWeeks[ $weeks ];
					echo $actual;
					$query = 'SELECT 
											CONCAT( "Week ", YEARWEEK( date ) ) AS `week`, 
											COUNT( DISTINCT( ( u.phone ) ) ) AS Users, 
											c.name AS `Community`
										FROM `order` o 
										INNER JOIN user u ON u.id_user = o.id_user 
										LEFT JOIN community c ON o.id_community = c.id_community
										WHERE c.name IS NOT NULL  AND c.name != "Testing" AND YEARWEEK(o.date) >= ' . $actual . '
										GROUP BY YEARWEEK(o.date), o.id_community 
										ORDER BY YEARWEEK(o.date) DESC';

					c::view()->display('charts/column', ['set' => [
						'chartId' => $chart,
						'data' => c::db()->get( $query  ),
						'title' => 'Unique users per week by Community',
						'unit' => 'users',
						'maxWeeks' => $maxWeeks,
						'weeks' => $weeks,
						'tooltip' => false,
						'tooltipShared' => true
					]]); 
				break;

			case 'users-per-week':
					$maxWeeks = $this->weeks();
					$weeks = ( $_REQUEST[ 'weeks' ] ? $_REQUEST[ 'weeks' ] : $maxWeeks );
					$query = 'SELECT 
											CONCAT( "Week ", YEARWEEK( date ) ) AS `week`, 
											COUNT( DISTINCT( ( u.phone ) ) ) AS Users, 
											"Users" AS label 
										FROM `order` o 
										INNER JOIN user u ON u.id_user = o.id_user 
										GROUP BY YEARWEEK(o.date) 
										ORDER BY YEARWEEK(o.date) DESC
										LIMIT ' . $weeks ;
					c::view()->display('charts/column', ['set' => [
						'chartId' => $chart,
						'data' => c::db()->get( $query  ),
						'title' => 'Unique users per week',
						'unit' => 'users',
						'maxWeeks' => $maxWeeks,
						'weeks' => $weeks,
						'tooltip' => false
					]]); 
				break;

			case 'orders-per-week':
					$maxWeeks = $this->weeks();
					$weeks = ( $_REQUEST[ 'weeks' ] ? $_REQUEST[ 'weeks' ] : $maxWeeks );
					$query = 'SELECT 
												CONCAT( "Week ", YEARWEEK( date ) ) AS `week`, 
												COUNT(*) AS Orders, 
												"Orders" AS label 
										FROM `order` 
										GROUP BY YEARWEEK(date) 
										ORDER BY YEARWEEK(date) DESC 
										LIMIT ' . $weeks ;
					c::view()->display('charts/column', ['set' => [
						'chartId' => $chart,
						'data' => c::db()->get( $query  ),
						'title' => 'Orders per week',
						'unit' => 'orders',
						'maxWeeks' => $maxWeeks,
						'weeks' => $weeks,
						'tooltip' => false
					]]); 
				break;

			case 'gross-revenue':
					$maxWeeks = $this->weeks();
					$weeks = ( $_REQUEST[ 'weeks' ] ? $_REQUEST[ 'weeks' ] : $maxWeeks );
					$query = 'SELECT 
											CONCAT( "Week ", YEARWEEK( date ) ) AS `week`, 
											CAST( SUM( final_price ) AS DECIMAL( 14, 2 ) ) AS "US$", 
											"US$" AS label 
										FROM `order` 
										GROUP BY YEARWEEK(date) 
										ORDER BY YEARWEEK(date) DESC 
										LIMIT ' . $weeks ;
					c::view()->display('charts/column', ['set' => [
						'chartId' => $chart,
						'data' => c::db()->get( $query  ),
						'title' => 'Gross revenue',
						'unit' => '',
						'maxWeeks' => $maxWeeks,
						'weeks' => $weeks,
						'tooltip' => false
					]]); 
				break;

			case 'orders-by-date-by-community':
					$query = 'SELECT
											DATE_FORMAT( CONVERT_TZ( `date`, "-8:00", "-5:00" ), "%W" ) AS `Day`,
											COUNT(*) AS `Orders`,
											community.name AS `Community`
										FROM `order`
										LEFT JOIN community USING( id_community )
										WHERE
											env = "live"
											AND community.name IS NOT NULL
											AND community.name != "Testing"
										GROUP BY DATE_FORMAT( CONVERT_TZ( `date`, "-8:00", "-5:00" ), "%W" ), id_community
										ORDER BY DATE_FORMAT( CONVERT_TZ( `date`, "-8:00", "-5:00" ), "%Y%m%d" ), id_community';
					c::view()->display('charts/area', ['set' => [
						'chartId' => $chart,
						'data' => c::db()->get( $query  ),
						'title' => 'Orders by day by community',
						'unit' => 'orders',
						'tooltip' => false
					]]); 
		break;
			default:
				break;
		}
	}

	private function weeks(){
		$query = 'SELECT COUNT( DISTINCT( YEARWEEK( date) ) ) AS weeks FROM `order`';
		$result = c::db()->get( $query );
		return $result->_items[0]->weeks; 
	}
	private function maxMinWeeks(){
		$query = 'SELECT DISTINCT( YEARWEEK( o.date ) ) week FROM `order` o ORDER BY week DESC';
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
}

/*


/*
		// Get the last users (different phones) by date
		$days = [ 1, 7, 30 ];
		$preQuery = 'SELECT 
										"Last {days} day(s)" AS day, COUNT(*) AS Users, IF( result.total > 1, "Returned", "1st time users" ) serie 
									FROM (
													SELECT filter.phone, filter.filter, total.total
														FROM ( SELECT u.phone, COUNT(*) AS filter FROM user u INNER JOIN `order` o ON u.id_user = o.id_user AND o.date BETWEEN CURDATE() - INTERVAL {days} DAY AND CURDATE() AND o.env = "live" GROUP BY u.phone ) filter
													INNER JOIN ( SELECT u.phone, COUNT(*) AS total FROM user u INNER JOIN `order` o ON u.id_user = o.id_user AND o.env = "live" GROUP BY u.phone ) total ON filter.phone = total.phone ) result
									GROUP BY serie';
		$query = '';
		$union = '';
		foreach ( $days as $day ) {
			$query .= $union . str_replace( '{days}', $day, $preQuery );
			$union = ' UNION ';
		}
		$graphs['active-users'] = [
			'title' => 'Active Users',
			'type' => 'area',
			'unit' => 'users',
			'tooltip' => "function() {
				var total = 0;
				var body = '';
				$.each( this.points, function( i, point ) {
					body += '<br/><span style=\"color:' + point.series.color + '\">' +  point.series.name + '</span>: ' + point.y + ' users (' + point.percentage.toFixed(2) + '%)';
					total += point.y;
				});
				var html = '<b>Total: ' + total + ' users</b>' + body;
				return html;}",
			'data' => c::db()->get( $query  )
		];

		// Orders per Active User (NOT counting 1st-time orders)
		$preQuery = 'SELECT  "Last {days} day(s)" AS day, SUM( total ) as Orders, "1st time users" as serie FROM (
									SELECT 
										u.phone, 
										COUNT(*) AS total,
										allorders.allorders 
									FROM user u 
										INNER JOIN `order` o ON u.id_user = o.id_user AND o.date BETWEEN CURDATE() - INTERVAL {days} DAY AND CURDATE() AND o.env = "live"
										INNER JOIN ( SELECT u.phone, COUNT(*) AS allorders FROM user u INNER JOIN `order` o ON u.id_user = o.id_user AND o.env = "live" GROUP BY u.phone ) allorders ON allorders.phone = u.phone 
										WHERE u.phone IS NOT NULL
										GROUP BY u.phone HAVING allorders = 1 ) orders
								UNION
								SELECT  "Last {days} day(s)" AS day, SUM( total ) as Orders, "Returned" as serie FROM (
								SELECT 
										u.phone, 
										COUNT(*) AS total,
										allorders.allorders 
									FROM user u 
									INNER JOIN `order` o ON u.id_user = o.id_user AND o.date BETWEEN CURDATE() - INTERVAL {days} DAY AND CURDATE() AND o.env = "live"
									INNER JOIN ( SELECT u.phone, COUNT(*) AS allorders FROM user u INNER JOIN `order` o ON u.id_user = o.id_user AND o.env = "live" GROUP BY u.phone ) allorders ON allorders.phone = u.phone 
									WHERE u.phone IS NOT NULL
										GROUP BY u.phone HAVING allorders > 1 ) orders';

		$query = '';
		$union = '';
		foreach ( $days as $day ) {			
			$query .= $union . str_replace( '{days}', $day, $preQuery );
			$union = ' UNION ';
		}

		$graphs['orders-per-active'] = [
			'title' => 'Orders per Users',
			'type' => 'column',
			'unit' => 'orders',
			'tooltip' => "function() {
				var total = 0;
				var body = '';
				$.each( this.points, function( i, point ) {
					body += '<br/><span style=\"color:' + point.series.color + '\">' +  point.series.name + '</span>: ' + point.y + ' orders (' + point.percentage.toFixed(2) + '%)';
					total += point.y;
				});
				var html = '<b>Total: ' + total + ' orders</b>' + body;
				return html;}",
			'data' => c::db()->get( $query  )
		];

		// Total orders
		$preQuery = 'SELECT "Last {days} day(s)" AS day, COUNT( * ) as Orders, "Orders" as serie FROM `order` o WHERE env="live" AND o.date BETWEEN CURDATE() - INTERVAL {days} DAY AND CURDATE()';

		$query = '';
		$union = '';
		foreach ( $days as $day ) {			
			$query .= $union . str_replace( '{days}', $day, $preQuery );
			$union = ' UNION ';
		}

		$graphs['total-orders'] = [
			'title' => 'Total orders',
			'type' => 'column',
			'unit' => 'orders',
			'tooltip' => "false",
			'data' => c::db()->get( $query  )
		];

		// Gross Revenue
		$preQuery = 'SELECT "Last {days} day(s)" AS day, CAST( SUM( o.final_price ) AS DECIMAL( 14, 2 ) ) as Dollar, "US$" as serie FROM `order` o WHERE env="live" AND o.date BETWEEN CURDATE() - INTERVAL {days} DAY AND CURDATE()';

		$query = '';
		$union = '';
		foreach ( $days as $day ) {			
			$query .= $union . str_replace( '{days}', $day, $preQuery );
			$union = ' UNION ';
		}

		$graphs['gross-revenue'] = [
			'title' => 'Gross Revenue',
			'type' => 'column',
			'unit' => '',
			'tooltip' => "false",
			'data' => c::db()->get( $query  )
		];
*/