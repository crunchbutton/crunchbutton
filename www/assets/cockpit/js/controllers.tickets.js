NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/tickets', {
			action: 'tickets',
			controller: 'SupportCtrl',
			templateUrl: 'assets/view/support.html'

		}).when('/ticket/:id', {
			action: 'ticket',
			controller: 'Support',
			templateUrl: 'assets/view/maps-drivers.html'

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
	TicketService.get(TicketViewService.scope.viewTicket, function(ticket) {
		$scope.ticket = ticket;
	});
});

NGApp.controller('SideSupportCtrl', function($scope, $rootScope, TicketViewService) {
	TicketViewService.scope = $scope;
	$scope.setViewTicket = TicketViewService.setViewTicket;
});


NGApp.factory('TicketViewService', function($rootScope, $resource, $routeParams) {

	var service = {};
	
	service.setViewTicket = function(id) {
		service.scope.viewTicket = id;
	};

	return service;

});