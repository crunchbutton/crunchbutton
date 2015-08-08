NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/support', {
			action: 'support',
			controller: 'SupportCtrl',
			templateUrl: 'assets/view/support.html',
			title: 'Support',
			reloadOnSearch: false
		})
		.when('/chat', {
			action: 'chat',
			controller: 'ChatCtrl',
			templateUrl: 'assets/view/chat.html',
			title: 'Chat'
		})
		.when('/chat/:room', {
			action: 'chat',
			controller: 'ChatCtrl',
			templateUrl: 'assets/view/chat.html',
			title: 'Chat'
		})
		.when('/support/phone', {
			action: 'support',
			controller: 'SupportPhoneCtrl',
			templateUrl: 'assets/view/support-phone.html',
			title: 'Support',
			reloadOnSearch: false
		});
}]);

NGApp.controller('ChatCtrl', function($scope, $rootScope, $routeParams, SocketService, AccountService) {
	$scope.room = $routeParams.room || 'lobby';

	SocketService.listen('chat.' + $scope.room, $scope)
		.on('message', function(d) {
			console.log(d);
		});
	/*
	$scope.send = function() {
		//SocketService.socket.emit('chat.' + $scope.room, 'message');
		SocketService.socket.emit('event.message', {
			url: 'api/'
		});
	};
	*/
});


NGApp.controller('SideTicketsCtrl', function($scope, $rootScope, $location, TicketService, TicketViewService, AccountService) {

	$scope.params = { status: 'open' };

	var getTickets = function() {
		if (!AccountService || !AccountService.user || !AccountService.user.permissions) {
			return;
		}
		TicketService.shortlist( $scope.params, function(tickets) {
			TicketViewService.scope.tickets = tickets.results;
		});
	};

	if (!TicketViewService.scope.tickets) {
		getTickets();
	}

	$scope.loadTicket = function( id_support ){
		TicketViewService.setViewTicket( id_support );
	}

	$rootScope.$watch('supportMessages', function(newValue, oldValue) {
		if (!newValue) {
			return;
		}
		if (!oldValue || newValue.count != oldValue.count || newValue.timestamp != oldValue.timestamp ) {
			getTickets();
		}
	}, true);
});

NGApp.controller( 'SideTicketCtrl', function($scope, $rootScope, $routeParams, $timeout, TicketService, TicketViewService, SocketService, MainNavigationService ) {

	var id_support = null;

	if (typeof TicketViewService.scope.viewTicket == 'string') {
		id_support = TicketViewService.scope.viewTicket;
	} else {
		id_support = TicketViewService.scope.viewTicket.id;
	}

	TicketViewService.sideInfo.setTicket( id_support );

	var loadData = function(){
		if( TicketViewService.sideInfo.load() ){
			$scope.isLoading = true;
		}
	}

	// $scope.$watchCollection( 'ticket', function( newValue, oldValue ) {
	// 	if( newValue && newValue.total && !$rootScope.supportToggled ){
	// 		$timeout( function(){
	// 			$rootScope.supportToggled = true;
	// 		}, 300 );
	// 	}
	// } );

	$scope.isLoading = false;

	$scope.loadMoreMessages = function(){
		loadData()
	}

	$rootScope.$on( 'triggerTicketInfoUpdated', function(e, data) {
		console.log('$scope.ticket',$scope.ticket);
		console.log('data',data);
		if( $scope.ticket.id_support != data.id_support ){
			$scope.ticket = data;
		} else {
			if( !angular.equals( $scope.ticket.messages, data.messages ) ){
				if( !$scope.ticket.messages || ( $scope.ticket.messages && $scope.ticket.messages.length < data.messages.length ) ){
					$scope.ticket.messages = data.messages;
				}

				console.log('!=!=!=!=!=');
			} else {
				console.log('=====');
			}

			if( $scope.ticket.total != data.total ){
				$timeout( function(){
					$scope.ticket.has_more = data.has_more;
					$scope.ticket.loaded = data.loaded;
					$scope.ticket.page = data.page;
					$scope.ticket.total = data.total;
					console.log('update everything else');
				 }, 300 );
			}
		}

		$scope.isLoading = false;
		if( !$rootScope.supportToggled ){
			$timeout( function(){ $rootScope.supportToggled = true; }, 300 );
		}
	} );


	$rootScope.$on( 'loadMoreMessages', function(e, data) {
		$scope.loadMoreMessages();
	} );

	var socketStuff = function(){
		SocketService.listen('ticket.' + id_support, TicketViewService.scope ).on('message', function(d) {
			if( d.guid ){
				for ( var x in TicketViewService.sideInfo.data.messages ) {
					if ( TicketViewService.sideInfo.data.messages[x].guid == d.guid ) {
						// if( TicketViewService.sideInfo.data.messages[x].sending ){
							// TicketViewService.sideInfo.data.messages[x] = d;
							// console.log('updated status');
						// }
						console.log('message already added!');
						return;
					}
				}
			}
			TicketViewService.sideInfo.add_message( d );
		});
	}

	$rootScope.$on( 'triggerViewTicket', function(e, ticket) {
		if( ticket.id_support != TicketViewService.sideInfo.id_support ){
			TicketViewService.sideInfo.setTicket( 0 );
			TicketViewService.sideInfo.setTicket( ticket.id_support );
			loadData();
			socketStuff();
		}
	} );

	$scope.send = TicketViewService.send;
	loadData();
	socketStuff();

} );

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

