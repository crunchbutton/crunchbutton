NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/tickets', {
			action: 'tickets',
			controller: 'TicketsCtrl',
			templateUrl: 'assets/view/tickets.html',
			reloadOnSearch: false

		}).when('/ticket/:id', {
			action: 'ticket',
			controller: 'TicketCtrl',
			templateUrl: 'assets/view/tickets-ticket.html'
		});
}]);

NGApp.controller('TicketsCtrl', function ($rootScope, $scope, TicketService, ViewListService) {
	$rootScope.title = 'Tickets';

	angular.extend($scope, ViewListService);

	$scope.view({
		scope: $scope,
		watch: {
			search: '',
			status: 'open',
			admin: 'all'
		},
		update: function() {
			TicketService.list($scope.query, function(d) {
				$scope.lotsoftickets = d.results;
				$scope.complete(d);
			});
		}
	});
});


NGApp.controller('TicketCtrl', function($scope, $rootScope, $interval, $routeParams, OrderService, TicketService, MapService, SocketService) {

	$rootScope.title = 'Ticket #' + $routeParams.id;
	$scope.loading = true;

	SocketService.listen('ticket.' + $routeParams.id, $scope)
		.on('update', function(d) {
			update();
		});


	var cleanup;

	var draw = function() {
		if (!$scope.map || !$scope.ticket) {
			return;
		}

		MapService.trackOrder({
			map: $scope.map,
			order: $scope.ticket.order,
			restaurant: {
				location_lat: $scope.ticket.order._restaurant_lat,
				location_lon: $scope.ticket.order._restaurant_lon
			},
			driver: $scope.ticket.order.driver,
			id: 'ticket-driver-location',
			scope: $scope
		});
	};

	var update = function() {
		TicketService.get($routeParams.id, function(ticket) {
			$scope.ticket = ticket;
			$scope.loading = false;

			if (!cleanup) {
				if( ticket && ticket.order ){
					cleanup = $rootScope.$on('order-route-' + ticket.order.id_order, function(event, args) {
						$scope.$apply(function() {
							$scope.eta = args;
						});
						console.debug('Got route update: ', args);
					});
				}
			}
			draw();
		});
	};

	$scope.openCloseTicket = function(){
		TicketService.openClose( $routeParams.id, function() { update(); } );
	}

	$scope.$on('mapInitialized', function(event, map) {
		$scope.map = map;
		MapService.style(map);
		draw();
	});

	update();

	$rootScope.$broadcast('triggerViewTicket', $routeParams.id);

});