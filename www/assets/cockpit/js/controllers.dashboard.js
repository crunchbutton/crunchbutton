NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/dashboard', {
			action: 'dashboard',
			controller: 'DashboardCtrl',
			templateUrl: '/assets/view/dashboard.html'
		})
		.when('/dashboard/beta', {
			action: 'dashboard-beta',
			controller: 'DashboardBetaCtrl',
			templateUrl: '/assets/view/dashboard-beta.html'
		});
}]);

NGApp.controller('DashboardCtrl', function ($rootScope, $scope, DashboardService) {
	$scope.dashboards = null;
	$scope.loading = true;

	DashboardService.get(null, function(dashboards) {
		$scope.dashboards = dashboards;
		$scope.loading = false;
	});
});

NGApp.controller('DashboardBetaCtrl', function ($rootScope, $scope, $timeout, DashboardService, CommunityService, TicketService) {

	$scope.loading = true;

	var communitiesWithShift = [];

	// first load
	$scope.selectCommunitiesWithShift = function(){
		$scope.selectNoneCommunity();
		for( x in communitiesWithShift ){
			$scope.options.communities.push( communitiesWithShift[ x ] );
		}
	}

	DashboardService.communities_with_shift(function(data) {
		$scope.dashboard = data;
		$scope.loading = false;
		for( x in $scope.dashboard ){
			if($scope.dashboard[ x ].community){
				$scope.options.communities.push( $scope.dashboard[ x ].community.permalink );
				communitiesWithShift.push($scope.dashboard[ x ].community.permalink);
			}
		}
	});

	$scope.selectNoneCommunity = function(){
		$scope.options.communities = [];
	}

	$scope.selectAllCommunities = function(){
		$scope.selectNoneCommunity();
		for( x in $scope.communities ){
			if( $scope.communities[ x ].permalink ){
				$scope.options.communities.push( $scope.communities[ x ].permalink );
			}
		}
	}

	$scope.options = { communities: [], autoreaload: true };

	$scope.loadCommunities = function(){
		DashboardService.communities($scope.options.communities, function(data){
			$scope.dashboard = data;
		});
	}

	CommunityService.listPermalink( function( json ){
		$scope.communities = json;
	} );

	$scope.formatDate = function(date){
		return new Date(date);
	}

	$scope.modalOrders = function(orders, title, driver){
		App.dialog.show('.dashboard-orders-dialog-container');
		$scope.modal = {};
		$scope.modal.title = title;
		$scope.modal.orders = orders;
		$scope.modal.driver = driver;
	}

	$scope.modalDrivers = function(drivers, title){
		App.dialog.show('.dashboard-drivers-dialog-container');
		$scope.modal = {};
		$scope.modal.title = title;
		$scope.modal.drivers = drivers;
	}

	var getTickets = function() {
		TicketService.list_beta( { status: 'open' }, function(tickets) {
			$scope.tickets = tickets.results;
		});
	};

	$scope.closeTicket = function( id_support ){
		TicketService.openClose( id_support, function() { getTickets(); } );
	}

	var getLastOrders = function(){
		DashboardService.chart_last_orders(function(data){
			$scope.chart_last_orders = data;
		})
	}

	getLastOrders();
	getTickets();

	var startTimer = function(){
		var timer = $timeout( function(){
			if($scope.options.autoreaload){
				$scope.loadCommunities();
				getTickets();
				getLastOrders();
			}
			startTimer()
		}, 20000);
	}
	startTimer();

	var tick = function() {
		$scope.clock = Date.now();
		$timeout(tick, 1000);
	}

	$timeout(tick, 1000);
		$scope.$on( "$destroy", function( event ) {
			$timeout.cancel( timer );
			$timeout.cancel( tick );
	} );
});