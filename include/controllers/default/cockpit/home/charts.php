<?php

class Controller_home_charts extends Crunchbutton_Controller_Account {
	public function init() {

		$chart = c::getPagePiece(2);

		switch ( $chart ) {
			
			case 'orders-per-week':
					$maxWeeks = $this->weeks();
					$weeks = ( $_REQUEST[ 'weeks' ] ? $_REQUEST[ 'weeks' ] : $maxWeeks );
					$query = 'SELECT DATE_FORMAT( CONVERT_TZ( `date`, "-8:00","-5:00" ), "Week %v/%Y" ) `week`, COUNT(*) AS Orders, "Orders" AS label FROM `order` GROUP BY YEARWEEK(date) ORDER BY YEARWEEK(date) DESC LIMIT ' .$weeks ;
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
					$query = 'SELECT DATE_FORMAT( CONVERT_TZ( `date`, "-8:00","-5:00" ), "week %v/%Y" ) `week`, CAST( SUM( final_price ) AS DECIMAL( 14, 2 ) ) AS "US$", "US$" AS label FROM `order` GROUP BY YEARWEEK(date) ORDER BY YEARWEEK(date) DESC LIMIT ' .$weeks ;
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
											date_format(CONVERT_TZ(`date`, "-8:00","-5:00"), "%W") AS `Day`,
											COUNT(*) AS `Orders`,
											community.name AS `Community`
										FROM `order`
										LEFT JOIN community using(id_community)
										WHERE
											env="live"
											and community.name IS NOT NULL
											and community.name != "Testing"
										GROUP BY date_format(CONVERT_TZ(`date`, "-8:00","-5:00"), "%W"), id_community
										ORDER BY date_format(CONVERT_TZ(`date`, "-8:00","-5:00"), "%Y%m%d"), id_community
									';
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
		$query = 'SELECT COUNT( DISTINCT( YEARWEEK( date) ) ) AS weeks FROM `order` ';
		$result = c::db()->get( $query );
		return $result->_items[0]->weeks; 
	}

}