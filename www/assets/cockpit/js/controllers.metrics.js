/* global NGApp, App, Chart, angular */
NGApp.config(['$routeProvider', function ($routeProvider) {
	$routeProvider
		.when('/metrics', {
			action: 'metrics',
			controller: 'MetricsCtrl',
			templateUrl: 'assets/view/metrics.html',
			reloadOnSearch: false

		}).when('/metrics/:id', {
			action: 'metrics',
			controller: 'MetricsViewCtrl',
			templateUrl: 'assets/view/metrics-view.html'
		});
}]);

NGApp.controller('MetricsCtrl', function ($rootScope, $scope, $timeout, $location, MetricsService, ViewListService, $http) {
	// pretty straightforward, we always want the charts to have zero as base
	Chart.defaults.global.scaleBeginAtZero = true;
	Chart.defaults.global.animation = false;
	Chart.defaults.global.maintainAspectRatio = false;
	Chart.defaults.global.responsive = true;
	Chart.defaults.global.scaleFontSize = 10;
	console.log('METRICSCTRL');
	angular.extend($scope, ViewListService);
	$scope.showCharts = 0;
	$scope.sortMethods = [
		{'kind': 'min', 'description': 'Minimum Value'},
		{'kind': 'max', 'description': 'Maximum Value'},
		{'kind': 'avg', 'description': 'Average Value'},
		{'kind': 'first', 'description': 'Last Value'},
		{'kind': 'last', 'description': 'First Value'}
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
		{'symbol': 'h', 'description': 'Hours'},
		{'symbol': 'd', 'description': 'Days'},
		{'symbol': 'w', 'description': 'Weeks'},
		{'symbol': 'M', 'description': 'Months'},
		{'symbol': 'Y', 'description': 'Years'}
	]
	$scope.settings = {
		separateCharts: true,
		charts: [
			{'type': 'orders', 'orderMethod': 'last', 'orderDirection': 'asc'},
			{'type': 'new-users'},
			{'type': 'gross-revenue'}
		],
		period: 'd',
		start: '-30d',
		end: '-15d'
	};
	var defaultOptions = {
		communities: 'active',
		start: '-14d',
		end: 'now',
		period: 'day',
		charts: 'daily-order,new-users,existing-users'
	};
	var resetData = function () {
		$scope.loadedChartTypes = {};
		$scope.chartData = {};
	};
	var timer = null;
	var refreshOnTimer = function () {
		if (timer) {
			$timeout.cancel(timer);
		}
		timer = $timeout($scope.refreshData, 1000);
	}
	$scope.unselectAllCommunities = function () {
		console.log('unselect ALL communities');
		Object.keys($scope.allowedCommunities).forEach(function (k) { $scope.allowedCommunities[k].selected = false; });
		$scope.orderSelectedCommunities();
	}
	// TODO: Figure out how to avoid the multiple refreshes here!
	$scope.$watch('settings.start', refreshOnTimer);
	$scope.$watch('settings.end', refreshOnTimer);
	resetData();
	$scope.refreshData = function () {
		console.log('REFRESH DATA');
		resetData();
		// make sure we don't double refresh
		$timeout.cancel(timer);
		$scope.settings.charts.forEach(function (chartOption) {
			$scope.updateChartOption(chartOption);
		});
	}
	$scope.addChart = function () {
		$scope.settings.charts.push({});
	}
	$scope.updateChartOption = function (chartOption) {
		var type = chartOption.type;
		if (!type) {
			console.debug('not loading chart data - no type selected');
			return;
		}
		// only reference current loaded array, in case data gets reset before the load is finished.
		var loaded = $scope.loadedChartTypes;
		var chartData = $scope.chartData;
		function finalCallback() {
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
					break;
				}
			}
			console.log('ChartData after callback finished: ', $scope.chartData);
		}
		if (loaded[type]) {
			finalCallback();
			return;
		}
		MetricsService.getChartData(type, chartData, $scope.settings).then(function (result) {
			if ($scope.chartData !== chartData) {
				console.debug('chart data changed since load. Not processing further.');
				return;
			}
			// fill if we have no sort order yet
			if (!$scope.orderedCommunities) {
				$scope.orderedCommunities = Object.keys($scope.allowedCommunities).map(function (k) { return $scope.allowedCommunities[k]; });
			}
			loaded[type] = true;
			finalCallback();
		}, function (err) { console.log('error on chartOption: ', chartOption, 'error: ', err) });
	};
	$scope.removeChartOption = function (chartOption) {
		var index = $scope.settings.charts.indexOf(chartOption);
		if (index > -1) {
			$scope.settings.charts.splice(index, 1);
		}
		if (chartOption.orderMethod && chartOption.orderDirection) {
			$scope.updateChartOrders(null, true);
		}
	}
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
		$scope.orderSelectedCommunities()
	}
	$scope.orderSelectedCommunities = function () {
		var ordered = [];
		var cID, comm;
		for (var i = 0; i < $scope.communityOrdering.length; i++) {
			cID = $scope.communityOrdering[i];
			if (!cID) {
				console.error('found invalid community ID!', comm);
				continue;
			}
			comm = $scope.allowedCommunities[cID];
			if (comm.selected) {
				ordered.push(comm);
			}
		}
		$scope.orderedCommunities = ordered;
		// If we change the selected communities or community order, need to recalculate combined Charts
		// TODO: Figure out how to make this more natural
		if (!$scope.settings.separateCharts) {
			$scope.calculateCombinedData();
		}
	}
	$scope.toggleCombinedView = function () {
		$scope.settings.separateCharts = !$scope.settings.separateCharts
		if (!$scope.settings.separateCharts) {
			$scope.calculateCombinedData();
		}
	}
	$scope.calculateCombinedData = function (maxSize) {
		// We *assume* communities are already ordered by this point!
		maxSize = maxSize || 5;
		var selectedCommunities = $scope.orderedCommunities;
		var selectedCommunityIDs = selectedCommunities.map(function (c) { return c.id_community; });
		var combinedChartData = MetricsService.combineChartData(selectedCommunityIDs, $scope.chartData);
		var labels, keys, series, comm;
		var allowedCommunities = $scope.allowedCommunities;
		Object.keys(combinedChartData).forEach(function (type) {
			keys = combinedChartData[type].keys.slice(0, maxSize);
			combinedChartData[type].data = combinedChartData[type]['data'].slice(0, maxSize);
			combinedChartData[type].keys = keys;
			series = [];
			for (var i = 0; i < keys.length; i++) {
				comm = allowedCommunities[keys[i]];
				if (comm && comm.name) {
					series.push(comm.name);
				} else {
					series.push('Community: ' + key);
				}
			}
			combinedChartData[type].series = series;
		})
		$scope.combinedChartData = combinedChartData;
	}
	$http.get(App.service + 'metrics/permissions').success(function (data) {
		console.debug('got allowed communities');
		var allowedCommunities = {};
		var comm;
		for (var i = 0; i < data.length; i++) {
			comm = data[i];
			comm.selected = +comm.active === 1 || comm.active === true;
			allowedCommunities[comm.id_community] = comm;
		}
		// [{community: XYZ, id_community: XYZ}, ...]
		$scope.allowedCommunities = allowedCommunities;
		$scope.multiSelectCommunities = data;
		if ($scope.availableCharts) {
			$scope.refreshData();
		}
		console.log($scope.allowedCommunities);
	}).error(function (err) {
		$scope.allowedCommunities = {};
		console.error('ERROR getting community metrics permissions', err);
	});
	$http.get(App.service + 'metrics/available').success(function (data) {
		$scope.availableCharts = {};
		data.map(function (e) { $scope.availableCharts[e.type] = e; });
		console.log('got available charts: ', data);
		if ($scope.orderedCommunities) {
			$scope.refreshData();
		}
	}).error(function (err) {
		$scope.availableCharts = {};
		console.error('ERROR getting available charts', err);
	});
	var allowedKeys = Object.keys(defaultOptions);
	var dataKey = 'crunchbutton-metrics-preferences';
	var rawStorageData = localStorage.getItem(dataKey);
	var queryData = $location.search() || {};
	var storedData = null;
	if (rawStorageData) {
		try {
			storedData = JSON.parse(storedData);
		} catch (e) {
			console.error('EXCEPTION parsing stored data', e);
		}
	}
	storedData = storedData || {};
	// combines options, starting with earliest data source and going to fallback (accepts variadic arguments
	function mergeOptions(keys) { // + variadic data source args
		var k, data;
		var dataLength = arguments.length;
		var outputData = {};
		for (var i = 0; i < keys.length; i++) {
			k = keys[i];
			// for each key, try to pull from each
			for (var j = 0; j < arguments.length; j++) {
				data = arguments[j];
				if (data && (data[k] !== null && data[k] !== undefined)) {
					outputData[k] = data[k];
					break;
				}
			}
			if (outputData[k] === null || outputData[k] === undefined) {
				console.error('ERROR: found no valid data for key: ', k);
			}
		}
		return outputData;
	}
	var options = mergeOptions(allowedKeys, queryData, storedData, defaultOptions);
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
					console.error('got non numeric community id: ', id_community);
				}
			}
		}
		return communities;
	}
});

NGApp.controller('MetricsViewCtrl', function () {
	// get a specific metric view
});
