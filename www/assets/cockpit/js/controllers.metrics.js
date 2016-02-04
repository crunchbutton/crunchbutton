/* global NGApp, App, Chart, angular, moment */
NGApp.config(['$routeProvider', function ($routeProvider) {
	$routeProvider
		.when('/metrics', {
			action: 'metrics',
			controller: 'MetricsCtrl',
			templateUrl: '/assets/view/metrics.html',
			reloadOnSearch: false

		});
}]);

NGApp.controller('MetricsCtrl', function ($rootScope, $scope, $timeout, $location, MetricsService, CSVService, $http) {

	// pretty straightforward, we always want the charts to have zero as base
	Chart.defaults.global.scaleBeginAtZero = true;
	Chart.defaults.global.animation = false;
	Chart.defaults.global.maintainAspectRatio = false;
	Chart.defaults.global.responsive = true;
	Chart.defaults.global.scaleFontSize = 10;

	$scope.showCharts = 0;
	$scope.sortMethods = [
		{'kind': 'last', 'description': 'Last Value'},
		{'kind': 'min', 'description': 'Minimum Value'},
		{'kind': 'max', 'description': 'Maximum Value'},
		{'kind': 'avg', 'description': 'Average Value'},
		{'kind': 'first', 'description': 'Earliest Value'},
	];
	$scope.sortDirections = [
		{'kind': 'asc', 'description': 'Ascending'},
		{'kind': 'desc', 'description': 'Descending'}
	];
	$scope.setScales = [
		{'kind': true, 'description': 'Use uniform scale'},
		{'kind': false, 'description': 'Scale each chart individually'}
	];
	$scope.availablePeriods = [
    // this is complicated for start / end, but we want to support this for dumping data
		{'symbol': 'h', 'description': 'By Hour', 'title': 'Hour'},
		{'symbol': 'd', 'description': 'By Day', 'title': 'Day'},
		{'symbol': 'w', 'description': 'By Week', 'title': 'Week'},
		{'symbol': 'M', 'description': 'By Month', 'title': 'Month'},
		{'symbol': 'Y', 'description': 'By Year', 'title': 'Year'}
	];
	$scope.chartFormats = [
		{'kind': 'line', 'description': 'Line Chart'},
		{'kind': 'bar', 'description': 'Bar Chart'}
	];

	var queryCommunities = ( $location.search().communities ? $location.search().communities : null );
	function defaultSettings () {
		return {
			communities: [],
			combineCharts: false,
			showEmpty: false,
			maxCombinedCommunities: 5,
			charts: [
				{'type': 'orders', 'orderMethod': 'last', 'orderDirection': 'asc', 'format': 'line'},
			],
			start: moment().subtract(14, 'days').toDate(),
			end: new Date(),
			period: 'd',
		};
	}
	$scope.settings = defaultSettings();

	$scope.resetSettings = function () {
		sessionStorage.removeItem(dataKey);
		$scope.settings = defaultSettings();
		$scope.persistSettings();
		$scope.refreshData();
		$location.url('/metrics').replace();
	};
	/**
	 * Saves settings into query string and updates with angular location.replace
	 * @param {boolean} skipStorage (optional) - if true, does not persist
	 * settings into sessionStorage. Useful if you want to make sure just
	* visiting a metrics page does not overwrite your last view.
	 **/
	$scope.persistSettings = function (skipStorage) {
		$scope.serializedSettings = MetricsService.serializeSettings($scope.settings, $scope.settings.communities);
		// $scope.roundTrippedSerialized = MetricsService.serializeSettings(MetricsService.deserializeSettings($scope.serializedSettings, $scope.allowedCommunities), $scope.multiSelectCommunities, $scope.allowedCommunities);
		Object.keys($scope.serializedSettings).map(function (k) {
			var data = $scope.serializedSettings[k];
			if (typeof data === 'string' || typeof data === 'boolean' || typeof data === 'number') {
				$location.search(k, data);
			} else {
				// console.warn('invalid data in persistence string (expecting only strings)', data);
			}
		});
		if (!skipStorage) {
			sessionStorage.setItem(dataKey, JSON.stringify($scope.serializedSettings));
			$location.replace();
		}
	};
	var resetData = function () {
		$scope.loadedChartTypes = {};
		$scope.chartData = {};
		$scope.combinedChartData = {};
	};
	var timer = null;
	var refreshOnTimer = function (reason) {
		if (timer) {
			$timeout.cancel(timer);
		}
		timer = $timeout(function () { $scope.refreshData('timed ' + reason); }, 1000);
	};
	$scope.unselectAllCommunities = function () {
		// console.log('unselect ALL communities');
		// multiselect MUST reference the same objects as allowed comunities
		Object.keys($scope.allowedCommunities).forEach(function (k) { if ($scope.allowedCommunities[k]) { $scope.allowedCommunities[k].selected = false; }});
		$scope.orderSelectedCommunities();
	};
	// TODO: Figure out how to avoid the multiple refreshes here!
	resetData();
	$scope.refreshData = function (reason) {
		// console.info('REFRESH DATA BECAUSE OF: ', reason);
		resetData();
		// make sure we don't double refresh
		$timeout.cancel(timer);
		$scope.settings.charts.forEach(function (chartOption) {
			$scope.updateChartOption(chartOption);
		});
		$scope.calculateCombinedData();
	};
	$scope.addChart = function () {
		$scope.settings.charts.push({'format': 'line', 'uniformScale': false});
	};
	$scope.updateChartOption = function (chartOption, changed) {
		var type = chartOption.type;
		if (!type) {
			// console.debug('not loading chart data - no type selected');
			return;
		}
		// only reference current loaded array, in case data gets reset before the load is finished.
		var loaded = $scope.loadedChartTypes;
		var chartData = $scope.chartData;
		function finalCallback() {
			// console.log('chartdata pre-change: ', chartData);
			if ($scope.chartData !== chartData) {
				// console.info('chart data has changed since callback, not updating');
				return;
			}
			// grab current chart option (so we don't overwrite any sorts on a late call)
			var opt;
			for (var i = 0; i < $scope.settings.charts.length; i++) {
				opt = $scope.settings.charts[i];
				if (opt.type === type) {
					if (opt.uniformScale) {
						MetricsService.joinChartScales(chartData, type);
					} else {
						MetricsService.resetScales(chartData, type);
					}
					// we assume these get cleared if we switch to another option
					if (opt.orderMethod && opt.orderDirection) {
						$scope.updateChartOrders(opt);
					}
					$scope.orderSelectedCommunities();
					break;
				}
			}
			// console.log('chartData post-change: ', chartData);
			// // console.debug('ChartData after callback finished: ', $scope.chartData);
			if (!$scope.orderedCommunities) {
				$scope.orderSelectedCommunities();
			}
			$scope.calculateCombinedData();
			$scope.persistSettings();
		}
		if (loaded[type]) {
			finalCallback();
			return;
		}
		MetricsService.getChartData(type, chartData, $scope.settings).then(function (result) {
			if ($scope.chartData !== chartData) {
				// console.debug('chart data changed since load. Not processing further.');
				return;
			}
			// fill if we have no sort order yet
			if (!$scope.orderedCommunities) {
				$scope.orderedCommunities = Object.keys($scope.allowedCommunities).map(function (k) { return $scope.allowedCommunities[k]; });
			}
			loaded[type] = true;
			finalCallback();
		}, function (err) { console.warn('error on chartOption: ', chartOption, 'error: ', err); });
	};
	$scope.removeChartOption = function (chartOption) {
		var index = $scope.settings.charts.indexOf(chartOption);
		if (index > -1) {
			$scope.settings.charts.splice(index, 1);
		}
		if (chartOption.orderMethod && chartOption.orderDirection) {
			$scope.updateChartOrders(null, true);
		}
	};
	/**
	 * Orders communities by the selected chart order method and direction
	 **/
	$scope.updateChartOrders = function (chartOption, clearOthers) {
		var communityOrdering;
		// grab current chart ordering if we're just updating with no specified chart
		if (!chartOption && !clearOthers) {
			var co;
			for (var i = 0; i < $scope.settings.charts.length; i++) {
				co = $scope.settings.charts[i];
				if (co.orderMethod && co.orderDirection) {
					chartOption = co;
					break;
				}
			}
		}
		if (chartOption) {
			if (!chartOption.orderDirection) {
				chartOption.orderDirection = 'desc';
			}
			$scope.communityOrdering = MetricsService.orderChartData(
				$scope.chartData,
				chartOption.type,
				chartOption.orderMethod,
				chartOption.orderDirection
			);
		} else {
			$scope.communityOrdering = Object.keys($scope.allowedCommunities);
		}
		if (clearOthers) {
			// clear other orders from other options for clarity
			$scope.settings.charts.forEach(function (opt) {
				if (opt !== chartOption) {
					delete(opt.orderMethod);
					delete(opt.orderDirection);
				}
			});
		}
		$scope.orderSelectedCommunities();
	};

	$scope.updateSelected = function () {
		// console.log($scope.multiSelectCommunities);
		$scope.orderSelectedCommunities();
		$scope.persistSettings();
	};
	$scope.$watch('multiSelectCommunities', $scope.updateSelected);

	$scope.orderSelectedCommunities = function () {
		var communityOrdering = $scope.communityOrdering;
		if (!communityOrdering) {
			// console.info('no ordering yet, cannot order anything (just picking selected)');
			if (!$scope.allowedCommunities) {
				// console.warn('no communities, cannot do anything');
				return
			}
			// default to everything if we don't have communities, so we only display selected
			communityOrdering = Object.keys($scope.allowedCommunities);
		}
		var ordered = [];
		var cID, comm;
		for (var i = 0; i < communityOrdering.length; i++) {
			cID = communityOrdering[i];
			if (!cID) {
				// console.error('found invalid community ID!', comm);
				continue;
			}
			if( $scope.allowedCommunities[cID] ){
				comm = $scope.allowedCommunities[cID];
				if (comm.selected) {
					ordered.push(comm);
				}
			}

		}
		$scope.orderedCommunities = ordered;
		// If we change the selected communities or community order, need to recalculate combined Charts
		// TODO: Figure out how to make this more natural
		if ($scope.settings.combineCharts) {
			$scope.calculateCombinedData();
		}
		$scope.persistSettings();
	};
	// TODO(jtratner): find a way to NOT have to reload the data when show Empty changes (e.g., keep raw data or something...)
	$scope.showEmptyChanged = function () {
		$scope.refreshData('show empty changed');
	};
	$scope.calculateCombinedData = function (maxSize) {
		// We *assume* communities are already ordered by this point!
		maxSize = maxSize || 5;
		if (!$scope.orderedCommunities || !$scope.chartData) {
			// console.log('not all data prepped - not calculating combined data');
			return;
		}
		// console.log('calculating combined data');
		var selectedCommunities = $scope.orderedCommunities;
		var selectedCommunityIDs = selectedCommunities.map(function (c) { return c.id_community; });
		var combinedChartData = MetricsService.combineChartData(selectedCommunityIDs, $scope.chartData);
		if (Object.keys(combinedChartData).length === 0) {
			// console.warn('got no combined chart data back from metrics service!');
		} else {
			// console.log('setting types for combinedChartData', combinedChartData);
		}
		var labels, keys, series, comm;
		var allowedCommunities = $scope.allowedCommunities;
		Object.keys(combinedChartData).forEach(function (type) {
			keys = combinedChartData[type].keys.slice(0, maxSize);
			combinedChartData[type].data = combinedChartData[type].data.slice(0, maxSize);
			combinedChartData[type].keys = keys;
			series = [];
			for (var i = 0; i < keys.length; i++) {
				comm = allowedCommunities[keys[i]];
				if (comm && comm.name) {
					series.push(comm.name);
				} else {
					series.push('Community: ' + keys[i]);
				}
			}
			combinedChartData[type].series = series;
		});
		$scope.combinedChartData = combinedChartData;
	};

	$http.get(App.service + 'metrics/permissions').success(function (data) {
		// console.debug('got allowed communities');
		var allowedCommunities = {};
		var comm;
		for (var i = 0; i < data.length; i++) {
			comm = data[i];
			comm.selected = +comm.active === 1 || comm.active === true;
			// allowedCommunities[comm.id_community] = comm;
		}

		$scope.communities = data;

		// {cID : {community: XYZ, id_community:XYZ}}
		$scope.allowedCommunities = allowedCommunities;
		// console.log($scope.allowedCommunities);
		// this is just allowedCommunities values
		// [{community: XYZ, id_community: XYZ}, ...]
		$scope.multiSelectCommunities = data;
		if (getSettingsFromStorage() && !(initialQueryData && Object.keys(initialQueryData).length)) {
			$scope.persistSettings();
		}
		addQueryDataToSettings(initialQueryData);
		$scope.persistSettings(true);
		if ($scope.availableCharts) {
			// $scope.refreshData('allowed communities');
		}

		// select communities from $location.query
		if( queryCommunities ){
			queryCommunities = queryCommunities.split( ',' );
			for( x in $scope.communities ){
				if( queryCommunities.indexOf( ( $scope.communities[ x ].id_community ).toString() ) >= 0 ){
					$scope.communities[ x ].selected = true;
					$scope.settings.communities.push( $scope.communities[ x ] );
				}
			}
			$scope.communityChanged();
		}

		// // console.debug('allowedCommunities: ', $scope.allowedCommunities);
	}).error(function (err) {
		$scope.allowedCommunities = {};
		// console.error('ERROR getting community metrics permissions', err);
	});
	$http.get(App.service + 'metrics/available').success(function (data) {
		$scope.availableCharts = {};
		data.map(function (e) { $scope.availableCharts[e.type] = e; });
		// // console.log('got available charts: ', data);
		if ($scope.orderedCommunities) {
			$scope.refreshData('got available charts');
		}
	}).error(function (err) {
		$scope.availableCharts = {};
		// console.error('ERROR getting available charts', err);
	});
	var dataKey = 'crunchbutton-metrics-preferences';
	function mergeSettings (settings) {
		if (!settings || Object.keys(settings).length === 0) {
			return;
		}
		var changed = false;
		var communities = settings.communities;
		delete(settings.communities);
		if(communities && communities.length) {
			// console.log('settings stuff by community: ', communities);
			// force selections to just those communities that are available
			var communityMap = {};
			communities.forEach(function (cID) { communityMap[cID] = true; });
			Object.keys($scope.allowedCommunities).forEach(function (cID) { if ($scope.allowedCommunities[cID]) { $scope.allowedCommunities[cID].selected = !!communityMap[cID]; }});
			// console.log('communities found: ', communities.map(function (cID) { return $scope.allowedCommunities[cID]; }));
			$scope.updateSelected();
		}
		for (var k in settings) {
			if (settings[k] !== null && settings[k] !== undefined) {
				$scope.settings[k] = settings[k];
				changed = true;
			}
		}
		return changed;
	}
	function getSettingsFromStorage() {
		var rawStorageData = sessionStorage.getItem(dataKey);
		var storedData = null;
		if (rawStorageData) {
			try {
				storedData = JSON.parse(storedData);
			} catch (e) {
				// console.error('EXCEPTION parsing stored settings data', e);
				return;
			}
		}
		if (storedData && Object.keys(storedData).length) {
			try {
				return mergeSettings(MetricsService.deserializeSettings(storedData, $scope.allowedCommunities));
			} catch (e) {
				// console.error('could not deserialize settings from local storage :-(', e);
			}
		}
	}

	$scope.selectNoneCommunity = function(){
		$scope.settings.communities = [];
		$scope.communityChanged();
	}

	$scope.selectAllCommunities = function(){
		var communities = [];
		for( x in $scope.communities ){
			communities.push( $scope.communities[ x ] );
		}
		$scope.settings.communities = communities;
		$scope.communityChanged();
	}

	$scope.communityChanged = function(){
		var allowedCommunities = {};
		for( x in $scope.settings.communities ){
			var community = $scope.settings.communities[x];
			community.selected = true;
			allowedCommunities[community.id_community] = community;
			$scope.allowedCommunities = allowedCommunities;
		}
		$scope.refreshData();
	}

	function addQueryDataToSettings(data) {
		if (!data || Object.keys(data).length === 0) {
			return;
		}
		try {
			return mergeSettings(MetricsService.deserializeSettings(data, $scope.allowedCommunities));
		} catch (e) {
			// console.error('EXCEPTION loading query data', e);
			throw e;
		}
	}
	// capture query right now (in case we overwrite it by changes later on);
	var initialQueryData = $location.search();
	function getSelectedCommunities(communityString) {
		var communities = [];
		var community, i;
		if (communityString === 'active') {
			var names = Object.keys(App.communities);
			var name;
			for (i = 0; i < names.length; i++) {
				name = names[i];
				community = App.communities[names[i]];
				if (community && community.active) {
					communities.push(community);
				}
			}
		} else {
			var communityList = communityString.split(/,/);
			for (i = 0; i < communityList.length; i++) {
				var id_community = parseInt(communityList[i], 10);
				if (!isNaN(id_community)) {
					community = App.communities[App.community_name_by_id[id_community]];
					if (community) {
						communities.push(community);
					}
				} else {
					// console.error('got non numeric community id: ', id_community);
				}
			}
		}
		return communities;
	}
	var NA = 'NA';
	// columnsToRows -  converts a mapping of column => data into a set of
	// rows, based on given columnNames. Order is preserved and
	// non-existent columns are converted to NA
	function columnsToRows(columns) {
		var r, c, col, row;
		var length = 0;
		var colArrays = [];
		var numColumns = columns.length;
		for (var i = 0; i < numColumns; i++) {
			col = columns[i];
			if(col) {
				if (length < col.length) {
					length = col.length;
				}
			}
			colArrays.push(col);
		}
		out = [];
		for (r = 0; r < length; r++) {
			row = [];
			for (c = 0; c < numColumns; c++) {
				col = colArrays[c];
				cell = col ? col[r] : null;
				// let CSVService handle undefined or null here
				row.push(cell);
			}
			out.push(row);
		}
		return out;
	}
	// grabs the first non-empty labels it can find
	function getLabels (chartData, chartTypes) {
		var keys = Object.keys(chartData);
		var data, group, labels;
		for (var k = 0; k < keys.length; k++) {
			group = chartData[keys[k]];
			for (var c = 0; c < chartTypes.length; c++) {
				data = group[chartTypes[c]];
				if (data && data.labels && data.labels.length > 0) {
					return data.labels;
				}
			}
			labels = data && data.labels || [];
			if (labels.length > 0) {
			}
		}
	}
	function periodNameForSymbol (symbol) {
		for (i = 0; i < $scope.availablePeriods.length; i++) {
			if ($scope.availablePeriods[i].symbol === symbol) {
				return $scope.availablePeriods[i].title;
			}
		}
		return 'Unknown Period';
	}
	$scope.exportDataToCSV = function ($event, chart) {
		var i, j, comm, data, k;
		// console.log('starting export');
		var exportData = [];
		// these are arrays because most of the helper code can handle multiple
		// chart types / complex arrays, however joining up labels is
		// problematic so for now we've defaulted to just allowing per-chart
		// export (where backend API guarantee is that all objects will have
		// the same labels in the same order)
		var chartNames = [($scope.availableCharts[chart.type] && $scope.availableCharts[chart.type].description) || 'Unknown Column'];
		var chartTypes = [chart.type];
		var emptyRowData = chartTypes.map(function () { return ''; });
		var periodName = periodNameForSymbol($scope.settings.period);
		var labels = getLabels($scope.chartData, chartTypes);
		// Order of CSV is <community>, <date/time>, <chart1>, <chart2>, etc.
		// e.g., ["Community", "Hour", "All Orders", "New User Orders"]
		var columnNames = ['Community', periodName].concat(chartNames);
		exportData.push(columnNames);
		$scope.orderedCommunities.forEach(function (community) {
			var name = community.name;
			var id_community = community.id_community;
			var chartTypeData = $scope.chartData[id_community];
			var columns = chartTypes.map(function (type) { return chartTypeData[type] && chartTypeData[type].data && chartTypeData[type].data[0]; });
			var rows = columnsToRows(columns);
			if (rows.length === 0) {
				// console.log('no data to export for community ', community);
				labels.forEach(function (label) {
					exportData.push([name, label].concat(emptyRowData));
				});
			} else {
				rows.forEach(function (row, idx) {
					exportData.push([name, labels[idx]].concat(row));
				});
			}
		});
		var anchor = $event.target;
		return CSVService.addCSVToAnchor(anchor, exportData);
	};
	// ng-change does not work for some reason...
	$scope.$watch('setting.start', function () { refreshOnTimer('start date')});
	$scope.$watch('settings.end', function () { refreshOnTimer('end date') });

});
