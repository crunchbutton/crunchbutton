<?php

class MetricsHttpException extends Exception {
	public $status_code;
	public function __construct($status_code = 500, $message = null) {
		if(is_null($message)) {
			$message = 'Unknown server error';
		}
		parent::__construct($message, $status_code);
		$this->status_code = $status_code;
	}
}


// Metrics API
//
// @param communities - comma separated list of communities or special community name ('active', 'all')
// @param start - UNIX timestamp or relative time period (e.g., '-7d', '-3h', see list of valid time suffixes below)
// @param end - UNIX timestamp or relative time period
// @param period - period of time to group on (valid: 'hour', 'day', 'week', 'month', 'year')
//
// valid time suffixes: s (second), m (minute), h (hour), d (day), w (week), M (month), y (year)
// currently only negative relative times are supported and they only indicate relative to current time, forced to start of current period.
// e.g. -7d means the start of the day 7 days ago, -3w means the start of the week 3 weeks ago, etc.
class Controller_api_metrics extends Crunchbutton_Controller_RestAccount {
	public function init() {
		try {
			// selected communities = split string on , for community=
			$allowedCommunities = $this->availableCommunities();
		} catch (MetricsHttpException $e) {
			// Ask Devin how to error out here
			echo "ERROR: " . $e->$status_code . " msg: " . $e->$message;
		}
		try {
			$startDate = Cockpit_Metrics::getStartDate($_REQUEST['start']);
		} catch (MetricsDateException $e) {
			echo "ERROR: with start date" . $e->$message;
		}
		try {
			$endDate = Cockpit_Metrics::getEndDate($_REQUEST['end']);
		} catch (MetricsDateException $e) {
			echo "ERROR: with end date" . $e->$message;
		}
	}
	public function availableCommunities() {
		$hasPermissionFullPermission = c::admin()->permission()->check( [ 'global', 'metrics-all', 'metrics-communities-all' ] );
		$communities = Crunchbutton_Community::q( 'SELECT * FROM community WHERE active = 1 ORDER BY name ASC' );

		if( !$hasPermissionFullPermission ){
			$_communities = [];
			foreach ( $communities as $community ) {
				$permission_name = strtolower( $community->name );
				$permission_name = str_replace( ' ' , '-', $permission_name );
				$permission_name = "metrics-communities-{$permission_name}";
				if( c::admin()->permission()->check( [ $permission_name ] ) ){
					$_communities[] = $community;
				}
			}
		} else {
			$_communities = $communities;
		}
		return $_communities;
	}

	// constructs a query grouping orders by $period, Assumes sanitized inputs and is NOT safe from SQL injection.
	public static function _buildOrdersQuery($communities, $startDate, $endDate, $period) {
		if(!isset(self::periodSQLFormats()[$period])) {
			throw new MetricsHttpException(400, 'invalid period');
		}
		$periodFormat = self::periodSQLFormats()[$period];
		$commString = '(' . implode(',', $communities) . ')';
		$q = '
			SELECT C.name community_name, DATE_FORMAT(O.date, ' . $periodFormat . ') date_group, MIN(date) start_date, COUNT(*) count FROM `order` O
			LEFT JOIN community C
				ON O.id_community = C.id_community
			GROUP BY community_name, date
			WHERE date >= ' . $startDate . ' AND date <= ' . $endDate . '
				AND C.id_community IN ' . $commString . '
				AND (O.likely_test IS NULL OR O.likely_test IS FALSE);
		';
		$queryResult = c::app()->metricsDB()->query($q);
		return Cockpit_Metrics::groupByIndex($queryResult, 0);
	}
	// standard filters to get at test orders
	const orderFilter = '
		community.name NOT LIKE "%test%"
		AND restaurant.name NOT LIKE "%test%"
		AND `order`.name NOT LIKE "%test%"
		AND `order`.address NOT LIKE "%test%"
		';
	public static function dedupedOrderTable($select, $additionalFilters) {
		$q = '
			SELECT ' . $select . ' FROM `order` O
			LEFT JOIN restaurant ON restaurant.id_restaurant = O.id_restaurant
			LEFT JOIN community ON community.id_community = O.id_community
			WHERE community.name NOT LIKE "%test%"
				AND restaurant.name NOT LIKE "%test%"
				AND order.name NOT LIKE "%test%"
			';
	}
}
