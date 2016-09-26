NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/support', {
			action: 'support',
			controller: 'SupportCtrl',
			templateUrl: '/assets/view/support.html',
			title: 'Support',
			reloadOnSearch: false
		})
		.when('/chat', {
			action: 'chat',
			controller: 'ChatCtrl',
			templateUrl: '/assets/view/chat.html',
			title: 'Chat'
		})
		.when('/chat/:room', {
			action: 'chat',
			controller: 'ChatCtrl',
			templateUrl: '/assets/view/chat.html',
			title: 'Chat'
		})
		.when('/support/phone', {
			action: 'support',
			controller: 'SupportPhoneCtrl',
			templateUrl: '/assets/view/support-phone.html',
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


NGApp.controller('SideTicketsCtrl', function($scope, $rootScope, $location, TicketService, TicketViewService, AccountService, $timeout) {

	$scope.params = { status: 'open' };

	var getTickets = function() {
		if (!AccountService || !AccountService.user || !AccountService.user.permissions) {
			return;
		}
		TicketService.list_beta( $scope.params, function(tickets) {
			TicketViewService.scope.tickets = tickets.results;
		});
	};

	if (!TicketViewService.scope.tickets) {
		getTickets();
	}

	$scope.loadTicket = function( id_support ){
		TicketViewService.setViewTicket( id_support );
	}

	$scope.closeTicket = function( id_support ){
		TicketService.openClose( id_support, function() {
			getTickets();
			$rootScope.$broadcast( 'ticketStatusUpdated', { ignoreBroadcast: true } );
		} );
	}

	var updateSideTickets = $rootScope.$on( 'updateSideTickets', function(e, data) {
		getTickets();
	});

	var supportMessages = $rootScope.$watch('supportMessages', function(newValue, oldValue) {
		if (!newValue) {
			return;
		}
		if (!oldValue || newValue.count != oldValue.count || newValue.timestamp != oldValue.timestamp ) {
			getTickets();
		}
	}, true);

	$scope.$on('$destroy', function() {
		if(updateSideTickets){updateSideTickets();}
		if(supportMessages){supportMessages();}
	});

	$timeout(function(){getTickets();},1500);

});

NGApp.controller( 'SideTicketCtrl', function($scope, $route, $rootScope, $routeParams, $timeout, TicketService, TicketViewService, SocketService, MainNavigationService ) {

	var id_support = null;

	if (typeof TicketViewService.scope.viewTicket == 'string' || typeof TicketViewService.scope.viewTicket == 'number') {
		id_support = TicketViewService.scope.viewTicket;
	} else {
		id_support = TicketViewService.scope.viewTicket.id;
	}

	TicketViewService.sideInfo.setTicket( id_support );

	$scope.reloadMessages = function(){
		TicketViewService.sideInfo.setTicket( id_support );
		socketStuff();
		loadData();
	}

	var loadData = function(){
		if( TicketViewService.sideInfo.load() ){
			$scope.isLoading = true;
		}
	}

	$scope.closeTicket = function(){
		TicketService.openClose( id_support, function() {
			$scope.setViewTicket( 0 );
			setTimeout(function(){
				TicketService.shortlist( { status: 'open' }, function(tickets) {
					TicketViewService.scope.tickets = tickets.results;
				});
			}, 1000);
			$rootScope.$broadcast( 'updateSideTickets' );
			$rootScope.$broadcast( 'ticketStatusUpdated', { ignoreBroadcast: true } );
		} );
	}

	$scope.isLoading = false;

	$scope.loadMoreMessages = function(){
		loadData()
	}

	var triggerTicketInfoUpdated = $scope.$on( 'triggerTicketInfoUpdated', function(e, data) {

		$rootScope.$safeApply( function(){
			if( !$scope.ticket || $scope.ticket.id_support != data.id_support ){
				$scope.ticket = data;
			} else {
				if( !angular.equals( $scope.ticket.messages, data.messages ) ){
					$scope.ticket.messages = data.messages;
				}
				$timeout( function(){
					$scope.ticket.has_more = data.has_more;
					$scope.ticket.loaded = data.loaded;
					$scope.ticket.page = data.page;
					$scope.ticket.total = data.total;
				 }, 300 );
			}
		} );

		$scope.isLoading = false;
		if( !$rootScope.supportToggled ){
			$timeout( function(){ $rootScope.supportToggled = true; }, 300 );
		}
	} );

	var loadMoreMessages = $scope.$on( 'loadMoreMessages', function(e, data) {
		$scope.loadMoreMessages();
	} );

	var triggerViewTicket = $scope.$on( 'triggerViewTicket', function(e, ticket) {
		if( ticket.pexcard != $scope.ticket.pexcard ){
			$scope.ticket.pexcard = ticket.pexcard;
		}
		$scope.ticket.status = ticket.status;
		if( ticket.id_support != TicketViewService.sideInfo.id_support ){
			TicketViewService.sideInfo.setTicket( 0 );
			TicketViewService.sideInfo.setTicket( ticket.id_support );
			loadData();
			socketStuff();
		}
	} );

	var socketStuff = function(){
		SocketService.listen('ticket.' + id_support, TicketViewService.scope ).on('message', function(d) {
			if( d.guid ){
				for ( var x in TicketViewService.sideInfo.data.messages ) {
					if ( TicketViewService.sideInfo.data.messages[x].guid == d.guid ) {
						if( TicketViewService.sideInfo.data.messages[x].sending ){
							TicketViewService.sideInfo.data.messages[x] = d;
						}
						return;
					}
				}
			}
			TicketViewService.sideInfo.add_message( d );
		});
	}

	$scope.send = TicketViewService.send;
	loadData();
	socketStuff();

	$scope.$on('$destroy', function() {
		triggerTicketInfoUpdated();
		loadMoreMessages();
		triggerViewTicket();
	});


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

		if( $scope.ticket && $scope.ticket.pexcard && $scope.ticket.pexcard.id_pexcard ){

			$scope.isLoadingBalance = true;

			PexCardService.cache( $scope.ticket.pexcard.id_pexcard,
				function( json ){
				PexCardService.id_pexcard( $scope.ticket.pexcard.id_pexcard,
					function( json ){
						$scope.isLoadingBalance = false;
						if( json.id ){
							$scope.card = json;
						} else {
							$scope.flash.setMessage( json.error, 'error' );
						}
					}
				);
				}
			);

		} else {
			App.alert( 'Oops, it seems the driver doesn\'t have a PexCard!' );
		}
	}

} );

NGApp.controller('SupportPhoneCtrl', function( $scope, $rootScope, StaffService, CallService, MainNavigationService, TwilioService, AccountService) {

	$scope.call = { staff : '', to : 'customer', _to: CallService.call_to() };
	$scope.sms = { staff : '', to : 'customer', _to: CallService.call_to(), open_ticket: true };

	$scope.sms.staffList = [];

	$scope.yesNo = StaffService.yesNo();

	$scope.$watch( 'call.staff', function( newValue, oldValue, scope ) {
		$scope.call.phone = newValue.phone;
	}	);

	$scope.$watch( 'sms.staff', function( newValue, oldValue, scope ) {
		$scope.sms.phone = newValue.phone;
		$scope.sms.name = newValue.name;
	}	);

	$scope.$watch( 'sms.staffList', function( newValue, oldValue, scope ) {
		$scope.sms.phones = '';
		var commas = '';
		for(x in $scope.sms.staffList){
			$scope.sms.phones += commas + $scope.sms.staffList[x].phone;
			commas = ', ';
		}
	}	);

	$scope.formCallSending = false;
	$scope.formSMSSending = false;

	$scope.reset = function(){

		$scope.call.phone = '';
		$scope.sms.phone = '';
		$scope.sms.message = '';
		$scope.sms.open_ticket = true;
		$scope.sms.phones = '';
		$scope.sms.staffList = [];

		StaffService.phones( function( response ){
			$scope.staff = response;
		} );
	}

	$scope.sms.send = function(){

		if( $scope.formSMS.$invalid ){
			$scope.formSMSSubmitted = true;
			return;
		}

		if($scope.sms.phones && $scope.sms.phones != ''){
			$scope.sms.phone = [];
			$scope.sms.phone = $scope.sms.phones.split(',');
			for(x in $scope.sms.phone){
				$scope.sms.phone[x] = $scope.sms.phone[x].trim();
			}
		}

		if( $scope.sms.phone && angular.isArray( $scope.sms.phone ) ){
			$scope.formSMSSending = true;
			CallService.send_sms_list( $scope.sms, function( json ){
				$scope.formSMSSending = false;
				if( json.success ){
					if( $scope.complete ){
						$scope.complete( json );
					}
					$scope.reset();
				} else {
					setTimeout(function() { App.alert( json.error ); }, 100);
				}
			} );

		} else {
			$scope.formSMSSending = true;
			CallService.send_sms( $scope.sms, function( json ){
				$scope.formSMSSending = false;
				if( json.success ){
					if( $scope.sms.open_ticket ){
						MainNavigationService.link( '/ticket/' + json.success);
					} else {
						App.alert( 'Message sent!' );
					}
					if( $scope.complete ){
						$scope.complete();
					}
					$scope.reset();
				} else {
					App.alert( json.error );
				}
			} );
		}
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
		CallService.register_voip( $scope.call, function( json ){} );
		TwilioService.call( $scope.call.phone );
		App.dialog.close();
	};

	// Reset stuff when calling from modal
	$rootScope.$on('callText', function(e, num) {
		$scope.reset();
		$scope.call.phone = num;
		$scope.sms.phone = num;
	} );

	$rootScope.$on('textNumber', function(e, phones) {
		$scope.reset();
		$scope.sms.phone = phones;
	} );

	$rootScope.$on('textInfo', function(e, data) {
		$scope.sms.message = data.message;
		$scope.sms.permalink = data.permalink;
		$scope.sms.type = data.type;
	} );

	if ($rootScope.account.isLoggedIn()) {
		$scope.reset();
	}

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
		TicketService.shortlist($scope.ticketparams, function(d) {
			$scope.lotsoftickets = d.results;
			TicketViewService.scope.tickets = d.results;
		});
	}

	CallService.list($scope.callparams, function(d) {
		$scope.calls = d.results;
	});

	update();
});
