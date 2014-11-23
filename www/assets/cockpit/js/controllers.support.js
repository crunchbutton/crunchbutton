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

NGApp.controller('TicketsTicketCtrl', function($scope, $rootScope, $interval, $routeParams, TicketService, MapService) {
	
	
	
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
	});
	
	$scope.$on('order-route-' + $routeParams.id, function(event, args) {
		var eta = {
			customer: {},
			restaurant: {},
			total: {}
		};
		if (args.length == 2) {
			eta.restaurant.distance = args[0].distance.value * 0.000621371;
			eta.restaurant.duration = args[0].duration.value/60;
			
			eta.customer.distance = args[1].distance.value;
			eta.customer.duration = args[1].duration.value/60;
			
			eta.total.duration = eta.restaurant.duration + eta.customer.duration;
			eta.total.distance = eta.restaurant.distance + eta.customer.distance;

		} else {
			eta.customer.distance = args[0].distance.value * 0.000621371;
			eta.customer.duration = args[0].duration.value/60;
			
			eta.total.duration = eta.customer.duration;
			eta.total.distance = eta.customer.distance;
		}
		

		$scope.$apply(function() {
			$scope.eta = eta;
		});

		console.debug('Got route update: ', eta);
	});
	
	update();
	
	
	
	
	
	$rootScope.$broadcast('triggerViewTicket', $routeParams.id);

});