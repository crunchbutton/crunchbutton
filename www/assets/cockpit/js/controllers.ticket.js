NGApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider
		.when('/tickets', {
			action: 'tickets',
			controller: 'TicketsCtrl',
			templateUrl: 'assets/view/tickets.html',
			reloadOnSearch: false

		}).when('/ticket/:id', {
			action: 'ticket',
			controller: 'TicketCtrl',
			templateUrl: 'assets/view/tickets-ticket.html'
		});
}]);

NGApp.controller('TicketsCtrl', function ($rootScope, $scope, TicketService, ViewListService) {
	$rootScope.title = 'Tickets';

	angular.extend($scope, ViewListService);

	$scope.view({
		scope: $scope,
		watch: {
			search: '',
			type: 'all',
			status: 'all',
			admin: 'all',
			fullcount: false
		},
		update: function() {
			update();
		}
	});

	var update = function(){
		TicketService.list($scope.query, function(d) {
			$scope.lotsoftickets = d.results;
			$scope.complete(d);
		});
	}

	$scope.closeTicket = function( id_support ){
		TicketService.openClose( id_support, function() { update(); } );
	}

});


NGApp.controller('TicketCtrl', function($scope, $rootScope, $interval, $routeParams, OrderService, TicketService, MapService, SocketService) {

	$rootScope.title = 'Ticket #' + $routeParams.id;
	$scope.loading = true;

	SocketService.listen('ticket.' + $routeParams.id, $scope).on('update', function(d) { update(); });

	$scope.refund = function(){
		OrderService.askRefund( $scope.ticket.order.id_order, function(){
			update();
		} );
	}

	var couldLoadMoreMessages = true;

	$scope.messages = function( callback ){

		if( $scope.ticket.messages && $scope.ticket.messages.length >= $scope.ticket.total_messages ){
			return;
		}

		if( !couldLoadMoreMessages ){
			return;
		}

		couldLoadMoreMessages = false;

		$scope.ticket.messages_page++;

		$scope.ticket.isLoadingMessages = true;

		var params = { id_support: $routeParams.id, page: $scope.ticket.messages_page }

		TicketService.messages( params, function( data ){

			var currentScroll = $('.support-chat-contents-scroll')[0].scrollHeight;

			var new_messages = [];
			for( x in data ){
				if( data[ x ].id_support_message ){
					new_messages.push( data[ x ] );
				}
			}

			for( x in $scope.ticket.messages ){
				if( $scope.ticket.messages[ x ].id_support_message ){
					new_messages.push( $scope.ticket.messages[ x ] );
				}
			}
			$scope.ticket.messages = [];
			$scope.ticket.messages = new_messages;
			if( callback ){
				callback();
			}

			setTimeout(function() {
				var finalScroll = $('.support-chat-contents-scroll')[0].scrollHeight;
				var goToScroll = finalScroll - currentScroll;
				$('.support-chat-contents-scroll')[0].scrollTop = goToScroll;
				setTimeout(function() { couldLoadMoreMessages = true }, 500 );
			}, 100 );

			$scope.ticket.isLoadingMessages = false;

		} );
	}

	$rootScope.$on( 'loadMoreMessages', function(e, data) {
		$scope.messages();
	} );

	$scope.do_not_pay_driver = function(){
		OrderService.do_not_pay_driver( $scope.ticket.order.id_order, function( result ){
			if( result.success ){
				$scope.flash.setMessage( 'Saved!' );
				$scope.ticket.order.do_not_pay_driver = ( $scope.ticket.order.do_not_pay_driver ? 0 : 1 );
			} else {
				$scope.flash.setMessage( 'Error!' );
			}
		} );
	}

	$scope.do_not_pay_restaurant = function(){
		OrderService.do_not_pay_restaurant( $scope.ticket.order.id_order, function( result ){
			if( result.success ){
				$scope.flash.setMessage( 'Saved!' );
				$scope.ticket.order.do_not_pay_restaurant = ( $scope.ticket.order.do_not_pay_restaurant ? 0 : 1 );
			} else {
				$scope.flash.setMessage( 'Error!' );
			}
		} );
	}

	$scope.do_not_reimburse_driver = function(){
		OrderService.do_not_reimburse_driver( $scope.ticket.order.id_order, function( result ){
			if( result.success ){
				$scope.flash.setMessage( 'Saved!' );
				$scope.ticket.order.do_not_reimburse_driver = ( $scope.ticket.order.do_not_reimburse_driver ? 0 : 1 );
			} else {
				$scope.flash.setMessage( 'Error!' );
			}
		} );
	}

	$scope.isCommentSaving = false;

	var saveComment = function( close ){
		if( close && $scope.ticket.status != 'open' ){
			return;
		}
		if( $scope.comment_text ){
			$scope.isCommentSaving = true;
			TicketService.message( { 'id_support': $routeParams.id, 'body': $scope.comment_text, 'note': true }, function(){
				$scope.comment_text = '';
				$scope.isCommentSaving = false;
				if( close ){
					if( $scope.ticket.status == 'open' ){
						$scope.openCloseTicket();
					}
				} else {
					update();
				}
			} );
		} else {
			App.alert( 'Please type something!' );
		}
	}

	$scope.comment = function(){
		saveComment( false );
	}

	$scope.close_and_comment = function(){
		saveComment( true );
	}

	var cleanup;

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
			$scope.ticket.messages_page = 0;
			$scope.loading = false;

			if (!cleanup) {
				if( ticket && ticket.order ){
					cleanup = $rootScope.$on('order-route-' + ticket.order.id_order, function(event, args) {
						$scope.$apply(function() {
							$scope.eta = args;
						});
						console.debug('Got route update: ', args);
					});
				}
			}
			$rootScope.$broadcast('triggerViewTicket', $scope.ticket);
			draw();
			$scope.ticket.isLoadingMessages = false;
			$scope.messages( function(){ scrollDown() } );

			$scope.ticket.loadMessages = function(){
				$scope.messages();
			}

		});
	};

	var scrollDown = function(){
		setTimeout( function(){
				$('.support-chat-contents-scroll').stop( true, false ).animate( { scrollTop: $('.support-chat-contents-scroll')[0].scrollHeight }, 0 );
			}, 500 );
	}

	$scope.openCloseTicket = function(){
		TicketService.openClose( $routeParams.id, function() { update(); } );
	}

	$scope.$on('mapInitialized', function(event, map) {
		$scope.map = map;
		MapService.style(map);
		draw();
	});

	update();



});