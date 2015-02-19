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
// *** CLIENT API: ***
// @param communities - comma separated list of communities or special community name ('active', 'all')
// @param start - UNIX timestamp, date string or relative time period (e.g., '-7d', '-3h', see list of valid time suffixes below)
// @param end - UNIX timestamp, date string or relative time period
// @param period - period of time to group on (valid: 'hour', 'day', 'week', 'month', 'year')
// @param type - the type of metric requested
//
// Outputs JSON in object format {id_community => {labels => [], data => []}} 
// where labels are the same for all communities (with data backfilled to zero 
// for missing values).
//
// valid time suffixes: s (second), m (minute), h (hour), d (day), w (week), M (month), y (year)
// currently only negative relative times are supported and they only indicate relative to current time, forced to start of current period.
// e.g. -7d means the start of the day 7 days ago, -3w means the start of the week 3 weeks ago, etc.
//
// Available charts: GET /api/metrics/available
// Available communities for metrics: GET /api/metrics/permissions
//
// *** INTERNAL STRUCTURE ***
// SQL Queries are built by hand (sorry!) but using a few shared filters via a single input/output functions. To add a new chart you need to:
// 1. Add the chart to Cockpit_Metrics::availableMetrics()
// 2. define a function that takes in $communities, $startDate, $endDate and $period and add it into the switch statement in funcForQueryType
//
// Useful helpers:
//  - _buildDateFilter (constructs an appropriate stirng for the where filter for dates)
//  - _buildOrderFilter (adds in filters for likely_test and also not refunded)
//  - _buildCommunityFilter (REQUIRED! Makes sure that only communities available to user are returned)
//  - formatQueryResults - takes raw Cana objects and converts them back into the desired format
//  - prettifyLabels - transforms SQL labels for dates into dates that look good for the front end
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
		if($_REQUEST['start']) {
			$startDate = Cockpit_Metrics::getStartDate($_REQUEST['start']);
		} else {
			$startDate = Cockpit_Metrics::getStartDate('-60d');
		}
		if ($_REQUEST['end']) {
			$endDate = Cockpit_Metrics::getEndDate($_REQUEST['end']);
		} else {
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
		return self::formatQueryResults(self::_getMySQLQuery($q), 'id_community', 'date_group', 'count');
	}
	public static function _getMySQLQuery($query) {
		// TODO: Secure this with admin only
		if ($_REQUEST['showSQL']) {
			echo $query;
		}
		return c::db()->query($query);
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
		return self::formatQueryResults(self::_getMySQLQuery($q), 'id_community', 'date_group', 'final_price');
	}

	/**
	 * formatQueryResults takes in a Cana Tabe iterator and groups it by the specified column, backfilling labels via labelCol
	 * @param $queryResult - Cana Iterator
	 * @param $groupCol - the name of the column to group data on
	 * @param $labelCol - name of the column to pull labels from (should be able to be lexicographically sorted)
	 * @param $dataCol - column with actual data and/or numeric values
	 * @param $fillValue - value to substitute in for missing labels
	 *
	 * @return {$groupKey => {data => <ARRAY OF DATA>, labels => <ARRAY OF LABELS>}}
	 * where labels are shared between *all* group keys (same object) and data 
	 * is backfilled to match up with labels. Labels will be lexicogrpahically 
	 * sorted from lowest to highest.
	 **/
	public static function formatQueryResults($queryResult, $groupCol, $labelCol, $dataCol, $isInt=true, $fillValue = 0) {
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
		return self::formatQueryResults(self::_getMySQLQuery($q), 'id_community', 'date_group', 'count');
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
				AND (`order`.likely_test = FALSE OR `order`.likely_test IS NULL) AND `order`.refunded = TRUE
			GROUP BY id_community, date_group
			';
		return self::formatQueryResults(self::_getMySQLQuery($q), 'id_community', 'date_group', 'count');
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
		return self::formatQueryResults(self::_getMySQLQuery($q), 'id_community', 'date_group', 'count');
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
		return self::formatQueryResults(self::_getMySQLQuery($q), 'id_community', 'hour_of_day', 'count');
	}
}
