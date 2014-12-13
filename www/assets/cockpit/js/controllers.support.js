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

NGApp.controller('SideTicketCtrl', function($scope, $rootScope, TicketService, TicketViewService, SocketService) {

	var loaded = false;

	SocketService.listen('ticket.' + TicketViewService.scope.viewTicket, TicketViewService.scope)
		.on('message', function(d) {
			for (var x in TicketViewService.scope.ticket.messages) {
				if (TicketViewService.scope.ticket.messages[x].guid == d.guid) {
					return;
				}
			}

			TicketViewService.scope.ticket.messages.push(d);
			TicketViewService.scroll();
		});

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

NGApp.controller('SideSupportPexCardCtrl', function( $scope, StaffPayInfoService, PexCardService ) {

	$scope.add_funds = function(){
		if( $scope.ticket && $scope.ticket.pexcard && $scope.ticket.pexcard.id_pexcard ){
			if( $scope.form.$invalid ){
				App.alert( 'Please fill in all required fields' );
				$scope.submitted = true;
				return;
			}
			$scope.pexcard.id_pexcard = $scope.ticket.pexcard.id_pexcard;
			$scope.isAdding = true;
			PexCardService.add_funds( $scope.pexcard, function( data ){
				if( data.error ){
					App.alert( data.error);
					$scope.isAdding = false;
					return;
				} else {
					$scope.isAdding = false;
					$scope.flash.setMessage( 'Funds Added!' );
					$scope.pexcard = {};
					$scope.isLoadingBalance = true;
					setTimeout( function(){ $scope.current_balanced(); }, 3000 );
				}
			} );
		} else {
			App.alert( 'Oops, it seems the driver doesn\'t have a PexCard!' );
		}
	}

	$scope.current_balanced = function(){
		if( $scope.ticket && $scope.ticket.pexcard && $scope.ticket.pexcard.card_serial ){

			$scope.isLoadingBalance = true;

			PexCardService.pex_id( $scope.ticket.pexcard.card_serial,
				function( json ){
					$scope.isLoadingBalance = false;
					if( json.id ){
						$scope.card = json;
					} else {
						$scope.flash.setMessage( json.error, 'error' );
					}
				}
			);
		} else {
			App.alert( 'Oops, it seems the driver doesn\'t have a PexCard!' );
		}
	}

} );


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
