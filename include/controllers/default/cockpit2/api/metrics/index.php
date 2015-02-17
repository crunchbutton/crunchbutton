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
	const MySQLDateFormat = 'Y-m-d H:i:s';
	// date format for mysql groupings
	public static function periodSQLFormats($isMySQL = true) {
		if($isMySQL) {
			return [
				'Y' => '%Y',
				'M' => '%Y-%m', // month
				'w' => '%Y-w%U', // starting Sunday
				'd' => '%Y-%m-%d',
				'h' => '%Y-%m-%d %H',
				'm' => '%Y-%m-%d %H:%i', // minute
				's' => '%Y-%m-%d %H:%i:%s'
			];
		} else {
			// POSTGRES!
			return [
				'Y' => 'YYYY',
				'M' => 'YYYY-MM', // month
				'w' => 'IYYY', // starting Sunday
				'd' => 'YYYY-MM-DD',
				'h' => 'YYYY-MM-DD HH24',
				'm' => 'YYYY-MM-DD HH24:MI', // minute
				's' => 'YYYY-MM-DD HH24:MI:SS'
			];
		}
	}

	public function init() {
		try {
			// selected communities = split string on , for community=
			$allowedCommunities = Cockpit_Metrics::availableCommunities();
		} catch (MetricsHttpException $e) {
			// Ask Devin how to error out here
			echo "ERROR: " . $e->$status_code . " msg: " . $e->$message;
			exit();
		}
		try {
			$startDate = Cockpit_Metrics::getStartDate($_REQUEST['start']);
		} catch (MetricsDateException $e) {
			/* echo "ERROR: with start date" . $e->getMessage(); */
			$startDate = Cockpit_Metrics::getStartDate('-60d');
		}
		try {
			$endDate = Cockpit_Metrics::getEndDate($_REQUEST['end']);
		} catch (MetricsDateException $e) {
			/* echo "ERROR: with end date" . $e->getMessage(); */
			$endDate = Cockpit_Metrics::getEndDate('-0d');
		}
		if(!$_REQUEST['communities']) {
			$communities = self::filterCommunities();
		} else {
			// 'active' gets handled below
			$communities = self::filterCommunities(explode(',', $_REQUEST['communities']));
		}
		$period = $_REQUEST['period'] ? $_REQUEST['period'] : 'd';
		$chartType = $_REQUEST['type'] ? $_REQUEST['type'] : 'orders';
		echo json_encode(self::funcForQueryType($chartType, $communities, $startDate, $endDate, $period));
	}
	/**
	 * returns a filtered set of community IDs for the given set of
	 * communityIDs (or retrieves all active communities if none passed). 
	 * communityIDs should be an array of integer-likes.
	 **/
	public static function filterCommunities($communityIDs = null) {
		$allowedCommunityMap = [];
		foreach(Cockpit_Metrics::availableCommunities() as $community) {
			$allowedCommunityMap[$community->id_community] = $community;
		}
		// default to active communities if not specified
		if(is_null($communityIDs) || $communityIDs == ['active']) {
			$communityIDs = [];
			foreach($allowedCommunityMap as $id_community => $community) {
				if($community->active == 1) {
					$communityIDs[] = $id_community;
				}
			}
		}
		$allowed = [];
		foreach($communityIDs as $id_community) {
			$id_community = intval($id_community);
			if(isset($allowedCommunityMap[intval($id_community)])) {
				$allowed[] = $id_community;
			}
		}
		return $allowed;
	}
	public static function _getPeriodFormat($period, $isMySQL = true) {
		if(!isset(self::periodSQLFormats($isMySQL)[$period])) {
			throw new MetricsHttpException(400, 'invalid period ' . $period);
		}
		return self::periodSQLFormats($isMySQL)[$period];
	}
	public static function _buildCommunityFilter($communities, $tableName = 'community') {
		// check for SQL injection best we can by forcing this to be all integers
		foreach($communities as $c) {
			if(intval($c) != $c) {
				throw new MetricsHttpException(400, 'invalid community ' . $c);
			}
		}
		$commString = '(' . implode(',', $communities) . ')';
		return $tableName . '.id_community IN ' . $commString;
	}
	public static function _buildDateFilter($startDate, $endDate, $dateColumn = 'date') {
		return '( ' . $dateColumn . ' >= "' . $startDate->format(self::MySQLDateFormat) . '" AND ' . $dateColumn . ' <= "' . $endDate->format(self::MySQLDateFormat) . '" )';
	}
	public static function _buildOrdersQuery($communities, $startDate, $endDate, $period) {
		$periodFormat = self::_getPeriodFormat($period);
		$q = '
			SELECT
				community.id_community,
				DATE_FORMAT(`order`.date, "' . $periodFormat . '") date_group,
				COUNT(*) count
			FROM `order`
			LEFT JOIN community
				ON `order`.id_community = community.id_community
			WHERE ' . self::_buildDateFilter($startDate, $endDate, '`order`.date') . '
				AND ' . self::_buildCommunityFilter($communities, 'community') . '
				AND ' . self::_buildOrderFilter('`order`') . '
			GROUP BY id_community, date_group
			';
		return self::formatQueryResults(c::db()->query($q), 'id_community', 'date_group', 'count');
	}
	public static function _grossRevenueQuery($communities, $startDate, $endDate, $period) {
		$periodFormat = self::_getPeriodFormat($period);
		$q = '
			SELECT
				community.id_community,
				DATE_FORMAT(`order`.date, "' . $periodFormat . '") date_group,
				ROUND(SUM(final_price), 2) final_price
			FROM `order`
			LEFT JOIN community
				ON `order`.id_community = community.id_community
			WHERE ' . self::_buildDateFilter($startDate, $endDate, '`order`.date') . '
				AND ' . self::_buildCommunityFilter($communities, 'community') . '
				AND ' . self::_buildOrderFilter('`order`') . '
			GROUP BY id_community, date_group
			';
		return self::formatQueryResults(c::db()->query($q), 'id_community', 'date_group', 'final_price');
	}

	public static function formatQueryResults($queryResult, $groupCol, $labelCol, $dataCol, $isInt=true, $defaultValue = 0) {
		$rows = [];
		while($r = $queryResult->fetch()) {
			$rows[] = (array) $r;
		}
		$grouped = Cockpit_Metrics::groupByIndex($rows, $groupCol);
		// grab all the labels on every row and make sure that every grouping
		// has not-present labels filled with the default value
		$allLabels = [];
		foreach($rows as $row) {
			$allLabels[$row[$labelCol]] = 1;
		}
		$allLabels = array_keys($allLabels);
		sort($allLabels);
		$out = [];
		foreach($grouped as $key => $rows) {
			$data = [];
			foreach($rows as $row) {
				$labelMap[$row[$labelCol]] = $row;
			}
			foreach($allLabels as $label) {
				if(isset($labelMap[$label])) {
					$row = $labelMap[$label];
					if($isInt) {
						$data[]  = intval($row[$dataCol]);
					} else {
						$data[] = floatval($row[$dataCol]);
					}
				} else {
					$data[] = $defaultValue;
				}
			}
			$out[$key] = ['labels' => $allLabels, 'data' => [$data]];
		}
		return $out;
	}
	public static function funcForQueryType($type, $communities, $startDate, $endDate, $period) {
		switch($type) {
		case 'users':
			return self::_uniqueUsersQuery($communities, $startDate, $endDate, $period);
			break;
		case 'new-users':
			return self::_newUsersQuery($communities, $startDate, $endDate, $period);
			break;
		case 'orders-by-hour':
			return self::_ordersByHourQuery($communities, $startDate, $endDate, $period);
			break;
		case 'orders':
			return self::_buildOrdersQuery($communities, $startDate, $endDate, $period);
			break;
		case 'refunded':
			return self::_refundedOrdersQuery($communities, $startDate, $endDate, $period);
			break;
		case 'gross-revenue':
			return self::_grossRevenueQuery($communities, $startDate, $endDate, $period);
			break;
		default:
			throw new MetricsHttpException(400, 'invalid chart type: ' . $type);
		}
	}
	public static function _buildOrderFilter($table) {
		$out = '(' . $table . '.likely_test = FALSE OR ' . $table . '.likely_test IS NULL)';
		$out = $out . ' AND (' . $table . '.refunded IS NULL OR ' . $table . '.refunded = FALSE)';
		return '(' . $out . ')';
	}
	public static function _uniqueUsersQuery($communities, $startDate, $endDate, $period) {
		$periodFormat = self::_getPeriodFormat($period);
		$q = '
			SELECT
				community.id_community,
				DATE_FORMAT(`order`.date, "' . $periodFormat . '") date_group,
				COUNT(DISTINCT phone) count
			FROM `order`
			LEFT JOIN community
				ON `order`.id_community = community.id_community
			WHERE ' . self::_buildDateFilter($startDate, $endDate, '`order`.date') . '
				AND ' . self::_buildCommunityFilter($communities, 'community') . '
				AND ' . self::_buildOrderFilter('`order`') . '
			GROUP BY id_community, date_group
			';
		return self::formatQueryResults(c::db()->query($q), 'id_community', 'date_group', 'count');
	}
	public static function _refundedOrdersQuery($communities, $startDate, $endDate, $period) {
		$periodFormat = self::_getPeriodFormat($period);
		$q = '
			SELECT
				community.id_community,
				DATE_FORMAT(`order`.date, "' . $periodFormat . '") date_group,
				COUNT(*) count
			FROM `order`
			LEFT JOIN community
				ON `order`.id_community = community.id_community
			WHERE ' . self::_buildDateFilter($startDate, $endDate, '`order`.date') . '
				AND ' . self::_buildCommunityFilter($communities, 'community') . '
				AND `order`.likely_test = FALSE OR `order`.likely_test IS NULL AND `order`.refunded = TRUE
			GROUP BY id_community, date_group
			';
		return self::formatQueryResults(c::db()->query($q), 'id_community', 'date_group', 'count');
	}
	public static function _newUsersQuery($communities, $startDate, $endDate, $period) {
		$periodFormat = self::_getPeriodFormat($period);
		// TODO(jtratner): pre-calculate first order to speed up this query
		$q = '
			SELECT
				community.id_community,
				DATE_FORMAT(`order`.date, "' . $periodFormat . '") date_group,
				COUNT(*) count
			FROM `order`
			LEFT JOIN community
				ON `order`.id_community = community.id_community
			INNER JOIN (
				SELECT MIN(id_order) id_order
				FROM `order` O
				INNER JOIN user u
					ON u.id_user = O.id_user
				GROUP BY u.phone) first_order
				ON first_order.id_order = `order`.id_order
			WHERE ' . self::_buildDateFilter($startDate, $endDate, '`order`.date') . '
				AND ' . self::_buildCommunityFilter($communities, 'community') . '
				AND ' . self::_buildOrderFilter('`order`') . '
			GROUP BY id_community, date_group
			';
		return self::formatQueryResults(c::db()->query($q), 'id_community', 'date_group', 'count');
	}

	public static function _ordersByHourQuery($communities, $startDate, $endDate, $period) {
		$q = '
			SELECT
				community.id_community,
				DATE_FORMAT(`order`.date, "%Hh") hour_of_day,
				MIN(date) start_date,
				COUNT(*) count
			FROM `order`
			LEFT JOIN community
				ON `order`.id_community = community.id_community
			WHERE ' . self::_buildDateFilter($startDate, $endDate, '`order`.date') . '
				AND ' . self::_buildCommunityFilter($communities, 'community') . '
				AND ' . self::_buildOrderFilter('`order`') . '
			GROUP BY id_community, hour_of_day
			';
		return self::formatQueryResults(c::db()->query($q), 'id_community', 'hour_of_day', 'count');
	}
}
