NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/support', {
			action: 'support',
			controller: 'SupportCtrl',
			templateUrl: 'assets/view/support.html',
			title: 'Support',
			reloadOnSearch: false
		});
}]);


NGApp.controller('SideTicketsCtrl', function($scope, $rootScope, TicketService, TicketViewService) {
	$scope.params = {
		status: 'open'
	};
	
	var getTickets = function() {
		console.debug('Updating support tickets...');
		TicketService.shortlist($scope.params, function(tickets) {
			TicketViewService.scope.tickets = tickets.results;
		});
	};

	if (!TicketViewService.scope.tickets) {
		getTickets();
	}

	$rootScope.$watch('supportMessages', function(newValue, oldValue) {
		if (!newValue) {
			return;
		}
		if (!oldValue || newValue.count != oldValue.count) {
			getTickets();
		}
	});
});

NGApp.controller('SideTicketCtrl', function($scope, $rootScope, TicketService, TicketViewService) {
	
	var loaded = false;

	var loadTicket = function(id) {
		TicketService.get(id, function(ticket) {
			TicketViewService.scope.ticket = ticket;
			TicketViewService.scroll(!loaded);
			loaded = true;
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


NGApp.controller('SupportCtrl', function($scope, $rootScope, TicketService, TicketViewService, CallService) {
	$scope.ticketparams = {
		status: 'open'
	};
	$scope.callparams = {
		status: ['in-progress','ringing'],
	};

	$rootScope.$watch('supportMessages', function(newValue, oldValue) {
		if (!newValue) {
			return;
		}
		if (!oldValue || newValue.count != oldValue.count) {
			console.debug('Updating support tickets...');
			TicketService.list($scope.ticketparams, function(d) {
				$scope.lotsoftickets = d.results;
			});
		}
	});
	
	TicketService.list($scope.ticketparams, function(d) {
		$scope.lotsoftickets = d.results;
	});

	CallService.list($scope.callparams, function(d) {
		$scope.calls = d.results;
	});
});
