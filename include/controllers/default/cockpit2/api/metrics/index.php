<?php

class MetricsConstants {
	SECONDS_IN_DAY = 86400;
}

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

class MetricsDateException extends Exception {
}

class Metrics_Helpers {
	// date format for mysql groupings
	const periodSQLFormats = [
		'Y' => '%Y',
		'M' => '%Y-%m', // month
		'w' => '%Y-%U', // starting Sunday
		'd' => '%Y-%m-%d',
		'h' => '%Y-%m-%d %H',
		'm' => '%Y-%m-%d %H:%i', // minute
		's' => '%Y-%m-%d %H:%i:%s'
	];

	/**
	* groupByIndex groups the given array of arrays by the values in groupIndex
	* @param array $arrays - the arrays to group
	* @param mixed $groupIndex - integer index or key to group on (checks if set on each element)
	* @param mixed $defaultGroup (default null) - value to use when groupIndex is not set
	* @return array $groupedArrays - groups of arrays by the values found at $groupIndex
	*
	* example:
	* 	$ret = Metrics_Helpers::groupByIndex([[2, 'a', 3], [4, 'c', 5], [1, 'a', 16], []]);
	* 	$ret == ['a' => [[2, 'a', 3], [1, 'a', 16]], 'c' => [[4, 'c', 5], null => [[]]];
	*/
	public static function groupByIndex($arrays, $groupIndex, $defaultGroup = null) {
		$output = [];
		foreach($arrays as $row) {
			$groupValue = isset($row[$groupIndex]) ? $row[$groupIndex] : $defaultGroup;
			if(!isset($output[$groupValue])) {
				$arr = [];
				$output[$groupValue] = $arr;
			}
			$output[$groupValue][] = $row;
		}
		return $output;
	}

	// whether date should be set to start or end.
	const START_PERIOD = TRUE;
	const END_PERIOD = FALSE;

	// converts $timeString to a naive datetime using rules described by Metrics API below.
	// (because it's far easier to work with naive datetimes internally than timestamps,
	//  since we mostly care about Pacific-time orders)
	public static function convertTime($timeString, $startDate = null) {
		if(is_null($startDate)) {
			$startDate = date_create('now');
		} else {
			// allow us to mutate start
			$startDate = clone $startDate;
		}
		if($timeString == 'now') {
			return $startDate;
		} else if (is_numeric($timeString))i {
			return date_create('@' . $timeString);
		} else {
			$period = substr($timeString, -1);
			$n = substr($timeString, 0, strlen($timeString) - 1);
			if(!is_numeric($n)) {
				throw new MetricsDateException('time delta must be expressed as numeric value');
			}
			return MetricsDateHelper::modifyDate($startDate, $n, $period);
		}
	}
}

