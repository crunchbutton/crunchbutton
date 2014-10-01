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


NGApp.controller('SideChatCtrl', function($scope, $rootScope, TicketService) {
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
});