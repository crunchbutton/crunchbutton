NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/support', {
			redirectTo: '/tickets'
		})
		.when('/tickets', {
			action: 'tickets',
			controller: 'TicketsViewCtrl',
			templateUrl: 'assets/view/tickets-view.html'
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


NGApp.controller('TicketsViewCtrl', function($scope, $rootScope, TicketService, TicketViewService) {
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
});

NGApp.controller('TicketsTicketCtrl', function($scope, $rootScope, $routeParams, TicketService) {
	$rootScope.$broadcast('triggerViewTicket', $routeParams.id);

	TicketService.get($routeParams.id, function(ticket) {
		$scope.ticket = ticket;
		$scope.ready = true;
	});
});