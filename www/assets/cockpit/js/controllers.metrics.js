NGApp.config(['$routeProvider', function($routeProvider) {
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

NGApp.controller('MetricsCtrl', function ($rootScope, $scope, $timeout, $location, MetricsService, ViewListService) {
	angular.extend( $scope, ViewListService );
	var defaultOptions = {
			communities: 'active',
			start: '-14d',
			end: 'now',
			period: 'day',
			charts: 'daily-order,new-users,existing-users'
	}
	var allowedKeys = Object.keys(defaultOptions);
	var dataKey = 'crunchbutton-metrics-preferences';
	var rawStorageData = localStorage.getItem(dataKey);
	var queryData = $location.search() || {};
	var storedData = null;
	if(rawStorageData) {
		try {
			storedData = JSON.parse(storedData);
		} catch (e) {
			log_error('EXCEPTION parsing stored data', e);
		}
	}
	storedData = storedData || {};
	// combines options, starting with earliest data source and going to fallback (accepts variadic arguments
	function mergeOptions(keys) { // + variadic data source args
		var k, data;
		var dataLength = arguments.length;
		var outputData = {};
		for(var i=0; i < keys.length; i++) {
			k = keys[i];
			// for each key, try to pull from each
			for(var j=0; j < arguments.length; j++) {
				data = arguments[j];
				if(data && data[k] != null) {
					outputData[k] = data[k];
					break;
				}
			}
			if(outputData[k] === null || outputData[k] === undefined) {
				log_error('ERROR: found no valid data for key: ', k);
			}
		}
		return outputData;
	}
	var options = mergeOptions(allowedKeys, queryData, storedData, defaultOptions);
	function getSelectedCommunities(communityString) {
		var communities = [];
		if(communityString === 'active') {
			var names = Object.keys(App.communities);
			var name, community;
			for(var i = 0; i < names.length; i++) {
				name = names[i];
				community = App.communities[names[i]];
				if(community && community.active) {
					communities.push(community);
				}
			}
		} else {
			communityList = communityString.split(/,/);
			for(var i = 0; i < communityList.length; i++) {
				var id_community = parseInt(communityList[i]);
				if(!isNaN(id_community)) {
					community = App.communities[App.community_name_by_id[id_community]];
					if(community) {
						communities.push(community);
					}
				} else {
					log_error('got non numeric community id: ', id_community);
				}
			}
		}
		return communities;
	}
	$scope.view({
		scope: $scope,
		watch: options
	});
	var initVars = function() {
		$scope.data = [];
		$scope.labels = [];
	}
	
	// doesnt seem to work. not sure why
	$scope.colours = {
		fillColor: "rgba(70,191,189,0.2)",
		strokeColor: "rgba(70,191,189,1)",
		pointColor: "rgba(70,191,189,1)",
		pointStrokeColor: "#fff",
		pointHighlightFill: "#fff",
		pointHighlightStroke: "rgba(70,191,189,0.8)"
	};
	
	initVars();

	MetricsService.get({id_metrics: 'example', 'days': 4000}, function(response) {
		initVars();
		var keys = [];
		for (var i in response.data) {
			for (var x in response.data[i]) {
				keys.push(x);
			}
			break;
		}
		console.log(keys);
		for (var i in response.data) {
			$scope.data.push(response.data[i][keys[0]]);
			$scope.labels.push(response.data[i][keys[1]]);
		}
		$scope.data = [$scope.data];
		console.log($scope.data);
	});

});

NGApp.controller('MetricsViewCtrl', function () {
	// get a specific metric view
});
