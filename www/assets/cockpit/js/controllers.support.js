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

NGApp.controller( 'SideTicketCtrl', function($scope, $rootScope, $routeParams, $timeout, TicketService, TicketViewService, SocketService, MainNavigationService) {

	var id_support = null;

	var reset = function(){
		$scope.ticket = { messages: { page: 0, total: false, list: [], isLoading: false } };
	}

	reset();

	var couldLoadMoreMessages = true;

	var loadData = function( callback ){
		if( $scope.ticket.messages.list && $scope.ticket.messages.total !== false && $scope.ticket.messages.list.length >= $scope.ticket.messages.total ){
			return;
		}

		if( !couldLoadMoreMessages ){
			return;
		}

		couldLoadMoreMessages = false;

		$scope.ticket.messages.page++;

		$scope.ticket.messages.isLoading = true;

		if( !id_support ){
			return;
		}

		var params = { id_support: id_support, page: $scope.ticket.messages.page };

		TicketService.side_info( params, function( data ){

			var currentScroll = $('.support-chat-contents-scroll')[0].scrollHeight;

			$scope.ticket.id_support = data.id_support;
			$scope.ticket.pexcard = data.pexcard;
			$scope.ticket.restaurant = data.restaurant;
			$scope.ticket.order = data.order;
			$scope.ticket.messages.total = data.messages.total;

			var messages = [];
			for( x in data.messages.list ){
				if( data.messages.list[ x ].id_support_message ){
					messages.push( data.messages.list[ x ] );
				}
			}

			for( x in $scope.ticket.messages.list ){
				if( $scope.ticket.messages.list[ x ].id_support_message ){
					messages.push( $scope.ticket.messages.list[ x ] );
				}
			}

			$scope.ticket.messages.list = messages;
			TicketViewService.messages = messages;

			if( callback ){
				callback();
			}

			setTimeout(function() {
				var finalScroll = $('.support-chat-contents-scroll')[0].scrollHeight;
				var goToScroll = finalScroll - currentScroll;
				$('.support-chat-contents-scroll')[0].scrollTop = goToScroll;
				setTimeout(function() { couldLoadMoreMessages = true }, 500 );
			}, 100 );

			$scope.ticket.messages.isLoading = false;

		} );
	}

	$scope.loadMoreMessages = function(){
		loadData();
	}

	$rootScope.$on( 'loadMoreMessages', function(e, data) {
		$scope.loadMoreMessages();
	} );

	var loadTicket = function( id, callback ) {
		TicketViewService.id_support = id_support;
		var displayTicket = function( ticket ) {
			TicketViewService.scope.ticket = ticket;
		};
		if (typeof id == 'string') {
			id_support = TicketViewService.scope.viewTicket;
			TicketService.get( id, displayTicket );
		} else {
			id_support = TicketViewService.scope.viewTicket.id;
			displayTicket( id );
		}

		loadData( callback );
	};

	$rootScope.$on( 'newSupportMessage', function(e, data) {
		$scope.ticket.messages.list.push( data );
		scrollDown();
	} );

	loadTicket( TicketViewService.scope.viewTicket, function(){ loadTicketPage(); } );

	var loadTicketPage = function(){
		scrollDown();
		if( parseInt( $routeParams.id ) != parseInt( id_support ) ){
			$timeout( function(){
				MainNavigationService.link( '/ticket/' + id_support );
			}, 500 );
		}
	}

	var scrollDown = function(){
		$timeout( function(){
			$('.support-chat-contents-scroll').stop( true, false ).animate( { scrollTop: $('.support-chat-contents-scroll')[0].scrollHeight }, 0 );
		}, 300 );
	}

	SocketService.listen('ticket.' + TicketViewService.scope.viewTicket, TicketViewService.scope)
		.on('message', function(d) {
			for (var x in TicketViewService.messages) {
				if (TicketViewService.messages[x].guid == d.guid) {
					return;
				}
			}
			TicketViewService.messages.push(d);
			TicketViewService.scroll();
		});

	// var sendingNote = false;

	// $scope.add_note = function(){
	// 	if( !sendingNote ){
	// 		sendingNote = true;
	// 	}
	// 	if ( sendingNote ) {
	// 		if( $scope.message_text ){
	// 			$scope.send( $scope.message_text, true, function(){
	// 				$scope.message_text = '';
	// 				loadTicket(TicketViewService.scope.viewTicket );
	// 			} );
	// 		} else {
	// 			App.alert( 'Please type something!' );
	// 		}
	// 	};
	// }

	$rootScope.$on('triggerViewTicket', function(e, ticket) {
		TicketViewService.setViewTicket( ticket == 'refresh' ? TicketViewService.scope.ticket.id_support : ticket.id_support );
		$scope.ticket.messages.list && $scope.ticket.messages.total !== false && $scope.ticket.messages.list.length >= $scope.ticket.messages.total
		loadTicket( ticket == 'refresh' ? TicketViewService.scope.ticket : ticket, function(){ loadData(); } );
	});

	$scope.send = TicketViewService.send;

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

NGApp.controller('SupportPhoneCtrl', function( $scope, $rootScope, StaffService, CallService, MainNavigationService, TwilioService) {

	$scope.call = { staff : '', to : 'customer', _to: CallService.call_to() };
	$scope.sms = { staff : '', to : 'customer', _to: CallService.call_to() };

	$scope.$watch( 'call.staff', function( newValue, oldValue, scope ) {
		$scope.call.phone = newValue;
	}	);

	$scope.$watch( 'sms.staff', function( newValue, oldValue, scope ) {
		var values = newValue.split( '##' );
		$scope.sms.phone = values[0];
		$scope.sms.name = values[1];
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

	$scope.loadTicket = function( id_support ){
		TicketViewService.setViewTicket( 0 );
		$timeout( function(){
			TicketViewService.setViewTicket( id_support );
		}, 100 );
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
		});
	}

	CallService.list($scope.callparams, function(d) {
		$scope.calls = d.results;
	});

	update();
});