NGApp.controller('SupportPhoneCtrl', function( $scope, $rootScope, StaffService, CallService, MainNavigationService, TwilioService) {

	$scope.call = { staff : '', to : 'customer', _to: CallService.call_to() };
	$scope.sms = { staff : '', to : 'customer', _to: CallService.call_to() };

	$scope.$watch( 'call.staff', function( newValue, oldValue, scope ) {
		$scope.call.phone = newValue.phone;
	}	);

	$scope.$watch( 'sms.staff', function( newValue, oldValue, scope ) {
		$scope.sms.phone = newValue.phone;
		$scope.sms.name = newValue.name;
	}	);

	StaffService.phones( function( response ){
		$scope.staff = response;
	} );

	$scope.formCallSending = false;
	$scope.formSMSSending = false;

	$scope.reset = function(){
		$scope.call.phone = '';
		$scope.sms.phone = '';
		$scope.sms.message = '';
	}


	$scope.sms.send = function(){

		if( $scope.formSMS.$invalid ){
			$scope.formSMSSubmitted = true;
			return;
		}

		$scope.formSMSSending = true;
		CallService.send_sms( $scope.sms, function( json ){
			$scope.formSMSSending = false;
			if( json.success ){
				MainNavigationService.link( '/ticket/' + json.success);
				if( $scope.complete ){
					$scope.complete();
				}
				$scope.reset();
			} else {
				App.alert( json.error );
			}
		} );
	}

	$scope.call.make = function(){
		if( $scope.formCall.$invalid ){
			$scope.formCallSubmitted = true;
			return;
		}
		$scope.formCallSending = true;
		CallService.make_call( $scope.call, function( json ){
			$scope.formCallSending = false;
			if( json.success ){
				App.alert( json.success );
				$scope.reset();
			} else {
				App.alert( json.error );
			}
		} );
	}

	$scope.call.voip = function() {
		TwilioService.call($scope.call.phone);
		App.dialog.close();
	};

	// Reset stuff when calling from modal
	$rootScope.$on('callText', function(e, num) {
		$scope.reset();
		$scope.call.phone = num;
		$scope.sms.phone = num;
	} );

} );

NGApp.controller('SupportCtrl', function($scope, $rootScope, $timeout, TicketService, TicketViewService, CallService) {

	$scope.ticketparams = {
		status: 'open'
	};

	$scope.callparams = {
		status: ['in-progress','ringing'],
		limit: 5,
		today: true
	};

	$scope.closeTicket = function( id_support ){
		TicketService.openClose( id_support, function() { update(); } );
	}

	$rootScope.$watch('supportMessages', function(newValue, oldValue) {
		if (!newValue) {
			return;
		}
		if (!oldValue || newValue.count != oldValue.count || newValue.timestamp != oldValue.timestamp ) {
			update();
		}
	}, true);

	var update = function(){
		TicketService.list($scope.ticketparams, function(d) {
			$scope.lotsoftickets = d.results;
			TicketViewService.scope.tickets = d.results;
		});
	}

	CallService.list($scope.callparams, function(d) {
		$scope.calls = d.results;
	});

	update();
});
