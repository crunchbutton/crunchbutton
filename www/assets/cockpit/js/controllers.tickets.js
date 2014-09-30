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


NGApp.controller('SideChatCtrl', function($scope, TicketService) {
	$scope.params = {
		status: 'open'
	};
	
	TicketService.list($scope.params, function(tickets) {
		$scope.tickets = tickets;
	});
});