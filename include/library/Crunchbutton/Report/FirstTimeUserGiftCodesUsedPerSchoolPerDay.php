<?php

class Crunchbutton_Report_FirstTimeUserGiftCodesUsedPerSchoolPerDay extends Cana_Model {

	public function report( $start, $end ){

		$pattern = "SELECT c.name AS community,
											 SUM(1) AS users,
											 '%s' AS `day`
								FROM `order` o
								INNER JOIN
									(SELECT SUM(1) orders,
													phone
									 FROM `order` o
									 WHERE o.date <= '%s 23:59:59'
									 GROUP BY o.phone
									 HAVING orders = 1) orders ON orders.phone = o.phone AND o.date BETWEEN '%s 00:00:00' AND '%s 23:59:59'
								INNER JOIN
									(SELECT o.id_order
									 FROM `order` o
									 INNER JOIN credit c ON c.id_order = o.id_order
									 AND c.type = 'DEBIT'
									 INNER JOIN credit giftcard ON giftcard.id_credit = c.id_credit_debited_from
									 AND giftcard.id_promo IS NOT NULL
									 AND giftcard.credit_type = 'cash'
									 AND c.credit_type = 'cash' WHERE o.date BETWEEN '%s 00:00:00' AND '%s 23:59:59'
								) giftcard ON giftcard.id_order = o.id_order
								INNER JOIN restaurant_community rc ON rc.id_restaurant = o.id_restaurant
								INNER JOIN community c ON rc.id_community = c.id_community
								GROUP BY c.name
								ORDER BY c.name ASC";

		$communities = [];
		$days = [];

		for( $i = $start; $i <= $end; $i++ ){
			$date = strval( $i );
			$query = sprintf( $pattern, $date, $date, $date, $date, $date, $date );
			$results = c::db()->get( $query );
			if( count( $results ) ){
				$formatted_date = new DateTime( $date, new DateTimeZone( c::config()->timezone ) );
				$formatted_date = $formatted_date->format( 'M jS Y' );
				foreach( $results as $result ){
					$community = $result->community;
					$users = intval( $result->users );
					$communities[] = [ 'users' => $users, 'day' => $formatted_date, 'community' => $community ];
					$days[] = [ 'users' => $users, 'day' => $formatted_date, 'community' => $community ];
				}
			}
		}
		return [ 'days' => $days, 'communities' => $communities ];
	}

	public function __construct(){}
}