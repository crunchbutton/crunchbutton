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
			$startDate = Cockpit_Metrics::startOfPeriod($_REQUEST['period'], $startDate);
		} else {
			$startDate = Cockpit_Metrics::getStartDate('-60d');
		}
		if ($_REQUEST['end']) {
			$endDate = Cockpit_Metrics::getEndDate($_REQUEST['end']);
			$endDate = Cockpit_Metrics::endOfPeriod($_REQUEST['period'], $endDate);
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
		$metricContainer = new _Community_Metric_Container($chartType, $communities, $startDate, $endDate, $period);
		echo json_encode($metricContainer->getData());
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
}

class _Community_Metric_Container {
	public $chartType;
	public $communities;
	public $startDate;
	public $endDate;
	public $period;
	public function __construct($chartType, $communities, $startDate, $endDate, $period) {
		$this->chartType = $chartType;
		$this->communities = $communities;
		$this->startDate = $startDate;
		$this->endDate = $endDate;
		$this->period = $period;
	}
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
	public function _buildOrdersQuery() {
		$periodFormat = self::_getPeriodFormat($this->period);
		$q = '
			SELECT
				id_community,
				DATE_FORMAT(`order`.date, "' . $periodFormat . '") date_group,
				COUNT(*) count
			FROM `order`
			WHERE ' . self::_buildDateFilter($this->startDate, $this->endDate, '`order`.date') . '
				AND ' . self::_buildCommunityFilter($this->communities, '`order`') . '
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
		if (is_null($query) || !$query) {
			throw new MetricsHttpException(500, 'failed to pass in query');
		}
		return c::db()->query($query);
	}
	public function _grossRevenueQuery() {
		$periodFormat = self::_getPeriodFormat($this->period);
		$q = '
			SELECT
				id_community,
				DATE_FORMAT(`order`.date, "' . $periodFormat . '") date_group,
				ROUND(SUM(final_price), 2) final_price
			FROM `order`
			WHERE ' . self::_buildDateFilter($this->startDate, $this->endDate, '`order`.date') . '
				AND ' . self::_buildCommunityFilter($this->communities, '`order`') . '
				AND ' . self::_buildOrderFilter('`order`') . '
			GROUP BY id_community, date_group
			';
		return self::formatQueryResults(self::_getMySQLQuery($q), 'id_community', 'date_group', 'final_price');
	}

