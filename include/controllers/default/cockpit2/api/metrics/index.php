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
	// converts $timeString to a naive datetime using rules described by Metrics API below.
	// (because it's far easier to work with naive datetimes internally than timestamps,
	//  since we mostly care about Pacific-time orders)
	public static function convertRelativeTime($timeString, $startDate = null) {
		if(is_null($startDate)) {
			$startDate = date_create('now');
		} else {
			// allow us to mutate start
			$startDate = clone $startDate;
		}
		$period = substr($timeString, -1);
		$n = substr($timeString, 0, strlen($timeString) - 1);
		if(!is_numeric($n)) {
			throw new MetricsDateException('time delta must be expressed as numeric value');
		}
		return MetricsDateHelper::modifyDate($startDate, $n, $period);
	}
}

// wraps together a set of functions (publicly accessible via modifyDate) for
// converting a Metrics API adjustment period into a date that starts at the
// start of that period.
// Valid identifiers are those as specified by the Metrics API. specify $n == 0 
// to move to start of the period. Expects naive-ish datetimes.
class MetricsDateHelper {
	// adjusts the date by the specified period, resetting date to the start of the period (for weeks, weeks start on Sunday)
	// does not mutate, but returns a *new* date.
	public static function modifyDate($date, $n, $period) {
		// allow internal functions not to care about mutating the date
		$date = clone $date;
		switch($period) {
		case 'Y':
			return MetricsDateHelper::_modifyYears($date, $n);
			break;
		case 'M':
			return MetricsDateHelper::_modifyMonths($date, $n);
			break;
		case 'w':
			return MetricsDateHelper::_modifyWeeks($date, $n);
			break;
		case 'd':
			return MetricsDateHelper::_modifyDays($date, $n);
			break;
		case 'h':
			return MetricsDateHelper::_modifyHours($date, $n);
			break;
		case 'm':
			return MetricsDateHelper::_modifyMinutes($date, $n);
			break;
		case 's':
			return MetricsDateHelper::_modifySeconds($date, $n);
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
	public static function _modifySeconds($date, $n) {
		$date = $date->setTime($date->format('H'), $date->format('m'), $date->format('s'));
		return MetricsDateHelper::_modifyByPeriod($date, $n, 'seconds');
	}
	public static function _modifyMinutes($date, $n) {
		$date = $date->setTime($date->format('H'), $date->format('m'), 0);
		return MetricsDateHelper::_modifyByPeriod($date, $n, 'minutes');
	}
	public static function _modifyHours($date, $n) {
		$date = $date->setTime($date->format('H'), 0, 0);
		return MetricsDateHelper::_modifyByPeriod($date, $n, 'hours');
	}
	public static function _modifyDays($date, $n) {
		$date = $date->setTime(0, 0, 0);
		return MetricsDateHelper::_modifyByPeriod($date, $n, 'days');
	}
	public static function _modifyWeeks($date, $n) {
		// force week to start on sunday
		$daysFromSunday = $date->format('w');
		if($daysFromSunday > 0) {
			$date = $date->modify('-' . $daysFromSunday . ' days');
		}
		return MetricsDateHelper::_modifyByPeriod($date, $n, 'weeks');
	}
	public static function _modifyMonths($date, $n) {
		$year = $date->format('Y');
		$month = $date->format('m');
		$yearDelta = floor($n / 12);
		$sign = ($n >= 0) ? 1 : -1;
		$monthDelta = abs($n) - (12 * $yearDelta);
		$year += $yearDelta;
		$month += $sign * $monthDelta;
		return $date->setDate($year, $month, 1)->setTime(0, 0, 0);
	}
	public static function _modifyYears($date, $n) {
		$year = date('Y', strtotime($date));
		return $date->setDate($year + $n, 1, 1)->setTime(0, 0, 0);
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
			$dateRange = $this->dateRange();
		} catch (MetricsHttpException $e) {
			// Ask Devin how to error out here
			echo "ERROR: " . $e->$status_code . " msg: " . $e->$message;
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
	public function dateRange() {
		if(!is_null($_REQUEST['days_ago'])) {
			$days = $_REQUEST['days_ago'];
			$now = time();
			$start_ts = $now - $days * MetricsConstants::SECONDS_IN_DAY;
			$end_ts = $now
			// somehow convert to datetime
			return [$start_ts, $end_ts];
		}
		if(!is_null($_REQUEST['start_date']) || !is_null($_REQUEST['end_date'])) {
			$start_date = $_REQUEST['start_date'];
			$end_date = $_REQUEST['end_date'];
			if(!($start_date && $end_date)) {
				// 400 error and somehow raise request-ending exception
				throw new MetricsHttpException(400, "must specify both start_date and end_date if one is specified.")
			}
		}
		// we got nothing, so return FALSE to match PHP convention
		return FALSE;
	}
}