// wraps together a set of functions (publicly accessible via modifyDate) for
// converting a Metrics API adjustment period into a date that starts at the
// start of that period.
// Valid identifiers are those as specified by the Metrics API. specify $n == 0
// to move to edge of the current period. Expects naive-ish datetimes.
class MetricsDateHelper {
	// adjusts the date by the specified period, setting the date to either the start or end of the period depending on the value of $atStart
	// see travis/Tests/MetricsTest.php for detailed examples of how this function works.
	// but in essence moveDateToPeriod(date_create('2014-05-10'), -3, 'Y', START_PERIOD) == date_create('2011-05-10');
	// AND moveDateToPeriod(date_create('2014-05-10', -3, 'Y', END_PERIOD) == date_create('2011-05-10 23:59:59');
	public static function moveDateToPeriod($date, $n, $period, $atStart) {
		// allow internal functions not to care about mutating the date
		$date = clone $date;
		// use the constant for clarity, even though it's just a boolean.
		$atStart = $atStart == Metrics_Helper::START_PERIOD;
		switch($period) {
		case 'Y':
			return MetricsDateHelper::_modifyYears($date, $n, $atStart);
			break;
		case 'M':
			return MetricsDateHelper::_modifyMonths($date, $n, $atStart);
			break;
		case 'w':
			return MetricsDateHelper::_modifyWeeks($date, $n, $atStart);
			break;
		case 'd':
			return MetricsDateHelper::_modifyDays($date, $n, $atStart);
			break;
		case 'h':
			return MetricsDateHelper::_modifyHours($date, $n, $atStart);
			break;
		case 'm':
			return MetricsDateHelper::_modifyMinutes($date, $n, $atStart);
			break;
		case 's':
			return MetricsDateHelper::_modifySeconds($date, $n, $atStart);
			break;
		default:
			// not sure if we can really include the modifier with the date b/c otherwise won't be sanitized;
			throw new MetricsDateException('invalid modifier');
		}
	}
	public static function _modifyByPeriod($date, $n, $period) {
		if($n == 0) {
			return $date;
		} else if($n > 0) {
			return $date->modify('+' . $n . ' ' . $period);
		} else {
			return $date->modify($n . ' ' . $period);
		}
	}
	// each modify function adjusts the date by the given period of time and resets it to the start of the period
	public static function _modifySeconds($date, $n, $atStart) {
		$date = $date->setTime($date->format('H'), $date->format('m'), $date->format('s'));
		return MetricsDateHelper::_modifyByPeriod($date, $n, 'second');
	}
	public static function _modifyMinutes($date, $n, $atStart) {
		if($atStart) {
			$seconds = 0;
		else {
			$seconds = 59;
		}
		$date = $date->setTime($date->format('H'), $date->format('m'), $seconds);
		return MetricsDateHelper::_modifyByPeriod($date, $n, 'minute');
	}
	public static function _modifyHours($date, $n, $atStart) {
		$hours = $date->format('H')
		if($atStart) {
			$date = $date->setTime($hours, 0, 0);
		} else {
			$date = $date->setTime($hours, 59, 59);
		}
		return MetricsDateHelper::_modifyByPeriod($date, $n, 'hour');
	}
	public static function _modifyDays($date, $n, $atStart) {
		if($atStart) {
			$date = $date->setTime(0, 0, 0);
		} else {
			$date = $date->setTime(23, 59, 59);
		}
		return MetricsDateHelper::_modifyByPeriod($date, $n, 'day');
	}
	public static function _modifyWeeks($date, $n, $atStart) {
		$daysFromSunday = $date->format('w');
		if($atStart) {
			// force to start on Sunday
			$date = MetricsDateHelper::_modifyByPeriod($date, -$daysFromSunday, 'week');
			$date = $date->setTime(23, 59, 59);
		} else {
			// force to Saturday
			$date = MetricsDateHelper::_modifyByPeriod($date, (6 - $daysFromSunday), 'week');
			$date = $date->setTime(0, 0, 0);
		}
		return $date;
	}
	public static function _modifyMonths($date, $n, $atStart) {
		$year = $date->format('Y');
		$month = $date->format('m');
		$yearDelta = floor($n / 12);
		$sign = ($n >= 0) ? 1 : -1;
		$monthDelta = abs($n) - (12 * $yearDelta, $atStart);
		$year += $yearDelta;
		$month += $sign * $monthDelta;
		$date = $date->setDate($year, $month, 1)->setTime(0, 0, 0);
		if(!$atStart) {
			// go to start of next month and then go one day back so we don't have to deal with different length months
			$date = $date->modify('+1 month')->modify('-1 day')->setTime(23, 59, 59);
		}
		return $date;
	}
	public static function _modifyYears($date, $n, $atStart) {
		$year = date('Y', strtotime($date));
		if($atStart) {
			return $date->setDate($year + $n, 1, 1)->setTime(0, 0, 0);
		} else {
			return $date->setDate($year + $n, 12, 31)->setTime(23, 59, 59);
		}
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
			$startDate = Metrics_Helper::convertTime($_REQUEST['start'], Metrics_Helper::START_PERIOD);
		} catch (MetricsDateException $e) {
			echo "ERROR: with start date" . $e->$message;
		}
		try {
			$endDate = Metrics_Helper::convertTime($_REQUEST['end'], Metrics_Helper::END_PERIOD);
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
		if(!isset(self::periodSQLFormats[$period])) {
			throw new MetricsHttpException(400, 'invalid period');
		}
		$periodFormat = self::periodSQLFormats[$period];
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
		return Metrics_Helper::groupByIndex($queryResult, 0);
	}
	// standard filters to get at test orders
	const orderFilter = '
		community.name NOT LIKE "%test%"
		AND restaurant.name NOT LIKE "%test%"
		AND `order`.name NOT LIKE "%test%"
		AND `order`.address NOT LIKE "%test%"
		'
	public static function dedupedOrderTable($select, $additionalFilters) {
		$q = '
			SELECT ' . $select . ' FROM `order` O
			LEFT JOIN restaurant ON restaurant.id_restaurant = O.id_restaurant
			LEFT JOIN community ON community.id_community = O.id_community
			WHERE community.name NOT LIKE "%test%"
				AND restaurant.name NOT LIKE "%test%"
				AND order.name NOT LIKE "%test%"
			'
	}
}
