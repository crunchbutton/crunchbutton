NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/support', {
			redirectTo: '/tickets'
		})
		.when('/tickets', {
			action: 'tickets',
			controller: 'TicketsCtrl',
			templateUrl: 'assets/view/tickets.html',
			title: 'Support'
		})
		.when('/ticket/:id', {
			action: 'ticket',
			controller: 'TicketsTicketCtrl',
			templateUrl: 'assets/view/tickets-ticket.html'
		});
}]);


NGApp.controller('SideTicketsCtrl', function($scope, $rootScope, TicketService, TicketViewService) {
	$scope.params = {
		status: 'open'
	};

	$rootScope.$watch('supportMessages', function(newValue, oldValue) {
		if (!newValue) {
			return;
		}
		if (!oldValue || newValue.count != oldValue.count) {
			console.debug('Updating support tickets...');
			TicketService.list($scope.params, function(tickets) {
				TicketViewService.scope.tickets = tickets;
			});
		}
	});
});

NGApp.controller('SideTicketCtrl', function($scope, $rootScope, TicketService, TicketViewService) {

	var loadTicket = function(id) {
		TicketService.get(id, function(ticket) {
			TicketViewService.scope.ticket = ticket;
			TicketViewService.scroll();
		});
	};

	$rootScope.$on('triggerViewTicket', function(e, ticket) {
		loadTicket(ticket == 'refresh' ? TicketViewService.scope.ticket : ticket);
	});
	
	$scope.send = TicketViewService.send;
	
	loadTicket(TicketViewService.scope.viewTicket);
});

NGApp.controller('SideSupportCtrl', function($scope, $rootScope, TicketViewService) {
	TicketViewService.scope = $scope;
	$scope.setViewTicket = TicketViewService.setViewTicket;
});


NGApp.controller('TicketsCtrl', function($scope, $rootScope, TicketService, TicketViewService, CallService) {
	$scope.params = {
		status: 'open'
	};

	$rootScope.$watch('supportMessages', function(newValue, oldValue) {
		if (!newValue) {
			return;
		}
		if (!oldValue || newValue.count != oldValue.count) {
			console.debug('Updating support tickets...');
			TicketService.list($scope.params, function(tickets) {
				$scope.tickets = tickets;
			});
		}
	});
	
	TicketService.list($scope.params, function(tickets) {
		$scope.tickets = tickets;
	});

	CallService.list($scope.params, function(calls) {
		$scope.calls = calls;
	});
});

NGApp.controller('TicketsTicketCtrl', function($scope, $rootScope, $interval, $routeParams, OrderService, TicketService, MapService) {
	
	$rootScope.title = 'Ticket #' + $routeParams.id;
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
			$scope.ready = true;
			
			if (!cleanup) {
				cleanup = $rootScope.$on('order-route-' + ticket.order.id_order, function(event, args) {
					$scope.$apply(function() {
						$scope.eta = args;
					});
					console.debug('Got route update: ', args);
				});
			}
			draw();
		});
	};
	
	$scope.$on('mapInitialized', function(event, map) {
		$scope.map = map;
		MapService.style(map);
		draw();
	});

	$scope.updater = $interval(update, 5000);
	$scope.$on('$destroy', function() {
		$interval.cancel($scope.updater);
		if (cleanup) {
			cleanup();
		}
	});
	
	update();
	
	$rootScope.$broadcast('triggerViewTicket', $routeParams.id);

});