	/**
	 * formatQueryResults takes in a Cana Tabe iterator and groups it by the specified column, backfilling labels via labelCol
	 * @param $queryResult - Cana Iterator
	 * @param $groupCol - the name of the column to group data on
	 * @param $labelCol (string) - name of the column to pull labels 
	 *    from (should be able to be lexicographically sorted, unless labels specified)
	 * @param $dataCol - column with actual data and/or numeric values
	 * @param $isInt (optional) - whether the valueis integral (true) or float (false)
	 * @param $fillValue (optional) - value to substitute in for missing labels
	 * @param $labels (optional) - ordered array of labels to pull (must exactly match 
	 *     labels in data)
	 *
	 * @return {'data' => {$groupKey => <ARRAY OF DATA>}, 'meta' => {'labels' => <ARRAY OF LABELS>, 'startDate': <ISO 80601 date>, 'endDate': <ISO 80601 date>}}
	 * where labels are shared between *all* group keys (same object) and data 
	 * is backfilled to match up with labels. Labels will be lexicogrpahically 
	 * sorted from lowest to highest. Missing communities are also backfilled into data.
	 **/
	public function formatQueryResults($queryResult, $groupCol, $labelCol, $dataCol, $isInt=true, $fillValue = 0, $labels = null) {
		$rows = [];
		while($r = $queryResult->fetch()) {
			$rows[] = (array) $r;
		}
		$grouped = Cockpit_Metrics::groupByIndex($rows, $groupCol);
		if (!is_null($labels)) {
			$allLabels = $labels;
		} else {
			// grab all the labels on every row and make sure that every grouping
			// has not-present labels filled with the default value
			$allLabels = [];
			foreach($rows as $row) {
				$allLabels[$row[$labelCol]] = 1;
			}
			$allLabels = array_keys($allLabels);
			sort($allLabels);
		}
		$missingData = array_pad([], count($allLabels), $fillValue);
		$out = [];
		foreach($grouped as $key => $rows) {
			$data = [];
			$labelMap = [];
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
					$data[] = $fillValue;
				}
			}
			$out[$key] = $data;
		}
		// backfill data for each community
		foreach($this->communities as $id_community) {
			if(!isset($out[$id_community])) {
				$out[$id_community] = $missingData;
			}
		}
		return ['data' => $out, 'meta' => ['labels' => $allLabels, 'startDate' => $this->startDate->format('Y-m-d H:i:s'), 'endDate' => $this->endDate->format('Y-m-d H:i:s')]];
	}
	public static function maybeStripLeadingYear($labels) {
		$years = [];
		$stripped = [];
		foreach($labels as $l) {
			$split = explode($l, '-', 2);
			if(count($split) != 2 || strlen($split[0]) != 4) {
				// we expect formatting like '2015-01-5'
				return clone $labels;
			}
			$years[$split[0]] = 1;
			$stripped[] = $split[1];
			if(count($years) > 1) {
				return clone $labels;
			}
		}
		return $stripped;
	}
	// converts labels output from the periodSQLFormat into a human format that the front end can display
	public static function prettifyLabels($labels, $period) {
		if (count($labels) == 0) {
			return $labels;
		}
		switch($period) {
		case 'M':
			$stripped = maybeStripLeadingYear($labels);
			if ($stripped[0] == $labels[0]) {
			}
		case 'd':
			// for day, we just strip off the 2015- part if we can
			return maybeStripLeadingYear($labels);
		case 'w':
			// TODO: make something work with week
		case 'Y':
		case 'm':
		case 's':
		case 'h':
		default:
			return clone $labels;
		}
	}
	public function getData() {
		switch($this->chartType) {
		case 'users':
			return $this->_uniqueUsersQuery();
			break;
		case 'new-users':
			return $this->_newUsersQuery();
			break;
		case 'new-orders':
			return $this->_newOrdersQuery();
			break;
		case 'new-users-gross-revenue':
			return $this->_firstOrderRevenueQuery('gross-revenue');
			break;
		case 'new-users-delivery-tips':
			return $this->_firstOrderRevenueQuery('tips');
			break;
		case 'new-users-delivery-fee':
			return $this->_firstOrderRevenueQuery('delivery-fee');
			break;
		case 'repeat-users':
			return $this->_repeatUserQuery();
			break;
		case 'repeat-orders':
			return $this->_repeatOrderQuery();
			break;
		case 'repeat-users-gross-revenue':
			return $this->_repeatRevenueQuery('gross-revenue');
			break;
		case 'repeat-users-delivery-tips':
			return $this->_repeatRevenueQuery('tips');
			break;
		case 'repeat-users-delivery-fee':
			return $this->_repeatRevenueQuery('delivery-fee');
			break;
		case 'orders-by-hour':
			return $this->_ordersByHourQuery();
			break;
		case 'orders':
			return $this->_buildOrdersQuery();
			break;
		case 'refunded':
			return $this->_refundedOrdersQuery();
			break;
		case 'gross-revenue':
			return $this->_grossRevenueQuery();
			break;
		default:
			throw new MetricsHttpException(400, 'invalid chart type: ' . $this->chartType);
		}
	}
	public static function _buildOrderFilter($table) {
		$out = '(' . $table . '.likely_test = FALSE OR ' . $table . '.likely_test IS NULL)';
		$out = $out . ' AND (' . $table . '.refunded IS NULL OR ' . $table . '.refunded = FALSE)';
		return '(' . $out . ')';
	}
	public function _uniqueUsersQuery() {
		$periodFormat = self::_getPeriodFormat($this->period);
		$q = '
			SELECT
				id_community,
				DATE_FORMAT(`order`.date, "' . $periodFormat . '") date_group,
				COUNT(DISTINCT phone) count
			FROM `order`
			WHERE ' . self::_buildDateFilter($this->startDate, $this->endDate, '`order`.date') . '
				AND ' . self::_buildCommunityFilter($this->communities, '`order`') . '
				AND ' . self::_buildOrderFilter('`order`') . '
			GROUP BY id_community, date_group
			';
		return self::formatQueryResults(self::_getMySQLQuery($q), 'id_community', 'date_group', 'count');
	}
	public function _refundedOrdersQuery() {
		$periodFormat = self::_getPeriodFormat($this->period);
		$q = '
			SELECT
				id_community,
				DATE_FORMAT(`order`.date, "' . $periodFormat . '") date_group,
				COUNT(*) count
			FROM `order`
			WHERE ' . self::_buildDateFilter($this->startDate, $this->endDate, '`order`.date') . '
				AND ' . self::_buildCommunityFilter($this->communities, '`order`') . '
				AND (`order`.likely_test = FALSE OR `order`.likely_test IS NULL) AND `order`.refunded = TRUE
			GROUP BY id_community, date_group
			';
		return self::formatQueryResults(self::_getMySQLQuery($q), 'id_community', 'date_group', 'count');
	}
	public function _newUsersQuery() {
		$periodFormat = self::_getPeriodFormat($this->period);
		// TODO(jtratner): pre-calculate first order to speed up this query
		$q = '
			SELECT
				id_community,
				DATE_FORMAT(`order`.date, "' . $periodFormat . '") date_group,
				COUNT(DISTINCT `order`.phone) count
			FROM `order`
			INNER JOIN (SELECT phone, MIN(id_order) first_order_id FROM `order` GROUP BY phone
				) UserWithFirstOrder
				ON UserWithFirstOrder.first_order_id = `order`.id_order
			WHERE ' . self::_buildDateFilter($this->startDate, $this->endDate, '`order`.date') . '
				AND ' . self::_buildCommunityFilter($this->communities, '`order`') . '
				AND ' . self::_buildOrderFilter('`order`') . '
			GROUP BY id_community, date_group
			';
		return self::formatQueryResults(self::_getMySQLQuery($q), 'id_community', 'date_group', 'count');
	}
	public function _repeatUserQuery() {
		$periodFormat = self::_getPeriodFormat($this->period);
		// TODO(jtratner): pre-calculate first order to speed up this query
		$q = '
			SELECT
				id_community,
				DATE_FORMAT(`order`.date, "' . $periodFormat . '") date_group,
				COUNT(DISTINCT `order`.phone) count
			FROM `order`
			INNER JOIN (SELECT phone, MIN(id_order) first_order_id FROM `order` GROUP BY phone
				) UserWithFirstOrder
				ON UserWithFirstOrder.phone = `order`.phone
			WHERE ' . self::_buildDateFilter($this->startDate, $this->endDate, '`order`.date') . '
				AND ' . self::_buildCommunityFilter($this->communities, '`order`') . '
				AND ' . self::_buildOrderFilter('`order`') . '
				AND UserWithFirstOrder.first_order_id != `order`.id_order
			GROUP BY id_community, date_group
			';
		return self::formatQueryResults(self::_getMySQLQuery($q), 'id_community', 'date_group', 'count');
	}
	public function _repeatOrderQuery() {
		$periodFormat = self::_getPeriodFormat($this->period);
		// TODO(jtratner): pre-calculate first order to speed up this query
		$q = '
			SELECT
				id_community,
				DATE_FORMAT(`order`.date, "' . $periodFormat . '") date_group,
				COUNT(*) count
			FROM `order`
			INNER JOIN (SELECT phone, MIN(id_order) first_order_id FROM `order` GROUP BY phone
				) UserWithFirstOrder
				ON UserWithFirstOrder.phone = `order`.phone
			WHERE ' . self::_buildDateFilter($this->startDate, $this->endDate, '`order`.date') . '
				AND ' . self::_buildCommunityFilter($this->communities, '`order`') . '
				AND ' . self::_buildOrderFilter('`order`') . '
				AND UserWithFirstOrder.first_order_id != `order`.id_order
			GROUP BY id_community, date_group
			';
		return self::formatQueryResults(self::_getMySQLQuery($q), 'id_community', 'date_group', 'count');
	}
	public static function _getNumberColumn($colType, $tableName) {
		switch($colType) {
		case 'gross-revenue':
			return 'ROUND(SUM(' . $tableName . '.final_price), 2)';
			break;
		case 'delivery-fee':
			return 'ROUND(SUM(' . $tableName . '.delivery_fee), 2)';
			break;
		case 'tips':
			return 'ROUND(SUM((CASE ' . $tableName . '.tip_type
							   WHEN "percent" THEN ' . $tableName . '.price_plus_delivery_markup * ' . $tableName . '.tip / 100
							   WHEN "number" THEN ' . $tableName . '.tip END)), 2)';
			break;
		case 'users':
			return 'COUNT(DISTINCT ' . $tableName . '.id_user)';
			break;
		case 'count':
			return 'COUNT(*)';
			break;
		default:
			throw new MetricsHttpException(500, 'got unknown type: ' . $colType);
		}
	}
	public function _repeatRevenueQuery( $colType) {
		$periodFormat = self::_getPeriodFormat($this->period);
		// TODO(jtratner): pre-calculate first order to speed up this query
		$q = '
			SELECT
				id_community,
				DATE_FORMAT(`order`.date, "' . $periodFormat . '") date_group,
				' . self::_getNumberColumn($colType, '`order`') . ' count
			FROM `order`
			INNER JOIN (SELECT phone, MIN(id_order) first_order_id FROM `order` GROUP BY phone
				) UserWithFirstOrder
				ON UserWithFirstOrder.phone = `order`.phone
			WHERE ' . self::_buildDateFilter($this->startDate, $this->endDate, '`order`.date') . '
				AND ' . self::_buildCommunityFilter($this->communities, '`order`') . '
				AND ' . self::_buildOrderFilter('`order`') . '
				AND UserWithFirstOrder.first_order_id != `order`.id_order
			GROUP BY id_community, date_group
			';
		return self::formatQueryResults(self::_getMySQLQuery($q), 'id_community', 'date_group', 'count', false);
	}

	public function _newOrdersQuery() {
		$periodFormat = self::_getPeriodFormat($this->period);
		// TODO(jtratner): pre-calculate first order to speed up this query
		$q = '
			SELECT
				id_community,
				DATE_FORMAT(`order`.date, "' . $periodFormat . '") date_group,
				COUNT(*) count
			FROM `order`
			INNER JOIN (SELECT phone, MIN(id_order) first_order_id FROM `order` GROUP BY phone
				) UserWithFirstOrder
				ON UserWithFirstOrder.first_order_id = `order`.id_order
			WHERE ' . self::_buildDateFilter($this->startDate, $this->endDate, '`order`.date') . '
				AND ' . self::_buildCommunityFilter($this->communities, '`order`') . '
				AND ' . self::_buildOrderFilter('`order`') . '
			GROUP BY id_community, date_group
			';
		return self::formatQueryResults(self::_getMySQLQuery($q), 'id_community', 'date_group', 'count');
	}
	public function _firstOrderRevenueQuery( $colType) {
		$periodFormat = self::_getPeriodFormat($this->period);
		// TODO(jtratner): pre-calculate first order to speed up this query
		$q = '
			SELECT
				id_community,
				DATE_FORMAT(`order`.date, "' . $periodFormat . '") date_group,
				' . self::_getNumberColumn($colType, '`order`') . ' count
			FROM `order`
			INNER JOIN (SELECT phone, MIN(id_order) first_order_id FROM `order` GROUP BY phone
				) UserWithFirstOrder
				ON UserWithFirstOrder.first_order_id = `order`.id_order
			WHERE ' . self::_buildDateFilter($this->startDate, $this->endDate, '`order`.date') . '
				AND ' . self::_buildCommunityFilter($this->communities, '`order`') . '
				AND ' . self::_buildOrderFilter('`order`') . '
			GROUP BY id_community, date_group
			';
		return self::formatQueryResults(self::_getMySQLQuery($q), 'id_community', 'date_group', 'count', false);
	}
	public function _ordersByHourQuery() {
		$q = '
			SELECT
				id_community,
				DATE_FORMAT(`order`.date, "%Hh") hour_of_day,
				MIN(date) start_date,
				COUNT(*) count
			FROM `order`
			WHERE ' . self::_buildDateFilter($this->startDate, $this->endDate, '`order`.date') . '
				AND ' . self::_buildCommunityFilter($this->communities, '`order`') . '
				AND ' . self::_buildOrderFilter('`order`') . '
			GROUP BY id_community, hour_of_day
			';
		return self::formatQueryResults(self::_getMySQLQuery($q), 'id_community', 'hour_of_day', 'count');
	}
}
